<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioHabitual extends Model
{
    use HasFactory;
    protected $table = 'horarios_habituales';
    protected $guarded = [];


    public function consejero(): BelongsTo
    {
        return $this->belongsTo(Consejero::class);
    }
}
