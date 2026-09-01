<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HitoDenuncia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hito_denuncias';

    protected $guarded = [];

    public function hito(): BelongsTo
    {
        return $this->belongsTo(Hito::class, 'hito_id');
    }

    public function foto(): BelongsTo
    {
        return $this->belongsTo(HitoFoto::class, 'foto_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resueltoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeResueltas($query)
    {
        return $query->where('estado', 'resuelta');
    }
}
