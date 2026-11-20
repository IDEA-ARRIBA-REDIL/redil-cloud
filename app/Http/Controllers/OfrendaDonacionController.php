<?php

namespace App\Http\Controllers;

use App\Mail\DefaultMail;
use App\Models\Configuracion;
use App\Models\EstadoPago;
use App\Models\Iglesia;
use App\Models\Ofrenda;
use App\Services\ZonaPagosOfrendaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OfrendaDonacionController extends Controller
{
    public function __construct(protected ZonaPagosOfrendaService $zonaPagosOfrendaService) {}

    /**
     * Muestra la página pública del formulario de Donaciones / Ofrendas Online en 3 pestañas.
     */
    public function formularioDonaciones(): View
    {
        $configuracion = Configuracion::first();
        $iglesia = Iglesia::first();

        return view('contenido.paginas.ofrendas.donaciones-online', [
            'configuracion' => $configuracion,
            'iglesia' => $iglesia,
        ]);
    }

    /**
     * Procesa el callback (Asentamiento de Pago) enviado por ZonaPagos cuando id_pago inicia con O-.
     */
    public function procesarCallbackOfrenda(int $ofrendaId): Response
    {
        $ofrenda = Ofrenda::with(['estadoPago', 'tipoOfrenda', 'destinatarioRelacion', 'moneda', 'usuario', 'tipoPago'])->find($ofrendaId);

        if (! $ofrenda) {
            Log::warning("OfrendaDonacionController::procesarCallbackOfrenda — No existe Ofrenda ID {$ofrendaId}.");

            return response('OK', 200);
        }

        $this->verificarYActualizarOfrenda($ofrenda);

        return response('OK', 200);
    }

    /**
     * Procesa el retorno del usuario desde ZonaPagos cuando id_pago inicia con O-.
     */
    public function retornoUsuario(int $ofrendaId): RedirectResponse
    {
        $ofrenda = Ofrenda::with(['estadoPago', 'tipoOfrenda', 'destinatarioRelacion', 'moneda', 'usuario', 'tipoPago'])->find($ofrendaId);

        if (! $ofrenda) {
            return redirect()->route('ofrendas.donaciones.formulario')
                ->with('error', 'No se encontró la referencia de la donación.');
        }

        if (! $ofrenda->estadoPago || $ofrenda->estadoPago->estado_pendiente) {
            $this->verificarYActualizarOfrenda($ofrenda);
        }

        return redirect()->route('ofrendas.donacion.finalizada', ['ofrenda' => $ofrenda->id]);
    }

    /**
     * Muestra la pantalla exclusiva de finalización de la Donación / Ofrenda.
     */
    public function donacionFinalizada(Ofrenda $ofrenda): View
    {
        $ofrenda->load(['estadoPago', 'tipoOfrenda', 'destinatarioRelacion', 'moneda', 'usuario', 'tipoPago']);

        if (! $ofrenda->estadoPago || $ofrenda->estadoPago->estado_pendiente) {
            $this->verificarYActualizarOfrenda($ofrenda);
            $ofrenda->refresh()->load(['estadoPago', 'tipoOfrenda', 'destinatarioRelacion', 'moneda', 'usuario', 'tipoPago']);
        }

        $configuracion = Configuracion::first();
        $iglesia = Iglesia::first();

        return view('contenido.paginas.ofrendas.donacion-finalizada', [
            'ofrenda' => $ofrenda,
            'configuracion' => $configuracion,
            'iglesia' => $iglesia,
        ]);
    }

    /**
     * Consulta en vivo el estado de una Ofrenda en ZonaPagos, actualiza la base de datos
     * (sin eliminar nunca la ofrenda) y envía el correo de agradecimiento una única vez
     * cuando la donación es confirmada.
     *
     * Cuando `$evaluarAbandono = true` (invocado por la Sonda), las ofrendas que superan la
     * ventana de espera (45 min, excluyendo `4001` Pendiente CR) sin concretarse pasan a estado
     * anulado/abandonado con `valor_real = 0` para diferenciarlas claramente en informes.
     */
    public function verificarYActualizarOfrenda(
        Ofrenda $ofrenda,
        bool $evaluarAbandono = false,
        ?int $minutosAbandonoPasarela = null
    ): bool {
        $ofrenda->loadMissing(['estadoPago', 'tipoPago']);

        // 1. Protección: nunca degradar una ofrenda que ya quedó efectivamente aprobada
        if ($ofrenda->estadoPago?->estado_final_inscripcion) {
            return false;
        }

        // 2. Consultar la API de verificación de ZonaPagos para la ofrenda (O-{id})
        $verificacion = $this->zonaPagosOfrendaService->verificarPago($ofrenda);

        if ($verificacion['error_red'] ?? false) {
            return false;
        }

        $minutosLimite = $minutosAbandonoPasarela ?? (int) config('services.zonapagos.minutos_abandono_pasarela', 45);
        $estaVencida = $ofrenda->created_at !== null && $ofrenda->created_at->lte(now()->subMinutes($minutosLimite));

        // 3. Caso A: ZonaPagos respondió OK pero la transacción no existe allí (donante cerró sin pagar)
        if (($verificacion['no_encontrado'] ?? false) || empty($verificacion['data']['str_res_pago'])) {
            if ($evaluarAbandono && $estaVencida) {
                return $this->marcarOfrendaComoAnuladaPorAbandono($ofrenda, [
                    'verificacion_no_encontrado' => $verificacion['response'] ?? ['detalle' => $verificacion['message'] ?? 'Sin registro'],
                    'motivo_abandono' => "Sin registro en ZonaPagos tras {$minutosLimite} minutos.",
                ]);
            }

            return false;
        }

        // 4. Parsear la trama de respuesta de ZonaPagos
        $datosVerificados = $this->zonaPagosOfrendaService->parsearRespuestaVerificacion((string) $verificacion['data']['str_res_pago']);

        if (empty($datosVerificados)) {
            return false;
        }

        $codigoEstadoZonaPagos = $datosVerificados['int_estado_pago'] ?? null;

        // 5. Buscar el EstadoPago priorizando el tipo_pago_id de la ofrenda
        $nuevoEstado = EstadoPago::query()
            ->when($ofrenda->tipo_pago_id, fn ($query) => $query->where('tipo_pago_id', $ofrenda->tipo_pago_id))
            ->where('id_codigo_externo', $codigoEstadoZonaPagos)
            ->first()
            ?: EstadoPago::where('id_codigo_externo', $codigoEstadoZonaPagos)->first();

        if (! $nuevoEstado && $codigoEstadoZonaPagos === ZonaPagosOfrendaService::CODIGO_ESTADO_APROBADO) {
            $nuevoEstado = EstadoPago::query()
                ->when($ofrenda->tipo_pago_id, fn ($query) => $query->where('tipo_pago_id', $ofrenda->tipo_pago_id))
                ->where('estado_final_inscripcion', true)
                ->first()
                ?: EstadoPago::where('estado_final_inscripcion', true)->first();
        }

        if (! $nuevoEstado) {
            Log::warning("OfrendaDonacionController — Código de estado '{$codigoEstadoZonaPagos}' no mapeado en estados_pago para Ofrenda ID {$ofrenda->id}.");

            return false;
        }

        // 6. Si continúa pendiente en pasarela (888, 999, 200, 4001)
        if ($nuevoEstado->estado_pendiente) {
            // 4001 (Pendiente por CR en tarjeta) tarda ~24h: NO vencer a los 45 min
            if ($evaluarAbandono && $estaVencida && $codigoEstadoZonaPagos !== ZonaPagosOfrendaService::CODIGO_ESTADO_PENDIENTE_CR) {
                return $this->marcarOfrendaComoAnuladaPorAbandono($ofrenda, [
                    'verificacion' => $datosVerificados,
                    'motivo_abandono' => "Ofrenda en estado {$codigoEstadoZonaPagos} tras {$minutosLimite} minutos sin concretar.",
                ]);
            }

            $cambioPendiente = (int) $ofrenda->estado_pago_id !== (int) $nuevoEstado->id;
            $ofrenda->estado_pago_id = $nuevoEstado->id;
            $ofrenda->gateway_response = array_merge($ofrenda->gateway_response ?? [], [
                'verificacion' => $datosVerificados,
            ]);
            $ofrenda->save();

            return $cambioPendiente;
        }

        // 7. Estado definitivo (aprobado '1' o rechazado '1000', '1001', '4000', '4003'): NUNCA se elimina la ofrenda
        $estadoAnteriorId = (int) $ofrenda->estado_pago_id;
        $ofrenda->estado_pago_id = $nuevoEstado->id;
        $ofrenda->int_id_forma_pago = ! empty($datosVerificados['int_id_forma_pago'])
            ? (int) $datosVerificados['int_id_forma_pago']
            : $ofrenda->int_id_forma_pago;
        $ofrenda->referencia_donacion = ! empty($datosVerificados['str_codigo_transaccion'])
            ? $datosVerificados['str_codigo_transaccion']
            : (! empty($datosVerificados['str_ticketID'])
                ? $datosVerificados['str_ticketID']
                : ($datosVerificados['int_n_pago'] ?: $ofrenda->referencia_donacion));
        $ofrenda->gateway_response = array_merge($ofrenda->gateway_response ?? [], [
            'verificacion' => $datosVerificados,
        ]);

        if ($nuevoEstado->estado_final_inscripcion) {
            $valorPagado = isset($datosVerificados['dbl_valor_pagado']) && is_numeric($datosVerificados['dbl_valor_pagado'])
                ? (float) $datosVerificados['dbl_valor_pagado']
                : 0.0;
            $ofrenda->valor_real = $valorPagado > 0 ? $valorPagado : (float) $ofrenda->valor;
        } else {
            // Rechazada / anulada: se conserva el registro en ofrendas con valor_real = 0 para informes
            $ofrenda->valor_real = 0;
        }

        $ofrenda->save();

        // 8. Si la ofrenda quedó confirmada/aprobada y aún no se ha enviado el correo, enviarlo una sola vez
        if ($nuevoEstado->estado_final_inscripcion && ! $ofrenda->correo_confirmacion_enviado) {
            $this->enviarCorreoConfirmacion($ofrenda);
        }

        return $estadoAnteriorId !== (int) $nuevoEstado->id;
    }

    /**
     * Marca una ofrenda no concretada como anulada/abandonada (`estado_anulado_inscripcion = true`,
     * `valor_real = 0`) sin eliminar el registro para mantener trazabilidad completa en informes.
     *
     * @param  array<string, mixed>  $evidencia
     */
    protected function marcarOfrendaComoAnuladaPorAbandono(Ofrenda $ofrenda, array $evidencia): bool
    {
        $estadoAnulado = $this->resolverEstadoAnuladoOfrenda($ofrenda->tipo_pago_id);

        if (! $estadoAnulado) {
            Log::error("OfrendaDonacionController — No existe estado anulado/abandonado configurado para Ofrenda ID {$ofrenda->id}.");

            return false;
        }

        $estadoAnteriorId = (int) $ofrenda->estado_pago_id;
        $ofrenda->estado_pago_id = $estadoAnulado->id;
        $ofrenda->valor_real = 0;
        $ofrenda->gateway_response = array_merge($ofrenda->gateway_response ?? [], [
            'abandono' => $evidencia,
            'fecha_abandono' => now()->toIso8601String(),
        ]);
        $ofrenda->save();

        Log::info("OfrendaDonacionController — Ofrenda ID {$ofrenda->id} marcada como '{$estadoAnulado->nombre}' (sin eliminar).");

        return $estadoAnteriorId !== (int) $estadoAnulado->id;
    }

    /**
     * Resuelve el estado anulado/abandonado (priorizando ID 16 "Transacción Abandonada" del método ZonaPagos).
     */
    protected function resolverEstadoAnuladoOfrenda(?int $tipoPagoId): ?EstadoPago
    {
        return EstadoPago::query()
            ->when($tipoPagoId, fn ($query) => $query->where('tipo_pago_id', $tipoPagoId))
            ->where('estado_anulado_inscripcion', true)
            ->where('estado_pendiente', false)
            ->where('estado_final_inscripcion', false)
            ->whereNull('id_codigo_externo')
            ->first()
            ?: EstadoPago::query()
                ->when($tipoPagoId, fn ($query) => $query->where('tipo_pago_id', $tipoPagoId))
                ->where('estado_anulado_inscripcion', true)
                ->where('estado_pendiente', false)
                ->where('estado_final_inscripcion', false)
                ->first()
            ?: EstadoPago::query()
                ->where('estado_anulado_inscripcion', true)
                ->where('estado_pendiente', false)
                ->where('estado_final_inscripcion', false)
                ->first();
    }

    /**
     * Envía el correo de agradecimiento utilizando la plantilla institucional DefaultMail (emails.default-mail).
     */
    protected function enviarCorreoConfirmacion(Ofrenda $ofrenda): void
    {
        $emailDonante = $ofrenda->email_donante ?: $ofrenda->usuario?->email;

        if (empty($emailDonante) || ! filter_var($emailDonante, FILTER_VALIDATE_EMAIL)) {
            Log::info("OfrendaDonacionController::enviarCorreoConfirmacion — Ofrenda ID {$ofrenda->id} no tiene un correo válido.");

            return;
        }

        $configuracion = Configuracion::first();
        $iglesia = Iglesia::first();

        $nombreDonante = $ofrenda->nombre_donante
            ?: ($ofrenda->usuario ? $ofrenda->usuario->nombre(3) : 'Donante');

        $nombreTipoOfrenda = e($ofrenda->tipoOfrenda?->nombre ?? 'Ofrenda');
        $nombreDestinatario = e($ofrenda->destinatarioRelacion?->nombre ?? 'General');
        $nombreCortoMoneda = e($ofrenda->moneda?->nombre_corto ?? 'COP');
        $simboloMoneda = e($ofrenda->moneda?->simbolo ?? '$');
        $valorFormateado = $simboloMoneda.' '.number_format((float) $ofrenda->valor, 0, ',', '.').' '.$nombreCortoMoneda;
        $referenciaTexto = 'O-'.$ofrenda->id.($ofrenda->referencia_donacion ? ' (Ref: '.e($ofrenda->referencia_donacion).')' : '');
        $fechaTexto = e((string) $ofrenda->fecha);

        $mailData = new \stdClass;
        $mailData->subject = '¡Gracias por tu donación! - '.($iglesia?->nombre ?? config('app.name'));
        $mailData->eyebrow = 'CONFIRMACIÓN DE DONACIÓN · OFRENDAS';
        $mailData->saludo = 'si';
        $mailData->nombre = $nombreDonante;

        if ($configuracion && ! empty($configuracion->banner_donacion_ofrenda)) {
            $mailData->banner = $configuracion->banner_donacion_ofrenda_url;
        }

        $mailData->mensaje = ! empty($configuracion?->mensaje_donacion_ofrenda)
            ? $configuracion->mensaje_donacion_ofrenda
            : '<p>Hemos recibido exitosamente tu donación. Damos gracias a Dios por tu vida y tu generosidad para continuar extendiendo Su Reino.</p>';

        $mailData->htmlAdicional = '
            <p style="margin:0 0 12px; font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:0.8px; color:#18181A;">
                Resumen de tu donación
            </p>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:14px; color:#3D3D40;">
                <tr>
                    <td style="padding:6px 0; font-weight:600; color:#6B6B70;">Referencia:</td>
                    <td style="padding:6px 0; text-align:right; font-weight:700; color:#18181A;">'.$referenciaTexto.'</td>
                </tr>
                <tr>
                    <td style="padding:6px 0; font-weight:600; color:#6B6B70;">Fecha:</td>
                    <td style="padding:6px 0; text-align:right;">'.$fechaTexto.'</td>
                </tr>
                <tr>
                    <td style="padding:6px 0; font-weight:600; color:#6B6B70;">Concepto:</td>
                    <td style="padding:6px 0; text-align:right;">'.$nombreTipoOfrenda.'</td>
                </tr>
                <tr>
                    <td style="padding:6px 0; font-weight:600; color:#6B6B70;">Destinado a:</td>
                    <td style="padding:6px 0; text-align:right;">'.$nombreDestinatario.'</td>
                </tr>
                <tr>
                    <td style="padding:8px 0 0; font-weight:700; color:#18181A; border-top:1px solid #EAEAE6;">Valor donado:</td>
                    <td style="padding:8px 0 0; text-align:right; font-weight:700; font-size:16px; color:#18181A; border-top:1px solid #EAEAE6;">'.$valorFormateado.'</td>
                </tr>
            </table>
        ';

        try {
            Mail::to($emailDonante)->send(new DefaultMail($mailData));
            $ofrenda->correo_confirmacion_enviado = true;
            $ofrenda->save();

            Log::info("OfrendaDonacionController::enviarCorreoConfirmacion — Correo enviado exitosamente a {$emailDonante} para Ofrenda ID {$ofrenda->id}.");
        } catch (\Exception $e) {
            Log::error("OfrendaDonacionController::enviarCorreoConfirmacion — Error al enviar correo para Ofrenda ID {$ofrenda->id}: ".$e->getMessage());
        }
    }
}
