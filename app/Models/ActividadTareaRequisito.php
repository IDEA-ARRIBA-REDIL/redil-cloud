<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ActividadTareaRequisito extends Model
{
    use HasFactory;
    protected $table = 'actividad_tareas_requisito';
    protected $guarded = [];
    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
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