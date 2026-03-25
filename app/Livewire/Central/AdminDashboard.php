<?php

namespace App\Livewire\Central;

use App\Models\Tenant;
use Livewire\Component;

class AdminDashboard extends Component
{
    public function toggleSuspension($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        // En stancl/tenancy v3 $tenant->data es un array por defecto
        $data = $tenant->data;
        $data['is_suspended'] = ! ($data['is_suspended'] ?? false);
        $tenant->data = $data;
        $tenant->save();

        session()->flash('message', 'Estado del inquilino actualizado.');
    }

    public function render()
    {
        $tenants = Tenant::with('domains')->get();

        return view('livewire.central.admin-dashboard', compact('tenants'))
            ->layout('layouts.centralApp');
    }
}
