<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escuela extends Model
{
    use HasFactory;

     protected $table = 'escuelas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo_matricula',
        'diploma_id',
        'habilitada_consilidacion'];
     /**
     * Una escuela tiene muchas plantillas de cortes.
     */
    public function cortesEscuela(): HasMany
    {
        return $this->hasMany(CorteEscuela::class);
    }

    /**
     * Una escuela tiene muchos periodos.
     */
    public function periodos(): HasMany
    {
        return $this->hasMany(Periodo::class);
    }

    /**
     * Una escuela tiene muchas materias.
     */
    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }

    public function niveles(): HasMany
    {
        // Correcto: Define una relación "uno a muchos" con NivelEscuela.
        // Eloquent asumirá que la tabla 'niveles_escuelas' tiene una columna 'escuela_id'.
        return $this->hasMany(NivelEscuela::class, 'escuela_id'); // La llave foránea por defecto es escuela_id
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'escuela_id');
    }


    /**
     * Una escuela pertenece a un diploma.
     */

    /**
     * Una escuela tiene muchos niveles de agrupación (Nuevo Sistema).
     */
    public function nivelesAgrupacion(): HasMany
    {
        return $this->hasMany(NivelAgrupacion::class);
    }
}
