<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class NivelEscuela extends Model
{
    protected $table = 'niveles_escuelas';

   
    /**
     * Relación uno a muchos con Materia.
     * Un nivel puede tener muchas materias.
     */
    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'nivel_id'); // Asegúrate que la llave foránea en 'materias' es 'nivel_id'
    }

    /**
     * Relación muchos a uno con Escuela.
     * Un nivel pertenece a una escuela.
     */
    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'escuela_id');
    }

    /**
     * Relación muchos a muchos para prerrequisitos de nivel.
     * Un nivel puede tener muchos niveles como prerrequisitos,
     * y a su vez puede ser prerrequisito para otros niveles.
     */
    public function prerrequisitos(): BelongsToMany
    {
        return $this->belongsToMany(
            NivelEscuela::class,         // Modelo relacionado
            'prerequisito_niveles',       // Tabla pivote
            'nivel_id',                  // Llave foránea de este modelo en la tabla pivote
            'nivel_requerido_id'     // Llave foránea del modelo relacionado en la tabla pivote
        );
    }

     /**
     * Relación muchos a muchos con PasoCrecimiento (para etapas del nivel).
     * Un nivel puede tener asociados varios pasos de crecimiento.
     */
    public function pasosCrecimiento(): BelongsToMany
    {
        return $this->belongsToMany(
                PasoCrecimiento::class,         // Modelo relacionado
                'nivel_paso_crecimiento',       // Tabla pivote
                'nivel_id',                     // Llave foránea de este modelo
                'paso_crecimiento_id'           // Llave foránea del modelo relacionado
            )
            ->withPivot(['al_iniciar', 'estado']) // Incluir campos extra de la tabla pivote
            ->withTimestamps();                   // Mantener timestamps en la tabla pivote si existen
    }

    /**
     * Relación muchos a muchos con PasoCrecimiento (para procesos prerrequisito).
     * Un nivel puede requerir completar ciertos pasos de crecimiento antes de iniciarlo.
     */
    public function procesosPrerrequisito(): BelongsToMany
    {
         return $this->belongsToMany(
                PasoCrecimiento::class,             // Modelo relacionado
                'nivel_proceso_prerrequisito',      // Tabla pivote
                'nivel_id',                         // Llave foránea de este modelo
                'paso_crecimiento_id'               // Llave foránea del modelo relacionado
            )
            ->withPivot('estado_proceso')           // Incluir el estado requerido del proceso
            ->withTimestamps();                       // Mantener timestamps si existen
    }
 

    
     
}
