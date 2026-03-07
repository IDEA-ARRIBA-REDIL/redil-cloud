<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteAsistenciaAlumnos extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Corresponde a la tabla de detalles de asistencia.
     *
     * @var string
     */
    protected $table = 'reportes_asistencia_alumnos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reporte_asistencia_clase_id', // FK a la tabla maestra 'reportes_asistencia_clase'
        'user_id',                     // FK al alumno (de la tabla 'users')
        'asistio',
        'motivo_inasistencia_id',
        'observaciones_alumno',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'asistio' => 'boolean',
        // No hay 'fecha_clase_reportada' aquí, ya que está en la tabla maestra 'reportes_asistencia_clase'
    ];

    /**
     * Obtiene el reporte de clase general (la cabecera) al que pertenece este detalle de asistencia.
     */
    public function reporteClase(): BelongsTo
    {
        return $this->belongsTo(ReporteAsistenciaClase::class, 'reporte_asistencia_clase_id');
    }

    /**
     * Obtiene el alumno (usuario) al que se le reporta esta asistencia.
     */
    public function alumno(): BelongsTo
    {
        // La clave foránea en la tabla 'reportes_asistencia_alumnos' es 'user_id' según tu última migración.
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtiene el motivo de la inasistencia (si aplica).
     */
    public function motivoInasistencia(): BelongsTo
    {
        // Asegúrate que el modelo MotivoInasistencia exista y esté en el namespace correcto
        return $this->belongsTo(MotivoInasistencia::class, 'motivo_inasistencia_id');
    }
}