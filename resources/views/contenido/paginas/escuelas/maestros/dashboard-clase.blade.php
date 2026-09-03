{{-- dashboard-clase.blade.php --}}
@section('isEscuelasModule', true)
@extends('layouts.layoutMaster')
@section('title', 'Dashboard Clase')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-style')
    <style>
        .chart-container {
            min-height: 360px;
        }

        .students-list {
            --student-list-columns: 2rem minmax(13rem, 2.5fr) repeat(var(--cut-count), minmax(3.5rem, 1fr)) minmax(3.5rem, 1fr) minmax(4rem, 1fr) minmax(4.75rem, 1.15fr) minmax(5.5rem, 1.2fr) minmax(5.25rem, 1.15fr);
        }

        .students-list-toolbar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .75rem;
            align-items: center;
        }

        .students-export-actions {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: max-content;
        }

        .students-list-header,
        .student-list-row {
            display: grid;
            grid-template-columns: var(--student-list-columns);
            column-gap: .5rem;
            align-items: center;
        }

        .students-list-header {
            min-width: 52rem;
            padding: 0 .75rem .75rem;
            color: var(--bs-secondary-color);
            font-size: .7rem;
            font-weight: 600;
        }

        .students-list-header > :not(:nth-child(2)) {
            text-align: center;
        }

        .student-item-card {
            border: 0;
            box-shadow: none;
        }

        .student-list-row {
            min-width: 52rem;
            min-height: 4.75rem;
            padding: .75rem;
        }

        .student-position,
        .student-metric {
            text-align: center;
        }

        .student-identity {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: .625rem;
            align-items: center;
            min-width: 0;
        }

        .student-name {
            overflow-wrap: anywhere;
        }

        .student-transfer {
            display: inline-block;
            margin-top: .25rem;
            font-size: .625rem;
        }

        .student-action-list {
            display: grid;
            justify-items: center;
            gap: .25rem;
        }

        .student-action-list form,
        .student-action-list .btn {
            width: 4.75rem;
        }

        .student-action-list .btn {
            min-height: 1.625rem;
            padding: .2rem .35rem;
            font-size: .6875rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .student-status .badge {
            max-width: 100%;
            padding: .35rem .5rem;
            font-size: .6875rem;
        }

        .student-toggle {
            display: none;
        }

        .student-metric-label {
            display: none;
        }

        @media (min-width: 992px) {
            .student-details,
            .student-details-grid {
                display: contents !important;
            }
        }

        @media (max-width: 1199.98px) {
            .students-list-header,
            .student-list-row {
                column-gap: .25rem;
            }

            .student-list-row {
                padding-right: .5rem;
                padding-left: .5rem;
            }

            .student-action-list form,
            .student-action-list .btn {
                width: 4.5rem;
            }

            .student-action-list .btn,
            .student-status .badge {
                font-size: .625rem;
            }
        }

        @media (max-width: 991.98px) {
            .students-list-header {
                display: none;
            }

            .student-item-card {
                border: 1px solid var(--bs-border-color);
            }

            .student-list-row {
                grid-template-columns: 2rem minmax(0, 1fr) auto;
                min-width: 0;
                min-height: auto;
                padding: .875rem;
            }

            .student-position {
                align-self: start;
                padding-top: .2rem;
                color: var(--bs-secondary-color);
            }

            .student-toggle {
                display: inline-grid;
                place-items: center;
                width: 2.25rem;
                height: 2.25rem;
                padding: 0;
                border: 0;
                border-radius: 50%;
                color: var(--bs-primary);
                background: var(--bs-primary-bg-subtle);
            }

            .student-toggle i {
                transition: transform .2s ease;
            }

            .student-toggle[aria-expanded='true'] i {
                transform: rotate(180deg);
            }

            .student-details.show,
            .student-details.collapsing {
                grid-column: 1 / -1;
                margin-top: .75rem;
                border-top: 1px solid var(--bs-border-color);
            }

            .student-details-grid {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .75rem 1rem;
                padding-top: .875rem;
            }

            .student-metric {
                display: grid;
                gap: .125rem;
                text-align: left;
            }

            .student-metric-label {
                display: block;
                color: var(--bs-secondary-color);
                font-size: .7rem;
                font-weight: 600;
            }

            .student-action-list {
                grid-column: 1 / -1;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                padding-top: .125rem;
            }

            .student-action-list form,
            .student-action-list .btn {
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .students-list-toolbar {
                grid-template-columns: minmax(0, 1fr);
            }

            .students-export-actions {
                grid-auto-flow: row;
                grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr));
                width: 100%;
            }

            .card-body.students-list-body {
                padding-right: .75rem;
                padding-left: .75rem;
            }

            .student-list-row {
                grid-template-columns: 1.5rem minmax(0, 1fr) auto;
                padding: .75rem;
            }

            .student-details-grid,
            .student-action-list {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
@endsection


@section('content')
    @include('layouts.status-msn')

    {{-- Encabezado --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-semibold text-primary">
                        Dashboard clase: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
                    </h4>
                    <p class="mb-0 text-black"><small>{{ $infoClase }} </small></p>
                </div>
                <div class="text-md-end text-start">
                    <span class="badge bg-label-info fs-6 mb-1">Total matriculados: {{ $totalAlumnos }}</span>
                    @php
                        $hombresCount = $conteoGenero['hombres'] ?? 0;
                        $mujeresCount = $conteoGenero['mujeres'] ?? 0;
                        $otrosCount = $conteoGenero['otros'] ?? 0;
                        $hombresPct = $totalAlumnos > 0 ? round(($hombresCount / $totalAlumnos) * 100) : 0;
                        $mujeresPct = $totalAlumnos > 0 ? round(($mujeresCount / $totalAlumnos) * 100) : 0;
                    @endphp
                    <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2 mt-1">
                        <span class="badge bg-label-primary fs-7">
                            <i class="mdi mdi-gender-male me-1"></i>{{ $hombresCount }} Hombres ({{ $hombresPct }}%)
                        </span>
                        <span class="badge bg-label-danger fs-7">
                            <i class="mdi mdi-gender-female me-1"></i>{{ $mujeresCount }} Mujeres ({{ $mujeresPct }}%)
                        </span>
                        @if ($otrosCount > 0)
                            <span class="badge bg-label-secondary fs-7">
                                {{ $otrosCount }} Otros
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('contenido.paginas.escuelas.maestros.nav-modulo')

    {{-- Fila 1 de Gráficos: Asistencia Semanal del Periodo (Ancho Completo) --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Niveles de asistencia por semana</h5>
                    <span class="badge bg-label-primary">Periodo completo</span>
                </div>
                <div class="card-body">
                    <div id="attendanceTrendChart" class="chart-container"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fila 2 de Gráficos: Aprobación y Ranking de Alumnos (2 Columnas col-lg-6) --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6 col-md-12 col-12">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estado de aprobación (general)</h5>
                </div>
                <div class="card-body">
                    <div id="approvalStatusChart" class="chart-container"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center pb-2">
                    <div>
                        <h5 class="card-title mb-0">Ranking de calificaciones</h5>
                        <small class="text-muted">Nota más alta a la más baja</small>
                    </div>
                    @if (!empty($alumnosRanking))
                        <span class="badge bg-label-primary rounded-pill">{{ count($alumnosRanking) }} alumnos</span>
                    @endif
                </div>
                <div class="card-body p-0" style="max-height: 380px; min-height: 380px; overflow-y: auto;">
                    @if (!empty($alumnosRanking) && count($alumnosRanking) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle mb-0">
                                <tbody>
                                    @foreach ($alumnosRanking as $index => $itemAlumno)
                                        @php
                                            $posicion = $index + 1;
                                            $nota = (float) ($itemAlumno['promedio_final_materia'] ?? 0);
                                            $aprobado = $itemAlumno['ha_aprobado'] ?? false;
                                            $estado = $itemAlumno['estado_materia'] ?? 'Cursando';
                                            $nombres = explode(' ', $itemAlumno['nombre_completo'] ?? '');
                                            $iniciales = !empty($nombres[0]) ? strtoupper(substr($nombres[0], 0, 1)) : '';
                                            if (count($nombres) > 1 && !empty($nombres[1])) {
                                                $iniciales .= strtoupper(substr($nombres[count($nombres) - 1], 0, 1));
                                            } elseif (strlen($iniciales) == 1 && strlen($nombres[0]) > 1) {
                                                $iniciales .= strtoupper(substr($nombres[0], 1, 1));
                                            } else {
                                                $iniciales = !empty($iniciales) ? $iniciales : 'NN';
                                            }

                                            $badgePosicionClass = match ($posicion) {
                                                1 => 'bg-warning text-white shadow-sm',
                                                2 => 'bg-secondary text-white shadow-sm',
                                                3 => 'bg-label-warning text-warning border border-warning',
                                                default => 'bg-label-secondary text-muted'
                                            };

                                            $badgeNotaClass = match (true) {
                                                $estado === 'Bloqueado' => 'bg-label-secondary',
                                                $aprobado => 'bg-label-success',
                                                default => 'bg-label-danger'
                                            };

                                            $porcentajeBarra = min(100, max(0, ($nota / 5.0) * 100));
                                            $colorBarra = $aprobado ? 'bg-success' : ($estado === 'Bloqueado' ? 'bg-secondary' : 'bg-danger');
                                        @endphp
                                        <tr class="border-bottom">
                                            <td class="ps-3 pe-1 py-2 text-center" style="width: 36px;">
                                                <span class="badge rounded-circle d-inline-flex align-items-center justify-content-center {{ $badgePosicionClass }}" style="width: 24px; height: 24px; font-size: 11px; font-weight: 700;">
                                                    {{ $posicion }}
                                                </span>
                                            </td>
                                            <td class="py-2 pe-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-xs me-2 flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-primary fs-6">{{ $iniciales }}</span>
                                                    </div>
                                                    <div class="overflow-hidden" style="max-width: 140px;">
                                                        <span class="d-block fw-semibold text-dark text-truncate small" title="{{ $itemAlumno['nombre_completo'] }}">
                                                            {{ $itemAlumno['nombre_completo'] }}
                                                        </span>
                                                        <div class="progress mt-1" style="height: 4px; width: 100%;">
                                                            <div class="progress-bar {{ $colorBarra }}" role="progressbar" style="width: {{ $porcentajeBarra }}%;" aria-valuenow="{{ $nota }}" aria-valuemin="0" aria-valuemax="5"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end pe-3 py-2" style="width: 80px;">
                                                <span class="badge {{ $badgeNotaClass }} fw-bold fs-7">
                                                    {{ number_format($nota, 2) }}
                                                </span>
                                                <small class="d-block text-muted" style="font-size: 10px;">{{ $estado }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="d-flex justify-content-center align-items-center h-100 p-4 text-center">
                            <div>
                                <i class="mdi mdi-account-group-outline mdi-36px text-muted"></i>
                                <p class="text-muted mt-2 mb-0 small">No hay calificaciones registradas.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Alumnos --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header students-list-toolbar">
                    <h5 class="card-title mb-0"><i class="mdi mdi-account-details-outline me-2"></i>Resumen de
                        calificaciones</h5>
                    @if ($cortesDefinidos->isNotEmpty())
                        <div class="students-export-actions" role="group" aria-label="Exportar notas por corte">
                            @foreach ($cortesDefinidos as $corte)
                                <a href="{{ route('maestros.exportarNotasCorte', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado, 'cortePeriodo' => $corte['id_db']]) }}"
                                    class="btn btn-sm btn-outline-success">
                                    <i class="ti ti-file-spreadsheet me-1"></i> Excel {{ $corte['nombre'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="card-body students-list-body">
                    @if ($alumnosParaDashboard->isNotEmpty())
                        <div class="students-list" style="--cut-count: {{ max(1, $cortesDefinidos->count()) }};">
                        <div class="students-list-header" aria-hidden="true">
                            <div>#</div>
                            <div>Nombre del alumno</div>
                            @if ($cortesDefinidos->isNotEmpty())
                                @foreach ($cortesDefinidos as $corte)
                                    <div>
                                        {{ $corte['nombre'] }}
                                        <small class="d-block text-muted">({{ number_format($corte['porcentaje_materia'], 0) }}%)</small>
                                    </div>
                                @endforeach
                            @else
                                <div>Cortes</div>
                            @endif
                            <div>Asist.</div>
                            <div>Inasist.</div>
                            <div>Prom. final</div>
                            <div>Estado</div>
                            <div>Acciones</div>
                        </div>

                        @foreach ($alumnosParaDashboard as $alumno)
                            <article class="student-item-card card mb-2 {{ $loop->even ? 'bg-body-secondary' : '' }}">
                                <div class="student-list-row">
                                    <div class="student-position">{{ $loop->iteration }}</div>
                                    <div class="student-identity">
                                        <div class="avatar avatar-xs">
                                                    @php
                                                        $nombres = explode(' ', $alumno['nombre_completo']);
                                                        $iniciales = !empty($nombres[0])
                                                            ? strtoupper(substr($nombres[0], 0, 1))
                                                            : '';
                                                        if (count($nombres) > 1 && !empty($nombres[1])) {
                                                            $iniciales .= strtoupper(
                                                                substr($nombres[count($nombres) - 1], 0, 1),
                                                            );
                                                        } elseif (strlen($iniciales) == 1 && strlen($nombres[0]) > 1) {
                                                            $iniciales .= strtoupper(substr($nombres[0], 1, 1));
                                                        } else {
                                                            $iniciales = !empty($iniciales) ? $iniciales : 'NN';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="avatar-initial rounded-circle bg-label-secondary">{{ $iniciales }}</span>
                                        </div>
                                        <div class="student-name">
                                            <div class="fw-medium">{{ $alumno['nombre_completo'] }}</div>
                                            @if (isset($alumno['user_model']->identificacion))
                                                <small class="text-muted">ID: {{ $alumno['user_model']->identificacion }}</small>
                                            @endif
                                            @if ($alumno['ultimo_traslado'])
                                                <a href="javascript:void(0);"
                                                    onclick="abrirDetalleTraslado({{ $alumno['ultimo_traslado']->id }})"
                                                    class="student-transfer badge bg-label-info rounded-pill"
                                                    data-bs-toggle="tooltip" title="Ver detalle del traslado">
                                                    Traslado <i class="ti ti-chevron-down"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <button class="student-toggle" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#studentDetails_{{ $alumno['id_db'] }}_{{ $loop->iteration }}"
                                        aria-expanded="false"
                                        aria-controls="studentDetails_{{ $alumno['id_db'] }}_{{ $loop->iteration }}"
                                        aria-label="Mostrar calificaciones de {{ $alumno['nombre_completo'] }}">
                                        <i class="ti ti-chevron-down"></i>
                                    </button>

                                    <div class="student-details collapse"
                                        id="studentDetails_{{ $alumno['id_db'] }}_{{ $loop->iteration }}">
                                        <div class="student-details-grid">
                                                @if ($cortesDefinidos->isNotEmpty())
                                                    @foreach ($cortesDefinidos as $corteLoop)
                                                        <div class="student-metric">
                                                            <span class="student-metric-label">{{ $corteLoop['nombre'] }}</span>
                                                            {{ $alumno['promedios_por_corte'][$corteLoop['id_html']] ?? '0.00' }}
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="student-metric">
                                                        <span class="student-metric-label">Cortes</span>
                                                        N/A
                                                    </div>
                                                @endif

                                                <div class="student-metric">
                                                    <span class="student-metric-label">Asistencias</span>
                                                    {{ $alumno['asistencias'] }}
                                                </div>
                                                <div class="student-metric">
                                                    <span class="student-metric-label">Inasistencias</span>
                                                    {{ $alumno['inasistencias'] }}
                                                </div>
                                                <div class="student-metric">
                                                    <span class="student-metric-label">Promedio final</span>
                                                    <span
                                                        class="fw-bold {{ $alumno['ha_aprobado'] ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($alumno['promedio_final_materia'] ?? 0, 2) }}
                                                    </span>
                                                </div>
                                                <div class="student-metric student-status">
                                                    <span class="student-metric-label">Estado</span>
                                                    <span
                                                        class="badge text-white {{ $alumno['ha_aprobado'] ? ($alumno['estado_materia'] === 'Aprobado' ? 'bg-success' : 'bg-warning') : 'bg-danger' }}">
                                                        {{ $alumno['estado_materia'] }}
                                                    </span>
                                                </div>
                                                <div class="student-action-list">
                                                    <a
                                                        href="{{ route('maestros.gestionarAlumno', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado, 'alumno' => $alumno['user_model']]) }}"
                                                        class="btn btn-sm btn-outline-primary rounded-pill"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Ver perfil del alumno">
                                                        Perfil
                                                    </a>
                                                    @can('escuelas.bloquear_matricula')
                                                        @if (! $alumno['matricula_model']->bloqueado && $horarioAsignado->materiaPeriodo->periodo->estado)
                                                            <form
                                                                action="{{ route('maestros.bloquearMatricula', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado, 'matricula' => $alumno['matricula_model']]) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('¿Deseas bloquear la matrícula de este alumno? Al finalizar el período quedará reprobado aunque cumpla las notas o asistencias.');">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger rounded-pill">
                                                                    Bloquear
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                        </div>
                    @else
                        {{-- El estado vacío se mantiene igual que en tu código original --}}
                        <div class="text-center p-5">
                            @if (isset($configuracion) && $configuracion->logotipo_claro)
                                <img src="{{ asset('storage/configuracion/' . $configuracion->logotipo_claro) }}"
                                    alt="No hay alumnos" height="120" class="mb-3">
                            @else
                                <i class="mdi mdi-account-multiple-outline mdi-48px text-muted mb-3"></i>
                            @endif
                            <h5 class="text-muted mt-2">No hay alumnos matriculados</h5>
                            <p class="text-muted mb-0">Aún no hay alumnos inscritos en esta clase o no se encontraron
                                datos.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@livewire('matricula.detalle-traslado-modal')
@endsection

@push('scripts')
    <script>
        function abrirDetalleTraslado(logId) {
            Livewire.dispatch('abrirModalDetalleTraslado', {
                logId: logId
            });
        }
        document.addEventListener('DOMContentLoaded', function() {


            // Tu código JavaScript para ApexCharts y tooltips se mantiene aquí sin cambios...
            const getColor = (variable, fallbackColor = '#8592a3') => {
                const colorValue = getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
                return colorValue || fallbackColor;
            };
            const headingColor = getColor('--bs-heading-color', '#566a7f');
            const legendColor = getColor('--bs-secondary-color', '#8592a3');
            const borderColor = getColor('--bs-border-color', '#dce1e5');
            const primaryColor = getColor('--bs-primary', '#696cff');
            const infoColor = getColor('--bs-info', '#03c3ec');
            const secondaryColor = getColor('--bs-secondary', '#8592a3');
            const successColor = getColor('--bs-success', '#71dd37');
            const dangerColor = getColor('--bs-danger', '#ff3e1d');
            const warningColor = getColor('--bs-warning', '#ffab00');
            const approvalChartEl = document.querySelector('#approvalStatusChart');
            if (approvalChartEl) {
                const approvalData = @json($datosAprobacion);
               const hasApprovalData = approvalData?.series?.[0]?.data?.some(d => d > 0);
                if (hasApprovalData) {
                    const approvalChartConfig = {
                        series: approvalData.series,
                        chart: {
                            type: 'bar',
                            height: 380,
                            toolbar: {
                                show: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '45%',
                                borderRadius: 5,
                                startingShape: 'rounded',
                                endingShape: 'rounded',
                                distributed: true
                            }
                        },
                        colors: [successColor, dangerColor, warningColor, secondaryColor],
                        dataLabels: {
                            enabled: true,
                            offsetY: -20,
                            style: {
                                fontSize: '12px',
                                colors: [headingColor]
                            },
                            formatter: function(val) {
                                return val > 0 ? val : '';
                            }
                        },
                        xaxis: {
                           categories: approvalData.categorias,
                            labels: { style: { colors: legendColor, fontSize: '13px' } },
                            axisBorder: { show: false }, axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: legendColor,
                                    fontSize: '13px'
                                },
                                formatter: function(val) {
                                    return val.toFixed(0);
                                }
                            },
                            title: {
                                text: 'Número de alumnos',
                                style: {
                                    color: headingColor,
                                    fontSize: '13px',
                                    fontWeight: 500
                                }
                            }
                        },
                        grid: {
                            show: true,
                            borderColor: borderColor,
                            strokeDashArray: 3,
                            padding: {
                                top: 0,
                                bottom: -8,
                                left: -10,
                                right: 0
                            }
                        },
                        legend: {
                            show: false
                        },
                        responsive: [{
                            breakpoint: 576,
                            options: {
                                chart: {
                                    height: 320
                                },
                                plotOptions: {
                                    bar: {
                                        columnWidth: '65%'
                                    }
                                },
                                dataLabels: {
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            }
                        }]
                    };
                    const approvalChart = new ApexCharts(approvalChartEl, approvalChartConfig);
                    approvalChart.render();
                } else {
                    approvalChartEl.innerHTML =
                        `<div class="d-flex justify-content-center align-items-center h-100 text-center"><div><i class="mdi mdi-chart-bar mdi-48px text-muted"></i><p class="text-muted mt-2 mb-0">No hay datos de aprobación.</p></div></div>`;
                }
            }

            // --- GRÁFICO 3: NIVELES DE ASISTENCIA SEMANAL (RANGO DEL PERIODO) ---
            const attendanceChartEl = document.querySelector('#attendanceTrendChart');
            if (attendanceChartEl) {
                const attendanceData = @json($datosAsistenciaSemanal);
                const hasAttendanceData = attendanceData && attendanceData.categorias && attendanceData.categorias.length > 0;

                if (hasAttendanceData) {
                    const attendanceChartConfig = {
                        series: attendanceData.series,
                        chart: {
                            type: 'area',
                            height: 380,
                            toolbar: {
                                show: false
                            },
                            dropShadow: {
                                enabled: true,
                                opacity: 0.08,
                                blur: 4,
                                left: 0,
                                top: 2
                            }
                        },
                        colors: [successColor, dangerColor],
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: [3, 2]
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: [0.45, 0.25],
                                opacityTo: [0.05, 0.05],
                                stops: [0, 90, 100]
                            }
                        },
                        markers: {
                            size: 4,
                            strokeWidth: 2,
                            hover: {
                                size: 6
                            }
                        },
                        xaxis: {
                            categories: attendanceData.categorias,
                            labels: {
                                style: {
                                    colors: legendColor,
                                    fontSize: '11px'
                                },
                                rotate: -30,
                                rotateAlways: false
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: legendColor,
                                    fontSize: '12px'
                                },
                                formatter: function(val) {
                                    return Math.round(val);
                                }
                            },
                            title: {
                                text: 'Número de alumnos',
                                style: {
                                    color: headingColor,
                                    fontSize: '13px',
                                    fontWeight: 500
                                }
                            },
                            min: 0
                        },
                        grid: {
                            borderColor: borderColor,
                            strokeDashArray: 3,
                            padding: {
                                top: 0,
                                bottom: -8,
                                left: 10,
                                right: 10
                            }
                        },
                        legend: {
                            show: true,
                            position: 'top',
                            horizontalAlign: 'right',
                            labels: {
                                colors: legendColor
                            },
                            markers: {
                                width: 10,
                                height: 10,
                                offsetX: -3
                            }
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: function(val) {
                                    return val !== null && val !== undefined ? `${val} alumnos` : 'Sin datos';
                                }
                            }
                        },
                        responsive: [{
                            breakpoint: 576,
                            options: {
                                chart: {
                                    height: 320
                                },
                                xaxis: {
                                    labels: {
                                        rotate: -45,
                                        style: {
                                            fontSize: '10px'
                                        }
                                    }
                                }
                            }
                        }]
                    };
                    const attendanceChart = new ApexCharts(attendanceChartEl, attendanceChartConfig);
                    attendanceChart.render();
                } else {
                    attendanceChartEl.innerHTML =
                        `<div class="d-flex justify-content-center align-items-center h-100 text-center"><div><i class="mdi mdi-calendar-blank-outline mdi-48px text-muted"></i><p class="text-muted mt-2 mb-0">No hay semanas configuradas para este periodo.</p></div></div>`;
                }
            }

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });


        });
    </script>
@endpush
