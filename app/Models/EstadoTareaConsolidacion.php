<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoTareaConsolidacion extends Model
{
  use HasFactory;
  protected $table = 'estados_tarea_consolidacion';
  protected $guarded = [];

  public function tareaDelUsuario(): HasMany
  {
    return $this->hasMany(TareaConsolidacionUsuario::class, 'estado_tarea_consolidacion_id');
  }
}
