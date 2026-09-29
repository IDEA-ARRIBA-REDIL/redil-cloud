<?php

namespace App\Livewire\Central\Auth;

use App\Services\SeguridadAdminService;
use Livewire\Component;

class AdminLogin extends Component
{
    public string $email = '';

    public string $password = '';

    public string $codigo = '';

    public function login(): void
    {
        $this->validate(['email' => 'required|email|max:254', 'password' => 'required|string|max:1024']);
        $password = $this->password;
        $this->reset('password', 'codigo');
        app(SeguridadAdminService::class)->iniciar($this->email, $password);
    }

    public function verificar(): mixed
    {
        $this->validate(['codigo' => 'required|digits:6']);
        app(SeguridadAdminService::class)->verificar($this->codigo);
        $this->reset('codigo');

        return redirect('/admin/dashboard');
    }

    public function cancelar(): void
    {
        app(SeguridadAdminService::class)->cancelar();
        $this->reset('codigo', 'password');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.central.auth.admin-login')->layout('layouts.centralApp');
    }
}
