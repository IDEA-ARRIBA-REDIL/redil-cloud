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
     * y envía el correo de agradecimiento una única vez cuando el pago es confirmado.
     */
    public function verificarYActualizarOfrenda(Ofrenda $ofrenda): void
    {
        // 1. Consultar la API de verificación de ZonaPagos para la ofrenda (O-{id})
        $verificacion = $this->zonaPagosOfrendaService->verificarPago($ofrenda);

        if (! $verificacion['success'] || empty($verificacion['data']['str_res_pago'])) {
            return;
        }

        // 2. Parsear la trama de respuesta de ZonaPagos
        $datosVerificados = $this->zonaPagosOfrendaService->parsearRespuestaVerificacion((string) $verificacion['data']['str_res_pago']);

        if (empty($datosVerificados)) {
            return;
        }

        $codigoEstadoZonaPagos = $datosVerificados['int_estado_pago'] ?? null;

        // 3. Buscar el EstadoPago en la base de datos del tenant según id_codigo_externo
        $nuevoEstado = EstadoPago::where('id_codigo_externo', $codigoEstadoZonaPagos)->first();

        if (! $nuevoEstado && $codigoEstadoZonaPagos === '1') {
            $nuevoEstado = EstadoPago::where('estado_final_inscripcion', true)->first();
        }

        if (! $nuevoEstado) {
            Log::warning("OfrendaDonacionController — Código de estado '{$codigoEstadoZonaPagos}' no mapeado en estados_pago para Ofrenda ID {$ofrenda->id}.");

            return;
        }

        // 4. Actualizar los datos de la pasarela en la tabla ofrendas
        $ofrenda->estado_pago_id = $nuevoEstado->id;
        $ofrenda->int_id_forma_pago = ! empty($datosVerificados['int_id_forma_pago'])
            ? (int) $datosVerificados['int_id_forma_pago']
            : $ofrenda->int_id_forma_pago;
        $ofrenda->referencia_donacion = ! empty($datosVerificados['str_ticketID'])
            ? $datosVerificados['str_ticketID']
            : ($datosVerificados['int_n_pago'] ?: $ofrenda->referencia_donacion);
        $ofrenda->gateway_response = array_merge($ofrenda->gateway_response ?? [], [
            'verificacion' => $datosVerificados,
        ]);
        $ofrenda->save();

        // 5. Si la ofrenda quedó confirmada/aprobada y aún no se ha enviado el correo, enviarlo
        if ($nuevoEstado->estado_final_inscripcion && ! $ofrenda->correo_confirmacion_enviado) {
            $this->enviarCorreoConfirmacion($ofrenda);
        }
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
