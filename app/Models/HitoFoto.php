<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HitoFoto extends Model
{
    use HasFactory;

    protected $table = 'hito_fotos';

    protected $guarded = [];

    protected $casts = [
        'es_admin' => 'boolean',
        'aprobada' => 'boolean',
    ];

    public function hito(): BelongsTo
    {
        return $this->belongsTo(Hito::class, 'hito_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUrlAttribute(): string
    {
        return tenant_asset('img/hitos/fotos/'.$this->ruta);
    }

    public function scopeAdmin($query)
    {
        return $query->where('es_admin', true);
    }

    public function scopeUsuario($query)
    {
        return $query->where('es_admin', false);
    }

    public function scopeAprobadas($query)
    {
        return $query->where('aprobada', true);
    }
}
