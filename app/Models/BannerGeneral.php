<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerGeneral extends Model
{
  protected $table = 'banner_generales';

  protected $fillable = [
    'imagen',
    'nombre',
    'fecha_inicio',
    'fecha_fin',
    'link',
    'visible'
  ];

  protected $casts = [
    'fecha_inicio' => 'date:Y-m-d',
    'fecha_fin' => 'date:Y-m-d',
    'visible' => 'boolean',
  ];
}
