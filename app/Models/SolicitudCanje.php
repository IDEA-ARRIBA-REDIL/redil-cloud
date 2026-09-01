<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCanje extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_canje';

    protected $guarded = [];

    protected $casts = [
        'puntos_gastados' => 'integer',
        'procesado_el' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoTienda::class, 'producto_id');
    }

    public function procesadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por_user_id');
    }
}
