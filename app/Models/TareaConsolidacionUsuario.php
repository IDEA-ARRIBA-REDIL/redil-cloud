<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TareaConsolidacionUsuario extends Pivot
{
    use HasFactory;
    public $incrementing = true;
    protected $table = 'tarea_consolidacion_usuario';
    protected $guarded = [];


  public function historial(): HasMany
  {
    return $this->hasMany(HistorialTareaConsolidacionUsuario::class, 'tarea_consolidacion_usuario_id');
  }

  public function estado(): BelongsTo
  {
    return $this->belongsTo(EstadoTareaConsolidacion::class, 'estado_tarea_consolidacion_id');
  }

   public function tareaConsolidacion(): BelongsTo
    {
        // Este registro pivote PERTENECE A una TareaConsolidacion.
        return $this->belongsTo(TareaConsolidacion::class, 'tarea_consolidacion_id');
    }

}
