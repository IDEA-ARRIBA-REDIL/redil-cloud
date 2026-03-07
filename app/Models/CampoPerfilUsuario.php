<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampoPerfilUsuario extends Model
{
    use HasFactory;
    protected $table = 'campos_perfil_usuario';
    protected $guarded = [];

  public function roles(): BelongsToMany
  {
    return $this->belongsToMany(Role::class, 'rol_campo_perfil_usuario_autogestion','campo_perfil_usuario_id','rol_id')->withPivot(
      'created_at',
      'updated_at',
      'requerido'
    );
  }
}
