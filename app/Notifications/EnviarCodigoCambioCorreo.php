<?php

namespace App\Notifications;

use App\Models\Iglesia;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class EnviarCodigoCambioCorreo extends Notification
{
    use Queueable;

    public $iglesia;
    public $codigo;

    /**
     * Crear una nueva instancia de notificación.
     */
    public function __construct(string $codigo)
    {
        // 1. Obtener los datos de la iglesia para la personalización del correo.
        $this->iglesia = Iglesia::find(1);
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Código de verificación para cambio de correo'))
            ->greeting(Lang::get('¡Hola!'))
            ->line(Lang::get('Has solicitado cambiar tu dirección de correo electrónico en nuestro sistema.'))
            ->line(Lang::get('Tu código de verificación de 6 dígitos es:'))
            ->line('**' . $this->codigo . '**')
            ->line(Lang::get('Copia y pega este código en la ventana de cambio de correo para confirmar el procedimiento.'))
            ->line(Lang::get('Si tú no solicitaste este cambio, puedes ignorar este mensaje.'))
            ->salutation(Lang::get('Saludos, ') . ($this->iglesia->nombre ?? 'CRECER'));
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
