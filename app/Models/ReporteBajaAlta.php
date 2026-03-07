<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteBajaAlta extends Model
{
  use HasFactory;
  protected $table = 'reporte_bajas_altas';
  protected $guarded = [];

  public function persona(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function tipo(): BelongsTo
  {
    return $this->belongsTo(TipoBajaAlta::class, 'tipo_baja_alta_id');
  }
}
