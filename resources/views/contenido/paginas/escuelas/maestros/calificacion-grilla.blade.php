@php
    use App\Models\Sede;
    use App\Models\User;
@endphp

@section('isEscuelasModule', true)

@extends('layouts.layoutMaster')

@section('title', 'Calificaciones Grilla')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('livewire:navigated', () => {
             var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

        // Listener para notificación de éxito guardado
        document.addEventListener('notaGuardada', event => {
             Swal.fire({
                 position: 'top-end',
                 icon: 'success',
                 title: 'Nota guardada',
                 showConfirmButton: false,
                 timer: 1000,
                 toast: true,
                 timerProgressBar: true
             });
        });
    </script>
@endsection

@section('content')
    @include('layouts.status-msn')

    {{-- Encabezado --}}
    <div class="row mb-3">
        <div class="col-12 mb-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-semibold text-primary">
                        Calificación Grilla: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
                    </h4>
                    <p class="mb-0 text-black"><small>{{ $infoClase }}</small></p>
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
                                class="nav-link module-nav-link p-3 waves-effect waves-light">
                                <i class="mdi mdi-view-dashboard-outline me-1"></i> Dashboard general
                            </a>
                        </li>
                        @endif

                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_detallada'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.calificacionMultiple', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light">
                                <i class="mdi mdi-table-edit me-1"></i> Calificación detallada
                            </a>
                        </li>
                        @endif

                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_grilla'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.calificacionGrilla', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light active">
                                <i class="mdi mdi-grid me-1"></i> Calificación Grilla
                            </a>
                        </li>
                        @endif

                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_reportes_asistencia'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.reporteAsistencia', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light">
                                <i class="mdi mdi-calendar-check-outline me-1"></i> Reportes de asistencia
                            </a>
                        </li>
                        @endif
                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_recursos_alumnos'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.recursosAlumnos', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}" class="nav-link module-nav-link p-3 waves-effect waves-light ">
                                <i class="mdi mdi-folder-multiple-outline me-1"></i> Recursos alumnos
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Contenido Principal: Grilla Livewire --}}
        <div class="row">
            <div class="col-12">
                @livewire('Maestros.CalificacionGrillaAlumnos', ['horarioAsignado' => $horarioAsignado])
            </div>
        </div>
    @endsection
