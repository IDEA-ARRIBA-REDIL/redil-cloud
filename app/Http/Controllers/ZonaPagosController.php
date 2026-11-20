<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Services\ZonaPagosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ZonaPagosController extends Controller
{
    public function __construct(
        protected ZonaPagosService $zonaPagosService,
    ) {}

    /**
     * Recibe el callback GET que ZonaPagos envía al finalizar un pago (exitoso o fallido).
     *
     * Nunca confía únicamente en los parámetros GET del callback (no tienen firma HMAC);
     * siempre verifica el estado real contra `POST /VerificacionPago`.
     */
    public function handleCallback(Request $request): JsonResponse|Response
    {
        $idReferencia = $request->input('id_pago');
        Log::info('ZonaPagos Callback recibido.', ['id_pago' => $idReferencia, 'query' => $request->all()]);

        if (! $idReferencia) {
            Log::error('ZonaPagos Callback: no se recibió id_pago.');

            return response()->json(['status' => 'error', 'message' => 'ID de pago no recibido.'], 400);
        }

        // Enrutador inteligente basado en prefijos
        if (str_starts_with((string) $idReferencia, 'O-')) {
            $ofrendaId = substr((string) $idReferencia, 2);

            return $this->procesarCallbackOfrenda($ofrendaId);
        }

        if (str_starts_with((string) $idReferencia, 'P-') || str_starts_with((string) $idReferencia, 'A-')) {
            $pagoId = substr((string) $idReferencia, 2);

            return $this->procesarCallbackPago($pagoId);
        }

        Log::info("ZonaPagos Callback: id_pago '{$idReferencia}' sin prefijo. Procesando como Pago de Actividad por defecto.");

        return $this->procesarCallbackPago((string) $idReferencia);
    }

    /**
     * Enrutador visual para cuando el usuario hace clic en "Volver al comercio" en ZonaPagos.
     * Verifica proactivamente el pago si aún está pendiente antes de mostrar la pantalla final.
     */
    public function retornoUsuario(Request $request): RedirectResponse
    {
        $idReferencia = $request->input('id_pago');

        if (! $idReferencia) {
            return redirect('/');
        }

        if (str_starts_with((string) $idReferencia, 'O-')) {
            $ofrendaId = (int) substr((string) $idReferencia, 2);

            return app(OfrendaDonacionController::class)->retornoUsuario($ofrendaId);
        }

        $pagoId = (str_starts_with((string) $idReferencia, 'P-') || str_starts_with((string) $idReferencia, 'A-'))
            ? substr((string) $idReferencia, 2)
            : (string) $idReferencia;

        if (ctype_digit($pagoId) && Schema::hasTable('pagos')) {
            $pago = Pago::with(['estadoPago', 'tipoPago', 'compra'])->find((int) $pagoId);
            if ($pago && (! $pago->estadoPago || $pago->estadoPago->estado_pendiente)) {
                $this->zonaPagosService->sincronizarPagoDesdePasarela($pago, evaluarAbandono: false);
            }
        }

        return redirect()->route('carrito.compraFinalizada', ['pago' => $pagoId]);
    }

    /**
     * Procesa un callback correspondiente a un Pago (Actividad / Matrícula / Abono)
     * delegando toda la lógica transaccional al servicio canónico `ZonaPagosService`.
     */
    private function procesarCallbackPago(string $pagoId): JsonResponse
    {
        if (! ctype_digit($pagoId)) {
            Log::error("ZonaPagos Callback: Pago ID inválido '{$pagoId}'.");

            return response()->json(['status' => 'error', 'message' => 'ID de pago inválido.'], 400);
        }

        $pago = Pago::with(['compra.inscripciones', 'tipoPago', 'estadoPago'])->find((int) $pagoId);

        if (! $pago) {
            Log::error("ZonaPagos Callback: Pago ID {$pagoId} no encontrado en BD.");

            return response()->json(['status' => 'error', 'message' => 'Registro de pago no encontrado.'], 404);
        }

        $resultado = $this->zonaPagosService->sincronizarPagoDesdePasarela($pago, evaluarAbandono: false);

        if (! $resultado['success']) {
            $statusCode = ($resultado['error_red'] ?? false) ? 500 : 422;
            Log::error("ZonaPagos Callback: Sincronización fallida para Pago ID {$pagoId}.", $resultado);

            return response()->json([
                'status' => 'error',
                'message' => $resultado['message'] ?? 'No se pudo verificar el pago.',
            ], $statusCode);
        }

        return response()->json([
            'status' => $resultado['actualizado'] ? 'success' : 'ok',
            'pago_id' => $pago->id,
            'nuevo_estado' => $resultado['estado'] ?? null,
            'motivo' => $resultado['motivo'] ?? null,
        ]);
    }

    /**
     * Procesa un callback correspondiente a una Ofrenda (`O-{id}`).
     */
    private function procesarCallbackOfrenda(string $ofrendaId): Response
    {
        Log::info("ZonaPagos Callback: Procesando Ofrenda ID {$ofrendaId}.");

        return app(OfrendaDonacionController::class)->procesarCallbackOfrenda((int) $ofrendaId);
    }

    /**
     * Endpoint auxiliar para verificar manualmente el estado de un pago.
     * Devuelve el estado crudo de ZonaPagos y sus campos parseados.
     */
    public function verificarEstadoPago(Pago $pago): JsonResponse
    {
        $resultado = $this->zonaPagosService->verificarPago($pago);

        if ($resultado['success']) {
            $datosTransaccion = $this->zonaPagosService->parsearRespuestaVerificacion(
                (string) ($resultado['data']['str_res_pago'] ?? '')
            );
            $resultado['datos_parseados'] = $datosTransaccion;
        }

        return response()->json($resultado);
    }
}
