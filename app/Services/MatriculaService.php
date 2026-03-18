<?php

namespace App\Services;

use App\Models\Escuela;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Clase de Servicio para la Gestión de Matrículas.
 *
 * Encapsula toda la lógica de negocio compleja relacionada con la disponibilidad
 * de materias para un estudiante, asegurando que los controladores se mantengan limpios
 * y la lógica sea reutilizable y fácil de probar.
 */
class MatriculaService
{
    /**
     * Obtiene un reporte detallado de todos los NIVELES de una escuela y su disponibilidad para el estudiante.
     * (Específico para escuelas con tipo_matricula === 'niveles_agrupados')
     *
     * @param  User  $estudiante  El estudiante para el cual se validarán los niveles.
     * @param  Escuela  $escuela  La escuela en la que se buscarán los niveles.
     * @return Collection Una colección con el nivel, su estado (DISPONIBLE, BLOQUEADA) y motivos.
     */
    public function getReporteDisponibilidadNiveles(User $estudiante, Escuela $escuela): Collection
    {
        // --- ETAPA 1: OBTENER EL HISTORIAL DE NIVELES DEL ESTUDIANTE ---
        // 1.1 Niveles finalizados vía matrícula normal
        $nivelesFinalizadosIds = DB::table('matriculas_nivel')
            ->where('usuario_id', $estudiante->id)
            ->where('estado', 'finalizado')
            ->pluck('nivel_escuela_id')
            ->toArray();

        // 1.2 Niveles aprobados o homologados históricamente
        $nivelesAprobadosTablaIds = DB::table('niveles_aprobado_usuario')
            ->where('user_id', $estudiante->id)
            ->where('aprobado', true)
            ->pluck('nivel_id')
            ->toArray();

        // Unificamos ambos listados
        $nivelesAprobadosIds = array_unique(array_merge($nivelesFinalizadosIds, $nivelesAprobadosTablaIds));

        $progresoPasos = $estudiante->pasosCrecimiento->pluck('pivot.estado_id', 'id');

        // --- ETAPA 2: OBTENER TODOS LOS NIVELES DE LA ESCUELA ---
        $niveles = \App\Models\NivelEscuela::with(['prerrequisitos', 'procesosPrerrequisito', 'tareasRequisito.tareaConsolidacion'])
            ->where('escuela_id', $escuela->id)
            ->orderBy('orden')
            ->get();

        // --- ETAPA 3: GENERAR EL REPORTE DETALLADO ---
        return $niveles->map(function ($nivel) use ($estudiante, $nivelesAprobadosIds, $progresoPasos) {
            $motivos = [];

            // 1. Validar si ya está aprobado/cursado
            if (in_array($nivel->id, $nivelesAprobadosIds)) {
                return (object) [
                    'item' => $nivel,
                    'tipo' => 'NIVEL',
                    'estado' => 'APROBADO',
                    'motivos' => [],
                ];
            }

            // 2. Validar requisitos de Niveles Previos (Jerarquía)
            foreach ($nivel->prerrequisitos as $nivelReq) {
                if (! in_array($nivelReq->id, $nivelesAprobadosIds)) {
                    $motivos[] = 'Requiere haber aprobado el nivel: '.$nivelReq->nombre;
                }
            }

            // 3. Validar requisitos de Pasos de Crecimiento (Lógica Reutilizada)
            $this->validarRequisitosDePasos($nivel, $progresoPasos, $motivos);

            // 4. Validar requisitos de Tareas de Consolidación (Lógica Reutilizada)
            $this->validarRequisitosDeTareas($nivel, $estudiante, $motivos);

            return (object) [
                'item' => $nivel,
                'tipo' => 'NIVEL',
                'estado' => empty($motivos) ? 'DISPONIBLE' : 'BLOQUEADA',
                'motivos' => $motivos,
            ];
        });
    }

