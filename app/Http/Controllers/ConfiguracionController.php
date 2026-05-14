<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    /**
     * Muestra el dashboard de configuración.
     *
     * @return View
     */
    public function index(): View
    {
        $user = auth()->user();
        $rolActivo = $user->roles()->wherePivot('activo', true)->first();

        $items = [
            [
                'title' => 'General',
                'route' => 'configuracion-general.configuracionGeneral',
                'icon' => 'ti-settings-automation',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_general',
            ],
            [
                'title' => 'Roles',
                'route' => 'configuracion.gestionar-roles',
                'icon' => 'ti-user-check',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_roles',
            ],
            [
                'title' => 'Zonas',
                'route' => 'configuracion.gestionar-zonas',
                'icon' => 'ti-map-pin',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_zonas',
            ],
            [
                'title' => 'Plantilla',
                'route' => 'theme-setting.index',
                'icon' => 'ti-palette',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_plantilla',
            ],
            [
                'title' => 'Notificaciones',
                'route' => 'notificaciones.configuracion',
                'icon' => 'ti-bell-ringing',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_general',
            ],
            [
                'title' => 'Pasos de crecimiento',
                'route' => 'gestionar-pasos-de-crecimiento.pasosDeCrecimiento',
                'icon' => 'ti-trending-up',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_pasos_de_crecimiento',
            ],
            [
                'title' => 'Tipos de grupos',
                'route' => 'gestionar-tipos-de-grupos.listar',
                'icon' => 'ti-users-group',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipos_de_grupos',
            ],
            [
                'title' => 'Tipos de actividad',
                'route' => 'gestionar-tipos-de-actividad.index',
                'icon' => 'ti-calendar-event',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.gestionar_tipos_actividad',
            ],
            [
                'title' => 'Tipos de usuarios',
                'route' => 'tipo-usuario.listar',
                'icon' => 'ti-user-cog',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipo_de_usuarios',
            ],
            [
                'title' => 'Filtro consolidación',
                'route' => 'filtros-consolidacion.listarFiltrosConsolidacion',
                'icon' => 'ti-filter',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipo_de_usuarios',
            ],
            [
                'title' => 'Tarea consolidación',
                'route' => 'tareas-consolidacion.listarTareasConsolidacion',
                'icon' => 'ti-list-check',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tarea_consolidacion',
            ],
            [
                'title' => 'Rangos de edad',
                'route' => 'rangos-edad.listar',
                'icon' => 'ti-cake',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_rangos_de_edad',
            ],
            [
                'title' => 'Tipos de ofrendas',
                'route' => 'tipo-ofrenda.listar',
                'icon' => 'ti-coin',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipos_de_ofrendas',
            ],
            [
                'title' => 'Servicios actividades',
                'route' => 'tipo-servicio-actividad.listar',
                'icon' => 'ti-briefcase',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.item_configuraciones',
            ],
            [
                'title' => 'Servicios reuniones',
                'route' => 'tipo-servicio-reunion.listar',
                'icon' => 'ti-building-church',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.item_configuraciones',
            ],
            [
                'title' => 'Lista reproducción',
                'route' => 'configuracion.gestionar-lista-reproduccion',
                'icon' => 'ti-music',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_lista_de_reproduccion',
            ],
            [
                'title' => 'Formularios',
                'route' => 'formularioUsuario.lista',
                'icon' => 'ti-forms',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_formulario_usuarios',
            ],
            [
                'title' => 'Banners generales',
                'route' => 'banner-general.listarBanners',
                'icon' => 'ti-photo',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_banner_general',
            ],
            [
                'title' => 'Tipo pago',
                'route' => 'tipo-pagos.listarTipoPagos',
                'icon' => 'ti-credit-card',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipo_pagos',
            ],
            [
                'title' => 'Gestionar videos',
                'route' => 'gestion-videos.listarVideos',
                'icon' => 'ti-video',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_gestionar_videos',
            ],
        ];

        // Filtrar items por permisos
        $filteredItems = array_filter($items, function ($item) use ($rolActivo) {
            return $rolActivo->hasPermissionTo($item['permission']);
        });

        return view('contenido.paginas.configuracion.index', [
            'items' => $filteredItems,
        ]);
    }
}
