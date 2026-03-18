<?php

namespace App\Services;

use App\Models\MatriculaNivel;
use App\Models\NivelEscuela;
use App\Models\Periodo;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class MatriculaNivelService
{
    /**
     * Verifica si el estudiante cumple los requisitos para inscribirse al nivel.
     */
    public function verificarRequisitos(User $alumno, NivelEscuela $nivel)
    {
        // 1. Verificar si ya está matriculado en este nivel para el periodo activo?
        // Esto se debe validar antes de llamar al servicio.

        // 2. Verificar prerrequisitos (niveles anteriores aprobados)
        // TODO: Implementar lógica de prerrequisitos cuando se defina la estructura de jerarquía de niveles.

        return true;
    }

    /**
     * Inscribe al estudiante en el nivel y en los horarios seleccionados.
     * Crea un registro maestro en 'matriculas_nivel' y registros individuales en 'matriculas' por cada materia.
     *
     * @param  User  $alumno  El estudiante a matricular.
     * @param  NivelEscuela  $nivel  El nivel seleccionado.
     * @param  Periodo  $periodo  El periodo académico activo.
     * @param  array  $seleccionHorarios  Array asociativo [materia_id => horario_id].
     * @return MatriculaNivel
     */
    public function inscribirEstudiante(User $alumno, NivelEscuela $nivel, Periodo $periodo, array $seleccionHorarios)
    {
        return DB::transaction(function () use ($alumno, $nivel, $periodo, $seleccionHorarios) {

            // 1. Crear el registro Maestro de la Matrícula por Nivel
            $matriculaNivel = MatriculaNivel::create([
                'usuario_id' => $alumno->id,
                'nivel_escuela_id' => $nivel->id,
                'periodo_id' => $periodo->id,
                'estado' => 'activa',
                'fecha_matricula' => now(),
            ]);

            // 1.1 ACTUALIZACIÓN DE TIPO DE USUARIO (SOLICITUD: Tipo Usuario Inicial)
            if ($nivel->tipo_usuario_inicial_id) {
                $alumno->update(['tipo_usuario_id' => $nivel->tipo_usuario_inicial_id]);
            }

            // 2. Procesar cada materia del nivel y crear sus matrículas individuales
            foreach ($seleccionHorarios as $materiaId => $horarioId) {
                // --- CONCURRENCIA (Regla 3) ---
                // Usamos lockForUpdate para evitar sobre-venta de cupos en procesos simultáneos.
                $horario = \App\Models\HorarioMateriaPeriodo::lockForUpdate()->findOrFail($horarioId);

                // --- VALIDACIÓN DE INTEGRIDAD ---
                // Corregimos la validación: El horario pertenece a una materia a través de materiaPeriodo.
                if ($horario->materiaPeriodo->materia_id != $materiaId) {
                    throw new Exception('El horario seleccionado no corresponde a la materia: '.$materiaId);
                }

                if ($horario->cupos_disponibles < 1) {
                    throw new Exception('No hay cupos en: '.$horario->materiaPeriodo->materia->nombre);
                }

                // 3. Crear el registro en la tabla 'matriculas'
                $matriculaIndividual = \App\Models\Matricula::create([
                    'user_id' => $alumno->id,
                    'periodo_id' => $periodo->id,
                    'horario_materia_periodo_id' => $horario->id,
                    'escuela_id' => $nivel->escuela_id,
                    'fecha_matricula' => now(),
                    'estado_pago_matricula' => 'pago_por_nivel',
                    'valor_a_pagar' => 0,
                ]);

                // 4. Crear el registro de 'Estado Académico'
                \App\Models\MatriculaHorarioMateriaPeriodo::create([
                    'user_id' => $alumno->id,
                    'horario_materia_periodo_id' => $horario->id,
                    'matricula_id' => $matriculaIndividual->id,
                    'periodo_id' => $periodo->id,
                    'estado_aprobacion' => 'cursando',
                ]);

                // 5. Actualizar cupos
                $horario->decrement('cupos_disponibles');

                // 6. ASIGNACIÓN DE PASOS DE CRECIMIENTO (Regla 4)
                $materia = $horario->materiaPeriodo->materia;
                $pasoIniciar = $materia->pasosCrecimiento()->wherePivot('al_iniciar', true)->first();

                if ($pasoIniciar) {
                    $estadoId = $pasoIniciar->pivot->estado_paso_crecimiento_usuario_id ?? $pasoIniciar->pivot->estado;

                    if ($estadoId) {
                        CrecimientoUsuario::updateOrCreate(
                            [
                                'user_id' => $alumno->id,
                                'paso_crecimiento_id' => $pasoIniciar->id,
                            ],
                            [
                                'estado_id' => $estadoId,
                                'fecha' => now(),
                                'detalle' => 'Asignado automáticamente (Matrícula por Nivel) en: '.$materia->nombre,
                            ]
                        );
                    }
                }
            }

            return $matriculaNivel;
        });
    }
}
