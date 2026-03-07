<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingreso extends Model
{
  use HasFactory;

  protected $table = 'ingresos';
  protected $guarded = [];

  public function centroDeCostosIngresos() : BelongsTo
  {
    return $this->belongsTo(CentroDeCostosIngresos::class);
  }

  public function cajasFinanzas(): BelongsTo
  {
    return $this->belongsTo(CajaFinanzas::class, 'caja_finanzas_id');
  }

  public function tipoOfrendas(): BelongsTo
  {
    return $this->belongsTo(TipoOfrenda::class, 'tipo_ofrenda_id');
  }

  public function moneda(): BelongsTo
  {
    return $this->belongsTo(Moneda::class, 'moneda_id');
  }
}
