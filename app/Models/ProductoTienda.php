<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProductoTienda extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos_tienda';

    protected $guarded = [];

    protected $casts = [
        'costo_puntos' => 'integer',
        'stock' => 'integer',
        'limite_por_usuario' => 'integer',
        'orden' => 'integer',
    ];

    public function solicitudesCanje(): HasMany
    {
        return $this->hasMany(SolicitudCanje::class, 'producto_id');
    }

    public function getImagenUrlAttribute(): ?string
    {
        if ($this->imagen_ruta && $this->imagen_ruta !== '') {
            return tenant_asset('img/tienda/'.$this->imagen_ruta);
        }

        return Storage::disk('global_media')->url('tienda/default.png');
    }
}
