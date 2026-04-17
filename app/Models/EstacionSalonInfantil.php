<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstacionSalonInfantil extends Model
{
    use HasFactory;

    protected $table = 'estaciones_salon_infantil';

    protected $guarded = [];

    /** 1. Salones donde esta estación está disponible (N:M) */
    public function salones(): BelongsToMany
    {
        return $this->belongsToMany(
            SalonInfantil::class,
            'salon_infantil_estacion',
            'estacion_salon_infantil_id',
            'salon_infantil_id'
        );
    }

    /** 2. Registros asociados a esta estación */
    public function registros(): HasMany
    {
        return $this->hasMany(RegistroIglesiaInfantil::class, 'estacion_salon_infantil_id');
    }
}
