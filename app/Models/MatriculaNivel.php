<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriculaNivel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'matriculas_nivel';

    protected $fillable = [
        'usuario_id',
        'nivel_agrupacion_id',
        'periodo_id',
        'estado',
        'fecha_matricula',
        'fecha_finalizacion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_matricula' => 'datetime',
        'fecha_finalizacion' => 'datetime',
    ];

    /**
     * Estudiante matriculado.
     */
    public function estudiante(): BelongsTo
    {
        // Asumiendo que el modelo de usuario es User
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Nivel al que se matriculó.
     */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelAgrupacion::class, 'nivel_agrupacion_id');
    }

    /**
     * Periodo académico.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }
}
