<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Continente extends Model
{
    use HasFactory;
    protected $table = 'continentes';
    protected $guarded = [];

    //funcion para crear relacion continente y paises
    public function paises(): HasMany
    {
      return $this->hasMany(Pais::class);
    }

    public function iglesias(): HasMany
    {
      return $this->hasMany(Iglesia::class);
    }

    public function sedes(): HasMany
    {
      return $this->hasMany(Sede::class);
    }
}
