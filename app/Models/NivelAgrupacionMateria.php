<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class NivelAgrupacionMateria extends Pivot
{
    protected $table = 'nivel_agrupacion_materias';

    public $incrementing = true;

    protected $fillable = [
        'nivel_agrupacion_id',
        'materia_id',
        'es_obligatoria',
        'orden',
        'creditos',
    ];

    protected $casts = [
        'es_obligatoria' => 'boolean',
    ];
}
