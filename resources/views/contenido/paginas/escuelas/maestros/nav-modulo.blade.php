<style>
    .module-nav-link {
        display: grid;
        min-width: 0;
        min-height: 2.5rem;
        padding: 0.6rem 0.8rem !important;
        place-items: center;
        overflow: hidden;
        font-size: 12px !important;
        text-align: center;
        text-overflow: ellipsis;
        transition: background-color 0.3s ease, color 0.3s ease;
        white-space: nowrap;
        border-radius: 0.375rem;
        border: 1px solid transparent;
    }

    .module-navigation {
        display: grid;
        grid-template-columns: repeat(var(--module-navigation-columns, 1), minmax(0, 1fr));
        gap: 0.25rem;
        align-items: stretch;
    }

    .module-navigation .nav-item {
        min-width: 0;
    }

    .module-nav-link.active {
        background-color: var(--bs-primary) !important;
        color: var(--bs-white) !important;
        border-color: var(--bs-primary) !important;
    }

    .module-nav-link:not(.active):hover {
        background-color: var(--bs-gray-200);
    }

    @media (max-width: 1199.98px) and (min-width: 992px) {
        .module-nav-link {
            min-height: 2.25rem;
            padding: 0.5rem 0.35rem !important;
            font-size: 11px !important;
        }

        .module-nav-link i {
            display: none;
        }
    }

    @media (max-width: 991.98px) {
        .module-navigation {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

{{-- Barra de Navegación del Módulo --}}
@php
    $moduleNavigationCount = isset($rolActivo)
        ? collect([
            $rolActivo->hasPermissionTo('escuelas.tab_dashboard_general'),
            $rolActivo->hasPermissionTo('escuelas.tab_calificacion_detallada'),
            $rolActivo->hasPermissionTo('escuelas.tab_reportes_asistencia'),
            $rolActivo->hasPermissionTo('escuelas.tab_recursos_alumnos'),
            $rolActivo->hasPermissionTo('escuelas.tab_gestionar_items'),
            $rolActivo->hasPermissionTo('escuelas.tab_calificacion_grilla'),
        ])->filter()->count()
        : 1;
@endphp
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card mb-0 p-0 border-0 shadow-sm">
            <ul class="nav nav-pills module-navigation px-2 py-1"
                style="--module-navigation-columns: {{ max(1, $moduleNavigationCount) }};">
                @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_dashboard_general'))
                <li class="nav-item">
                    <a href="{{ route('maestros.dashboardClase', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                        class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.dashboardClase') ? 'active' : '' }}">
                        <i class="mdi mdi-view-dashboard-outline me-1"></i> Dashboard general
                    </a>
                </li>
                @endif

                @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_detallada'))
                <li class="nav-item">
                    <a href="{{ route('maestros.calificacionMultiple', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                        class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.calificacionMultiple') ? 'active' : '' }}">
                        <i class="mdi mdi-table-edit me-1"></i> Calificación detallada
                    </a>
                </li>
                @endif

                @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_reportes_asistencia'))
                <li class="nav-item">
                    <a href="{{ route('maestros.reporteAsistencia', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                        class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.reporteAsistencia') ? 'active' : '' }}">
                        <i class="mdi mdi-calendar-check-outline me-1"></i> Reportes de asistencia
                    </a>
                </li>
                @endif

                @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_recursos_alumnos'))
                <li class="nav-item">
                    <a href="{{ route('maestros.recursosAlumnos', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                        class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.recursosAlumnos') ? 'active' : '' }}">
                        <i class="mdi mdi-folder-multiple-outline me-1"></i> Recursos alumnos
                    </a>
                </li>
                @endif

                @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_gestionar_items'))
                <li class="nav-item">
                    <a href="{{ route('maestros.gestionarItems', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                        class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.gestionarItems') ? 'active' : '' }}">
                        <i class="mdi mdi-list-box-outline me-1"></i> Gestionar Items
                    </a>
                </li>
                @endif

                @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_grilla'))
                <li class="nav-item">
                    <a href="{{ route('maestros.calificacionGrilla', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                        class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.calificacionGrilla') ? 'active' : '' }}">
                        <i class="mdi mdi-grid me-1"></i> Calificación Grilla
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