    /**
     * Obtiene un reporte detallado de todas las materias de una escuela y su disponibilidad para el estudiante.
     *
     * @param  User  $estudiante  El estudiante para el cual se validarán las materias.
     * @param  Escuela  $escuela  La escuela en la que se buscarán las materias.
     * @return Collection Una colección de objetos con la materia, su estado (DISPONIBLE, BLOQUEADA) y motivos.
     */
    public function getReporteDisponibilidadMaterias(User $estudiante, Escuela $escuela): Collection
    {
        // --- ETAPA 1: OBTENER EL HISTORIAL COMPLETO DEL ESTUDIANTE ---
        $materiasAprobadasIds = DB::table('materias_aprobada_usuario')
            ->where('user_id', $estudiante->id)
            ->where('aprobado', true)
            ->pluck('materia_id')
            ->toArray();

        $progresoPasos = $estudiante->pasosCrecimiento->pluck('pivot.estado_id', 'id');

        // --- ETAPA 2: OBTENER TODAS LAS MATERIAS DE LA ESCUELA ---
        $materias = Materia::with(['prerrequisitosMaterias', 'procesosPrerrequisito', 'tareasRequisito.tareaConsolidacion', 'tareasRequisito.estadoTarea'])
            ->where('escuela_id', $escuela->id)
            ->get();

        // --- ETAPA 3: GENERAR EL REPORTE DETALLADO ---
        return $materias->map(function ($materia) use ($estudiante, $materiasAprobadasIds, $progresoPasos) {
            $motivos = [];

            // 1. Validar si ya está aprobada
            if (in_array($materia->id, $materiasAprobadasIds)) {
                return (object) [
                    'item' => $materia, // Antes era 'materia', ahora usamos 'item' para consistencia con niveles
                    'tipo' => 'MATERIA',
                    'estado' => 'APROBADA',
                    'motivos' => [],
                ];
            }

            // 2. Validar requisitos académicos (Materias)
            foreach ($materia->prerrequisitosMaterias as $materiaRequerida) {
                if (! in_array($materiaRequerida->id, $materiasAprobadasIds)) {
                    $motivos[] = 'Requiere haber aprobado: '.$materiaRequerida->nombre;
                }
            }

            // 3. Validar requisitos de Pasos de Crecimiento (Lógica Reutilizada)
            $this->validarRequisitosDePasos($materia, $progresoPasos, $motivos);

            // 4. Validar requisitos de Tareas de Consolidación (Lógica Reutilizada)
            $this->validarRequisitosDeTareas($materia, $estudiante, $motivos);

            return (object) [
                'item' => $materia,
                'tipo' => 'MATERIA',
                'estado' => empty($motivos) ? 'DISPONIBLE' : 'BLOQUEADA',
                'motivos' => $motivos,
            ];
        });
    }

    /**
     * MÉTODO PRIVADO: Valida si un estudiante ha completado todos los Pasos de Crecimiento requeridos.
     * Esta lógica es compartida tanto para MATERIAS como para NIVELES.
     *
     * @param  mixed  $objeto  El objeto a validar (puede ser Materia o NivelEscuela)
     */
    private function validarRequisitosDePasos($objeto, Collection $progresoPasos, &$motivos): void
    {
        foreach ($objeto->procesosPrerrequisito as $pasoRequerido) {
            $estadoRequerido = $pasoRequerido->pivot->estado_proceso;
            $estadoActual = $progresoPasos->get($pasoRequerido->id, 0);

            if ($estadoActual < $estadoRequerido) {
                $motivos[] = 'Requiere el proceso: '.$pasoRequerido->nombre;
            }
        }
    }

    /**
     * MÉTODO PRIVADO: Valida si un estudiante ha completado las tareas de consolidación requeridas.
     * Esta lógica es compartida tanto para MATERIAS como para NIVELES.
     *
     * @param  mixed  $objeto  El objeto a validar (puede ser Materia o NivelEscuela)
     */
    private function validarRequisitosDeTareas($objeto, User $estudiante, &$motivos): void
    {
        foreach ($objeto->tareasRequisito as $tareaReq) {
            $asig = $estudiante->tareasConsolidacion()
                ->wherePivot('tarea_consolidacion_id', $tareaReq->tarea_consolidacion_id)
                ->wherePivot('estado_tarea_consolidacion_id', $tareaReq->estado_tarea_consolidacion_id)
                ->first();

            if (! $asig) {
                $motivos[] = 'Requiere la tarea "'.$tareaReq->tareaConsolidacion->nombre.
                              '" en estado "'.($tareaReq->estadoTarea->nombre ?? 'Completada').'"';
            }
        }
    }

    /**
     * Mantenemos el método antiguo por compatibilidad si otros componentes lo usan,
     * pero ahora basado en el reporte para evitar duplicidad de lógica.
     */
    public function getMateriasDisponibles(User $estudiante, Escuela $escuela): Collection
    {
        return $this->getReporteDisponibilidadMaterias($estudiante, $escuela)
            ->where('estado', 'DISPONIBLE')
            ->pluck('materia');
    }
}
