<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraTareaConsolidacion extends Model
{
    use HasFactory;

    protected $table = 'bitacora_tareas_consolidacion';

    protected $guarded = [];

    public function tareaConsolidacionUsuario(): BelongsTo
    {
        return $this->belongsTo(TareaConsolidacionUsuario::class, 'tarea_consolidacion_usuario_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function estadoTareaConsolidacion(): BelongsTo
    {
        return $this->belongsTo(EstadoTareaConsolidacion::class, 'estado_tarea_consolidacion_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
