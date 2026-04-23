<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;


class NotificacionGeneral extends Notification
{
    use Queueable;

    /**
     * @param  array{titulo: string, mensaje: string, icono: string, url: string|null, color: string}  $datos
     */
    public function __construct(
        public array $datos
    ) {}

    /**
     * Canales de entrega de la notificación.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }


    /**
     * Datos que se almacenan en la tabla notifications (columna data).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => $this->datos['titulo'] ?? 'Notificación',
            'mensaje' => $this->datos['mensaje'] ?? '',
            'icono' => $this->datos['icono'] ?? 'ti-bell',
            'color' => $this->datos['color'] ?? 'primary',
            'url' => $this->datos['url'] ?? null,
        ];
    }

    /**
     * Formato para notificaciones Push (navegador/sistema).
     */
    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->datos['titulo'] ?? 'REDIL CLOUD')
            ->icon('/assets/img/favicon/logo_crecer.ico')
            ->body($this->datos['mensaje'] ?? '')
            ->action('Ver ahora', 'view_action')
            ->data(['url' => $this->datos['url'] ?? '/dashboard']);
    }
}
