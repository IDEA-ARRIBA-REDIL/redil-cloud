{{-- Instaurar el módulo de escuelas como activo para la plantilla maestra --}}
@section('isEscuelasModule', true)

@extends('layouts.layoutMaster') {{-- O tu layout principal --}}

@section('title', 'Reporte de asistencia: ' . $nombreMateria)

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
    <style>
        .report-period-accordion .accordion-item {
            border: 1px solid var(--bs-border-color) !important;
            border-radius: 0.85rem !important;
            overflow: hidden;
            box-shadow: 0 0.15rem 0.6rem rgba(15, 23, 42, 0.04);
            background-color: var(--bs-card-bg, #fff);
        }

        .report-period-accordion .accordion-button {
            padding: 1rem 1.25rem;
            background-color: var(--bs-card-bg, #fff);
            box-shadow: none !important;
        }

        .report-period-accordion .accordion-button:not(.collapsed) {
            background-color: rgba(var(--bs-primary-rgb), 0.04) !important;
            color: var(--bs-heading-color, #2f2b3d) !important;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .report-header-icon {
            width: 2.15rem;
            height: 2.15rem;
            border-radius: 0.6rem;
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Pills de estado con contraste garantizado frente a _custom-variables.css */
        .report-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            padding: 0.28rem 0.68rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
        }

        .report-pill-sm {
            padding: 0.18rem 0.52rem;
            font-size: 0.68rem;
        }

        .report-pill-danger-solid {
            background-color: #ff4c51;
            color: #fff !important;
        }

        .report-pill-danger {
            background-color: rgba(255, 76, 81, 0.14);
            color: #c82333 !important;
        }

        .report-pill-success {
            background-color: rgba(40, 199, 111, 0.14);
            color: #167943 !important;
        }

        .report-pill-warning {
            background-color: rgba(255, 159, 67, 0.18);
            color: #9a5700 !important;
        }

        .report-pill-info {
            background-color: rgba(0, 186, 209, 0.14);
            color: #007b8a !important;
        }

        .report-pill-neutral {
            background-color: rgba(108, 117, 125, 0.12);
            color: #495057 !important;
        }

        /* Tarjetas de estado del periodo */
        .period-status-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.75rem;
            overflow: hidden;
            background-color: var(--bs-card-bg, #fff);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .period-status-card.is-danger {
            border-top: 3px solid #ff4c51;
        }

        .period-status-card.is-success {
            border-top: 3px solid #28c76f;
        }

        .period-status-card.is-info {
            border-top: 3px solid #00bad1;
        }

        .period-status-head {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--bs-border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            background-color: var(--bs-gray-100);
        }

        .period-status-title {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin: 0;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .period-dates-list {
            max-height: 220px;
            overflow-y: auto;
            padding: 0.5rem;
            margin: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            scrollbar-width: thin;
        }

        .period-date-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid var(--bs-border-color);
            background-color: var(--bs-card-bg, #fff);
            font-size: 0.82rem;
            color: #2f2b3d;
        }

        .period-date-item.is-omitted {
            background-color: rgba(255, 76, 81, 0.04);
            border-color: rgba(255, 76, 81, 0.22);
        }

        .period-date-item.is-done {
            background-color: rgba(40, 199, 111, 0.04);
            border-color: rgba(40, 199, 111, 0.22);
        }

        .period-date-item.is-pending {
            background-color: rgba(255, 159, 67, 0.08);
            border-color: rgba(255, 159, 67, 0.35);
            border-left: 3px solid #ff9f43;
        }

        .period-date-item.is-future {
            background-color: rgba(0, 186, 209, 0.04);
            border-color: rgba(0, 186, 209, 0.22);
        }

        .period-empty-state {
            padding: 1.5rem 1rem;
            text-align: center;
            color: var(--bs-secondary-color, #6c757d);
            font-size: 0.82rem;
            margin: auto 0;
        }

        .report-limit-alert {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.9rem;
            border-radius: 0.65rem;
            background-color: rgba(255, 159, 67, 0.16);
            border: 1px solid rgba(255, 159, 67, 0.35);
            color: #8a4f00 !important;
            font-size: 0.82rem;
            font-weight: 500;
        }
    </style>
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

        @php
            $cantidadOmitidos = count($estadoFechas['omitidos'] ?? []);
            $cantidadRealizados = count($estadoFechas['realizados'] ?? []);
            $cantidadPendientes = count($estadoFechas['pendientes'] ?? []);
            $cantidadFuturos = count($estadoFechas['futuros'] ?? []);
            $cantidadProximosTotal = $cantidadPendientes + $cantidadFuturos;
        @endphp

        {{-- INICIO CUADRO INFORMATIVO DE REPORTES --}}
        <div class="mb-4">
            <div class="accordion report-period-accordion" id="accordionReportes">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingReportes">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseReportes" aria-expanded="false" aria-controls="collapseReportes">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100 me-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="report-header-icon">
                                        <i class="ti ti-calendar-stats"></i>
                                    </span>
                                    <div>
                                        <span class="fw-bold text-dark d-block">Informe de reportes propios del periodo</span>
                                        <small class="text-muted fw-normal">Resumen de fechas vencidas, realizadas y programadas</small>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    @if ($cantidadOmitidos > 0)
                                        <span class="report-pill report-pill-danger-solid">
                                            <i class="ti ti-alert-circle"></i>
                                            {{ $cantidadOmitidos }} {{ $cantidadOmitidos === 1 ? 'Omitido' : 'Omitidos' }}
                                        </span>
                                    @endif
                                    <span class="report-pill report-pill-success">
                                        <i class="ti ti-circle-check"></i>
                                        {{ $cantidadRealizados }} {{ $cantidadRealizados === 1 ? 'Realizado' : 'Realizados' }}
                                    </span>
                                    @if ($cantidadProximosTotal > 0)
                                        <span class="report-pill {{ $cantidadPendientes > 0 ? 'report-pill-warning' : 'report-pill-info' }}">
                                            <i class="ti ti-clock"></i>
                                            {{ $cantidadProximosTotal }} {{ $cantidadProximosTotal === 1 ? 'Pendiente / Próximo' : 'Pendientes / Próximos' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapseReportes" class="accordion-collapse collapse" aria-labelledby="headingReportes"
                        data-bs-parent="#accordionReportes">
                        <div class="accordion-body p-3 p-md-4 bg-lighter">
                            <div class="row g-3">
                                {{-- Reportes Omitidos --}}
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="period-status-card is-danger">
                                        <div class="period-status-head">
                                            <h6 class="period-status-title text-danger">
                                                <i class="ti ti-alert-circle"></i>
                                                <span>Omitidos (Plazo vencido)</span>
                                            </h6>
                                            <span class="report-pill report-pill-sm report-pill-danger">
                                                {{ $cantidadOmitidos }}
                                            </span>
                                        </div>
                                        @if ($cantidadOmitidos > 0)
                                            <ul class="period-dates-list">
                                                @foreach ($estadoFechas['omitidos'] as $fecha)
                                                    <li class="period-date-item is-omitted">
                                                        <span class="d-inline-flex align-items-center gap-2 fw-medium">
                                                            <i class="ti ti-calendar-x text-danger"></i>
                                                            {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM YYYY') }}
                                                        </span>
                                                        <span class="report-pill report-pill-sm report-pill-danger">Vencido</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="period-empty-state">
                                                <i class="ti ti-mood-smile fs-4 d-block mb-1 text-success"></i>
                                                No hay reportes omitidos.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Reportes Realizados --}}
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="period-status-card is-success">
                                        <div class="period-status-head">
                                            <h6 class="period-status-title text-success">
                                                <i class="ti ti-circle-check"></i>
                                                <span>Realizados</span>
                                            </h6>
                                            <span class="report-pill report-pill-sm report-pill-success">
                                                {{ $cantidadRealizados }}
                                            </span>
                                        </div>
                                        @if ($cantidadRealizados > 0)
                                            <ul class="period-dates-list">
                                                @foreach ($estadoFechas['realizados'] as $fecha)
                                                    <li class="period-date-item is-done">
                                                        <span class="d-inline-flex align-items-center gap-2 fw-medium">
                                                            <i class="ti ti-calendar-check text-success"></i>
                                                            {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM YYYY') }}
                                                        </span>
                                                        <span class="report-pill report-pill-sm report-pill-success">Reportado</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="period-empty-state">
                                                <i class="ti ti-calendar-off fs-4 d-block mb-1"></i>
                                                No hay reportes realizados aún.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Reportes Próximos / Pendientes --}}
                                <div class="col-lg-4 col-md-12 col-12">
                                    <div class="period-status-card is-info">
                                        <div class="period-status-head">
                                            <h6 class="period-status-title text-info">
                                                <i class="ti ti-calendar-time"></i>
                                                <span>Próximos / Pendientes</span>
                                            </h6>
                                            <span class="report-pill report-pill-sm {{ $cantidadPendientes > 0 ? 'report-pill-warning' : 'report-pill-info' }}">
                                                {{ $cantidadProximosTotal }}
                                            </span>
                                        </div>
                                        @if ($cantidadProximosTotal > 0)
                                            <ul class="period-dates-list">
                                                @foreach ($estadoFechas['pendientes'] as $fecha)
                                                    <li class="period-date-item is-pending">
                                                        <span class="d-inline-flex align-items-center gap-2 fw-semibold">
                                                            <i class="ti ti-clock-exclamation text-warning"></i>
                                                            {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM YYYY') }}
                                                        </span>
                                                        <span class="report-pill report-pill-sm report-pill-warning">Pendiente</span>
                                                    </li>
                                                @endforeach
                                                @foreach ($estadoFechas['futuros'] as $fecha)
                                                    <li class="period-date-item is-future">
                                                        <span class="d-inline-flex align-items-center gap-2 fw-medium">
                                                            <i class="ti ti-calendar-event text-info"></i>
                                                            {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM YYYY') }}
                                                        </span>
                                                        <span class="report-pill report-pill-sm report-pill-info">Próximo</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="period-empty-state">
                                                <i class="ti ti-calendar-event fs-4 d-block mb-1"></i>
                                                No hay fechas futuras programadas.
                                            </div>
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

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            {{-- BOTÓN Y MODAL PARA CREAR NUEVO REPORTE DE ASISTENCIA --}}
            <button type="button" class="btn btn-primary waves-effect waves-light rounded-pill px-4"
                data-bs-toggle="modal" data-bs-target="#modalCrearNuevoReporteAsistencia"
                @if ($botonNuevoReporteHabilitado == false) disabled @endif>
                <i class="ti ti-plus me-1"></i> Nuevo reporte
            </button>

            @if (!empty($limiteTotalAlcanzado) && $limiteTotalAlcanzado)
                <div class="report-limit-alert">
                    <i class="ti ti-info-circle fs-5 flex-shrink-0"></i>
                    <span>
                        Se ha alcanzado el límite total de reportes configurados para esta materia ({{ $materiaAEvaluar->limite_reporte_asistencias }}).
                    </span>
                </div>
            @endif
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
                const fechasRealizadas = @json($estadoFechas['realizados'] ?? []);
                const limiteSemana = @json($materiaAEvaluar ? $materiaAEvaluar->obtenerLimiteReportesSemana() : 1);
                const minDatePeriodo = @json($limiteMinimoFechaPicker);
                const maxDatePeriodo = @json($limiteMaximoFechaPicker);

                let flatpickrConfig = {
                    dateFormat: "Y-m-d",
                    defaultDate: "{{ $fechaPorDefectoParaInput }}",
                };

                if (minDatePeriodo) {
                    flatpickrConfig.minDate = minDatePeriodo;
                }
                if (maxDatePeriodo) {
                    flatpickrConfig.maxDate = maxDatePeriodo;
                }

                if (!esSuperAdmin) {
                    if (fechasPermitidas.length > 0) {
                        flatpickrConfig.enable = fechasPermitidas;
                    } else {
                        // Si no hay fechas permitidas, bloquear todo.
                        flatpickrConfig.minDate = "today";
                        flatpickrConfig.maxDate = "2000-01-01";
                    }
                } else {
                    // Para usuarios con permiso especial: deshabilitar fechas ya reportadas y semanas que ya alcanzaron el límite semanal
                    flatpickrConfig.disable = [
                        function(date) {
                            const year = date.getFullYear();
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const day = String(date.getDate()).padStart(2, '0');
                            const dateStr = `${year}-${month}-${day}`;

                            if (fechasRealizadas.includes(dateStr)) {
                                return true;
                            }

                            const startOfWeek = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay());
                            const endOfWeek = new Date(startOfWeek.getFullYear(), startOfWeek.getMonth(), startOfWeek.getDate() + 6);

                            const startStr = `${startOfWeek.getFullYear()}-${String(startOfWeek.getMonth() + 1).padStart(2, '0')}-${String(startOfWeek.getDate()).padStart(2, '0')}`;
                            const endStr = `${endOfWeek.getFullYear()}-${String(endOfWeek.getMonth() + 1).padStart(2, '0')}-${String(endOfWeek.getDate()).padStart(2, '0')}`;

                            const reportesEnSemana = fechasRealizadas.filter(f => f >= startStr && f <= endStr).length;
                            return reportesEnSemana >= limiteSemana;
                        }
                    ];
                }

                flatpickr("#fecha_clase_reportada", flatpickrConfig);
            });
        </script>
    @endpush

@endsection
