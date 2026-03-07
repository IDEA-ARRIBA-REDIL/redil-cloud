<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ActividadCategoriaTareaRequisito extends Model
{
    use HasFactory;
    protected $table = 'actividad_categoria_tareas_requisito';
    protected $guarded = [];
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(ActividadCategoria::class, 'actividad_categoria_id');
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