<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoFormularioUsuario extends Model
{
  use HasFactory;
  protected $table = 'tipos_formulario_usuario';
  protected $guarded = [];

  public function formularios(): HasMany
  {
      return $this->hasMany(FormularioUsuario::class, 'tipo_formulario_id');
  }
}
