<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelTareaRequisito extends Model
{
    use HasFactory;

    protected $table = 'nivel_tarea_requisito';

    protected $fillable = [
        'nivel_agrupacion_id',
        'tarea_consolidacion_id',
        'estado_tarea_consolidacion_id',
        'indice'
    ];

    public function nivel()
    {
        return $this->belongsTo(NivelAgrupacion::class, 'nivel_agrupacion_id');
    }

    public function tareaConsolidacion()
    {
        return $this->belongsTo(TareaConsolidacion::class, 'tarea_consolidacion_id');
    }

    public function estadoTareaConsolidacion()
    {
        return $this->belongsTo(EstadoTareaConsolidacion::class, 'estado_tarea_consolidacion_id');
    }
}
