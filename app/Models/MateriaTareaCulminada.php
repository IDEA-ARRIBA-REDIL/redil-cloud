<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriaTareaCulminada extends Model
{
    use HasFactory;

    protected $table = 'materia_tarea_culminada';

    protected $fillable = [
        'materia_id',
        'tarea_consolidacion_id',
        'estado_tarea_consolidacion_id',
        'indice'
    ];

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function tareaConsolidacion(): BelongsTo
    {
        return $this->belongsTo(TareaConsolidacion::class, 'tarea_consolidacion_id');
    }

    public function estadoTarea(): BelongsTo
    {
        return $this->belongsTo(EstadoTareaConsolidacion::class, 'estado_tarea_consolidacion_id');
    }
}
