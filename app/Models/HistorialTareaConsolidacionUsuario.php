<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialTareaConsolidacionUsuario extends Model
{
    use HasFactory;
    protected $table = 'historiales_tarea_consolidacion_usuario';
    protected $guarded = [];

    public function tareaDelUsuario(): BelongsTo
    {
      return $this->belongsTo(TareaConsolidacionUsuario::class, 'tarea_consolidacion_usuario_id');
    }

    public function creador(): BelongsTo
    {
        // Este historial PERTENECE A un Usuario (a través de la columna 'usuario_creacion_id').
        return $this->belongsTo(User::class, 'usuario_creacion_id');
    }
}
