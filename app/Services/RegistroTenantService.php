<?php

namespace App\Services;

use App\Jobs\ConfigurarNuevoTenantJob;
use App\Models\AdminNotification;
use App\Models\InvitacionTenant;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistroTenantService
{
    public function registrar(array $datos): Tenant
    {
        $email = Str::lower(trim($datos['admin_email']));
        $domain = $this->dominio($datos['domain']);

        return (new InvitacionTenant)->getConnection()->transaction(function () use ($datos, $email, $domain): Tenant {
            $invitacion = InvitacionTenant::query()->where('token_hash', hash('sha256', trim($datos['codigo'])))->lockForUpdate()->first();
            if (! $invitacion?->disponible() || ! hash_equals($invitacion->email, $email)) {
                throw ValidationException::withMessages(['codigo' => 'El código no es válido, venció o no corresponde al correo.']);
            }
            $plan = Plan::query()->whereKey($invitacion->plan_id)->where('activo', true)->first();
            if (! $plan) {
                throw ValidationException::withMessages(['codigo' => 'Contacta con REDIL para revisar la invitación.']);
            }
            if (\Stancl\Tenancy\Database\Models\Domain::query()->where('domain', $domain)->exists()) {
                throw ValidationException::withMessages(['domain' => 'Este subdominio ya está registrado.']);
            }
            $tenant = Tenant::create([
                'id' => (string) Str::uuid(),
                'onboarding_version' => 1,
                'church_name' => $datos['church_name'], 'pastor_name' => $datos['pastor_name'],
                'city' => $datos['city'], 'country' => $datos['country'],
                'estimated_members' => $datos['estimated_members'], 'whatsapp' => $datos['whatsapp'],
                'pastor_phone' => $datos['pastor_phone'], 'admin_contact_name' => $datos['admin_contact_name'],
                'admin_contact_phone' => $datos['admin_contact_phone'],
                'admin_email' => $email, 'plan_id' => $plan->id,
                'status' => 'pending_review', 'is_suspended' => false,
            ]);
            $tenant->domains()->create(['domain' => $domain]);
            $invitacion->update(['used_at' => now(), 'tenant_id' => $tenant->id]);
            AdminNotification::create(['tenant_id' => $tenant->id, 'tipo' => 'nuevo_registro', 'mensaje' => 'Alta con invitación pagada. Pendiente de aprovisionamiento y aprobación.']);
            ConfigurarNuevoTenantJob::dispatch($tenant->id)->afterCommit();

            return $tenant;
        });
    }

    public function dominio(string $subdominio): string
    {
        $subdominio = Str::lower(trim($subdominio));
        $base = Str::lower(trim((string) config('admin_global.registration_domain')));
        if (! preg_match('/\\A[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\\z/', $subdominio)
            || in_array($subdominio, config('admin_global.reserved_subdomains'), true)) {
            throw ValidationException::withMessages(['domain' => 'Usa un subdominio válido y no reservado (máximo 63 caracteres).']);
        }
        if (! $base || str_contains($base, '/') || str_contains($base, ':') || strlen($subdominio.'.'.$base) > 253) {
            throw ValidationException::withMessages(['domain' => 'REDIL debe configurar el dominio de registro antes de continuar.']);
        }

        return $subdominio.'.'.$base;
    }
}
