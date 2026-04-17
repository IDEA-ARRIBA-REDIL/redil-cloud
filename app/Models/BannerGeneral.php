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

  public function getImagenVinculadaAttribute()
  {
      $configuracion = \App\Models\Configuracion::first();

      if (!$this->imagen) {
          return \Illuminate\Support\Facades\Storage::disk('global_media')->url('banner-default.jpg');
      }

      $relPath = 'storage/' . $configuracion->ruta_almacenamiento . '/img/banners/' . $this->imagen;
      $fullPath = public_path($relPath);
      
      if (file_exists($fullPath)) {
          return asset($relPath);
      }

      if (\Illuminate\Support\Facades\Storage::disk('global_media')->exists($this->imagen)) {
          return \Illuminate\Support\Facades\Storage::disk('global_media')->url($this->imagen);
      }

      return \Illuminate\Support\Facades\Storage::disk('global_media')->url('banner-default.jpg');
  }
}
