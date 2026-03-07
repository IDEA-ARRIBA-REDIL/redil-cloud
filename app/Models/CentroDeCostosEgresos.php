<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroDeCostosEgresos extends Model
{
  use HasFactory;

  public function egresos() {
    return $this->hasMany(Egreso::class);
  }
}
