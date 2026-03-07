<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipio extends Model
{
  use HasFactory;
  protected $table = 'municipios';
  protected $guarded = [];

  // relacion para conocer a que departamento pertence la ciudad
	public function departamento(): BelongsTo
	{
		return $this->belongsTo(Departamento::class);
	}

	public function localidades(): HasMany
	{
		return $this->hasMany(Localidad::class);
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
