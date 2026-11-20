<?php

namespace App\Console\Commands;

use App\Http\Controllers\OfrendaDonacionController;
use App\Models\Matricula;
use App\Models\Ofrenda;
use App\Models\Pago;
use App\Services\EstadoFinancieroService;
use App\Services\ZonaPagosService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sonda de ZonaPagos (Cron Job obligatorio para certificación PSE / ACH).
 *
 * Programada cada 5 minutos en `routes/console.php`.
 *
 * Ejecuta 3 etapas seguras y transaccionales por cada tenant:
 *  1. Consulta a ZonaPagos los `Pago` pendientes (`key_reservada = 'zona'`), actualiza
 *     aprobaciones/rechazos y vence transacciones abandonadas tras la ventana configurada (45 min).
 *  1.1 Verifica las `Ofrenda` online pendientes (`ingresada_por = 3`).
 *  2. Limpia de forma SEGURA únicamente reservas expiradas que nunca dieron clic en pagar (30 min)
 *     o matrículas pendientes cuyo pago YA está definitivamente rechazado/anulado/abandonado,
 *     restaurando siempre `cupos_disponibles`. NUNCA borra matrículas con pago PSE activo.
 */
class VerificarPagosPendientes extends Command
{
    protected $signature = 'pagos:verificar-zonapagos
                            {--minutos-pasarela= : Sobreescribe los minutos antes de marcar un pago en pasarela como abandonado (default config: 45)}
                            {--minutos-borrador= : Sobreescribe los minutos antes de expirar borradores sin clic en pagar (default config: 30)}';

    protected $description = 'Verifica el estado de los pagos pendientes en ZonaPagos, vence abandonados y limpia de forma segura reservas fallidas restaurando cupos.';

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
            $minutosPasarela = $this->option('minutos-pasarela') !== null
                ? (int) $this->option('minutos-pasarela')
                : (int) config('services.zonapagos.minutos_abandono_pasarela', 45);

            $minutosBorrador = $this->option('minutos-borrador') !== null
                ? (int) $this->option('minutos-borrador')
                : (int) config('services.zonapagos.minutos_abandono_borrador', 30);

            Log::info('Sonda ZonaPagos: Iniciando ejecución en inquilino.', [
                'minutos_pasarela' => $minutosPasarela,
                'minutos_borrador' => $minutosBorrador,
            ]);
            $this->info('Sonda ZonaPagos: Iniciando ejecución en inquilino.');

            // 1. Proceso para pagos PENDIENTES en ZonaPagos (incluye vencimiento de abandonados tras 45 min)
            $contadorActualizados = $this->verificarPagosPendientesZonaPagos($minutosPasarela);

            // 1.1 Proceso para ofrendas online PENDIENTES
            $contadorOfrendasActualizadas = $this->verificarOfrendasPendientesZonaPagos();

            // 2. Limpieza SEGURA de borradores sin pago expirados (30 min) y reservas de pagos ya fallidos
            $contadorLimpiadas = $this->limpiarReservasBorradorYFallidas($minutosBorrador);

            $mensaje = "Sonda ZonaPagos: Proceso finalizado. Se actualizaron {$contadorActualizados} pagos pendientes, {$contadorOfrendasActualizadas} ofrendas pendientes y se liberaron {$contadorLimpiadas} matrículas expiradas/fallidas.";
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
     * PASO 1: Consulta a ZonaPagos el estado de los pagos que continúan en estado pendiente.
     */
    public function verificarPagosPendientesZonaPagos(?int $minutosAbandonoPasarela = null): int
    {
        $pagosPendientes = Pago::query()
            ->whereHas('estadoPago', function ($query) {
                $query->where('estado_pendiente', true);
            })
            ->whereHas('tipoPago', function ($query) {
                $query->where('key_reservada', 'zona');
            })
            ->with(['compra.inscripciones', 'tipoPago', 'estadoPago'])
            ->get();

        if ($pagosPendientes->isEmpty()) {
            $this->info('    - No se encontraron pagos pendientes por consultar en pasarela.');

            return 0;
        }

        $this->info("    - {$pagosPendientes->count()} pagos pendientes encontrados para verificar con ZonaPagos.");
        $zonaPagosService = app(ZonaPagosService::class);
        $contadorActualizados = 0;

        foreach ($pagosPendientes as $pago) {
            if (! $pago->compra) {
                Log::warning("Sonda ZonaPagos: Pago ID {$pago->id} sin compra asociada. Se omite.");

                continue;
            }

            $this->info("--> Verificando Pago ID: {$pago->id}");
            $resultado = $zonaPagosService->sincronizarPagoDesdePasarela(
                $pago,
                evaluarAbandono: true,
                minutosAbandonoPasarela: $minutosAbandonoPasarela
            );

            if (! $resultado['success']) {
                Log::error("Sonda ZonaPagos: Error al verificar Pago ID {$pago->id}: ".($resultado['message'] ?? 'desconocido'));

                continue;
            }

            if ($resultado['actualizado']) {
                $contadorActualizados++;
                $this->info("    - Pago ID {$pago->id} actualizado a '{$resultado['estado']}' (motivo: {$resultado['motivo']}).");
            } else {
                $this->info("    - Pago ID {$pago->id}: sin cambio definitivo ({$resultado['motivo']}).");
            }
        }

        return $contadorActualizados;
    }

