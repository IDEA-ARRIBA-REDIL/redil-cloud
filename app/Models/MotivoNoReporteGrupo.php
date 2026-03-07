<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoNoReporteGrupo extends Model
{
    use HasFactory;
    protected $table = 'motivos_no_reporte_grupo';
    protected $guarded = [];

    //funcion para crear relacion entre reportes de grupo y grupos
    public function reportes(): HasMany
    {
      return $this->hasMany(ReporteGrupo::class);
    }



}
