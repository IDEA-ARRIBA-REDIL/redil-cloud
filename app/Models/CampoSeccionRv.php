<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampoSeccionRv extends Model
{
    use HasFactory;
    protected $table = 'campos_seccion_rv';
    protected $guarded = [];


    public function seccion(): BelongsTo
    {
        return $this->belongsTo(SeccionRv::class);
    }

    public function ruedasDeLaVida(): BelongsToMany
    {
        return $this->belongsToMany(RuedaDeLaVida::class, 'campo_rueda_de_la_vida', 'campos_seccion_rv_id', 'rueda_de_la_vida_id')
            ->withPivot('valor', 'nombre_campo_abierto');
    }
}
