<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NivelProcesoPrerrequisito extends Model
{
    use HasFactory;

    protected $table = 'nivel_proceso_prerrequisito';

    protected $fillable = [
        'nivel_id',
        'paso_crecimiento_id',
        'estado_proceso',
        'indice',
        'estado_paso_crecimiento_usuario_id',
    ];

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelEscuela::class, 'nivel_id');
    }

    public function pasoCrecimiento(): BelongsTo
    {
        return $this->belongsTo(PasoCrecimiento::class, 'paso_crecimiento_id');
    }

    public function estadoPasoCrecimiento(): BelongsTo
    {
        return $this->belongsTo(EstadoPasoCrecimientoUsuario::class, 'estado_paso_crecimiento_usuario_id');
    }
}
