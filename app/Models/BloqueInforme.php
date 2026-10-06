<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueInforme extends Model
{
    use HasFactory;

    protected $table = 'bloques_informes';

    protected $fillable = [
        'informe_personalizado_id',
        'nombre',
        'ids_sedes',
    ];

    public function informePersonalizado(): BelongsTo
    {
        return $this->belongsTo(InformePersonalizado::class, 'informe_personalizado_id');
    }

    /**
     * Retorna el arreglo de IDs de sedes vinculadas a este bloque.
     *
     * @return array<int>
     */
    public function getSedesIdsArrayAttribute(): array
    {
        if (empty($this->ids_sedes)) {
            return [];
        }

        return array_map('intval', array_filter(explode(',', $this->ids_sedes)));
    }
}
