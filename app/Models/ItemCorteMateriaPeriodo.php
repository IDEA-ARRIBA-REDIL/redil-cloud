<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCorteMateriaPeriodo extends Model
{
    use HasFactory;

    protected $table = 'item_corte_materia_periodo';

    protected $fillable = [
        'materia_periodo_id',
        'corte_periodo_id',
        'item_plantilla_id',        // Origen de la plantilla
        'tipo_item_id',             // Tipo de ítem (copiado de la plantilla)
        'horario_materia_periodo_id', // Vínculo al horario específico (¡IMPORTANTE!)
        'nombre',
        'contenido',
        'visible',                  // Visibilidad actual de esta instancia
        'fecha_inicio',
        'fecha_fin',
        'habilitar_entregable',     // Si esta instancia requiere entrega
        'porcentaje',               // Porcentaje actual de esta instancia
        'orden',

    ];

    protected $casts = [
        'visible' => 'boolean',
        'habilitar_entregable' => 'boolean',
        'porcentaje' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function materiaPeriodo(): BelongsTo
    {
        return $this->belongsTo(MateriaPeriodo::class, 'materia_periodo_id');
    }

    public function cortePeriodo(): BelongsTo
    {
        return $this->belongsTo(CortePeriodo::class, 'corte_periodo_id');
    }

    public function itemPlantilla(): BelongsTo
    {
        // La plantilla original de la cual se generó esta instancia
        return $this->belongsTo(ItemPlantilla::class, 'item_plantilla_id');
    }

    public function tipoItem(): BelongsTo
    {
        // Asumiendo que tienes un modelo TipoItem y la columna tipo_item_id en la tabla
        return $this->belongsTo(TipoItem::class, 'tipo_item_id');
    }

    public function horarioMateriaPeriodo(): BelongsTo
    {
        // El horario específico al que pertenece este ítem de evaluación
        return $this->belongsTo(HorarioMateriaPeriodo::class, 'horario_materia_periodo_id');
    }

    public function respuestas()
    {
        return $this->hasMany(AlumnoRespuestaItem::class, 'item_corte_materia_periodo_id');
    }

    // Si 'espacio_academico_materia_periodo_id' es una FK a otra tabla, define esa relación también.
    // public function espacioAcademicoMateriaPeriodo(): BelongsTo
    // {
    //     // return $this->belongsTo(EspacioAcademicoMateriaPeriodo::class, 'espacio_academico_materia_periodo_id');
    // }
}
