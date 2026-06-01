<?php

namespace App\Livewire\Central;

use App\Models\UserAdminRedil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class GestionarSuperAdmins extends Component
{
    public $admins;

    public $name;

    public $email;

    public $password;

    public $is_suspended;

    public $admin_id;

    public $isModalOpen = 0;

    protected function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users_admins_redil,email,'.$this->admin_id,
        ];
    }

    public function render()
    {
        $this->admins = UserAdminRedil::all();

        return view('livewire.central.gestionar-super-admins')
            ->layout('layouts.centralApp');
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->admin_id = null;
    }

    public function store()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        } elseif (! $this->admin_id) {
            $this->addError('password', 'La contraseña es obligatoria para nuevos admins.');

            return;
        }

        UserAdminRedil::updateOrCreate(['id' => $this->admin_id], $data);

        session()->flash('message', $this->admin_id ? 'Administrador Actualizado.' : 'Administrador Creado exitosamente.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $admin = UserAdminRedil::findOrFail($id);
        $this->admin_id = $id;
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->password = '';

        $this->openModal();
    }

    public function toggleSuspension($id)
    {
        if ($id == Auth::guard('admin')->user()->id) {
            session()->flash('error', 'No puedes suspender tu propia cuenta activa.');

            return;
        }

        $admin = UserAdminRedil::findOrFail($id);
        $admin->is_suspended = ! $admin->is_suspended;
        $admin->save();

        session()->flash('message', 'Estado de cuenta actualizado.');
    }
}
