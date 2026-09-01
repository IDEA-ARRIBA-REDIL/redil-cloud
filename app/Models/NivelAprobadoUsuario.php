<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NivelAprobadoUsuario extends Model
{
    use HasFactory;

    protected $table = 'niveles_aprobado_usuario';

    public const ESTADO_REPROBADO = 0;

    public const ESTADO_APROBADO = 1;

    public const ESTADO_EN_PROCESO = 2;

    protected $fillable = [
        'user_id',
        'nivel_id',
        'periodo_id',
        'aprobado',
        'nota_final',
        'es_homologacion',
        'observacion_homologacion',
        'sede_id',
        'fecha_homologacion',
        'fecha_homologacion_aprobacion',
        'homologado_por_user_id',
    ];

    protected $casts = [
        'aprobado' => 'integer',
        'nota_final' => 'decimal:2',
        'fecha_homologacion_aprobacion' => 'datetime',
    ];

    public function esAprobado(): bool
    {
        return (int) $this->aprobado === self::ESTADO_APROBADO;
    }

    public function esEnProceso(): bool
    {
        return (int) $this->aprobado === self::ESTADO_EN_PROCESO;
    }

    public function esReprobado(): bool
    {
        return (int) $this->aprobado === self::ESTADO_REPROBADO;
    }

    protected static function booted()
    {
        static::created(function ($nivelAprobado) {
            if ((int) $nivelAprobado->aprobado === self::ESTADO_APROBADO) {
                try {
                    $nivel = $nivelAprobado->nivel;
                    $fecha = $nivelAprobado->fecha_homologacion
                        ? substr((string) $nivelAprobado->fecha_homologacion, 0, 10)
                        : ($nivelAprobado->created_at ? $nivelAprobado->created_at->toDateString() : now()->toDateString());

                    app(\App\Services\HitoTriggerService::class)->onNivelAprobado(
                        $nivelAprobado->user_id,
                        $nivelAprobado->nivel_id,
                        $nivel?->escuela_id,
                        $nivelAprobado->id,
                        $fecha
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error disparando hito en NivelAprobadoUsuario (created): '.$e->getMessage());
                }
            }
        });

        static::updated(function ($nivelAprobado) {
            if ($nivelAprobado->isDirty('aprobado') && (int) $nivelAprobado->aprobado === self::ESTADO_APROBADO) {
                try {
                    $nivel = $nivelAprobado->nivel;
                    $fecha = $nivelAprobado->fecha_homologacion
                        ? substr((string) $nivelAprobado->fecha_homologacion, 0, 10)
                        : ($nivelAprobado->updated_at ? $nivelAprobado->updated_at->toDateString() : now()->toDateString());

                    app(\App\Services\HitoTriggerService::class)->onNivelAprobado(
                        $nivelAprobado->user_id,
                        $nivelAprobado->nivel_id,
                        $nivel?->escuela_id,
                        $nivelAprobado->id,
                        $fecha
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error disparando hito en NivelAprobadoUsuario (updated): '.$e->getMessage());
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelEscuela::class, 'nivel_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }
}
