<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotivoDesaprobacionReporteGrupo extends Model
{
  use HasFactory;
  protected $table = 'motivos_desaprobacion_reporte_grupo';
  protected $guarded = [];


  public function reportes(): HasMany
  {
    return $this->hasMany(ReporteGrupo::class);
  }

}
