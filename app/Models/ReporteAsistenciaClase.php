<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReporteAsistenciaClase extends Model
{
    use HasFactory;

    protected $table = 'reportes_asistencia_clase';

    protected $fillable = [
        'horario_materia_periodo_id', 'fecha_clase_reportada',
        'observaciones_generales', 'reportado_por_user_id', 'estado_reporte',
    ];

    protected $casts = ['fecha_clase_reportada' => 'date:Y-m-d'];

    public function horarioMateriaPeriodo(): BelongsTo
    {
        return $this->belongsTo(HorarioMateriaPeriodo::class, 'horario_materia_periodo_id');
    }

    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por_user_id');
    }

    public function detallesAsistencia(): HasMany
    {
        // Corregido para apuntar al modelo correcto que representa la tabla de detalles
        return $this->hasMany(ReporteAsistenciaAlumnos::class, 'reporte_asistencia_clase_id');
    }

    public function getPresentesCountAttribute(): int
    {
        return $this->detallesAsistencia()->where('asistio', true)->count();
    }

    public function getAusentesCountAttribute(): int
    {
        return $this->detallesAsistencia()->where('asistio', false)->count();
    }

    public function getTotalAlumnosReportadosAttribute(): int
    {
        return $this->detallesAsistencia()->count();
    }

    public function permiteEdicionPorFecha(?User $usuario): bool
    {
        if (! $usuario) {
            return false;
        }

        if ($usuario->hasAnyPermission([
            'escuelas.calificar_cualquier_fecha',
            'escuelas.reportar_cualquier_fecha',
            'escuelas.reportar_asistencia_cualquier_dia',
        ])) {
            return true;
        }

        $this->loadMissing('horarioMateriaPeriodo.materiaPeriodo.materia');
        $datosMateria = $this->horarioMateriaPeriodo?->materiaPeriodo?->materia;
        if (! $datosMateria) {
            return false;
        }

        $fechaActualSoloFecha = Carbon::now()->startOfDay();
        $fechaClaseReportadaCarbon = Carbon::parse($this->fecha_clase_reportada)->startOfDay();

        if ($datosMateria->tiene_dia_limite && isset($datosMateria->dia_limite_reporte)) {
            // Escenario A: Con día límite semanal
            $diaClaseReportadaNum = $fechaClaseReportadaCarbon->dayOfWeek;
            $diaLimiteConfiguradoMateria = (int) $datosMateria->dia_limite_reporte;
            $diasParaAlcanzarDiaLimite = ($diaLimiteConfiguradoMateria - $diaClaseReportadaNum + 7) % 7;
            $fechaTopeParaEdicion = $fechaClaseReportadaCarbon->copy()->addDays($diasParaAlcanzarDiaLimite);

            return $fechaActualSoloFecha->lte($fechaTopeParaEdicion); // Puede editar si hoy <= fecha tope
        } elseif (! $datosMateria->tiene_dia_limite && isset($datosMateria->dias_plazo_reporte)) {
            // Escenario B: Con días de plazo
            $diasPlazo = (int) $datosMateria->dias_plazo_reporte;
            $fechaTopeParaEdicion = $fechaClaseReportadaCarbon->copy()->addDays($diasPlazo);

            return $fechaActualSoloFecha->lte($fechaTopeParaEdicion); // Puede editar si hoy <= fecha tope
        }

        return false; // Por defecto, no se puede editar.
    }
}
