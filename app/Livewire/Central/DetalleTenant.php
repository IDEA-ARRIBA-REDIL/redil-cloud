<?php

namespace App\Livewire\Central;

use App\Jobs\ConfigurarNuevoTenantJob;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\AccesoInicialTenantService;
use App\Services\EstadoTenantService;
use App\Services\SeguridadAdminService;

class DetalleTenant extends ComponenteAdminCentral
{
    public Tenant $tenant;

    public $status;

    public $plan_id;

    public $license_ends_at;

    public function mount(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->status = $tenant->status;
        $this->plan_id = $tenant->plan_id;
        $this->license_ends_at = $tenant->license_ends_at?->format('Y-m-d') ?? '';
    }

    public function updateStatus(): void
    {
        $this->validate([
            'status' => 'required|in:pending_review,active,suspended,expired,cancelled',
            'plan_id' => 'required|integer',
            'license_ends_at' => 'required|date_format:Y-m-d',
        ]);
        app(EstadoTenantService::class)->actualizar($this->tenant, $this->status, (int) $this->plan_id, $this->license_ends_at);
        session()->flash('success', 'Estado actualizado. Para entregar cuentas utiliza Enviar accesos iniciales.');
    }

    public function reintentarConfiguracion(): void
    {
        app(SeguridadAdminService::class)->exigir();
        $this->tenant->refresh();
        abort_unless(! $this->tenant->provisioned_at && $this->tenant->status === 'setup_failed' && ! $this->tenant->is_suspended, 422);
        app(SeguridadAdminService::class)->limitar('retry-tenant:'.$this->tenant->id, 3, 3600);
        $this->tenant->update(['status' => 'pending_review']);
        ConfigurarNuevoTenantJob::dispatch($this->tenant->id);
        session()->flash('success', 'Reintento enviado a la cola de aprovisionamiento.');
    }

    public function enviarAccesos(): void
    {
        $this->tenant->refresh();
        $cantidad = app(AccesoInicialTenantService::class)->enviar($this->tenant);
        session()->flash('success', $cantidad.' invitaciones enviadas. Las cuentas con correo de ejemplo o ya verificadas no reciben invitación.');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.central.detalle-tenant', ['planes' => Plan::query()->where('activo', true)->get()])
            ->layout('layouts.centralApp');
    }
}
