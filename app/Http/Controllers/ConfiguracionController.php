<?php

namespace App\Http\Controllers;

use App\Services\BrandingEntitlementService;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function personalizacionLogin(BrandingEntitlementService $entitlementService): View
    {
        $entitlementService->authorizeUser(
            auth()->user(),
            'configuraciones.subitem_personalizacion_login'
        );

        return view('contenido.paginas.configuracion.personalizacion-login');
    }

    /**
     * Muestra el dashboard de configuración.
     */
    public function index(BrandingEntitlementService $entitlementService): View
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
                'keywords' => 'general datos iglesia sede parametros basico configuracion logo informacion',
            ],
            [
                'title' => 'Roles',
                'route' => 'configuracion.gestionar-roles',
                'icon' => 'ti-user-check',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_roles',
                'keywords' => 'roles permisos accesos privilegios seguridad perfiles usuarios administradores',
            ],
            [
                'title' => 'Zonas',
                'route' => 'configuracion.gestionar-zonas',
                'icon' => 'ti-map-pin',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_zonas',
                'keywords' => 'zonas sectores ubicacion barrios regiones ciudades localidades',
            ],
            [
                'title' => 'Plantilla',
                'route' => 'theme-setting.index',
                'icon' => 'ti-palette',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_plantilla',
                'requires_branding' => true,
                'keywords' => 'plantilla tema colores apariencia diseño theme interfaz personalizacion layout estilo',
            ],
            [
                'title' => 'Personalización del login',
                'route' => 'configuracion.personalizacion-login',
                'icon' => 'ti-photo-cog',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_personalizacion_login',
                'requires_branding' => true,
                'keywords' => 'login inicio sesion fondo branding portada acceso imagen logo autenticacion',
            ],
            [
                'title' => 'Notificaciones',
                'route' => 'notificaciones.configuracion',
                'icon' => 'ti-bell-ringing',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_general',
                'keywords' => 'notificaciones alertas correos avisos email push recordatorios mensajes plantillas',
            ],
            [
                'title' => 'Pasos de crecimiento',
                'route' => 'gestionar-pasos-de-crecimiento.pasosDeCrecimiento',
                'icon' => 'ti-trending-up',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_pasos_de_crecimiento',
                'keywords' => 'pasos crecimiento discipulado procesos etapas ruta espiritual requisitos niveles',
            ],
            [
                'title' => 'Rueda de la vida',
                'route' => 'ruedaDeLaVida.gestionar',
                'icon' => 'ti-circle-dashed-check',
                'color' => 'bg-label-secondary',
                'permission' => 'rueda_de_la_vida.item_rueda_de_la_vida',
                'keywords' => 'rueda de la vida diagnostico evaluacion areas dimensiones espiritual personal metas balance',
            ],
            [
                'title' => 'Tipos de grupos',
                'route' => 'gestionar-tipos-de-grupos.listar',
                'icon' => 'ti-users-group',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipos_de_grupos',
                'keywords' => 'tipos grupos celulas grupos de conexion comunidades red lideres reuniones',
            ],
            [
                'title' => 'Tipos de actividad',
                'route' => 'gestionar-tipos-de-actividad.index',
                'icon' => 'ti-calendar-event',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.gestionar_tipos_actividad',
                'keywords' => 'tipos actividad eventos servicios reuniones categorias calendario programas campamentos',
            ],
            [
                'title' => 'Tipos de usuarios',
                'route' => 'tipo-usuario.listar',
                'icon' => 'ti-user-cog',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipo_de_usuarios',
                'keywords' => 'tipos usuarios miembros lideres categorias clasificacion perfiles personas',
            ],
            [
                'title' => 'Filtro consolidación',
                'route' => 'filtros-consolidacion.listarFiltrosConsolidacion',
                'icon' => 'ti-filter',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipo_de_usuarios',
                'keywords' => 'filtro consolidacion nuevos creyentes seguimiento visitantes etapas conversion',
            ],
            [
                'title' => 'Tarea consolidación',
                'route' => 'tareas-consolidacion.listarTareasConsolidacion',
                'icon' => 'ti-list-check',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tarea_consolidacion',
                'keywords' => 'tarea consolidacion asignaciones llamadas visitas seguimiento pasos tareas llamadas',
            ],
            [
                'title' => 'Rangos de edad',
                'route' => 'rangos-edad.listar',
                'icon' => 'ti-cake',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_rangos_de_edad',
                'keywords' => 'rangos edad etapas generacionales niños jovenes adultos edades clasificacion',
            ],
            [
                'title' => 'Profesiones',
                'route' => 'profesiones.index',
                'icon' => 'ti-briefcase',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_profesiones',
                'keywords' => 'profesiones ocupaciones trabajos oficios carreras titulos empleo laboral',
            ],
            [
                'title' => 'Ocupaciones',
                'route' => 'ocupaciones.index',
                'icon' => 'ti-hammer',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_ocupaciones',
                'keywords' => 'ocupaciones oficios trabajos profesiones empleo cargos labor actividades',
            ],
            [
                'title' => 'Sectores económicos',
                'route' => 'sectores-economicos.index',
                'icon' => 'ti-building-factory-2',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_sectores_economicos',
                'keywords' => 'sectores economicos economia industrias comercio empresas financiero agropecuario servicios',
            ],
            [
                'title' => 'Estados civiles',
                'route' => 'estados-civiles.index',
                'icon' => 'ti-heart-handshake',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_estados_civiles',
                'keywords' => 'estados civiles soltero casado union libre divorciado viudo pareja matrimonio relacion conyugal',
            ],
            [
                'title' => 'Tipos de vinculación',
                'route' => 'tipo-vinculaciones.index',
                'icon' => 'ti-link',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipo_vinculaciones',
                'keywords' => 'tipo vinculaciones miembros asistentes lideres visitantes conexion membresia grupos relacion',
            ],
            [
                'title' => 'Tipos de identificación',
                'route' => 'tipo-identificaciones.index',
                'icon' => 'ti-id',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipo_identificaciones',
                'keywords' => 'tipo identificacion documento cedula pasaporte nit tarjeta identidad registro civil abreviatura donacion',
            ],
            [
                'title' => 'Tipos de ofrendas',
                'route' => 'tipo-ofrenda.listar',
                'icon' => 'ti-coin',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipos_de_ofrendas',
                'keywords' => 'tipos ofrendas diezmos donaciones finanzas aportes dinero ingresos sobres',
            ],
            [
                'title' => 'Servicios actividades',
                'route' => 'tipo-servicio-actividad.listar',
                'icon' => 'ti-briefcase',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.item_configuraciones',
                'keywords' => 'servicios actividades eventos ministerios atencion servidores voluntariado turnos',
            ],
            [
                'title' => 'Servicios reuniones',
                'route' => 'tipo-servicio-reunion.listar',
                'icon' => 'ti-building-church',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.item_configuraciones',
                'keywords' => 'servicios reuniones cultos dominicales servidores programacion turnos',
            ],
            [
                'title' => 'Lista reproducción',
                'route' => 'configuracion.gestionar-lista-reproduccion',
                'icon' => 'ti-music',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_lista_de_reproduccion',
                'keywords' => 'lista reproduccion canciones musica audio alabanza tiempo con dios playlist adoracion reproductor',
            ],
            [
                'title' => 'Formularios',
                'route' => 'formularioUsuario.lista',
                'icon' => 'ti-forms',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_formulario_usuarios',
                'keywords' => 'formularios encuestas registros datos campos dinamicos inscripcion preguntas',
            ],
            [
                'title' => 'Campos formularios',
                'route' => 'formularioUsuario.listaCampos',
                'icon' => 'ti ti-input-check',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_gestionar_campos_formulario_usuario',
                'keywords' => 'campos formularios preguntas inputs personalizados datos formulario atributos',
            ],
            [
                'title' => 'Banners generales',
                'route' => 'banner-general.listarBanners',
                'icon' => 'ti-photo',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_banner_general',
                'keywords' => 'banners generales imagenes anuncios publicidad destacados avisos carrusel slider',
            ],
            [
                'title' => 'Tipo pago',
                'route' => 'tipo-pagos.listarTipoPagos',
                'icon' => 'ti-credit-card',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipo_pagos',
                'keywords' => 'tipo pago metodos medios pasarelas tarjetas efectivo transferencias pasarela compras taquilla',
            ],
            [
                'title' => 'Tipo de peticiones',
                'route' => 'tipo-peticiones.listar',
                'icon' => 'ti-file-star',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_tipo_de_peticiones',
                'keywords' => 'tipo peticiones oracion motivos solicitudes intercesion peticion necesidades',
            ],
            [
                'title' => 'Gestionar videos',
                'route' => 'gestion-videos.listarVideos',
                'icon' => 'ti-video',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.subitem_gestionar_videos',
                'keywords' => 'gestionar videos multimedia youtube grabaciones transmisiones audiovisuales enlaces',
            ],
            [
                'title' => 'Tipos de hitos',
                'route' => 'tipo-hitos.listarTipoHitos',
                'icon' => 'ti-trophy',
                'color' => 'bg-label-secondary',
                'permission' => ['hitos.gestionar', 'configuraciones.item_configuraciones'],
                'keywords' => 'tipos hitos logros metas avances celebraciones etapas reconocimientos',
            ],
            [
                'title' => 'Gamificación',
                'route' => 'gamificacion.configuracion',
                'icon' => 'ti-award',
                'color' => 'bg-label-secondary',
                'permission' => 'configuraciones.configuracion_gamificacion',
                'keywords' => 'gamificacion puntos insignias niveles recompensas desafios logros medallas reglas',
            ],
        ];

        // Filtrar items por permisos
        $filteredItems = array_filter($items, function ($item) use ($entitlementService, $rolActivo) {
            if (! $rolActivo) {
                return false;
            }

            if (($item['requires_branding'] ?? false) && ! $entitlementService->canCustomize()) {
                return false;
            }

            try {
                if (is_array($item['permission'])) {
                    foreach ($item['permission'] as $perm) {
                        try {
                            if ($rolActivo->hasPermissionTo($perm)) {
                                return true;
                            }
                        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist) {
                            continue;
                        }
                    }

                    return false;
                }

                return $rolActivo->hasPermissionTo($item['permission']);
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist) {
                return false;
            }
        });

        return view('contenido.paginas.configuracion.index', [
            'items' => array_values($filteredItems),
        ]);
    }
}
