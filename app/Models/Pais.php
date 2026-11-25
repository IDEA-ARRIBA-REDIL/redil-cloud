<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pais extends Model
{
    use HasFactory;

    protected $table = 'paises';

    protected $guarded = [];

    /**
     * Municipios que pertenecen al país.
     */
    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class, 'pais_id');
    }

    // relacion para conocer los asistentes que pertencen al pais
    public function usuario(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function iglesias(): HasMany
    {
        return $this->hasMany(Iglesia::class);
    }
}
