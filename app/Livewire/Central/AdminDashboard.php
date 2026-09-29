<?php

namespace App\Livewire\Central;

use App\Models\Tenant;

class AdminDashboard extends ComponenteAdminCentral
{
    public function toggleSuspension($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        if (! $tenant->license_ends_at || ! $tenant->plan_id) {
            $this->addError('status', 'Configura y revisa la licencia desde el detalle del tenant.');

            return;
        }
        app(\App\Services\EstadoTenantService::class)->actualizar(
            $tenant, $tenant->status === 'active' ? 'suspended' : 'active',
            $tenant->plan_id, $tenant->license_ends_at->toDateString()
        );

        session()->flash('message', 'Estado del inquilino actualizado.');
    }

    public function render()
    {
        $tenants = Tenant::with(['domains', 'plan'])->get();

        return view('livewire.central.admin-dashboard', compact('tenants'))
            ->layout('layouts.centralApp');
    }
}
