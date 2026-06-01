<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumnoRespuestaItem extends Model
{
    use HasFactory;

    protected $table = 'alumno_respuesta_items';

    protected $fillable = [
        'user_id',
        'item_corte_materia_periodo_id',
        // 'matricula_horario_materia_periodo_id', // Si la incluyes en la migración
        'nota_obtenida',
        'respuesta_alumno',
        'enlace_documento_alumno',
        // 'ruta_documento_alumno',
        'maestro_calificador_id',
        'fecha_calificacion',
        'observaciones_maestro',
    ];

    protected $casts = [
        'nota_obtenida' => 'decimal:2',
        'fecha_calificacion' => 'datetime',
    ];

    public function alumno(): BelongsTo // 'alumno' es más semántico que 'user' aquí
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function itemCalificado(): BelongsTo // 'itemCalificado' o 'itemCorteMateriaPeriodo'
    {
        return $this->belongsTo(ItemCorteMateriaPeriodo::class, 'item_corte_materia_periodo_id');
    }

    public function maestroCalificador(): BelongsTo
    {
        return $this->belongsTo(Maestro::class, 'maestro_calificador_id');
    }

    protected function archivoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Si no hay nombre de archivo en la base de datos, no hay nada que hacer.
                if (! $this->enlace_documento_alumno) {
                    return null;
                }

                /*
                 * OPTIMIZACIÓN (Fix #6):
                 * Antes este accesor hacía dos lazy loads silenciosos:
                 *   1. Configuracion::first() — una query a BD que era innecesaria porque la ruta
                 *      se construye solo con el periodo_id, no con datos de configuración.
                 *   2. $this->itemCalificado y $this->itemCalificado->materiaPeriodo se cargaban
                 *      de forma lazy (una query por cada fila de la lista de calificaciones).
                 *
                 * La solución:
                 *   - Se eliminó Configuracion::first() completamente (no se usaba para construir la URL).
                 *   - Se usa loadMissing() para cargar las relaciones anidadas eficientemente si no
                 *     están ya en memoria (ideal cuando se llama sobre una colección en lugar de uno a uno).
                 *     Al hacer ->with(['itemCalificado.materiaPeriodo']) en la consulta del maestro, este
                 *     loadMissing() no genera queries adicionales — ya está en caché.
                 */
                $this->loadMissing('itemCalificado.materiaPeriodo');

                $item = $this->itemCalificado;
                if (! $item || ! $item->materiaPeriodo) {
                    return null;
                }

                $periodoId = $item->materiaPeriodo->periodo_id;

                // 3. Reconstruimos la ruta relativa TAL COMO la guardamos (sin barra inicial para tenant_asset)
                $rutaRelativa = "archivos/escuelas/periodo-{$periodoId}/respuestas/{$this->enlace_documento_alumno}";

                // 4. Usamos el helper oficial de Tenancy para generar la URL pública del tenant
                return tenant_asset($rutaRelativa);
            },
        );
    }

    // Si añades matricula_horario_materia_periodo_id:
    // public function estadoAcademico(): BelongsTo
    // {
    //     return $this->belongsTo(MatriculaHorarioMateriaPeriodo::class, 'matricula_horario_materia_periodo_id');
    // }
}
