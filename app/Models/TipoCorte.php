<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCorte extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    /**
     * Un tipo de corte tiene muchos periodos.
     */
    public function periodos(): HasMany
    {
        return $this->hasMany(Periodo::class, 'tipo_corte_id');
    }
}