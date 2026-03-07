<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoPregunta extends Model
{
    use HasFactory;

    protected $table = 'curso_preguntas';

    protected $fillable = [
        'curso_evaluacion_id',
        'pregunta',
        'tipo_respuesta',
        'orden',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(CursoEvaluacion::class, 'curso_evaluacion_id');
    }

    public function opciones()
    {
        return $this->hasMany(CursoPreguntaOpcion::class);
    }
}
