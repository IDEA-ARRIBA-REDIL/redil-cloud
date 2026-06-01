<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Album extends Model
{
    use HasFactory;
    protected $table = 'albumes';
    protected $guarded = [];

    protected $appends = [
        'portada_url',
    ];

    public function canciones(): HasMany
    {
      return $this->hasMany(Cancion::class);
    }

    public function getPortadaUrlAttribute(): string
    {
        if ($this->imagen && $this->imagen !== '' && $this->imagen !== 'album-default.png' && $this->imagen !== 'temporal.png') {
            return tenant_asset('img/reproductor/' . $this->imagen);
        }
        return Storage::disk('global_media')->url('reproductor/album-default.png');
    }
}
