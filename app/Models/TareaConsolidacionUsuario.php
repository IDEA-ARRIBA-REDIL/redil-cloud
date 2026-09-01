<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TareaConsolidacionUsuario extends Pivot
{
    use HasFactory;

    public $incrementing = true;

    protected $table = 'tarea_consolidacion_usuario';

    protected $guarded = [];

    protected static function booted()
    {
        static::created(function ($model) {
            static::registrarBitacora($model, 'creacion');

            // Disparar hitos automáticos
            try {
                app(\App\Services\HitoTriggerService::class)->onTareaConsolidacionCambio(
                    $model->user_id,
                    $model->tarea_consolidacion_id,
                    $model->estado_tarea_consolidacion_id
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error disparando hito en TareaConsolidacionUsuario: '.$e->getMessage());
            }
        });

        static::updated(function ($model) {
            if ($model->isDirty('estado_tarea_consolidacion_id')) {
                static::registrarBitacora($model, 'actualizacion');

                // Disparar hitos automáticos en cambio de estado
                try {
                    app(\App\Services\HitoTriggerService::class)->onTareaConsolidacionCambio(
                        $model->user_id,
                        $model->tarea_consolidacion_id,
                        $model->estado_tarea_consolidacion_id
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error disparando hito en TareaConsolidacionUsuario (update): '.$e->getMessage());
                }
            }
        });
    }

    protected static function registrarBitacora($model, $tipo)
    {
        $user = $model->user;

        if ($user) {
            $sede = $user->sede;
            $zonaId = null;
            if ($sede) {
                // Buscamos la zona vinculada a la sede
                $zonaId = $sede->zonas()->first()?->id;
            }

            \App\Models\BitacoraTareaConsolidacion::create([
                'tarea_consolidacion_usuario_id' => $model->id,
                'user_id' => $model->user_id,
                'zona_id' => $zonaId,
                'sede_id' => $user->sede_id,
                'estado_tarea_consolidacion_id' => $model->estado_tarea_consolidacion_id,
                'autor_id' => auth()->id() ?? 1,
                'observaciones' => $tipo === 'creacion' ? 'Tarea asignada inicialmente' : 'Cambio de estado de la tarea',
            ]);
        }
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialTareaConsolidacionUsuario::class, 'tarea_consolidacion_usuario_id');
    }

    public function bitacora(): HasMany
    {
        return $this->hasMany(BitacoraTareaConsolidacion::class, 'tarea_consolidacion_usuario_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoTareaConsolidacion::class, 'estado_tarea_consolidacion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tareaConsolidacion(): BelongsTo
    {
        // Este registro pivote PERTENECE A una TareaConsolidacion.
        return $this->belongsTo(TareaConsolidacion::class, 'tarea_consolidacion_id');
    }

    /**
     * Registra la asignación inicial o la actualización jerárquica de una Tarea de Consolidación para un usuario.
     * Solo actualiza si el estado objetivo posee un puntaje superior al estado actual del usuario (evita degradación).
     */
    public static function procesarTarea(
        int $userId,
        int $tareaConsolidacionId,
        int $estadoObjetivoId,
        ?string $observaciones = null,
        mixed $fecha = null,
        ?int $autorId = null
    ): ?self {
        $fechaDefinitiva = $fecha ? \Carbon\Carbon::parse($fecha) : now();

        $existente = static::where('user_id', $userId)
            ->where('tarea_consolidacion_id', $tareaConsolidacionId)
            ->first();

        $estadoObjetivo = EstadoTareaConsolidacion::find($estadoObjetivoId);
        if (! $estadoObjetivo) {
            return null;
        }

        if (! $existente) {
            // Caso Creación Inicial
            $nuevo = static::create([
                'user_id' => $userId,
                'tarea_consolidacion_id' => $tareaConsolidacionId,
                'estado_tarea_consolidacion_id' => $estadoObjetivoId,
                'fecha' => $fechaDefinitiva,
            ]);

            HistorialTareaConsolidacionUsuario::create([
                'tarea_consolidacion_usuario_id' => $nuevo->id,
                'fecha' => $fechaDefinitiva,
                'detalle' => $observaciones ?? 'Asignación inicial de tarea',
                'usuario_creacion_id' => $autorId ?? (auth()->id() ?? $userId),
            ]);

            return $nuevo;
        }

        // Caso Evaluación Jerárquica por Puntaje
        $estadoActual = $existente->estado;
        $puntajeActual = $estadoActual ? (int) $estadoActual->puntaje : 0;
        $puntajeObjetivo = (int) $estadoObjetivo->puntaje;

        if ($puntajeObjetivo > $puntajeActual) {
            $existente->update([
                'estado_tarea_consolidacion_id' => $estadoObjetivoId,
                'fecha' => $fechaDefinitiva,
            ]);

            HistorialTareaConsolidacionUsuario::create([
                'tarea_consolidacion_usuario_id' => $existente->id,
                'fecha' => $fechaDefinitiva,
                'detalle' => $observaciones ?? 'Actualización de estado de tarea',
                'usuario_creacion_id' => $autorId ?? (auth()->id() ?? $userId),
            ]);

            return $existente;
        }

        \Illuminate\Support\Facades\Log::info("TareaConsolidacionUsuario::procesarTarea: Omite actualización para User ID {$userId}, Tarea ID {$tareaConsolidacionId}. Puntaje actual ({$puntajeActual}) >= Objetivo ({$puntajeObjetivo})");

        return $existente;
    }
}
