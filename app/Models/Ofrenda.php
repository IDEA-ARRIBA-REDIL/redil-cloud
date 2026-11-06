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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'correo_confirmacion_enviado' => 'boolean',
            'gateway_response' => 'array',
        ];
    }

    public function reporteReuniones(): BelongsToMany
    {
        return $this->belongsToMany(ReporteReunion::class, 'ofrenda_reuniones', 'ofrenda_id', 'reporte_reunion_id')->withTimestamps();
    }

    public function tipoOfrenda(): BelongsTo
    {
        return $this->belongsTo(TipoOfrenda::class, 'tipo_ofrenda_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    public function estadoPago(): BelongsTo
    {
        return $this->belongsTo(EstadoPago::class, 'estado_pago_id');
    }

    public function destinatarioRelacion(): BelongsTo
    {
        return $this->belongsTo(Destinatario::class, 'destinatario_id');
    }

    public function paisDonante(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_donante_id');
    }

    public function tipoIdentificacionDonante(): BelongsTo
    {
        return $this->belongsTo(TipoIdentificacion::class, 'tipo_identificacion_donante_id');
    }
}
