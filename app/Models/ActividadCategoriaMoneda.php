<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActividadCategoriaMoneda extends Model
{
      use HasFactory;
      protected $table = 'actividad_categoria_monedas';
      protected $guarded = [];


      public function categoriaActividad(): BelongsTo
    {
        return $this->belongsTo(ActividadCategoria::class, 'actividad_categoria_id', 'id');
    }


}
