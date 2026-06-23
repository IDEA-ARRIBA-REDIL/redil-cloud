<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPeticion extends Model
{
  use HasFactory;
  protected $table = 'tipo_peticiones';
  protected $guarded = [];

  public function peticiones(): HasMany
  {
    return $this->hasMany(Peticion::class);
  }

  public function getBannerEmailUrlAttribute(): string
  {
      if ($this->banner_email && $this->banner_email !== '') {
          return tenant_asset('img/email/peticiones/'.$this->banner_email);
      }

      return "";
  }

  public function intercesores(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
  {
      return $this->belongsToMany(Intercesor::class, 'intercesor_tipo_peticion', 'tipo_peticion_id', 'intercesor_id');
  }

}
