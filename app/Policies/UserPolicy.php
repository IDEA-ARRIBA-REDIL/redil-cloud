<?php

namespace App\Policies;

use App\Models\FormularioUsuario;
use App\Models\User;

class UserPolicy
{
    private function tienePermiso($rolActivo, string $permiso): bool
    {
        try {
            return $rolActivo->hasPermissionTo($permiso);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist) {
            return false;
        }
    }

    public function nuevoUsuarioPolitica(?User $usuarioLogueado, FormularioUsuario $formulario): bool
    {
        if ($formulario->tipo->es_formulario_exterior) {
            return true;
        }

        if ($usuarioLogueado === null) {
            return false;
        }

        $rolActivo = $usuarioLogueado->rolActivoParaPermisos();

        if (! $rolActivo) {
            return false;
        }

        if (! $this->tienePermiso($rolActivo, 'personas.subitem_nuevo_asistente')) {
            return false;
        }

        // Verificar que el formulario esté asignado al rol activo del usuario
        if (! $formulario->roles()->where('roles.id', $rolActivo->id)->exists()) {
            return false;
        }

        // Verificar si el usuario cumple la restricción de edad para operar este formulario
        return $formulario->puedeSerOperadoPor($usuarioLogueado);
    }

    public function modificarUsuarioPolitica(?User $usuarioLogueado, ?FormularioUsuario $formulario = null): bool
    {
        if ($formulario?->tipo?->es_formulario_exterior) {
            return true;
        }

        if ($usuarioLogueado === null) {
            return false;
        }

        $rolActivo = $usuarioLogueado->rolActivoParaPermisos();

        if (! $rolActivo) {
            return false;
        }

        return $this->tienePermiso($rolActivo, 'personas.pestana_actualizar_asistente') || $this->tienePermiso($rolActivo, 'personas.opcion_modificar_asistente');
    }

    public function verPerfilUsuarioPolitica(User $usuarioLogueado, User $usuarioUrl, string $nombrePermiso): bool
    {
        $rolActivo = $usuarioLogueado->rolActivoParaPermisos();

        if (! $rolActivo) {
            return false;
        }

        // 1. Permiso de Administrador: ¿Puede ver esta sección en CUALQUIER perfil?
        // Construimos el nombre del permiso dinámicamente: 'personas.perfil.familia'
        $permiso = 'personas.perfil.'.$nombrePermiso;
        if ($this->tienePermiso($rolActivo, $permiso) && $usuarioLogueado->id != $usuarioUrl->id) {
            return true;
        }

        // 2. Permiso de Autogestión: ¿Puede ver esta sección en SU PROPIO perfil?
        // Construimos el nombre del permiso: 'personas.perfil.familia_autogestion'
        $autoPermiso = 'personas.perfil.'.$nombrePermiso.'_autogestion';

        if ($this->tienePermiso($rolActivo, $autoPermiso) && $usuarioLogueado->id === $usuarioUrl->id) {
            return true;
        }

        // 3. Si no cumple ninguna condición, se deniega el acceso.
        return false;
    }

    public function accederFicha(User $actor, int $idUsuario): bool
    {
        $rol = $actor->rolActivoParaPermisos();
        if (! $rol) {
            return false;
        }
        if ($actor->id === $idUsuario || $this->tienePermiso($rol, 'personas.lista_asistentes_todos')
            || $this->tienePermiso($rol, 'consolidacion.lista_toda_consolidacion')) {
            return true;
        }
        if ($this->tienePermiso($rol, 'personas.lista_asistentes_solo_ministerio')
            && $actor->discipulos('todos')->contains('id', $idUsuario)) {
            return true;
        }
        if ($this->tienePermiso($rol, 'consolidacion.lista_consolidacion_solo_ministerio')
            && $actor->consolidacion()->where('id', $idUsuario)->count() > 0) {
            return true;
        }

        return $actor->parientesDelUsuario()->where('users.id', $idUsuario)->exists()
            || $actor->usuariosDelPariente()->where('users.id', $idUsuario)->exists();
    }

