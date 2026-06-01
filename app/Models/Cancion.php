<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cancion extends Model
{
    use HasFactory;
    protected $table = 'canciones';
    protected $guarded = [];

    protected $appends = [
        'ruta_audio',
    ];

    public function album(): BelongsTo
    {
      return $this->belongsTo(Album::class);
    }

    public function getRutaAudioAttribute(): string
    {
        if ($this->archivo && $this->archivo !== '' && $this->archivo !== 'temporal.mp3') {
            return tenant_asset('archivos/reproductor/' . $this->archivo);
        }
        return '';
    }
}
