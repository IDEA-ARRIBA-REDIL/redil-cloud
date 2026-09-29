<?php

namespace App\Livewire\Central;

use App\Models\InvitacionTenant;
use App\Models\Plan;
use App\Services\SeguridadAdminService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;

class GestionarInvitaciones extends ComponenteAdminCentral
{
    public string $email = '';

    public $planId = '';

    public string $referenciaPago = '';

    public bool $pagoConfirmado = false;

    #[Locked]
    public string $codigoGenerado = '';

    public function emitir(): void
    {
        $admin = app(SeguridadAdminService::class)->exigir();
        $this->email = Str::lower(trim($this->email));
        $this->validate([
            'email' => ['required', 'email', 'max:254'],
            'planId' => ['required', Rule::exists('central.plans', 'id')->where('activo', true)],
            'referenciaPago' => ['required', 'string', 'max:150', Rule::unique('central.invitaciones_tenant', 'payment_reference')],
            'pagoConfirmado' => ['accepted'],
        ]);
        $codigo = bin2hex(random_bytes(32));
        InvitacionTenant::create([
            'token_hash' => hash('sha256', $codigo), 'email' => $this->email, 'plan_id' => $this->planId,
            'created_by' => $admin->id, 'payment_reference' => $this->referenciaPago,
            'paid_at' => now(), 'expires_at' => now()->addDays(config('admin_global.invitation_days')),
        ]);
        $this->reset('referenciaPago', 'pagoConfirmado');
        $this->codigoGenerado = $codigo;
    }

    public function revocar(int $id): void
    {
        app(SeguridadAdminService::class)->exigir();
        InvitacionTenant::query()->whereKey($id)->whereNull('used_at')->update(['revoked_at' => now()]);
        $this->reset('codigoGenerado');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.central.gestionar-invitaciones', [
            'planes' => Plan::query()->where('activo', true)->get(),
            'invitaciones' => InvitacionTenant::query()->latest()->limit(50)->get(),
        ])->layout('layouts.centralApp');
    }
}
