@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Auth;
    use App\Models\User;
    $maestro = auth()->user()->first();
    $user = auth()->user();
    $rolActivo = auth()->user()->roles()->where('activo', true)->first();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme-escuelas">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo p-0">
                @include('_partials.macros', [
                    'height' => '40px',
                    'width' => '40px',
                    'fill' => '#3772e4',
                ])
            </span>
            <span style='color:{{ config('variables.templateNameColor') }} !important'
                class="app-brand-text demo menu-text fw-bold fs-4 pt-3">{{ config('variables.templateName') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
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



        @if ($rolActivo->hasPermissionTo('escuelas.subitem_lista_escuelas'))
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
                    <li class="menu-item">
                        <a href="{{ isset($escuela) ? route('niveles-escuelas.crear', $escuela) : '#' }}"
                            class="menu-link">
                            <div>Grados Escuela</div>
                        </a>
                    </li>
                </ul>

            </li>
            </li>
        @endif



        @if ($rolActivo->hasPermissionTo('escuelas.item_aula'))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-window"></i>
                    <div>Aulas </div>
                </a>

                @if ($rolActivo->hasPermissionTo('escuelas.gestionar_aulas'))
                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a href="{{ route('aulas.gestionar') }}" class="menu-link">
                                <div>Gestionar</div>
                            </a>
                        </li>

                    </ul>
                @endif

            </li>
        @endif





        @if ($rolActivo->hasPermissionTo('escuelas.item_periodos'))
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

        @if ($rolActivo->hasPermissionTo('escuelas.item_matriculas'))
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



                </ul>

            </li>
        @endif
        @if ($rolActivo->hasPermissionTo('escuelas.homologaciones'))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class=" menu-icon ti ti-list-check"></i>
                    <div> Homologaciones </div>
                </a>

                <ul class="menu-sub">

                    <li class="menu-item">
                        <a href="{{ route('escuelas.homologaciones') }}" class="menu-link">
                            <div>Gestionar</div>
                        </a>
                    </li>

                </ul>
            </li>
        @endif


        @if ($rolActivo->hasPermissionTo('escuelas.calificaciones'))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-check"></i>
                    <div> Calificaciones </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('escuelas.subitem_gestionar_calificaciones'))
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
        @if ($rolActivo->hasPermissionTo('escuelas.item_informes_escuelas'))
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


        @if ($rolActivo->hasPermissionTo('escuelas.subitem_recursos_generales'))
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



        @if ($rolActivo->hasPermissionTo('escuelas.item_maestros'))
            <li class="menu-item">
                <a href="" class="menu-link menu-toggle">
                    <i class="menu-icon ti ti-user-screen"></i>
                    <div> Maestros </div>
                </a>

                <ul class="menu-sub">
                    @if ($rolActivo->hasPermissionTo('escuelas.opcion_gestionar_maestro'))
                        <li class="menu-item">
                            <a href="{{ route('maestros.gestionar') }}" class="menu-link">
                                <div>Gestionar</div>
                            </a>
                        </li>
                    @endif
                    <li class="menu-item">
                        <a href="{{ route('maestros.misHorarios', $user) }}" class="menu-link">
                            <div>Mis horarios</div>
                        </a>
                    </li>



                </ul>

            </li>
        @endif
        @if ($rolActivo->hasPermissionTo('escuelas.item_banners'))
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
