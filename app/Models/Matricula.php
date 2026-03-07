<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Matricula extends Model
{
    use HasFactory;

    protected $table = 'matriculas';

    protected $fillable = [
        'user_id',
        'periodo_id',
        'horario_materia_periodo_id',
        'referencia_pago',
        'valor_a_pagar',
        'valor_pagado',
        'fecha_pago',
        'tipo_pago_id',
        'estado_pago_id', // Relación con estados_pago
        'fecha_matricula',
        'observacion',
        'material_sede_id',
        'escuela_id',
        'sede_id',
        'trasladado',
        'fecha_bloqueo',
        'bloqueado'
    ];

    protected $casts = [
        'fecha_matricula' => 'date',
        'fecha_pago' => 'datetime',
        'valor_pagado' => 'decimal:2',
        'valor_a_pagar' => 'decimal:2',
        'trasladado' => 'boolean',
        'bloqueado' => 'boolean',
        'fecha_bloqueo' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }

    public function horarioMateriaPeriodo(): BelongsTo
    {
        return $this->belongsTo(HorarioMateriaPeriodo::class, 'horario_materia_periodo_id');
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    public function estadoPago(): BelongsTo
    {
        return $this->belongsTo(EstadoPago::class, 'estado_pago_id');
    }

    public function trasladosLog()
    {
        // Una matrícula puede tener muchos registros de traslado en su historial.
        return $this->hasMany(TrasladoMatriculaLog::class)->latest(); // 'latest()' para ordenar por más reciente
    }

    /**
     * El registro del estado académico y progreso del alumno en la clase
     * que esta matrícula (pago) habilitó.
     */
    public function estadoAcademicoClase(): HasOne // Renombrado para mayor claridad semántica
    {
        // El segundo argumento es la FK en la tabla 'matricula_horario_materia_periodo'
        return $this->hasOne(MatriculaHorarioMateriaPeriodo::class, 'matricula_id');
    }

    public function materialSede(): BelongsTo
    {
        return $this->belongsTo(SedeDestinatario::class, 'material_sede_id');
    }

    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'escuela_id');
    }
}
