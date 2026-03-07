<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadAsistenciaInscripcion extends Model
{
    use HasFactory;

    // Especificamos el nombre de la tabla
    protected $table = 'actividad_asistencias_inscripcion';

    // Permitimos la asignación masiva de todos los campos
    protected $guarded = [];
}
