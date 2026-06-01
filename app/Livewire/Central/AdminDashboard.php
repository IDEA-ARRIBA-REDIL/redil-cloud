<?php

namespace App\Livewire\Central;

use App\Models\Tenant;
use Livewire\Component;

class AdminDashboard extends Component
{
    public function toggleSuspension($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->is_suspended = ! $tenant->is_suspended;
        if ($tenant->is_suspended) {
            $tenant->status = 'suspended';
        } else {
            $tenant->status = 'active'; // o al estado anterior, pero por ahora active
        }
        $tenant->save();

        session()->flash('message', 'Estado del inquilino actualizado.');
    }

    public function render()
    {
        $tenants = Tenant::with(['domains', 'plan'])->get();

        return view('livewire.central.admin-dashboard', compact('tenants'))
            ->layout('layouts.centralApp');
    }
}
