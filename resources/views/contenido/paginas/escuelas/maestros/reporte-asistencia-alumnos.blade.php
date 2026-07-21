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

        @include('contenido.paginas.escuelas.maestros.nav-modulo')
        {{-- INICIO CUADRO INFORMATIVO DE REPORTES --}}
        <div class="row mb-3 px-4">
            <div class="col-12">
                <div class="accordion" id="accordionReportes">
                    <div class="accordion-item shadow-sm border-0">
                        <h2 class="accordion-header" id="headingReportes">
                            <button class="accordion-button collapsed border-top" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseReportes" aria-expanded="false" aria-controls="collapseReportes">
                                <i class="mdi mdi-information-outline me-2 text-primary"></i>
                                <strong>Informe de reportes propios del periodo</strong>
                                @if(count($estadoFechas['omitidos']) > 0)
                                    <span class="badge bg-danger text-white ms-2">{{ count($estadoFechas['omitidos']) }} Omitidos</span>
                                @endif
                            </button>
                        </h2>
                        <div id="collapseReportes" class="accordion-collapse collapse" aria-labelledby="headingReportes"
                            data-bs-parent="#accordionReportes">
                            <div class="accordion-body p-5 border ">
                                <div class="row ">
                                    {{-- Reportes Omitidos --}}
                                    <div class="col-md-4 mb-3">
                                        <h6 class="text-danger"><i class="mdi mdi-alert-circle-outline"></i> Omitidos (Plazo Vencido)</h6>
                                        @if(count($estadoFechas['omitidos']) > 0)
                                            <ul class="list-group list-group-flush border" style="max-height: 200px; overflow-y: auto;">
                                                @foreach($estadoFechas['omitidos'] as $fecha)
                                                    <li class="list-group-item px-2 py-1 text-danger" style="font-size: 0.85rem;">
                                                        {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM YYYY') }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted small">No hay reportes omitidos.</p>
                                        @endif
                                    </div>

                                    {{-- Reportes Realizados --}}
                                    <div class="col-md-4 mb-3">
                                        <h6 class="text-success"><i class="mdi mdi-check-circle-outline"></i> Realizados</h6>
                                        @if(count($estadoFechas['realizados']) > 0)
                                            <ul class="list-group list-group-flush border" style="max-height: 200px; overflow-y: auto;">
                                                @foreach($estadoFechas['realizados'] as $fecha)
                                                    <li class="list-group-item px-2 py-1 text-success" style="font-size: 0.85rem;">
                                                        {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM YYYY') }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted small">No hay reportes realizados aún.</p>
                                        @endif
                                    </div>

                                    {{-- Reportes Futuros --}}
                                    <div class="col-md-4 mb-3">
                                        <h6 class="text-info"><i class="mdi mdi-calendar-clock"></i> Próximos/Pendientes</h6>
                                        @if(count($estadoFechas['futuros']) > 0 || count($estadoFechas['pendientes']) > 0)
                                            <ul class="list-group list-group-flush border" style="max-height: 200px; overflow-y: auto;">
                                                @foreach($estadoFechas['pendientes'] as $fecha)
                                                    <li class="list-group-item px-2 py-1 text-warning fw-bold" style="font-size: 0.85rem;">
                                                        {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM YYYY') }} (Pendiente)
                                                    </li>
                                                @endforeach
                                                @foreach($estadoFechas['futuros'] as $fecha)
                                                    <li class="list-group-item px-2 py-1 text-info" style="font-size: 0.85rem;">
                                                        {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM YYYY') }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted small">No hay fechas futuras programadas.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- FIN CUADRO INFORMATIVO --}}

        <div class="row mb-3  ps-4 ">
            {{-- BOTÓN Y MODAL PARA CREAR NUEVO REPORTE DE ASISTENCIA --}}

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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const esSuperAdmin = @json($esSuperAdmin);
                const fechasPermitidas = @json($estadoFechas['fechasPermitidasFlatpickr'] ?? []);

                let flatpickrConfig = {
                    dateFormat: "Y-m-d",
                    defaultDate: "{{ $fechaPorDefectoParaInput }}",
                };

                if (!esSuperAdmin) {
                    if (fechasPermitidas.length > 0) {
                        flatpickrConfig.enable = fechasPermitidas;
                    } else {
                        // Si no hay fechas permitidas, bloquear todo.
                        // Configuramos minDate mayor a maxDate para bloquear la selección
                        flatpickrConfig.minDate = "today";
                        flatpickrConfig.maxDate = "2000-01-01";
                    }
                }

                flatpickr("#fecha_clase_reportada", flatpickrConfig);
            });
        </script>
    @endpush

@endsection
