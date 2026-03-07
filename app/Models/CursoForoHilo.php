<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoForoHilo extends Model
{
    use HasFactory;

    protected $table = 'curso_foro_hilos';

    protected $fillable = [
        'curso_id',
        'curso_item_id',
        'user_id',
        'titulo',
        'cuerpo',
        'estado',
    ];

    /**
     * Relación: El hilo pertenece a un Curso.
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Relación: El hilo pertenece opcionalmente a un Ítem / Lección.
     */
    public function item()
    {
        return $this->belongsTo(CursoItem::class, 'curso_item_id');
    }

    /**
     * Relación: El creador de la pregunta.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un hilo tiene muchas respuestas en forma de chat.
     */
    public function respuestas()
    {
        return $this->hasMany(CursoForoRespuesta::class, 'hilo_id');
    }
}
