<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrera extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'carreras';

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen_portada',
        'estado'
    ];

    public function cursos()
    {
        return $this->hasMany(Curso::class, 'carrera_id');
    }
}
