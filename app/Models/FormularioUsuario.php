<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class FormularioUsuario extends Model
{
  use HasFactory;
  protected $table = 'formularios_usuario';
  protected $guarded = [];
  use SoftDeletes;

  public function roles(): BelongsToMany
  {
    return $this->belongsToMany(
      Role::class,
      'formulario_usuario_rol',
      'formulario_usuario_id',
      'rol_id'
    )->withTimestamps();
  }

  public function secciones(): HasMany
  {
    return $this->hasMany(SeccionFormularioUsuario::class);
  }

  public function tipo(): BelongsTo
  {
      return $this->belongsTo(TipoFormularioUsuario::class,'tipo_formulario_id');
  }
  public function camposExtras(): BelongsToMany
  {
    return $this->belongsToMany(
      CampoExtra::class,
      'campos_extras_formularios',
      'formulario_id',
      'campo_extra_id'
    )->withPivot('visible','required');
  }
}
