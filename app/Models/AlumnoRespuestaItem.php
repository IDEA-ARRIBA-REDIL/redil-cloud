<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use App\Models\Configuracion;

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
                if (!$this->enlace_documento_alumno) {
                    return null;
                }

                // 1. Obtenemos la configuración que contiene la carpeta base (ej: 'iglesia1')
                $configuracion = Configuracion::find(1);

                // 2. Navegamos por las relaciones para obtener el ID del periodo
                $item = ItemCorteMateriaPeriodo::find($this->itemCalificado->id);
                $periodoId = $item->materiaPeriodo->periodo_id;



                // 3. Reconstruimos la ruta relativa TAL COMO la guardamos
                $rutaRelativa = "{$configuracion->ruta_almacenamiento}/archivos/escuelas/periodo-" . $periodoId . "/respuestas/{$this->enlace_documento_alumno}";

                // 4. Usamos el helper de Laravel para generar la URL pública completa
                return Storage::disk('public')->url($rutaRelativa);
            },
        );
    }

    // Si añades matricula_horario_materia_periodo_id:
    // public function estadoAcademico(): BelongsTo
    // {
    //     return $this->belongsTo(MatriculaHorarioMateriaPeriodo::class, 'matricula_horario_materia_periodo_id');
    // }
}
