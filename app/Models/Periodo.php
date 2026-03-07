<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periodo extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'escuela_id',
        'nombre',
        'fecha_inicio_matricula',
        'fecha_fin_matricula',
        'estado',
        'sistema_calificaciones_id',
        // 'porcentaje_general_corte', // Eliminado
        // 'tipo_corte_id', // Eliminado
        'fecha_maxima_entrega_notas',
        'tiene_pagos', // Asegúrate que este campo existe en tu migración o añádelo
    ];
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_inicio_matricula' => 'date',
        'fecha_fin_matricula' => 'date',
        'fecha_maxima_entrega_notas' => 'date',
        'estado' => 'boolean', // Aprovechamos para castear el estado a booleano
    ];



    /**
     * Un periodo pertenece a una escuela.
     */
    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class);
    }

    /**
     * Un periodo tiene muchas instancias de cortes (basadas en la plantilla de su escuela).
     */
    public function cortesPeriodo(): HasMany
    {
        return $this->hasMany(CortePeriodo::class);
    }

    // ... otras relaciones existentes como materiasPeriodo, sedes, sistemaCalificaciones ...
    public function materiasPeriodo(): HasMany
    {
        return $this->hasMany(MateriaPeriodo::class);
    }
    public function sedes() // Asumiendo que tienes esta relación
    {
        return $this->belongsToMany(Sede::class, 'sedes_periodo');
    }
    public function sistemaCalificaciones(): BelongsTo // Asumiendo que tienes esta relación
    {
        // Asegúrate que el modelo SistemaCalificacion existe
        return $this->belongsTo(SistemaCalificacion::class, 'sistema_calificaciones_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'periodo_id');
    }
}
