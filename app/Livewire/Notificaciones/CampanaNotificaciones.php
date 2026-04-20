<?php

namespace App\Livewire\Notificaciones;

use Livewire\Component;

class CampanaNotificaciones extends Component
{
    public int $conteoNoLeidas = 0;

    public $notificaciones = [];

    public bool $mostrarDropdown = false;

    /**
     * Carga inicial: conteo + últimas notificaciones.
     */
    public function mount(): void
    {
        $this->actualizarConteo();
        $this->cargarNotificaciones();
    }

    /**
     * Actualiza el conteo de no leídas (llamado por wire:poll).
     */
    public function actualizarConteo(): void
    {
        $viejoConteo = $this->conteoNoLeidas;
        $this->conteoNoLeidas = auth()->user()->unreadNotifications()->count();

        // Siempre despachar el estado actual para la PWA al montar y actualizar
        $this->dispatch('AppBadgeUpdated', count: $this->conteoNoLeidas);
    }

    /**
     * Carga las últimas 10 notificaciones para el dropdown.
     */
    public function cargarNotificaciones(): void
    {
        $this->notificaciones = auth()->user()
            ->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notificacion) {
                return [
                    'id' => $notificacion->id,
                    'titulo' => $notificacion->data['titulo'] ?? 'Notificación',
                    'mensaje' => $notificacion->data['mensaje'] ?? '',
                    'icono' => $notificacion->data['icono'] ?? 'ti-bell',
                    'color' => $notificacion->data['color'] ?? 'primary',
                    'url' => $notificacion->data['url'] ?? null,
                    'leida' => $notificacion->read_at !== null,
                    'tiempo' => $notificacion->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    /**
     * Marca una notificación individual como leída.
     */
    public function marcarComoLeida(string $id): void
    {
        $notificacion = auth()->user()->notifications()->find($id);

        if ($notificacion) {
            $notificacion->markAsRead();

            // Si tiene URL, redirigir
            $url = $notificacion->data['url'] ?? null;

            $this->actualizarConteo();
            $this->cargarNotificaciones();

            if ($url) {
                $this->redirect($url);
            }
        }
    }

    /**
     * Marca todas las notificaciones como leídas.
     */
    public function marcarTodasComoLeidas(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->conteoNoLeidas = 0;
        $this->cargarNotificaciones();
    }

    /**
     * Se ejecuta cada vez que se abre el dropdown para refrescar.
     */
    public function abrirDropdown(): void
    {
        $this->cargarNotificaciones();
        $this->actualizarConteo();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.notificaciones.campana-notificaciones');
    }
}
