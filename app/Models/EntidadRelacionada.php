<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntidadRelacionada extends Model
{
    use HasFactory;

    protected $table = 'entidades_relacionadas';

    protected $fillable = [
        'nombre',
        'nit',
        'direccion',
        'telefono',
        'representante_legal',
    ];
}
