<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovedadActividad extends Model
{
    use HasFactory;

    public const ESTADO_NO_REVISADO = 'no_revisado';

    public const ESTADO_INICIADO = 'iniciado';

    public const ESTADO_FINALIZADO = 'finalizado';

    protected $table = 'novedades_actividad';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha_respuesta' => 'datetime',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoNovedad(): BelongsTo
    {
        return $this->belongsTo(TipoNovedad::class, 'tipo_novedad_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function respondidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondido_por_id');
    }

    /**
     * Retorna la clase CSS del badge según el estado.
     */
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_NO_REVISADO => 'bg-label-danger',
            self::ESTADO_INICIADO => 'bg-label-info',
            self::ESTADO_FINALIZADO => 'bg-label-success',
            default => 'bg-label-secondary',
        };
    }

    /**
     * Retorna la etiqueta legible del estado.
     */
    public function getEstadoNombreAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_NO_REVISADO => 'No revisado',
            self::ESTADO_INICIADO => 'Iniciado',
            self::ESTADO_FINALIZADO => 'Finalizado',
            default => ucfirst(str_replace('_', ' ', $this->estado ?? '')),
        };
    }

    public function scopeEstado(Builder $query, ?string $estado): Builder
    {
        if ($estado && in_array($estado, [self::ESTADO_NO_REVISADO, self::ESTADO_INICIADO, self::ESTADO_FINALIZADO], true)) {
            return $query->where('estado', $estado);
        }

        return $query;
    }

    public function scopeActividad(Builder $query, ?int $actividadId): Builder
    {
        if ($actividadId) {
            return $query->where('actividad_id', $actividadId);
        }

        return $query;
    }

    public function scopeTipoNovedad(Builder $query, ?int $tipoId): Builder
    {
        if ($tipoId) {
            return $query->where('tipo_novedad_id', $tipoId);
        }

        return $query;
    }

    public function scopeFecha(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        if ($desde) {
            $query->whereDate('created_at', '>=', Carbon::parse($desde)->toDateString());
        }

        if ($hasta) {
            $query->whereDate('created_at', '<=', Carbon::parse($hasta)->toDateString());
        }

        return $query;
    }

    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        if (! empty($termino)) {
            $termino = trim($termino);

            return $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'ILIKE', "%{$termino}%")
                    ->orWhere('identificacion', 'ILIKE', "%{$termino}%")
                    ->orWhere('email', 'ILIKE', "%{$termino}%")
                    ->orWhere('telefono', 'ILIKE', "%{$termino}%")
                    ->orWhere('asunto', 'ILIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$termino}%");
            });
        }

        return $query;
    }
}
