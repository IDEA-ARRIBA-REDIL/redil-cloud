<?php

namespace App\Livewire\Consolidacion;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class OffcanvasUltimaPeticion extends Component
{
    public ?int $personaId = null;

    #[On('cargarUltimaPeticion')]
    public function cargarUltimaPeticion(int|string $personaId): void
    {
        $this->personaId = (int) $personaId;
    }

    public function cerrar(): void
    {
        $this->reset(['personaId']);
    }

    public function render(): View
    {
        $persona = null;
        $peticion = null;

        if ($this->personaId) {
            $persona = User::withTrashed()
                ->with(['sede'])
                ->find($this->personaId);

            if ($persona) {
                $peticion = $persona->peticiones()
                    ->with([
                        'tipoPeticion',
                        'asignado',
                        'autorCreacion',
                        'formularioUsuario',
                        'seguimientos.usuarioCreacion',
                    ])
                    ->latest('id')
                    ->first();
            }
        }

        return view('livewire.consolidacion.offcanvas-ultima-peticion', [
            'persona' => $persona,
            'peticion' => $peticion,
        ]);
    }
}
