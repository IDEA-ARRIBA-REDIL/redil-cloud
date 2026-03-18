<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriaNivelEscuela extends Model
{
    use HasFactory;

    protected $table = 'materia_niveles_escuelas';

    protected $fillable = [
        'nivel_id',
        'escuela_id',
        'nombre',
        'descripcion',
    ];

    /**
     * Obtiene el nivel al que pertenece esta materia.
     */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelEscuela::class, 'nivel_id');
    }

    /**
     * Obtiene la escuela a la que pertenece esta materia.
     */
    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'escuela_id');
    }
}
