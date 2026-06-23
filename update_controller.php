<?php

$content = file_get_contents('app/Http/Controllers/InformesPersonalizadosController.php');

$newFunction = <<<'FUNC'
    private function construirTablaObreros(
        \App\Models\Grupo $grupoSeleccionado,
        array $arrayTiposGrupos,
        string $rango,
        int $anio,
        int $semanaIni,
        int $semanaFin,
        array $arraySemanasDeshabilitadas,
        $camposInfoPrincipalSeleccionados,
        $camposExtraGrupoSeleccionados,
        int $cantidadCamposExtraGrupo,
        int $cantidadCamposInfoSeleccionados,
        array $arrayNombreCampos,
        bool $incluirEncargados,
        bool $incluirAsistentes,
    ): string {
        $gruposIds = $grupoSeleccionado->gruposMinisterio()
            ->where('grupos.dado_baja', false)
            ->whereIn('tipo_grupo_id', $arrayTiposGrupos)
            ->pluck('id')
            ->toArray();

        if (in_array($grupoSeleccionado->tipoGrupo->id, $arrayTiposGrupos)) {
            $gruposIds[] = $grupoSeleccionado->id;
        }

        $grupos = Grupo::whereIn('id', $gruposIds)
            ->with(['encargados'])
            ->select('id', 'codigo', 'nombre', 'tipo_grupo_id')
            ->orderBy('id', 'asc')
            ->get();

        $tablaBody = '<table>';

        $coloresGrupo = ['#d9ead3', '#cfe2f3', '#fce5cd', '#e2d0f9', '#d0e0e3'];
        $indexColor = 0;

        foreach ($grupos as $grupo) {
            $color = $coloresGrupo[$indexColor % count($coloresGrupo)];
            $indexColor++;

            // PRE-CALCULAR LOS DATOS DE LAS SEMANAS
            $semanasHtml = '';
            $arraySemanasDatos = [];
            
            for ($i = $semanaIni; $i <= $semanaFin; $i++) {
                if (in_array($i, $arraySemanasDeshabilitadas)) {
                    continue;
                }

                $primer = new \DateTime;
                $ultimo = new \DateTime;
                $primer->modify("{$anio}W".sprintf('%02d', $i));
                $ultimo->modify("{$anio}W".sprintf('%02d', $i).' +6 days');

                $semanasHtml .= "<td style='background-color: {$color}; border: 1px solid #000; text-align: center; font-weight: bold;'>ASISTENCIA SEMANA {$i}</td>";
                
                $arraySemanasDatos[] = [
                    'semana' => $i,
                    'primerDia' => $primer->format('Y-m-d'),
                    'ultimoDia' => $ultimo->format('Y-m-d'),
                ];
            }

            // ─── BLOQUE 1: INFO CONDENSADA DEL GRUPO ───
            $tablaBody .= '<tr>';
            $tablaBody .= "<td colspan='10' style='background-color: {$color}; font-weight: bold;'>BLOQUE 1: INFORMACION CONDENSADA GRUPO - {$grupo->nombre}</td>";
            $tablaBody .= '</tr>';

            $tablaBody .= '<tr>';
            $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>NOMBRE DEL GRUPO</td>";
            $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>NOMBRE ENCARGADO DEL GRUPO</td>";
            $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>TIPO GRUPO</td>";
            $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>TOTAL ASISTENCIAS</td>";
            $tablaBody .= $semanasHtml;
            $tablaBody .= '</tr>';

            $tablaBody .= '<tr>';
            $tablaBody .= "<td>{$grupo->nombre}</td>";
            
            $nombresEncargados = $grupo->encargados->pluck('primer_nombre')->join(', ');
            $tablaBody .= "<td>{$nombresEncargados}</td>";
            
            $tipoGrupoNombre = $grupo->tipoGrupo->nombre ?? 'Sin Asignar';
            $tablaBody .= "<td>{$tipoGrupoNombre}</td>";

            // Calcular totales semanales del grupo
            $totalesSemanaHtml = '';
            $totalAsistenciasGrupo = 0;
            
            foreach ($arraySemanasDatos as $datoSemana) {
                $reportesSemana = ReporteGrupo::where('fecha', '>=', $datoSemana['primerDia'])
                    ->where('fecha', '<=', $datoSemana['ultimoDia'])
                    ->where('grupo_id', $grupo->id)
                    ->get();
                
                $sumaSemana = $reportesSemana->sum('cantidad_asistencias');
                
                $totalAsistenciasGrupo += $sumaSemana;
                $totalesSemanaHtml .= "<td>{$sumaSemana}</td>";
            }

            $tablaBody .= "<td>{$totalAsistenciasGrupo}</td>";
            $tablaBody .= $totalesSemanaHtml;
            $tablaBody .= '</tr>';
            $tablaBody .= '<tr></tr>';


            // Closure para generar las tablas de encargados y asistentes
            $generarSubTabla = function ($titulo, $idsRelacion) use (
                &$tablaBody, $color, $camposInfoPrincipalSeleccionados, $camposExtraGrupoSeleccionados,
                $semanasHtml, $arrayNombreCampos, $arraySemanasDatos, $grupo
            ) {
                if (empty($idsRelacion)) return;

                $usuarios = \App\Models\User::whereIn('id', $idsRelacion)->get();

                if ($usuarios->isEmpty()) return;

                $tablaBody .= "<tr><td colspan='10' style='background-color: {$color}; font-weight: bold;'>{$titulo}</td></tr>";
                $tablaBody .= '<tr>';
                $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>NOMBRE ASISTENTE</td>";
                
                foreach ($camposInfoPrincipalSeleccionados as $campo) {
                    $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>{$campo->nombre_campo_informe}</td>";
                }
                foreach ($camposExtraGrupoSeleccionados as $campoExtra) {
                    $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>{$campoExtra->nombre}</td>";
                }
                
                $tablaBody .= $semanasHtml;
                $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>TOTAL ASISTENCIAS</td>";
                $tablaBody .= '</tr>';

                foreach ($usuarios as $usuario) {
                    $asistenciasAsistente = 0;
                    $celdasAsistenciaHtml = '';

                    foreach ($arraySemanasDatos as $datoSemana) {
                        $reportesSemana = ReporteGrupo::where('fecha', '>=', $datoSemana['primerDia'])
                            ->where('fecha', '<=', $datoSemana['ultimoDia'])
                            ->where('grupo_id', $grupo->id)
                            ->select('id', 'informacion_encargado_grupo')
                            ->get();

                        $asistio = 'NO';

                        if ($reportesSemana->count() > 0) {
                            foreach ($reportesSemana as $reporte) {
                                // Verificar si es encargado y asistió
                                $infoEncargados = is_string($reporte->informacion_encargado_grupo) 
                                    ? json_decode($reporte->informacion_encargado_grupo, true) 
                                    : $reporte->informacion_encargado_grupo;
                                $infoEncargados = $infoEncargados ?: [];

                                foreach ($infoEncargados as $infoEncargado) {
                                    $encargadoId = is_array($infoEncargado) ? ($infoEncargado['id'] ?? null) : ($infoEncargado->id ?? null);
                                    $encargadoAsistio = is_array($infoEncargado) ? ($infoEncargado['asistio'] ?? false) : ($infoEncargado->asistio ?? false);

                                    if ($encargadoId == $usuario->id && $encargadoAsistio == true) {
                                        $asistio = 'SI';
                                        $asistenciasAsistente++;
                                        break 2;
                                    }
                                }

                                // Verificar si es asistente y asistió
                                $asistioComoAsistente = $reporte->usuarios()
                                    ->wherePivot('asistio', true)
                                    ->where('users.id', $usuario->id)
                                    ->exists();

                                if ($asistioComoAsistente) {
                                    $asistio = 'SI';
                                    $asistenciasAsistente++;
                                    break;
                                }
                            }
                        }

                        $celdasAsistenciaHtml .= "<td>{$asistio}</td>";
                    }

                    $tablaBody .= '<tr>';
                    $tablaBody .= "<td>{$usuario->primer_nombre} {$usuario->primer_apellido}</td>";

                    // Campos de info principal
                    foreach ($camposInfoPrincipalSeleccionados as $campo) {
                        if ($campo->nombre_campo_bd == 'tipo_usuario_id') {
                            $tipoUsuario = \App\Models\TipoUsuario::find($usuario->tipo_usuario_id);
                            $tablaBody .= '<td>'.($tipoUsuario?->nombre ?? 'Sin Asignar').'</td>';
                        } elseif ($campo->nombre_campo_bd == 'grupo_id') {
                            $encargadoPrincipal = $grupo->encargados()->first();
                            if ($encargadoPrincipal) {
                                $gruposDirectos = $encargadoPrincipal->gruposEncargados()->get();
                                $tablaBody .= '<td>'.$gruposDirectos->pluck('nombre')->join(' / ').'</td>';
                            } else {
                                $tablaBody .= '<td>Sin Asignar</td>';
                            }
                        } else {
                            $valor = $usuario->{$campo->nombre_campo_bd} ?? 'Sin Asignar';
                            $tablaBody .= "<td>{$valor}</td>";
                        }
                    }

                    // Campos extra de grupo
                    foreach ($camposExtraGrupoSeleccionados as $campo) {
                        $campoExtraGrupo = $grupo->camposExtras()
                            ->where('campos_extra_grupo.id', $campo->id)
                            ->first();

                        $tablaBody .= $this->renderCampoExtra($campo, $campoExtraGrupo);
                    }

                    $tablaBody .= $celdasAsistenciaHtml;
                    // Total asistencias de este usuario
                    $tablaBody .= "<td>{$asistenciasAsistente}</td>";
                    
                    $tablaBody .= '</tr>';
                }
            };

            // ─── BLOQUE 2: ENCARGADOS ───
            if ($incluirEncargados) {
                $idsEncargados = $grupo->encargados()->pluck('users.id')->toArray();
                $generarSubTabla('BLOQUE 2: DETALLE ENCARGADOS', $idsEncargados);
            }

            // ─── BLOQUE 3: ASISTENTES ───
            if ($incluirAsistentes) {
                $idsAsistentes = $grupo->asistentes()->pluck('users.id')->toArray();
                $generarSubTabla('BLOQUE 3: DETALLE ASISTENTES', $idsAsistentes);
            }

            $tablaBody .= '<tr></tr><tr></tr>';
        }

        $tablaBody .= '</table>';

        return $tablaBody;
    }
FUNC;

$pattern = '/\s+private function construirTablaObreros\(.*?return \$tablaHeader\.\$tablaBody;\n    \}/s';
$content = preg_replace($pattern, "\n".$newFunction, $content, 1);

file_put_contents('app/Http/Controllers/InformesPersonalizadosController.php', $content);
