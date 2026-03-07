<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Egreso extends Model
{
  use HasFactory;

  protected $table = 'egresos';

  protected $fillable = [
    'fecha',
    'proveedor_id',
    'documento_equivalente_id',
    'caja_finanzas_id',
    'tipo_egreso_id',
    'valor',
    'descripcion',
    'campo_adicional1',
    'anulado',
    'motivo_anulacion_id',
    'usuario_anulacion_id',
    'fecha_anulacion',
    'sede_id',
    'moneda_id',
  ];

  public function centroDeCostosEgresos() : BelongsTo
  {
    return $this->belongsTo(CentroDeCostosEgresos::class);
  }

  public function cajaFinanzas(): BelongsTo
  {
    return $this->belongsTo(CajaFinanzas::class);
  }

  public function proveedor(): BelongsTo
  {
    return $this->belongsTo(Proveedor::class);
  }

  public function tipoEgreso(): BelongsTo
  {
    return $this->belongsTo(TipoEgreso::class);
  }

  public function documentoEquivalente(): BelongsTo
  {
    return $this->belongsTo(DocumentoEquivalente::class);
  }

  public function moneda(): BelongsTo
  {
    return $this->belongsTo(Moneda::class, 'moneda_id');
  }
}
