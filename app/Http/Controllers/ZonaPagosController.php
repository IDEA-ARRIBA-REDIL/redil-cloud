<?php

namespace App\Http\Controllers;

use App\Models\EstadoPago;
use App\Models\Pago;
use App\Services\ZonaPagosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZonaPagosController extends Controller
{
    /**
     * Recibe el callback GET que ZonaPagos envía al finalizar un pago (exitoso o fallido).
     *
     * Esta ruta debe estar configurada en ZonaPagos como la URL de retorno del comercio.
     * ZonaPagos envía en el GET el campo 'id_pago' (nuestro pago->id) para identificar la transacción.
     *
     * Flujo:
     *  1. Busca el Pago en nuestra BD por id_pago.
     *  2. Llama al servicio de verificación para obtener el estado REAL desde ZonaPagos.
     *  3. Parsea la respuesta para extraer el código de estado externo (ej: 1, 999, 1000).
     *  4. Busca el EstadoPago local que mapea ese código externo.
     *  5. Actualiza el Pago, la Compra y las Inscripciones/Matrículas en cascada.
     */
    public function handleCallback(Request $request)
    {
        $idReferencia = $request->input('id_pago');
        Log::info('ZonaPagos Callback recibido.', ['id_pago' => $idReferencia, 'query' => $request->all()]);

        if (! $idReferencia) {
            Log::error('ZonaPagos Callback: no se recibió id_pago.');

            return response()->json(['status' => 'error', 'message' => 'ID de pago no recibido.'], 400);
        }

        // Enrutador inteligente basado en prefijos
        if (str_starts_with($idReferencia, 'O-')) {
            $ofrendaId = substr($idReferencia, 2);

            return $this->procesarCallbackOfrenda($ofrendaId, $request);
        } elseif (str_starts_with($idReferencia, 'P-') || str_starts_with($idReferencia, 'A-')) {
            $pagoId = substr($idReferencia, 2);

            return $this->procesarCallbackPago($pagoId, $request);
        } else {
            // Compatibilidad hacia atrás: si no tiene prefijo, asumimos que es un Pago de Actividad
            Log::info("ZonaPagos Callback: id_pago '{$idReferencia}' sin prefijo. Procesando como Pago de Actividad por defecto.");

            return $this->procesarCallbackPago($idReferencia, $request);
        }
    }

    /**
     * Enrutador visual para cuando el usuario hace clic en "Volver al comercio" en ZonaPagos.
     * Lee el id_pago con prefijo y redirige a la vista final correspondiente.
     */
    public function retornoUsuario(Request $request)
    {
        $idReferencia = $request->input('id_pago');

        if (! $idReferencia) {
            // Si por alguna razón no llega, redirigir al home o dashboard
            return redirect('/');
        }

        // Enrutador inteligente basado en prefijos
        if (str_starts_with($idReferencia, 'O-')) {
            $ofrendaId = substr($idReferencia, 2);

            // TODO: Crear ruta para ofrenda finalizada cuando se desarrolle el módulo
            // return redirect()->route('ofrendas.finalizada', ['ofrenda' => $ofrendaId]);
            return redirect('/');
        } elseif (str_starts_with($idReferencia, 'P-') || str_starts_with($idReferencia, 'A-')) {
            $pagoId = substr($idReferencia, 2);

            return redirect()->route('carrito.compraFinalizada', ['pago' => $pagoId]);
        } else {
            // Compatibilidad hacia atrás: sin prefijo asumimos que es Pago de Actividad
            return redirect()->route('carrito.compraFinalizada', ['pago' => $idReferencia]);
        }
    }

    /**
     * Lógica para procesar un callback correspondiente a un Pago (Actividad / Matrícula / etc.)
     */
    private function procesarCallbackPago($pagoId, Request $request)
    {
        $pago = Pago::with('compra.inscripciones', 'tipoPago')->find($pagoId);

        if (! $pago) {
            Log::error("ZonaPagos Callback: Pago ID {$pagoId} no encontrado en BD.");

            return response()->json(['status' => 'error', 'message' => 'Registro de pago no encontrado.'], 404);
        }

        // Consultamos el estado REAL de la transacción en ZonaPagos
        $zonaPagosService = new ZonaPagosService;
        $resultado = $zonaPagosService->verificarPago($pago);

        if (! $resultado['success']) {
            Log::error("ZonaPagos Callback: Verificación fallida para Pago ID {$pagoId}.", $resultado);

            return response()->json(['status' => 'error', 'message' => 'No se pudo verificar el pago.'], 500);
        }

        // Parseamos la respuesta para extraer los campos individuales
        $strResPago = $resultado['data']['str_res_pago'] ?? '';
        $datosTransaccion = $zonaPagosService->parsearRespuestaVerificacion($strResPago);
        $codigoEstadoExterno = $datosTransaccion['int_estado_pago'] ?? null;

        Log::info("ZonaPagos Callback: Pago ID {$pagoId} — Código externo recibido: {$codigoEstadoExterno}");

        if (! $codigoEstadoExterno) {
            Log::warning("ZonaPagos Callback: No se pudo extraer el código de estado para Pago ID {$pagoId}.");

            return response()->json(['status' => 'warning', 'message' => 'No se pudo determinar el estado del pago.']);
        }

        // Buscamos el EstadoPago local que corresponde al código externo de ZonaPagos
        $nuevoEstado = EstadoPago::where('id_codigo_externo', $codigoEstadoExterno)
            ->where('tipo_pago_id', $pago->tipo_pago_id)
            ->first();

        if (! $nuevoEstado) {
            Log::error("ZonaPagos Callback: No existe EstadoPago para código externo '{$codigoEstadoExterno}' y TipoPago ID {$pago->tipo_pago_id}. Verifica la tabla estados_pago.");

            return response()->json(['status' => 'error', 'message' => "Estado externo '{$codigoEstadoExterno}' no configurado."], 500);
        }

        // Solo actualizamos si el estado realmente cambió
        if ($nuevoEstado->id === $pago->estado_pago_id) {
            Log::info("ZonaPagos Callback: Pago ID {$pagoId} ya está en el estado '{$nuevoEstado->nombre}'. Sin cambios.");

            return response()->json(['status' => 'ok', 'message' => 'Sin cambios (mismo estado).']);
        }

        // Guardamos el número de transacción de ZonaPagos como referencia de pago
        $referenciaPago = $datosTransaccion['int_n_pago'] ?: $datosTransaccion['int_ped_numero'] ?: null;

        // Actualizamos el Pago con el estado real y la respuesta completa de la API
        $pago->update([
            'estado_pago_id' => $nuevoEstado->id,
            'referencia_pago' => $referenciaPago,
            'gateway_response' => $resultado['data'],
        ]);

        Log::info("ZonaPagos Callback: Pago ID {$pagoId} actualizado a '{$nuevoEstado->nombre}'.");

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
                Log::info("ZonaPagos Callback: Compra ID {$compra->id} actualizada a PAGADA (estado = 3).");
            }

            // Detectar tipo de compra via str_campo1 (str_opcional1 enviado al iniciar pago)
            $tipoCompra = strtoupper(trim($datosTransaccion['str_campo1'] ?? ''));

            if ($tipoCompra === 'ESCUELAS') {
                // Usamos la relación definida en el modelo Pago
                $matricula = $pago->matricula;

                if ($matricula) {
                    $matricula->update(['estado_pago_matricula' => 'pagada']);
                    Log::info("ZonaPagos Callback: Matrícula ID {$matricula->id} actualizada a 'pagada'.");
                } else {
                    Log::warning("ZonaPagos Callback: No se encontró matrícula para Pago ID {$pago->id}.");
                }
            } elseif ($compra && $compra->inscripciones->isNotEmpty()) {
                // Actualizar inscripciones normales
                $compra->inscripciones()->update(['estado' => true]);
                Log::info("ZonaPagos Callback: {$compra->inscripciones->count()} inscripciones actualizadas.");
            }
        } elseif ($nuevoEstado->estado_anulado_inscripcion || (! $nuevoEstado->estado_pendiente && ! $nuevoEstado->estado_final_inscripcion)) {
            $compra = $pago->compra;

            if ($compra) {
                $compraEstadoNuevo = ($nuevoEstado->id == 2) ? 2 : 4;
                $compra->update(['estado' => $compraEstadoNuevo]); // 4 = RECHAZADA, 2 = ANULADA
                Log::info("ZonaPagos Callback: Compra ID {$compra->id} actualizada a estado = {$compraEstadoNuevo}.");
            }

            // Si el pago fue RECHAZADO, ANULADO o CANCELADO → Liberar matrícula borrador, horario y cupos
            \App\Models\Matricula::limpiarMatriculasDePagoFallido($pago);
            Log::info("ZonaPagos Callback: Matrícula/Reserva liberada para Pago ID {$pago->id} por rechazo/anulación.");
        }

        return response()->json([
            'status' => 'success',
            'pago_id' => $pagoId,
            'nuevo_estado' => $nuevoEstado->nombre,
        ]);
    }

    /**
     * Lógica para procesar un callback correspondiente a una Ofrenda.
     */
    private function procesarCallbackOfrenda($ofrendaId, Request $request)
    {
        Log::info("ZonaPagos Callback: Procesando Ofrenda ID {$ofrendaId}.");

        // TODO: Implementar la lógica para ofrendas.
        // Ej: $ofrenda = Ofrenda::find($ofrendaId);
        // Verificar en ZonaPagos, buscar estado y actualizar.

        return response()->json([
            'status' => 'success',
            'ofrenda_id' => $ofrendaId,
            'message' => 'Callback de ofrenda recibido. Lógica pendiente de implementar.',
        ]);
    }

    /**
     * Endpoint auxiliar para verificar manualmente el estado de un pago.
     * Útil para debugging y soporte. Devuelve el estado crudo de ZonaPagos.
     */
    public function verificarEstadoPago(Pago $pago)
    {
        $zonaPagosService = new ZonaPagosService;
        $resultado = $zonaPagosService->verificarPago($pago);

        if ($resultado['success']) {
            $datosTransaccion = $zonaPagosService->parsearRespuestaVerificacion(
                $resultado['data']['str_res_pago'] ?? ''
            );
            $resultado['datos_parseados'] = $datosTransaccion;
        }

        return response()->json($resultado);
    }
}
