<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NivelPeriodo extends Model
{
    protected $table = 'niveles_periodo';

    protected $fillable = [
        'periodo_id',
        'nivel_escuela_id',
        'escuela_id',
    ];

    /**
     * Un registro de nivel en un periodo pertenece a un periodo.
     */
    public function periodo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    /**
     * Un registro de nivel en un periodo pertenece a un molde de nivel (NivelEscuela).
     */
    public function nivelEscuela(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NivelEscuela::class, 'nivel_escuela_id');
    }

    /**
     * Un registro de nivel en un periodo pertenece a una escuela.
     */
    public function escuela(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Escuela::class);
    }
}
