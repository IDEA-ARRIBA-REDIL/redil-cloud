<?php

namespace App\Services;

use App\Models\Calificaciones;
use App\Models\MateriaAprobadaUsuario;
use App\Models\Periodo;
use App\Traits\AplicaEfectosAprobacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio encargado de la lógica de negocio para finalizar un periodo académico.
 * Funciona procesando alumnos en lotes para ser más eficiente y evitar timeouts.
 */
class ServicioValidacionPeriodo
{
    use AplicaEfectosAprobacion;

    /**
     * Orquesta el procesamiento de un único LOTE de alumnos para un periodo.
     * Este es el método principal que será llamado por el FinalizarPeriodoJob.
     *
     * @param  Periodo  $periodo  El periodo que se está procesando.
     * @param  int  $pagina  El número del lote actual (ej: 1, 2, 3...).
     * @param  int  $porPagina  El tamaño de cada lote (ej: 200 alumnos por lote).
     * @return int El número de alumnos que fueron procesados en este lote.
     */
    public function procesarLoteDeAlumnos(Periodo $periodo, int $pagina, int $porPagina): int
    {
        // --- PASO 1: OBTENER LOS ALUMNOS DE ESTE LOTE ---
        Log::info("Servicio: Buscando alumnos para el lote {$pagina} (hasta {$porPagina} alumnos).");

        // Se obtiene una lista única de IDs de usuarios matriculados en el periodo, paginada.
        $idsAlumnosDelLote = DB::table('matriculas')
            ->where('periodo_id', $periodo->id)
            ->distinct()
            ->orderBy('user_id')
            ->offset(($pagina - 1) * $porPagina) // Calcula el punto de inicio del lote
            ->limit($porPagina) // Limita el número de resultados al tamaño del lote
            ->pluck('user_id');

        // Si la consulta no devuelve IDs, significa que ya no hay más alumnos por procesar.
        if ($idsAlumnosDelLote->isEmpty()) {
            Log::info("Servicio: No se encontraron más alumnos para el lote {$pagina}. Finalizando.");

            return 0; // Se devuelve 0 para indicarle al Job que debe detenerse.
        }
        Log::info('Servicio: Se procesarán '.$idsAlumnosDelLote->count().' alumnos en este lote.');

        // --- PASO 2: OBTENER LOS DATOS ACADÉMICOS SOLO PARA ESE LOTE DE ALUMNOS ---
        $resultadosAcademicos = $this->obtenerResultadosAcademicos($periodo, $idsAlumnosDelLote);
        Log::info('Servicio: La consulta SQL para el lote se completó. Se encontraron '.count($resultadosAcademicos).' registros de alumno/materia.');

        if (empty($resultadosAcademicos)) {
            Log::warning('No se encontraron datos académicos para los alumnos de este lote.');

            return $idsAlumnosDelLote->count(); // Devolvemos el conteo para que el job sepa que debe continuar con el siguiente lote.
        }

        // --- PASO 3: APLICAR LAS REGLAS DE NEGOCIO (igual que antes) ---
        $notaMinimaAprobacion = Calificaciones::where('sistema_calificacion_id', $periodo->sistema_calificaciones_id)
            ->where('aprobado', true)
            ->min('nota_minima');

        if (is_null($notaMinimaAprobacion)) {
            throw new \Exception("No se pudo encontrar la nota mínima de aprobación para el sistema de calificación ID: {$periodo->sistema_calificaciones_id}");
        }

        $datosParaUpsert = [];
        foreach ($resultadosAcademicos as $resultado) {
            $estadoFinal = $this->determinarEstadoFinal($resultado, (float) $notaMinimaAprobacion);

            $datosParaUpsert[] = [
                'user_id' => $resultado->user_id,
                'materia_id' => $resultado->materia_id,
                'materia_periodo_id' => $resultado->materia_periodo_id,
                'periodo_id' => $periodo->id,
                'nota_final' => $resultado->nota_final_calculada,
                'creditos_aprobados' => $estadoFinal['aprobado'] ? $resultado->creditos : null,
                'total_asistencias' => $resultado->total_asistencias,
                'aprobado' => $estadoFinal['aprobado'],
                'motivo_reprobacion' => $estadoFinal['motivo'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // --- PASO 4: PERSISTIR LOS RESULTADOS DE ESTE LOTE (igual que antes) ---
        $this->persistirResultados($datosParaUpsert);

        // --- PASO 4.1: APLICAR EFECTOS COLATERALES DE APROBACIÓN (NUEVO) ---
        // Actualizar tareas de consolidación y pasos de crecimiento para los aprobados
        $this->aplicarEfectosCulminacion($datosParaUpsert);

        // --- PASO 5: DEVOLVER LA CANTIDAD DE ALUMNOS PROCESADOS ---
        // El Job usará este número para saber si debe continuar con el siguiente lote.
        return $idsAlumnosDelLote->count();
    }

    /**
     * Ejecuta la consulta SQL optimizada para un lote específico de IDs de alumnos.
     *
     * @param  Collection  $idsAlumnos  Colección de IDs de los alumnos a consultar.
     */
    private function obtenerResultadosAcademicos(Periodo $periodo, Collection $idsAlumnos): array
    {
        // Transforma la colección de IDs en un array plano para usar en la consulta.
        $idsArray = $idsAlumnos->toArray();
        // Crea los placeholders (?, ?, ?) para la cláusula WHERE IN de SQL.
        $placeholders = implode(',', array_fill(0, count($idsArray), '?'));

        $sql = "
            SELECT
                mat.user_id, mat.bloqueado AS matricula_bloqueada,
                mp.id AS materia_periodo_id, mp.materia_id, m.creditos,
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
            WHERE mat.periodo_id = ? AND mat.user_id IN ({$placeholders}) -- <-- AQUÍ FILTRAMOS POR EL LOTE DE ALUMNOS
            GROUP BY
                mat.user_id, mat.bloqueado, mp.id, mp.materia_id, m.creditos, m.habilitar_calificaciones, m.habilitar_asistencias, m.asistencias_minimas;
        ";

        // Los "bindings" son los valores que reemplazarán a los '?'.
        // Es importante unir el ID del periodo con el array de IDs de alumnos.
        $bindings = array_merge([$periodo->id], $idsArray);

        return DB::select($sql, $bindings);
    }

    /**
     * Aplica las reglas de negocio para determinar si un alumno aprueba.
     */
    private function determinarEstadoFinal(object $resultado, float $notaMinima): array
    {
        if ((bool) $resultado->matricula_bloqueada) {
            return [
                'aprobado' => false,
                'motivo' => 'MATRICULA_BLOQUEADA',
            ];
        }

        $aproboPorNota = true;
        $aproboPorAsistencia = true;
        $motivos = [];
        if ($resultado->habilitar_calificaciones && $resultado->nota_final_calculada < $notaMinima) {
            $aproboPorNota = false;
            $motivos[] = 'NOTA_INSUFICIENTE';
        }
        if ($resultado->habilitar_asistencias && $resultado->total_asistencias < $resultado->asistencias_minimas) {
            $aproboPorAsistencia = false;
            $motivos[] = 'ASISTENCIA_INSUFICIENTE';
        }

        return ['aprobado' => $aproboPorNota && $aproboPorAsistencia, 'motivo' => empty($motivos) ? null : implode(', ', $motivos)];
    }

    /**
     * Guarda los resultados finales en la BD con validación manual para evitar operaciones innecesarias.
     *
     * @param  array  $datosCalculados  El conjunto completo de resultados calculados.
     */
    private function persistirResultados(array $datosCalculados): void
    {
        if (empty($datosCalculados)) {
            return;
        }

        Log::info('Servicio: Iniciando persistencia manual para '.count($datosCalculados).' registros.');

        // --- PASO 1: Obtener todos los registros que YA existen para este periodo ---
        // Hacemos UNA sola consulta para traer todos los registros existentes a memoria.
        $periodoId = $datosCalculados[0]['periodo_id'];
        $registrosExistentes = MateriaAprobadaUsuario::where('periodo_id', $periodoId)
            ->get()
            // Creamos un "mapa" para búsquedas súper rápidas, usando una clave compuesta.
            ->keyBy(function ($item) {
                return $item->user_id.'-'.$item->materia_periodo_id;
            });

        Log::info('Se encontraron '.$registrosExistentes->count().' registros existentes en la BD para este periodo.');

        // --- PASO 2: Separar los datos en "para insertar" y "para actualizar" ---
        $paraInsertar = [];
        $paraActualizar = [];

        foreach ($datosCalculados as $dato) {
            $clave = $dato['user_id'].'-'.$dato['materia_periodo_id'];

            // Comprobamos si el registro ya existe en nuestro "mapa"
            if (isset($registrosExistentes[$clave])) {
                $registroExistente = $registrosExistentes[$clave];

                // Comparamos si la nota O las asistencias han cambiado.
                // Usamos una comparación no estricta para floats por posibles problemas de precisión.
                if (
                    abs($registroExistente->nota_final - $dato['nota_final']) > 0.001 ||
                    $registroExistente->total_asistencias != $dato['total_asistencias'] ||
                    (int) $registroExistente->aprobado !== (int) $dato['aprobado'] ||
                    $registroExistente->motivo_reprobacion !== $dato['motivo_reprobacion'] ||
                    $registroExistente->creditos_aprobados != $dato['creditos_aprobados']
                ) {
                    // Si algo cambió, lo añadimos a la lista de registros a actualizar.
                    $paraActualizar[] = $dato;
                }
                // Si no hay cambios, simplemente lo ignoramos.
            } else {
                // Si no existe en nuestro mapa, es un registro nuevo.
                $paraInsertar[] = $dato;
            }
        }

        Log::info('Análisis completado: '.count($paraInsertar).' registros para insertar, '.count($paraActualizar).' para actualizar.');

        // --- PASO 3: Ejecutar las operaciones en la base de datos ---

        // Insertamos todos los registros nuevos en una sola operación masiva.
        if (! empty($paraInsertar)) {
            // Usamos insert para mayor rendimiento, ya que son datos nuevos.
            foreach (array_chunk($paraInsertar, 500) as $chunk) {
                MateriaAprobadaUsuario::insert($chunk);
            }
            Log::info('Se insertaron '.count($paraInsertar).' nuevos registros.');
        }

        // Actualizamos los registros que cambiaron, uno por uno.
        // Aunque es un bucle, solo se ejecuta para los registros que REALMENTE cambiaron.
        if (! empty($paraActualizar)) {
            foreach ($paraActualizar as $datoActualizar) {
                MateriaAprobadaUsuario::where('user_id', $datoActualizar['user_id'])
                    ->where('materia_periodo_id', $datoActualizar['materia_periodo_id'])
                    ->update([
                        'nota_final' => $datoActualizar['nota_final'],
                        'total_asistencias' => $datoActualizar['total_asistencias'],
                        'aprobado' => $datoActualizar['aprobado'],
                        'creditos_aprobados' => $datoActualizar['creditos_aprobados'],
                        'motivo_reprobacion' => $datoActualizar['motivo_reprobacion'],
                        'updated_at' => now(),
                    ]);
            }
            Log::info('Se actualizaron '.count($paraActualizar).' registros existentes.');
        }
    }

    /**
     * ===== MÉTODO NUEVO =====
     * Cierra administrativamente todos los componentes asociados a un periodo.
     * Marca todas las materias como 'finalizadas' y todos los cortes como 'cerrados'.
     *
     * @param  Periodo  $periodo  El periodo a finalizar.
     */
    public function finalizarComponentesDelPeriodo(Periodo $periodo): void
    {
        Log::info("Servicio: Finalizando componentes para el periodo ID: {$periodo->id} '{$periodo->nombre}'.");

        // 1. Cerrar todos los cortes del periodo en una sola consulta.
        // El método update() sobre la relación devuelve el número de filas afectadas.
        $cortesAfectados = $periodo->cortesPeriodo;

        foreach ($cortesAfectados as $corte) {
            $corte->cerrado = true;
            $corte->save();
        }
        Log::info("Se cerraron {$cortesAfectados} cortes del periodo.");

        // 2. Finalizar todas las materias del periodo en una sola consulta.
        $materiasAfectadas = $periodo->materiasPeriodo;

        foreach ($materiasAfectadas as $materia) {
            $materia->finalizada = true;
            $materia->save();
        }

        Log::info("Se finalizaron {$materiasAfectadas} materias del periodo.");
    }
}
