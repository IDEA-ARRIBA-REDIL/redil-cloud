<?php

namespace App\Services;

use App\Models\HorarioMateriaPeriodo;
use App\Models\Matricula;
use App\Models\MatriculaNivel;
use App\Models\NivelAgrupacion;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class MatriculaNivelService
{
    /**
     * Verifica si el estudiante cumple los requisitos para inscribirse al nivel.
     */
    public function verificarRequisitos(User $alumno, NivelAgrupacion $nivel)
    {
        // 1. Verificar si ya está matriculado en este nivel para el periodo activo?
        // Esto se debe validar antes de llamar al servicio.

        // 2. Verificar prerrequisitos (niveles anteriores aprobados)
        // TODO: Implementar lógica de prerrequisitos cuando se defina la estructura de jerarquía de niveles.

        return true;
    }

    /**
     * Inscribe al estudiante en el nivel y en los horarios seleccionados.
     *
     * @param User $alumno
     * @param NivelAgrupacion $nivel
     * @param Periodo $periodo
     * @param array $horariosIds Array de IDs de HorarioMateriaPeriodo
     * @return MatriculaNivel
     */
    public function inscribir(User $alumno, NivelAgrupacion $nivel, Periodo $periodo, array $horariosIds)
    {
        return DB::transaction(function () use ($alumno, $nivel, $periodo, $horariosIds) {

            // 1. Crear Matrícula de Nivel
            $matriculaNivel = MatriculaNivel::create([
                'usuario_id' => $alumno->id,
                'nivel_agrupacion_id' => $nivel->id,
                'periodo_id' => $periodo->id,
                'estado' => 'activa',
                'fecha_matricula' => now(),
            ]);

            // 2. Procesar Materias Individuales
            foreach ($horariosIds as $horarioId) {
                $horario = HorarioMateriaPeriodo::findOrFail($horarioId);

                // Validar que el horario corresponda a una materia del nivel
                // $esMateriaDelNivel = $nivel->materias()->where('materias.id', $horario->materia_id)->exists();
                // if (!$esMateriaDelNivel) { throw new Exception("La materia del horario {$horarioId} no pertenece al nivel."); }

                // Crear Matrícula Estándar (Sistema antiguo compatibilidad)
                // OJO: Aquí decidimos si usamos la tabla 'matriculas' existente o una nueva.
                // El plan dice: "Crear los registros de matricula (sistema existente)"

                Matricula::create([
                    'usuario_id' => $alumno->id,
                    'escuela_id' => $nivel->escuela_id, // La escuela padre
                    'periodo_id' => $periodo->id,
                    'materia_id' => $horario->materia_id,
                    'horario_materia_periodo_id' => $horario->id,
                    'estado' => 'activa', // O 'inscrita'
                    // Campos adicionales si son necesarios
                ]);

                // Opcional: Vincular esta matrícula específica con matricula_nivel si creamos una tabla pivote
                // matriculas_nivel_asignaturas -> id, matricula_nivel_id, matricula_id
            }

            return $matriculaNivel;
        });
    }
}
