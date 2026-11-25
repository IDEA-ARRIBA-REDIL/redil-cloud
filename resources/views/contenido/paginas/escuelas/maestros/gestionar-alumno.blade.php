{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)
@extends('layouts.layoutMaster')

@section('title', 'Gestionar Estudiante: ' . ($alumno->nombre(3) ?? 'Estudiante'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('page-style')
    <style>
        .student-page-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.85rem;
            box-shadow: 0 0.15rem 0.6rem rgba(15, 23, 42, 0.04);
            overflow: hidden;
            background-color: var(--bs-card-bg, #fff);
        }

        .student-card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--bs-border-color);
            padding: 1rem 1.25rem;
        }

        .section-icon-box {
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

        .student-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            background-color: var(--bs-gray-100);
            border: 1px solid var(--bs-border-color);
            color: #4b5563;
            font-size: 0.8rem;
        }

        /* Perfil del estudiante y avatar circular sin recuadro de sombra */
        .student-profile-banner {
            height: 4rem;
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.14) 0%, rgba(var(--bs-primary-rgb), 0.04) 100%);
            border-bottom: 1px solid rgba(var(--bs-primary-rgb), 0.08);
        }

        .student-avatar-wrap {
            margin-top: -2.75rem;
            margin-bottom: 0.85rem;
            display: flex;
            justify-content: center;
        }

        .student-avatar-circle {
            width: 5.25rem;
            height: 5.25rem;
            border-radius: 50%;
            border: 4px solid var(--bs-card-bg, #fff);
            outline: 2px solid rgba(var(--bs-primary-rgb), 0.2);
            box-shadow: 0 0.45rem 1.15rem rgba(15, 23, 42, 0.14);
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bs-primary);
            color: #fff;
            font-size: 1.65rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            line-height: 1;
            flex-shrink: 0;
        }

        .student-avatar-circle.is-fallback {
            background-color: #6c757d;
        }

        .student-avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Tarjetas KPI internas */
        .kpi-tile {
            background-color: var(--bs-gray-100);
            border: 1px solid var(--bs-border-color);
            border-radius: 0.7rem;
            padding: 0.85rem 0.75rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        .kpi-tile:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.3);
            background-color: rgba(var(--bs-primary-rgb), 0.03);
        }

        .kpi-tile-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--bs-secondary-color, #6c757d);
        }

        /* Lista de detalles del estudiante */
        .student-detail-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px dashed var(--bs-border-color);
        }

        .student-detail-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .student-detail-icon {
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 0.5rem;
            background-color: var(--bs-gray-100);
            color: var(--bs-secondary-color, #6c757d);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        /* Sistema de pills con contraste garantizado (independiente de _custom-variables.css) */
        .ui-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
        }

        .ui-pill-sm {
            padding: 0.2rem 0.55rem;
            font-size: 0.7rem;
        }

        .ui-pill-primary {
            background-color: rgba(var(--bs-primary-rgb), 0.12);
            color: var(--bs-primary) !important;
        }

        .ui-pill-success {
            background-color: rgba(40, 199, 111, 0.14);
            color: #167943 !important;
        }

        .ui-pill-danger {
            background-color: rgba(255, 76, 81, 0.14);
            color: #c82333 !important;
        }

        .ui-pill-warning {
            background-color: rgba(255, 159, 67, 0.18);
            color: #9a5700 !important;
        }

        .ui-pill-info {
            background-color: rgba(0, 186, 209, 0.14);
            color: #007b8a !important;
        }

        .ui-pill-neutral {
            background-color: rgba(108, 117, 125, 0.12);
            color: #495057 !important;
        }

        /* Tablas de asistencia e ítems */
        .student-clean-table thead th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.45px;
            font-weight: 700;
            color: var(--bs-secondary-color, #6c757d);
            background-color: var(--bs-gray-100) !important;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .student-clean-table tbody td {
            padding-top: 0.8rem;
            padding-bottom: 0.8rem;
        }

        /* Tarjetas de cortes de evaluación */
        .corte-eval-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.75rem;
            overflow: hidden;
            background-color: var(--bs-card-bg, #fff);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .corte-eval-card:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.35);
            box-shadow: 0 0.25rem 0.75rem rgba(15, 23, 42, 0.05);
        }

        .corte-eval-header {
            background-color: var(--bs-gray-100);
            border-left: 4px solid var(--bs-primary);
            border-bottom: 1px solid var(--bs-border-color);
            padding: 0.9rem 1.25rem;
        }

        .corte-number-pill {
            width: 2.15rem;
            height: 2.15rem;
            border-radius: 999px;
            background-color: var(--bs-primary);
            color: #fff !important;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .corte-eval-table thead th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.45px;
            font-weight: 700;
            color: var(--bs-secondary-color, #6c757d);
            background-color: rgba(var(--bs-secondary-rgb, 108, 117, 125), 0.04);
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .corte-eval-table tbody td {
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }

        .corte-eval-table tfoot td {
            background-color: rgba(var(--bs-secondary-rgb, 108, 117, 125), 0.04);
            border-top: 1px solid var(--bs-border-color);
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
            font-size: 0.8125rem;
        }

        /* Valoración del maestro */
        .teacher-feedback-box {
            background-color: var(--bs-gray-100);
            border: 1px solid var(--bs-border-color);
            border-left: 4px solid var(--bs-primary);
            border-radius: 0.7rem;
            padding: 1rem 1.25rem;
        }

        /* Acordeón de historial educativo */
        .accordion-school-header .accordion-item {
            border: 1px solid var(--bs-border-color) !important;
            border-radius: 0.75rem !important;
            overflow: hidden;
        }

        .accordion-school-header .accordion-button {
            padding: 1rem 1.25rem;
            background-color: var(--bs-card-bg, #fff);
            box-shadow: none !important;
        }

        .accordion-school-header .accordion-button:not(.collapsed) {
            background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
            color: var(--bs-primary) !important;
            box-shadow: none !important;
        }

        .history-subject-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.65rem;
            background-color: var(--bs-card-bg, #fff);
            transition: border-color 0.2s ease, transform 0.15s ease;
        }

        .history-subject-card:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.35);
            transform: translateY(-1px);
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('content')
    @include('layouts.status-msn')

    @php
        $notaMinima = $notaMinimaAprobatoria ?? 3.0;
        $cortesAgrupados = $cortesEvaluacion ?? collect();
        $porcentajeAsistencia = $totalClasesReportadas > 0
            ? round(($totalAsistenciasCumplidas / $totalClasesReportadas) * 100)
            : 0;
        $asistenciaPillClass = match (true) {
            $totalClasesReportadas === 0 => 'ui-pill-neutral',
            $porcentajeAsistencia >= 80 => 'ui-pill-success',
            $porcentajeAsistencia >= 60 => 'ui-pill-warning',
            default => 'ui-pill-danger',
        };
        $asistenciaBarClass = match (true) {
            $totalClasesReportadas === 0 => 'bg-secondary',
            $porcentajeAsistencia >= 80 => 'bg-success',
            $porcentajeAsistencia >= 60 => 'bg-warning',
            default => 'bg-danger',
        };
    @endphp

    {{-- Encabezado Principal de la Página --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-2 fw-bold text-primary d-flex align-items-center gap-2">
                <i class="ti ti-school"></i>
                <span>Gestionar Estudiante: <span class="text-dark fw-semibold">{{ $alumno->nombre(3) }}</span></span>
            </h4>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="student-meta-chip">
                    <i class="ti ti-book text-primary"></i>
                    <strong>Clase:</strong> {{ $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'N/A' }}
                </span>
                <span class="student-meta-chip">
                    <i class="ti ti-calendar-event text-primary"></i>
                    <strong>Periodo:</strong> {{ $horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'N/A' }}
                </span>
                <span class="student-meta-chip">
                    <i class="ti ti-user text-primary"></i>
                    <strong>Maestro:</strong> {{ $horarioAsignado->nombreMaestroAsignado() }}
                </span>
                <span class="student-meta-chip">
                    <i class="ti ti-clock text-primary"></i>
                    <strong>Horario:</strong> {{ $horarioAsignado->descripcionHorario() }}
                </span>
            </div>
        </div>
        <div>
            <a href="{{ route('maestros.dashboardClase', ['horarioAsignado' => $horarioAsignado, 'maestro' => $maestro]) }}"
                class="btn btn-outline-secondary rounded-pill waves-effect">
                <i class="ti ti-arrow-left me-1"></i> Volver a la clase
            </a>
        </div>
    </div>

    {{-- FILA 1: PERFIL DEL ESTUDIANTE Y REPORTES DE ASISTENCIA --}}
    <div class="row g-4 mb-4">
        {{-- Tarjeta del Perfil del Alumno --}}
        <div class="col-xl-4 col-lg-5 col-12">
            <div class="card student-page-card h-100">
                <div class="student-profile-banner"></div>
                <div class="card-body pt-0 px-4 pb-4 d-flex flex-column justify-content-between">
                    <div>
                        {{-- Avatar Circular Limpio y Nombre --}}
                        <div class="text-center pb-3 mb-3 border-bottom">
                            <div class="student-avatar-wrap">
                                @if (isset($alumno) && ($alumno->foto == 'default-m.png' || $alumno->foto == 'default-f.png'))
                                    <div class="student-avatar-circle">
                                        {{ $alumno->inicialesNombre() }}
                                    </div>
                                @elseif (isset($alumno))
                                    <div class="student-avatar-circle">
                                        <img src="{{ tenant_asset('img/usuarios/foto-usuario/' . $alumno->foto) }}"
                                            alt="{{ $alumno->nombre(3) }}">
                                    </div>
                                @else
                                    <div class="student-avatar-circle is-fallback">?</div>
                                @endif
                            </div>

                            <h5 class="mb-1 fw-bold text-dark">{{ $alumno->nombre(3) }}</h5>
                            <p class="text-muted small mb-0 d-inline-flex align-items-center justify-content-center gap-1 text-break">
                                <i class="ti ti-mail"></i>
                                <span>{{ $alumno->email ?: 'Sin correo registrado' }}</span>
                            </p>
                        </div>

                        {{-- KPIs del Curso Actual --}}
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="kpi-tile text-center">
                                    <span class="kpi-tile-label">Nota Final</span>
                                    @if (isset($estadoAcademicoAlumno) && $estadoAcademicoAlumno->nota_final_numerica !== null)
                                        <span class="fs-4 fw-bold lh-1 {{ $estadoAcademicoAlumno->nota_final_numerica >= $notaMinima ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($estadoAcademicoAlumno->nota_final_numerica, 1) }}
                                        </span>
                                    @else
                                        <span class="ui-pill ui-pill-warning">Sin calificar</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="kpi-tile text-center">
                                    <span class="kpi-tile-label">Estado</span>
                                    @php
                                        $estadoStr = $estadoAcademicoAlumno->estado_aprobacion ?? 'cursando';
                                        $estadoPillClass = match ($estadoStr) {
                                            'aprobado' => 'ui-pill-success',
                                            'no_aprobado' => 'ui-pill-danger',
                                            'retirado_oficialmente' => 'ui-pill-neutral',
                                            default => 'ui-pill-info',
                                        };
                                    @endphp
                                    <span class="ui-pill {{ $estadoPillClass }} text-capitalize">
                                        {{ str_replace('_', ' ', $estadoStr) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Información General Detallada --}}
                        <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            Detalles del estudiante
                        </h6>
                        <div>
                            <div class="student-detail-row">
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <span class="student-detail-icon"><i class="ti ti-phone"></i></span>
                                    <span>Celular</span>
                                </div>
                                <span class="fw-semibold text-dark small text-end">{{ $alumno->celular ?? 'N/A' }}</span>
                            </div>
                            <div class="student-detail-row">
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <span class="student-detail-icon"><i class="ti ti-building-skyscraper"></i></span>
                                    <span>Sede origen</span>
                                </div>
                                <span class="fw-semibold text-dark small text-end">{{ $alumno->sede->nombre ?? 'N/A' }}</span>
                            </div>
                            <div class="student-detail-row">
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <span class="student-detail-icon"><i class="ti ti-user-check"></i></span>
                                    <span>Líder directo</span>
                                </div>
                                <span class="fw-semibold text-dark small text-end" style="max-width: 58%;">
                                    @if ($alumno->encargadosDirectos()->count() > 0)
                                        @foreach ($alumno->encargadosDirectos() as $encargado)
                                            {{ $encargado->nombre }}@if (!$loop->last), @endif
                                        @endforeach
                                    @else
                                        Sin encargados
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reportes de Asistencia Reales --}}
        <div class="col-xl-8 col-lg-7 col-12">
            <div class="card student-page-card h-100 d-flex flex-column">
                <div class="student-card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon-box"><i class="ti ti-calendar-check"></i></span>
                            <div>
                                <h5 class="mb-0 fw-bold text-dark fs-6">Reportes de Asistencia</h5>
                                <small class="text-muted">Historial de sesiones registradas en la clase</small>
                            </div>
                        </div>
                        <span class="ui-pill {{ $asistenciaPillClass }}">
                            <i class="ti ti-user-check"></i>
                            {{ $totalAsistenciasCumplidas }} de {{ $totalClasesReportadas }} asistencias ({{ $porcentajeAsistencia }}%)
                        </span>
                    </div>
                    @if ($totalClasesReportadas > 0)
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar {{ $asistenciaBarClass }}" role="progressbar"
                                style="width: {{ $porcentajeAsistencia }}%;"
                                aria-valuenow="{{ $porcentajeAsistencia }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    @endif
                </div>
                <div class="card-body p-0 flex-grow-1">
                    <div class="table-responsive" style="max-height: 390px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 student-clean-table">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="ps-4">Fecha reporte</th>
                                    <th class="text-center" style="width: 120px;">Asistió</th>
                                    <th class="pe-4">Motivo / Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reportesAsistencia as $detalleReporte)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ti ti-calendar-event text-muted"></i>
                                                <span class="fw-semibold text-dark">
                                                    {{ $detalleReporte->reporteClase ? \Carbon\Carbon::parse($detalleReporte->reporteClase->fecha_clase_reportada)->isoFormat('D [de] MMMM YYYY') : 'N/A' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if ($detalleReporte->asistio)
                                                <span class="ui-pill ui-pill-success">
                                                    <i class="ti ti-check"></i>Sí
                                                </span>
                                            @else
                                                <span class="ui-pill ui-pill-danger">
                                                    <i class="ti ti-x"></i>No
                                                </span>
                                            @endif
                                        </td>
                                        <td class="pe-4">
                                            <small class="text-muted">
                                                {{ $detalleReporte->motivoInasistencia?->nombre ?? ($detalleReporte->observaciones_alumno ?? 'Sin observaciones') }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">
                                            <i class="ti ti-calendar-off fs-2 d-block mb-2"></i>
                                            <span class="d-block fw-semibold text-dark mb-1">Sin reportes registrados</span>
                                            <small>No hay reportes de asistencia registrados para este estudiante en este curso.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILA 2: VALORACIÓN FINAL DEL MAESTRO --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card student-page-card">
                <div class="student-card-header d-flex align-items-center gap-2">
                    <span class="section-icon-box"><i class="ti ti-message-2"></i></span>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark fs-6">Valoración Final del Maestro</h5>
                        <small class="text-muted">Observaciones de cierre registradas para el estudiante</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    @php
                        $observacionCierre = $estadoAcademicoAlumno->observaciones_cierre ?? null;
                    @endphp
                    <div class="teacher-feedback-box">
                        <p class="mb-0 {{ filled($observacionCierre) ? 'text-dark' : 'text-muted fst-italic' }}">
                            {{ filled($observacionCierre) ? $observacionCierre : 'Sin observaciones o valoración final registrada para este curso.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILA 3: ÍTEMS DE EVALUACIÓN DEL CURSO AGRUPADOS POR CORTE --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card student-page-card">
                <div class="student-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon-box"><i class="ti ti-list-check"></i></span>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark fs-6">Ítems de Evaluación del Curso</h5>
                            <small class="text-muted">Actividades y calificaciones separadas por corte</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="ui-pill ui-pill-neutral">
                            {{ $cortesAgrupados->count() }} {{ $cortesAgrupados->count() === 1 ? 'corte' : 'cortes' }}
                        </span>
                        <span class="ui-pill ui-pill-primary">
                            {{ count($itemsEvaluacion) }} {{ count($itemsEvaluacion) === 1 ? 'ítem' : 'ítems' }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    @forelse ($cortesAgrupados as $corte)
                        <div class="corte-eval-card {{ !$loop->last ? 'mb-4' : '' }}">
                            {{-- Cabecera de cada corte --}}
                            <div class="corte-eval-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="corte-number-pill" aria-hidden="true">
                                        {{ $corte['numero'] ?? '—' }}
                                    </span>
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <h6 class="mb-0 fw-bold text-dark">{{ $corte['etiqueta'] }}</h6>
                                            @if ($corte['porcentaje'] !== null)
                                                <span class="ui-pill ui-pill-sm ui-pill-neutral">
                                                    Peso del corte: {{ rtrim(rtrim(number_format($corte['porcentaje'], 2), '0'), '.') }}%
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            {{ $corte['total_items'] }} {{ $corte['total_items'] === 1 ? 'ítem' : 'ítems' }}
                                            <span class="mx-1">•</span>
                                            {{ $corte['items_calificados'] }} de {{ $corte['total_items'] }} calificados
                                        </small>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    @if ($corte['nota_corte'] !== null)
                                        <small class="text-muted fw-medium">Acumulado:</small>
                                        <span class="ui-pill {{ $corte['nota_corte'] >= $notaMinima ? 'ui-pill-success' : 'ui-pill-danger' }}">
                                            {{ number_format($corte['nota_corte'], 2) }}
                                        </span>
                                    @else
                                        <span class="ui-pill ui-pill-neutral">Sin calificaciones</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Tabla de ítems del corte --}}
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 corte-eval-table">
                                    <thead>
                                        <tr>
                                            <th class="ps-4" style="min-width: 220px;">Nombre del Ítem</th>
                                            <th class="text-center" style="width: 115px;">Corte</th>
                                            <th class="text-center" style="width: 100px;">Peso</th>
                                            <th class="text-center" style="width: 115px;">Nota</th>
                                            <th class="text-center" style="width: 155px;">Entregable</th>
                                            <th class="text-center pe-4" style="width: 145px;">Fecha Límite</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($corte['items'] as $item)
                                            @php
                                                $respuesta = $item->respuestas->first();
                                                $pesoFormateado = is_numeric($item->porcentaje)
                                                    ? rtrim(rtrim(number_format((float) $item->porcentaje, 2), '0'), '.')
                                                    : '0';
                                            @endphp
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="text-muted small fw-semibold">{{ $loop->iteration }}.</span>
                                                        <span class="fw-semibold text-dark">{{ $item->nombre }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="ui-pill ui-pill-primary">
                                                        {{ $corte['etiqueta'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="ui-pill ui-pill-neutral">
                                                        {{ $pesoFormateado }}%
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if ($respuesta && $respuesta->nota_obtenida !== null)
                                                        <span class="ui-pill {{ $respuesta->nota_obtenida >= $notaMinima ? 'ui-pill-success' : 'ui-pill-danger' }}">
                                                            {{ number_format((float) $respuesta->nota_obtenida, 1) }}
                                                        </span>
                                                    @else
                                                        <span class="ui-pill ui-pill-neutral">Sin nota</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($item->habilitar_entregable && $respuesta && $respuesta->archivo_url)
                                                        <a href="{{ $respuesta->archivo_url }}" target="_blank"
                                                            class="btn btn-xs btn-outline-primary rounded-pill waves-effect">
                                                            <i class="ti ti-download me-1"></i>Ver entregable
                                                        </a>
                                                    @elseif ($item->habilitar_entregable)
                                                        <span class="ui-pill ui-pill-warning">Sin entrega</span>
                                                    @else
                                                        <span class="text-muted small">No requiere</span>
                                                    @endif
                                                </td>
                                                <td class="text-center pe-4 small text-muted">
                                                    @if ($item->fecha_fin)
                                                        <span class="d-inline-flex align-items-center gap-1">
                                                            <i class="ti ti-calendar-event"></i>
                                                            {{ \Carbon\Carbon::parse($item->fecha_fin)->format('d/m/Y') }}
                                                        </span>
                                                    @else
                                                        <span>—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="ps-4 fw-semibold text-muted">
                                                Resumen {{ $corte['etiqueta'] }}
                                            </td>
                                            <td class="text-center fw-bold text-dark">
                                                {{ rtrim(rtrim(number_format($corte['peso_items_total'], 2), '0'), '.') }}%
                                            </td>
                                            <td class="text-center fw-bold {{ $corte['nota_corte'] !== null ? ($corte['nota_corte'] >= $notaMinima ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                                {{ $corte['nota_corte'] !== null ? number_format($corte['nota_corte'], 2) : '—' }}
                                            </td>
                                            <td colspan="2" class="pe-4 text-end text-muted small">
                                                {{ $corte['items_calificados'] }}/{{ $corte['total_items'] }} ítems calificados
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5 bg-lighter rounded-3">
                            <i class="ti ti-clipboard-off fs-2 d-block mb-2"></i>
                            <h6 class="mb-1 fw-semibold text-dark">Sin ítems de evaluación</h6>
                            <p class="mb-0 small">No hay ítems de evaluación registrados para esta clase.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- FILA 4: HISTORIAL EDUCATIVO GENERAL DEL ALUMNO --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card student-page-card">
                <div class="student-card-header d-flex align-items-center gap-2">
                    <span class="section-icon-box"><i class="ti ti-books"></i></span>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark fs-6">Historial Educativo General del Estudiante</h5>
                        <small class="text-muted">Avance por escuela y estado de cada materia</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if (!empty($escuelas) && $escuelas->count() > 0)
                        <div class="accordion accordion-school-header" id="accordionHistorialEscuelas">
                            @foreach ($escuelas as $escuela)
                                <div class="accordion-item shadow-none mb-3">
                                    <h2 class="accordion-header" id="headingEscuela{{ $escuela->id }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseEscuela{{ $escuela->id }}" aria-expanded="false"
                                            aria-controls="collapseEscuela{{ $escuela->id }}">
                                            <div class="d-flex flex-column w-100 me-3">
                                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                                    <span class="fw-bold fs-6 text-dark d-inline-flex align-items-center gap-2">
                                                        <i class="ti ti-bookmark text-primary"></i>{{ $escuela->nombre }}
                                                    </span>
                                                    <span class="ui-pill ui-pill-primary">
                                                        {{ $escuela->aprobadas_obligatorias }} / {{ $escuela->total_obligatorias }} obligatorias
                                                    </span>
                                                </div>
                                                <div class="progress mt-2" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $escuela->progreso }}%;"
                                                        aria-valuenow="{{ $escuela->progreso }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted mt-1" style="font-size: 0.75rem;">
                                                    Avance académico: <strong class="text-dark">{{ $escuela->progreso }}%</strong>
                                                </small>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseEscuela{{ $escuela->id }}" class="accordion-collapse collapse"
                                        aria-labelledby="headingEscuela{{ $escuela->id }}" data-bs-parent="#accordionHistorialEscuelas">
                                        <div class="accordion-body p-3 bg-lighter">
                                            <div class="row g-3">
                                                @forelse ($escuela->materias as $materia)
                                                    <div class="col-12 col-md-6 col-xl-4">
                                                        <div class="card history-subject-card h-100 shadow-none">
                                                            <div class="card-body p-3 d-flex flex-column justify-content-between gap-2">
                                                                <div>
                                                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                                        <h6 class="mb-0 fw-semibold text-dark text-truncate" title="{{ $materia->nombre }}">
                                                                            {{ $materia->nombre }}
                                                                        </h6>
                                                                        @if ($materia->caracter_obligatorio)
                                                                            <span class="ui-pill ui-pill-sm ui-pill-primary">Obligatoria</span>
                                                                        @else
                                                                            <span class="ui-pill ui-pill-sm ui-pill-warning">Opcional</span>
                                                                        @endif
                                                                    </div>

                                                                    <div>
                                                                        @if ($materia->resultado)
                                                                            @if ($materia->resultado->aprobado)
                                                                                <span class="ui-pill ui-pill-sm ui-pill-success">
                                                                                    <i class="ti ti-circle-check"></i>Aprobada
                                                                                </span>
                                                                            @else
                                                                                <span class="ui-pill ui-pill-sm ui-pill-danger">
                                                                                    <i class="ti ti-circle-x"></i>Reprobada
                                                                                </span>
                                                                            @endif
                                                                        @else
                                                                            <span class="ui-pill ui-pill-sm ui-pill-neutral">
                                                                                <i class="ti ti-clock"></i>Disponible
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div class="pt-2 mt-1 border-top small text-muted">
                                                                    @if ($materia->resultado)
                                                                        <div class="d-flex justify-content-between">
                                                                            <span>Nota final</span>
                                                                            <strong class="text-dark">{{ $materia->resultado->nota_final }}</strong>
                                                                        </div>
                                                                    @endif
                                                                    <div class="d-flex justify-content-between">
                                                                        <span>Estado</span>
                                                                        <span class="fw-medium text-dark">
                                                                            @if ($materia->resultado)
                                                                                {{ $materia->resultado->aprobado ? 'Aprobado' : 'No aprobado' }}
                                                                            @else
                                                                                Disponible para cursar
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 text-center text-muted small py-3">
                                                        No hay materias registradas para esta escuela.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4 bg-lighter rounded-3">
                            <i class="ti ti-info-circle fs-3 d-block mb-1"></i>
                            No hay historial educativo disponible para este estudiante.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
