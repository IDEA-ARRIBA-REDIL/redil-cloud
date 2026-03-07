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
}
