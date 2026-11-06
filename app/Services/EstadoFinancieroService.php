<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\EstadoPago;
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

            $actual->fill(Arr::only($datos, ['referencia_pago', 'gateway_response', 'fecha']));
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
            $matriculasPendientes = $compra->matriculas()->where('estado_pago_matricula', 'pendiente')->get();
            foreach ($matriculasPendientes as $matricula) {
                $matricula->update(['estado_pago_matricula' => 'pagada', 'valor_pagado' => $total]);
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
                $matricula->estadoAcademicoClase()->delete();
                $matricula->delete();
            }

            return $matriculas->count();
        }, 3);
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
