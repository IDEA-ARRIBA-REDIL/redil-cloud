<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SedeDestinatario extends Model
{
  
    use HasFactory;
  protected $table = 'sedes_destinatarios';
  protected $fillable = [
    'nombre',
    'barrio',
    'direccion',
    'latitud',
    'longitud',
    'detalle'
];
  protected $guarded = [];

  
}
