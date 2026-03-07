<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoOfrenda extends Model
{
  use HasFactory;

  protected $table = 'tipo_ofrenda';

  // Campos que pueden ser asignados masivamente
  protected $fillable = [
    'descripcion',
    'generica',
    'nombre',
    'formulario_donaciones',
    'codigo_sap',
    'tipo_reunion',
  ];

  public function ofrenda()
  {
    return $this->hasMany("Ofrenda");
  }

  public function ingresos()
  {
    return $this->hasMany(Ingreso::class, 'tipo_ofrenda_id');
  }
}
