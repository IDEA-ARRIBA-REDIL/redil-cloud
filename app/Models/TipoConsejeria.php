<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class TipoConsejeria extends Model
{
    use HasFactory;
    protected $table = 'tipo_consejerias';
    protected $guarded = [];

    public function consejeros():BelongsToMany
    {
        return $this->belongsToMany(Consejero::class, 'consejero_tipo_consejeria', 'tipo_consejeria_id', 'consejero_id');
    }

    public function tareasConsolidacion(): BelongsToMany
    {
        return $this->belongsToMany(TareaConsolidacion::class, 'tarea_consolidacion_tipo_consejeria', 'tipo_consejeria_id', 'tarea_consolidacion_id');
    }

    public function pasosCrecimiento(): BelongsToMany
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'paso_crecimiento_tipo_consejeria', 'tipo_consejeria_id', 'paso_crecimiento_id');
    }
}
