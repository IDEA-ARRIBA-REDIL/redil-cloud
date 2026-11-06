<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'compras';

    protected $guarded = [];

    public const PENDIENTE = 1;

    public const ANULADA = 2;

    public const PAGADA = 3;

    public const RECHAZADA = 4;

    public const EN_PASARELA = 5;

    protected $casts = ['valor' => 'decimal:2', 'anulacion_solicitada' => 'boolean'];

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

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'compra_id');
    }

    public function estadoComercial(): object
    {
        if ($this->anulacion_solicitada || ((int) $this->estado === self::EN_PASARELA && str_contains((string) $this->listado_carrito, 'MOTIVO_ANULACION:'))) {
            return (object) ['nombre' => 'Anulación solicitada', 'color' => '#a66b00'];
        }
        [$nombre, $color] = match ((int) $this->estado) {
            self::PENDIENTE => ['Pendiente', '#a66b00'],
            self::ANULADA => ['Anulada', '#6c757d'],
            self::PAGADA => ['Pagada', '#198754'],
            self::RECHAZADA => ['Rechazada', '#dc3545'],
            self::EN_PASARELA => ['Pendiente en pasarela', '#0d6efd'],
            default => ['Sin estado', '#6c757d'],
        };

        return (object) ['nombre' => $nombre, 'color' => $color];
    }

    public function scopeEstadoInforme(\Illuminate\Database\Eloquent\Builder $query, string|int $filtro): \Illuminate\Database\Eloquent\Builder
    {
        return match ((string) $filtro) {
            '1' => $query->whereIn('estado', [self::PENDIENTE, self::EN_PASARELA]),
            '2' => $query->where('estado', self::PAGADA),
            '3' => $query->whereIn('estado', [self::ANULADA, self::RECHAZADA]),
            '4' => $query->where('estado', self::PENDIENTE)->whereHas('pagos', fn ($pagos) => $pagos->where('anulado_pdp', false)->whereHas('estadoPago', fn ($estados) => $estados->where('estado_final_inscripcion', true)->where('estado_anulado_inscripcion', false))),
            default => $query,
        };
    }
}
