<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestionVideo extends Model
{
  // Asegúrate de que esta línea coincida con el nombre en tu migración
  protected $table = 'gestion_videos';

  protected $fillable = [
    'nombre',
    'url_video',
    'fecha_publicacion',
    'visible'
  ];

  protected $casts = [
    'fecha_publicacion' => 'date',
    'visible' => 'boolean'
  ];
}
