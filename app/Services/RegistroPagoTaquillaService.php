<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\Compra;
use App\Models\Pago;
use App\Models\TipoPago;
use Illuminate\Validation\ValidationException;

class RegistroPagoTaquillaService
{
    public function registrar(Compra $compra, Caja $caja, array $pagos, string $operacionId, int $categoriaId, bool $pagoCompleto = true): void
    {
        $compra->getConnection()->transaction(function () use ($compra, $caja, $pagos, $operacionId, $categoriaId, $pagoCompleto): void {
            $compra = Compra::query()->lockForUpdate()->findOrFail($compra->id);
            $caja = Caja::query()->lockForUpdate()->findOrFail($caja->id);
            $existentes = Pago::query()->where('operacion_id', $operacionId)->get();
            if ($existentes->isNotEmpty()) {
                if ($existentes->contains(fn (Pago $pago): bool => (int) $pago->compra_id !== (int) $compra->id)) {
                    throw ValidationException::withMessages(['pagos' => 'La operación pertenece a otra compra.']);
                }

                return;
            }
            if (! $caja->estado) {
                throw ValidationException::withMessages(['pagos' => 'La caja está cerrada.']);
            }
            if (! $caja->estaEnHorario()) {
                throw ValidationException::withMessages(['pagos' => "Operación no permitida: La caja '{$caja->nombre}' se encuentra fuera de su horario de atención ({$caja->horario_atencion})."]);
            }
            $finanzas = app(EstadoFinancieroService::class);
            $total = ImporteMonetario::sumar(array_column($pagos, 'valor'));
            $saldoResultante = ImporteMonetario::sumar([$finanzas->totalAprobado($compra), $total]);
            if (ImporteMonetario::comparar($total, 0) <= 0 || ImporteMonetario::comparar($saldoResultante, $compra->valor) > 0 || ($pagoCompleto && ImporteMonetario::comparar($saldoResultante, $compra->valor) !== 0)) {
                throw ValidationException::withMessages(['pagos' => 'El importe no corresponde al saldo vigente de la compra.']);
            }
            foreach (array_values($pagos) as $linea => $datos) {
                $tipo = TipoPago::query()->where('activo', true)->where('habilitado_punto_pago', true)->findOrFail($datos['tipo_pago_id']);
                $valor = ImporteMonetario::normalizar($datos['valor']);
                if (ImporteMonetario::comparar($valor, 0) <= 0) {
                    throw ValidationException::withMessages(['pagos' => 'Cada pago debe ser mayor que cero.']);
                }
                if ($tipo->tiene_limite_dinero_acumulado) {
                    $otraMoneda = Pago::query()->where('registro_caja_id', $caja->id)->where('moneda_id', '!=', $compra->moneda_id)
                        ->where('anulado_pdp', false)->whereHas('tipoPago', fn ($query) => $query->where('tiene_limite_dinero_acumulado', true))->exists();
                    if ($otraMoneda) {
                        throw ValidationException::withMessages(['pagos' => 'Esta caja tiene movimientos en otra moneda. Use una caja separada para conservar su acumulado.']);
                    }
                    $acumulado = ImporteMonetario::sumar([$caja->dinero_acumulado, $valor]);
                    if (ImporteMonetario::comparar($caja->limite_dinero_acumulado, 0) > 0 && ImporteMonetario::comparar($acumulado, $caja->limite_dinero_acumulado) > 0) {
                        throw ValidationException::withMessages(['pagos' => 'El pago supera el límite disponible de la caja.']);
                    }
                    $caja->update(['dinero_acumulado' => $acumulado]);
                }
                Pago::query()->create([
                    'compra_id' => $compra->id, 'tipo_pago_id' => $tipo->id,
                    'estado_pago_id' => $finanzas->estadoAprobado($tipo->id)->id,
                    'moneda_id' => $compra->moneda_id, 'valor' => $valor, 'fecha' => now(),
                    'actividad_categoria_id' => $categoriaId, 'registro_caja_id' => $caja->id,
                    'codigo_vaucher' => $datos['codigo_vaucher'] ?? null,
                    'operacion_id' => $operacionId, 'operacion_linea' => $linea,
                ]);
            }
            $finanzas->reconciliarCompra($compra);
        }, 3);
    }
}
