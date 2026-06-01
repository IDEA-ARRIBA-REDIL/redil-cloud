<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActividadBanner extends Model
{
    use HasFactory;

    protected $table = 'actividad_banners';

    protected $guarded = [];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class);
    }

    public function getUrlAttribute(): string
    {
        return tenant_asset('img/actividades/banners/'.$this->nombre);
    }
}
