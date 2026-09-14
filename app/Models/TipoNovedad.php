<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoNovedad extends Model
{
    use HasFactory;

    protected $table = 'tipos_novedad';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function novedades(): HasMany
    {
        return $this->hasMany(NovedadActividad::class, 'tipo_novedad_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
