<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Plan;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class EstadoTenantService
{
    public function actualizar(Tenant $tenant, string $estado, int $planId, string $vencimiento): void
    {
        $admin = app(SeguridadAdminService::class)->exigir();
        $tenant->getConnection()->transaction(function () use ($tenant, $estado, $planId, $vencimiento, $admin): void {
            $actual = Tenant::query()->whereKey($tenant->id)->lockForUpdate()->firstOrFail();
            if (! in_array($estado, ['pending_review', 'active', 'suspended', 'expired', 'cancelled'], true)) {
                throw ValidationException::withMessages(['status' => 'Estado no permitido.']);
            }
            $conservaPlanAlBloquear = in_array($estado, ['suspended', 'expired', 'cancelled'], true)
                && $planId === $actual->plan_id;
            if (! $conservaPlanAlBloquear && ! Plan::query()->whereKey($planId)->where('activo', true)->exists()) {
                throw ValidationException::withMessages(['plan_id' => 'Selecciona un plan activo.']);
            }
            $fin = Carbon::parse($vencimiento)->startOfDay();
            if ($estado === 'active' && (! $actual->provisioned_at || $fin->copy()->addDays(7)->endOfDay()->isPast())) {
                throw ValidationException::withMessages(['status' => 'No se puede activar sin aprovisionamiento completo y licencia vigente o en gracia.']);
            }
            $cambioLicencia = $actual->license_ends_at?->toDateString() !== $fin->toDateString();
            $datos = [
                'status' => $estado, 'is_suspended' => in_array($estado, ['suspended', 'expired', 'cancelled'], true),
                'plan_id' => $planId, 'license_starts_at' => $actual->license_starts_at ?? now(),
                'license_ends_at' => $fin, 'grace_ends_at' => $fin->copy()->addDays(7),
                'suspension_reason' => $estado === 'active' ? null : $estado,
            ];
            if ($estado === 'active' && ! $actual->approved_at) {
                $datos['approved_at'] = now();
                $datos['approved_by'] = $admin->id;
            }
            if ($cambioLicencia) {
                $datos += ['notified_30_days' => false, 'notified_7_days' => false, 'notified_grace' => false];
            }
            $anterior = $actual->status;
            $actual->update($datos);
            AdminNotification::create([
                'tenant_id' => $actual->id, 'tipo' => 'cambio_estado',
                'mensaje' => 'Admin '.$admin->id.': '.$anterior.' → '.$estado.'; plan '.$planId.'; vence '.$fin->toDateString().'.',
            ]);
        });
        $tenant->refresh();
    }
}
