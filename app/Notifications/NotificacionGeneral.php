<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

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
        return ['database'];
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
}
