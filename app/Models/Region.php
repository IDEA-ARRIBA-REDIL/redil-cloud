<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{

  use HasFactory;
  protected $table = 'regiones';
  protected $guarded = [];

  //relacion para conocer de que grupo pertenece el asistente
  public function pais(): BelongsTo
  {
      return $this->belongsTo(Pais::class);
  }

  // relacion para conocer los de partamentos del una region
  public function departamentos(): HasMany
  {
    return $this->hasMany(Departamento::class);
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
