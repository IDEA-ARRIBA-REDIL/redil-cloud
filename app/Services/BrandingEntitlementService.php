<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class BrandingEntitlementService
{
    public function canCustomize(?Tenant $tenant = null): bool
    {
        $tenant ??= tenant();

        if (! $tenant instanceof Tenant) {
            return false;
        }

        $tenant->loadMissing('plan');
        $configuracion = Configuracion::query()->first();

        if (! $configuracion) {
            return false;
        }

        return ((bool) $tenant->plan?->incluye_logo && (bool) $configuracion->logo_personalizado)
            || ((bool) $tenant->plan?->incluye_marca_blanca && (bool) $configuracion->marca_blanca);
    }

    public function authorizeUser(?User $user, string $permission): void
    {
        abort_unless($user, 403);
        abort_unless($this->canCustomize(), 403, 'El plan actual no tiene activa la personalización de marca.');

        $rolActivo = $user->roles()->wherePivot('activo', true)->first();
        abort_unless($rolActivo, 403);

        try {
            $rolActivo->verificacionDelPermiso($permission);
        } catch (PermissionDoesNotExist) {
            abort(403, 'El permiso requerido todavía no está instalado en este tenant.');
        }
    }
}
