<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maestro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maestros';

    protected $fillable = [
        'user_id',
        'activo',
        'descripcion',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Los horarios de materia-periodo asignados a este maestro.
     */
    public function horariosMateriaPeriodo(): BelongsToMany
    {
        return $this->belongsToMany(
            HorarioMateriaPeriodo::class,
            'horario_materia_periodo_maestro', // Nombre de la tabla pivote
            'maestro_id',                      // Clave foránea de ESTE modelo (Maestro) en la tabla pivote
            'horario_materia_periodo_id'       // Clave foránea del OTRO modelo (HorarioMateriaPeriodo) en la tabla pivote
        )->withTimestamps();
    }
}
