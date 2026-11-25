@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Mi Materia - Escuelas')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Tarjeta principal de cabecera (limpia y compacta, respetando color del tenant) */
        .materia-hero-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.9rem;
            background-color: var(--bs-card-bg, #fff);
            box-shadow: 0 0.15rem 0.65rem rgba(15, 23, 42, 0.04);
        }

        .materia-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.72rem;
            border-radius: 999px;
            background-color: var(--bs-gray-100);
            border: 1px solid var(--bs-border-color);
            color: #495057;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .materia-teacher-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem 0.25rem 0.3rem;
            border-radius: 999px;
            background-color: var(--bs-gray-100);
            border: 1px solid var(--bs-border-color);
            font-size: 0.8125rem;
            font-weight: 600;
            color: #2f2b3d;
        }

        .materia-teacher-avatar {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 50%;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
            flex-shrink: 0;
            border: 0 !important;
        }

        .materia-teacher-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Navegación por pestañas */
        .materia-tabs-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.75rem;
            background-color: var(--bs-card-bg, #fff);
            padding: 0.35rem;
            box-shadow: 0 0.1rem 0.4rem rgba(15, 23, 42, 0.03);
        }

        .materia-tabs-nav .nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border-radius: 0.55rem;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.6rem 1.15rem;
            color: #4b5563;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .materia-tabs-nav .nav-link:not(.active):hover {
            background-color: var(--bs-gray-100);
            color: #2f2b3d;
        }

        /* Tarjetas de sección y KPIs */
        .alumno-surface-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.85rem;
            background-color: var(--bs-card-bg, #fff);
            box-shadow: 0 0.15rem 0.6rem rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .alumno-kpi-card {
            border: 1px solid var(--bs-border-color);
            border-left-width: 4px;
            border-radius: 0.85rem;
            background-color: var(--bs-card-bg, #fff);
            box-shadow: 0 0.15rem 0.6rem rgba(15, 23, 42, 0.04);
        }

        .alumno-kpi-card.is-success {
            border-left-color: #28c76f;
        }

        .alumno-kpi-card.is-danger {
            border-left-color: #ff4c51;
        }

        .alumno-kpi-card.is-info {
            border-left-color: #00bad1;
        }

        .alumno-kpi-card.is-warning {
            border-left-color: #ff9f43;
        }

        .kpi-icon-box {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            flex-shrink: 0;
        }

        .kpi-icon-box.is-success {
            background-color: rgba(40, 199, 111, 0.14);
            color: #167943;
        }

        .kpi-icon-box.is-danger {
            background-color: rgba(255, 76, 81, 0.14);
            color: #c82333;
        }

        .kpi-icon-box.is-info {
            background-color: rgba(0, 186, 209, 0.14);
            color: #007b8a;
        }

        .kpi-icon-box.is-warning {
            background-color: rgba(255, 159, 67, 0.16);
            color: #9a5700;
        }

        .section-icon-box {
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 0.6rem;
            background-color: var(--bs-gray-100);
            border: 1px solid var(--bs-border-color);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Sistema de pills con contraste garantizado frente a _custom-variables.css */
        .ui-pill {
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

        .ui-pill-primary {
            background-color: var(--bs-gray-100);
            border: 1px solid var(--bs-border-color);
            color: #2f2b3d !important;
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

        /* Tablas limpias */
        .alumno-clean-table thead th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            color: var(--bs-secondary-color, #6c757d);
            background-color: var(--bs-gray-100);
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .alumno-clean-table tbody td {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            vertical-align: middle;
        }

        /* Tarjetas de calificación por corte (Tab 2) */
        .corte-alumno-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.85rem;
            background-color: var(--bs-card-bg, #fff);
            box-shadow: 0 0.15rem 0.6rem rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .corte-alumno-header {
            background-color: var(--bs-gray-100);
            border-bottom: 1px solid var(--bs-border-color);
            padding: 0.9rem 1.25rem;
        }

        .card-item-calificacion {
            border: 1px solid var(--bs-border-color);
            border-left-width: 4px;
            border-radius: 0.75rem;
            background-color: var(--bs-card-bg, #fff);
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .card-item-calificacion:hover {
            box-shadow: 0 0.25rem 0.75rem rgba(15, 23, 42, 0.05);
        }

        .status-calificado {
            border-left-color: #28c76f;
        }

        .status-entregado {
            border-left-color: #00bad1;
        }

        .status-pendiente {
            border-left-color: #ff9f43;
        }

        .nota-valor {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        /* Tarjetas de recursos (Tab 3) */
        .alumno-recurso-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.85rem;
            background-color: var(--bs-card-bg, #fff);
            box-shadow: 0 0.15rem 0.6rem rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
        }

        .alumno-recurso-card:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.4);
            box-shadow: 0 0.35rem 1rem rgba(15, 23, 42, 0.07);
            transform: translateY(-1px);
        }

        .recurso-option-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.55rem 0.8rem;
            border-radius: 0.6rem;
            border: 1px solid var(--bs-border-color);
            background-color: var(--bs-gray-100);
            color: #2f2b3d;
            text-decoration: none;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .recurso-option-link:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.45);
            background-color: rgba(var(--bs-primary-rgb), 0.04);
            color: #2f2b3d;
        }
    </style>
@endsection

@section('content')
    @php
        $promedioAprobado = $materia->promedio_actual >= $notaMinimaAprobacion;
        $asistenciaBarClass = match (true) {
            $asistencia->total_clases === 0 => 'bg-secondary',
            $asistencia->porcentaje >= 80 => 'bg-success',
            $asistencia->porcentaje >= 60 => 'bg-warning',
            default => 'bg-danger',
        };
    @endphp

    {{-- ========================================== --}}
    {{-- CABECERA LIMPIA Y COMPACTA DE LA MATERIA --}}
    {{-- ========================================== --}}
    <div class="card materia-hero-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="ui-pill bg-label-primary border-0">
                    <i class="ti ti-calendar-event"></i>
                    {{ $materia->periodo }}
                </span>
                <span class="ui-pill ui-pill-neutral">
                    <i class="ti ti-school text-primary"></i>
                    {{ $materia->escuela }}
                </span>
            </div>

            <h3 class="fw-bold text-dark mb-3 lh-sm">{{ $materia->nombre }}</h3>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="materia-meta-chip">
                    <i class="ti ti-clock text-primary"></i>
                    {{ $materia->horario }}
                </span>
                <span class="materia-meta-chip">
                    <i class="ti ti-building-skyscraper text-primary"></i>
                    Sede: <strong class="text-dark">{{ $materia->sede }}</strong>
                </span>
                <span class="materia-meta-chip">
                    <i class="ti ti-door text-primary"></i>
                    Tipo: <strong class="text-dark">{{ $materia->tipo_aula }}</strong>
                </span>
            </div>

            {{-- Franja compacta horizontal de maestros --}}
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
                <span class="text-muted small fw-semibold d-inline-flex align-items-center gap-1 me-1">
                    <i class="ti ti-users text-primary"></i>
                    {{ count($materia->maestros) > 1 ? 'Maestros:' : 'Maestro:' }}
                </span>
                @forelse ($materia->maestros as $maestro)
                    <span class="materia-teacher-pill">
                        @if ($maestro->imagen == 'default-m.png' || $maestro->imagen == 'default-f.png')
                            <span class="materia-teacher-avatar bg-label-primary">{{ $maestro->iniciales }}</span>
                        @else
                            <span class="materia-teacher-avatar">
                                <img src="{{ tenant_asset('img/usuarios/foto-usuario/' . $maestro->imagen) }}"
                                    alt="{{ $maestro->nombre }}">
                            </span>
                        @endif
                        <span>{{ $maestro->nombre }}</span>
                    </span>
                @empty
                    <span class="text-muted small fst-italic">Sin maestro asignado</span>
                @endforelse
            </div>
        </div>
    </div>

    @include('layouts.status-msn')

    {{-- ========================================== --}}
    {{-- PESTAÑAS DE NAVEGACIÓN --}}
    {{-- ========================================== --}}
    <div class="row">
        <div class="col-12">
            <div class="materia-tabs-card mb-4">
                <ul class="nav nav-pills materia-tabs-nav flex-column flex-sm-row gap-1" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active w-100" id="pills-resumen-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-resumen" type="button" role="tab" aria-controls="pills-resumen"
                            aria-selected="true">
                            <i class="ti ti-layout-dashboard"></i>
                            <span>Resumen</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link w-100" id="pills-calificaciones-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-calificaciones" type="button" role="tab"
                            aria-controls="pills-calificaciones" aria-selected="false">
                            <i class="ti ti-calculator"></i>
                            <span>Calificaciones por Corte</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link w-100" id="pills-recursos-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-recursos" type="button" role="tab" aria-controls="pills-recursos"
                            aria-selected="false">
                            <i class="ti ti-folders"></i>
                            <span>Recursos</span>
                            <span class="ui-pill ui-pill-neutral ms-1" style="padding: 0.12rem 0.45rem; font-size: 0.7rem;">
                                {{ count($recursos) }}
                            </span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content p-0 bg-transparent shadow-none" id="pills-tabContent">
                {{-- ========================================== --}}
                {{-- PESTAÑA 1: RESUMEN --}}
                {{-- ========================================== --}}
                <div class="tab-pane fade show active" id="pills-resumen" role="tabpanel"
                    aria-labelledby="pills-resumen-tab">

                    {{-- KPI CARDS --}}
                    <div class="row g-4 mb-4">
                        {{-- Promedio --}}
                        <div class="col-md-4 col-12">
                            <div class="card alumno-kpi-card h-100 {{ $promedioAprobado ? 'is-success' : 'is-danger' }}">
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                                                Promedio Actual
                                            </small>
                                            <h2 class="mb-0 fw-bold {{ $promedioAprobado ? 'text-success' : 'text-danger' }}">
                                                {{ $materia->promedio_actual }}
                                            </h2>
                                        </div>
                                        <span class="kpi-icon-box {{ $promedioAprobado ? 'is-success' : 'is-danger' }}">
                                            <i class="ti {{ $promedioAprobado ? 'ti-circle-check' : 'ti-alert-circle' }}"></i>
                                        </span>
                                    </div>
                                    <div class="mt-3 pt-2 border-top text-muted small d-flex justify-content-between">
                                        <span>Mínimo aprobatorio</span>
                                        <strong class="text-dark">{{ $notaMinimaAprobacion }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Asistencia --}}
                        <div class="col-md-4 col-12">
                            <div class="card alumno-kpi-card is-info h-100">
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                                                Asistencia
                                            </small>
                                            <h2 class="mb-0 fw-bold text-info">{{ $asistencia->porcentaje }}%</h2>
                                        </div>
                                        <span class="kpi-icon-box is-info">
                                            <i class="ti ti-calendar-check"></i>
                                        </span>
                                    </div>
                                    <div class="mt-3 pt-2 border-top">
                                        <div class="progress mb-2" style="height: 5px;">
                                            <div class="progress-bar {{ $asistenciaBarClass }}" role="progressbar"
                                                style="width: {{ $asistencia->porcentaje }}%;"
                                                aria-valuenow="{{ $asistencia->porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="text-muted small">
                                            <strong class="text-dark">{{ $asistencia->asistencias_alumno }}</strong> de
                                            <strong class="text-dark">{{ $asistencia->total_clases }}</strong> clases asistidas
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pendientes --}}
                        <div class="col-md-4 col-12">
                            <div class="card alumno-kpi-card is-warning h-100">
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                                                Actividades Pendientes
                                            </small>
                                            <h2 class="mb-0 fw-bold text-warning">{{ $materia->pendientes_count }}</h2>
                                        </div>
                                        <span class="kpi-icon-box is-warning">
                                            <i class="ti ti-clipboard-list"></i>
                                        </span>
                                    </div>
                                    <div class="mt-3 pt-2 border-top text-muted small">
                                        Sin calificación o por entregar
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TABLAS DE RESUMEN --}}
                    <div class="row g-4">
                        <div class="col-lg-6 col-12">
                            <div class="card alumno-surface-card h-100 d-flex flex-column">
                                <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center gap-2">
                                    <span class="section-icon-box"><i class="ti ti-list-check text-primary"></i></span>
                                    <div>
                                        <h5 class="mb-0 fw-bold text-dark fs-6">Actividades Recientes y Pendientes</h5>
                                        <small class="text-muted">Vista rápida de tus próximas entregas y notas</small>
                                    </div>
                                </div>
                                <div class="card-body p-0 flex-grow-1">
                                    <div class="table-responsive">
                                        <table class="table table-hover alumno-clean-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="ps-4">Actividad</th>
                                                    <th>Corte</th>
                                                    <th class="text-center pe-4">Estado / Nota</th>
                                                </tr>
                                            </thead>
                                            <tbody class="table-border-bottom-0">
                                                @php
                                                    $actividadesMostradas = 0;
                                                @endphp
                                                @foreach ($materia->items_tabla_resumen as $item)
                                                    @if ($actividadesMostradas < 5 && ($item->nota === null || $actividadesMostradas < 3))
                                                        <tr>
                                                            <td class="ps-4">
                                                                <span class="fw-semibold text-dark">{{ $item->nombre }}</span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">{{ $item->corte }}</small>
                                                            </td>
                                                            <td class="text-center pe-4">
                                                                @if (is_numeric($item->nota))
                                                                    <span class="ui-pill {{ $item->nota >= $notaMinimaAprobacion ? 'ui-pill-success' : 'ui-pill-danger' }}">
                                                                        {{ $item->nota }}
                                                                    </span>
                                                                @else
                                                                    <span class="ui-pill ui-pill-warning">Pendiente</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @php $actividadesMostradas++; @endphp
                                                    @endif
                                                @endforeach
                                                @if ($actividadesMostradas == 0)
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-5">
                                                            <i class="ti ti-clipboard-check fs-2 d-block mb-1"></i>
                                                            No hay actividades recientes.
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top py-3 px-4 text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4"
                                        onclick="document.getElementById('pills-calificaciones-tab').click();">
                                        Ver todas las calificaciones <i class="ti ti-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-12">
                            <div class="card alumno-surface-card h-100 d-flex flex-column" x-data="{ expanded: false }">
                                <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="section-icon-box"><i class="ti ti-calendar-stats text-primary"></i></span>
                                        <div>
                                            <h5 class="mb-0 fw-bold text-dark fs-6">Historial de Asistencia</h5>
                                            <small class="text-muted">Registro de sesiones recientes</small>
                                        </div>
                                    </div>
                                    <span class="ui-pill ui-pill-info">
                                        {{ count($asistencia->historial) }} {{ count($asistencia->historial) === 1 ? 'sesión' : 'sesiones' }}
                                    </span>
                                </div>
                                <div class="card-body p-0 flex-grow-1">
                                    <div class="table-responsive"
                                        :style="expanded ? '' : 'max-height: 290px; overflow: hidden; position: relative;'">
                                        <table class="table table-hover alumno-clean-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="ps-4">Fecha</th>
                                                    <th class="text-center">Estado</th>
                                                    <th class="pe-4">Observación</th>
                                                </tr>
                                            </thead>
                                            <tbody class="table-border-bottom-0">
                                                @forelse ($asistencia->historial as $registro)
                                                    <tr>
                                                        <td class="ps-4">
                                                            <span class="fw-medium text-dark d-inline-flex align-items-center gap-1">
                                                                <i class="ti ti-calendar-event text-muted"></i>
                                                                {{ $registro->fecha }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($registro->estado == 'Asistió')
                                                                <span class="ui-pill ui-pill-success">
                                                                    <i class="ti ti-check"></i>{{ $registro->estado }}
                                                                </span>
                                                            @else
                                                                <span class="ui-pill ui-pill-danger">
                                                                    <i class="ti ti-x"></i>{{ $registro->estado }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="pe-4">
                                                            <small class="text-muted">
                                                                @if ($registro->motivo)
                                                                    {{ \Illuminate\Support\Str::limit($registro->motivo, 22, '...') }}
                                                                    @if (strlen($registro->motivo) > 22)
                                                                        <i class="ti ti-info-circle text-warning ms-1"
                                                                            title="{{ $registro->motivo }}" style="cursor: help;"></i>
                                                                    @endif
                                                                @else
                                                                    <span>—</span>
                                                                @endif
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-5">
                                                            <i class="ti ti-calendar-off fs-2 d-block mb-1"></i>
                                                            Aún no hay registros de asistencia.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                        @if (count($asistencia->historial) > 5)
                                            <div x-show="!expanded"
                                                style="position: absolute; bottom: 0; left: 0; right: 0; height: 48px; background: linear-gradient(to top, rgba(255,255,255,0.98), rgba(255,255,255,0)); pointer-events: none;">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if (count($asistencia->historial) > 5)
                                    <div class="card-footer bg-transparent border-top py-3 px-4 text-center">
                                        <button type="button" @click="expanded = !expanded"
                                            class="btn btn-sm btn-outline-secondary rounded-pill px-4">
                                            <span x-text="expanded ? 'Ver menos' : 'Ver historial completo'"></span>
                                            <i class="ti ms-1" :class="expanded ? 'ti-chevron-up' : 'ti-chevron-down'"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- PESTAÑA 2: CALIFICACIONES POR CORTE --}}
                {{-- ========================================== --}}
                <div class="tab-pane fade" id="pills-calificaciones" role="tabpanel"
                    aria-labelledby="pills-calificaciones-tab">
                    <livewire:alumno.calificacionesAlumno :horario="$horario" />
                </div>

                {{-- ========================================== --}}
                {{-- PESTAÑA 3: RECURSOS --}}
                {{-- ========================================== --}}
                <div class="tab-pane fade" id="pills-recursos" role="tabpanel" aria-labelledby="pills-recursos-tab">
                    <div class="row g-4">
                        @forelse ($recursos as $recurso)
                            @php
                                $tipoNormalizado = strtolower((string) $recurso->tipo);
                                $metaRecurso = match ($tipoNormalizado) {
                                    'video' => ['icono' => 'ti-video', 'pill' => 'ui-pill-danger', 'box' => 'is-danger'],
                                    'documento' => ['icono' => 'ti-file-text', 'pill' => 'ui-pill-primary', 'box' => 'is-info'],
                                    'enlace' => ['icono' => 'ti-link', 'pill' => 'ui-pill-info', 'box' => 'is-info'],
                                    'libro' => ['icono' => 'ti-book', 'pill' => 'ui-pill-primary', 'box' => 'is-info'],
                                    'clase' => ['icono' => 'ti-presentation', 'pill' => 'ui-pill-success', 'box' => 'is-success'],
                                    'predica', 'prédica' => ['icono' => 'ti-microphone-2', 'pill' => 'ui-pill-warning', 'box' => 'is-warning'],
                                    default => ['icono' => 'ti-file', 'pill' => 'ui-pill-neutral', 'box' => 'is-info'],
                                };
                                $tieneArchivo = filled($recurso->nombre_archivo);
                                $tieneYoutube = filled($recurso->link_youtube);
                                $tieneExterno = filled($recurso->link_externo);
                                $dominioExterno = $tieneExterno ? (parse_url($recurso->link_externo, PHP_URL_HOST) ?: $recurso->link_externo) : null;
                            @endphp
                            <div class="col-12 col-md-6">
                                <div class="card alumno-recurso-card">
                                    <div class="card-header bg-transparent border-bottom py-3 px-4">
                                        <div class="d-flex align-items-start gap-3">
                                            <span class="kpi-icon-box {{ $metaRecurso['box'] }}" style="width: 2.5rem; height: 2.5rem; font-size: 1.25rem;">
                                                <i class="ti {{ $metaRecurso['icono'] }}"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <h6 class="mb-1 fw-bold text-dark lh-sm">{{ $recurso->nombre }}</h6>
                                                <span class="ui-pill {{ $metaRecurso['pill'] }}">
                                                    <i class="ti {{ $metaRecurso['icono'] }}"></i>
                                                    {{ $recurso->tipo ?: 'Recurso' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body px-4 py-3 flex-grow-1 d-flex flex-column justify-content-between gap-3">
                                        @if (filled($recurso->descripcion))
                                            <p class="text-muted small mb-0">{{ $recurso->descripcion }}</p>
                                        @else
                                            <p class="text-muted small fst-italic mb-0">Sin descripción adicional.</p>
                                        @endif

                                        <div class="d-flex flex-column gap-2">
                                            @if ($tieneArchivo)
                                                <a href="{{ $recurso->archivo_url }}" target="_blank" rel="noopener noreferrer"
                                                    download class="recurso-option-link">
                                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                                        <i class="ti ti-file-description text-success fs-5 flex-shrink-0"></i>
                                                        <div class="min-w-0">
                                                            <span class="d-block fw-semibold small lh-sm">Archivo adjunto</span>
                                                            <small class="text-muted d-block text-truncate">{{ $recurso->nombre_archivo }}</small>
                                                        </div>
                                                    </div>
                                                    <i class="ti ti-download text-muted flex-shrink-0"></i>
                                                </a>
                                            @endif

                                            @if ($tieneYoutube)
                                                <a href="{{ $recurso->link_youtube }}" target="_blank" rel="noopener noreferrer"
                                                    class="recurso-option-link">
                                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                                        <i class="ti ti-brand-youtube text-danger fs-5 flex-shrink-0"></i>
                                                        <div class="min-w-0">
                                                            <span class="d-block fw-semibold small lh-sm">Ver video en YouTube</span>
                                                            <small class="text-muted d-block text-truncate">{{ $recurso->link_youtube }}</small>
                                                        </div>
                                                    </div>
                                                    <i class="ti ti-external-link text-muted flex-shrink-0"></i>
                                                </a>
                                            @endif

                                            @if ($tieneExterno)
                                                <a href="{{ $recurso->link_externo }}" target="_blank" rel="noopener noreferrer"
                                                    class="recurso-option-link">
                                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                                        <i class="ti ti-world-www text-info fs-5 flex-shrink-0"></i>
                                                        <div class="min-w-0">
                                                            <span class="d-block fw-semibold small lh-sm">Abrir enlace externo</span>
                                                            <small class="text-muted d-block text-truncate">{{ $dominioExterno }}</small>
                                                        </div>
                                                    </div>
                                                    <i class="ti ti-external-link text-muted flex-shrink-0"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card alumno-surface-card">
                                    <div class="card-body text-center py-5">
                                        <i class="ti ti-folder-off fs-1 text-muted d-block mb-2"></i>
                                        <h5 class="fw-bold text-dark mb-1">No hay recursos disponibles</h5>
                                        <p class="text-muted mb-0">El maestro aún no ha añadido materiales de apoyo para esta materia.</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal responder (compatibilidad) --}}
    <div class="modal fade" id="responderItemModal" tabindex="-1" aria-labelledby="responderItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="responderItemModalLabel">
                        Responder a: <span id="modal-item-titulo"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="respuestaTexto" class="form-label fw-semibold">Tu respuesta:</label>
                        <textarea class="form-control" id="respuestaTexto" rows="8" placeholder="Escribe tu respuesta aquí..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="adjuntarArchivo" class="form-label fw-semibold">Adjuntar archivo (opcional):</label>
                        <input class="form-control" type="file" id="adjuntarArchivo">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4">Guardar Respuesta</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal ver respuesta --}}
    <div class="modal fade" id="verRespuestaModal" tabindex="-1" aria-labelledby="verRespuestaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="verRespuestaModalLabel">
                        Revisión de: <span id="modal-ver-item-titulo"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-user text-primary"></i>Tu entrega
                        </h6>
                        <div class="p-3 bg-lighter border rounded-3">
                            <p class="mb-0" id="modal-ver-respuesta-alumno"></p>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-message-2 text-primary"></i>Feedback del maestro
                        </h6>
                        <div class="p-3 bg-lighter border rounded-3">
                            <p class="mb-0" id="modal-ver-feedback-maestro"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lógica para el gráfico de asistencia
            const asistenciaChartEl = document.querySelector('#asistenciaChart');
            if (asistenciaChartEl) {
                const options = {
                    chart: {
                        height: 250,
                        type: 'radialBar'
                    },
                    series: @json([$asistencia->porcentaje]),
                    plotOptions: {
                        radialBar: {
                            hollow: {
                                size: '70%'
                            },
                            dataLabels: {
                                name: {
                                    show: false
                                },
                                value: {
                                    fontSize: '2rem',
                                    fontWeight: '600',
                                    offsetY: 8,
                                    formatter: function(val) {
                                        return val + '%';
                                    }
                                }
                            }
                        }
                    },
                    stroke: {
                        lineCap: 'round'
                    },
                    labels: ['Asistencia']
                };
                const chart = new ApexCharts(asistenciaChartEl, options);
                chart.render();
            }

            // Lógica para poblar los modales dinámicamente
            function decodeHTML(html) {
                var txt = document.createElement("textarea");
                txt.innerHTML = html;
                let decoded = txt.value;
                for (let i = 0; i < 3; i++) {
                    if (decoded && (decoded.includes('&lt;') || decoded.includes('&gt;') || decoded.includes('&amp;'))) {
                        txt.innerHTML = decoded;
                        decoded = txt.value;
                    } else {
                        break;
                    }
                }
                return decoded;
            }

            const responderItemModal = document.getElementById('responderItemModal');
            if (responderItemModal) {
                responderItemModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const titulo = button.getAttribute('data-item-titulo');
                    document.getElementById('modal-item-titulo').textContent = titulo;
                });
            }

            const verRespuestaModal = document.getElementById('verRespuestaModal');
            if (verRespuestaModal) {
                verRespuestaModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const titulo = button.getAttribute('data-item-titulo');
                    const respuestaAlumno = button.getAttribute('data-respuesta-alumno');
                    const feedbackMaestro = button.getAttribute('data-feedback-maestro');

                    document.getElementById('modal-ver-item-titulo').textContent = titulo;
                    document.getElementById('modal-ver-respuesta-alumno').innerHTML = decodeHTML(respuestaAlumno ||
                        'No se encontró una respuesta de texto.');
                    document.getElementById('modal-ver-feedback-maestro').innerHTML = decodeHTML(feedbackMaestro ||
                        'El maestro aún no ha dejado un feedback.');
                });
            }
        });
    </script>
@endpush
