<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;

class Role extends SpatieRole
{
  use HasFactory;
  protected $table = 'roles';
  protected $guarded = [];

  public function verificacionDelPermiso(string|array $permission, ?string $message = 'No tienes los permisos necesarios.'): self
{
    // Usamos el helper Arr::wrap() para asegurarnos de que siempre trabajamos con un array,
    // ya sea que se nos pase un string o un array.
    $permissions = Arr::wrap($permission);

    // Ahora usamos hasAnyPermission() que evalúa si el rol tiene CUALQUIERA de los permisos en el array.
    // Si el array tiene un solo elemento, funciona igual que hasPermissionTo().
    abort_if(! $this->hasAnyPermission($permissions), 403, $message);

    // Devolvemos $this para permitir encadenar otros métodos.
    return $this;
}

  public function formularios(): BelongsToMany
  {
    return $this->belongsToMany(
      FormularioUsuario::class,
      'formulario_usuario_rol',
      'rol_id',
      'formulario_usuario_id'
    )->withTimestamps();
  }

  // antes tipoAsistentesBloqueados
  public function tipoUsuariosBloqueados(): BelongsToMany
  {
    return $this->belongsToMany(
      TipoUsuario::class,
      'tipo_usuario_bloqueado_rol',
      'rol_id',
      'tipo_usuario_id'
    );
  }

  public function privilegiosTiposGrupo(): BelongsToMany
  {
    return $this->belongsToMany(
      TipoGrupo::class,
      'privilegios_tipo_grupo_rol',
      'rol_id',
      'tipo_grupo_id'
    )->withPivot('asignar_asistente', 'desvincular_asistente', 'asignar_encargado', 'desvincular_encargado', 'created_at', 'updated_at');
  }

  public function pasosCrecimiento(): BelongsToMany
  {
    return $this->belongsToMany(PasoCrecimiento::class, 'privilegios_pasos_crecimiento_roles', 'rol_id', 'paso_crecimiento_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function camposPerfil(): BelongsToMany
  {
    return $this->belongsToMany(
      CampoPerfilUsuario::class,
      'rol_campo_perfil_usuario_autogestion',
      'rol_id',
      'campo_perfil_usuario_id'
    )->withPivot(
      'created_at',
      'updated_at',
      'requerido'
    );
  }

  public function camposExtraAutogestion(): BelongsToMany
  {
    return $this->belongsToMany(
      CampoExtra::class,
      'campo_extra_rol_autogestion',
      'rol_id',
      'campo_extra_id'
    )->withPivot(
      'created_at',
      'updated_at',
      'requerido'
    );
  }


}
