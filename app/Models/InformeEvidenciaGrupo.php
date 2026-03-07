<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformeEvidenciaGrupo extends Model
{
    use HasFactory;

    protected $table = 'informes_evidencias_grupo';

    protected $fillable = [
        'grupo_id',
        'nombre',
        'fecha',
        'campo1',
        'campo2',
        'campo3',
    ];

    public function grupo() 
    {
        return $this->belongsTo(Grupo::class);
    }
}
