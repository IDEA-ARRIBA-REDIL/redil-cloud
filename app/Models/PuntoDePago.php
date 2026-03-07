<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class PuntoDePago extends Model
{
  use HasFactory;
  protected $table = 'puntos_de_pago';
  protected $guarded = [];
  use SoftDeletes;


  // Relación: Un punto de pago tiene muchas cajas
  public function cajas(): HasMany
  {
    return $this->hasMany(Caja::class);
  }

  // Relación: Un punto de pago pertenece a una Sede
  public function sede(): BelongsTo
  {
    return $this->belongsTo(Sede::class);
  }

  // ¡NUEVO! Relación: Un punto de pago tiene un Encargado (Usuario)
  public function encargado(): BelongsTo
  {
    return $this->belongsTo(User::class, 'encargado_id');
  }
}
