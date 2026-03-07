<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriaAprobadaUsuario extends Model
{
    use HasFactory;

    protected $table = 'materias_aprobada_usuario';

    /**
     * Los atributos que se pueden asignar de forma masiva.
     */
    protected $fillable = [
        'user_id',
        'materia_id',
        'materia_periodo_id',
        'periodo_id',
        'aprobado',
        'nota_final',
        'total_asistencias',
        'motivo_reprobacion',
        'es_homologacion',
        'observacion_homologacion',
        'sede_id',
        'fecha_homologacion',
        'homologado_por_user_id',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     */
    protected $casts = [
        'aprobado' => 'boolean',
        'nota_final' => 'decimal:2',
        'total_asistencias' => 'integer',
    ];

    // -----------------------------------------------------------------
    // RELACIONES
    // -----------------------------------------------------------------

    /**
     * Obtiene el usuario (alumno) al que pertenece este registro de resultado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtiene la materia base a la que se refiere este resultado.
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    /**
     * Obtiene la instancia de la materia en el periodo específico.
     */
    public function materiaPeriodo(): BelongsTo
    {
        return $this->belongsTo(MateriaPeriodo::class, 'materia_periodo_id');
    }

    /**
     * Obtiene el periodo al que pertenece este resultado.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }
}
