<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NivelAgrupacionConfiguracion extends Model
{
    use HasFactory;

    protected $table = 'niveles_agrupacion_configuracion';

    protected $fillable = [
        'nivel_agrupacion_id',
        'asistencias_minimas',
        'max_reportes_permitidos',
        'dias_alerta_inasistencia',
        'requiere_aprobacion_total',
        'minimo_materias_aprobadas',
        'habilitar_clases_espejo',
        'bloquear_matricula_extemporanea',

        // Nuevos campos de configuración avanzada
        'habilitar_calificaciones',
        'habilitar_asistencias',
        'habilitar_inasistencias',
        'habilitar_alerta_inasistencias',
        'limite_reportes',
        'asistencias_minima_alerta',
        'cantidad_inasistencias_alerta',
        'dia_limite_habilitado',
        'dia_limite_reporte',
        'cantidad_reportes_semana',
        'dias_plazo_reporte',
        'habilitar_traslado',
        'caracter_obligatorio',
        'tipo_usuario_objetivo_id'
    ];

    protected $casts = [
        'requiere_aprobacion_total' => 'boolean',
        'habilitar_clases_espejo' => 'boolean',
        'bloquear_matricula_extemporanea' => 'boolean',
    ];

    /**
     * Relación inversa con el Nivel.
     */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelAgrupacion::class, 'nivel_agrupacion_id');
    }
}
