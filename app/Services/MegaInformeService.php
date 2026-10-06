<?php

namespace App\Services;

use App\Models\BloqueInforme;
use App\Models\Grupo;
use App\Models\GrupoDeGrupo;
use App\Models\InformeEnCola;
use App\Models\InformePersonalizado;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class MegaInformeService
{
    /**
     * Meses en español en mayúsculas.
     */
    protected array $meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

    /**
     * Procesa la generación del Megainforme a partir de un registro de InformeEnCola.
     *
     * @return array{tablaHtml: string, nombreArchivo: string, informe: InformePersonalizado}
     */
    public function procesar(InformeEnCola $informeEnCola): array
    {
        $informe = InformePersonalizado::with(['secciones.subsecciones.items', 'bloques'])
            ->findOrFail($informeEnCola->informe_personalizado_id);

        $anio = (int) $informeEnCola->year;
        $rango = (string) $informeEnCola->periodo;
        $semana = $informeEnCola->semana;

        // 1. Cálculo de Rango de Fechas
        [$fechaInicio, $fechaFin] = $this->calcularRangoFechas($rango, $anio, $semana);

        // 2. Cálculo de Semanas y Meses
        [$semanaIni, $semanaFin] = $this->calcularSemanas($fechaInicio, $fechaFin, $anio);
        $mesIni = (int) date('m', strtotime($fechaInicio));
        $mesFin = (int) date('m', strtotime($fechaFin));

        // 3. Encabezados de Meses y Semanas
        [$thSoloMeses, $colspanPorMeses] = $this->construirHeadersMeses($mesIni, $mesFin);
        [$thSemanas, $thMeses, $colspanPorSemanas, $arraySemanasDatos] = $this->construirHeadersSemanas($semanaIni, $semanaFin, $anio);

        // 4. Construcción del THEAD de 4 niveles
        [$tablaHead, $arrayPasosItems] = $this->construirThead(
            $informe,
            $thSoloMeses,
            $colspanPorMeses,
            $thSemanas,
            $colspanPorSemanas
        );

        // 5. Consulta de Grupos Ministeriales (Jerarquía con GrupoDeGrupo)
        $grupoSeleccionado = Grupo::findOrFail($informeEnCola->grupo_id);
        $tipoGrupoId = $informeEnCola->agrupar_por_tipo_grupo_id;

        $gruposMinisterio = GrupoDeGrupo::where('grupo_padre', $grupoSeleccionado->id)
            ->where('tipo_grupo_id_hijo', $tipoGrupoId)
            ->with('grupoHijo')
            ->get();

        // Fallback si la tabla grupos_de_grupos aún no tiene registros para este grupo
        $gruposAProcesar = [];
        if ($gruposMinisterio->isNotEmpty()) {
            foreach ($gruposMinisterio as $gdg) {
                if ($gdg->grupoHijo) {
                    $gruposAProcesar[] = $gdg->grupoHijo;
                }
            }
        } else {
            // Si el grupo raíz coincide con el tipo
            if ($grupoSeleccionado->tipo_grupo_id == $tipoGrupoId) {
                $gruposAProcesar[] = $grupoSeleccionado;
            }
            // O grupos directos del ministerio
            $gruposDirectos = $grupoSeleccionado->gruposMinisterio()
                ->where('tipo_grupo_id', $tipoGrupoId)
                ->where('dado_baja', false)
                ->get();
            foreach ($gruposDirectos as $g) {
                $gruposAProcesar[] = $g;
            }
        }

        // 6. Extracción y Cálculo de Métricas por cada Grupo
        $megaJson = [];
        $secciones = $informe->secciones;

        foreach ($gruposAProcesar as $grupoHijo) {
            // Obtener todos los IDs de grupos descendientes de este grupo
            $gruposDelGrupo = GrupoDeGrupo::where('grupo_padre', $grupoHijo->id)
                ->pluck('grupo_hijo')
                ->toArray();
            $gruposDelGrupo = array_unique(array_merge([$grupoHijo->id], $gruposDelGrupo));

            // Obtener IDs de personas en el grupo directo (para herencia de creación)
            $idsPersonasGrupoDirecto = DB::table('integrantes_grupo')
                ->where('grupo_id', $grupoHijo->id)
                ->pluck('user_id')
                ->toArray();
            if (empty($idsPersonasGrupoDirecto)) {
                $idsPersonasGrupoDirecto = [0];
            }

            $arrayItem = [];

            foreach ($secciones as $seccion) {
                foreach ($seccion->subsecciones as $subseccion) {
                    foreach ($subseccion->items as $item) {
                        $tdTotalTemporal = 0;
                        $tdTemporal = '';
                        $tipoTD = 'normal';
                        $semanal = null;
                        $mensual = null;
                        $dataItem = null;
                        $estaCalculado = 'si';

                        if (! $item->con_operacion && empty($item->operacion) && empty($item->totalizar_items)) {
                            $tipoTD = 'normal';

                            // Construcción de la consulta base para el ítem
                            $baseQuery = $this->construirQueryItem(
                                $item,
                                $gruposDelGrupo,
                                $idsPersonasGrupoDirecto,
                                $fechaInicio,
                                $fechaFin
                            );

                            if ($item->visualizar_por_mes) {
                                $mensual = 'si';
                                $dataItem = [];
                                for ($i = $mesIni; $i <= $mesFin; $i++) {
                                    $primerDiaMes = Carbon::create($anio, $i, 1)->startOfMonth()->format('Y-m-d');
                                    $ultimoDiaMes = Carbon::create($anio, $i, 1)->endOfMonth()->format('Y-m-d');

                                    $queryMes = (clone $baseQuery);
                                    $this->aplicarFiltrosTemporales($queryMes, $item, $primerDiaMes, $ultimoDiaMes);

                                    $cantidadMes = $queryMes->distinct('users.id')->count('users.id');
                                    $tdTemporal .= "<td style='text-align: center; border: 1px solid #ddd;'>{$cantidadMes}</td>";
                                    $tdTotalTemporal += $cantidadMes;
                                    $dataItem[] = $cantidadMes;
                                }
                            } elseif ($item->visualizar_por_semanas) {
                                $semanal = 'si';
                                $dataItem = [];
                                foreach ($arraySemanasDatos as $datoSemana) {
                                    $querySemana = (clone $baseQuery);
                                    $this->aplicarFiltrosTemporales(
                                        $querySemana,
                                        $item,
                                        $datoSemana['primerDia'],
                                        $datoSemana['ultimoDia']
                                    );

                                    $cantidadSemana = $querySemana->distinct('users.id')->count('users.id');
                                    $tdTemporal .= "<td style='text-align: center; border: 1px solid #ddd;'>{$cantidadSemana}</td>";
                                    $tdTotalTemporal += $cantidadSemana;
                                    $dataItem[] = $cantidadSemana;
                                }
                            } else {
                                $cantidadTotal = $baseQuery->distinct('users.id')->count('users.id');
                                $tdTemporal .= "<td style='text-align: center; border: 1px solid #ddd;'>{$cantidadTotal}</td>";
                                $tdTotalTemporal = $cantidadTotal;
                            }
                        } elseif (! empty($item->operacion)) {
                            $tipoTD = 'operacion';
                            $estaCalculado = 'no';
                        } elseif (! empty($item->totalizar_items)) {
                            $tipoTD = 'totalizar';
                            $estaCalculado = 'no';
                        }

                        $stdItem = new stdClass;
                        $stdItem->id = $item->id;
                        $stdItem->item = $item->nombre;
                        $stdItem->estaCalculado = $estaCalculado;
                        $stdItem->total = $tdTotalTemporal;
                        $stdItem->tds = $tdTemporal;
                        $stdItem->dataItem = $dataItem;
                        $stdItem->tipoTD = $tipoTD;
                        $stdItem->semanal = $semanal;
                        $stdItem->mensual = $mensual;
                        $stdItem->totalizar_items = $item->totalizar_items;
                        $stdItem->operacion = $item->operacion;
                        $stdItem->item_a = $item->item_a;
                        $stdItem->item_b = $item->item_b;

                        $arrayItem['item'.$item->id] = $stdItem;
                    }
                }
            }

            $fila = new stdClass;
            $fila->grupo = $grupoHijo->nombre.' #'.$grupoHijo->id;
            $fila->sede_id = $grupoHijo->sede_id;
            $fila->data = $arrayItem;

            $megaJson[] = $fila;
        }

        // 7. Resolución de Fórmulas y Operaciones en Cascada (do-while)
        $this->resolverOperacionesCascada($megaJson);

        // 8. Construcción del TBODY agrupado por Bloques (Sedes) y Totales
        $tablaBody = $this->construirTbody($megaJson, $informe->bloques);

        $tablaCompleta = "<table style='border-collapse: collapse; width: 100%;'>".$tablaHead.$tablaBody.'</table>';

        $nombreArchivoSanitizado = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '_', $informe->nombre)).'_'.time();

        return [
            'tablaHtml' => $tablaCompleta,
            'nombreArchivo' => $nombreArchivoSanitizado,
            'informe' => $informe,
        ];
    }

    /**
     * Construye la consulta base para un ítem sin crear vistas temporales.
     */
    protected function construirQueryItem(
        mixed $item,
        array $gruposDelGrupo,
        array $idsPersonasGrupoDirecto,
        string $fechaInicio,
        string $fechaFin
    ) {
        $query = DB::table('users')
            ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
            ->where(function ($q) use ($gruposDelGrupo, $idsPersonasGrupoDirecto) {
                $q->whereIn('integrantes_grupo.grupo_id', $gruposDelGrupo)
                    ->orWhere(function ($qSub) use ($idsPersonasGrupoDirecto) {
                        $qSub->whereNull('integrantes_grupo.grupo_id')
                            ->whereIn('users.creado_por_user_id', $idsPersonasGrupoDirecto);
                    });
            });

        // Filtro por fecha de creación/ingreso del usuario
        if ($item->fecha_creacion) {
            $query->whereBetween('users.created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59']);
        }

        // Filtro por grupo de personas (1: Todos, 2: Alta/Activos, 3: Baja/Eliminados)
        if ($item->grupo_de_personas == 2) {
            $query->whereNull('users.deleted_at');
        } elseif ($item->grupo_de_personas == 3) {
            $query->whereNotNull('users.deleted_at');
        }

        // Filtro por tipo de vinculación
        if (! empty($item->filtrar_tipo_vinculacion)) {
            $idsVinculacion = array_filter(explode(',', $item->filtrar_tipo_vinculacion));
            if (! empty($idsVinculacion)) {
                $query->whereIn('users.tipo_vinculacion_id', $idsVinculacion);
            }
        }

        // Filtro por estado civil
        if (! empty($item->filtrar_estado_civil)) {
            $idsEstadoCivil = array_filter(explode(',', $item->filtrar_estado_civil));
            if (! empty($idsEstadoCivil)) {
                $query->whereIn('users.estado_civil_id', $idsEstadoCivil);
            }
        }

        // Filtro por Paso de Crecimiento 1
        if (! empty($item->paso_crecimiento_id)) {
            $query->join('crecimiento_usuario as cu1', function ($join) use ($item) {
                $join->on('users.id', '=', 'cu1.user_id')
                    ->where('cu1.paso_crecimiento_id', '=', $item->paso_crecimiento_id);
            });

            if (! empty($item->estado_paso_crecimiento)) {
                $query->where('cu1.estado_id', $item->estado_paso_crecimiento);
            }

            if ($item->filtrar_fecha_paso_crecimiento) {
                $query->whereBetween('cu1.fecha', [$fechaInicio, $fechaFin]);
            }
        }

        // Comparación con Paso de Crecimiento 2
        if ($item->parametro_de_comparacion === 'paso-crecimiento' && ! empty($item->paso_crecimiento_id)) {
            if ($item->no_existe_paso_crecimiento_id_2 && ! empty($item->paso_crecimiento_id_2)) {
                $query->whereNotExists(function ($sub) use ($item) {
                    $sub->select(DB::raw(1))
                        ->from('crecimiento_usuario as cu2_sub')
                        ->whereColumn('cu2_sub.user_id', 'users.id')
                        ->where('cu2_sub.paso_crecimiento_id', $item->paso_crecimiento_id_2);
                });
            } elseif (! empty($item->paso_crecimiento_id_2)) {
                $query->join('crecimiento_usuario as cu2', function ($join) use ($item) {
                    $join->on('users.id', '=', 'cu2.user_id')
                        ->where('cu2.paso_crecimiento_id', '=', $item->paso_crecimiento_id_2);
                });

                if (! empty($item->estado_paso_crecimiento_2)) {
                    $query->where('cu2.estado_id', $item->estado_paso_crecimiento_2);
                }

                if (! empty($item->cantidad_dias_dilacion)) {
                    $dias = (int) $item->cantidad_dias_dilacion;
                    $query->whereRaw("cu2.fecha >= cu1.fecha AND cu2.fecha <= cu1.fecha + INTERVAL '{$dias} days'");
                } elseif ($item->filtrar_fecha_paso_crecimiento_2) {
                    $query->whereBetween('cu2.fecha', [$fechaInicio, $fechaFin]);
                }
            }
        } elseif ($item->parametro_de_comparacion === 'fecha-creacion' && $item->fecha_creacion) {
            if ($item->no_existe_paso_crecimiento_id_2 && ! empty($item->paso_crecimiento_id_2)) {
                $query->whereNotExists(function ($sub) use ($item) {
                    $sub->select(DB::raw(1))
                        ->from('crecimiento_usuario as cu2_sub')
                        ->whereColumn('cu2_sub.user_id', 'users.id')
                        ->where('cu2_sub.paso_crecimiento_id', $item->paso_crecimiento_id_2);
                });
            } elseif (! empty($item->paso_crecimiento_id_2)) {
                $query->join('crecimiento_usuario as cu2', function ($join) use ($item) {
                    $join->on('users.id', '=', 'cu2.user_id')
                        ->where('cu2.paso_crecimiento_id', '=', $item->paso_crecimiento_id_2);
                });

                if (! empty($item->estado_paso_crecimiento_2)) {
                    $query->where('cu2.estado_id', $item->estado_paso_crecimiento_2);
                }

                if (! empty($item->cantidad_dias_dilacion)) {
                    $dias = (int) $item->cantidad_dias_dilacion;
                    $query->whereRaw("cu2.fecha >= DATE(users.created_at) AND cu2.fecha <= DATE(users.created_at) + INTERVAL '{$dias} days'");
                } elseif ($item->filtrar_fecha_paso_crecimiento_2) {
                    $query->whereBetween('cu2.fecha', [$fechaInicio, $fechaFin]);
                }
            }
        }

        // Filtro por Reportes de Baja / Alta
        if (! empty($item->tipo_baja_alta_id)) {
            $query->join('reporte_bajas_altas as rba', 'users.id', '=', 'rba.user_id')
                ->where('rba.tipo_baja_alta_id', $item->tipo_baja_alta_id);

            if ($item->estado_reporte_dado_baja !== null) {
                $query->where('rba.dado_baja', (bool) $item->estado_reporte_dado_baja);
            }

            if ($item->filtrar_fecha_reporte_baja_alta) {
                $query->whereBetween('rba.fecha', [$fechaInicio, $fechaFin]);
            }
        }

        // Filtro por Matrículas
        if (! empty($item->estado_matricula)) {
            $query->join('matriculas as m', 'users.id', '=', 'm.user_id')
                ->where('m.estado_pago_id', $item->estado_matricula);

            if ($item->filtro_fecha_matricula) {
                $query->whereBetween('m.fecha_matricula', [$fechaInicio, $fechaFin]);
            }
        }

        return $query;
    }

    /**
     * Aplica restricciones de fecha para cálculos semanales o mensuales.
     */
    protected function aplicarFiltrosTemporales($query, mixed $item, string $fechaIni, string $fechaFin): void
    {
        if ($item->filtrar_fecha_paso_crecimiento && ! empty($item->paso_crecimiento_id)) {
            $query->whereBetween('cu1.fecha', [$fechaIni, $fechaFin]);
        }

        if ($item->filtrar_fecha_reporte_baja_alta && ! empty($item->tipo_baja_alta_id)) {
            $query->whereBetween('rba.fecha', [$fechaIni, $fechaFin]);
        }

        if ($item->filtro_fecha_matricula && ! empty($item->estado_matricula)) {
            $query->whereBetween('m.fecha_matricula', [$fechaIni, $fechaFin]);
        }
    }

    /**
     * Resuelve en cascada las fórmulas aritméticas y totalizadores de ítems.
     */
    protected function resolverOperacionesCascada(array &$megaJson): void
    {
        foreach ($megaJson as $fila) {
            $iteracionesMaximas = 50; // Prevenir loop infinito en fórmulas circulares
            $iteracion = 0;

            do {
                $operacionesSinRealizar = 0;
                $iteracion++;

                foreach ($fila->data as $item) {
                    if ($item->tipoTD !== 'normal' && $item->estaCalculado === 'no') {
                        $operacionesSinRealizar++;

                        if ($item->tipoTD === 'totalizar') {
                            $idsItems = array_filter(explode(',', (string) $item->totalizar_items));
                            $todosDisponibles = true;
                            $sumaTotal = 0;

                            foreach ($idsItems as $idRef) {
                                $key = 'item'.trim($idRef);
                                if (isset($fila->data[$key]) && $fila->data[$key]->estaCalculado === 'si') {
                                    $sumaTotal += (float) $fila->data[$key]->total;
                                } else {
                                    $todosDisponibles = false;
                                    break;
                                }
                            }

                            if ($todosDisponibles) {
                                $item->total = $sumaTotal;
                                $item->estaCalculado = 'si';
                                $item->tds = "<td style='text-align: center; border: 1px solid #ddd; font-weight: bold;'>{$sumaTotal}</td>";
                                $operacionesSinRealizar--;
                            }
                        }

                        if ($item->tipoTD === 'operacion') {
                            $keyA = 'item'.$item->item_a;
                            $keyB = 'item'.$item->item_b;

                            if (
                                isset($fila->data[$keyA], $fila->data[$keyB]) &&
                                $fila->data[$keyA]->estaCalculado === 'si' &&
                                $fila->data[$keyB]->estaCalculado === 'si'
                            ) {
                                $valA = (float) $fila->data[$keyA]->total;
                                $valB = (float) $fila->data[$keyB]->total;
                                $resultado = 0;

                                switch ((int) $item->operacion) {
                                    case 1: // Suma
                                        $resultado = $valA + $valB;
                                        break;
                                    case 2: // Resta
                                        $resultado = $valA - $valB;
                                        break;
                                    case 3: // Multiplicación
                                        $resultado = $valA * $valB;
                                        break;
                                    case 4: // División protegida
                                        $resultado = ($valB != 0) ? round($valA / $valB, 2) : 0;
                                        break;
                                    case 5: // Promedio
                                        $resultado = round(($valA + $valB) / 2, 2);
                                        break;
                                }

                                $item->total = $resultado;
                                $item->estaCalculado = 'si';
                                $item->tds = "<td style='text-align: center; border: 1px solid #ddd; font-weight: bold;'>{$resultado}</td>";
                                $operacionesSinRealizar--;
                            }
                        }
                    }
                }
            } while ($operacionesSinRealizar > 0 && $iteracion < $iteracionesMaximas);
        }
    }

    /**
     * Construye el TBODY agrupado por bloques/sedes y calcula subtotales y total general.
     */
    protected function construirTbody(array $megaJson, mixed $bloques): string
    {
        $tablaBody = '<tbody>';
        $arrayTotalGeneral = [];

        if ($bloques->isNotEmpty()) {
            foreach ($bloques as $bloque) {
                $sedesBloque = $bloque->sedes_ids_array;
                if (empty($sedesBloque)) {
                    continue;
                }

                $arrayTotalBloque = [];
                $filasEnBloque = 0;

                foreach ($megaJson as $fila) {
                    if (in_array((int) $fila->sede_id, $sedesBloque, true)) {
                        $filasEnBloque++;
                        $pos = 0;
                        $tablaBody .= "<tr><td style='border: 1px solid #ddd; padding: 6px;'>".strtoupper($fila->grupo).'</td>';

                        foreach ($fila->data as $item) {
                            $tablaBody .= $item->tds;

                            if ($item->semanal === 'si' || $item->mensual === 'si') {
                                if (is_array($item->dataItem)) {
                                    foreach ($item->dataItem as $val) {
                                        $arrayTotalGeneral[$pos] = ($arrayTotalGeneral[$pos] ?? 0) + $val;
                                        $arrayTotalBloque[$pos] = ($arrayTotalBloque[$pos] ?? 0) + $val;
                                        $pos++;
                                    }
                                }
                            } else {
                                $arrayTotalGeneral[$pos] = ($arrayTotalGeneral[$pos] ?? 0) + $item->total;
                                $arrayTotalBloque[$pos] = ($arrayTotalBloque[$pos] ?? 0) + $item->total;
                                $pos++;
                            }
                        }

                        $tablaBody .= '</tr>';
                    }
                }

                if ($filasEnBloque > 0) {
                    // Fila de Subtotal del Bloque
                    $tablaBody .= "<tr style='background-color: #f2f2f2; font-weight: bold;'><td style='border: 1px solid #000; padding: 6px;'>TOTAL ".strtoupper($bloque->nombre).'</td>';
                    foreach ($arrayTotalBloque as $valBloque) {
                        $tablaBody .= "<td style='text-align: center; border: 1px solid #000; padding: 6px;'>{$valBloque}</td>";
                    }
                    $tablaBody .= '</tr>';
                }
            }
        } else {
            foreach ($megaJson as $fila) {
                $pos = 0;
                $tablaBody .= "<tr><td style='border: 1px solid #ddd; padding: 6px;'>".strtoupper($fila->grupo).'</td>';

                foreach ($fila->data as $item) {
                    $tablaBody .= $item->tds;

                    if ($item->semanal === 'si' || $item->mensual === 'si') {
                        if (is_array($item->dataItem)) {
                            foreach ($item->dataItem as $val) {
                                $arrayTotalGeneral[$pos] = ($arrayTotalGeneral[$pos] ?? 0) + $val;
                                $pos++;
                            }
                        }
                    } else {
                        $arrayTotalGeneral[$pos] = ($arrayTotalGeneral[$pos] ?? 0) + $item->total;
                        $pos++;
                    }
                }

                $tablaBody .= '</tr>';
            }
        }

        // Fila de Gran Total
        $tablaBody .= "<tr style='background-color: #d9ead3; font-weight: bold; border-top: 2px solid #000;'><td style='border: 1px solid #000; padding: 8px;'>GRAN TOTAL</td>";
        foreach ($arrayTotalGeneral as $valTotal) {
            $tablaBody .= "<td style='text-align: center; border: 1px solid #000; padding: 8px;'>{$valTotal}</td>";
        }
        $tablaBody .= '</tr>';

        $tablaBody .= '</tbody>';

        return $tablaBody;
    }

    /**
     * Construye los 4 niveles de cabecera HTML del Megainforme.
     */
    protected function construirThead(
        InformePersonalizado $informe,
        string $thSoloMeses,
        int $colspanPorMeses,
        string $thSemanas,
        int $colspanPorSemanas
    ): array {
        $thHead1 = "<th style='background-color: #2c3e50; color: #fff; border: 1px solid #000;'></th>";
        $thHead2 = "<th style='background-color: #34495e; color: #fff; border: 1px solid #000;'></th>";
        $thHead3 = "<th style='background-color: #4b6584; color: #fff; border: 1px solid #000;'>GRUPO / RED</th>";
        $thHead4 = "<th style='background-color: #778ca3; color: #fff; border: 1px solid #000;'></th>";

        $arrayPasosItems = [];

        foreach ($informe->secciones as $seccion) {
            $colspanSeccion = 0;

            foreach ($seccion->subsecciones as $subseccion) {
                $colspanSubseccion = 0;

                foreach ($subseccion->items as $item) {
                    if (! empty($item->paso_crecimiento_id)) {
                        $arrayPasosItems[] = $item->paso_crecimiento_id;
                    }
                    if (! empty($item->paso_crecimiento_id_2)) {
                        $arrayPasosItems[] = $item->paso_crecimiento_id_2;
                    }

                    if ($item->visualizar_por_mes) {
                        $colspanSeccion += $colspanPorMeses;
                        $colspanSubseccion += $colspanPorMeses;
                        $thHead3 .= "<th style='text-align: center; border: 1px solid #000;' colspan='{$colspanPorMeses}'>".strtoupper($item->nombre).'</th>';
                        $thHead4 .= $thSoloMeses;
                    } elseif ($item->visualizar_por_semanas) {
                        $colspanSeccion += $colspanPorSemanas;
                        $colspanSubseccion += $colspanPorSemanas;
                        $thHead3 .= "<th style='text-align: center; border: 1px solid #000;' colspan='{$colspanPorSemanas}'>".strtoupper($item->nombre).'</th>';
                        $thHead4 .= $thSemanas;
                    } else {
                        $colspanSeccion++;
                        $colspanSubseccion++;
                        $thHead3 .= "<th style='text-align: center; border: 1px solid #000;'>".strtoupper($item->nombre).'</th>';
                        $thHead4 .= "<th style='border: 1px solid #000;'></th>";
                    }
                }

                $thHead2 .= "<th style='text-align: center; background-color: #4b6584; color: #fff; border: 1px solid #000;' colspan='{$colspanSubseccion}'>".strtoupper($subseccion->nombre).'</th>';
            }

            $thHead1 .= "<th style='text-align: center; background-color: #2c3e50; color: #fff; border: 1px solid #000;' colspan='{$colspanSeccion}'>".strtoupper($seccion->nombre).'</th>';
        }

        $trHead1 = "<tr style='font-weight: bold;'>{$thHead1}</tr>";
        $trHead2 = "<tr style='font-weight: bold;'>{$thHead2}</tr>";
        $trHead3 = "<tr style='font-weight: bold;'>{$thHead3}</tr>";
        $trHead4 = "<tr style='font-weight: bold;'>{$thHead4}</tr>";

        $tablaHead = "<thead>{$trHead1}{$trHead2}{$trHead3}{$trHead4}</thead>";

        return [$tablaHead, array_unique($arrayPasosItems)];
    }

    /**
     * Construye encabezados HTML de meses.
     */
    protected function construirHeadersMeses(int $mesIni, int $mesFin): array
    {
        $thSoloMeses = '';
        $colspanPorMeses = 0;

        for ($i = $mesIni; $i <= $mesFin; $i++) {
            $colspanPorMeses++;
            $nombreMes = $this->meses[$i - 1] ?? '';
            $thSoloMeses .= "<th style='text-align: center; border: 1px solid #000; font-size: 11px;'><b>{$nombreMes}</b></th>";
        }

        return [$thSoloMeses, $colspanPorMeses];
    }

    /**
     * Construye encabezados HTML de semanas y rangos de fechas.
     */
    protected function construirHeadersSemanas(int $semanaIni, int $semanaFin, int $anio): array
    {
        $thSemanas = '';
        $thMeses = '';
        $colspanPorSemanas = 0;
        $arraySemanasDatos = [];

        for ($i = $semanaIni; $i <= $semanaFin; $i++) {
            $primer = (new DateTime)->modify("{$anio}W".sprintf('%02d', $i));
            $ultimo = (new DateTime)->modify("{$anio}W".sprintf('%02d', $i).' +6 days');

            $mesPri = (int) $primer->format('m');
            $mesUlt = (int) $ultimo->format('m');

            $rangoTexto = strtoupper($primer->format('d').' '.($this->meses[$mesPri - 1] ?? '').' - '.$ultimo->format('d').' '.($this->meses[$mesUlt - 1] ?? ''));

            $thSemanas .= "<th style='text-align: center; border: 1px solid #000; font-size: 10px;'><b>Sem {$i}<br>{$rangoTexto}</b></th>";
            $colspanPorSemanas++;

            $arraySemanasDatos[] = [
                'semana' => $i,
                'primerDia' => $primer->format('Y-m-d'),
                'ultimoDia' => $ultimo->format('Y-m-d'),
            ];
        }

        return [$thSemanas, $thMeses, $colspanPorSemanas, $arraySemanasDatos];
    }

    /**
     * Determina las fechas de inicio y fin para el periodo indicado.
     */
    protected function calcularRangoFechas(string $rango, int $anio, ?string $semana = null): array
    {
        if ($rango === 'semana' && ! empty($semana)) {
            $fechaInicio = Carbon::parse($semana)->startOfWeek()->format('Y-m-d');
            $fechaFin = Carbon::parse($semana)->endOfWeek()->format('Y-m-d');

            return [$fechaInicio, $fechaFin];
        }

        $mesMap = [
            '1m' => [1, 1], '2m' => [2, 2], '3m' => [3, 3], '4m' => [4, 4],
            '5m' => [5, 5], '6m' => [6, 6], '7m' => [7, 7], '8m' => [8, 8],
            '9m' => [9, 9], '10m' => [10, 10], '11m' => [11, 11], '12m' => [12, 12],
            '1t' => [1, 3], '2t' => [4, 6], '3t' => [7, 9], '4t' => [10, 12],
            '1s' => [1, 6], '2s' => [7, 12], 'anio' => [1, 12],
        ];

        if (isset($mesMap[$rango])) {
            [$mesInicio, $mesFin] = $mesMap[$rango];
            $fechaInicio = Carbon::create($anio, $mesInicio, 1)->startOfMonth()->format('Y-m-d');
            $fechaFin = Carbon::create($anio, $mesFin, 1)->endOfMonth()->format('Y-m-d');

            return [$fechaInicio, $fechaFin];
        }

        // Default al primer trimestre
        return [
            Carbon::create($anio, 1, 1)->startOfMonth()->format('Y-m-d'),
            Carbon::create($anio, 3, 1)->endOfMonth()->format('Y-m-d'),
        ];
    }

    /**
     * Calcula semana inicial y final ajustando límites del año.
     */
    protected function calcularSemanas(string $fechaInicio, string $fechaFin, int $anio): array
    {
        $semanaIni = (int) date('W', strtotime($fechaInicio));
        $semanaFin = (int) date('W', strtotime($fechaFin));

        if ($semanaIni >= 52) {
            $semanaIni = 1;
        }

        if ($semanaFin < $semanaIni) {
            $semanaFin = 52;
        }

        // Ajustar a semana corriente si es el año en curso
        $semanaActual = (int) date('W');
        if ((int) date('Y') === $anio) {
            if ($semanaActual >= $semanaIni && $semanaActual <= $semanaFin) {
                $semanaFin = $semanaActual;
            }
        }

        return [$semanaIni, $semanaFin];
    }
}
