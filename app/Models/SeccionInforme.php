<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionInforme extends Model
{
    use HasFactory;

    protected $table = 'secciones_informes';

    protected $fillable = [
        'informe_personalizado_id',
        'nombre',
        'orden',
    ];

    public function informePersonalizado(): BelongsTo
    {
        return $this->belongsTo(InformePersonalizado::class, 'informe_personalizado_id');
    }

    public function subsecciones(): HasMany
    {
        return $this->hasMany(SubseccionInforme::class, 'seccion_informe_id')->orderBy('orden', 'asc');
    }
}
