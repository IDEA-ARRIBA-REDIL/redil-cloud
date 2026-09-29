<?php

namespace App\Livewire\Central;

use App\Services\RegistroTenantService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class RegistroIglesia extends Component
{
    public string $codigo = '';

    public string $church_name = '';

    public string $domain = '';

    public string $pastor_name = '';

    public string $pastor_phone = '';

    public string $admin_contact_name = '';

    public string $admin_contact_phone = '';

    public string $city = '';

    public string $country = '';

    public $estimated_members = 1;

    public string $whatsapp = '';

    public string $admin_email = '';

    public string $full_domain_preview = '';

    protected function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'size:64'],
            'church_name' => ['required', 'string', 'max:100'],
            'domain' => ['required', 'string', 'max:63'],
            'pastor_name' => ['required', 'string', 'max:100'],
            'pastor_phone' => ['required', 'string', 'max:25'],
            'admin_contact_name' => ['required', 'string', 'max:100'],
            'admin_contact_phone' => ['required', 'string', 'max:25'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'estimated_members' => ['required', 'integer', 'between:1,1000000'],
            'whatsapp' => ['required', 'regex:/^\\+?[0-9 ()-]{7,25}$/'],
            'admin_email' => ['required', 'email', 'max:254', 'not_regex:/@example\\.invalid$/i'],
        ];
    }

    public function updatedDomain(): void
    {
        $this->full_domain_preview = '';
        if ($this->domain !== '') {
            $this->full_domain_preview = app(RegistroTenantService::class)->dominio($this->domain);
        }
    }

    public function register(): void
    {
        $key = 'central:registro:'.hash('sha256', (string) request()->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['codigo' => 'Demasiados intentos. Intenta más tarde.']);
        }
        RateLimiter::hit($key, 3600);
        $datos = $this->validate();
        app(RegistroTenantService::class)->registrar($datos);
        $this->reset();
        session()->flash('success', 'Solicitud recibida. REDIL preparará y revisará el entorno antes de habilitar el acceso.');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.central.registro-iglesia')->layout('layouts.centralApp');
    }
}
