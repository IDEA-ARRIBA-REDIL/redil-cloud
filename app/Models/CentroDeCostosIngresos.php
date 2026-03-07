<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroDeCostosIngresos extends Model
{
    use HasFactory;

    public function ingresos() {
      return $this->hasMany(Ingreso::class);
    }
}
