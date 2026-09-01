<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriaAprobadaUsuario extends Model
{
    use HasFactory;

    protected $table = 'materias_aprobada_usuario';

    public const ESTADO_REPROBADO = 0;

    public const ESTADO_APROBADO = 1;

    public const ESTADO_EN_PROCESO = 2;

    /**
     * Los atributos que se pueden asignar de forma masiva.
     */
    protected $fillable = [
        'user_id',
        'materia_id',
        'materia_periodo_id',
        'periodo_id',
        'aprobado',
        'nota_final',
        'creditos_aprobados',
        'total_asistencias',
        'motivo_reprobacion',
        'es_homologacion',
        'observacion_homologacion',
        'sede_id',
        'fecha_homologacion',
        'fecha_homologacion_aprobacion',
        'homologado_por_user_id',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     */
    protected $casts = [
        'aprobado' => 'integer',
        'nota_final' => 'decimal:2',
        'creditos_aprobados' => 'integer',
        'total_asistencias' => 'integer',
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
        static::saving(function ($materiaAprobada) {
            if ((int) $materiaAprobada->aprobado === self::ESTADO_APROBADO) {
                if (is_null($materiaAprobada->creditos_aprobados) && $materiaAprobada->materia_id) {
                    $materia = $materiaAprobada->materia ?? Materia::find($materiaAprobada->materia_id);
                    if ($materia && ! is_null($materia->creditos)) {
                        $materiaAprobada->creditos_aprobados = $materia->creditos;
                    }
                }
            } else {
                $materiaAprobada->creditos_aprobados = null;
            }
        });

        static::created(function ($materiaAprobada) {
            if ((int) $materiaAprobada->aprobado === self::ESTADO_APROBADO) {
                try {
                    $materia = $materiaAprobada->materia;
                    $fecha = $materiaAprobada->fecha_homologacion
                        ? substr((string) $materiaAprobada->fecha_homologacion, 0, 10)
                        : ($materiaAprobada->created_at ? $materiaAprobada->created_at->toDateString() : now()->toDateString());

                    app(\App\Services\HitoTriggerService::class)->onMateriaAprobada(
                        $materiaAprobada->user_id,
                        $materiaAprobada->materia_id,
                        $materia?->escuela_id,
                        $materia?->nivel_id,
                        $materiaAprobada->id,
                        $fecha
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error disparando hito en MateriaAprobadaUsuario (created): '.$e->getMessage());
                }
            }
        });

        static::updated(function ($materiaAprobada) {
            if ($materiaAprobada->isDirty('aprobado') && (int) $materiaAprobada->aprobado === self::ESTADO_APROBADO) {
                try {
                    $materia = $materiaAprobada->materia;
                    $fecha = $materiaAprobada->fecha_homologacion
                        ? substr((string) $materiaAprobada->fecha_homologacion, 0, 10)
                        : ($materiaAprobada->updated_at ? $materiaAprobada->updated_at->toDateString() : now()->toDateString());

                    app(\App\Services\HitoTriggerService::class)->onMateriaAprobada(
                        $materiaAprobada->user_id,
                        $materiaAprobada->materia_id,
                        $materia?->escuela_id,
                        $materia?->nivel_id,
                        $materiaAprobada->id,
                        $fecha
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error disparando hito en MateriaAprobadaUsuario (updated): '.$e->getMessage());
                }
            }
        });
    }

    // -----------------------------------------------------------------
    // RELACIONES
    // -----------------------------------------------------------------

    /**
     * Obtiene el usuario (alumno) al que pertenece este registro de resultado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtiene la materia base a la que se refiere este resultado.
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    /**
     * Relación con el Nivel a través de la materia.
     */
    public function nivel(): BelongsTo
    {
        return $this->materia->nivel();
    }

    /**
     * Obtiene la instancia de la materia en el periodo específico.
     */
    public function materiaPeriodo(): BelongsTo
    {
        return $this->belongsTo(MateriaPeriodo::class, 'materia_periodo_id');
    }

    /**
     * Obtiene el periodo al que pertenece este resultado.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }
}
