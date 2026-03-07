<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CitaConsejeria extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'citas_consejeria';
    protected $guarded = [];

    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
        'fecha_hora_fin' => 'datetime',
        'enlace_virtual' => 'string',
        'notas_paciente' => 'string',
        'notas_cancelacion' => 'string',
        'cancelado_por' => 'integer', // Assuming cancelado_por stores a user ID
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtiene el perfil del consejero que atenderá la cita.
     */
    public function consejero(): BelongsTo
    {
        return $this->belongsTo(Consejero::class, 'consejero_id');
    }

    /**
     * Obtiene el tipo de consejería (motivo) de la cita.
     */
    public function tipoConsejeria(): BelongsTo
    {
        return $this->belongsTo(TipoConsejeria::class, 'tipo_consejeria_id');
    }


    public function canceladoPorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelado_por');
    }
}
