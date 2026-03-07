<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pais extends Model
{
    use HasFactory;
    protected $table = 'paises';
    protected $guarded = [];

    //relacion para conocer a que continente pertenece el pais
    public function continente(): BelongsTo
    {
      return $this->belongsTo(Continente::class);
    }

    // relacion para conocer las regiones que pertencen al pais
    public function regiones(): HasMany
    {
    	return $this->hasMany(Region::class);
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
