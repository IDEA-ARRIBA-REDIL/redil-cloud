<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Asegúrate que esta línea esté presente

class HorarioBase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'horarios_base';

    protected $fillable = [
        'materia_id',
        'aula_id',
        'dia',
        'hora_inicio',
        'hora_fin',
        'capacidad',
        'capacidad_limite',
        'activo',
    ];

    // --- RELACIONES (Sin cambios) ---
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class);
    }

    public function horariosMateriaPeriodo(): HasMany
    {
        return $this->hasMany(HorarioMateriaPeriodo::class);
    }

    // --- ACCESSORS (Con la nueva sintaxis) ---

    /**
     * Define un accesor para el atributo "dia_semana".
     */
    protected function diaSemana(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->dia !== null ? (int) $this->dia : null) {
                0, 7 => 'Domingo',
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
                default => 'Día no definido',
            },
        );
    }

    /**
     * Define un accesor para el atributo "hora_inicio_formato".
     */
    protected function horaInicioFormato(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hora_inicio ? Carbon::parse($this->hora_inicio)->format('h:i A') : null,
        );
    }

    /**
     * Define un accesor para el atributo "hora_fin_formato".
     */
    protected function horaFinFormato(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hora_fin ? Carbon::parse($this->hora_fin)->format('h:i A') : null,
        );
    }
}
