{{-- Instaurar el módulo de escuelas como activo para la plantilla maestra --}}
@section('isEscuelasModule', true)

@extends('layouts.layoutMaster') {{-- O tu layout principal --}}

@section('title', 'Reporte de asistencia: ' . $nombreMateria)

<!-- Page -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection


@section('content')
    @include('layouts.status-msn')

    <div class="container-fluid">
        {{-- Título de la página e información de la clase --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1  fw-semibold text-primary">Gestion de reporte: <span class="text-black fw-normal">
                        {{ $nombreMateria }} </span> </h4>
                <p class="mb-0 text-black">{{ $informacionDeLaClase }}</p>
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
                                class="nav-link module-nav-link p-3 waves-effect waves-light  "> {{-- Marcado como activo --}}
                                <i class="mdi mdi-view-dashboard-outline me-1"></i> Dashboard general
                            </a>
                        </li>
                        @endif

                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_detallada'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.calificacionMultiple', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light ">
                                <i class="mdi mdi-table-edit me-1"></i> Calificación detallada
                            </a>
                        </li>
                        @endif
                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_reportes_asistencia'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.reporteAsistencia', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light active">
                                <i class="mdi mdi-calendar-check-outline me-1"></i> Reportes de asistencia
                            </a>
                        </li>
                        @endif
                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_grilla'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.calificacionGrilla', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light">
                                <i class="mdi mdi-grid me-1"></i> Calificación Grilla
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
        <div class="row mb-3  ps-4 ">
            {{-- BOTÓN Y MODAL PARA CREAR NUEVO REPORTE DE ASISTENCIA (Esto permanece como lo tenías) --}}

            <button type="button" class="col-md-3 btn btn-primary waves-effect waves-light rounded-pill"
                data-bs-toggle="modal" data-bs-target="#modalCrearNuevoReporteAsistencia"
                @if ($botonNuevoReporteHabilitado == false) disabled @endif>
                <i class="ti ti-plus"></i> Nuevo reporte
            </button>

        </div>
        {{-- Aquí se incluye el componente Livewire --}}
        @livewire('maestros.reporte-asistencia-alumnos', [
            'horarioAsignado' => $horarioAsignado,
            'maestro' => $maestro,
        ])

    </div>


    <div class="modal fade" id="modalCrearNuevoReporteAsistencia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Reporte de Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST"
                    action="{{ route('maestros.guardarNuevoReporteAsistenciaClase', ['maestro' => $maestro->id, 'horarioAsignado' => $horarioAsignado->id]) }}">
                    @csrf
                    <div class="modal-body">
                        {{-- Dentro del div con id="modalCrearNuevoReporteAsistencia" --}}
                        <div class="mb-3">
                            <label for="fecha_clase_reportada" class="form-label">Fecha de la clase</label>
                            <input type="text" {{-- Usar type="text" para que Flatpickr tenga control total --}}
                                class="form-control @error('fecha_clase_reportada', 'formCrearReporte') is-invalid @enderror"
                                id="fecha_clase_reportada" name="fecha_clase_reportada"
                                value="{{ $fechaPorDefectoParaInput }}" {{-- Valor por defecto desde el controlador --}}
                                {{ $inputFechaEsSoloLectura ? 'readonly' : '' }} {{-- Hacerlo readonly si es necesario --}}>
                            @error('fecha_clase_reportada', 'formCrearReporte')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="observaciones_generales" class="form-label">Observaciones generales</label>
                            <textarea class="form-control @error('observaciones_generales', 'formCrearReporte') is-invalid @enderror"
                                id="observaciones_generales" name="observaciones_generales" rows="3">{{ old('observaciones_generales') }}</textarea>
                            @error('observaciones_generales', 'formCrearReporte')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary rounded-pill"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">Crear reporte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script para manejar errores de validación del modal de creación y mantenerlo abierto si hay error --}}
    @if ($errors->formCrearReporte->any())
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var myModal = new bootstrap.Modal(document.getElementById('modalCrearNuevoReporteAsistencia'));
                    myModal.show();
                });
            </script>
        @endpush
    @endif

    {{-- Script para mostrar notificaciones (ej. con Toastr o SweetAlert) --}}
    @push('scripts')
        <script></script>
    @endpush

@endsection
