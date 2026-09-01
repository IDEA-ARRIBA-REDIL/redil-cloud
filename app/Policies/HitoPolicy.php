<?php

namespace App\Policies;

use App\Models\Hito;
use App\Models\User;

class HitoPolicy
{
    public function verMuro(User $user): bool
    {
        return $user->can('hitos.ver_muro');
    }

    public function gestionar(User $user): bool
    {
        return $user->can('hitos.gestionar');
    }

    public function crear(User $user): bool
    {
        return $user->can('hitos.crear');
    }

    public function editar(User $user, Hito $hito): bool
    {
        return $user->can('hitos.editar') || $hito->user_id === $user->id;
    }

    public function eliminar(User $user, Hito $hito): bool
    {
        return $user->can('hitos.eliminar') || $hito->user_id === $user->id;
    }

    public function gestionarDenuncias(User $user): bool
    {
        return $user->can('hitos.gestionar_denuncias');
    }

    public function gestionarAsistencia(User $user): bool
    {
        return $user->can('hitos.gestionar_asistencia');
    }

    public function subirFotos(User $user): bool
    {
        return $user->can('hitos.subir_fotos');
    }

    public function darLike(User $user): bool
    {
        return $user->can('hitos.like');
    }

    public function migrarRetroactivo(User $user): bool
    {
        return $user->can('hitos.migrar_retroactivo');
    }
}
