<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RangoEdad extends Model
{
  use HasFactory;
  protected $table = 'rangos_edad';
  protected $guarded = [];

  public function actividad(): BelongsToMany
  {
    return $this->belongsToMany(Actividad::class, 'actividad_rangos_edad', 'rango_edad_id', 'actividad_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function reuniones(): BelongsToMany
  {
    return $this->belongsToMany(Reunion::class, 'actividad_rangos_edades', 'rangos_edades_id', 'reuniones_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }
}