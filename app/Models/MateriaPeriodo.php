<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Ya no necesitas BelongsToMany para los cortes aquí

class MateriaPeriodo extends Model
{
    use HasFactory;
    protected $table = 'materia_periodo';
    protected $fillable = [
        'materia_id',
        'periodo_id',
        'maestro_id',
        'habilitar_calificaciones',
        'habilitar_asistencias',
        'asistencias_minimas',
        'auto_matricula',
        'estado_auto_matricula',
        'finalizado',
        'descripcion',
        'cantidad_inasistencias_alerta',
        'habilitar_alerta_inasistencias',
        'habilitar_traslado',
    ];

    protected $casts = [
         'habilitar_calificaciones' => 'boolean',
         'habilitar_asistencias' => 'boolean',
         'auto_matricula' => 'boolean',
         'finalizado' => 'boolean',
         'habilitar_alerta_inasistencias' => 'boolean',
         'habilitar_traslado' => 'boolean',
    ];

    // --- Relaciones existentes ---
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    public function actividadCategoria(): HasMany // Asumiendo que ActividadCategoria existe
    {
         // Asegúrate que la tabla actividad_categorias tiene materia_periodo_id
        return $this->hasMany(ActividadCategoria::class, 'materia_periodo_id');
    }

    public function maestro() // Mantén tu lógica actual
    {
        // return $this->belongsTo(Maestro::class);
        return null;
    }
    // --- Fin Relaciones existentes ---


    /**
     * Get the item instances for the materia periodo.
     * Define la relación uno a muchos con ItemCorteMateriaPeriodo.
     */
    public function itemInstancias(): HasMany
    {
        // Usamos el nombre del modelo que creamos para las instancias
        return $this->hasMany(ItemCorteMateriaPeriodo::class);
    }

     /**
      * Get the associated HorarioMateriaPeriodo records.
      * Define la relación uno a muchos con HorarioMateriaPeriodo.
      */
     public function horariosMateriaPeriodo(): HasMany
     {
         return $this->hasMany(HorarioMateriaPeriodo::class);
     }

      /**
     * Obtener todos los reportes de asistencia de todos los horarios
     * asociados a esta MateriaPeriodo.
     */
    public function reportesAsistenciaDeMateria(): HasManyThrough
    {
        return $this->hasManyThrough(
            ReporteAsistenciaEscuela::class, // <--- NOMBRE DEL MODELO CAMBIADO AQUÍ
            HorarioMateriaPeriodo::class,
            'materia_periodo_id',         // Clave foránea en HorarioMateriaPeriodo
            'horario_materia_periodo_id', // Clave foránea en ReporteAsistenciaEscuela
            'id',                         // Clave local en MateriaPeriodo
            'id'                          // Clave local en HorarioMateriaPeriodo
        );
    }

    // Los métodos contarAsistenciasTotalesAlumno y contarInasistenciasTotalesAlumno
    // usarán automáticamente la relación actualizada.
    public function contarAsistenciasTotalesAlumno(int $alumnoUserId): int
    {
        return $this->reportesAsistenciaDeMateria()
                    ->where('alumno_user_id', $alumnoUserId)
                    ->where('asistio', true)
                    ->count();
    }

    public function contarInasistenciasTotalesAlumno(int $alumnoUserId): int
    {
        return $this->reportesAsistenciaDeMateria()
                    ->where('alumno_user_id', $alumnoUserId)
                    ->where('asistio', false)
                    ->count();
    }
}