    public function gestionarParentesco(User $actor, User $persona, string $permiso): bool
    {
        $rol = $actor->rolActivoParaPermisos();

        return $rol && $this->tienePermiso($rol, $permiso)
            && $this->accederFicha($actor, $persona->id)
            && ($this->relacionesFamiliaresUsuarioPolitica($actor, $persona)
                || $this->tienePermiso($rol, 'familiar.subitem_gentionar_relaciones'));
    }

    public function relacionesFamiliaresUsuarioPolitica(User $usuarioLogueado, User $usuarioUrl): bool
    {
        // Obtenemos el rol activo del usuario que está realizando la acción.
        $rolActivo = $usuarioLogueado->rolActivoParaPermisos();

        // Si por alguna razón no hay un rol activo, denegamos el permiso.
        if (! $rolActivo) {
            return false;
        }

        // Comprobamos si el usuario está viendo su propio perfil.
        $usuarioAutogestion = $usuarioLogueado->id === $usuarioUrl->id;

        if ($usuarioAutogestion) {
            // Si está viendo su propio perfil, necesita el permiso de "autogestión".
            return $this->tienePermiso($rolActivo, 'personas.auto_gestion_pestana_gentionar_relaciones_familiares');
        } else {
            // Si está viendo el perfil de otra persona, necesita el permiso general.
            return $this->tienePermiso($rolActivo, 'personas.pestana_gentionar_relaciones_familiares') || $this->tienePermiso($rolActivo, 'personas.opcion_gentionar_relaciones_familiares');
        }
    }

    public function geoasignacionUsuarioPolitica(User $usuarioLogueado, User $usuarioUrl): bool
    {
        $rolActivo = $usuarioLogueado->rolActivoParaPermisos();

        if (! $rolActivo) {
            return false;
        }

        $usuarioAutogestion = $usuarioLogueado->id === $usuarioUrl->id;

        if ($usuarioAutogestion) {
            return $this->tienePermiso($rolActivo, 'personas.auto_gestion_pestana_geoasignacion_grupo');
        } else {
            return $this->tienePermiso($rolActivo, 'personas.pestana_geoasignacion') || $this->tienePermiso($rolActivo, 'personas.opcion_geoasignar_asistente');
        }
    }

    public function informacionCongregacionalPolitica(User $usuarioLogueado, User $usuarioUrl): bool
    {
        $rolActivo = $usuarioLogueado->rolActivoParaPermisos();

        if (! $rolActivo) {
            return false;
        }

        $usuarioAutogestion = $usuarioLogueado->id === $usuarioUrl->id;

        if ($usuarioAutogestion) {
            return $this->tienePermiso($rolActivo, 'personas.autogestion_pestana_informacion_congregacional');
        } else {
            return $this->tienePermiso($rolActivo, 'personas.pestana_informacion_congregacional') || $this->tienePermiso($rolActivo, 'personas.opcion_modificar_informacion_congregacional');
        }
    }

    /**
     * Determina si el usuario logueado puede gestionar tareas de consolidación sobre una persona.
     */
    public function gestionarTareasConsolidacionPolitica(User $usuarioLogueado, User $persona): bool
    {
        $rolActivo = $usuarioLogueado->rolActivoParaPermisos();

        if (! $rolActivo) {
            return false;
        }

        // 1. Si tiene permiso global para toda la consolidación
        if ($this->tienePermiso($rolActivo, 'consolidacion.lista_toda_consolidacion')) {
            return true;
        }

        // 2. Si tiene permiso limitado solo a su ministerio / discípulos
        if ($this->tienePermiso($rolActivo, 'consolidacion.lista_consolidacion_solo_ministerio')) {
            $idsPermitidos = $usuarioLogueado->consolidacion()->pluck('id')->all();

            return in_array($persona->id, $idsPermitidos, true);
        }

        // 3. Sin permisos de consolidación
        return false;
    }
}
