<?php

namespace App\Livewire\Central;

use App\Services\AccesoInicialTenantService;
use App\Services\SeguridadAdminService;
use Livewire\Component;

class ActivarCuentaTenant extends Component
{
    public string $codigo = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function activar(): void
    {
        app(SeguridadAdminService::class)->limitar('activar-cuenta:'.request()->ip(), 10, 3600);
        $this->validate([
            'codigo' => 'required|string|size:64',
            'password' => 'required|string|min:14|max:128|confirmed',
        ]);
        $password = $this->password;
        $this->reset('password', 'password_confirmation');
        app(AccesoInicialTenantService::class)->activar($this->codigo, $password);
        $this->reset('codigo');
        session()->flash('success', 'Acceso activado. Ingresa por el subdominio de tu iglesia.');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.central.activar-cuenta-tenant')->layout('layouts.centralApp');
    }
}
