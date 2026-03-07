<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActividadBanner extends Model
{
    use HasFactory;
    protected $table = 'actividad_banners';
    protected $guarded = [];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class);
    }
}
