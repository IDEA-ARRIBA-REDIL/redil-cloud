<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BannerEscuela extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'banner_escuelas';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'imagen',
        'descripcion',
        'activo',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Accesor para obtener la URL completa de la imagen.
     * Esto es muy útil para mostrar la imagen en el frontend.
     */
    public function getImagenUrlAttribute(): string
    {
        // Verifica si el campo 'imagen' tiene un valor y si el archivo existe en el disco 'public'
        return $this->imagen && Storage::disk('public')->exists($this->imagen)
            ? Storage::disk('public')->url($this->imagen)
            : asset('images/placeholder.jpg'); // Devuelve una imagen por defecto si no existe
    }
}
