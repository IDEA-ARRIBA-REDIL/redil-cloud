<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
  use HasFactory;

  protected $table = 'proveedores';

  protected $fillable = [
    'nombre',
    'identificacion',
    'tipo_identificacion',
    'telefono',
    'direccion',
    'correo',
  ];

  public function egresos(): HasMany
  {
    return $this->hasMany(Egreso::class);
  }
}
