<?php

namespace App\View\Components;

use App\Models\TareaConsolidacionUsuario;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class TareasConsolidacionPerfil extends Component
{
    /** @var Collection<int, TareaConsolidacionUsuario> */
    public Collection $asignaciones;

    public function __construct(User $usuario)
    {
        $this->asignaciones = $usuario->asignacionesConsolidacion()
            ->with(['tareaConsolidacion', 'estado'])
            ->get()
            ->sortBy([
                ['tareaConsolidacion.orden', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.tareas-consolidacion-perfil');
    }
}
