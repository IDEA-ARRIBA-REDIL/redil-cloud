<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CajaFinanzas extends Model
{
  use HasFactory;

  protected $table = 'caja_finanzas';

  protected $fillable = [
    'nombre', // Ajusta los campos que realmente tenga esta tabla
    'descripcion',
    // otros campos
  ];

  public function ingresos(): HasMany
  {
    return $this->hasMany(Ingreso::class, 'caja_finanzas_id');
  }

  public function egresos(): HasMany
  {
    return $this->hasMany(Egreso::class);
  }
}
