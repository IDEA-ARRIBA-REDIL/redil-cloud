<?php

namespace App\Console\Commands;

use App\Models\EstadoPago;
use App\Models\Matricula;
use App\Models\Pago;
use App\Services\ZonaPagosService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sonda de ZonaPagos (Cron Job).
 *
 * Según el manual de ZonaPagos (sección 7.3.2), el comercio debe ejecutar este proceso
 * cada 10-15 minutos para verificar transacciones que están en estado pendiente.
 *
 * Además, incluye una función dedicada para limpiar matrículas de pagos anulados/rechazados.
 */
class VerificarPagosPendientes extends Command
{
    protected $signature = 'pagos:verificar-zonapagos';

    protected $description = 'Verifica el estado de los pagos pendientes en ZonaPagos y ejecuta la limpieza dedicada de matrículas en pagos anulados/rechazados.';

    public function handle(): int
    {
        if (function_exists('tenancy') && ! tenancy()->initialized) {
            $tenants = \App\Models\Tenant::all();
            $this->info("Sonda ZonaPagos: Ejecutando verificación en {$tenants->count()} inquilino(s).");

            foreach ($tenants as $tenant) {
                $this->info("=== Inquilino: {$tenant->id} ===");
                $tenant->run(function () {
                    $this->ejecutarVerificacionInquilino();
                });
            }

            return 0;
        }

        return $this->ejecutarVerificacionInquilino();
    }

