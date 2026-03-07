<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
  use HasFactory;
  protected $table = 'cajas';
  protected $guarded = [];
  use SoftDeletes;


  // ¡RELACIÓN RESTAURADA!
  // Obtiene el cajero (usuario) que está asignado a esta caja.
  public function usuario(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  // Relación: Una caja pertenece a un Punto de Pago
  public function puntoDePago(): BelongsTo
  {
    return $this->belongsTo(PuntoDePago::class);
  }

  /* public function registros(): HasMany
    {
      return $this->hasMany(RegistroCaja::class);
    }

    public function historiales(): HasMany
    {
      return $this->hasMany(HistorialCierreCaja::class);
    }	*/
}
