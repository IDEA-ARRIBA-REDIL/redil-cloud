<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HorarioMateriaPeriodo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'horarios_materia_periodo'; // Asegúrate que este sea el nombre correcto de tu tabla

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'materia_periodo_id',
        'horario_base_id',
        'habilitado',             // Si este horario específico está activo o no
        'fecha_inicio_habilitado',// Fecha desde la cual está habilitado
        'fecha_fin_habilitado',   // Fecha hasta la cual está habilitado
        'cupos_disponibles',      // Podrías añadir un campo para gestionar cupos si es necesario
        // Otros campos específicos que puedas necesitar para esta instancia de horario
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'habilitado' => 'boolean',
        'fecha_inicio_habilitado' => 'date',
        'fecha_fin_habilitado' => 'date',
        'cupos_disponibles' => 'integer',
    ];

    // --------------------------------------------------------------------------------
    // RELACIONES FUNDAMENTALES (Definen qué es este HorarioMateriaPeriodo)
    // --------------------------------------------------------------------------------

    /**
     * La materia específica en un periodo a la que pertenece este horario.
     * Ejemplo: "Cálculo I - Semestre 2025-1"
     */
    public function materiaPeriodo(): BelongsTo
    {
        return $this->belongsTo(MateriaPeriodo::class, 'materia_periodo_id');
    }

    /**
     * El horario base (día, hora, aula) que utiliza esta instancia de horario.
     * Ejemplo: "Lunes 7-9 AM en Aula A101"
     */
    public function horarioBase(): BelongsTo
    {
        return $this->belongsTo(HorarioBase::class, 'horario_base_id');
    }

    // --------------------------------------------------------------------------------
    // RELACIONES CON MAESTROS
    // --------------------------------------------------------------------------------

    /**
     * Los maestros asignados para impartir clase en este horario específico.
     * Un horario puede tener varios maestros (ej. titular, auxiliar).
     */
   public function maestros(): BelongsToMany
{
    return $this->belongsToMany(
        Maestro::class,
        'horario_materia_periodo_maestro', // Nombre de la tabla pivote
        'horario_materia_periodo_id',      // Clave foránea de ESTE modelo (HorarioMateriaPeriodo) en la tabla pivote
        'maestro_id'                       // Clave foránea del OTRO modelo (Maestro) en la tabla pivote
    )->withTimestamps();
}

    public function instanciasMatriculaAlumnos(): HasMany
    {
        return $this->hasMany(MatriculaHorarioMateriaPeriodo::class, 'horario_materia_periodo_id');
    }

    // --------------------------------------------------------------------------------
    // RELACIONES CON ÍTEMS DE EVALUACIÓN
    // --------------------------------------------------------------------------------

    /**
     * Los ítems de evaluación específicos (instancias) que se aplicarán
     * a los alumnos matriculados en ESTE horario/grupo.
     * Estos se crean a partir de ItemPlantilla.
     */
    public function itemsEvaluacion(): HasMany
    {
        // Asumiendo que la FK en 'item_corte_materia_periodo' que vincula al horario es 'horario_materia_periodo_id'
        return $this->hasMany(ItemCorteMateriaPeriodo::class, 'horario_materia_periodo_id');
    }


    // --------------------------------------------------------------------------------
    // ACCESORES Y MÉTODOS AUXILIARES (Opcional, pero útil)
    // --------------------------------------------------------------------------------

    /**
     * Accesor para obtener la capacidad actual del HorarioBase asociado.
     * (Ejemplo de cómo acceder a información del HorarioBase)
     * Asegúrate que `HorarioBase` tenga los campos `ampliar_cupos_limite`, `capacidad_limite`, `capacidad`
     * y que `ampliar_cupos_limite` esté en $casts de HorarioBase como booleano.
     */
    public function getCapacidadDefinidaAttribute(): ?int
    {
        if ($this->horarioBase) {
            // Ejemplo: Si HorarioBase tiene un atributo 'ampliar_cupos_limite' (booleano)
            // y 'capacidad' y 'capacidad_limite'
            // Esta lógica es un ejemplo, ajústala a tus campos reales en HorarioBase
            // if (property_exists($this->horarioBase, 'ampliar_cupos_limite') && $this->horarioBase->ampliar_cupos_limite) {
            //    return $this->horarioBase->capacidad_limite;
            // }
            return $this->horarioBase->capacidad; // Accede a la capacidad normal del horario base
        }
        return null;
    }

    /**
     * Método para obtener las sedes donde se imparte este horario.
     * (Reconstrucción del método estático que tenías, como un método de instancia si es más útil)
     * O puede seguir siendo estático si lo prefieres.
     */
    public function getSedeAttribute() // Accesor para $this->sede
    {
        if ($this->horarioBase && $this->horarioBase->aula && $this->horarioBase->aula->sede) {
            return $this->horarioBase->aula->sede;
        }
        return null;
    }

    /**
     * Método estático para obtener sedes únicas para una MateriaPeriodo específica.
     * (Manteniendo el original si esa es la necesidad)
     */
    public static function getSedesForMateriaPeriodo(int $materiaPeriodoId)
    {
        $horarios = self::with('horarioBase.aula.sede')
            ->where('materia_periodo_id', $materiaPeriodoId)
            ->get();

        return $horarios->map(function ($horario) {
            if ($horario->horarioBase && $horario->horarioBase->aula && $horario->horarioBase->aula->sede) {
                return $horario->horarioBase->aula->sede;
            }
            return null;
        })
        ->filter()
        ->unique('id')
        ->values();
    }

    public function matriculasDeAlumnos(): HasMany
{
    return $this->hasMany(Matricula::class, 'horario_materia_periodo_id');
}

    /**
     * Los usuarios (alumnos) que están matriculados en este horario específico.
     */
    public function alumnosMatriculados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'matriculas', 'horario_materia_periodo_id', 'user_id')
                    ->withTimestamps()
                    ->withPivot(['id', 'periodo_id', 'pago_id', 'fecha_matricula', 'estado_matricula', 'valor_matricula', 'referencia_pago', 'valor_pagado', 'fecha_pago', 'metodo_pago'])
                    ->as('detalles_matricula');
    }

 public function reportesAsistencia(): HasMany
    {
        //               NOMBRE DEL MODELO CAMBIADO AQUÍ 👇
        return $this->hasMany(ReporteAsistenciaClase::class, 'horario_materia_periodo_id');
    }

    // El método asistenciasDeAlumno usará automáticamente la relación actualizada.
    public function asistenciasDeAlumno(int $alumnoUserId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->reportesAsistencia()->where('alumno_user_id', $alumnoUserId)->get();
    }

}