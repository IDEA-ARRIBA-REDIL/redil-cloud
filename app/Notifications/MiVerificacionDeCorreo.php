<?php

namespace App\Notifications;

use App\Mail\DefaultMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class MiVerificacionDeCorreo extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): Mailable
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $mailData = new \stdClass;
        $mailData->subject = Lang::get('Verifica tu correo');
        $mailData->eyebrow = 'BIENVENIDA · ACTIVACIÓN DE CUENTA';
        $mailData->titulo = Lang::get('¡Hola!');
        $mailData->nombre = method_exists($notifiable, 'nombre') ? $notifiable->nombre(3) : ($notifiable->name ?? '');

        $mailData->mensaje = Lang::get('Gracias por registrarte. Por favor, haz clic en el botón de abajo para verificar tu cuenta y comenzar a utilizar la plataforma.')
            .'<p style="font-size:13px;color:#6B7280;line-height:1.5;margin-top:24px;margin-bottom:0;">'
            .Lang::get('Si no creaste esta cuenta, puedes ignorar este mensaje de forma segura.')
            .'</p>';

        $mailData->actionUrl = $verificationUrl;
        $mailData->actionText = Lang::get('Verificar correo electrónico →');

        return (new DefaultMail($mailData))
            ->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    protected function verificationUrl(object $notifiable): string
    {
        // Este método no cambia
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
