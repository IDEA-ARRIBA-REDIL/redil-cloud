<?php

namespace App\Livewire\InformesPersonalizados;

use App\Models\InformePersonalizado;
use App\Models\TipoUsuario;
use Livewire\Component;

class Index extends Component
{
    public $informes;

    public $tiposUsuarios;

    // Modal state
    public $informeSeleccionado = null;

    public $tiposUsuariosSeleccionados = [];

    public $showModal = false;

    public function mount()
    {
        $this->informes = InformePersonalizado::all();
        $this->tiposUsuarios = TipoUsuario::orderBy('nombre')->get();
    }

    public function openModal($informeId)
    {
        $this->informeSeleccionado = InformePersonalizado::with('tiposUsuarios')->find($informeId);
        $this->tiposUsuariosSeleccionados = $this->informeSeleccionado->tiposUsuarios->pluck('id')->toArray();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->informeSeleccionado = null;
        $this->tiposUsuariosSeleccionados = [];
    }

    public function guardarTiposUsuarios()
    {
        if ($this->informeSeleccionado) {
            $this->informeSeleccionado->tiposUsuarios()->sync($this->tiposUsuariosSeleccionados);
            $this->closeModal();
            $this->dispatch('swal:success', [
                'title' => 'Éxito',
                'text' => 'Roles asignados correctamente.',
            ]);
            // Refresh informes to show updated state if needed
            $this->informes = InformePersonalizado::all();
        }
    }

    public function toggleActivo($informeId)
    {
        $informe = InformePersonalizado::find($informeId);
        if ($informe) {
            $informe->activo = ! $informe->activo;
            $informe->save();

            $this->dispatch('swal:success', [
                'title' => 'Éxito',
                'text' => 'Estado del informe actualizado.',
            ]);

            $this->informes = InformePersonalizado::all();
        }
    }

    public function render()
    {
        return view('contenido.paginas.informes-personalizados.livewire.index');
    }
}
