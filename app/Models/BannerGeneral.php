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

  public function getImagenVinculadaAttribute(): string
  {
      if (!$this->imagen) {
          return \Illuminate\Support\Facades\Storage::disk('global_media')->url('banner-default.jpg');
      }

      $rutaRelativa = 'img/banners/' . $this->imagen;

      // tenant_asset() es el helper oficial para archivos del tenant en local
      if (\Illuminate\Support\Facades\Storage::disk()->exists($rutaRelativa)) {
          return tenant_asset($rutaRelativa);
      }

      // Fallback a global_media
      if (\Illuminate\Support\Facades\Storage::disk('global_media')->exists($this->imagen)) {
          return \Illuminate\Support\Facades\Storage::disk('global_media')->url($this->imagen);
      }

      return \Illuminate\Support\Facades\Storage::disk('global_media')->url('banner-default.jpg');
  }
}
