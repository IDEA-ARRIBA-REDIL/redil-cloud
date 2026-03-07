<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// Asegúrate de importar HasMany si no estaba ya

class Materia extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable = [
        'nombre',
        'nivel_id', // Considera cambiar a nivel_escuela_id si usas NivelEscuela
        'escuela_id',
        'habilitar_calificaciones',
        'habilitar_asistencias',
        'asistencias_minimas',
        'descripcion',
        'habilitar_alerta_inasistencias', // Asegúrate que este campo existe en la migración
        'habilitar_traslado',
        'caracter_obligatorio',
        'portada', // Añadido si usas $fillable estricto
        'asistencias_minima_alerta', // Añadido si usas $fillable estricto
        'habilitar_inasistencias', // Añadido si usas $fillable estricto
        'tipo_usuario_objetivo_id', // NUEVO
    ];

    // --- Relaciones existentes ---
    public function prerrequisitosMaterias()
    {
        return $this->belongsToMany(Materia::class, 'materia_prerrequisito', 'materia_id', 'materia_prerrequisito_id');
    }

    public function nivel() // Asumiendo que NivelEscuela es el modelo correcto
    {
        // Si nivel_id en la tabla materias realmente apunta a la tabla niveles, usa Nivel::class
        // Si apunta a una tabla intermedia nivel_escuela, usa NivelEscuela::class
        return $this->belongsTo(NivelEscuela::class, 'nivel_id'); // Ajusta NivelEscuela::class si es necesario
    }

    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class);
    }

    public function tipoUsuarioObjetivo(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_objetivo_id');
    }

    public function materiasPeriodo(): HasMany
    {
        return $this->hasMany(MateriaPeriodo::class);
    }

    public function procesosPrerrequisito()
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'materia_proceso_prerrequisito', 'materia_id', 'paso_crecimiento_id')
            ->withPivot('estado_proceso', 'estado_paso_crecimiento_usuario_id', 'indice');
    }

    public function pasosCrecimiento()
    {
        // materia_paso_crecimiento maneja tanto inicio como culminación
        return $this->belongsToMany(PasoCrecimiento::class, 'materia_paso_crecimiento', 'materia_id', 'paso_crecimiento_id')
            ->withPivot('estado', 'al_iniciar', 'estado_paso_crecimiento_usuario_id', 'indice');
    }

    public function tareasRequisito(): HasMany
    {
        return $this->hasMany(MateriaTareaRequisito::class, 'materia_id');
    }

    public function tareasCulminadas(): HasMany
    {
        return $this->hasMany(MateriaTareaCulminada::class, 'materia_id');
    }
    // --- Fin Relaciones existentes ---


    /**
     * Get the item templates for the materia.
     * Define la relación uno a muchos con ItemPlantilla.
     */
    public function itemPlantillas(): HasMany
    {
        return $this->hasMany(ItemPlantilla::class);
    }
}
