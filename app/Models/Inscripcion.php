<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany;   // Importar HasMany
use Illuminate\Database\Eloquent\SoftDeletes;

class Inscripcion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inscripciones';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // Se usa user_id según tu migración
        'actividad_categoria_id',
        'compra_id',
        'fecha',
        'estado',
        'json_campos_adicionales',
        'persona_externa_id',
        'fecha_pago',
        'email',                  // <-- ASEGÚRATE DE QUE ESTÉ AQUÍ
        'nombre_inscrito',
        'inscripcion_asociada',
        'limite_invitados'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha' => 'date',
        'fecha_pago' => 'date',
    ];

    /**
     * Obtiene el usuario (asistente) al que pertenece la inscripción.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtiene la categoría de la actividad a la que pertenece la inscripción.
     * (Nombre corregido a la convención de Laravel para que funcione con with('categoria'))
     */
    public function categoriaActividad(): BelongsTo
    {
        return $this->belongsTo(ActividadCategoria::class, 'actividad_categoria_id');
    }

    /**
     * Obtiene la compra a la que pertenece la inscripción.
     */
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    /**
     * MÉTODO NUEVO:
     * Define la relación donde una Inscripción tiene muchas Respuestas.
     */
    public function respuestas(): HasMany
    {
        // Esto le dice a Laravel que busque en la tabla 'respuestas_formulario_elemento_compra'
        // todos los registros donde la columna 'inscripcion_id' coincida con el 'id' de esta inscripción.
        return $this->hasMany(RespuestaElementoFormulario::class, 'inscripcion_id');
    }

    /**
     * Define la relación: Una inscripción tiene muchos registros de asistencia.
     */

    public function asistencias()
    {
        return $this->hasMany(ActividadAsistenciaInscripcion::class, 'inscripcion_id');
    }

    // --- INICIO DEL CÓDIGO AÑADIDO ---

    /**
     * Obtiene todas las inscripciones de invitados asociadas a esta inscripción principal.
     */
    public function invitados()
    {
        // Una inscripción principal puede tener muchas inscripciones de invitados.
        return $this->hasMany(Inscripcion::class, 'inscripcion_asociada', 'id');
    }

    /**
     * Obtiene la inscripción principal a la que este invitado está asociado.
     */
    public function inscripcionPrincipal()
    {
        // Una inscripción de invitado pertenece a una inscripción principal.
        return $this->belongsTo(Inscripcion::class, 'inscripcion_asociada', 'id');
    }
}
