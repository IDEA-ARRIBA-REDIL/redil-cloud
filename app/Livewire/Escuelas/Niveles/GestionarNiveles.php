<?php

namespace App\Livewire\Escuelas\Niveles;

use App\Models\Escuela;
use App\Models\NivelAgrupacion;
use Livewire\Component;

class GestionarNiveles extends Component
{
    public $escuela;

    public function mount(Escuela $escuela)
    {
        $this->escuela = $escuela;
    }

    public function eliminar($id)
    {
        // Lógica de eliminación, preferiblemente usar SweetAlert en el frontend y evento aquí
        $nivel = NivelAgrupacion::find($id);
        if ($nivel) {
            $nivel->delete();
            // Emitir evento toast de éxito
            $this->dispatch('msn', ['icon' => 'success', 'title' => 'Nivel eliminado correctamente']);
        }
    }

    public function render()
    {
        $niveles = $this->escuela->nivelesAgrupacion()->orderBy('orden')->get();
        $configuracion = \App\Models\Configuracion::first();

        return view('livewire.escuelas.niveles.gestionar-niveles', [
            'niveles' => $niveles,
            'configuracion' => $configuracion
        ]);
    }
}
