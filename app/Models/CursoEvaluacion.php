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
    ];

    protected $casts = [
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
