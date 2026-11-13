<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// Asegúrate de importar HasMany si no estaba ya

class Materia extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable = [
        'nombre',
        'creditos',
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
        'tipo_usuario_inicial_id', // NUEVO INICIAL
        'orden',
        'limite_reporte_asistencias',
        'dia_limite_reporte',
        'dias_plazo_reporte',
        'tiene_dia_limite',
        'cantidad_limite_reportes_semana',
    ];

    protected $casts = [
        'creditos' => 'integer',
        'orden' => 'integer',
        'habilitar_calificaciones' => 'boolean',
        'habilitar_asistencias' => 'boolean',
        'caracter_obligatorio' => 'boolean',
        'tiene_dia_limite' => 'boolean',
        'limite_reporte_asistencias' => 'integer',
        'dia_limite_reporte' => 'integer',
        'dias_plazo_reporte' => 'integer',
        'cantidad_limite_reportes_semana' => 'integer',
        'asistencias_minimas' => 'integer',
        'asistencias_minima_alerta' => 'integer',
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

    public function tipoUsuarioInicial(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_inicial_id');
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

    public function tareasIniciar(): HasMany
    {
        return $this->hasMany(MateriaTareaIniciar::class, 'materia_id');
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

    // --- Lógica de herencia de configuración del Nivel ---

    /**
     * Determina si la materia debe heredar configuración de un nivel.
     */
    public function tieneNivelAsociado(): bool
    {
        return ! empty($this->nivel_id);
    }

    /**
     * Obtiene un valor de configuración, heredando del nivel si aplica.
     */
    public function getConfigProp($property, $value)
    {
        if ($this->tieneNivelAsociado() && $this->nivel) {
            return $this->nivel->{$property};
        }

        return $value;
    }

    // Accessors útiles para la UI
    public function getHabilitarAsistenciasAttribute($value)
    {
        return $this->getConfigProp('habilitar_asistencias', $value);
    }

    public function getHabilitarCalificacionesAttribute($value)
    {
        return $this->getConfigProp('habilitar_calificaciones', $value);
    }

    public function getHabilitarInasistenciasAttribute($value)
    {
        return $this->getConfigProp('habilitar_inasistencias', $value);
    }

    public function getHabilitarTrasladoAttribute($value)
    {
        return $this->getConfigProp('habilitar_traslado', $value);
    }

    public function getCaracterObligatorioAttribute($value)
    {
        return $this->getConfigProp('caracter_obligatorio', $value);
    }

    public function getAsistenciasMinimasAttribute($value)
    {
        return $this->getConfigProp('asistencias_minimas', $value);
    }

    public function getAsistenciasMinimaAlertaAttribute($value)
    {
        return $this->getConfigProp('asistencias_minima_alerta', $value);
    }

    public function getLimiteReporteAsistenciasAttribute($value)
    {
        return $this->getConfigProp('limite_reporte_asistencias', $value);
    }

    public function getDiaLimiteReporteAttribute($value)
    {
        return $this->getConfigProp('dia_limite_reporte', $value);
    }

    public function getDiasPlazoReporteAttribute($value)
    {
        return $this->getConfigProp('dias_plazo_reporte', $value);
    }

    public function getTieneDiaLimiteAttribute($value)
    {
        return (bool) $this->getConfigProp('tiene_dia_limite', $value);
    }

    public function getCantidadLimiteReportesSemanaAttribute($value)
    {
        return $this->getConfigProp('cantidad_limite_reportes_semana', $value);
    }

    /**
     * Obtiene la cantidad máxima de reportes permitidos por semana.
     * Si tiene día límite activo o la cantidad configurada es <= 0, el límite efectivo es 1 por semana.
     */
    public function obtenerLimiteReportesSemana(): int
    {
        $cantidadConfigurada = (int) $this->cantidad_limite_reportes_semana;

        return $cantidadConfigurada > 0 ? $cantidadConfigurada : 1;
    }

    /**
     * Accesor para obtener la URL pública de la portada.
     */
    public function getPortadaUrlAttribute(): ?string
    {
        if ($this->portada && $this->portada !== 'default.png') {
            return tenant_asset('archivos/escuelas/materias/'.$this->portada);
        }

        return null;
    }
}
