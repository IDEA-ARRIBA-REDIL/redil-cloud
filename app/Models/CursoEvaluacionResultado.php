<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoEvaluacionResultado extends Model
{
    use HasFactory;

    protected $table = 'curso_evaluacion_resultados';

    protected $fillable = [
        'user_id',
        'curso_id',
        'curso_item_id',
        'curso_evaluacion_id',
        'nota',
        'aprobado',
        'intento',
        'respuestas_json',
    ];

    protected $casts = [
        'aprobado' => 'boolean',
        'respuestas_json' => 'array',
        'nota' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function item()
    {
        return $this->belongsTo(CursoItem::class, 'curso_item_id');
    }

    public function evaluacion()
    {
        return $this->belongsTo(CursoEvaluacion::class, 'curso_evaluacion_id');
    }
}
