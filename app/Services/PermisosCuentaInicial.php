<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class PermisosCuentaInicial
{
    public const ADMINISTRADOR = 'Administrador iglesia';

    public const SUPERADMINISTRADOR = 'Super Administrador REDIL';

    public const NOTIFICACIONES = 'configuraciones.subitem_notificaciones';

    /**
     * Permisos que operan como filtros restrictivos de alcance (solo ministerio / solo propios)
     * y no deben asignarse a los roles administrativos globales.
     *
     * @var list<string>
     */
    public const RESTRICCIONES_ALCANCE = [
        'personas.lista_asistentes_solo_ministerio',
        'personas.ajax_obtiene_asistentes_solo_ministerio',
        'grupos.lista_grupos_solo_ministerio',
        'grupos.ajax_obtiene_grupos_solo_ministerio',
        'grupos.mapa_grupos_solo_ministerio',
        'grupos.grafico_ministerio_solo_ministerio',
        'reportes_grupos.lista_reportes_grupo_solo_ministerio',
        'reuniones.lista_reuniones_solo_ministerio',
        'reporte_reuniones.lista_reportes_reunion_solo_ministerio',
        'sedes.lista_sedes_solo_ministerio',
        'ingresos.lista_ingresos_solo_ministerio',
        'peticiones.lista_peticiones_solo_ministerio',
        'consolidacion.lista_consolidacion_solo_ministerio',
        'posts.listar_solo_mis_publicaciones',
        'cursos.listar_solo_cursos_asignados',
        'planes_lectores.listar_solo_mis_planes_lectores',
    ];

    public static function esRestriccionAlcance(string $permission): bool
    {
        return in_array($permission, self::RESTRICCIONES_ALCANCE, true);
    }

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
