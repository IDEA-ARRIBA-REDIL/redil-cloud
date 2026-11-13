<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoCivil extends Model
{
    use HasFactory;

    protected $table = 'estados_civiles';

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'es_union_libre' => 'boolean',
            'es_matrimonio' => 'boolean',
            'orden' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function actividadEstados()
    {
        return $this->belongsToMany(EstadoCivil::class, 'actividad_estados_civiles', 'estado_civil_id', 'actividad_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    /**
     * Scope para filtrar únicamente los estados civiles activos.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para ordenar por consecutivo de orden y secundariamente por nombre.
     */
    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('orden', 'asc')->orderBy('nombre', 'asc');
    }

    /**
     * Scope para listados de selección en formularios.
     * Retorna activos en orden, incluyendo opcionalmente IDs ya asignados aunque estén inactivos.
     */
    public function scopeParaSelect(Builder $query, int|array|null $idActual = null): Builder
    {
        $ids = is_array($idActual) ? array_filter($idActual) : array_filter([$idActual]);

        return $query->where(function (Builder $q) use ($ids): void {
            $q->where('activo', true);
            if (! empty($ids)) {
                $q->orWhereIn('id', $ids);
            }
        })->ordenados();
    }
}
