<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Moneda extends Model
{
  use HasFactory;
  protected $table = 'monedas';
  protected $guarded = [];

  public function actividades(): BelongsToMany
  {
    return $this->belongsToMany(Actividad::class, 'actividad_monedas', 'moneda_id', 'actividad_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }
}
