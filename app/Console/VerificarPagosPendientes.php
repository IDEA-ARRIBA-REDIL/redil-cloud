<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Pago;
use App\Models\EstadoPago;
use App\Services\ZonaPagosService;
use Throwable;

class VerificarPagosPendientes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagos:verificar-zonapagos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el estado de los pagos pendientes de ZonaPagos y los actualiza en cascada (Pago, Compra, Inscripcion).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            Log::info('Sonda ZonaPagos: Iniciando verificación.');
            $this->info('Sonda ZonaPagos: Iniciando verificación.');

            // 1. Buscamos los Pagos (no las Compras) que están pendientes y son de ZonaPagos.
            $pagosPendientes = Pago::whereHas('estadoPago', function ($query) {
                $query->where('estado_pendiente', true);
            })->whereHas('tipoPago', function ($query) {
                $query->where('key_reservada', 'zona');
            })->with('compra.inscripciones')->get(); // Precargamos las relaciones para eficiencia

            if ($pagosPendientes->isEmpty()) {
                Log::info('Sonda ZonaPagos: No se encontraron pagos pendientes.');
                $this->info('Sonda ZonaPagos: No se encontraron pagos pendientes.');
                return 0;
            }

            $this->info("Sonda ZonaPagos: Se encontraron {$pagosPendientes->count()} pagos pendientes.");
            $zonaPagosService = new ZonaPagosService();
            $contadorActualizados = 0;

            foreach ($pagosPendientes as $pago) {
                // Asegurarse de que el pago tiene una compra asociada antes de proceder.
                if (!$pago->compra) {
                    Log::warning("Sonda ZonaPagos: El Pago ID {$pago->id} no tiene una compra asociada. Se omite.");
                    continue;
                }

                $this->info("--> Verificando Pago ID: {$pago->id}");
                $resultado = $zonaPagosService->verificarPago($pago);

                if (!$resultado['success']) {
                    Log::error("Sonda ZonaPagos: Error al verificar  xcxcxvxvcvx Pago ID: {$pago->id}. Mensaje: " . ($resultado['message'] ?? 'Error desconocido'));
                    continue; // Continuar con el siguiente pago
                }

                $datosRespuesta = $resultado['data'];
                $partesRespuesta = explode('|', $datosRespuesta['str_res_pago']);
                $codigoEstadoExterno = $partesRespuesta[4] ?? null;

                if (!$codigoEstadoExterno) {
                    continue;
                }

                // 2. Buscamos el nuevo estado en nuestra BD
                $nuevoEstado = EstadoPago::where('id_codigo_externo', $codigoEstadoExterno)
                    ->where('tipo_pago_id', $pago->tipo_pago_id)
                    ->first();

                // 3. Si el estado ha cambiado y ya no es pendiente, actualizamos en cascada.
                if ($nuevoEstado && !$nuevoEstado->estado_pendiente && $nuevoEstado->id !== $pago->estado_pago_id) {
                    // Paso A: Actualizar el Pago
                    $pago->update(['estado_pago_id' => $nuevoEstado->id]);
                    $this->info("    - Pago ID: {$pago->id} actualizado a '{$nuevoEstado->nombre}'.");

                    // Paso B: Actualizar la Compra (si el pago fue exitoso)
                    if ($nuevoEstado->estado_final_inscripcion) {
                        $compra = $pago->compra;
                        $compra->update(['estado' => 3]); // 3 = PAGADA según tu migración
                        $this->info("    - Compra ID: {$compra->id} actualizada a 'PAGADA'.");

                        // Paso C: Actualizar las Inscripciones asociadas
                        if ($compra->inscripciones->isNotEmpty()) {
                            // Usamos un update masivo para eficiencia
                            $compra->inscripciones()->update(['estado' => true]); // true = Activa/Pagada
                            $this->info("    - Se actualizaron {$compra->inscripciones->count()} inscripciones asociadas.");
                        }
                    }
                    $contadorActualizados++;
                } else {
                    $this->info("    - El estado del Pago ID: {$pago->id} no ha cambiado.");
                }
            }

            Log::info("Sonda ZonaPagos: Proceso finalizado.  asdsad Se actualizaron {$contadorActualizados} registros.");
            $this->info("Sonda ZonaPagos: Proceso finalizado. asdsada Se actualizaron {$contadorActualizados} registros.");
        } catch (Throwable $e) {
            Log::error('Sonda ZonaPagos: Error fatal en la ejecución.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->error('Ocurrió un error fatal. Revisa el archivo de log de Laravel.');
            return 1;
        }

        return 0;
    }
}
