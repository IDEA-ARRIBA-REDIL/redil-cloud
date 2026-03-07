<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServidorGrupo extends Model
{
  use HasFactory;
  protected $table = 'servidores_grupo';
  protected $guarded = [];

  public function tipoServicioGrupo()
  {
    return $this->belongsToMany(
      TipoServicioGrupo::class,
      'servicios_servidores_grupo',
      'servidores_grupo_id',
      'tipo_servicio_grupos_id'
    )->withTimestamps();
  }

}
