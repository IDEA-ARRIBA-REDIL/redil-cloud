<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// <- IMPORTANTE

class RecursoAlumnoHorario extends Model
{
    use HasFactory;

    // Nombre de la tabla explícito
    protected $table = 'recurso_alumno_horario';

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'horario_materia_periodo_id',
        'nombre',
        'descripcion',
        'tipo',
        'link_externo',
        'link_youtube',
        'nombre_archivo',
        'ruta_archivo',
        'visible',
    ];

    // Relación inversa: Un recurso pertenece a un horario.
    public function horarioMateriaPeriodo()
    {
        return $this->belongsTo(HorarioMateriaPeriodo::class);
    }

    public function getArchivoUrlAttribute()
    {
        if ($this->ruta_archivo) {
            $directorio = "archivos/escuelas/recursos-horario/horario-{$this->horario_materia_periodo_id}";

            return tenant_asset("{$directorio}/{$this->ruta_archivo}");
        }

        return null;
    }
}
