<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cajas';

    protected $guarded = [];

    // ¡RELACIÓN RESTAURADA!
    // Obtiene el cajero (usuario) que está asignado a esta caja.
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: Una caja pertenece a un Punto de Pago
    public function puntoDePago(): BelongsTo
    {
        return $this->belongsTo(PuntoDePago::class)->withTrashed();
    }

    /**
     * Determina si la caja se encuentra dentro de su horario de atención configurado.
     *
     * @param  \Carbon\Carbon|string|null  $hora
     */
    public function estaEnHorario($hora = null): bool
    {
        // 1. Si no hay horario configurado, no existe restricción de atención
        if (! $this->hora_apertura || ! $this->hora_cierre) {
            return true;
        }

        // 2. Normalizar la hora a evaluar en formato H:i:s
        $horaActual = $hora
            ? ($hora instanceof Carbon ? $hora->format('H:i:s') : Carbon::parse($hora)->format('H:i:s'))
            : now()->format('H:i:s');

        $apertura = Carbon::parse($this->hora_apertura)->format('H:i:s');
        $cierre = Carbon::parse($this->hora_cierre)->format('H:i:s');

        // 3. Evaluar horario regular (mismo día) o nocturno (cruza medianoche)
        if ($apertura <= $cierre) {
            return $horaActual >= $apertura && $horaActual <= $cierre;
        }

        return $horaActual >= $apertura || $horaActual <= $cierre;
    }

    /**
     * Retorna el horario de atención configurado en formato legible (ej: 7:00 AM - 3:00 PM).
     */
    public function getHorarioAtencionAttribute(): ?string
    {
        if (! $this->hora_apertura || ! $this->hora_cierre) {
            return null;
        }

        $hApertura = Carbon::parse($this->hora_apertura)->format('g:i A');
        $hCierre = Carbon::parse($this->hora_cierre)->format('g:i A');

        return "{$hApertura} - {$hCierre}";
    }

    /**
     * Determina si la caja está activa y dentro de su horario de atención.
     *
     * @param  \Carbon\Carbon|string|null  $hora
     */
    public function puedeOperar($hora = null): bool
    {
        return (bool) $this->estado && $this->estaEnHorario($hora);
    }

    /**
     * Retorna el mensaje de error correspondiente si la caja no puede operar, o null si está habilitada.
     *
     * @param  \Carbon\Carbon|string|null  $hora
     */
    public function obtenerMensajeFueraDeHorario($hora = null): ?string
    {
        if (! $this->estado) {
            return "La caja '{$this->nombre}' se encuentra inactiva o cerrada.";
        }

        if (! $this->estaEnHorario($hora)) {
            return "Operación no permitida: La caja '{$this->nombre}' se encuentra fuera de su horario de atención ({$this->horario_atencion}).";
        }

        return null;
    }

    /* public function registros(): HasMany
      {
        return $this->hasMany(RegistroCaja::class);
      }

      public function historiales(): HasMany
      {
        return $this->hasMany(HistorialCierreCaja::class);
      }	*/
}
