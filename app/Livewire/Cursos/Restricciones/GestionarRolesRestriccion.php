<?php

namespace App\Livewire\Cursos\Restricciones;

use Livewire\Component;
use App\Models\Curso;
use App\Models\Role;

class GestionarRolesRestriccion extends Component
{
    public Curso $curso;
    public $rolesList = [];
    public $roleSeleccionado = '';

    public function mount(Curso $curso)
    {
        $this->curso = $curso;
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->rolesList = Role::orderBy('name')->get();
    }

    public function agregarRol()
    {
        $this->validate([
            'roleSeleccionado' => 'required|exists:roles,id',
        ], [
            'roleSeleccionado.required' => 'Debes seleccionar un rol.',
            'roleSeleccionado.exists' => 'El rol seleccionado no existe.',
        ]);

        if ($this->curso->rolesRestringidos()->where('role_id', $this->roleSeleccionado)->exists()) {
             $this->dispatch('msn', [
                'msn' => 'El rol ya está agregado.',
                'icon' => 'warning'
            ]);
            return;
        }

        $this->curso->rolesRestringidos()->attach($this->roleSeleccionado);

        $this->roleSeleccionado = '';

        $this->dispatch('msn', [
            'msn' => 'Rol agregado correctamente.',
            'icon' => 'success'
        ]);
    }

    public function eliminarRol($rolId)
    {
        $this->curso->rolesRestringidos()->detach($rolId);

        $this->dispatch('msn', [
            'msn' => 'Rol eliminado correctamente.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.cursos.restricciones.gestionar-roles-restriccion', [
            'rolesRestingidos' => $this->curso->rolesRestringidos()->orderBy('name')->get()
        ]);
    }
}
