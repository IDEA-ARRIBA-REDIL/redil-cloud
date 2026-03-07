<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class RecursoGeneralEscuela extends Model
{
    use HasFactory;

    protected $table = 'recursos_generales_escuela';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'link_externo',
        'link_youtube',
        'nombre_archivo',
        'ruta_archivo',
        'visible',
    ];

    /**
     * Define la relación muchos a muchos con los Roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'recurso_general_escuela_rol',
            'recurso_general_escuela_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Accesor para obtener la URL pública del archivo.
     */
    public function getArchivoUrlAttribute(): ?string
    {
        if ($this->ruta_archivo) {
            return Storage::disk('public')->url($this->ruta_archivo);
        }
        return null;
    }
}
