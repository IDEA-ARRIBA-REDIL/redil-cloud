<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pagos';
    protected $guarded = [];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }
    public function estadoPago(): BelongsTo
    {
        return $this->belongsTo(EstadoPago::class, 'estado_pago_id');
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function historialModificaciones()
    {
        return $this->hasMany(HistorialModificacionPago::class, 'pago_id');
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'registro_caja_id');
    }

    public function matricula(): HasOne
    {
        return $this->hasOne(Matricula::class, 'referencia_pago');
    }
}
