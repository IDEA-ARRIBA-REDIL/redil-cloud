<?php

namespace App\Livewire\Central;

use Livewire\Component;
use App\Models\Tenant;
use Illuminate\Support\Str;
use App\Jobs\ConfigurarNuevoTenantJob;
use Illuminate\Support\Facades\Mail;
use App\Mail\CuentaCreadaMail;
use Illuminate\Validation\Rule;

class RegistroIglesia extends Component
{
    public $church_name, $plan = 'basico', $domain, $pastor_name, $city, $country;
    public $estimated_members, $whatsapp, $admin_email, $admin_password;

    public $full_domain_preview = '';

    protected function rules()
    {
        return [
            'church_name' => 'required|string|max:255',
            'plan' => 'required|in:basico,premium',
            'domain' => 'required|string',
            'pastor_name' => 'required|string|max:255',
            'city' => 'required|string',
            'country' => 'required|string',
            'estimated_members' => 'required|integer|min:1',
            'whatsapp' => 'required|string',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:6',
        ];
    }

    public function updatedDomain()
    {
        $this->updateDomainPreview();
    }

    public function updatedPlan()
    {
        $this->updateDomainPreview();
    }

    private function updateDomainPreview()
    {
        if (empty($this->domain)) {
            $this->full_domain_preview = '';
            return;
        }

        $central = env('CENTRAL_DOMAIN', 'redilcloud');
        if ($this->plan === 'basico') {
            $this->full_domain_preview = Str::slug($this->domain) . '.' . $central;
        } else {
            $this->full_domain_preview = $this->domain;
        }
    }

    public function register()
    {
        $this->validate();
        $this->updateDomainPreview();

        $finalDomain = $this->full_domain_preview;

        // Check uniqueness of domain manually or via validation
        $exists = \Illuminate\Support\Facades\DB::table('domains')->where('domain', $finalDomain)->exists();
        if ($exists) {
            $this->addError('domain', 'Este dominio o subdominio ya se encuentra registrado.');
            return;
        }

        $tenantId = Str::slug($this->church_name . '-' . rand(1000, 9999));

        $tenant = Tenant::create([
            'id' => $tenantId,
            'church_name' => $this->church_name,
            'plan' => $this->plan,
            'pastor_name' => $this->pastor_name,
            'city' => $this->city,
            'country' => $this->country,
            'estimated_members' => $this->estimated_members,
            'whatsapp' => $this->whatsapp,
            'admin_email' => $this->admin_email,
            'is_suspended' => false,
        ]);

        $tenant->domains()->create(['domain' => $finalDomain]);

        // Despachar a la cola para crear la BD y sembrarla sin bloquear la vista
        ConfigurarNuevoTenantJob::dispatch($tenant, $this->admin_email, $this->admin_password);

        // Enviar correo (puede ir encolado también)
        // Mail::to($this->admin_email)->queue(new CuentaCreadaMail($tenant, $finalDomain));

        session()->flash('success', '¡Registro Exitoso! Tu cuenta está siendo configurada en este momento. Te notificaremos por correo cuando el sistema esté 100% listo.');
        
        $this->reset();
    }

    public function render()
    {
        return view('livewire.central.registro-iglesia')
            ->layout('layouts.blankLayout'); // Formularios públicos suelen usar blankLayout o layoutFront
    }
}
