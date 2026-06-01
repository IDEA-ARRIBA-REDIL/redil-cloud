<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Accesor para obtener la URL completa de la imagen de forma compatible con Multi-Tenant.
     * Esto es muy útil para mostrar la imagen en el frontend.
     */
    public function getImagenUrlAttribute(): string
    {
        if ($this->imagen) {
            return tenant_asset($this->imagen);
        }

        return asset('images/placeholder.jpg'); // Devuelve una imagen por defecto si no existe
    }
}
