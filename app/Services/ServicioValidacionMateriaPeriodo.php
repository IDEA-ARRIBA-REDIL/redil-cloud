<?php

namespace App\Services;

use App\Models\AlumnoRespuestaItem;
use App\Models\Calificaciones;
use App\Models\MateriaAprobadaUsuario;
use App\Models\MateriaPeriodo;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\ReporteAsistenciaAlumnos;
use App\Traits\AplicaEfectosAprobacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Misión Única: Procesa la finalización de una MATERIA INDIVIDUAL dentro de un periodo.
 */
class ServicioValidacionMateriaPeriodo
{
    use AplicaEfectosAprobacion;

    public function procesarLoteDeAlumnosPorMateria(MateriaPeriodo $materiaPeriodo, int $pagina, int $porPagina): int
    {
        $idsAlumnosDelLote = Matricula::query()
            ->where('periodo_id', $materiaPeriodo->periodo_id)
            ->whereHas('horarioMateriaPeriodo', fn ($consulta) => $consulta->where('materia_periodo_id', $materiaPeriodo->id))
            ->whereNotIn('estado_pago_matricula', ['anulada', 'rechazada'])
            ->distinct()->orderBy('user_id')
            ->offset(($pagina - 1) * $porPagina)->limit($porPagina)
            ->pluck('user_id');

        if ($idsAlumnosDelLote->isEmpty()) {
            return 0;
        }

        $resultados = $this->obtenerResultadosAcademicos($materiaPeriodo, $idsAlumnosDelLote);
        $datosParaGuardar = $this->prepararDatosParaGuardar($materiaPeriodo->periodo, $resultados);
        $this->persistirResultados($datosParaGuardar, $materiaPeriodo->id);
        $this->aplicarEfectosCulminacion($datosParaGuardar);

        return $idsAlumnosDelLote->count();
    }

