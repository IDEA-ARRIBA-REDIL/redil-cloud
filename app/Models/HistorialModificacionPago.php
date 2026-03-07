<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialModificacionPago extends Model
{
    protected $table = 'historial_modificacion_pagos';

    protected $fillable = [
        'asesor_id',
        'caja_id',
        'punto_de_pago_id',
        'compra_id',
        'pago_id',
        'usuario_afectado_id',
        'actividad_id',
        'categoria_actividad_id',
        'tipo_pago_id',
        'valor',
        'motivo',
    ];

    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function puntoDePago()
    {
        return $this->belongsTo(PuntoDePago::class, 'punto_de_pago_id');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function usuarioAfectado()
    {
        return $this->belongsTo(User::class, 'usuario_afectado_id');
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
    }

    public function categoriaActividad()
    {
        return $this->belongsTo(ActividadCategoria::class, 'categoria_actividad_id');
    }

    public function tipoPago()
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }
}
