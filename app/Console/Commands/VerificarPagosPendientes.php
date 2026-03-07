<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Pago;
use App\Models\EstadoPago;
use App\Models\Matricula;
use App\Services\ZonaPagosService;
use Throwable;

class VerificarPagosPendientes extends Command
{
    protected $signature = 'pagos:verificar-zonapagos';
    protected $description = 'Verifica el estado de los pagos pendientes de ZonaPagos y los actualiza en cascada.';

    public function handle()
    {
        try {
            Log::info('Sonda ZonaPagos: Iniciando verificación.');
            $this->info('Sonda ZonaPagos: Iniciando verificación.');

            // He vuelto a tu lógica original para buscar pagos pendientes, es más robusta.
            $pagosPendientes = Pago::whereHas('estadoPago', function ($query) {
                $query->where('estado_pendiente', true);
            })->whereHas('tipoPago', function ($query) {
                $query->where('key_reservada', 'zona');
            })->get();

            if ($pagosPendientes->isEmpty()) {
                Log::info('Sonda ZonaPagos: No se encontraron pagos pendientes.');
                $this->info('Sonda ZonaPagos: No se encontraron pagos pendientes.');
                return 0;
            }

            $this->info("Sonda ZonaPagos: Se encontraron {$pagosPendientes->count()} pagos pendientes para verificar.");
            $zonaPagosService = new ZonaPagosService();
            $contadorActualizados = 0;

            foreach ($pagosPendientes as $pago) {
                if (!$pago->compra) {
                    Log::warning("Sonda ZonaPagos: El Pago ID {$pago->id} no tiene una compra asociada. Se omite.");
                    continue;
                }

                $this->info("--> Verificando Pago ID: {$pago->id}");
                $resultado = $zonaPagosService->verificarPago($pago);

                if (!$resultado['success']) {
                    Log::error("Sonda ZonaPagos: Error al verificar Pago ID: {$pago->id}. Mensaje: " . ($resultado['message'] ?? 'Error desconocido'));
                    continue;
                }

                $datosTransaccion = $zonaPagosService->parsearRespuestaVerificacion($resultado['data']['str_res_pago']);
                $codigoEstadoExterno = $datosTransaccion['int_estado_pago'] ?? null;

                if ($codigoEstadoExterno) {
                    $nuevoEstado = EstadoPago::where('id_codigo_externo', $codigoEstadoExterno)
                        ->where('tipo_pago_id', $pago->tipo_pago_id)
                        ->first();

                    if ($nuevoEstado && !$nuevoEstado->estado_pendiente && $nuevoEstado->id !== $pago->estado_pago_id) {
                        $pago->update(['estado_pago_id' => $nuevoEstado->id]);
                        $this->info("    - Pago ID: {$pago->id} actualizado a '{$nuevoEstado->nombre}'.");
                        $contadorActualizados++;

                        if ($nuevoEstado->estado_final_inscripcion) {
                            $compra = $pago->compra;
                            $compra->update(['estado' => 3]); // 3 = PAGADA
                            $this->info("    - Compra ID: {$compra->id} actualizada a 'PAGADA'.");

                            // --- INICIO DE LA CORRECCIÓN Y LÓGICA ROBUSTA ---
                            // 1. Obtenemos el valor del campo opcional.
                            $valorOpcional1 = $datosTransaccion['str_campo1'] ?? null;

                            // 2. "Normalizamos" el valor: quitamos espacios y lo ponemos en mayúsculas.
                            $tipoCompra = $valorOpcional1 ? strtoupper(trim($valorOpcional1)) : null;

                            $this->info("    -> Tipo de compra detectado: '{$tipoCompra}'");
                            Log::info("Tipo de compra para Pago ID {$pago->id}: '{$tipoCompra}'");

                            // 3. Comparamos el valor normalizado. Esta comparación es mucho más segura.
                            if ($tipoCompra === 'ESCUELAS') {
                                $this->info("    -> Coincide con ESCUELAS. Actualizando matrícula...");
                                $matricula = Matricula::where('referencia_pago', $pago->id)->first();
                                if ($matricula) {
                                    $matricula->update(['estado_pago_matricula' => 'pagada']);
                                    $this->info("        - Matrícula ID: {$matricula->id} actualizada a 'pagada'.");
                                } else {
                                    $this->warn("        - ADVERTENCIA: No se encontró la matrícula para el Pago ID {$pago->id}.");
                                }
                            } else {
                                // Lógica para inscripciones normales
                                if ($compra->inscripciones->isNotEmpty()) {
                                    $compra->inscripciones()->update(['estado' => true]);
                                    $this->info("    - Se actualizaron {$compra->inscripciones->count()} inscripciones asociadas.");
                                }
                            }
                            // --- FIN DE LA CORRECCIÓN ---
                        }
                    } else {
                        $this->info("    - El estado del Pago ID: {$pago->id} no ha cambiado.");
                    }
                }
            }

            Log::info("Sonda ZonaPagos: Proceso finalizado. Se actualizaron {$contadorActualizados} registros.");
            $this->info("Sonda ZonaPagos: Proceso finalizado. Se actualizaron {$contadorActualizados} registros.");
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
