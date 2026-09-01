<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class HitoUsuario extends Pivot
{
    public $incrementing = true;

    protected $table = 'hito_usuario';

    protected $guarded = [];

    protected $casts = [
        'fecha' => 'date',
        'asistio' => 'boolean',
    ];

    public function hito(): BelongsTo
    {
        return $this->belongsTo(Hito::class, 'hito_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asignador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }
}
