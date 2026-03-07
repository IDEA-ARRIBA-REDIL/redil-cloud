<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SistemaCalificacion extends Model
{
    use HasFactory;
    protected $table = 'sistema_calificaciones';
    protected $fillable = [
        'nombre',
        'es_numerico',
    ];

    /**
     * Un sistema de calificaciones tiene muchos periodos.
     */
    public function periodos(): HasMany
    {
        return $this->hasMany(Periodo::class);
    }
    public function calificaciones(): HasMany
    {
        // Asegúrate que 'sistema_calificacion_id' es la clave foránea en la tabla 'calificaciones'
        // y usa el nombre correcto del modelo ('Calificacion' o 'Calificaciones')
        return $this->hasMany(Calificaciones::class, 'sistema_calificacion_id');
    }
}