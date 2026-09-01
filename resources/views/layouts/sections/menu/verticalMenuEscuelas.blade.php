@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Auth;
    use App\Models\User;
    use App\Models\Configuracion;
    $maestro = auth()->user()->first();
    $user = auth()->user();
    $rolActivo = auth()->user()->roles()->where('activo', true)->first();
    $configuracion = Configuracion::find(1);
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme-escuelas">
      <div style="padding-left: 20px !important;" class="app-brand mt-5 demo p-0 mb-3">
            <a href="{{ url('/') }}" class="app-brand-link">
                @include('_partials.macros', [
                    'width' => '120px'
                ])
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle pe-5"></i>
                <i class="ti ti-x d-block d-xl-none ti-sm align-middle pe-2"></i>
            </a>
        </div>


    <div class="menu-inner-shadow"></div>


    <ul class="menu-inner py-1">
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Menú escuelas</span>
        </li>
        <li class="menu-item">
            <a href="{{ route('escuelas.dashboard') }}" class="menu-link active">
                <i class="menu-icon tf-icons ti ti-home"></i>
                <div>Dashboard escuelas</div>
            </a>
        </li>



        @if ($rolActivo->hasAnyPermission([
            'escuelas.subitem_lista_escuelas',
            'escuelas.subitem_nueva_escuela',
            'escuelas.opcion_actualizar_escuela',
            'escuelas.opcion_eliminar_escuela'
        ]))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-building-skyscraper"></i>
                    <div>Escuelas </div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('escuelas.gestionarEscuelas') }}" class="menu-link">
                            <div>Gestionar</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if ($rolActivo->hasAnyPermission(['escuelas.item_aula', 'escuelas.gestionar_aulas']))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-window"></i>
                    <div>Aulas </div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('aulas.gestionar') }}" class="menu-link">
                            <div>Gestionar</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if ($rolActivo->hasAnyPermission([
            'escuelas.item_periodos',
            'escuelas.subitem_lista_periodos',
            'escuelas.opcion_modificar_periodo',
            'escuelas.opcion_finalizar_periodo'
        ]))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-calendar-cog"></i>
                    <div>Periodos </div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('periodo.gestionar') }}" class="menu-link">
                            <div>Gestionar</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if ($rolActivo->hasAnyPermission([
            'escuelas.item_matriculas',
            'escuelas.subitem_gestionar_matriculas',
            'escuelas.subitem_gestionar_traslados',
            'escuelas.subitem_gestionar_solicitudes_traslado',
            'escuelas.subitem_gestionar_mis_solicitudes_traslado',
            'escuelas.subitem_historial_matriculas',
            'escuelas.opcion_eliminar_matricula'
        ]))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-receipt"></i>
                    <div> Matriculas </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('escuelas.subitem_gestionar_matriculas'))
                        <li class="menu-item">
                            <a href="{{ route('matriculas.gestionar', $user) }}" class="menu-link">
                                <div>Nueva</div>
                            </a>
                        </li>
                    @endif
                    @if ($rolActivo->hasPermissionTo('escuelas.subitem_gestionar_traslados'))
                        <li class="menu-item">
                            <a href="{{ route('matriculas.gestionarTraslados', $user) }}" class="menu-link">
                                <div>Traslados</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('escuelas.subitem_gestionar_solicitudes_traslado'))
                        <li class="menu-item">
                            <a href="{{ route('matriculas.solicitudesTraslado', $user) }}" class="menu-link">
                                <div>Solicitudes de traslados</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasPermissionTo('escuelas.subitem_gestionar_mis_solicitudes_traslado'))
                        <li class="menu-item">
                            <a href="{{ route('matriculas.solicitarTraslado', $user) }}" class="menu-link">
                                <div>Solicitar traslado</div>
                            </a>
                        </li>
                    @endif

                    @if ($rolActivo->hasAnyPermission(['escuelas.subitem_historial_matriculas', 'escuelas.opcion_eliminar_matricula']))
                        <li class="menu-item">
                            <a href="{{ route('matriculas.historialEliminadas', $user) }}" class="menu-link">
                                <div>Eliminadas / Canceladas</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($rolActivo->hasAnyPermission(['escuelas.homologaciones', 'escuelas.subitem_homologaciones']))
            <li class="menu-item {{ Route::is('escuelas.homologaciones*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-list-check"></i>
                    <div>Homologaciones</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('escuelas.homologaciones') ? 'active' : '' }}">
                        <a href="{{ route('escuelas.homologaciones') }}" class="menu-link">
                            <div>Gestionar</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('escuelas.homologaciones.masivas') ? 'active' : '' }}">
                        <a href="{{ route('escuelas.homologaciones.masivas') }}" class="menu-link">
                            <div>Cargue masivo</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if ($rolActivo->hasAnyPermission([
            'escuelas.calificaciones',
            'escuelas.todas_las_calificaciones',
            'escuelas.subitem_gestionar_calificaciones',
            'escuelas.subitem_mis_calificaciones'
        ]))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-check"></i>
                    <div> Calificaciones </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasAnyPermission(['escuelas.subitem_gestionar_calificaciones', 'escuelas.todas_las_calificaciones']))
                        <li class="menu-item">
                            <a href="{{ route('escuelas.historialCalificaciones') }}" class="menu-link">
                                <div>Consultar</div>
                            </a>
                        </li>
                    @endif
                    @if ($rolActivo->hasPermissionTo('escuelas.subitem_mis_calificaciones'))
                        <li class="menu-item">
                            <a href="{{ route('escuelas.alumno.historial') }}" class="menu-link">
                                <div>Mi historial</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($rolActivo->hasAnyPermission(['escuelas.item_informes_escuelas', 'escuelas.subitem_gestionar_asistencias']))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class=" menu-icon ti ti-file-analytics"></i>
                    <div> Informes</div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('escuelas.subitem_gestionar_asistencias'))
                        <li class="menu-item">
                            <a href="{{ route('reporteEscuela.vistaFiltros') }}" class="menu-link">
                                <div>Asistencias</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($rolActivo->hasAnyPermission([
            'escuelas.subitem_recursos_generales',
            'escuelas.gestionar_recursos_generales',
            'escuelas.mis_recursos_generales'
        ]))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class=" menu-icon ti ti-file-zip"></i>
                    <div> Recursos Generales</div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('escuelas.gestionar_recursos_generales'))
                        <li class="menu-item">
                            <a href="{{ route('escuela.recursos-generales', $user) }}" class="menu-link">
                                <div>Gestionar</div>
                            </a>
                        </li>
                    @endif
                    @if ($rolActivo->hasPermissionTo('escuelas.mis_recursos_generales'))
                        <li class="menu-item">
                            <a href="{{ route('escuela.mis-recursos', $user) }}" class="menu-link">
                                <div>Mis recursos</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($rolActivo->hasAnyPermission([
            'escuelas.item_maestros',
            'escuelas.opcion_gestionar_maestro',
            'escuelas.subitem_lista_maestros',
            'escuelas.es_maestro'
        ]))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-user-screen"></i>
                    <div> Maestros </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasAnyPermission(['escuelas.opcion_gestionar_maestro', 'escuelas.subitem_lista_maestros']))
                        <li class="menu-item">
                            <a href="{{ route('maestros.gestionar') }}" class="menu-link">
                                <div>Gestionar</div>
                            </a>
                        </li>
                    @endif
                    @if ($rolActivo->hasAnyPermission(['escuelas.es_maestro', 'escuelas.item_maestros']))
                        <li class="menu-item">
                            <a href="{{ route('maestros.misHorarios', $user) }}" class="menu-link">
                                <div>Mis horarios</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($rolActivo->hasAnyPermission(['escuelas.item_banners', 'escuelas.subitem_gestionar_banners']))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class=" menu-icon ti ti-photo-scan"></i>
                    <div> Banners</div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('escuelas.subitem_gestionar_banners'))
                        <li class="menu-item">
                            <a href="{{ route('banner-escuela.gestionar', $user) }}" class="menu-link">
                                <div>Gestionar</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        <li class="menu-item">
            <a href="{{ url('/') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-arrow-left"></i>
                <div>Menú principal</div>
            </a>
        </li>

    </ul>
</aside>
