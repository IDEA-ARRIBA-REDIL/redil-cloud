<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class TipoPago extends Model
{
  use HasFactory;
  protected $table = 'tipos_pago';
  protected $fillable = [
    'nombre',
    'enlace',
    'cuenta_sap',
    'client_id',
    'key_id',
    'bussines_id',
    'url_retorno',
    'identity_token',
    'key_reservada',
    'account_id',
    'imagen',
    'fondo',
    'color',
    'unica_moneda_id',
    'porcentaje_tax1',
    'porcentaje_tax2',
    'transaccion_minima',
    'transaccion_maxima',
    'incremento_pdp',
    // BOLEANOS (Importante)
    'activo',
    'habilitado_punto_pago',
    'subir_archivo_pagos',
    'botones_valores_moneda',
    'habilitado_donacion',
    'tiene_limite_dinero_acumulado',
    'permite_personas_externas',
    'codigo_datafono',
    'label_destinatario',
    'observaciones'
  ];
  protected $guarded = [];

  public function actividades(): BelongsToMany
  {
    return $this->belongsToMany(Actividad::class, 'actividad_tipos_pago', 'tipo_pago_id', 'actividad_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function estadosPago(): HasMany
  {
    return $this->hasMany(EstadoPago::class, 'tipo_pago_id');
  }

  public function getLogoUrlAttribute(): ?string
  {
      if (!$this->imagen) {
          return null;
      }
      return tenant_asset('img/tipos-pagos/logos/' . $this->imagen);
  }

  public function getFondoUrlAttribute(): ?string
  {
      if (!$this->fondo) {
          return null;
      }
      return tenant_asset('img/tipos-pagos/fondos/' . $this->fondo);
  }
}
