<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaActividadCompra extends Model
{
    use HasFactory;

    protected $table = 'categoria_actividad_compra';

    protected $guarded = [];

    public function compras()
    {
        return $this->belongsTo(Compra::class);
    }

    public function actividadCategoria()
    {
        return $this->belongsTo(ActividadCategoria::class, 'actividad_categoria_id');
    }
}
