<?php

namespace App\Livewire\Central;

use Livewire\Component;

use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Support\Facades\Mail;
use App\Mail\CuentaActivadaMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DetalleTenant extends Component
{
    public Tenant $tenant;
    public $status, $plan_id, $license_ends_at;

    public function mount(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->status = $tenant->status;
        $this->plan_id = $tenant->plan_id;
        $this->license_ends_at = $tenant->license_ends_at ? $tenant->license_ends_at->format('Y-m-d') : '';
    }

    public function updateStatus()
    {
        $this->validate([
            'status' => 'required|in:pending_review,active,suspended,expired',
            'plan_id' => 'required|exists:plans,id',
            'license_ends_at' => 'required|date',
        ]);

        $previousStatus = $this->tenant->status;

        $this->tenant->update([
            'status' => $this->status,
            'is_suspended' => ($this->status === 'suspended'),
            'plan_id' => $this->plan_id,
            'license_starts_at' => $this->tenant->license_starts_at ?? now(),
            'license_ends_at' => Carbon::parse($this->license_ends_at),
            'grace_ends_at' => Carbon::parse($this->license_ends_at)->addDays(7),
        ]);

        // Si se acaba de activar por primera vez
        if ($previousStatus === 'pending_review' && $this->status === 'active') {
            $domain = $this->tenant->domains->first()->domain ?? null;
            if ($domain) {
                Mail::to($this->tenant->admin_email)->queue(new CuentaActivadaMail($this->tenant, $domain));
            }

            DB::table('admin_notifications')->insert([
                'tenant_id' => $this->tenant->id,
                'tipo' => 'account_approved',
                'mensaje' => "El tenant {$this->tenant->church_name} ha sido aprobado.",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        session()->flash('success', 'El estado del inquilino ha sido actualizado correctamente.');
    }

    public function render()
    {
        return view('livewire.central.detalle-tenant', [
            'planes' => Plan::all(),
        ])->layout('layouts.centralApp');
    }
}
