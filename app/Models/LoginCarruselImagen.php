<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginCarruselImagen extends Model
{
    protected $table = 'login_carrusel_imagenes';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function personalizacion(): BelongsTo
    {
        return $this->belongsTo(LoginPersonalizacion::class, 'login_personalizacion_id');
    }
}
