<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueInforme extends Model
{
    use HasFactory;

    protected $table = 'bloques_informes';

    protected $guarded = [];

    public function informe(): BelongsTo
    {
        return $this->belongsTo(Informe::class, 'informe_id');
    }

    public function informePersonalizado(): BelongsTo
    {
        return $this->informe();
    }

    /**
     * Retorna el arreglo de IDs de sedes vinculadas a este bloque.
     *
     * @return array<int>
     */
    public function getSedesIdsArrayAttribute(): array
    {
        $sedesStr = $this->ids_sedes ?? $this->sedes ?? '';
        if (empty($sedesStr)) {
            return [];
        }

        return array_map('intval', array_filter(explode(',', (string) $sedesStr)));
    }
}
