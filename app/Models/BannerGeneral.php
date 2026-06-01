<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
          return Storage::disk('global_media')->url('banner-default.jpg');
      }

      $rutaRelativa = 'img/banners/' . $this->imagen;

      // tenant_asset() es el helper oficial para archivos del tenant en local
      if (Storage::disk()->exists($rutaRelativa)) {
          return tenant_asset($rutaRelativa);
      }

      return Storage::disk('global_media')->url('banner-default.jpg');
  }
}
