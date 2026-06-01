<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'max_miembros',
        'incluye_logo',
        'incluye_marca_blanca',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'max_miembros' => 'integer',
            'incluye_logo' => 'boolean',
            'incluye_marca_blanca' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /** Retorna la descripción de capacidad formateada. */
    public function capacidadFormateada(): string
    {
        return $this->max_miembros
            ? 'Hasta '.number_format($this->max_miembros).' miembros'
            : 'Miembros ilimitados';
    }
}
