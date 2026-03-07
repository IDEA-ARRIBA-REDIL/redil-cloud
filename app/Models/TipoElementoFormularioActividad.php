<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoElementoFormularioActividad extends Model
{
    use HasFactory;
    protected $table = 'tipos_elemento_formulario_actividad';
    protected $guarded = [];

    public function elementosFormulario()
    {
        return $this->hasMany(ElementoFormularioActividad::class);
    }
}
