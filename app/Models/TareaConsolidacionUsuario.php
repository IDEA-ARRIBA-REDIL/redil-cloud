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
        });

        static::updated(function ($model) {
            if ($model->isDirty('estado_tarea_consolidacion_id')) {
                static::registrarBitacora($model, 'actualizacion');
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
}
