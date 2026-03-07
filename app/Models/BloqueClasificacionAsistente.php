<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BloqueClasificacionAsistente extends Model
{
    use HasFactory;

    protected $table = 'bloques_clasificacion_asistente';

    protected $guarded = [];

    public function clasificaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            ClasificacionAsistente::class, 
            'bloque_clasif_asistente_clasif_asistente', 
            'bloque_id', 
            'clasificacion_asistente_id'
        );
    }
}
