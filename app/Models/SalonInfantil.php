<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalonInfantil extends Model
{
    use HasFactory;

    protected $table = 'salones_infantil';

    protected $guarded = [];

    /** 1. Estaciones asignadas a este salón (N:M) */
    public function estaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            EstacionSalonInfantil::class,
            'salon_infantil_estacion',
            'salon_infantil_id',
            'estacion_salon_infantil_id'
        );
    }

    /** 2. Registros de menores en este salón */
    public function registros(): HasMany
    {
        return $this->hasMany(RegistroIglesiaInfantil::class, 'salon_infantil_id');
    }

    /** 3. Scope para traer solo salones activos */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
