<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoAprendizaje extends Model
{
    use HasFactory;

    protected $table = 'curso_aprendizajes';

    protected $fillable = [
        'curso_id',
        'texto',
        'orden'
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}
