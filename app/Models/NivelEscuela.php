<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NivelEscuela extends Model
{
    protected $table = 'niveles_escuelas';

    /**
     * Relación uno a muchos con Materia.
     * Un nivel puede tener muchas materias.
     */
    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'nivel_id'); // Asegúrate que la llave foránea en 'materias' es 'nivel_id'
    }

    /**
     * Relación muchos a uno con Escuela.
     * Un nivel pertenece a una escuela.
     */
    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'escuela_id');
    }

    /**
     * Relación muchos a muchos para prerrequisitos de nivel.
     * Un nivel puede tener muchos niveles como prerrequisitos,
     * y a su vez puede ser prerrequisito para otros niveles.
     */
    public function prerrequisitos(): BelongsToMany
    {
        return $this->belongsToMany(
            NivelEscuela::class,                // Modelo relacionado
            'nivel_escuela_prerrequisitos',      // Tabla pivote
            'nivel_escuela_id_inicial',         // Llave foránea de este modelo
            'nivel_escuela_requerido_id'        // Llave foránea del modelo relacionado
        )->withPivot('escuela_id')->withTimestamps();
    }

    /**
     * Relación muchos a muchos con PasoCrecimiento (para etapas del nivel).
     * Un nivel puede tener asociados varios pasos de crecimiento.
     */
    public function pasosCrecimiento(): BelongsToMany
    {
        return $this->belongsToMany(
            PasoCrecimiento::class,
            'nivel_paso_crecimiento',
            'nivel_id',
            'paso_crecimiento_id'
        )
            ->withPivot(['id', 'al_iniciar', 'estado', 'indice', 'estado_paso_crecimiento_usuario_id'])
            ->withTimestamps();
    }

    /**
     * Relación muchos a muchos con PasoCrecimiento (para procesos prerrequisito).
     * Un nivel puede requerir completar ciertos pasos de crecimiento antes de iniciarlo.
     */
    public function procesosPrerrequisito(): BelongsToMany
    {
        return $this->belongsToMany(
            PasoCrecimiento::class,
            'nivel_proceso_prerrequisito',
            'nivel_id',
            'paso_crecimiento_id'
        )
            ->withPivot(['id', 'estado_proceso', 'indice', 'estado_paso_crecimiento_usuario_id'])
            ->withTimestamps();
    }

    public function tareasRequisito(): HasMany
    {
        return $this->hasMany(NivelTareaRequisito::class, 'nivel_id');
    }

    public function tareasCulminadas(): HasMany
    {
        return $this->hasMany(NivelTareaCulminada::class, 'nivel_id');
    }

    /**
     * Obtiene las materias asociadas a este nivel (modo unificado).
     */
    public function materiasAgrupadas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Materia::class, 'nivel_id');
    }

    /**
     * Obtiene las matriculas realizadas en este nivel.
     */
    public function matriculas(): HasMany
    {
        return $this->hasMany(MatriculaNivel::class, 'nivel_escuela_id');
    }

    /**
     * Obtiene los periodos asociados a la escuela de este nivel.
     */
    public function periodos(): HasMany
    {
        return $this->hasMany(Periodo::class, 'escuela_id', 'escuela_id');
    }

    public function tipoUsuarioInicial(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_inicial_id');
    }

    public function tipoUsuarioObjetivo(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_objetivo_id');
    }

    /**
     * Accesor para obtener la URL pública de la portada.
     */
    public function getPortadaUrlAttribute(): ?string
    {
        if ($this->portada && $this->portada !== 'default.png') {
            return tenant_asset('archivos/escuelas/niveles/'.$this->portada);
        }

        return null;
    }
}
