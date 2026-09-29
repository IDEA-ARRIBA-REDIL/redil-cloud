<?php

namespace App\Services;

use App\Models\InsigniaUser;
use App\Models\ReglaGamificacion;
use App\Models\TransaccionPuntos;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GamificacionService
{
    /**
     * Procesa las reglas de gamificación activas para una acción específica de un usuario.
     *
     * @param  User  $user  Usuario que ejecuta la acción
     * @param  string  $accionCodigo  Código del trigger (ej: 'tiempo_con_dios', 'rueda_de_la_vida')
     * @param  array  $contexto  Datos adicionales opcionales
     * @return array Resumen de recompensas (puntos ganados, insignias desbloqueadas, progreso)
     */
    public static function procesarAccion(User $user, string $accionCodigo, array $contexto = []): array
    {
        $resultado = [
            'puntosGanados' => 0,
            'insigniasDesbloqueadas' => [],
            'insigniasProgreso' => [],
            'reglasAplicadas' => [],
        ];

        try {
            // Obtener todas las reglas activas asociadas a la acción
            $reglas = ReglaGamificacion::with('insignia')
                ->where('accion_codigo', $accionCodigo)
                ->get();

            if ($reglas->isEmpty()) {
                return $resultado;
            }

            DB::transaction(function () use ($user, $reglas, &$resultado) {
                // Bloquear registro de usuario para actualización segura
                $usuario = User::where('id', $user->id)->lockForUpdate()->first();
                if (! $usuario) {
                    return;
                }

                $hoy = Carbon::today();

                foreach ($reglas as $regla) {
                    switch ($regla->frecuencia) {
                        case 'cada_vez':
                            // Validar límite diario si está configurado
                            if ($regla->limite_diario !== null && $regla->limite_diario > 0) {
                                $conteoHoy = TransaccionPuntos::where('user_id', $usuario->id)
                                    ->where('motivo', 'like', "%{$regla->nombre}%")
                                    ->whereDate('created_at', $hoy)
                                    ->count();

                                if ($conteoHoy >= $regla->limite_diario) {
                                    continue 2; // Superó el límite de hoy
                                }
                            }

                            // Otorgar puntos
                            if ($regla->puntos_premio > 0) {
                                $usuario->increment('puntos', $regla->puntos_premio);
                                TransaccionPuntos::create([
                                    'user_id' => $usuario->id,
                                    'monto' => $regla->puntos_premio,
                                    'motivo' => "Recompensa por {$regla->nombre}",
                                    'creado_por_user_id' => $usuario->id,
                                ]);
                                $resultado['puntosGanados'] += $regla->puntos_premio;
                            }

                            // Asignar insignia si está configurada
                            if ($regla->insignia_id && $regla->insignia) {
                                $insigniaUser = InsigniaUser::firstOrCreate(
                                    ['user_id' => $usuario->id, 'insignia_id' => $regla->insignia_id],
                                    ['progreso_actual' => 1, 'completada' => true, 'obtenida_el' => now()]
                                );

                                if ($insigniaUser->wasRecentlyCreated) {
                                    $resultado['insigniasDesbloqueadas'][] = $regla->insignia;
                                } elseif (! $insigniaUser->completada) {
                                    $insigniaUser->update(['completada' => true, 'obtenida_el' => now()]);
                                    $resultado['insigniasDesbloqueadas'][] = $regla->insignia;
                                }
                            }

                            $resultado['reglasAplicadas'][] = $regla->nombre;
                            break;

                        case 'unica_vez':
                            // Verificar si ya se le otorgó esta regla anteriormente
                            $yaOtorgada = TransaccionPuntos::where('user_id', $usuario->id)
                                ->where('motivo', 'like', "%{$regla->nombre}%")
                                ->exists();

                            if ($yaOtorgada) {
                                continue 2;
                            }

                            if ($regla->puntos_premio > 0) {
                                $usuario->increment('puntos', $regla->puntos_premio);
                                TransaccionPuntos::create([
                                    'user_id' => $usuario->id,
                                    'monto' => $regla->puntos_premio,
                                    'motivo' => "Recompensa única por {$regla->nombre}",
                                    'creado_por_user_id' => $usuario->id,
                                ]);
                                $resultado['puntosGanados'] += $regla->puntos_premio;
                            }

                            if ($regla->insignia_id && $regla->insignia) {
                                $insigniaUser = InsigniaUser::firstOrNew([
                                    'user_id' => $usuario->id,
                                    'insignia_id' => $regla->insignia_id,
                                ]);
                                $insigniaUser->progreso_actual = 1;
                                $insigniaUser->completada = true;
                                $insigniaUser->obtenida_el = now();
                                $insigniaUser->save();

                                $resultado['insigniasDesbloqueadas'][] = $regla->insignia;
                            }

                            $resultado['reglasAplicadas'][] = $regla->nombre;
                            break;

                        case 'meta':
                            if (! $regla->insignia_id || ! $regla->insignia) {
                                continue 2;
                            }

                            $meta = max(1, (int) ($regla->meta_cantidad ?: 1));

                            $insigniaUser = InsigniaUser::firstOrNew([
                                'user_id' => $usuario->id,
                                'insignia_id' => $regla->insignia_id,
                            ]);

                            // Si la insignia no ha sido completada aún
                            if (! $insigniaUser->completada) {
                                $progresoActual = (int) ($insigniaUser->progreso_actual ?: 0) + 1;
                                $insigniaUser->progreso_actual = $progresoActual;

                                if ($progresoActual >= $meta) {
                                    $insigniaUser->completada = true;
                                    $insigniaUser->obtenida_el = now();
                                    $insigniaUser->save();

                                    // Otorgar puntos de premio de la meta
                                    if ($regla->puntos_premio > 0) {
                                        $usuario->increment('puntos', $regla->puntos_premio);
                                        TransaccionPuntos::create([
                                            'user_id' => $usuario->id,
                                            'monto' => $regla->puntos_premio,
                                            'motivo' => "Meta completada: {$regla->nombre} ({$meta} repeticiones)",
                                            'creado_por_user_id' => $usuario->id,
                                        ]);
                                        $resultado['puntosGanados'] += $regla->puntos_premio;
                                    }

                                    $resultado['insigniasDesbloqueadas'][] = $regla->insignia;
                                } else {
                                    $insigniaUser->save();
                                    $resultado['insigniasProgreso'][] = [
                                        'insignia' => $regla->insignia,
                                        'progreso_actual' => $progresoActual,
                                        'meta' => $meta,
                                    ];
                                }

                                $resultado['reglasAplicadas'][] = $regla->nombre;
                            }
                            break;
                    }
                }
            });

        } catch (\Throwable $th) {
            Log::error("GamificacionService::procesarAccion - Error procesando '{$accionCodigo}' para usuario {$user->id}: ".$th->getMessage(), [
                'trace' => $th->getTraceAsString(),
            ]);
        }

        return $resultado;
    }
}
