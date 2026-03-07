<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoPasoCrecimientoUsuario extends Model
{
  use HasFactory;
  protected $table = 'estados_pasos_crecimiento_usuario';
  protected $guarded = [];

  public function usuarios(): HasMany
  {
    return $this->hasMany(CrecimientoUsuario::class);
  }
}
