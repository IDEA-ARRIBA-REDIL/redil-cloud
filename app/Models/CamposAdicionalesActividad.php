<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CamposAdicionalesActividad extends Model
{
  use HasFactory;
  protected $table = 'campos_adicionales_actividad';
  protected $guarded = [];


  public function actividades(): BelongsToMany
  {
    return $this->belongsToMany(Actividad::class, 'actividad_campos_adicionales_actividad',  'campos_adicionales_actividad_id','actividad_id' )->withPivot(
      'created_at',
      'updated_at'
    );
  }

}
