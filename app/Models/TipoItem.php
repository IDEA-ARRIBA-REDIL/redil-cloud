<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tipos_item';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Get the item templates associated with the type.
     * Define la relación uno a muchos con ItemPlantilla.
     */
    public function itemPlantillas(): HasMany
    {
        return $this->hasMany(ItemPlantilla::class);
    }

    /**
     * Get the item instances associated with the type.
     * Define la relación uno a muchos con ItemCorteMateriaPeriodo.
     */
    public function itemInstancias(): HasMany
    {
        // Asegúrate de usar el nombre correcto del modelo de instancias
        return $this->hasMany(ItemCorteMateriaPeriodo::class);
    }
}
