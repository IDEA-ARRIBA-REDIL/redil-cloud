<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zona extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'zonas';

    protected $guarded = [];

    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'sede_zona', 'zona_id', 'sede_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    public function localidades(): BelongsToMany
    {
        return $this->belongsToMany(Localidad::class, 'localidad_zona', 'zona_id', 'localidad_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }
}
