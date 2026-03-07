<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCargoCurso extends Model
{
    use HasFactory;

    protected $table = 'tipos_cargo_cursos';

    protected $fillable = [
        'nombre',
        'puede_responder_preguntas',
    ];

    protected $casts = [
        'puede_responder_preguntas' => 'boolean',
    ];

    public function asignaciones(): HasMany
    {
        return $this->hasMany(CursoUsuarioCargo::class, 'tipo_cargo_curso_id');
    }
}
