<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrasladoMatriculaLog extends Model
{
     use HasFactory;

    // Constantes para el estado del traslado
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADO = 'aprobado';
    const ESTADO_RECHAZADO = 'rechazado';

    protected $table = 'traslados_matricula_log';
    protected $guarded = [];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function horarioOrigen(): BelongsTo
    {
        return $this->belongsTo(HorarioMateriaPeriodo::class, 'origen_horario_id');
    }

    public function horarioDestino(): BelongsTo
    {
        return $this->belongsTo(HorarioMateriaPeriodo::class, 'destino_horario_id');
    }
}
