<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadCarritoCompra extends Model
{
    use HasFactory;
    protected $table = 'actividad_carritos_compra';
    protected $guarded = [];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function categoria()
    {
        return $this->belongsTo(ActividadCategoria::class, 'actividad_categoria_id');
    }
}
