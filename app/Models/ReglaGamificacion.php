<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReglaGamificacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reglas_gamificacion';

    protected $guarded = [];

    protected $casts = [
        'meta_cantidad' => 'integer',
        'puntos_premio' => 'integer',
        'limite_diario' => 'integer',
    ];

    public function insignia(): BelongsTo
    {
        return $this->belongsTo(Insignia::class, 'insignia_id');
    }
}
