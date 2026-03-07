<?php

namespace App\Policies;

use App\Models\FormularioUsuario;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{


    public function nuevoUsuarioPolitica(?User $usuarioLogueado, FormularioUsuario $formulario): bool
    {
        if ($formulario->tipo->es_formulario_exterior) {
            return true;
        }

        if ($usuarioLogueado === null) {
            return false;
        }

        $rolActivo = $usuarioLogueado->roles()->wherePivot('activo', true)->first();

        if (! $rolActivo) {
            return false;
        }

        return $rolActivo->hasPermissionTo('personas.subitem_nuevo_asistente');
    }

    public function modificarUsuarioPolitica(?User $usuarioLogueado, FormularioUsuario $formulario): bool
    {
        if ($formulario->tipo->es_formulario_exterior) {
            return true;
        }

        if ($usuarioLogueado === null) {
          return false;
        }

        $rolActivo = $usuarioLogueado->roles()->wherePivot('activo', true)->first();

        if (! $rolActivo) {
            return false;
        }

        return $rolActivo->hasPermissionTo('personas.pestana_actualizar_asistente') || $rolActivo->hasPermissionTo('personas.opcion_modificar_asistente');
    }

    public function verPerfilUsuarioPolitica(User $usuarioLogueado, User $usuarioUrl, string $nombrePermiso): bool
    {
        // 1. Permiso de Administrador: ¿Puede ver esta sección en CUALQUIER perfil?
        // Construimos el nombre del permiso dinámicamente: 'personas.perfil.familia'
        $permiso = 'personas.perfil.' . $nombrePermiso;
        if ($usuarioLogueado->can($permiso) && $usuarioLogueado->id != $usuarioUrl->id) {
            return true;
        }

        // 2. Permiso de Autogestión: ¿Puede ver esta sección en SU PROPIO perfil?
        // Construimos el nombre del permiso: 'personas.perfil.familia_autogestion'
        $autoPermiso = 'personas.perfil.' . $nombrePermiso . '_autogestion';

        if ($usuarioLogueado->can($autoPermiso) && $usuarioLogueado->id === $usuarioUrl->id) {
            return true;
        }

        // 3. Si no cumple ninguna condición, se deniega el acceso.
        return false;
    }

    public function relacionesFamiliaresUsuarioPolitica(User $usuarioLogueado, User $usuarioUrl): bool
    {
        // Obtenemos el rol activo del usuario que está realizando la acción.
        $rolActivo = $usuarioLogueado->roles()->wherePivot('activo', true)->first();

        // Si por alguna razón no hay un rol activo, denegamos el permiso.
        if (! $rolActivo) {
            return false;
        }

        // Comprobamos si el usuario está viendo su propio perfil.
        $usuarioAutogestion = $usuarioLogueado->id === $usuarioUrl->id;

        if ($usuarioAutogestion) {
            // Si está viendo su propio perfil, necesita el permiso de "autogestión".
            return $rolActivo->hasPermissionTo('personas.auto_gestion_pestana_gentionar_relaciones_familiares');
        } else {
            // Si está viendo el perfil de otra persona, necesita el permiso general.
            return $rolActivo->hasPermissionTo('personas.pestana_gentionar_relaciones_familiares')  || $rolActivo->hasPermissionTo('personas.opcion_gentionar_relaciones_familiares');
        }
    }

    public function geoasignacionUsuarioPolitica(User $usuarioLogueado, User $usuarioUrl): bool
    {
        $rolActivo = $usuarioLogueado->roles()->wherePivot('activo', true)->first();

        if (! $rolActivo) {
            return false;
        }

        $usuarioAutogestion = $usuarioLogueado->id === $usuarioUrl->id;

        if ($usuarioAutogestion) {
            return $rolActivo->hasPermissionTo('personas.auto_gestion_pestana_geoasignacion_grupo');
        } else {
            return $rolActivo->hasPermissionTo('personas.pestana_geoasignacion') || $rolActivo->hasPermissionTo('personas.opcion_geoasignar_asistente');
        }
    }

    public function informacionCongregacionalPolitica(User $usuarioLogueado, User $usuarioUrl): bool
    {
        $rolActivo = $usuarioLogueado->roles()->wherePivot('activo', true)->first();

        if (! $rolActivo) {
            return false;
        }

        $usuarioAutogestion = $usuarioLogueado->id === $usuarioUrl->id;

        if ($usuarioAutogestion) {
            return $rolActivo->hasPermissionTo('personas.autogestion_pestana_informacion_congregacional');
        } else {
            return $rolActivo->hasPermissionTo('personas.pestana_informacion_congregacional') || $rolActivo->hasPermissionTo('personas.opcion_modificar_informacion_congregacional');
        }
    }



}
