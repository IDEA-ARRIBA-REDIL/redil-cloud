<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barrio extends Model
{
    use HasFactory;
    protected $table = 'barrios';
    protected $guarded = [];


    public function localidad(): BelongsTo
    {
      return $this->belongsTo(Localidad::class);
    }

    public function municipio(): BelongsTo
    {
      return $this->belongsTo(Municipio::class);
    }

    public function usuarios(): HasMany
    {
      return $this->hasMany(User::class);
    }

    public function grupos(): HasMany
    {
      return $this->hasMany(Grupo::class);
    }

    public function sedes(): HasMany
    {
      return $this->hasMany(Sede::class);
    }

}
