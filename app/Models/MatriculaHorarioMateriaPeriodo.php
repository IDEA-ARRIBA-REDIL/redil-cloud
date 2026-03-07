<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriculaHorarioMateriaPeriodo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'matricula_horario_materia_periodo'; // Manteniendo tu nombre preferido

    protected $fillable = [
        'user_id',
        'maestro_id',                       // Denormalizado para consultas directas de progreso del usuario
        'horario_materia_periodo_id',    // Denormalizado para consultas directas de progreso en el horario
        'matricula_id',                  // FK a la matricula (pago) que habilitó esta inscripción/cursada
        'periodo_id',                    // Denormalizado para facilitar consultas
        'estado_aprobacion',             // 'cursando', 'aprobado', 'no_aprobado', 'retirado_oficialmente'
        'nota_final_numerica',
        'nota_final_conceptual',
        'observaciones_cierre',
        'fecha_actualizacion_estado',
        // Otros campos relevantes para el seguimiento académico en esta clase
    ];

    protected $casts = [
        'fecha_actualizacion_estado' => 'datetime',
        'nota_final_numerica' => 'decimal:2',
    ];

    /**
     * El usuario (alumno) al que pertenece este registro de estado académico.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * El horario (clase) específico cuyo estado académico se está registrando.
     */
    public function horarioMateriaPeriodo(): BelongsTo
    {
        return $this->belongsTo(HorarioMateriaPeriodo::class, 'horario_materia_periodo_id');
    }

    /**
     * La matrícula (con su pago) que permitió y está asociada a este estado académico.
     * Relación uno a uno (inversa).
     */
    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    /**
     * El periodo en el que se cursó esta materia (si se denormaliza).
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }
}