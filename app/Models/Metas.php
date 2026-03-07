<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metas extends Model
{
    use HasFactory;
    protected $table = 'metas';
    protected $guarded = [];


    public function habitos(): HasMany
    {
        return $this->hasMany(HabitosRv::class);
    }

    public function ruedasDeLaVida(): BelongsToMany
    {
        return $this->belongsToMany(RuedaDeLaVida::class, 'meta_rueda_de_la_vida', 'metas_id', 'rueda_de_la_vida_id')
            ->withPivot('valor');
    }
}