    /**
     * PASO 2 SEGURO: Libera únicamente:
     *  A) Matrículas borrador que NUNCA hicieron clic en pagar (`tipo_pago_id IS NULL && payment_url IS NULL`)
     *     y superaron la ventana de abandono (default 30 minutos).
     *  B) Matrículas pendientes cuyo `Pago` asociado YA está en un estado rechazado/anulado/abandonado
     *     (respaldo para pagos fallidos o registros históricos legacy).
     *
     * NUNCA elimina matrículas con un pago activo pendiente en ZonaPagos/PSE o aprobado.
     */
    public function limpiarReservasBorradorYFallidas(?int $minutosAbandonoBorrador = null): int
    {
        $minutosLimite = $minutosAbandonoBorrador ?? (int) config('services.zonapagos.minutos_abandono_borrador', 30);
        $estadoFinanciero = app(EstadoFinancieroService::class);
        $contadorLimpiadas = 0;

        $this->info("--> Ejecutando limpieza segura de borradores expirados (>= {$minutosLimite} min) y reservas fallidas...");

        // 2A. Matrículas borrador pendientes que nunca llegaron a una pasarela y superaron los 30 minutos
        $borradoresExpirados = Matricula::query()
            ->where('estado_pago_matricula', 'pendiente')
            ->where('created_at', '<=', now()->subMinutes($minutosLimite))
            ->get();

        foreach ($borradoresExpirados as $matricula) {
            if ($estadoFinanciero->limpiarReservaBorradorExpirada($matricula)) {
                $contadorLimpiadas++;
                $this->info("    - [BORRADOR EXPIRADO]: Matrícula ID {$matricula->id} liberada y cupo restaurado.");
                Log::info("Sonda ZonaPagos: Matrícula borrador ID {$matricula->id} liberada por inactividad (>= {$minutosLimite} min).");
            }
        }

        // 2B. Matrículas pendientes vinculadas a un Pago que YA está definitivamente rechazado/anulado/abandonado
        $matriculasConPagoFallido = Matricula::query()
            ->where('estado_pago_matricula', 'pendiente')
            ->get();

        foreach ($matriculasConPagoFallido as $matricula) {
            $pago = $matricula->pago_id
                ? Pago::with('estadoPago')->find($matricula->pago_id)
                : ($matricula->referencia_pago && ctype_digit((string) $matricula->referencia_pago)
                    ? Pago::with('estadoPago')->find((int) $matricula->referencia_pago)
                    : null);

            if (! $pago || ! $pago->estadoPago) {
                continue;
            }

            // Protección absoluta: jamás tocar matrículas de pagos pendientes o aprobados
            if ($pago->estadoPago->estado_pendiente || $pago->estadoPago->estado_final_inscripcion) {
                continue;
            }

            if ($matricula->compra_id && $matricula->pago_id) {
                $limpiadas = $estadoFinanciero->limpiarReservasFallidas($pago);
                if ($limpiadas > 0) {
                    $contadorLimpiadas += $limpiadas;
                    $this->info("    - [PAGO FALLIDO]: Matrícula ID {$matricula->id} liberada y cupo restaurado.");
                }
            } elseif ($estadoFinanciero->limpiarReservaLegacyFallida($matricula, $pago)) {
                $contadorLimpiadas++;
                $this->info("    - [PAGO FALLIDO LEGACY]: Matrícula ID {$matricula->id} liberada y cupo restaurado.");
            }
        }

        return $contadorLimpiadas;
    }

    /**
     * Alias de compatibilidad hacia atrás; redirige a la limpieza segura.
     */
    public function limpiarMatriculasDePagosAnulados(): int
    {
        return $this->limpiarReservasBorradorYFallidas();
    }

    /**
     * Consulta a ZonaPagos el estado de las ofrendas online (ingresada_por = 3) pendientes.
     */
    public function verificarOfrendasPendientesZonaPagos(): int
    {
        $ofrendasPendientes = Ofrenda::where('ingresada_por', 3)
            ->whereHas('estadoPago', function ($query) {
                $query->where('estado_pendiente', true);
            })
            ->with(['estadoPago', 'tipoOfrenda', 'destinatarioRelacion', 'moneda', 'usuario', 'tipoPago'])
            ->get();

        if ($ofrendasPendientes->isEmpty()) {
            $this->info('    - No se encontraron ofrendas online pendientes por consultar.');

            return 0;
        }

        $this->info("    - {$ofrendasPendientes->count()} ofrendas online pendientes encontradas para verificar con ZonaPagos.");
        $ofrendaController = app(OfrendaDonacionController::class);
        $contadorActualizadas = 0;

        foreach ($ofrendasPendientes as $ofrenda) {
            $estadoAnterior = $ofrenda->estado_pago_id;
            $ofrendaController->verificarYActualizarOfrenda($ofrenda);

            if ($ofrenda->estado_pago_id !== $estadoAnterior) {
                $contadorActualizadas++;
                $this->info("    - Ofrenda ID {$ofrenda->id} actualizada.");
            }
        }

        return $contadorActualizadas;
    }
}
