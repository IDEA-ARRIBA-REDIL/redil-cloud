<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoModulo extends Model
{
    use HasFactory;

    protected $table = 'curso_modulos';

    protected $fillable = [
        'curso_id',
        'nombre',
        'descripcion',
        'orden',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function items()
    {
        return $this->hasMany(CursoItem::class)->orderBy('orden');
    }
}
