<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoHito extends Model
{
    use HasFactory;

    protected $table = 'tipo_hitos';

    protected $guarded = [];

    protected $casts = [
        'requiere_trigger' => 'boolean',
        'requiere_actividad' => 'boolean',
        'permite_fotos_usuario' => 'boolean',
        'permite_likes' => 'boolean',
        'evaluacion_dinamica' => 'boolean',
        'configuracion' => 'array',
        'activo' => 'boolean',
    ];

    /**
     * Relación con los hitos pertenecientes a este tipo.
     */
    public function hitos(): HasMany
    {
        return $this->hasMany(Hito::class, 'tipo_hito_id');
    }

    /**
     * Scope para tipos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Helpers de conveniencia para verificar el slug del tipo.
     */
    public function esGeneral(): bool
    {
        return $this->slug === 'general';
    }

    public function esAutomatico(): bool
    {
        return $this->slug === 'automatico';
    }

    public function esDeActividad(): bool
    {
        return $this->slug === 'actividad';
    }

    public function esManualIndividual(): bool
    {
        return $this->slug === 'manual';
    }
}
