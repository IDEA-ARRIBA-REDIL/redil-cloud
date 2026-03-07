<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Asegúrate de importar HasMany

class CortePeriodo extends Model
{
    use HasFactory;

    protected $table = 'cortes_periodo';

    protected $fillable = [
        'periodo_id',
        'corte_escuela_id',
        'fecha_inicio',
        'fecha_fin',
        'porcentaje',
        'cerrado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'porcentaje' => 'decimal:2',
        'cerrado' => 'boolean',
    ];

    /**
     * Un corte de periodo pertenece a un Periodo.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    /**
     * Un corte de periodo pertenece a una plantilla de CorteEscuela.
     */
    public function corteEscuela(): BelongsTo
    {
        return $this->belongsTo(CorteEscuela::class);
    }

    /**
     * Get the item instances associated with this specific period cut.
     * Define la relación uno a muchos con ItemCorteMateriaPeriodo.
     */
    public function itemInstancias(): HasMany
    {
        // Usamos el nombre del modelo que creamos para las instancias
        return $this->hasMany(ItemCorteMateriaPeriodo::class);
    }
}
