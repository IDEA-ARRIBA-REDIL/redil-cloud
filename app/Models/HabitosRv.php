<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HabitosRv extends Model
{
    use HasFactory;
    protected $table = 'habitos_rueda_vida';
    protected $guarded = [];

    public function metas(): BelongsTo
    {
        return $this->belongsTo(Metas::class);
    }

    public function ruedasDeLaVida(): BelongsToMany
    {
        return $this->belongsToMany(RuedaDeLaVida::class, 'habitos_rueda_de_la_vida', 'habitos_rueda_vida_id', 'rueda_de_la_vida_id')
            ->withPivot('valor');
    }
}
