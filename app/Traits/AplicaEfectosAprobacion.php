<?php

namespace App\Traits;

use App\Models\CrecimientoUsuario;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\NivelAprobadoUsuario;
use App\Models\Role;
use App\Models\TipoUsuario;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
            return $dato['aprobado'] === true;
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
                DB::table('tarea_consolidacion_usuario')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'tarea_consolidacion_id' => $tareaConfig->tarea_consolidacion_id,
                    ],
                    [
                        'estado_tarea_consolidacion_id' => $tareaConfig->estado_tarea_consolidacion_id,
                        'updated_at' => now(),
                    ]
                );
            }

            // --- B. Actualizar Pasos de Crecimiento ---
            foreach ($materia->pasosCrecimiento as $pasoConfig) {
                $estadoObjetivoId = $pasoConfig->pivot->estado_paso_crecimiento_usuario_id;

                if ($estadoObjetivoId) {
                    CrecimientoUsuario::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'paso_crecimiento_id' => $pasoConfig->id,
                        ],
                        [
                            'estado_id' => $estadoObjetivoId,
                            'fecha' => now(),
                            'detalle' => 'Culminación automática por aprobación de la materia: '.$materia->nombre,
                        ]
                    );
                }
            }

            // --- C. Actualizar Tipo de Usuario y Roles ---
            if ($materia->tipo_usuario_objetivo_id) {
                $usuario = User::with('tipoUsuario')->find($userId);
                $tipoObjetivo = $materia->tipoUsuarioObjetivo;

                if ($usuario && $tipoObjetivo) {
                    $this->actualizarTipoUsuarioYRoles($usuario, $tipoObjetivo);
                }
            }

            // --- D. Verificar Aprobación de Nivel (NUEVO) ---
            if ($materia->nivel_id && isset($usuario)) {
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
                            ->where('aprobado', true)
                            ->count();

                        if ($aprobadasCount >= $materiasDelNivelIn->count()) {
                            Log::info("Servicio: Usuario ID {$userId} ha completado todas las materias obligatorias del Nivel ID {$nivel->id}.");

                            // a. Crear registro de Nivel Aprobado
                            NivelAprobadoUsuario::create([
                                'user_id' => $userId,
                                'nivel_id' => $nivel->id,
                                'periodo_id' => $dato['periodo_id'],
                                'aprobado' => true,
                                'nota_final' => 0,
                            ]);

                            // b. Cambiar rol al objetivo del nivel si existe
                            if ($nivel->tipo_usuario_objetivo_id) {
                                $tipoObjetivoNivel = $nivel->tipoUsuarioObjetivo;
                                if ($tipoObjetivoNivel) {
                                    $this->actualizarTipoUsuarioYRoles($usuario, $tipoObjetivoNivel);
                                }
                            }
                        }
                    }
                }
            }
            unset($usuario);
        }
    }

    /**
     * Helper para actualizar el tipo de usuario y sus roles de forma síncrona.
     */
    private function actualizarTipoUsuarioYRoles(User $usuario, TipoUsuario $tipoObjetivo): void
    {
        $puntajeActual = $usuario->tipoUsuario ? $usuario->tipoUsuario->puntaje : 0;
        $puntajeObjetivo = $tipoObjetivo->puntaje;

        if ($puntajeActual <= $puntajeObjetivo) {
            Log::info("Trait Aprobación: Actualizando Tipo de Usuario para User ID {$usuario->id}. Objetivo: {$tipoObjetivo->id}");

            $usuario->update(['tipo_usuario_id' => $tipoObjetivo->id]);

            $nuevoRolId = $tipoObjetivo->id_rol_dependiente;

            if ($nuevoRolId) {
                DB::table('model_has_roles')
                    ->where('model_id', $usuario->id)
                    ->where('model_type', 'App\Models\User')
                    ->update(['activo' => false]);

                $rolesDependientesIds = Role::where('dependiente', true)->pluck('id');

                if ($rolesDependientesIds->isNotEmpty()) {
                    DB::table('model_has_roles')
                        ->where('model_id', $usuario->id)
                        ->where('model_type', 'App\Models\User')
                        ->whereIn('role_id', $rolesDependientesIds)
                        ->delete();
                }

                $existeRelacion = DB::table('model_has_roles')
                    ->where('model_id', $usuario->id)
                    ->where('role_id', $nuevoRolId)
                    ->exists();

                if (! $existeRelacion) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $nuevoRolId,
                        'model_type' => 'App\Models\User',
                        'model_id' => $usuario->id,
                        'activo' => true,
                    ]);
                } else {
                    DB::table('model_has_roles')
                        ->where('model_id', $usuario->id)
                        ->where('role_id', $nuevoRolId)
                        ->update(['activo' => true]);
                }

                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }
        }
    }
}
