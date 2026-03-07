<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemPlantilla extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item_plantillas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'materia_id',
        'corte_escuela_id',
        'tipo_item_id',
        'nombre',
        'contenido',
        'visible_predeterminado',
        'entregable_predeterminado',
        'porcentaje_sugerido',
        'orden',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visible_predeterminado' => 'boolean',
        'entregable_predeterminado' => 'boolean',
        'porcentaje_sugerido' => 'decimal:2',
    ];

    /**
     * Get the materia that owns the item template.
     * Define la relación inversa uno a muchos con Materia.
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Get the school cut template (CorteEscuela) that owns the item template.
     * Define la relación inversa uno a muchos con CorteEscuela.
     */
    public function corteEscuela(): BelongsTo
    {
        return $this->belongsTo(CorteEscuela::class);
    }

    /**
     * Get the type of the item template.
     * Define la relación inversa uno a muchos con TipoItem.
     */
    public function tipoItem(): BelongsTo
    {
        return $this->belongsTo(TipoItem::class);
    }

    /**
     * Get the instances created from this item template.
     * Define la relación uno a muchos con ItemCorteMateriaPeriodo.
     */
    public function itemInstancias(): HasMany
    {
        // Asegúrate de usar el nombre correcto del modelo de instancias
        return $this->hasMany(ItemCorteMateriaPeriodo::class);
    }
}
