<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoBajaAlta extends Model
{
  use HasFactory;
  protected $table = 'tipos_baja_alta';
  protected $guarded = [];

  public function reportes(): HasMany
  {
    return $this->hasMany(ReporteBajaAlta::class);
  }
}
