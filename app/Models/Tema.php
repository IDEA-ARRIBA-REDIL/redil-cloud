<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tema extends Model
{
    use HasFactory;
    protected $table = 'temas';
    protected $guarded = [];



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

}
