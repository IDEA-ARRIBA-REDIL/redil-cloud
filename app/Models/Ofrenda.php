<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ofrenda extends Model
{
  use HasFactory;
  protected $table = 'ofrendas';
  protected $guarded = [];

  public function reporteReuniones(): BelongsToMany
  {
    return $this->belongsToMany(ReporteReunion::class, "ofrenda_reuniones", "ofrenda_id", "reporte_reunion_id")->withTimestamps();
  }

  public function tipoOfrenda(): BelongsTo
  {
    return $this->belongsTo(TipoOfrenda::class);
  }

  public function usuario(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }



}
