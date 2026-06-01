<?php

namespace App\Livewire\Central;

use App\Jobs\ConfigurarNuevoTenantJob;
use App\Mail\CuentaPendienteAprobacionMail;
use App\Mail\NuevoTenantAdminMail;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\UserAdminRedil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class RegistroIglesia extends Component
{
    public $church_name;

    public $plan = 'basico';

    public $domain;

    public $pastor_name;

    public $city;

    public $country;

    public $estimated_members;

    public $whatsapp;

    public $admin_email;

    public $admin_password;

    public $full_domain_preview = '';

    protected function rules()
    {
        return [
            'church_name' => 'required|string|max:255',
            'plan' => 'required|exists:plans,slug',
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
        $planModel = Plan::where('slug', $this->plan)->first();

        if ($planModel && ! $planModel->incluye_marca_blanca) {
            $this->full_domain_preview = Str::slug($this->domain).'.'.$central;
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

        $tenantId = Str::slug($this->church_name.'-'.rand(1000, 9999));
        $planModel = Plan::where('slug', $this->plan)->first();

        $tenant = Tenant::create([
            'id' => $tenantId,
            'church_name' => $this->church_name,
            'plan_id' => $planModel?->id,
            'pastor_name' => $this->pastor_name,
            'city' => $this->city,
            'country' => $this->country,
            'estimated_members' => $this->estimated_members,
            'whatsapp' => $this->whatsapp,
            'admin_email' => $this->admin_email,
            'status' => 'pending_review',
            'is_suspended' => false,
        ]);

        $tenant->domains()->create(['domain' => $finalDomain]);

        // Despachar a la cola para crear la BD y sembrarla sin bloquear la vista
        ConfigurarNuevoTenantJob::dispatch($tenant, $this->admin_email, $this->admin_password);

        // Notificación al cliente de que su cuenta está pendiente
        Mail::to($this->admin_email)->queue(new CuentaPendienteAprobacionMail($tenant));

        // Registro de alerta en el panel
        DB::table('admin_notifications')->insert([
            'tenant_id' => $tenant->id,
            'tipo' => 'nuevo_registro',
            'mensaje' => "Nueva iglesia registrada: {$tenant->church_name} ({$tenant->estimated_members} miembros).",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notificación a todos los super admins activos
        $admins = UserAdminRedil::where('is_suspended', false)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new NuevoTenantAdminMail($tenant));
        }

        session()->flash('success', '¡Registro Exitoso! Tu solicitud ha sido recibida y se encuentra en estado Pendiente de Aprobación. Nos pondremos en contacto contigo pronto.');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.central.registro-iglesia')
            ->layout('layouts.centralApp');
    }
}
