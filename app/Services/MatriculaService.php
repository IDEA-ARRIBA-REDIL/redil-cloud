<?php

namespace App\Services;

use App\Models\User;
use App\Models\Escuela;
use App\Models\Materia;
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
     * Obtiene un reporte detallado de todas las materias de una escuela y su disponibilidad para el estudiante.
     *
     * @param User $estudiante El estudiante para el cual se validarán las materias.
     * @param Escuela $escuela La escuela en la que se buscarán las materias.
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
                return (object)[
                    'materia' => $materia,
                    'estado' => 'APROBADA',
                    'motivos' => []
                ];
            }

            // 2. Validar requisitos académicos (Materias)
            $this->validarRequisitosDeMateria($materia, $materiasAprobadasIds, $motivos);

            // 3. Validar requisitos de Pasos de Crecimiento
            $this->validarRequisitosDePasos($materia, $progresoPasos, $motivos);

            // 4. Validar requisitos de Tareas de Consolidación
            $this->validarRequisitosDeTareas($materia, $estudiante, $motivos);

            return (object)[
                'materia' => $materia,
                'estado' => empty($motivos) ? 'DISPONIBLE' : 'BLOQUEADA',
                'motivos' => $motivos
            ];
        });
    }

    /**
     * MÉTODO PRIVADO: Valida si un estudiante ha aprobado todas las materias requeridas.
     */
    private function validarRequisitosDeMateria(Materia $materia, array $materiasAprobadasIds, &$motivos): void
    {
        foreach ($materia->prerrequisitosMaterias as $materiaRequerida) {
            if (!in_array($materiaRequerida->id, $materiasAprobadasIds)) {
                $motivos[] = "Requiere haber aprobado: " . $materiaRequerida->nombre;
            }
        }
    }

    /**
     * MÉTODO PRIVADO: Valida si un estudiante ha completado todos los Pasos de Crecimiento requeridos.
     */
    private function validarRequisitosDePasos(Materia $materia, Collection $progresoPasos, &$motivos): void
    {
        foreach ($materia->procesosPrerrequisito as $pasoRequerido) {
            $estadoRequerido = $pasoRequerido->pivot->estado_proceso;
            $estadoActual = $progresoPasos->get($pasoRequerido->id, 0);

            if ($estadoActual < $estadoRequerido) {
                // Podríamos buscar el nombre del estado para ser más descriptivos
                $motivos[] = "Requiere el proceso: " . $pasoRequerido->nombre;
            }
        }
    }

    /**
     * MÉTODO PRIVADO: Valida si un estudiante ha completado las tareas de consolidación requeridas.
     */
    private function validarRequisitosDeTareas(Materia $materia, User $estudiante, &$motivos): void
    {
        foreach ($materia->tareasRequisito as $tareaReq) {
            $asig = $estudiante->tareasConsolidacion()
                ->wherePivot('tarea_consolidacion_id', $tareaReq->tarea_consolidacion_id)
                ->wherePivot('estado_tarea_consolidacion_id', $tareaReq->estado_tarea_consolidacion_id)
                ->first();

            if (!$asig) {
                $motivos[] = "Requiere la tarea \"" . $tareaReq->tareaConsolidacion->nombre . 
                              "\" en estado \"" . $tareaReq->estadoTarea->nombre . "\"";
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