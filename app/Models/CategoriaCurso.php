<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaCurso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias_cursos';

    protected $fillable = [
        'nombre',
        'color',
        'descripcion'
    ];

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_categoria', 'categoria_curso_id', 'curso_id');
    }
}
