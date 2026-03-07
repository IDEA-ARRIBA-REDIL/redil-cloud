<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\EstadoPago;
use App\Services\ZonaPagosService;
use App\Models\Compra;
use Illuminate\Support\Facades\Log;

class ZonaPagosController extends Controller
{
    public function handleCallback(Request $request)
    {
        $pagoId = $request->input('id_pago');
        Log::info('Callback de ZonaPagos (Prueba) recibido para id_pago: ' . $pagoId);

        if (!$pagoId) {
            Log::error('Callback de ZonaPagos no incluyó un id_pago.');
            return response()->json(['status' => 'error', 'message' => 'ID de pago no recibido'], 400);
        }

        $pago = Pago::find($pagoId);
        if (!$pago) {
            Log::error('Callback para un pago no existente en la base de datos. ID: ' . $pagoId);
            return response()->json(['status' => 'error', 'message' => 'Registro de pago no encontrado'], 404);
        }

        $zonaPagosService = new ZonaPagosService();
        $resultadoVerificacion = $zonaPagosService->verificarPago($pago);

        // --- INICIO DE LA LÓGICA SIMPLIFICADA PARA PRUEBAS ---

        if ($resultadoVerificacion['success']) {
            // Si la verificación es exitosa, actualizamos el pago.
            $datosPago = $resultadoVerificacion['data'];

            $pago->update([
                'estado_pago_id' => 9,
                'referencia_pago' => $datosPago['int_n_pago'] ?? 'Respuesta-OK',
                'gateway_response' => json_encode($resultadoVerificacion['raw_response']) // Guardamos la respuesta para revisión
            ]);

            Log::info('Pago ID ' . $pagoId . ' actualizado a estado 9 (Éxito en prueba).');
            // Puedes redirigir a una página de "gracias" o devolver una respuesta JSON.
            return response()->json(['status' => 'success', 'pago_id' => $pagoId, 'nuevo_estado' => 9]);
        } else {
            // Si la verificación falla, actualizamos con los valores de error.
            $pago->update([
                'estado_pago_id' => 1000,
                'referencia_pago' => 'xxx1111',
                'gateway_response' => json_encode($resultadoVerificacion['response'] ?? ['error' => 'No se pudo verificar'])
            ]);

            Log::warning('Pago ID ' . $pagoId . ' actualizado a estado 1000 (Fallo en prueba).');
            return response()->json(['status' => 'failure', 'pago_id' => $pagoId, 'nuevo_estado' => 1000]);
        }

        // --- FIN DE LA LÓGICA SIMPLIFICADA ---
    }

    public function verificarEstadoPago(Pago $pago)
    {

        $zonaPagosService = new ZonaPagosService();
        $resultado = $zonaPagosService->verificarPago($pago);

        return response()->json($resultado);
    }
}
