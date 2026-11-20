<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\EstadoPago;
use App\Models\HorarioMateriaPeriodo;
use App\Models\Matricula;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class EstadoFinancieroService
{
    public function aplicar(Pago $pago, EstadoPago $estado, array $datos = [], ?int $tipoPagoId = null): Pago
    {
        return $pago->getConnection()->transaction(function () use ($pago, $estado, $datos, $tipoPagoId): Pago {
            $compra = Compra::query()->lockForUpdate()->findOrFail($pago->compra_id);
            $actual = Pago::query()->lockForUpdate()->findOrFail($pago->id);
            if ((int) $actual->compra_id !== (int) $compra->id) {
                throw ValidationException::withMessages(['pago' => 'La compra del pago cambió. Vuelva a consultar.']);
            }
            if ($actual->anulado_pdp) {
                throw ValidationException::withMessages(['pago' => 'Un pago anulado requiere conciliación administrativa.']);
            }
            $tipo = $tipoPagoId ?? $actual->tipo_pago_id;
            if ((int) $estado->tipo_pago_id !== (int) $tipo) {
                throw ValidationException::withMessages(['pago' => 'El estado no corresponde al método de pago.']);
            }
            if ((int) $actual->moneda_id !== (int) $compra->moneda_id) {
                throw ValidationException::withMessages(['pago' => 'La moneda del pago no corresponde a la compra.']);
            }

            $aprobado = $actual->estadoPago?->estado_final_inscripcion;
            if ($aprobado && (! $estado->estado_final_inscripcion || (int) $tipo !== (int) $actual->tipo_pago_id)) {
                $this->reconciliarCompra($compra);

                return $actual->fresh();
            }

            $actual->fill(Arr::only($datos, [
                'referencia_pago',
                'gateway_response',
                'gateway_transaction_id',
                'int_id_forma_pago',
                'fecha',
            ]));
            $actual->estado_pago_id = $estado->id;
            $actual->tipo_pago_id = $tipo;
            $actual->save();
            $actual->unsetRelation('estadoPago');
            Matricula::query()->where('compra_id', $compra->id)->where('pago_id', $actual->id)
                ->update(['tipo_pago_id' => $tipo, 'estado_pago_id' => $estado->id]);
            $this->reconciliarCompra($compra);

            if (! $estado->estado_pendiente && ! $estado->estado_final_inscripcion) {
                $this->limpiarReservasFallidas($actual);
            }

            return $actual->fresh();
        }, 3);
    }

    public function pagosAprobados(Compra $compra): Builder
    {
        return Pago::query()->where('compra_id', $compra->id)->where('moneda_id', $compra->moneda_id)->where('anulado_pdp', false)
            ->whereHas('estadoPago', fn (Builder $query): Builder => $query->where('estado_final_inscripcion', true)->where('estado_anulado_inscripcion', false));
    }

    public function totalAprobado(Compra $compra): string
    {
        return ImporteMonetario::normalizar($this->pagosAprobados($compra)->sum('valor'));
    }

    /** El llamador mantiene el bloqueo de la compra hasta confirmar su transacción. */
    public function reconciliarCompra(Compra $compra): void
    {
        $total = $this->totalAprobado($compra);
        $pendiente = $compra->pagos()->where(fn (Builder $query): Builder => $query->whereNull('estado_pago_id')->orWhereHas('estadoPago', fn (Builder $estado): Builder => $estado->where('estado_pendiente', true)))->exists();
        if (ImporteMonetario::comparar($total, $compra->valor) >= 0 && $this->pagosAprobados($compra)->exists()) {
            $compra->update(['estado' => Compra::PAGADA]);
            $compra->inscripciones()->update(['estado' => 3]);
            $pagoAprobado = $this->pagosAprobados($compra)->latest('id')->first();
            $matriculasPendientes = $compra->matriculas()->where('estado_pago_matricula', 'pendiente')->get();
            foreach ($matriculasPendientes as $matricula) {
                $datosMatricula = [
                    'estado_pago_matricula' => 'pagada',
                    'valor_pagado' => $total,
                    'fecha_pago' => now(),
                ];
                if ($pagoAprobado) {
                    $datosMatricula['tipo_pago_id'] = $pagoAprobado->tipo_pago_id;
                    $datosMatricula['estado_pago_id'] = $pagoAprobado->estado_pago_id;
                }
                $matricula->update($datosMatricula);
                $matricula->aplicarEfectosMatricula();
            }
        } elseif (ImporteMonetario::comparar($total, 0) > 0 || $pendiente) {
            $compra->update(['estado' => Compra::PENDIENTE]);
        } elseif ($compra->pagos()->exists()) {
            $compra->update(['estado' => Compra::ANULADA]);
        }
    }

    public function limpiarReservasFallidas(Pago $pago): int
    {
        return $pago->getConnection()->transaction(function () use ($pago): int {
            $compra = Compra::query()->lockForUpdate()->find($pago->compra_id);
            $actual = Pago::query()->lockForUpdate()->find($pago->id);
            if (! $compra || ! $actual || ! $actual->estadoPago || $actual->estadoPago->estado_pendiente || $actual->estadoPago->estado_final_inscripcion) {
                return 0;
            }
            if ($compra->pagos()->where(fn (Builder $query): Builder => $query->whereNull('estado_pago_id')->orWhereHas('estadoPago', fn (Builder $estado): Builder => $estado->where('estado_pendiente', true)->orWhere('estado_final_inscripcion', true)))->exists()) {
                return 0;
            }
            $matriculas = $compra->matriculas()->where('pago_id', $actual->id)
                ->where('estado_pago_matricula', 'pendiente')->lockForUpdate()->get();
            foreach ($matriculas as $matricula) {
                $this->restaurarCupoHorario($matricula);
                $matricula->estadoAcademicoClase()->delete();
                $matricula->delete();
            }

            return $matriculas->count();
        }, 3);
    }

    /**
     * Libera una matrícula borrador que nunca fue enviada a pasarela (sin tipo_pago_id ni payment_url)
     * tras vencer la ventana de abandono, restaurando el cupo disponible del horario.
     */
    public function limpiarReservaBorradorExpirada(Matricula $matricula): bool
    {
        return $matricula->getConnection()->transaction(function () use ($matricula): bool {
            $actual = Matricula::query()->lockForUpdate()->find($matricula->id);
            if (! $actual || $actual->estado_pago_matricula !== 'pendiente') {
                return false;
            }

            $compra = $actual->compra_id
                ? Compra::query()->lockForUpdate()->find($actual->compra_id)
                : null;

            $pago = $actual->pago_id
                ? Pago::query()->lockForUpdate()->find($actual->pago_id)
                : ($actual->referencia_pago && ctype_digit((string) $actual->referencia_pago)
                    ? Pago::query()->lockForUpdate()->find((int) $actual->referencia_pago)
                    : null);

            if (! $compra && $pago?->compra_id) {
                $compra = Compra::query()->lockForUpdate()->find($pago->compra_id);
            }

            // 1. Nunca limpiar si el pago asociado ya fue enviado a una pasarela o tiene método asignado
            if ($pago && ($pago->tipo_pago_id !== null || ! empty($pago->payment_url))) {
                return false;
            }

            // 2. Nunca limpiar si la compra tiene pagos aprobados o pagos activos en pasarela
            if ($compra) {
                $tienePagoActivoOAprobado = $compra->pagos()
                    ->where(function (Builder $query): void {
                        $query->whereNotNull('tipo_pago_id')
                            ->orWhereNotNull('payment_url');
                    })
                    ->whereHas('estadoPago', fn (Builder $estado): Builder => $estado
                        ->where('estado_pendiente', true)
                        ->orWhere('estado_final_inscripcion', true))
                    ->exists();

                if ($tienePagoActivoOAprobado) {
                    return false;
                }
            }

            // 3. Restaurar cupo, limpiar estado académico y aplicar soft-delete a la matrícula
            $this->restaurarCupoHorario($actual);
            $actual->estadoAcademicoClase()->delete();
            $actual->delete();

            // 4. Anular compra y soft-delete del pago borrador sin pasarela
            if ($pago && $pago->tipo_pago_id === null && empty($pago->payment_url)) {
                $pago->delete();
            }

            if ($compra && (int) $compra->estado !== Compra::PAGADA) {
                $compra->update(['estado' => Compra::ANULADA]);
            }

            return true;
        }, 3);
    }

    /**
     * Libera de forma segura una matrícula histórica (legacy sin compra_id/pago_id explícitos)
     * únicamente cuando el pago vinculado por referencia_pago ya está definitivamente rechazado/anulado/abandonado.
     */
    public function limpiarReservaLegacyFallida(Matricula $matricula, Pago $pago): bool
    {
        return $pago->getConnection()->transaction(function () use ($matricula, $pago): bool {
            $actualMatricula = Matricula::query()->lockForUpdate()->find($matricula->id);
            $actualPago = Pago::query()->lockForUpdate()->find($pago->id);
            $compra = $actualPago?->compra_id
                ? Compra::query()->lockForUpdate()->find($actualPago->compra_id)
                : null;

            if (! $actualMatricula || $actualMatricula->estado_pago_matricula !== 'pendiente') {
                return false;
            }

            if (! $actualPago || ! $actualPago->estadoPago || $actualPago->estadoPago->estado_pendiente || $actualPago->estadoPago->estado_final_inscripcion) {
                return false;
            }

            if ($compra && $compra->pagos()->where(fn (Builder $query): Builder => $query->whereNull('estado_pago_id')->orWhereHas('estadoPago', fn (Builder $estado): Builder => $estado->where('estado_pendiente', true)->orWhere('estado_final_inscripcion', true)))->exists()) {
                return false;
            }

            $this->restaurarCupoHorario($actualMatricula);
            $actualMatricula->estadoAcademicoClase()->delete();
            $actualMatricula->delete();

            return true;
        }, 3);
    }

    /**
     * Devuelve 1 cupo al horario asociado a la matrícula liberada.
     */
    protected function restaurarCupoHorario(Matricula $matricula): void
    {
        $horarioId = $matricula->getAttribute('horario_materia_periodo_id');
        if (! $horarioId) {
            return;
        }

        $horario = HorarioMateriaPeriodo::query()->lockForUpdate()->find($horarioId);
        if ($horario) {
            $horario->increment('cupos_disponibles');
        }
    }

    public function estadoAprobado(int $tipoPagoId): EstadoPago
    {
        $estados = EstadoPago::query()->where('tipo_pago_id', $tipoPagoId)
            ->where('estado_final_inscripcion', true)->where('estado_anulado_inscripcion', false)->get();
        if ($estados->count() !== 1) {
            throw ValidationException::withMessages(['pagos' => 'Configure un único estado aprobado para el método de pago seleccionado.']);
        }

        return $estados->first();
    }
}
