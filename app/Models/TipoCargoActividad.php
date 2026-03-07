<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCargoActividad extends Model
{
    use HasFactory;
    protected $table = 'tipo_cargo_actividades';
    protected $guarded = [];

    public function actividadEncargados(): HasMany
    {
        return $this->hasMany(ActividadEncargado::class, 'tipo_cargo_id');
    }
}