    private function prepararDatosParaGuardar(Periodo $periodo, array $resultadosAcademicos): array
    {
        // Este método es idéntico al del otro servicio
        if (empty($resultadosAcademicos)) {
            return [];
        }
        $notaMinima = Calificaciones::where('sistema_calificacion_id', $periodo->sistema_calificaciones_id)->where('aprobado', true)->min('nota_minima');
        if (is_null($notaMinima) && collect($resultadosAcademicos)->contains(fn (object $resultado): bool => (bool) $resultado->habilitar_calificaciones)) {
            throw new \Exception("No se encontró nota mínima de aprobación para el sistema de calificación ID: {$periodo->sistema_calificaciones_id}");
        }
        $datosParaGuardar = [];
        foreach ($resultadosAcademicos as $resultado) {
            $estadoFinal = $this->determinarEstadoFinal($resultado, (float) $notaMinima);
            $datosParaGuardar[] = [
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

        return $datosParaGuardar;
    }

    private function obtenerResultadosAcademicos(MateriaPeriodo $materiaPeriodo, Collection $idsAlumnos): array
    {
        $matriculas = Matricula::query()->where('periodo_id', $materiaPeriodo->periodo_id)
            ->whereHas('horarioMateriaPeriodo', fn ($consulta) => $consulta->where('materia_periodo_id', $materiaPeriodo->id))
            ->whereNotIn('estado_pago_matricula', ['anulada', 'rechazada'])
            ->whereIn('user_id', $idsAlumnos)->get();
        $horarios = $matriculas->pluck('horario_materia_periodo_id')->unique();
        $notas = AlumnoRespuestaItem::query()
            ->join('item_corte_materia_periodo as item', 'item.id', '=', 'alumno_respuesta_items.item_corte_materia_periodo_id')
            ->join('cortes_periodo as corte', 'corte.id', '=', 'item.corte_periodo_id')
            ->where('item.materia_periodo_id', $materiaPeriodo->id)
            ->where('corte.periodo_id', $materiaPeriodo->periodo_id)
            ->whereIn('item.horario_materia_periodo_id', $horarios)
            ->whereIn('alumno_respuesta_items.user_id', $idsAlumnos)
            ->select('alumno_respuesta_items.user_id', 'item.horario_materia_periodo_id')
            ->selectRaw('SUM(nota_obtenida * item.porcentaje / 100.0 * corte.porcentaje / 100.0) as nota')
            ->groupBy('alumno_respuesta_items.user_id', 'item.horario_materia_periodo_id')->get()
            ->keyBy(fn ($nota): string => $nota->user_id.'-'.$nota->horario_materia_periodo_id);
        $asistencias = ReporteAsistenciaAlumnos::query()
            ->join('reportes_asistencia_clase as clase', 'clase.id', '=', 'reportes_asistencia_alumnos.reporte_asistencia_clase_id')
            ->whereIn('clase.horario_materia_periodo_id', $horarios)
            ->whereIn('reportes_asistencia_alumnos.user_id', $idsAlumnos)->where('asistio', true)
            ->select('reportes_asistencia_alumnos.user_id', 'clase.horario_materia_periodo_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('reportes_asistencia_alumnos.user_id', 'clase.horario_materia_periodo_id')->get()
            ->keyBy(fn ($asistencia): string => $asistencia->user_id.'-'.$asistencia->horario_materia_periodo_id);
        $materiaPeriodo->loadMissing('materia');

        return $matriculas->groupBy('user_id')->map(function (Collection $inscripciones) use ($materiaPeriodo, $notas, $asistencias): object {
            if ($inscripciones->pluck('horario_materia_periodo_id')->unique()->count() > 1) {
                throw new \RuntimeException('Hay alumnos inscritos en varios horarios de la misma materia. Revisa sus matrículas antes de cerrar.');
            }
            $matricula = $inscripciones->first();
            $clave = $matricula->user_id.'-'.$matricula->horario_materia_periodo_id;

            return (object) [
                'user_id' => $matricula->user_id,
                'matricula_bloqueada' => $inscripciones->contains('bloqueado', true),
                'materia_periodo_id' => $materiaPeriodo->id,
                'materia_id' => $materiaPeriodo->materia_id,
                'creditos' => $materiaPeriodo->materia?->creditos,
                'habilitar_calificaciones' => $materiaPeriodo->habilitar_calificaciones,
                'habilitar_asistencias' => $materiaPeriodo->habilitar_asistencias,
                'asistencias_minimas' => $materiaPeriodo->asistencias_minimas,
                'nota_final_calculada' => (float) ($notas->get($clave)?->nota ?? 0),
                'total_asistencias' => (int) ($asistencias->get($clave)?->total ?? 0),
            ];
        })->values()->all();
    }

    private function determinarEstadoFinal(object $resultado, float $notaMinima): array
    {
        if ((bool) $resultado->matricula_bloqueada) {
            return [
                'aprobado' => false,
                'motivo' => 'MATRICULA_BLOQUEADA',
            ];
        }

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
            ->keyBy(fn ($item) => "{$item->user_id}-{$item->materia_periodo_id}");

        Log::info('Se encontraron '.$registrosExistentes->count().' registros existentes para esta materia.');

        // --- PASO 2: CLASIFICAR DATOS ---
        // Preparamos dos "cubetas": una para los registros nuevos y otra para los que necesitan actualizarse.
        $paraInsertar = [];
        $paraActualizar = [];

        // Recorremos los datos recién calculados.
        foreach ($datosCalculados as $dato) {
            $clave = $dato['user_id'].'-'.$dato['materia_periodo_id'];

            // Comprobamos si el registro ya existe en nuestro "mapa".
            if (isset($registrosExistentes[$clave])) {
                $registroExistente = $registrosExistentes[$clave];

                // Comparamos si la nota, las asistencias o el estado de aprobación han cambiado.
                if (
                    abs($registroExistente->nota_final - $dato['nota_final']) > 0.001 ||
                    $registroExistente->total_asistencias != $dato['total_asistencias'] ||
                    $registroExistente->aprobado != $dato['aprobado'] ||
                    $registroExistente->motivo_reprobacion !== $dato['motivo_reprobacion'] ||
                    $registroExistente->creditos_aprobados != $dato['creditos_aprobados']
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

        Log::info('Análisis completado: '.count($paraInsertar).' para insertar, '.count($paraActualizar).' para actualizar.');

        // --- PASO 3: EJECUTAR OPERACIONES EN LA BASE DE DATOS ---

        // Insertamos todos los registros nuevos en una sola operación masiva para máxima eficiencia.
        if (! empty($paraInsertar)) {
            foreach (array_chunk($paraInsertar, 500) as $chunk) {
                MateriaAprobadaUsuario::insert($chunk);
            }
            Log::info('Se insertaron '.count($paraInsertar).' nuevos registros.');
        }

        // Actualizamos los registros que cambiaron, uno por uno.
        // Aunque es un bucle, solo se ejecuta para la pequeña cantidad de registros que REALMENTE cambiaron.
        if (! empty($paraActualizar)) {
            foreach ($paraActualizar as $datoActualizar) {
                MateriaAprobadaUsuario::where('user_id', $datoActualizar['user_id'])
                    ->where('materia_periodo_id', $datoActualizar['materia_periodo_id'])
                    ->update([
                        'nota_final' => $datoActualizar['nota_final'],
                        'total_asistencias' => $datoActualizar['total_asistencias'],
                        'aprobado' => $datoActualizar['aprobado'],
                        'motivo_reprobacion' => $datoActualizar['motivo_reprobacion'],
                        'creditos_aprobados' => $datoActualizar['creditos_aprobados'],
                        'updated_at' => now(),
                    ]);
            }
            Log::info('Se actualizaron '.count($paraActualizar).' registros existentes.');
        }
    }
}
