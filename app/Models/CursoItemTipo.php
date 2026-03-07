<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoItemTipo extends Model
{
    use HasFactory;

    protected $table = 'curso_item_tipos';

    protected $fillable = [
        'nombre',
        'categoria',
        'codigo',
        'icono',
    ];

    public function items()
    {
        return $this->hasMany(CursoItem::class);
    }
}