    public function ejecutarVerificacionInquilino(): int
    {
        try {
            Log::info('Sonda ZonaPagos: Iniciando ejecución en inquilino.');
            $this->info('Sonda ZonaPagos: Iniciando ejecución en inquilino.');

            // 1. Proceso para pagos PENDIENTES (Consultar pasarela ZonaPagos)
            $contadorActualizados = $this->verificarPagosPendientesZonaPagos();

            // 2. NUEVA FUNCIÓN DEDICADA SEPARADA: Proceso exclusivo para limpiar matrículas de pagos ANULADOS / RECHAZADOS
            $contadorLimpiadas = $this->limpiarMatriculasDePagosAnulados();

            $mensaje = "Sonda ZonaPagos: Proceso finalizado. Se actualizaron {$contadorActualizados} pagos pendientes y se eliminaron {$contadorLimpiadas} matrículas de pagos anulados.";
            Log::info($mensaje);
            $this->info($mensaje);

        } catch (Throwable $e) {
            Log::error('Sonda ZonaPagos: Error fatal.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->error('Error fatal en la Sonda. Revisa el log de Laravel.');

            return 1;
        }

        return 0;
    }

    /**
     * FUNCIÓN 1: Consulta a ZonaPagos el estado de los pagos que continúan en estado pendiente.
     */
    public function verificarPagosPendientesZonaPagos(): int
    {
        // Se consultan TODOS los pagos en estado pendiente sin filtro de tiempo en updated_at,
        // para que la Sonda verifique cualquier pago pendiente sin importar el tiempo transcurrido.
        $pagosPendientes = Pago::whereHas('estadoPago', function ($query) {
            $query->where('estado_pendiente', true);
        })
            ->whereHas('tipoPago', function ($query) {
                $query->where('key_reservada', 'zona');
            })
            ->with('compra.inscripciones', 'tipoPago')
            ->get();

        if ($pagosPendientes->isEmpty()) {
            $this->info('    - No se encontraron pagos pendientes por consultar en pasarela.');

            return 0;
        }

        $this->info("    - {$pagosPendientes->count()} pagos pendientes encontrados para verificar con ZonaPagos.");
        $zonaPagosService = new ZonaPagosService;
        $contadorActualizados = 0;

        foreach ($pagosPendientes as $pago) {
            if (! $pago->compra) {
                Log::warning("Sonda ZonaPagos: Pago ID {$pago->id} sin compra asociada. Se omite.");

                continue;
            }

            $this->info("--> Verificando Pago ID: {$pago->id}");

            $resultado = $zonaPagosService->verificarPago($pago);

            if (! $resultado['success']) {
                Log::error("Sonda ZonaPagos: Error al verificar Pago ID {$pago->id}: ".($resultado['message'] ?? 'desconocido'));

                continue;
            }

            $strResPago = $resultado['data']['str_res_pago'] ?? '';
            $datosTransaccion = $zonaPagosService->parsearRespuestaVerificacion($strResPago);
            $codigoEstadoExterno = $datosTransaccion['int_estado_pago'] ?? null;

            if (! $codigoEstadoExterno) {
                $this->info("    - Pago ID {$pago->id}: no se pudo extraer el código de estado. Se omite.");

                continue;
            }

            $nuevoEstado = EstadoPago::where('id_codigo_externo', $codigoEstadoExterno)
                ->where('tipo_pago_id', $pago->tipo_pago_id)
                ->first();

            if (! $nuevoEstado) {
                Log::error("Sonda ZonaPagos: No existe EstadoPago para código externo '{$codigoEstadoExterno}' y TipoPago ID {$pago->tipo_pago_id}.");

                continue;
            }

            if ($nuevoEstado->id === $pago->estado_pago_id) {
                $this->info("    - Pago ID {$pago->id}: sin cambio de estado.");

                continue;
            }

            if ($nuevoEstado->estado_pendiente) {
                $pago->touch();

                continue;
            }

            $referenciaPago = $datosTransaccion['int_n_pago'] ?: $datosTransaccion['int_ped_numero'] ?: null;

            $pago->update([
                'estado_pago_id' => $nuevoEstado->id,
                'referencia_pago' => $referenciaPago,
                'gateway_response' => $resultado['data'],
            ]);

            $this->info("    - Pago ID {$pago->id} actualizado a '{$nuevoEstado->nombre}'.");
            Log::info("Sonda ZonaPagos: Pago ID {$pago->id} actualizado a '{$nuevoEstado->nombre}'.");
            $contadorActualizados++;

            /**
             * MAPEO DE ESTADOS DE LA COMPRA ($compra->estado / Foreign Key a estados_pago.id):
             * -----------------------------------------------------------------------------
             * 1 = Compra Pendiente / Borrador
             * 2 = Compra Anulada / Cancelada
             * 3 = Compra Pagada / Finalizada Exitosamente
             * 4 = Compra Rechazada por Pasarela/Banco
             * 5 = Compra Pendiente por Finalizar en Pasarela (PSE / ZonaPagos)
             */
            if ($nuevoEstado->estado_final_inscripcion) {
                $compra = $pago->compra;
                if ($compra) {
                    $compra->update(['estado' => 3]); // 3 = PAGADA / APROBADA
                }

                $tipoCompra = strtoupper(trim($datosTransaccion['str_campo1'] ?? ''));

                if ($tipoCompra === 'ESCUELAS') {
                    $matricula = $pago->matricula;

                    if ($matricula) {
                        $matricula->update(['estado_pago_matricula' => 'pagada']);
                        $this->info("    - Matrícula ID {$matricula->id} actualizada a 'pagada'.");
                    }
                } elseif ($compra && $compra->inscripciones->isNotEmpty()) {
                    $compra->inscripciones()->update(['estado' => true]);
                }
            } elseif ($nuevoEstado->estado_anulado_inscripcion || (! $nuevoEstado->estado_pendiente && ! $nuevoEstado->estado_final_inscripcion)) {
                $compra = $pago->compra;
                if ($compra) {
                    $compraEstadoNuevo = ($nuevoEstado->id == 2) ? 2 : 4;
                    $compra->update(['estado' => $compraEstadoNuevo]); // 4 = RECHAZADA, 2 = ANULADA
                }

                Matricula::limpiarMatriculasDePagoFallido($pago);
                $this->info("    - Matrícula/Reserva liberada para Pago ID {$pago->id} por rechazo/anulación.");
            }
        }

        return $contadorActualizados;
    }

    /**
     * FUNCIÓN 2 SEPARADA EXCLUSIVA:
     * Consulta directamente la tabla de matrículas por su ID y elimina tabla por tabla
     * absolutamente cualquier matrícula borrador o no pagada (estado_pago_matricula != 'pagada').
     * Sin restricciones ni condicionales complejas.
     */
    public function limpiarMatriculasDePagosAnulados(): int
    {
        $this->info('--> Ejecutando limpieza directa tabla por tabla de matrículas no pagadas/anuladas...');

        // Consultamos todas las matrículas cuyo estado_pago_matricula NO sea 'pagada' (incluyendo nulos, pendientes, rechazadas, etc.)
        $matriculasNoPagadas = Matricula::where(function ($q) {
            $q->whereNull('estado_pago_matricula')
                ->orWhere('estado_pago_matricula', '!=', 'pagada');
        })->get();

        $contadorLimpiadas = 0;

        foreach ($matriculasNoPagadas as $mat) {
            // Eliminar tabla por tabla por el ID de la matrícula
            $eliminado = Matricula::eliminarMatriculaCompletaPorId($mat->id);
            if ($eliminado) {
                $contadorLimpiadas++;
                $this->info("    - [LIMPIEZA DIRECTA TABLA POR TABLA]: Matrícula ID {$mat->id} eliminada de todas las tablas.");
                Log::info("Limpieza Directa: Matrícula ID {$mat->id} eliminada de todas las tablas exitosamente.");
            }
        }

        return $contadorLimpiadas;
    }
}
