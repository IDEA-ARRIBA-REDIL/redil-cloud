<?php

namespace App\Livewire\Notificaciones;

use Livewire\Component;
use Livewire\WithPagination;

class ListaNotificaciones extends Component
{
    use WithPagination;

    public string $filtro = 'todas';

    /**
     * Cambia el filtro activo y reinicia la paginación.
     */
    public function cambiarFiltro(string $filtro): void
    {
        $this->filtro = $filtro;
        $this->resetPage();
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
            if ($url) {
                $this->redirect($url);
            }
        }
    }

    /**
     * Marca todas como leídas.
     */
    public function marcarTodasComoLeidas(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    /**
     * Elimina una notificación individual.
     */
    public function eliminar(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->delete();
    }

    /**
     * Elimina todas las notificaciones leídas.
     */
    public function eliminarLeidas(): void
    {
        auth()->user()->readNotifications()->delete();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $query = auth()->user()->notifications()->latest()
            ->where(function ($q) {
                // Excluir notificaciones expiradas — cast a jsonb requerido en PostgreSQL
                // porque la columna data es text, no jsonb nativo
                $q->whereRaw("(data::jsonb)->>'expira_en' IS NULL")
                    ->orWhereRaw("((data::jsonb)->>'expira_en')::timestamptz > NOW()");
            });

        if ($this->filtro === 'no-leidas') {
            $query->whereNull('read_at');
        } elseif ($this->filtro === 'leidas') {
            $query->whereNotNull('read_at');
        }

        $notificaciones = $query->paginate(15);
        $conteoNoLeidas = auth()->user()->unreadNotifications()->count();

        return view('livewire.notificaciones.lista-notificaciones', [
            'notificaciones' => $notificaciones,
            'conteoNoLeidas' => $conteoNoLeidas,
        ]);
    }
}
