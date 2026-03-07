<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElementoFormularioActividad extends Model
{
    use HasFactory;
    protected $fillable = [
        'titulo',
        'tipo_elemento_id',
        'required',
        'visible',
        'visible_asistencia',
        'descripcion',
        'orden'
    ];
    protected $table = 'elementos_formulario_actividad';
    protected $guarded = [];

    public function tipoElemento(): belongsTo
    {
        return $this->belongsTo(TipoElementoFormularioActividad::class);
    }

    public function opciones(): hasMany
    {
        return $this->hasMany(OpcionesElementoFormularioActividad::class);
    }


    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class);
    }
}
