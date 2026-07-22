<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Mail\DefaultMail;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class NotificacionReactivacionCuenta extends Notification
{
    use Queueable;

    /**
     * URL temporal firmada que el usuario clicara para reactivarse.
     *
     * @var string
     */
    protected $urlFirmada;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @param string $urlFirmada
     * @return void
     */
    public function __construct(string $urlFirmada)
    {
        $this->urlFirmada = $urlFirmada;
    }

    /**
     * Define por qué canales se va a entregar la notificación (sólo correo electrónico).
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Construye y envía el correo electrónico, en idioma español con las instrucciones.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Mail\Mailable
     */
    public function toMail($notifiable): Mailable
    {
        $mailData = new \stdClass();
        $mailData->subject = Lang::get('Reactivación de tu cuenta');
        $mailData->eyebrow = 'SEGURIDAD · RESTAURACIÓN DE CUENTA';
        $mailData->titulo = Lang::get('Reactivación de tu cuenta');
        $mailData->nombre = method_exists($notifiable, 'nombre') ? $notifiable->nombre(3) : ($notifiable->name ?? '');

        $mailData->mensaje = Lang::get('Has recibido este correo electrónico porque tu cuenta se encuentra dada de baja y se ha solicitado su reactivación.<br><br>Si tú realizaste esta solicitud, por favor haz clic en el botón de abajo para restaurar tu cuenta e ingresar nuevamente:')
            . '<p style="font-size:13px;color:#6B7280;line-height:1.5;margin-top:24px;margin-bottom:0;">'
            . Lang::get('Por motivos de seguridad, este enlace de reactivación expirará en 30 minutos.<br><br>Si tú no solicitaste reactivar tu cuenta, puedes ignorar este mensaje de forma segura. Tu cuenta permanecerá inactiva.')
            . '</p>';

        $mailData->actionUrl = $this->urlFirmada;
        $mailData->actionText = Lang::get('Reactivar mi cuenta →');

        return (new DefaultMail($mailData))
            ->to($notifiable->email);
    }
}
