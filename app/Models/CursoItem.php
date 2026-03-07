<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoItem extends Model
{
    use HasFactory;

    protected $table = 'curso_items';

    protected $fillable = [
        'curso_modulo_id',
        'curso_item_tipo_id',
        'titulo',
        'orden',
        'itemable_id',
        'itemable_type',
    ];

    public function modulo()
    {
        return $this->belongsTo(CursoModulo::class, 'curso_modulo_id');
    }

    public function tipo()
    {
        return $this->belongsTo(CursoItemTipo::class, 'curso_item_tipo_id');
    }

    // Establece la relación polimórfica
    public function itemable()
    {
        return $this->morphTo();
    }

    // Relación con el foro (Preguntas específicas de este ítem)
    public function hilosForo()
    {
        return $this->hasMany(CursoForoHilo::class, 'curso_item_id')->orderBy('created_at', 'desc');
    }
}
