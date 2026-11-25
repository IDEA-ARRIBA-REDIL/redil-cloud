<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Localidad extends Model
{
    use HasFactory;

    protected $table = 'localidades';

    protected $guarded = [];

    /**
     * Municipio al que pertenece la localidad.
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'localidad_sede');
    }
}
