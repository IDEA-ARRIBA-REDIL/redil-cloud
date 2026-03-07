<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    use HasFactory;
    protected $table = 'departamentos';
    protected $guarded = [];

    // relacion de para conocer a que region pertence el departamento
    public function region(): BelongsTo
    {
      return $this->belongsTo(Region::class);
    }

    public function municipios(): HasMany
    {
      return $this->hasMany (Municipio::class);
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
