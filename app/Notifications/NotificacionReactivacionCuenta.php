<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Reactivación de tu cuenta - REDIL Cloud')
                    ->greeting('Hola ' . $notifiable->name . '!')
                    ->line('Has recibido este correo electrónico porque tu cuenta está dada de baja y se ha solicitado su reactivación.')
                    ->line('Si tú hiciste la solicitud, haz clic en el botón a continuación para restaurar tu cuenta y poder ingresar de nuevo:')
                    ->action('Reactivar Mi Cuenta', $this->urlFirmada)
                    ->line('Por temas de seguridad, este enlace expirará en 30 minutos.')
                    ->line('Si no pediste reactivar tu cuenta, puedes ignorar de forma segura este mensaje, nadie más accederá sin conocer tu contraseña.')
                    ->salutation('Saludos desde el equipo de ' . config('app.name') . '.');
    }
}
