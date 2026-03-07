<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// Es común que los modelos de tablas pivote extiendan de 'Pivot' en lugar de 'Model'
// si no necesitas 'incrementing' IDs, timestamps por defecto (aunque los puedes añadir), etc.
// Sin embargo, extender de 'Model' te da más flexibilidad si la necesitas.
// Si tu tabla pivote tiene su propio ID primario autoincremental, usa 'Model'.
// Si solo tiene las FKs y quizás otros datos, 'Pivot' es más semántico.
// Para este ejemplo, usaré 'Model' ya que es más versátil si decides añadir un ID propio a la tabla pivote.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Para las relaciones inversas si las necesitas desde aquí

class HorarioMateriaPeriodoMaestro extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'horario_materia_periodo_maestro';

    /**
     * Indica si el modelo debe tener timestamps.
     * Si tu tabla pivote tiene `created_at` y `updated_at` gestionados por Eloquent, mantenlo en true.
     * Si la relación BelongsToMany que usa esta tabla pivote ya especifica `withTimestamps()`,
     * esta propiedad aquí puede ser redundante para la gestión automática, pero no daña.
     *
     * @var bool
     */
    public $timestamps = true; // O false si no los usas en esta tabla o los manejas manualmente

    /**
     * The attributes that are mass assignable.
     *
     * Se usa si vas a crear o actualizar registros de esta tabla directamente usando Eloquent
     * (ej. `HorarioMateriaPeriodoMaestro::create([...])`).
     * Si solo manejas la relación a través de `attach()`, `sync()`, `detach()` en los modelos
     * Maestro u HorarioMateriaPeriodo, `$fillable` aquí podría no ser estrictamente necesario
     * para los campos que se llenan con `withPivot()`.
     *
     * Sin embargo, es buena práctica definirlos si la tabla tiene más que solo las FKs.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'maestro_id',
        'horario_materia_periodo_id',
    
        // Otros campos específicos de esta asignación maestro-horario
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Ejemplo: si 'rol_maestro' tuviera valores predefinidos y quisieras castearlo a un Enum (PHP 8.1+)
        // 'rol_maestro' => RolMaestroEnum::class,
    ];

    // --------------------------------------------------------------------------------
    // RELACIONES (Generalmente, desde un modelo pivote, son BelongsTo a los modelos que conecta)
    // --------------------------------------------------------------------------------

    /**
     * Obtener el maestro asociado con esta entrada de la tabla pivote.
     */
    public function maestro(): BelongsTo
    {
        return $this->belongsTo(Maestro::class, 'maestro_id');
    }

    /**
     * Obtener el horario de materia-periodo asociado con esta entrada de la tabla pivote.
     */
    public function horarioMateriaPeriodo(): BelongsTo
    {
        return $this->belongsTo(HorarioMateriaPeriodo::class, 'horario_materia_periodo_id');
    }

    // --------------------------------------------------------------------------------
    // LÓGICA ADICIONAL (Si es necesaria para este modelo pivote)
    // --------------------------------------------------------------------------------
    // Por ejemplo, si 'rol_maestro' tiene significados específicos
    // public function esTitular(): bool
    // {
    //     return $this->rol_maestro === 'titular';
    // }
}