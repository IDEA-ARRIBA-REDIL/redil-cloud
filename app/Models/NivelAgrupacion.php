<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NivelAgrupacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'niveles_agrupacion';

    protected $fillable = [
        'escuela_id',
        'nombre',
        'descripcion',
        'orden',
        'activo',
    ];

    /**
     * Relación con la Escuela.
     */
    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class);
    }

    /**
     * Configuración del Nivel.
     */
    // Relación con configuración del nivel
    public function configuracion(): HasOne
    {
        return $this->hasOne(NivelAgrupacionConfiguracion::class, 'nivel_agrupacion_id');
    }

    // Pasos de Crecimiento (Inicio y Culminación)
    public function pasosCrecimiento()
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'nivel_paso_crecimiento', 'nivel_agrupacion_id', 'paso_crecimiento_id')
            ->withPivot('estado', 'al_iniciar', 'estado_paso_crecimiento_usuario_id', 'indice');
    }

    // Prerrequisitos de Procesos (Pasos)
    public function procesosPrerrequisito()
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'nivel_proceso_prerrequisito', 'nivel_agrupacion_id', 'paso_crecimiento_id')
            ->withPivot('estado_proceso', 'estado_paso_crecimiento_usuario_id', 'indice');
    }

    // Prerrequisitos de otros Niveles
    public function nivelesPrerrequisito()
    {
        return $this->belongsToMany(NivelAgrupacion::class, 'nivel_prerrequisito', 'nivel_agrupacion_id', 'nivel_prerrequisito_id');
    }

    // Tareas Prerrequisito
    public function tareasRequisito(): HasMany
    {
        return $this->hasMany(NivelTareaRequisito::class, 'nivel_agrupacion_id');
    }

    // Tareas a Culminar
    public function tareasCulminadas(): HasMany
    {
        return $this->hasMany(NivelTareaCulminada::class, 'nivel_agrupacion_id');
    }

    /**
     * Materias asociadas al Nivel (Tabla pivote con modelo intermedio).
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'nivel_agrupacion_materias', 'nivel_agrupacion_id', 'materia_id')
                    ->withPivot(['id', 'es_obligatoria', 'orden', 'creditos'])
                    ->withTimestamps()
                    ->orderByPivot('orden');
    }

    /**
     * Relación directa con el modelo intermedio si se requiere manipularlo.
     */
    public function nivelMaterias(): HasMany
    {
        return $this->hasMany(NivelAgrupacionMateria::class, 'nivel_agrupacion_id');
    }

    /**
     * Matrículas en este nivel.
     */
    public function matriculas(): HasMany
    {
        return $this->hasMany(MatriculaNivel::class, 'nivel_agrupacion_id');
    }
}
