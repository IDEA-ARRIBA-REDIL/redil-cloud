<?php

namespace App\Livewire\Escuelas;

use App\Models\NivelEscuela;
use Livewire\Component;

class NivelesEscuela extends Component
{
    public $escuelaId;

    public $niveles = [];

    public $search = '';

    public $configuracion;

    public function mount($escuelaId)
    {
        $this->escuelaId = $escuelaId;
        $this->configuracion = \App\Models\Configuracion::find(1);
        $this->loadNiveles();
    }

    public function loadNiveles()
    {
        $query = NivelEscuela::where('escuela_id', $this->escuelaId);

        if ($this->search) {
            $query->where('nombre', 'like', '%'.$this->search.'%');
        }

        $this->niveles = $query->orderBy('id', 'asc')->get();
    }

    public function updatedSearch()
    {
        $this->loadNiveles();
    }

    public function eliminarNivel($id)
    {
        $nivel = NivelEscuela::find($id);
        if ($nivel) {
            $nivel->delete();
            $this->loadNiveles();
            $this->dispatch('msn', [
                'msn' => 'Grado eliminado correctamente',
                'icon' => 'success',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.escuelas.niveles-escuela');
    }
}
