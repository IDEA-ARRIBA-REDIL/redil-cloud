<?php

namespace App\Services;

use App\Models\Periodo;
use App\Models\MateriaPeriodo;
use App\Models\Calificaciones;
use App\Models\MateriaAprobadaUsuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Misión Única: Procesa la finalización de una MATERIA INDIVIDUAL dentro de un periodo.
 */
class ServicioValidacionMateriaPeriodo
{
    public function procesarLoteDeAlumnosPorMateria(MateriaPeriodo $materiaPeriodo, int $pagina, int $porPagina): int
    {
        $idsAlumnosDelLote = DB::table('matriculas as mat')
            ->join('horarios_materia_periodo as hmp', 'mat.horario_materia_periodo_id', '=', 'hmp.id')
            ->where('hmp.materia_periodo_id', $materiaPeriodo->id)
            ->distinct()->orderBy('mat.user_id')
            ->offset(($pagina - 1) * $porPagina)->limit($porPagina)
            ->pluck('mat.user_id');

        if ($idsAlumnosDelLote->isEmpty()) {
            return 0;
        }

        $resultados = $this->obtenerResultadosAcademicos($materiaPeriodo, $idsAlumnosDelLote);
        $datosParaGuardar = $this->prepararDatosParaGuardar($materiaPeriodo->periodo, $resultados);
        $this->persistirResultados($datosParaGuardar, $materiaPeriodo->id);

        return $idsAlumnosDelLote->count();
    }

