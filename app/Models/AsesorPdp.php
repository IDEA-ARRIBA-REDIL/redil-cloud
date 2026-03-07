<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsesorPdp extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla
    protected $table = 'asesores_pdp';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'user_id',
        'es_cajero',
        'es_encargado',
        'descripcion',
        'activo',
    ];

    /**
     * Un asesor "es" un Usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
