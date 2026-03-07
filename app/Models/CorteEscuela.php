<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorteEscuela extends Model
{
    use HasFactory;

    protected $table = 'cortes_escuela';

    protected $fillable = [
        'escuela_id',
        'nombre',
        'orden',
    ];

    /**
     * Un corte de escuela pertenece a una Escuela.
     */
    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class);
    }

    /**
     * Una plantilla de corte de escuela puede tener muchas instancias en diferentes periodos.
     */
    public function cortesPeriodo(): HasMany
    {
        return $this->hasMany(CortePeriodo::class);
    }

    /**
     * Get the item templates associated with this school cut template.
     * Define la relación uno a muchos con ItemPlantilla.
     */
    public function itemPlantillas(): HasMany
    {
        return $this->hasMany(ItemPlantilla::class);
    }
}
