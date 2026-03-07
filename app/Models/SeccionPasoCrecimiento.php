<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SeccionPasoCrecimiento extends Model
{
  use HasFactory;
  protected $table = 'secciones_pasos_crecimiento';
  protected $guarded = [];

  public function pasosCrecimiento(): HasMany
  {
    return $this->hasMany(PasoCrecimiento::class);
  }
}
