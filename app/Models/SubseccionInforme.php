<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubseccionInforme extends Model
{
    use HasFactory;

    protected $table = 'subsecciones_informes';

    protected $fillable = [
        'seccion_informe_id',
        'nombre',
        'orden',
    ];

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(SeccionInforme::class, 'seccion_informe_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemSubseccionInforme::class, 'subseccion_informe_id')->orderBy('orden', 'asc');
    }
}
