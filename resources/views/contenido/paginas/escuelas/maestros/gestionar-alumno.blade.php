{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)
@extends('layouts.layoutMaster')

@section('title', 'Gestionar Alumno: ' . ($alumno->nombre(3) ?? 'Alumno'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('page-style')
    <style>
        .profile-avatar-wrapper {
            position: relative;
            display: inline-block;
        }
        .profile-avatar-wrapper .avatar-initial {
            font-size: 1.75rem;
            font-weight: 600;
        }
        .kpi-card {
            background-color: var(--bs-gray-100);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }
        .kpi-card:hover {
            background-color: var(--bs-gray-200);
        }
        .table-custom-header th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: var(--bs-secondary);
            background-color: var(--bs-gray-100) !important;
        }
        .accordion-school-header .accordion-button {
            padding: 0.85rem 1.25rem;
            border-radius: 0.5rem !important;
        }
        .accordion-school-header .accordion-button:not(.collapsed) {
            background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
            color: var(--bs-primary) !important;
            box-shadow: none !important;
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('content')
    @include('layouts.status-msn')

    {{-- Encabezado Principal de la Página --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-primary">
                <i class="mdi mdi-account-school me-2"></i>Gestionar Alumno:
                <span class="text-black fw-normal">{{ $alumno->nombre(3) }}</span>
            </h4>
            <p class="mb-0 text-muted">
                <strong>Clase:</strong> {{ $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'N/A' }}
                <span class="mx-2">•</span>
                <strong>Periodo:</strong> {{ $horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'N/A' }}
            </p>
        </div>
        <div>
            <a href="{{ route('maestros.dashboardClase', ['horarioAsignado' => $horarioAsignado, 'maestro' => $maestro]) }}"
                class="btn btn-outline-secondary rounded-pill waves-effect shadow-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Volver a la Clase
            </a>
        </div>
    </div>

    {{-- FILA 1: DATOS DEL ESTUDIANTE Y REPORTES DE ASISTENCIA CONECTADOS --}}
    <div class="row g-4 mb-4">
        {{-- Tarjeta del Perfil del Alumno --}}
        <div class="col-xl-4 col-lg-5 col-md-5 col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        {{-- Avatar y Nombre --}}
                        <div class="text-center pb-3 border-bottom mb-3">
                            <div class="profile-avatar-wrapper mb-3">
                                @if (isset($alumno) && ($alumno->foto == 'default-m.png' || $alumno->foto == 'default-f.png'))
                                    <div class="avatar avatar-xl mx-auto shadow-sm">
                                        <span class="avatar-initial rounded-circle border border-3 border-white bg-primary text-white">
                                            {{ $alumno->inicialesNombre() }}
                                        </span>
                                    </div>
                                @elseif (isset($alumno))
                                    <div class="avatar avatar-xl mx-auto shadow-sm">
                                        <img src="{{ tenant_asset('img/usuarios/foto-usuario/' . $alumno->foto) }}"
                                            alt="{{ $alumno->foto }}"
                                            class="avatar-initial rounded-circle border border-3 border-white object-fit-cover">
                                    </div>
                                @else
                                    <div class="avatar avatar-xl mx-auto shadow-sm">
                                        <span class="avatar-initial rounded-circle border border-3 border-white bg-secondary text-white">
                                            ?
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $alumno->nombre(3) }}</h5>
                            <p class="text-muted small mb-0"><i class="mdi mdi-email-outline me-1"></i>{{ $alumno->email }}</p>
                        </div>

                        {{-- KPIs del Curso Actual --}}
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="kpi-card text-center">
                                    <small class="text-muted d-block mb-1">Nota Final</small>
                                    @if(isset($estadoAcademicoAlumno) && $estadoAcademicoAlumno->nota_final_numerica !== null)
                                        <span class="fs-5 fw-bold {{ $estadoAcademicoAlumno->nota_final_numerica >= 3.0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($estadoAcademicoAlumno->nota_final_numerica, 1) }}
                                        </span>
                                    @else
                                        <span class="badge bg-label-warning rounded-pill">Sin calificar</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="kpi-card text-center">
                                    <small class="text-muted d-block mb-1">Estado</small>
                                    @php
                                        $estadoStr = $estadoAcademicoAlumno->estado_aprobacion ?? 'cursando';
                                        $badgeClass = match($estadoStr) {
                                            'aprobado' => 'bg-label-success',
                                            'no_aprobado' => 'bg-label-danger',
                                            'retirado_oficialmente' => 'bg-label-secondary',
                                            default => 'bg-label-info',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill text-capitalize">
                                        {{ str_replace('_', ' ', $estadoStr) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Información General Detallada --}}
                        <h6 class="fw-bold text-uppercase text-secondary mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Detalles del Estudiante
                        </h6>
                        <ul class="list-group list-group-flush mb-0">
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                                <span class="text-muted small"><i class="mdi mdi-phone-outline me-2"></i>Celular:</span>
                                <span class="fw-semibold text-dark small">{{ $alumno->celular ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                                <span class="text-muted small"><i class="mdi mdi-office-building me-2"></i>Sede origen:</span>
                                <span class="fw-semibold text-dark small">{{ $alumno->sede->nombre ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 py-2 border-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-muted small"><i class="mdi mdi-account-supervisor-outline me-2"></i>Líder directo:</span>
                                    <span class="fw-semibold text-dark small text-end" style="max-width: 60%;">
                                        @if ($alumno->encargadosDirectos()->count() > 0)
                                            @foreach ($alumno->encargadosDirectos() as $encargado)
                                                {{ $encargado->nombre }}@if (!$loop->last), @endif
                                            @endforeach
                                        @else
                                            Sin encargados
                                        @endif
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reportes de Asistencia Reales --}}
        <div class="col-xl-8 col-lg-7 col-md-7 col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark fs-6">
                        <i class="mdi mdi-calendar-check text-primary me-2"></i>Reportes de Asistencia
                    </h5>
                    @php
                        $porcentajeAsistencia = $totalClasesReportadas > 0 ? round(($totalAsistenciasCumplidas / $totalClasesReportadas) * 100) : 0;
                    @endphp
                    <span class="badge bg-label-success rounded-pill px-3 py-1 fw-semibold">
                        {{ $totalAsistenciasCumplidas }} de {{ $totalClasesReportadas }} asistencias ({{ $porcentajeAsistencia }}%)
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-custom-header sticky-top">
                                <tr>
                                    <th class="ps-3">Fecha Reporte</th>
                                    <th class="text-center">Asistió</th>
                                    <th class="pe-3">Motivo / Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reportesAsistencia as $detalleReporte)
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <span class="fw-semibold text-dark">
                                                {{ $detalleReporte->reporteClase ? \Carbon\Carbon::parse($detalleReporte->reporteClase->fecha_clase_reportada)->isoFormat('D [de] MMMM YYYY') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-center py-2">
                                            @if ($detalleReporte->asistio)
                                                <span class="badge bg-label-success px-2 py-1"><i class="mdi mdi-check me-1"></i>Sí</span>
                                            @else
                                                <span class="badge bg-label-danger px-2 py-1"><i class="mdi mdi-close me-1"></i>No</span>
                                            @endif
                                        </td>
                                        <td class="pe-3 py-2">
                                            <small class="text-muted">
                                                {{ $detalleReporte->motivoInasistencia?->nombre ?? ($detalleReporte->observaciones_alumno ?? 'Sin observaciones') }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="mdi mdi-calendar-blank mdi-24px d-block mb-1"></i>
                                            No hay reportes de asistencia registrados para este alumno en este curso.
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

    {{-- FILA 2: VALORACIÓN FINAL DEL MAESTRO (COL-12) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark fs-6">
                        <i class="mdi mdi-comment-text-outline text-primary me-2"></i>Valoración Final del Maestro
                    </h5>
                </div>
                <div class="card-body py-3">
                    <div class="p-3 bg-lighter rounded-3 border-start border-4 border-primary">
                        <p class="mb-0 text-dark fst-italic">
                            {{ $estadoAcademicoAlumno->observaciones_cierre ?? 'Sin observaciones o valoración final registrada para este curso.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILA 3: ÍTEMS DE EVALUACIÓN DEL CURSO (COL-12) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark fs-6">
                        <i class="mdi mdi-format-list-checks text-primary me-2"></i>Items de Evaluación del Curso
                    </h5>
                    <small class="text-muted fw-semibold">Total Ítems: {{ count($itemsEvaluacion) }}</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-custom-header">
                                <tr>
                                    <th class="ps-3">Nombre del Ítem</th>
                                    <th class="text-center">Peso</th>
                                    <th class="text-center">Corte</th>
                                    <th class="text-center">Nota</th>
                                    <th class="text-center">Entregable</th>
                                    <th class="text-center pe-3">Fecha Límite</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($itemsEvaluacion as $item)
                                    @php
                                        $respuesta = $item->respuestas->first();
                                    @endphp
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <span class="fw-bold text-dark">{{ $item->nombre }}</span>
                                        </td>
                                        <td class="text-center py-2">
                                            <span class="badge bg-label-primary px-2">{{ $item->porcentaje }}%</span>
                                        </td>
                                        <td class="text-center py-2 text-muted">
                                            {{ $item->cortePeriodo?->nombre ?? ($item->corte_periodo_id ?? 'N/A') }}
                                        </td>
                                        <td class="text-center py-2">
                                            @if($respuesta && $respuesta->nota_obtenida !== null)
                                                <span class="fw-bold {{ $respuesta->nota_obtenida >= 3.0 ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($respuesta->nota_obtenida, 1) }}
                                                </span>
                                            @else
                                                <span class="text-muted small">Sin nota</span>
                                            @endif
                                        </td>
                                        <td class="text-center py-2">
                                            @if ($item->habilitar_entregable && $respuesta && $respuesta->archivo_url)
                                                <a href="{{ $respuesta->archivo_url }}" target="_blank"
                                                    class="btn btn-xs btn-outline-primary rounded-pill waves-effect">
                                                    <i class="mdi mdi-download me-1"></i>Ver Entregable
                                                </a>
                                            @elseif($item->habilitar_entregable)
                                                <span class="badge bg-label-secondary">Sin entrega</span>
                                            @else
                                                <span class="badge bg-label-light text-muted">No requiere</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-3 py-2 small text-muted">
                                            {{ $item->fecha_fin ? \Carbon\Carbon::parse($item->fecha_fin)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="mdi mdi-clipboard-text-outline mdi-24px d-block mb-1"></i>
                                            No hay ítems de evaluación registrados para esta clase.
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

    {{-- FILA 4: HISTORIAL EDUCATIVO GENERAL DEL ALUMNO (COL-12) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark fs-6">
                        <i class="mdi mdi-school-outline text-primary me-2"></i>Historial Educativo General del Alumno
                    </h5>
                </div>
                <div class="card-body py-4">
                    @if (!empty($escuelas) && $escuelas->count() > 0)
                        <div class="accordion accordion-school-header" id="accordionHistorialEscuelas">
                            @foreach ($escuelas as $escuela)
                                <div class="accordion-item card border shadow-none mb-3 overflow-hidden">
                                    <h2 class="accordion-header" id="headingEscuela{{ $escuela->id }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseEscuela{{ $escuela->id }}" aria-expanded="false"
                                            aria-controls="collapseEscuela{{ $escuela->id }}">
                                            <div class="d-flex flex-column w-100 me-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-bold fs-6 text-dark">
                                                        <i class="mdi mdi-bookmark-outline me-2 text-primary"></i>{{ $escuela->nombre }}
                                                    </span>
                                                    <span class="badge bg-label-primary rounded-pill px-3">
                                                        {{ $escuela->aprobadas_obligatorias }} / {{ $escuela->total_obligatorias }} Obligatorias
                                                    </span>
                                                </div>
                                                <div class="progress mt-2" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $escuela->progreso }}%;" aria-valuenow="{{ $escuela->progreso }}"
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted mt-1" style="font-size: 0.75rem;">
                                                    Avance académico: {{ $escuela->progreso }}%
                                                </small>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseEscuela{{ $escuela->id }}" class="accordion-collapse collapse"
                                        aria-labelledby="headingEscuela{{ $escuela->id }}" data-bs-parent="#accordionHistorialEscuelas">
                                        <div class="accordion-body p-3 bg-lighter">
                                            <div class="row g-3">
                                                @forelse($escuela->materias as $materia)
                                                    <div class="col-12 col-xl-4 col-md-6">
                                                        <div class="card h-100 border shadow-none">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-start justify-content-between mb-2">
                                                                    <h6 class="mb-0 fw-semibold text-dark text-truncate" style="max-width: 70%;" title="{{ $materia->nombre }}">
                                                                        {{ $materia->nombre }}
                                                                    </h6>
                                                                    @if($materia->caracter_obligatorio)
                                                                        <span class="badge bg-label-primary" style="font-size: 0.65rem;">Obligatoria</span>
                                                                    @else
                                                                        <span class="badge bg-label-warning" style="font-size: 0.65rem;">Opcional</span>
                                                                    @endif
                                                                </div>

                                                                <div class="mb-2">
                                                                    @if($materia->resultado)
                                                                        @if($materia->resultado->aprobado)
                                                                            <span class="badge bg-label-success rounded-pill">Aprobada</span>
                                                                        @else
                                                                            <span class="badge bg-label-danger rounded-pill">Reprobada</span>
                                                                        @endif
                                                                    @else
                                                                        <span class="badge bg-label-secondary rounded-pill">Disponible</span>
                                                                    @endif
                                                                </div>

                                                                <ul class="list-unstyled mb-0 small text-muted">
                                                                    @if($materia->resultado)
                                                                        <li><strong>Nota Final:</strong> {{ $materia->resultado->nota_final }}</li>
                                                                    @endif
                                                                    <li><strong>Estado:</strong>
                                                                        @if($materia->resultado)
                                                                            {{ $materia->resultado->aprobado ? 'Aprobado' : 'No aprobado' }}
                                                                        @else
                                                                            Disponible para cursar
                                                                        @endif
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 text-center text-muted small py-2">No hay materias registradas para esta escuela.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info text-center mb-0" role="alert">
                            <i class="mdi mdi-information-outline me-1"></i>No hay historial educativo disponible para este alumno.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
