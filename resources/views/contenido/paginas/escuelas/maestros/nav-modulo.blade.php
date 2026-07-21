<style>
    .module-nav-link {
        font-size: 12px !important;
        padding: 0.6rem 0.8rem !important;
        transition: background-color 0.3s ease, color 0.3s ease;
        border-radius: 0.375rem;
        border: 1px solid transparent;
    }

    .module-nav-link.active {
        background-color: var(--bs-primary) !important;
        color: var(--bs-white) !important;
        border-color: var(--bs-primary) !important;
    }

    .module-nav-link:not(.active):hover {
        background-color: var(--bs-gray-200);
    }
</style>

{{-- Barra de Navegación del Módulo --}}
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card mb-0 p-0 border-0 shadow-sm">
            <ul class="nav nav-pills nav-fill justify-content-start flex-column flex-md-row gap-1 px-2 py-1">
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
