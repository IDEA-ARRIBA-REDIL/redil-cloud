{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)
{{-- resources/views/maestros/horarios_asignados.blade.php --}}
@extends('layouts.layoutMaster')
@section('title', 'Gestionar Items - ' . $nombreMateria)

@section('vendor-style')


@vite(['resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])

@endsection

@section('page-style')
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
@endsection

@section('vendor-script')
@vite(['resources/js/app.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])
<script>
   $(".fecha-picker").flatpickr({
            dateFormat: "Y-m-d",
            disableMobile: true
        });
</script>
@endsection




@section('content')
<div class="container-fluid">
    {{-- Encabezado (sin cambios) --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-semibold text-primary">
                        Gestionar Items: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
                    </h4>
                    <p class="mb-0 text-black"><small>{{ $infoClase }} </small></p>
                </div>
            </div>
        </div>
    </div>

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
                    @if(isset($rolActivo))
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

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Gestión de Items de Evaluación</h5>
                        <small class="text-muted">{{ $nombreMateria }} - {{ $infoClase }} </small><br>
                        <small class="text-muted">ID:{{ $horarioAsignado->id}}</small>
                    </div>
                     <a href="{{ route('maestros.dashboardClase', [$maestro->id, $horarioAsignado->id]) }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Volver al Dashboard
                    </a>
                </div>
                <div class="card-body">
                    @livewire('escuelas.gestion-items-corte-materia-periodo', ['horarioAsignado' => $horarioAsignado])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
