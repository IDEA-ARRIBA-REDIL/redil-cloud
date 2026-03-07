<?php

namespace App\Livewire\Informes;

use Livewire\Component;
use App\Models\Informe;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\On; // ¡Importante para escuchar eventos!


class ModalGestionarRolesInforme extends Component
{
    public $showModal = false;
    public $informeId;
    public $nombreInforme;
    public $todosLosRoles = [];
    public $rolesSeleccionados = [];

    public function mount()
    {
       // Cargamos todos los roles disponibles una sola vez.
       $this->todosLosRoles = Role::all();
    }

    #[On('abrirModalRoles')]
    public function abrirModal($informeId)
    {
        $this->informeId = $informeId;
        $informe = Informe::find($this->informeId);

        if ($informe) {
            $this->nombreInforme = $informe->nombre;
            $this->rolesSeleccionados = $informe->roles->pluck('id')->toArray();

            $this->showModal = true;
        }
    }

    public function cerrarModal()
    {
        $this->reset(['showModal', 'informeId', 'nombreInforme', 'rolesSeleccionados']);
    }

    public function guardarRoles()
    {
        $informe = Informe::find($this->informeId);
        if ($informe) {
            // sync() es el método perfecto. Adjunta los nuevos, quita los que no están
            // y deja los que ya estaban. ¡Todo en una sola línea!
            $informe->roles()->sync($this->rolesSeleccionados);

            $this->cerrarModal();

            // Opcional: Despacha un evento para notificar al usuario (ej. con un toast)
            $this->dispatch('roles-actualizados', '¡Roles actualizados correctamente!');

            $this->dispatch(
                'msn',
                msnIcono: 'success',
                msnTitulo: '¡Muy bien!',
                msnTexto: '¡Roles actualizados correctamente!'
            );
        }
    }

    public function render()
    {
        return view('livewire.informes.modal-gestionar-roles-informe');
    }
}
