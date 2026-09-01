<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Insignia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insignias';

    protected $guarded = [];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'insignia_user')
            ->withPivot(['progreso_actual', 'completada', 'obtenida_el'])
            ->withTimestamps();
    }

    public function reglas(): HasMany
    {
        return $this->hasMany(ReglaGamificacion::class, 'insignia_id');
    }

    public function getImagenUrlAttribute(?string $value): ?string
    {
        if ($value && $value !== '') {
            return tenant_asset('img/insignias/'.$value);
        }

        return Storage::disk('global_media')->url('insignias/default.png');
    }
}
