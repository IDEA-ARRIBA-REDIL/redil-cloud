<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'compras';
    protected $guarded = [];


    public function carritos()
    {
        return $this->hasMany(ActividadCarritoCompra::class);
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }


    public function camposAdicionales()
    {
        return $this->hasMany(ActividadCampoAdicionalCompra::class);
    }

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }


    public function categorias(): HasMany
    {
        return $this->hasMany(CategoriaActividadCompra::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'compra_id');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaElementoFormulario::class, 'compra_id');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(Destinatario::class, 'destinatario_id');
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(Pago::class, 'compra_id');
    }

    public function estadoPago(): BelongsTo
    {
        return $this->belongsTo(EstadoPago::class, 'estado');
    }

    public function metodoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'metodo_pago_id');
    }
}
