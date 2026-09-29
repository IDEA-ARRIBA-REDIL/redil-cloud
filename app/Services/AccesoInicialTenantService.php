<?php

namespace App\Services;

use App\Mail\AccesoInicialTenantMail;
use App\Models\InvitacionAccesoTenant;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccesoInicialTenantService
{
    public function enviar(Tenant $tenant): int
    {
        app(SeguridadAdminService::class)->exigir();
        abort_unless($tenant->permiteAcceso() && $tenant->provisioned_at, 422, 'Activa un entorno preparado antes de enviar accesos.');
        app(SeguridadAdminService::class)->limitar('acceso-inicial:'.$tenant->id, 3, 3600);
        $cuentas = $tenant->run(fn () => User::query()->whereIn('id', $tenant->initial_user_ids ?? [])
            ->whereNull('email_verified_at')->get(['id', 'email'])->toArray());
        $enviados = 0;
        foreach ($cuentas as $cuenta) {
            if (! filter_var($cuenta['email'], FILTER_VALIDATE_EMAIL) || Str::endsWith(Str::lower($cuenta['email']), '.invalid')) {
                continue;
            }
            $codigo = bin2hex(random_bytes(32));
            InvitacionAccesoTenant::updateOrCreate(
                ['tenant_id' => $tenant->id, 'user_id' => $cuenta['id']],
                ['email' => $cuenta['email'], 'token_hash' => hash('sha256', $codigo),
                    'expires_at' => now()->addHours(config('admin_global.access_hours')), 'used_at' => null]
            );
            Mail::to($cuenta['email'])->queue(new AccesoInicialTenantMail($codigo, $tenant->church_name));
            $enviados++;
        }

        return $enviados;
    }

    public function activar(string $codigo, string $password): void
    {
        (new InvitacionAccesoTenant)->getConnection()->transaction(function () use ($codigo, $password): void {
            $invitacion = InvitacionAccesoTenant::query()->where('token_hash', hash('sha256', $codigo))->lockForUpdate()->first();
            $tenant = $invitacion ? Tenant::find($invitacion->tenant_id) : null;
            if (! $invitacion || $invitacion->used_at || ! $invitacion->expires_at->isFuture() || ! $tenant?->permiteAcceso()) {
                throw ValidationException::withMessages(['codigo' => 'Código inválido, vencido o entorno no habilitado.']);
            }
            $tenant->run(function () use ($invitacion, $password): void {
                $user = User::query()->find($invitacion->user_id);
                if (! $user || $user->email !== $invitacion->email || $user->email_verified_at !== null) {
                    throw ValidationException::withMessages(['codigo' => 'El acceso cambió o ya fue activado. Solicita revisión a REDIL.']);
                }
                $user->forceFill(['password' => Hash::make($password), 'email_verified_at' => now(), 'remember_token' => Str::random(60)])->save();
            });
            $invitacion->update(['used_at' => now()]);
        });
    }
}
