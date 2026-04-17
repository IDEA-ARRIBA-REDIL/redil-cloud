<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'curso_evaluaciones';

    protected $fillable = [
        'minimo_aprobacion',
        'limite_tiempo',
        'cantidad_repeticiones',
        'tiempo_dilatacion',
        'mostrar_respuestas_si_aprueba',
        'mostrar_respuestas_si_pierde',
    ];

    protected $casts = [
        'mostrar_respuestas_si_aprueba' => 'boolean',
        'mostrar_respuestas_si_pierde' => 'boolean',
    ];

    public function item()
    {
        return $this->morphOne(CursoItem::class, 'itemable');
    }

    public function preguntas()
    {
        return $this->hasMany(CursoPregunta::class)->orderBy('orden');
    }
}
