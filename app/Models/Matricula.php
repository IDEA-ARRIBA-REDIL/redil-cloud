<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matricula extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'matriculas';

    protected $fillable = [
        'user_id',
        'periodo_id',
        'horario_materia_periodo_id',
        'referencia_pago',
        'compra_id',
        'pago_id',
        'estado_pago_matricula',
        'valor_a_pagar',
        'valor_pagado',
        'fecha_pago',
        'tipo_pago_id',
        'estado_pago_id', // Relación con estados_pago
        'fecha_matricula',
        'observacion',
        'material_sede_id',
        'escuela_id',
        'sede_id',
        'trasladado',
        'fecha_bloqueo',
        'bloqueado',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_matricula' => 'date',
        'fecha_pago' => 'datetime',
        'valor_pagado' => 'decimal:2',
        'valor_a_pagar' => 'decimal:2',
        'trasladado' => 'boolean',
        'bloqueado' => 'boolean',
        'fecha_bloqueo' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Matricula $matricula) {
            \Illuminate\Support\Facades\DB::table('matricula_horario_materia_periodo')
                ->where('matricula_id', $matricula->id)
                ->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }

    public function horarioMateriaPeriodo(): BelongsTo
    {
        return $this->belongsTo(HorarioMateriaPeriodo::class, 'horario_materia_periodo_id');
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    public function estadoPago(): BelongsTo
    {
        return $this->belongsTo(EstadoPago::class, 'estado_pago_id');
    }

    public function trasladosLog()
    {
        // Una matrícula puede tener muchos registros de traslado en su historial.
        return $this->hasMany(TrasladoMatriculaLog::class)->latest(); // 'latest()' para ordenar por más reciente
    }

    /**
     * El registro del estado académico y progreso del alumno en la clase
     * que esta matrícula (pago) habilitó.
     */
    public function estadoAcademicoClase(): HasOne // Renombrado para mayor claridad semántica
    {
        // El segundo argumento es la FK en la tabla 'matricula_horario_materia_periodo'
        return $this->hasOne(MatriculaHorarioMateriaPeriodo::class, 'matricula_id');
    }

    public function materialSede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'material_sede_id');
    }

    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'escuela_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    /**
     * Elimina matrículas, estado académico e inscripciones borrador asociadas a un pago que fue rechazado, anulado o cancelado,
     * para liberar la materia, aula, horario y cupos y permitir que el alumno pueda volver a matricularse.
     */
    public static function limpiarMatriculasDePagoFallido(Pago $pago): void
    {
        app(\App\Services\EstadoFinancieroService::class)->limpiarReservasFallidas($pago);
    }

    /**
     * Elimina una matrícula específica junto con sus registros pivote de estado académico y horarios.
     */
    public static function borrarMatriculaYRelaciones(Matricula $matricula): void
    {
        self::eliminarMatriculaCompletaPorId($matricula->id);
    }

    /**
     * Elimina tabla por tabla por el ID de la matrícula todo lo relacionado a esa matrícula.
     * Sin restricciones ni condicionales complejas.
     */
    public static function eliminarMatriculaCompletaPorId(int $matriculaId): bool
    {
        try {
            $matricula = \Illuminate\Support\Facades\DB::table('matriculas')->where('id', $matriculaId)->first();
            if (! $matricula) {
                return false;
            }

            // 1. Eliminar pivote horario/materia/periodo y estado académico
            \Illuminate\Support\Facades\DB::table('matricula_horario_materia_periodo')->where('matricula_id', $matriculaId)->delete();

            // 2. Eliminar logs de traslados
            if (\Illuminate\Support\Facades\Schema::hasTable('traslados_matricula_log')) {
                \Illuminate\Support\Facades\DB::table('traslados_matricula_log')->where('matricula_id', $matriculaId)->delete();
            }

            // 3. Eliminar asistencias temporales si aplican
            if (\Illuminate\Support\Facades\Schema::hasTable('reporte_asistencia_alumnos') && isset($matricula->horario_materia_periodo_id, $matricula->user_id)) {
                \Illuminate\Support\Facades\DB::table('reporte_asistencia_alumnos')
                    ->where('horario_materia_periodo_id', $matricula->horario_materia_periodo_id)
                    ->where('user_id', $matricula->user_id)
                    ->delete();
            }

            // 4. Soft Delete en la tabla matriculas asignando fecha de eliminación y usuario responsable (si existen las columnas)
            $updateData = [];
            if (\Illuminate\Support\Facades\Schema::hasColumn('matriculas', 'deleted_at')) {
                $updateData['deleted_at'] = now();
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('matriculas', 'deleted_by')) {
                $updateData['deleted_by'] = auth()->id() ?? null;
            }

            if (! empty($updateData)) {
                \Illuminate\Support\Facades\DB::table('matriculas')
                    ->where('id', $matriculaId)
                    ->update($updateData);
            } else {
                \Illuminate\Support\Facades\DB::table('matriculas')->where('id', $matriculaId)->delete();
            }

            // 5. Eliminar carritos e inscripciones temporales no pagadas asociadas
            if ($matricula->referencia_pago) {
                \Illuminate\Support\Facades\DB::table('actividades_carrito_compras')
                    ->where('compra_id', $matricula->referencia_pago)
                    ->delete();

                \Illuminate\Support\Facades\DB::table('inscripciones')
                    ->where('compra_id', $matricula->referencia_pago)
                    ->where('estado', '!=', 1)
                    ->delete();
            }

            if ($matricula->user_id) {
                \Illuminate\Support\Facades\DB::table('actividades_carrito_compras')
                    ->where('user_id', $matricula->user_id)
                    ->delete();
            }

            \Illuminate\Support\Facades\Log::info("Matricula: Matrícula ID {$matriculaId} eliminada tabla por tabla exitosamente.");

            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error eliminando matrícula ID {$matriculaId} tabla por tabla: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Aplica los efectos académicos y de progreso configurados para la matrícula:
     * 1. Promoción a Tipo de Usuario Inicial (materia / nivel).
     * 2. Asignación automática de Pasos de Crecimiento configurados "Al Iniciar" (materia / nivel).
     */
    public function aplicarEfectosMatricula(): void
    {
        if (! $this->user_id) {
            \Illuminate\Support\Facades\Log::warning("Matricula::aplicarEfectosMatricula: No se encontró user_id para Matricula ID {$this->id}");

            return;
        }

        $usuario = $this->relationLoaded('user') ? $this->user : User::find($this->user_id);
        if (! $usuario) {
            \Illuminate\Support\Facades\Log::warning("Matricula::aplicarEfectosMatricula: No se encontró usuario para Matricula ID {$this->id}");

            return;
        }

        if (! $this->horario_materia_periodo_id) {
            \Illuminate\Support\Facades\Log::warning("Matricula::aplicarEfectosMatricula: No se encontró horario_materia_periodo_id para Matricula ID {$this->id}");

            return;
        }

        // Obtener la materia a través de horarioMateriaPeriodo -> materiaPeriodo -> materia
        $horario = $this->relationLoaded('horarioMateriaPeriodo') ? $this->horarioMateriaPeriodo : HorarioMateriaPeriodo::find($this->horario_materia_periodo_id);
        $materia = $horario?->materiaPeriodo?->materia;

        if (! $materia) {
            \Illuminate\Support\Facades\Log::warning("Matricula::aplicarEfectosMatricula: No se encontró materia para Matricula ID {$this->id}");

            return;
        }

        \Illuminate\Support\Facades\Log::info("Matricula::aplicarEfectosMatricula: Aplicando efectos para Usuario ID {$usuario->id} en Materia '{$materia->nombre}' (ID {$materia->id})");

        // 1. Tipo de Usuario Inicial de la Materia
        if ($materia->tipo_usuario_inicial_id) {
            $usuario->promoverTipoUsuario($materia->tipo_usuario_inicial_id);
        }

        // 2. Pasos de Crecimiento configurados "Al Iniciar" en la Materia
        $pasosIniciar = $materia->pasosCrecimiento()
            ->wherePivot('al_iniciar', 1)
            ->orderBy('materia_paso_crecimiento.indice')
            ->get();

        foreach ($pasosIniciar as $paso) {
            $estadoId = $paso->pivot->estado_paso_crecimiento_usuario_id ?? $paso->pivot->estado;
            if ($estadoId) {
                CrecimientoUsuario::procesarPaso(
                    userId: $usuario->id,
                    pasoCrecimientoId: $paso->id,
                    estadoObjetivoId: (int) $estadoId,
                    detalle: 'Asignado automáticamente al iniciar matrícula en: '.$materia->nombre,
                    fecha: now()
                );
            }
        }

        // 3. Tareas de Consolidación configuradas "Al Iniciar" en la Materia
        if (method_exists($materia, 'tareasIniciar')) {
            $tareasIniciar = $materia->tareasIniciar()
                ->orderBy('indice')
                ->get();

            foreach ($tareasIniciar as $tareaConfig) {
                if ($tareaConfig->tarea_consolidacion_id && $tareaConfig->estado_tarea_consolidacion_id) {
                    TareaConsolidacionUsuario::procesarTarea(
                        userId: $usuario->id,
                        tareaConsolidacionId: $tareaConfig->tarea_consolidacion_id,
                        estadoObjetivoId: $tareaConfig->estado_tarea_consolidacion_id,
                        observaciones: 'Asignado automáticamente al iniciar matrícula en: '.$materia->nombre,
                        fecha: now()
                    );
                }
            }
        }

        // 4. Si la materia pertenece a un Nivel, aplicar también efectos de inicio del Nivel si aplican
        if ($materia->nivel_id && $materia->nivel) {
            $nivel = $materia->nivel;

            if ($nivel->tipo_usuario_inicial_id) {
                $usuario->promoverTipoUsuario($nivel->tipo_usuario_inicial_id);
            }

            $pasosNivelIniciar = $nivel->pasosCrecimiento()
                ->wherePivot('al_iniciar', 1)
                ->orderBy('nivel_paso_crecimiento.indice')
                ->get();

            foreach ($pasosNivelIniciar as $pasoNivel) {
                $estadoId = $pasoNivel->pivot->estado_paso_crecimiento_usuario_id ?? $pasoNivel->pivot->estado;
                if ($estadoId) {
                    CrecimientoUsuario::procesarPaso(
                        userId: $usuario->id,
                        pasoCrecimientoId: $pasoNivel->id,
                        estadoObjetivoId: (int) $estadoId,
                        detalle: 'Asignado automáticamente (Nivel: '.$nivel->nombre.') al iniciar matrícula en: '.$materia->nombre,
                        fecha: now()
                    );
                }
            }
        }
    }
}