    private function prepararDatosParaGuardar(Periodo $periodo, array $resultadosAcademicos): array
    {
        // Este método es idéntico al del otro servicio
        if (empty($resultadosAcademicos)) return [];
        $notaMinima = Calificaciones::where('sistema_calificacion_id', $periodo->sistema_calificaciones_id)->where('aprobado', true)->min('nota_minima');
        if (is_null($notaMinima)) throw new \Exception("No se encontró nota mínima de aprobación para el sistema de calificación ID: {$periodo->sistema_calificaciones_id}");
        $datosParaGuardar = [];
        foreach ($resultadosAcademicos as $resultado) {
            $estadoFinal = $this->determinarEstadoFinal($resultado, (float) $notaMinima);
            $datosParaGuardar[] = [
                'user_id' => $resultado->user_id,
                'materia_id' => $resultado->materia_id,
                'materia_periodo_id' => $resultado->materia_periodo_id,
                'periodo_id' => $periodo->id,
                'nota_final' => $resultado->nota_final_calculada,
                'total_asistencias' => $resultado->total_asistencias,
                'aprobado' => $estadoFinal['aprobado'],
                'motivo_reprobacion' => $estadoFinal['motivo'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        return $datosParaGuardar;
    }

    private function obtenerResultadosAcademicos(MateriaPeriodo $materiaPeriodo, Collection $idsAlumnos): array
    {
        $placeholders = implode(',', array_fill(0, count($idsAlumnos), '?'));
        // El binding ahora incluye el materia_periodo_id
        $bindings = array_merge([$materiaPeriodo->periodo_id, $materiaPeriodo->id], $idsAlumnos->toArray());

        $sql = "
            SELECT
                mat.user_id, mp.id AS materia_periodo_id, mp.materia_id,
                m.habilitar_calificaciones, m.habilitar_asistencias, m.asistencias_minimas,
                COALESCE(SUM(ari.nota_obtenida * (icp.porcentaje / 100.0) * (cp.porcentaje / 100.0)), 0) AS nota_final_calculada,
                (
                    SELECT COUNT(*) FROM reportes_asistencia_alumnos AS raa
                    JOIN reportes_asistencia_clase AS rac ON raa.reporte_asistencia_clase_id = rac.id
                    JOIN horarios_materia_periodo AS hmp_asistencia ON rac.horario_materia_periodo_id = hmp_asistencia.id
                    WHERE raa.user_id = mat.user_id AND hmp_asistencia.materia_periodo_id = mp.id AND raa.asistio = TRUE
                ) AS total_asistencias
            FROM matriculas AS mat
            JOIN horarios_materia_periodo AS hmp ON mat.horario_materia_periodo_id = hmp.id
            JOIN materia_periodo AS mp ON hmp.materia_periodo_id = mp.id
            JOIN materias AS m ON mp.materia_id = m.id
            LEFT JOIN item_corte_materia_periodo AS icp ON icp.horario_materia_periodo_id = hmp.id
            LEFT JOIN alumno_respuesta_items AS ari ON ari.item_corte_materia_periodo_id = icp.id AND ari.user_id = mat.user_id
            LEFT JOIN cortes_periodo AS cp ON icp.corte_periodo_id = cp.id
            WHERE mat.periodo_id = ? AND mp.id = ? AND mat.user_id IN ({$placeholders})
            GROUP BY mat.user_id, mp.id, mp.materia_id, m.habilitar_calificaciones, m.habilitar_asistencias, m.asistencias_minimas;
        ";

        return DB::select($sql, $bindings);
    }

    private function determinarEstadoFinal(object $resultado, float $notaMinima): array
    {
        // Por defecto, asumimos que el alumno aprueba ambas condiciones.
        $aproboPorNota = true;
        $aproboPorAsistencia = true;
        $motivos = [];

        // --- Validación de Nota (Condicional) ---
        // Solo se ejecuta si la materia tiene las calificaciones habilitadas.
        if ($resultado->habilitar_calificaciones) {
            if ($resultado->nota_final_calculada < $notaMinima) {
                $aproboPorNota = false;
                $motivos[] = 'NOTA_INSUFICIENTE';
            }
        }

        // --- Validación de Asistencia (Condicional) ---
        // Solo se ejecuta si la materia tiene las asistencias habilitadas.
        if ($resultado->habilitar_asistencias) {
            if ($resultado->total_asistencias < $resultado->asistencias_minimas) {
                $aproboPorAsistencia = false;
                $motivos[] = 'ASISTENCIA_INSUFICIENTE';
            }
        }

        // El estado final 'aprobado' solo es verdadero si ambas condiciones
        // (las que apliquen) se cumplieron.
        return [
            'aprobado' => $aproboPorNota && $aproboPorAsistencia,
            'motivo' => empty($motivos) ? null : implode(', ', $motivos),
        ];
    }

    private function persistirResultados(array $datosCalculados, int $materiaPeriodoId): void
    {
        // Si no hay datos calculados para este lote, no hacemos nada.
        if (empty($datosCalculados)) {
            return;
        }

        Log::info("ServicioMateria: Iniciando persistencia para la MateriaPeriodo ID {$materiaPeriodoId}.");

        // --- PASO 1: OBTENER REGISTROS EXISTENTES ---
        // Hacemos UNA sola consulta a la BD para traer todos los registros que ya existen
        // para esta materia específica y los guardamos en un mapa para una búsqueda rápida.
        $registrosExistentes = MateriaAprobadaUsuario::where('materia_periodo_id', $materiaPeriodoId)
            ->get()
            ->keyBy(fn($item) => "{$item->user_id}-{$item->materia_periodo_id}");

        Log::info("Se encontraron " . $registrosExistentes->count() . " registros existentes para esta materia.");

        // --- PASO 2: CLASIFICAR DATOS ---
        // Preparamos dos "cubetas": una para los registros nuevos y otra para los que necesitan actualizarse.
        $paraInsertar = [];
        $paraActualizar = [];

        // Recorremos los datos recién calculados.
        foreach ($datosCalculados as $dato) {
            $clave = $dato['user_id'] . '-' . $dato['materia_periodo_id'];

            // Comprobamos si el registro ya existe en nuestro "mapa".
            if (isset($registrosExistentes[$clave])) {
                $registroExistente = $registrosExistentes[$clave];

                // Comparamos si la nota, las asistencias o el estado de aprobación han cambiado.
                if (
                    abs($registroExistente->nota_final - $dato['nota_final']) > 0.001 ||
                    $registroExistente->total_asistencias != $dato['total_asistencias'] ||
                    $registroExistente->aprobado != $dato['aprobado']
                ) {
                    // Si algo cambió, lo añadimos a la lista de registros a actualizar.
                    $paraActualizar[] = $dato;
                }
                // Si no hay cambios, simplemente lo ignoramos y no hacemos nada con él.
            } else {
                // Si no existe en nuestro mapa, es un registro completamente nuevo.
                $paraInsertar[] = $dato;
            }
        }

        Log::info("Análisis completado: " . count($paraInsertar) . " para insertar, " . count($paraActualizar) . " para actualizar.");

        // --- PASO 3: EJECUTAR OPERACIONES EN LA BASE DE DATOS ---

        // Insertamos todos los registros nuevos en una sola operación masiva para máxima eficiencia.
        if (!empty($paraInsertar)) {
            foreach (array_chunk($paraInsertar, 500) as $chunk) {
                MateriaAprobadaUsuario::insert($chunk);
            }
            Log::info("Se insertaron " . count($paraInsertar) . " nuevos registros.");
        }

        // Actualizamos los registros que cambiaron, uno por uno.
        // Aunque es un bucle, solo se ejecuta para la pequeña cantidad de registros que REALMENTE cambiaron.
        if (!empty($paraActualizar)) {
            foreach ($paraActualizar as $datoActualizar) {
                MateriaAprobadaUsuario::where('user_id', $datoActualizar['user_id'])
                    ->where('materia_periodo_id', $datoActualizar['materia_periodo_id'])
                    ->update([
                        'nota_final' => $datoActualizar['nota_final'],
                        'total_asistencias' => $datoActualizar['total_asistencias'],
                        'aprobado' => $datoActualizar['aprobado'],
                        'motivo_reprobacion' => $datoActualizar['motivo_reprobacion'],
                        'updated_at' => now(),
                    ]);
            }
            Log::info("Se actualizaron " . count($paraActualizar) . " registros existentes.");
        }
    }
}
