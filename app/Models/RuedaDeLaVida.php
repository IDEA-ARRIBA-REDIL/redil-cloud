<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RuedaDeLaVida extends Model
{

    use HasFactory;
    protected $table = 'rueda_de_la_vida';
    protected $guarded = [];

    public function campos(): BelongsToMany
    {
        return $this->belongsToMany(CampoSeccionRv::class, 'campo_rueda_de_la_vida', 'rueda_de_la_vida_id', 'campos_seccion_rv_id')
            ->withPivot('valor', 'nombre_campo_abierto');
    }

    public function metas(): BelongsToMany
    {
        return $this->belongsToMany(Metas::class, 'meta_rueda_de_la_vida', 'rueda_de_la_vida_id', 'metas_id')
            ->withPivot('valor');
    }

    public function habitos(): BelongsToMany
    {
        return $this->belongsToMany(HabitosRv::class, 'habitos_rueda_de_la_vida', 'rueda_de_la_vida_id', 'habitos_rueda_vida_id')
            ->withPivot('valor');
    }
}
