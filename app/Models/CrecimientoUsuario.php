<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CrecimientoUsuario extends Pivot
{
    use HasFactory;

    protected $table = 'crecimiento_usuario';

    protected $guarded = [];

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoPasoCrecimientoUsuario::class, 'estado_id');
    }

    protected static function booted()
    {
        static::created(function ($crecimiento) {
            $user = User::find($crecimiento->user_id);
            BitacoraCrecimientoUsuario::create([
                'user_id' => $crecimiento->user_id,
                'paso_crecimiento_id' => $crecimiento->paso_crecimiento_id,
                'estado_id_anterior' => null,
                'estado_id_nuevo' => $crecimiento->estado_id,
                'autor_id' => auth()->id(),
                'sede_id' => $user?->sede_id,
                'fecha' => $crecimiento->fecha ?? now(),
                'detalle' => $crecimiento->detalle,
            ]);

            // Disparar hitos automáticos
            try {
                app(\App\Services\HitoTriggerService::class)->onCrecimientoUsuarioCambio(
                    $crecimiento->user_id,
                    $crecimiento->paso_crecimiento_id,
                    $crecimiento->estado_id
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error disparando hito en CrecimientoUsuario: '.$e->getMessage());
            }
        });

        static::updated(function ($crecimiento) {
            if ($crecimiento->isDirty('estado_id')) {
                $user = User::find($crecimiento->user_id);
                BitacoraCrecimientoUsuario::create([
                    'user_id' => $crecimiento->user_id,
                    'paso_crecimiento_id' => $crecimiento->paso_crecimiento_id,
                    'estado_id_anterior' => $crecimiento->getOriginal('estado_id'),
                    'estado_id_nuevo' => $crecimiento->estado_id,
                    'autor_id' => auth()->id(),
                    'sede_id' => $user?->sede_id,
                    'fecha' => $crecimiento->fecha ?? now(),
                    'detalle' => $crecimiento->detalle,
                ]);

                // Disparar hitos automáticos en cambio de estado
                try {
                    app(\App\Services\HitoTriggerService::class)->onCrecimientoUsuarioCambio(
                        $crecimiento->user_id,
                        $crecimiento->paso_crecimiento_id,
                        $crecimiento->estado_id
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error disparando hito en CrecimientoUsuario (update): '.$e->getMessage());
                }
            }
        });
    }

    /**
     * Registra la creación inicial o la actualización jerárquica de un Paso de Crecimiento para un usuario.
     * Solo actualiza si el estado objetivo tiene un puntaje superior al estado previo (evita degradación).
     */
    public static function procesarPaso(
        int $userId,
        int $pasoCrecimientoId,
        int $estadoObjetivoId,
        ?string $detalle = null,
        mixed $fecha = null,
        ?int $autorId = null
    ): ?self {
        $fechaDefinitiva = $fecha ? \Carbon\Carbon::parse($fecha) : now();

        $existente = static::where('user_id', $userId)
            ->where('paso_crecimiento_id', $pasoCrecimientoId)
            ->first();

        $estadoObjetivo = EstadoPasoCrecimientoUsuario::find($estadoObjetivoId);
        if (! $estadoObjetivo) {
            return null;
        }

        if (! $existente) {
            // Caso Creación Inicial
            return static::create([
                'user_id' => $userId,
                'paso_crecimiento_id' => $pasoCrecimientoId,
                'estado_id' => $estadoObjetivoId,
                'fecha' => $fechaDefinitiva,
                'detalle' => $detalle,
            ]);
        }

        // Caso Evaluación Jerárquica por Puntaje
        $estadoActual = $existente->estado;
        $puntajeActual = $estadoActual ? (int) $estadoActual->puntaje : 0;
        $puntajeObjetivo = (int) $estadoObjetivo->puntaje;

        if ($puntajeObjetivo > $puntajeActual) {
            $existente->update([
                'estado_id' => $estadoObjetivoId,
                'fecha' => $fechaDefinitiva,
                'detalle' => $detalle ?? $existente->detalle,
            ]);

            return $existente;
        }

        \Illuminate\Support\Facades\Log::info("CrecimientoUsuario::procesarPaso: Omite actualización para User ID {$userId}, Paso ID {$pasoCrecimientoId}. Puntaje actual ({$puntajeActual}) >= Objetivo ({$puntajeObjetivo})");

        return $existente;
    }
}
