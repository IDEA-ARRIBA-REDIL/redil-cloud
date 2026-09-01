<?php

namespace App\Traits;

use App\Models\CrecimientoUsuario;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\NivelAprobadoUsuario;
use App\Models\TareaConsolidacionUsuario;
use App\Models\User;
use Illuminate\Support\Facades\Log;

trait AplicaEfectosAprobacion
{
    /**
     * Aplica los cambios en Tareas de Consolidación y Pasos de Crecimiento
     * para los alumnos que han aprobado sus materias en este lote.
     *
     * @param  array  $datosCalculados  resultados del procesamiento del lote.
     */
    private function aplicarEfectosCulminacion(array $datosCalculados): void
    {
        // 1. Filtramos solo los registros APROBADOS
        $aprobados = array_filter($datosCalculados, function ($dato) {
            return (int) $dato['aprobado'] === MateriaAprobadaUsuario::ESTADO_APROBADO;
        });

        if (empty($aprobados)) {
            return;
        }

        Log::info('Trait Aprobación: Aplicando efectos de culminación para '.count($aprobados).' aprobaciones.');

        // 2. Cargamos la configuración de las materias involucradas
        $materiaIds = collect($aprobados)->pluck('materia_id')->unique();

        $materiasConfig = Materia::whereIn('id', $materiaIds)
            ->with([
                'nivel',
                'tipoUsuarioObjetivo' => function ($query) {
                    $query->select('id', 'puntaje', 'id_rol_dependiente');
                },
                'tareasCulminadas',
                'pasosCrecimiento' => function ($query) {
                    $query->wherePivot('al_iniciar', false);
                },
            ])
            ->get()
            ->keyBy('id');

        // 3. Iteramos y aplicamos cambios
        foreach ($aprobados as $dato) {
            $materiaId = $dato['materia_id'];
            $userId = $dato['user_id'];

            $materia = $materiasConfig->get($materiaId);
            if (! $materia) {
                continue;
            }

            // --- A. Actualizar Tareas de Consolidación ---
            foreach ($materia->tareasCulminadas as $tareaConfig) {
                TareaConsolidacionUsuario::procesarTarea(
                    userId: $userId,
                    tareaConsolidacionId: $tareaConfig->tarea_consolidacion_id,
                    estadoObjetivoId: $tareaConfig->estado_tarea_consolidacion_id,
                    observaciones: 'Culminación automática por aprobación de la materia: '.$materia->nombre,
                    fecha: now()
                );
            }

            // --- B. Actualizar Pasos de Crecimiento ---
            foreach ($materia->pasosCrecimiento as $pasoConfig) {
                $estadoObjetivoId = $pasoConfig->pivot->estado_paso_crecimiento_usuario_id;

                if ($estadoObjetivoId) {
                    CrecimientoUsuario::procesarPaso(
                        userId: $userId,
                        pasoCrecimientoId: $pasoConfig->id,
                        estadoObjetivoId: $estadoObjetivoId,
                        detalle: 'Culminación automática por aprobación de la materia: '.$materia->nombre,
                        fecha: now()
                    );
                }
            }

            $usuario = User::find($userId);

            // --- C. Actualizar Tipo de Usuario y Roles ---
            if ($materia->tipo_usuario_objetivo_id && $usuario) {
                $usuario->promoverTipoUsuario($materia->tipo_usuario_objetivo_id);
            }

            // --- D. Verificar Aprobación de Nivel (NUEVO) ---
            if ($materia->nivel_id && $usuario) {
                $nivel = $materia->nivel;

                // 1. Verificar si ya tiene un registro de aprobación para este nivel
                $yaAprobado = NivelAprobadoUsuario::where('user_id', $userId)
                    ->where('nivel_id', $nivel->id)
                    ->exists();

                if (! $yaAprobado) {
                    // 2. Obtener materias obligatorias del nivel
                    $materiasDelNivelIn = Materia::where('nivel_id', $nivel->id)
                        ->where('caracter_obligatorio', true)
                        ->pluck('id');

                    if ($materiasDelNivelIn->isNotEmpty()) {
                        // 3. Contar cuántas de esas materias ha aprobado el usuario en su historial TOTAL
                        $aprobadasCount = MateriaAprobadaUsuario::where('user_id', $userId)
                            ->whereIn('materia_id', $materiasDelNivelIn)
                            ->where('aprobado', MateriaAprobadaUsuario::ESTADO_APROBADO)
                            ->count();

                        if ($aprobadasCount >= $materiasDelNivelIn->count()) {
                            Log::info("Servicio: Usuario ID {$userId} ha completado todas las materias obligatorias del Nivel ID {$nivel->id}.");

                            // a. Crear registro de Nivel Aprobado
                            NivelAprobadoUsuario::create([
                                'user_id' => $userId,
                                'nivel_id' => $nivel->id,
                                'periodo_id' => $dato['periodo_id'],
                                'aprobado' => NivelAprobadoUsuario::ESTADO_APROBADO,
                                'fecha_homologacion_aprobacion' => now(),
                                'nota_final' => 0,
                            ]);

                            // b. Cambiar rol al objetivo del nivel si existe
                            if ($nivel->tipo_usuario_objetivo_id) {
                                $usuario->promoverTipoUsuario($nivel->tipo_usuario_objetivo_id);
                            }
                        }
                    }
                }
            }

            // --- E. Disparar Hitos Automáticos por Aprobación de Materia ---
            try {
                app(\App\Services\HitoTriggerService::class)->onMateriaAprobada(
                    $userId,
                    $materiaId,
                    $materia->escuela_id,
                    $materia->nivel_id,
                    null,
                    now()->toDateString()
                );
            } catch (\Throwable $e) {
                Log::error('Error disparando hito en AplicaEfectosAprobacion: '.$e->getMessage());
            }

            unset($usuario);
        }
    }
}
