<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraCrecimientoUsuario extends Model
{
    use HasFactory;

    protected $table = 'bitacora_crecimiento_usuario';

    protected $guarded = [];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * El estudiante al que pertenece la gestión.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * El paso de crecimiento registrado.
     */
    public function pasoCrecimiento(): BelongsTo
    {
        return $this->belongsTo(PasoCrecimiento::class, 'paso_crecimiento_id');
    }

    /**
     * El autor de la gestión.
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    /**
     * El estado anterior registrado.
     */
    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(EstadoPasoCrecimientoUsuario::class, 'estado_id_anterior');
    }

    /**
     * El nuevo estado alcanzado.
     */
    public function estadoNuevo(): BelongsTo
    {
        return $this->belongsTo(EstadoPasoCrecimientoUsuario::class, 'estado_id_nuevo');
    }

    /**
     * La sede donde se realizó la gestión.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}
