<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoCivil extends Model
{
  use HasFactory;
  protected $table = 'estados_civiles';
  protected $guarded = [];

  public function usuarios(): HasMany
  {
    return $this->hasMany(User::class);
  }

  public function actividadEstados()
  {
    return $this->belongsToMany(EstadoCivil::class, 'actividad_estados_civiles',  'estado_civil_id', 'actividad_id' )->withPivot(
      'created_at',
      'updated_at'
    );
  }
}
