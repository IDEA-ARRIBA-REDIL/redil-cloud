<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Configuracion extends Model
{
  use HasFactory;
  protected $table = 'configuraciones';
  protected $guarded = []; // 'cantidad_intentos_traslados' will be automatically allowed

  //Relación de uno a uno que permite vincular los rangos de edad a la configuración de la iglesia.
  public function rangoEdad(): hasMany
  {
    return $this->hasMany(RangoEdad::class);
  }
}
