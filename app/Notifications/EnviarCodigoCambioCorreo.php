<?php

namespace App\Notifications;

use App\Models\Iglesia;
use Illuminate\Bus\Queueable;
use App\Mail\DefaultMail;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class EnviarCodigoCambioCorreo extends Notification
{
    use Queueable;

    public $codigo;

    /**
     * Crear una nueva instancia de notificación.
     */
    public function __construct(string $codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * Obtener los canales de entrega de la notificación.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Obtener la representación de correo de la notificación.
     */
    public function toMail(object $notifiable): Mailable
    {
        $mailData = new \stdClass();
        $mailData->subject = Lang::get('Código de verificación para cambio de correo');
        $mailData->eyebrow = 'SEGURIDAD · CAMBIO DE CORREO';
        $mailData->titulo = Lang::get('¡Hola!');
        $mailData->nombre = method_exists($notifiable, 'nombre') ? $notifiable->nombre(3) : ($notifiable->name ?? '');
        $mailData->mensaje = Lang::get('Has solicitado cambiar tu dirección de correo electrónico en nuestro sistema.<br>Tu código de verificación de 6 dígitos es:');
        
        // Maquetación del código de 6 dígitos y su texto instructivo en la sección de HTML adicional
        $mailData->htmlAdicional = '
        <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 12px auto 24px;">
          <tr>
            <td style="background-color:#FFFFFF;border:1px dashed #0099d9;border-radius:8px;padding:16px 40px;text-align:center;font-family:Arial,sans-serif;font-size:28px;font-weight:700;color:#0099d9;letter-spacing:6px;">
              ' . $this->codigo . '
            </td>
          </tr>
        </table>
        <p style="font-family:Arial,sans-serif;font-size:14px;color:#374151;line-height:1.6;text-align:center;margin:0 0 12px 0;">
          ' . Lang::get('Copia y pega este código en la ventana de cambio de correo para confirmar el procedimiento.') . '
        </p>
        <p style="font-family:Arial,sans-serif;font-size:12px;color:#6B7280;line-height:1.5;text-align:center;margin:0;">
          ' . Lang::get('Si tú no solicitaste este cambio, puedes ignorar este correo de forma segura.') . '
        </p>';

        return (new DefaultMail($mailData))
            ->to($notifiable->email);
    }

    /**
     * Obtener la representación en array de la notificación.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'codigo' => $this->codigo,
            'correo_nuevo' => $notifiable->email,
        ];
    }
}
