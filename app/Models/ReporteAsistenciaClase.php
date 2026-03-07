<?php

namespace App\Models;

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
}