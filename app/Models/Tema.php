<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Tema extends Model
{
    use HasFactory;
    protected $table = 'temas';
    protected $guarded = [];
    protected $appends = ['portada_url'];

    public function getPortadaUrlAttribute(): string
    {
        if ($this->portada && $this->portada !== '' && $this->portada !== 'default.png') {
            return tenant_asset('img/temas/'.$this->portada);
        }
        return Storage::disk('global_media')->url('temas/default.png'); 
    }



  public function categorias(): BelongsToMany
  {
    return $this->belongsToMany(CategoriaTema::class, 'temas_categorias', 'tema_id', 'categoria_tema_id')->withPivot(

      'created_at',
      'updated_at'
    );
  }

  public function sedes(): BelongsToMany
  {
    return $this->belongsToMany(Sede::class, 'sedes_temas', 'tema_id', 'sede_id')->withPivot(

      'created_at',
      'updated_at'
    );
  }

  public function tiposUsuarios(): BelongsToMany
  {
    return $this->belongsToMany(TipoUsuario::class, 'tipos_usuarios_temas', 'tema_id', 'tipo_usuario_id')->withPivot(

      'created_at',
      'updated_at'
    );
  }

  public function tiposGrupos(): BelongsToMany
  {
    return $this->belongsToMany(TipoGrupo::class, 'tipos_grupos_temas', 'tema_id', 'tipo_grupo_id')->withPivot(

      'created_at',
      'updated_at'
    );
  }


  public function temasGrupos(): BelongsToMany
  {
    return $this->belongsToMany(Grupo::class, 'grupos_temas', 'tema_id', 'grupo_id')->withPivot(

      'created_at',
      'updated_at'
    );
  }

  /**
   * Filtra los temas permitidos para un usuario según su rol activo y restricciones.
   */
  public static function filtrarTemasPermitidos($usuario, $rolActivo)
  {
    if ($rolActivo->hasPermissionTo('temas.ver_todos_los_temas')) {
      return self::query();
    }

    $grupos = $usuario->gruposDondeAsiste()->select('grupos.id')->pluck('grupos.id')->toArray();
    $tiposGrupo = $usuario->gruposDondeAsiste()->select('grupos.tipo_grupo_id')->pluck('grupos.tipo_grupo_id')->toArray();
    $sede = $usuario->sede;
    $tipoUsuario = $usuario->tipoUsuario;

    $temasCollection = self::leftJoin('sedes_temas', 'temas.id', '=', 'sedes_temas.tema_id')
      ->leftJoin('tipos_usuarios_temas', 'temas.id', '=', 'tipos_usuarios_temas.tema_id')
      ->leftJoin('tipos_grupos_temas', 'temas.id', '=', 'tipos_grupos_temas.tema_id')
      ->leftJoin('grupos_temas', 'temas.id', '=', 'grupos_temas.tema_id')
      ->where(function ($query) {
        return $query->where('sedes_temas.sede_id', null)
          ->where('tipos_usuarios_temas.tipo_usuario_id', null);
      })
      ->orWhere(function ($query) use ($sede) {
        return $query->where('sedes_temas.sede_id', $sede->id);
      })->orWhere(function ($query) use ($tipoUsuario) {
        return $query->where('tipos_usuarios_temas.tipo_usuario_id', $tipoUsuario->id);
      })
      ->orWhere(function ($query) use ($tiposGrupo) {
        return $query->whereIn('tipos_grupos_temas.tipo_grupo_id', $tiposGrupo);
      })->orWhere(function ($query) use ($grupos) {
        return $query->whereIn('grupos_temas.grupo_id', $grupos);
      })
      ->select('temas.*', 'sedes_temas.sede_id', 'tipos_usuarios_temas.tipo_usuario_id', 'tipos_grupos_temas.tipo_grupo_id', 'grupos_temas.grupo_id')
      ->get();

    $idsPermitidos = $temasCollection->filter(function ($tema) use ($sede, $grupos, $tipoUsuario, $tiposGrupo) {
      $bandera = true;
      if ($tema->sede_id && $tema->sede_id != $sede->id) {
        $bandera = false;
      }
      if ($tema->grupo_id && !in_array($tema->grupo_id, $grupos)) {
        $bandera = false;
      }
      if ($tema->tipo_usuario_id && $tema->tipo_usuario_id != $tipoUsuario->id) {
        $bandera = false;
      }
      if ($tema->tipo_grupo_id && !in_array($tema->tipo_grupo_id, $tiposGrupo)) {
        $bandera = false;
      }

      return $bandera;
    })->pluck('id')->unique()->toArray();

    if (count($idsPermitidos) > 0) {
      return self::whereIn('id', $idsPermitidos);
    }

    return self::whereRaw('1=2');
  }

}
