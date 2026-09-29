<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeccionRv extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'secciones_rv';

    protected $guarded = [];

    public function tipoSeccion(): BelongsTo
    {
        return $this->belongsTo(TipoSeccionRv::class);
    }

    public function campos(): HasMany
    {
        return $this->hasMany(CampoSeccionRv::class, 'seccion_rv_id')->orderBy('orden', 'asc');
    }

    public function metas(): HasMany
    {
        return $this->hasMany(Metas::class);
    }

    public function promedio($ruedaVidaId)
    {
        $promedio = $this->campos()->leftJoin('campo_rueda_de_la_vida', 'campos_seccion_rv.id', '=', 'campo_rueda_de_la_vida.campos_seccion_rv_id')
            ->where('campo_rueda_de_la_vida.rueda_de_la_vida_id', $ruedaVidaId)->avg('campo_rueda_de_la_vida.valor');

        return $promedio;
    }
}
