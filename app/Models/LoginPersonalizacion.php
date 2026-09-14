<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoginPersonalizacion extends Model
{
    protected $table = 'login_personalizaciones';

    protected $guarded = ['id', 'singleton_key'];

    protected function casts(): array
    {
        return [
            'singleton_key' => 'boolean',
            'carousel_interval_ms' => 'integer',
        ];
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(LoginCarruselImagen::class)->orderBy('sort_order');
    }
}
