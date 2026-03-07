<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class RespuestaElementoFormulario extends Model
{
    use HasFactory;
    protected $table = 'respuestas_formulario_elemento_compra';
    protected $guarded = [];
    /**
     * Define la relación: Una respuesta pertenece a un Elemento del formulario.
     * Esto le permite a Laravel encontrar la pregunta asociada a esta respuesta.
     */
    public function elemento(): BelongsTo
    {
        // El segundo parámetro 'elemento_formulario_actividad_id' es la llave foránea en la tabla 'respuestas_formulario_elemento_compra'
        return $this->belongsTo(ElementoFormularioActividad::class, 'elemento_formulario_actividad_id');
    }

    /**
     * Opcional: Relación para encontrar el usuario que dio la respuesta.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Opcional: Relación para encontrar la compra asociada.
     */
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }
}
