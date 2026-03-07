<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Localidad extends Model
{
    use HasFactory;
    protected $table = 'localidades';
    protected $guarded = [];

    // relacion para conocer a que departamento pertence la ciudad
    public function municipio(): BelongsTo
    {
      return $this->belongsTo(Municipio::class);
    }

    public function barrios(): HasMany
    {
      return $this->HasMany(Barrio::class);
    }
}
