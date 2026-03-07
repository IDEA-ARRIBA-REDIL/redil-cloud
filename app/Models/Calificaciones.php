<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificaciones extends Model
{
    use HasFactory;
    protected $table = 'calificaciones';
    protected $guarded = [];

    // dentro de la clase Calificaciones
    public function sistemaCalificacion(): BelongsTo
    {
        // Asegúrate que 'sistema_calificacion_id' es la clave foránea correcta
        return $this->belongsTo(SistemaCalificacion::class, 'sistema_calificacion_id');
    }
}
