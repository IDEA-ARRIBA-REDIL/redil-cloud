<?php

namespace App\Policies;

use App\Models\Sede;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SedePolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede ver el perfil de la sede.
     */
    public function verPerfil(User $user, Sede $sede): bool
    {
        return $user->hasPermissionTo('sedes.opcion_ver_perfil_sede') &&
               $user->tieneJurisdiccionSobreSede($sede);
    }

    /**
     * Determina si el usuario puede modificar la sede.
     */
    public function modificar(User $user, Sede $sede): bool
    {
        return $user->hasPermissionTo('sedes.opcion_modificar_sede') &&
               $user->tieneJurisdiccionSobreSede($sede);
    }

    /**
     * Determina si el usuario puede eliminar la sede.
     */
    public function eliminar(User $user, Sede $sede): bool
    {
        return $user->hasPermissionTo('sedes.opcion_eliminar_sede') &&
               $user->tieneJurisdiccionSobreSede($sede);
    }

    /**
     * Determina si el usuario puede ver el dashboard de consolidación de la sede.
     */
    public function dashboardConsolidacion(User $user, Sede $sede): bool
    {
        return $user->hasPermissionTo('sedes.opcion_dashboard_consolidacion') &&
               $user->tieneJurisdiccionSobreSede($sede);
    }
}
