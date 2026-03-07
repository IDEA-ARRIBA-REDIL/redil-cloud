<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoPreguntaOpcion extends Model
{
    use HasFactory;

    protected $table = 'curso_pregunta_opciones';

    protected $fillable = [
        'curso_pregunta_id',
        'opcion',
        'es_correcta',
    ];

    protected $casts = [
        'es_correcta' => 'boolean',
    ];

    public function pregunta()
    {
        return $this->belongsTo(CursoPregunta::class);
    }
}
