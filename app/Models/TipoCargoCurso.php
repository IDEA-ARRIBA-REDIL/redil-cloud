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
        'puede_editar_curso',
        'puede_editar_restricciones',
        'puede_editar_contenido',
        'puede_gestionar_equipo',
        'puede_gestionar_estudiantes',
        'limita_carreras',
        'carreras_permitidas',
        'puede_ver_todos_los_cursos',
    ];

    protected $casts = [
        'puede_responder_preguntas' => 'boolean',
        'puede_editar_curso' => 'boolean',
        'puede_editar_restricciones' => 'boolean',
        'puede_editar_contenido' => 'boolean',
        'puede_gestionar_equipo' => 'boolean',
        'puede_gestionar_estudiantes' => 'boolean',
        'limita_carreras' => 'boolean',
        'carreras_permitidas' => 'array',
        'puede_ver_todos_los_cursos' => 'boolean',
    ];

    public function asignaciones(): HasMany
    {
        return $this->hasMany(CursoUsuarioCargo::class, 'tipo_cargo_curso_id');
    }
}
