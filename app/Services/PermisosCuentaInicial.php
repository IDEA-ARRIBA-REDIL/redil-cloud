<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class PermisosCuentaInicial
{
    public const ADMINISTRADOR = 'Administrador iglesia';

    public const SUPERADMINISTRADOR = 'Super Administrador REDIL';

    public const NOTIFICACIONES = 'configuraciones.subitem_notificaciones';

    public static function reservado(string $permission): bool
    {
        return Str::contains(Str::lower($permission), ['hito', 'notifica', 'rueda_de_la_vida', 'plantilla', 'personalizacion_login']);
    }

    public static function autorizarNotificaciones(?User $user): void
    {
        self::autorizar($user, self::NOTIFICACIONES);
    }

    public static function autorizar(?User $user, string $permission): void
    {
        abort_unless($user?->roles()->wherePivot('activo', true)
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))->exists(), 403);
    }
}
