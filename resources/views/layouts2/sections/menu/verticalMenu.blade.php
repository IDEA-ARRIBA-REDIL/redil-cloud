@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Auth;
    use App\Models\Iglesia;
    use App\Models\Configuracion;
    $configData = Helper::appClasses();

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $formularios = $rolActivo->formularios()->where('tipo_formulario_id', '=', 1)->get();
    $formularioMenores = $rolActivo->formularios()->where('tipo_formulario_id', '=', 4)->get();
    $grupo = auth()->user()->gruposEncargados()->first();
    $configuracion = Configuracion::find(1);
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
    @if (!isset($navbarFull))
        <div style="padding-left: 20px !important;" class="app-brand demo p-0">
            <a href="{{ url('/') }}" class="app-brand-link">
                @if ($configuracion->version == 1)
                    <img style="width:30px" class="app-brand-logo"
                        src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/logo_crecer.png') }}">
                @else
                    <span class="app-brand-logo demo p-0">
                        @include('_partials.macros', [
                            'height' => '40px',
                            'width' => '40px',
                            'fill' => '#3772e4',
                        ])
                    </span>
                @endif
                <span style='color:{{ config('variables.templateNameColor') }} !important'
                    class="app-brand-text demo menu-text fw-bold fs-4">{{ config('variables.templateName') }}</span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle pe-5"></i>
                <i class="ti ti-x d-block d-xl-none ti-sm align-middle pe-2"></i>
            </a>
        </div>
    @endif

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Menú</span>
        </li>

        <li class="menu-item">
            <a href="{{ url('') }}" class="menu-link active">

                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div>Inicio </div>
            </a>
        </li>

        @if ($rolActivo->hasPermissionTo('personas.item_asistentes'))
            <li class="menu-item {{ request()->routeIs('usuario.*') ? 'active open' : '' }}">
                <a href="" class="menu-link menu-toggle ">
                    <i class="menu-icon tf-icons ti ti-users"></i>
                    <div>Personas </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('personas.subitem_lista_asistentes'))
                        <li class="menu-item {{ request()->routeIs('usuario.lista') ? 'active' : '' }}">
                            <a href="{{ route('usuario.lista') }}" class="menu-link">
                                <div>Listado</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('personas.subitem_nuevo_asistente'))
                        @foreach ($formularios as $formulario)
                            <li class="menu-item">
                                <a href="{{ route('usuario.nuevo', $formulario) }}" class="menu-link">
                                    <div>{{ $formulario->label }}</div>
                                </a>
                            </li>
                        @endforeach
                        @foreach ($formularioMenores as $formulario)
                            <li class="menu-item">
                                <a href="{{ route('usuario.nuevo', $formulario) }}" class="menu-link">
                                    <div>{{ $formulario->label }} </div>
                                </a>
                            </li>
                        @endforeach
                    @endif


                </ul>
            </li>
        @endif

        @if ($rolActivo->hasPermissionTo('consolidacion.item_consolidacion'))
            <li class="menu-item {{ request()->routeIs('consolidacion.*') ? 'active open' : '' }}">
                <a href="" class="menu-link menu-toggle ">
                    <i class="menu-icon tf-icons ti ti-heart-handshake"></i>
                    <div>Consolidación </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('consolidacion.subitem_lista_consolidacion'))
                        <li class="menu-item {{ request()->routeIs('consolidacion.lista') ? 'active' : '' }}">
                            <a href="{{ route('consolidacion.lista') }}" class="menu-link">
                                <div>Listado</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('consolidacion.dashboard_consolidacion'))
                        <li class="menu-item {{ request()->routeIs('consolidacion.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('consolidacion.dashboard') }}" class="menu-link">
                                <div>Dashboard</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif


        @if ($rolActivo->hasPermissionTo('consejeria.item_consejeria'))
            <li class="menu-item {{ request()->routeIs('consejeria.*') ? 'active open' : '' }}">
                <a href="" class="menu-link menu-toggle ">
                    <i class="menu-icon tf-icons ti ti-messages"></i>
                    <div>Consejeria </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('consejeria.subitem_gestionar_consejeros'))
                        <li
                            class="menu-item {{ request()->routeIs('consejeria.gestionarConsejeros') ? 'active' : '' }}">
                            <a href="{{ route('consejeria.gestionarConsejeros') }}" class="menu-link">
                                <div>Gestionar consejeros</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('consejeria.subitem_nueva_cita'))
                        <li class="menu-item">
                            <a href="{{ route('consejeria.nuevaCita', auth()->user()->id) }}" class="menu-link">
                                <div>Nueva cita</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('consejeria.subitem_mis_citas'))
                        <li class="menu-item {{ request()->routeIs('consejeria.misCitas') ? 'active' : '' }}">
                            <a href="{{ route('consejeria.misCitas') }}" class="menu-link">
                                <div>Mis citas</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('consejeria.subitem_calendario_citas'))
                        <li class="menu-item {{ request()->routeIs('consejeria.calendarioCitas') ? 'active' : '' }}">
                            <a href="{{ route('consejeria.calendarioCitas') }}" class="menu-link">
                                <div>Calendario de citas</div>
                            </a>
                        </li>
                    @endif
                </ul>
        @endif

        @if ($rolActivo->hasPermissionTo('familiar.item_familiar'))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-home-heart"></i>
                    <div>Familias </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('familiar.subitem_gentionar_relaciones'))
                        <li class="menu-item">
                            <a href="{{ route('familias.gestionar') }}" class="menu-link">
                                <div>Gestionar</div>
                            </a>
                        </li>
                    @endif
                    @if ($rolActivo->hasPermissionTo('familiar.subitem_informes'))
                        <li class="menu-item">
                            <a href="{{ route('familias.informes') }}" class="menu-link">
                                <div>Informes</div>
                            </a>
                        </li>
                    @endif
                </ul>

            </li>
        @endif

        @if ($rolActivo->hasPermissionTo('rueda_de_la_vida.item_rueda_de_la_vida'))
            <li class="menu-item">
                <a href="{{ route('ruedaDeLaVida.gestor') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-circle-dashed-check"></i>
                    <div>Rueda</div>
                </a>
            </li>
        @endif

        @if ($rolActivo->hasPermissionTo('tiempo_con_dios.item_tiempo_con_dios'))
            <li class="menu-item">
                <a href="{{ route('tiempoConDios.historial') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-pray"></i>
                    <div>Mi tiempo con Dios</div>
                </a>
            </li>
        @endif

        @if ($rolActivo->hasPermissionTo('peticiones.item_peticiones'))
            <li class="menu-item {{ request()->routeIs('peticion.*') ? 'active open' : '' }}">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-notes"></i>
                    <div>Peticiones </div>
                </a>

                <ul class="menu-sub">

                    @if ($rolActivo->hasPermissionTo('peticiones.subitem_nueva_peticion'))
                        <li class="menu-item">
                            <a href="{{ route('peticion.nueva') }}" class="menu-link">
                                <div>Nueva</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('peticiones.subitem_gestionar_peticiones'))
                        <li class="menu-item">
                            <a href="{{ route('peticion.gestionar') }}" class="menu-link">
                                <div>Gestionar peticiones</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('peticiones.subitem_panel_peticiones'))
                        <li class="menu-item">
                            <a href="{{ route('peticion.panel') }}" class="menu-link">
                                <div>Panel peticiones</div>
                            </a>
                        </li>
                    @endif
                </ul>

            </li>
        @endif

        @if ($rolActivo->hasPermissionTo('grupos.mi_grupo'))
            <li class="menu-item">
                <a href="{{ isset($grupo) ? route('grupo.perfil.estadisticasGrupo', ['grupo' => $grupo, 'encargado' => auth()->user()->id]) : '' }}"
                    class="menu-link">
                    <i class="menu-icon tf-icons ti ti-users-group"></i>
                    <div>Mi grupo</div>
                </a>
            </li>
        @endif

        @if ($rolActivo->hasPermissionTo('grupos.item_grupos'))
            <li class="menu-item {{ request()->routeIs('grupo.*') ? 'active open' : '' }}">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-atom-2"></i>
                    <div>Grupos </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('grupos.subitem_lista_grupos'))
                        <li class="menu-item {{ request()->routeIs('grupo.lista') ? 'active' : '' }}">
                            <a href="{{ route('grupo.lista') }}" class="menu-link">
                                <div>Listado</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('grupos.subitem_nuevo_grupo'))
                        <li class="menu-item">
                            <a href="{{ route('grupo.nuevo') }}" class="menu-link">
                                <div>Nuevo</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('reportes_grupos.subitem_lista_reportes_grupo'))
                        <li class="menu-item">
                            <a href="{{ route('reporteGrupo.lista') }}" class="menu-link">
                                <div>Lista de reportes</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('grupos.subitem_grafico_ministerio'))
                        <li class="menu-item">
                            <a href="{{ route('grupo.graficoDelMinisterio') }}" class="menu-link">
                                <div>Gráfico del ministerio</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('grupos.subitem_mapa_grupos'))
                        <li class="menu-item">
                            <a href="{{ route('grupo.mapaDeGrupos') }}" class="menu-link">
                                <div>Mapa de grupos</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('grupos.subitem_excluir_asistentes_grupos'))
                        <li class="menu-item">
                            <a href="{{ route('grupo.verExclusiones') }}" class="menu-link">
                                <div>Excluir grupos</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('grupos.subitem_informe_administrativo_de_evidencia_de_grupos'))
                        <li class="menu-item">
                            <a href="{{ route('grupo.informesEvidenciaAdministrativo') }}" class="menu-link">
                                <div>Informes de evidencias</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('grupos.subitem_dashboard'))
                        <li class="menu-item {{ request()->routeIs('grupos.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('grupos.dashboard') }}" class="menu-link">
                                <div>Dashboard de grupos</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('grupos.subitem_lista_informes_grupo'))
                        <li class="menu-item">
                            <a href="{{ route('informe.lista', 1) }}" class="menu-link">
                                <div>Informes</div>
                            </a>
                        </li>
                    @endif
                </ul>

            </li>
        @endif

        @if ($rolActivo->hasPermissionTo('reuniones.item_reuniones'))
            <li
                class="menu-item {{ request()->routeIs('reuniones.*') || request()->routeIs('reporteReunion.*') ? 'active open' : '' }}">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-building-church"></i>
                    <div>Reuniones </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('reuniones.subitem_lista_reuniones'))
                        <li class="menu-item {{ request()->routeIs('reuniones.lista') ? 'active' : '' }}">
                            <a href="{{ route('reuniones.lista') }}" class="menu-link">
                                <div>Listado</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('reuniones.subitem_nueva_reunion'))
                        <li class="menu-item">
                            <a href="{{ route('reuniones.nueva') }}" class="menu-link">
                                <div>Nueva</div>
                            </a>
                        </li>
                    @endif
                    @if ($rolActivo->hasPermissionTo('reporte_reuniones.subitem_lista_reportes_reunion'))
                        <li class="menu-item">
                            <a href="{{ route('reporteReunion.lista') }}" class="menu-link">
                                <div>Listado reportes</div>
                            </a>
                        </li>
                    @endif
                    @if ($rolActivo->hasPermissionTo('reporte_reuniones.subitem_proximas_reuniones'))
                        <li class="menu-item">
                            <a href="{{ route('reporteReunion.iglesiaVirtual') }}" class="menu-link">
                                <div>Próximas reuniones </div>
                            </a>
                        </li>
                    @endif


                </ul>
            </li>
        @endif

        @if ($rolActivo->hasPermissionTo('escuelas.item_escuelas'))
            <li class="menu-item">
                <a target="_blank" href="{{ route('escuelas.dashboard') }}" class="menu-link ">
                    <i class="menu-icon tf-icons ti ti-school"></i>
                    <div>Escuelas </div>
                </a>
            </li>
        @endif

        {{-- Módulo Cursos --}}

        @if ($rolActivo && $rolActivo->hasPermissionTo('cursos.item_cursos'))
        <li class="menu-item {{ request()->routeIs('cursos.*') ? 'active open' : '' }}">
            <a href="" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-device-laptop"></i>
                <div>Cursos (LMS)</div>
            </a>
            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('cursos.subitem_gestionar_cursos'))
                <li class="menu-item {{ request()->routeIs('cursos.gestionar') ? 'active' : '' }}">
                    <a href="{{ route('cursos.gestionar') }}" class="menu-link">
                        <div>Gestionar</div>
                    </a>
                </li>
                @endif
                @if ($rolActivo->hasPermissionTo('cursos.subitem_campus_cursos'))
                <li class="menu-item {{ request()->routeIs('cursos.campus') ? 'active' : '' }}">
                    <a href="{{ route('cursos.campus') }}" class="menu-link">
                        <div>Campus</div>
                    </a>
                </li>
                @endif
                @if ($rolActivo->hasPermissionTo('cursos.subitem_foro_cursos'))
                <li class="menu-item {{ request()->routeIs('cursos.foro') ? 'active' : '' }}">
                    <a href="{{ route('cursos.foro') }}" class="menu-link">
                        <div>Foro (Q&A)</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif


        @if ($rolActivo->hasPermissionTo('sedes.item_sedes'))
            <li class="menu-item {{ request()->routeIs('sede.*') ? 'active open' : '' }}">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-building"></i>
                    <div>Sedes </div>
                </a>

                <ul class="menu-sub">

                    @if ($rolActivo->hasPermissionTo('sedes.subitem_lista_sedes'))
                        <li class="menu-item {{ request()->routeIs('sede.lista') ? 'active' : '' }}">
                            <a href="{{ route('sede.lista') }}" class="menu-link">
                                <div>Listado</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('sedes.subitem_nueva_sede'))
                        <li class="menu-item">
                            <a href="{{ route('sede.nueva') }}" class="menu-link">
                                <div>Nueva</div>
                            </a>
                        </li>
                    @endif

                </ul>

            </li>
        @endif

        @if ($rolActivo->hasPermissionTo('actividades.item_actividades'))
            <li class="menu-item {{ request()->routeIs('actividades.*') ? 'active open' : '' }}">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-calendar-star"></i>
                    <div>Actividades </div>
                </a>
                @if ($rolActivo->hasPermissionTo('actividades.subitem_listado_actividad'))
                    <ul class="menu-sub">
                        <li class="menu-item {{ request()->routeIs('actividades.listado') ? 'active' : '' }}">
                            <a href="{{ route('actividades.index') }}" class="menu-link">
                                <div>Listado</div>
                            </a>
                        </li>
                @endif
                @if ($rolActivo->hasPermissionTo('actividades.subitem_nueva_actividad'))
            <li class="menu-item {{ request()->routeIs('actividades.nueva') ? 'active' : '' }}">
                <a href="{{ route('actividades.nueva') }}" class="menu-link ">
                    <div>Nueva</div>
                </a>
            </li>
        @endif
    </ul>
    </li>
    @endif

    @if ($rolActivo->hasPermissionTo('pdp.item_puntos_de_pago'))
        <li
            class="menu-item {{ request()->routeIs('puntosDePago.*') || request()->routeIs('taquillas.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-building-store"></i>
                <div>Puntos de pago</div>
            </a>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('pdp.gestionar_pdp'))
                    <li class="menu-item {{ request()->routeIs('puntosDePago.gestionar') ? 'active' : '' }}">
                        <a href="{{ route('puntosDePago.gestionar') }}" class="menu-link">
                            <div>Gestionar puntos de pago</div>
                        </a>
                    </li>
                @endif
                @if ($rolActivo->hasPermissionTo('pdp.gestionar_taquillas'))
                    <li class="menu-item {{ request()->routeIs('taquillas.gestionar') ? 'active' : '' }}">
                        <a href="{{ route('taquillas.gestionar') }}" class="menu-link">
                            <div>Gestionar taquillas</div>
                        </a>
                    </li>
                @endif
                @if ($rolActivo->hasPermissionTo('pdp.gestionar_asesores'))
                    <li class="menu-item {{ request()->routeIs('asesores_pdp.gestionar') ? 'active' : '' }}">
                        <a href="{{ route('asesores_pdp.gestionar') }}" class="menu-link">
                            <div>Gestionar asesores</div>
                        </a>
                    </li>
                @endif
                @if ($rolActivo->hasPermissionTo('pdp.gestionar_anulaciones'))
                    <li class="menu-item ">
                        <a href="{{ route('taquilla.listarSolicitudesAnulacion') }}" class="menu-link">
                            <div>Gestionar anulaciones</div>
                        </a>
                    </li>
                @endif
                @if ($rolActivo->hasPermissionTo('pdp.historial_anulaciones'))
                    <li class="menu-item ">
                        <a href="{{ route('taquilla.historialModificaciones') }}" class="menu-link">
                            <div>Historial anulaciones</div>
                        </a>
                    </li>
                @endif
                @if ($rolActivo->hasPermissionTo('pdp.mis_cajas'))
                    <li class="menu-item {{ request()->routeIs('taquilla.mis-cajas') ? 'active' : '' }}">
                        <a href="{{ route('taquilla.mis-cajas') }}" class="menu-link">
                            <div>Mis cajas</div>
                        </a>
                    </li>
                @endif



            </ul>
        </li>
    @endif

    @if ($rolActivo->hasPermissionTo('finanzas.item_finanzas'))
        <li
            class="menu-item {{ request()->routeIs('finanzas.*') || request()->routeIs('finanzas.*') ? 'active open' : '' }}">
            <a href="" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-cash-banknote"></i>
                <div>Finanzas </div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('finanzas.ingreso') ? 'active' : '' }}">
                    <a href="{{ route('finanzas.ingreso') }}" class="menu-link">
                        <div>Crear ingreso</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('finanzas.gestionarIngresos') ? 'active' : '' }}">
                    <a href="{{ route('finanzas.gestionarIngresos') }}" class="menu-link">
                        <div>Gestionar ingreso</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('finanzas.egreso') ? 'active' : '' }}">
                    <a href="{{ route('finanzas.egreso') }}" class="menu-link">
                        <div>Crear egreso</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('finanzas.gestionarEgresos') ? 'active' : '' }}">
                    <a href="{{ route('finanzas.gestionarEgresos') }}" class="menu-link">
                        <div>Gestionar egreso</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('finanzas.estadisticas') ? 'active' : '' }}">
                    <a href="{{ route('finanzas.estadisticas') }}" class="menu-link">
                        <div>Estadisticas</div>
                    </a>
                </li>
            </ul>

        </li>
    @endif

    @if ($rolActivo->hasPermissionTo('temas.item_temas'))
        <li class="menu-item">
            <a href="" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-blockquote"></i>
                <div>Temas </div>
            </a>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('temas.item_listado_temas'))
                    <li class="menu-item">
                        <a href="{{ route('tema.lista') }}" class="menu-link">
                            <div>Listado</div>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif

    @if ($rolActivo->hasPermissionTo('versiculos.item_versiculos'))
        <li class="menu-item {{ request()->routeIs('versiculos.*') ? 'active open' : '' }}">
            <a href="" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-bible"></i>
                <div>Versículos </div>
            </a>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('versiculos.subitem_gestionar_versiculos'))
                    <li class="menu-item {{ request()->routeIs('versiculos.index') ? 'active' : '' }}">
                        <a href="{{ route('versiculos.index') }}" class="menu-link">
                            <div>Gestionar versículos</div>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif

    @if($rolActivo->hasPermissionTo('posts.item_publicaciones'))
        <li class="menu-item {{ request()->routeIs('posts.*') ? 'active open' : '' }}">
            <a href="" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-cards"></i>
                <div>Publicaciones </div>
            </a>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('posts.subitem_gestionar_publicaciones'))
                    <li class="menu-item {{ request()->routeIs('posts.gestionar') ? 'active' : '' }}">
                        <a href="{{ route('posts.gestionar') }}" class="menu-link">
                            <div>Gestionar publicaciones</div>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif

    @if ($rolActivo->hasPermissionTo('iglesia.item_iglesia'))
        <li class="menu-item">
            <a href="" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-building-church"></i>
                <div>Iglesia </div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item">
                    @php
                        $iglesia = Iglesia::first();
                    @endphp
                    <a href="{{ route('iglesia.perfil', $iglesia) }}" class="menu-link">
                        <div>Configuración</div>
                    </a>
                </li>
            </ul>

        </li>
    @endif

    @if ($rolActivo->hasPermissionTo('informes.item_informes'))
        <li class="menu-item">
            <a href="{{ route('informe.lista') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-report"></i>
                <div>Informes </div>
            </a>
        </li>
    @endif

    @if ($rolActivo->hasPermissionTo('configuraciones.item_configuraciones'))
        <li
            class="menu-item
      {{ request()->routeIs('configuracion.*') ||
      request()->routeIs('formularioUsuario.*') ||
      request()->routeIs('gestionar-tipos-de-grupos.*') ||
      request()->routeIs('gestionar-pasos-de-crecimiento.*')
          ? 'active open'
          : '' }}">

            <a href="" class="menu-link menu-toggle ">
                <i class="menu-icon tf-icons ti ti-settings"></i>
                <div>Configuración </div>
            </a>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_general'))
                    <li
                        class="menu-item {{ request()->routeIs('configuracion-general.configuracionGeneral') ? 'active' : '' }}">
                        <a href="{{ route('configuracion-general.configuracionGeneral') }}" class="menu-link">
                            <div>General</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_roles'))
                    <li class="menu-item {{ request()->routeIs('configuracion.lista') ? 'active' : '' }}">
                        <a href="{{ route('configuracion.gestionar-roles') }}" class="menu-link">
                            <div>Roles</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_zonas'))
                    <li class="menu-item {{ request()->routeIs('configuracion.gestionar-zonas') ? 'active' : '' }}">
                        <a href="{{ route('configuracion.gestionar-zonas') }}" class="menu-link">
                            <div>Zonas</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_plantilla'))
                    <li class="menu-item {{ request()->routeIs('theme-setting.index') ? 'active' : '' }}">
                        <a href="{{ route('theme-setting.index') }}" class="menu-link">
                            <div>Plantilla</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_pasos_de_crecimiento'))
                    <li
                        class="menu-item {{ request()->routeIs('gestionar-pasos-de-crecimiento.pasosDeCrecimiento') ? 'active' : '' }}">
                        <a href="{{ route('gestionar-pasos-de-crecimiento.pasosDeCrecimiento') }}"
                            class="menu-link">
                            <div>Pasos de crecimiento</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_tipos_de_grupos'))
                    <li
                        class="menu-item {{ request()->routeIs('gestionar-tipos-de-grupos.listar') ? 'active' : '' }}">
                        <a href="{{ route('gestionar-tipos-de-grupos.listar') }}" class="menu-link">
                            <div>Tipos de grupos</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_tipo_de_usuarios'))
                    <li class="menu-item {{ request()->routeIs('tipo-usuario.listar') ? 'active' : '' }}">
                        <a href="{{ route('tipo-usuario.listar') }}" class="menu-link">
                            <div>Tipos de usuarios</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_tipo_de_usuarios'))
                    <li
                        class="menu-item {{ request()->routeIs('filtros-consolidacion.listarFiltrosConsolidacion') ? 'active' : '' }}">
                        <a href="{{ route('filtros-consolidacion.listarFiltrosConsolidacion') }}" class="menu-link">
                            <div>Filtro de consolidación</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_tarea_consolidacion'))
                    <li
                        class="menu-item {{ request()->routeIs('tareas-consolidacion.listarTareasConsolidacion') ? 'active' : '' }}">
                        <a href="{{ route('tareas-consolidacion.listarTareasConsolidacion') }}" class="menu-link">
                            <div>Tarea de consolidación</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_rangos_de_edad'))
                    <li class="menu-item {{ request()->routeIs('rangos-edad.listar') ? 'active' : '' }}">
                        <a href="{{ route('rangos-edad.listar') }}" class="menu-link">
                            <div>Rangos de edad</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_tipos_de_ofrendas'))
                    <li class="menu-item {{ request()->routeIs('tipo-ofrenda.listar') ? 'active' : '' }}">
                        <a href="{{ route('tipo-ofrenda.listar') }}" class="menu-link">
                            <div>Tipos de ofrendas</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_lista_de_reproduccion'))
                    <li
                        class="menu-item {{ request()->routeIs('configuracion.gestionar-lista-reproduccion') ? 'active' : '' }}">
                        <a href="{{ route('configuracion.gestionar-lista-reproduccion') }}" class="menu-link">
                            <div>Lista de reproducción</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_formulario_usuarios'))
                    <li class="menu-item {{ request()->routeIs('formularioUsuario.*') ? 'open' : '' }}"
                        style="">
                        <a href="javascript:void(0)" class="menu-link menu-toggle">
                            <div>Formularios usuarios</div>
                        </a>
                        <ul class="menu-sub">
                            @if ($rolActivo->hasPermissionTo('configuraciones.subitem_gestionar_formulario_usuarios'))
                                <li
                                    class="menu-item {{ request()->routeIs('formularioUsuario.lista') ? 'active' : '' }}">
                                    <a href="{{ route('formularioUsuario.lista') }}" class="menu-link">
                                        <div>Formularios</div>
                                    </a>
                                </li>
                            @endif
                            @if ($rolActivo->hasPermissionTo('configuraciones.subitem_gestionar_campos_formulario_usuario'))
                                <li
                                    class="menu-item {{ request()->routeIs('formularioUsuario.listaCampos') ? 'active' : '' }}">
                                    <a href="{{ route('formularioUsuario.listaCampos') }}" class="menu-link">
                                        <div>Campos </div>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_banner_general'))
                    <li class="menu-item {{ request()->routeIs('banner-general.listarBanners') ? 'active' : '' }}">
                        <a href="{{ route('banner-general.listarBanners') }}" class="menu-link">
                            <div>Banners generales</div>
                        </a>
                    </li>
                @endif
            </ul>



            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_tipo_pagos'))
                    <li class="menu-item {{ request()->routeIs('tipo-pagos.listarTipoPagos') ? 'active' : '' }}">
                        <a href="{{ route('tipo-pagos.listarTipoPagos') }}" class="menu-link">
                            <div>Tipo pago</div>
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="menu-sub">
                @if ($rolActivo->hasPermissionTo('configuraciones.subitem_gestionar_videos'))
                    <li class="menu-item {{ request()->routeIs('gestion-videos.listarVideos') ? 'active' : '' }}">
                        <a href="{{ route('gestion-videos.listarVideos') }}" class="menu-link">
                            <div>Gestionar videos</div>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif

    <li class="menu-item">
        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="menu-link">
            <i class="menu-icon tf-icons ti ti-logout-2"></i>
            <div>Cerrar sesión </div>
        </a>
    </li>



    @if (1 == 0)
        @foreach ($menuData[0]->menu as $menu)
            {{-- adding active and open class if child is active --}}

            {{-- menu headers --}}
            @if (isset($menu->menuHeader))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ $menu->menuHeader }}</span>
                </li>
            @else
                {{-- active menu method --}}
                @php
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName();

                    if ($currentRouteName === $menu->slug) {
                        $activeClass = 'active';
                    } elseif (isset($menu->submenu)) {
                        if (gettype($menu->slug) === 'array') {
                            foreach ($menu->slug as $slug) {
                                if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
                                    $activeClass = 'active open';
                                }
                            }
                        } else {
                            if (
                                str_contains($currentRouteName, $menu->slug) and
                                strpos($currentRouteName, $menu->slug) === 0
                            ) {
                                $activeClass = 'active open';
                            }
                        }
                    }
                @endphp

                {{-- main menu --}}
                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                        @isset($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @endisset
                        <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                        @isset($menu->badge)
                            <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}
                            </div>
                        @endisset
                    </a>

                    {{-- submenu --}}
                    @isset($menu->submenu)
                        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                    @endisset
                </li>
            @endif
        @endforeach
    @endif
    </ul>

    <ul style="display:none" class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)
            {{-- adding active and open class if child is active --}}

            {{-- menu headers --}}
            @if (isset($menu->menuHeader))
                <li class="menu-header small">
                    <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
                </li>
            @else
                {{-- active menu method --}}
                @php
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName();

                    if ($currentRouteName === $menu->slug) {
                        $activeClass = 'active';
                    } elseif (isset($menu->submenu)) {
                        if (gettype($menu->slug) === 'array') {
                            foreach ($menu->slug as $slug) {
                                if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
                                    $activeClass = 'active open';
                                }
                            }
                        } else {
                            if (
                                str_contains($currentRouteName, $menu->slug) and
                                strpos($currentRouteName, $menu->slug) === 0
                            ) {
                                $activeClass = 'active open';
                            }
                        }
                    }
                @endphp

                {{-- main menu --}}
                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                        @isset($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @endisset
                        <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                        @isset($menu->badge)
                            <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}
                            </div>
                        @endisset
                    </a>

                    {{-- submenu --}}
                    @isset($menu->submenu)
                        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                    @endisset
                </li>
            @endif
        @endforeach
    </ul>

</aside>
