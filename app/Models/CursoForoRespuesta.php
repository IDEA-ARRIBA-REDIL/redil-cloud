<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoForoRespuesta extends Model
{
    use HasFactory;

    protected $table = 'curso_foro_respuestas';

    protected $fillable = [
        'hilo_id',
        'user_id',
        'cuerpo',
        'es_respuesta_oficial',
    ];

    /**
     * Relación: La respuesta pertenece a un hilo principal del foro.
     */
    public function hilo()
    {
        return $this->belongsTo(CursoForoHilo::class, 'hilo_id');
    }

    /**
     * Relación: La persona que emite la respuesta.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
