<?php

namespace App\Notifications;

use App\Models\Iglesia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Lang;

class MiVerificacionDeCorreo extends Notification
{
    use Queueable;

    public $iglesia;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->iglesia = Iglesia::find(1);
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
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
          ->subject(Lang::get('Verifica tu correo'))
          ->greeting(Lang::get('¡Hola!'))
          ->line(Lang::get('Gracias por registrarte. Por favor, haz clic en el botón de abajo para verificar tu cuenta.'))
          ->action(Lang::get('Verificar correo'), $verificationUrl)
          ->line(Lang::get('Si no creaste esta cuenta, puedes ignorar este mensaje.'))
          ->salutation(Lang::get('Saludos, ') . $this->iglesia->nombre); // <-- AÑADE ESTA LÍNEA
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
