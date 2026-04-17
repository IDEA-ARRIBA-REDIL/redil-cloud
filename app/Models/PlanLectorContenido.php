<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanLectorContenido extends Model
{
    use HasFactory;

    protected $table = 'plan_lector_contenidos';

    protected $fillable = [
        'plan_lector_dia_id',
        'orden',
        'plan_lector_tipo_contenido_id',
        'contenido',
    ];

    public function dia(): BelongsTo
    {
        return $this->belongsTo(PlanLectorDia::class, 'plan_lector_dia_id');
    }

    public function tipoContenido(): BelongsTo
    {
        return $this->belongsTo(PlanLectorTipoContenido::class, 'plan_lector_tipo_contenido_id');
    }

    public function tipo(): BelongsTo
    {
        return $this->tipoContenido();
    }
}
