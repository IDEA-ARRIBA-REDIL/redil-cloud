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

        .student-details-row .col-md-1 {
            /* Para forzar un ancho más consistente en escritorio para las columnas de detalles */
            flex-basis: auto;
            /* Permite que col-md-1 funcione como se espera */
        }

        /* Estilo para la columna # en escritorio */
        .col-md-auto-custom {
            flex: 0 0 auto;
            width: auto;
            max-width: 50px;
            /* Ajusta según sea necesario */
        }

        /* Ajustes para el botón de acordeón */
        .accordion-toggle-btn {
            font-size: 1.2rem;
            /* Tamaño del icono +/- */
            padding: 0.25rem 0.5rem;
            /* Padding más pequeño */
        }


        .title-encabezado {
            font-size: 11px !important;
        }

        #col-btn-perfil {
            margin-left: 5%;
        }

        @media (max-width: 575.98px) {
            #col-btn-perfil {
                margin-left: 1% !important;
            }

            .border-top-row {
                border-top: solid !important;
            }

            .border-top-row .col-12 {
                border-bottom: solid 1px;
                padding-top: 4px;
                padding-bottom: 4px;
                padding-left: 10%;
                border-color: #e1e1e1;
            }

            .student-details-row {
                margin-top: 20px !important
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
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <h5 class="card-title mb-0"><i class="mdi mdi-account-details-outline me-2"></i>Resumen de
                        calificaciones</h5>
                    @if ($cortesDefinidos->isNotEmpty())
                        <div class="btn-group" role="group" aria-label="Exportar notas por corte">
                            @foreach ($cortesDefinidos as $corte)
                                <a href="{{ route('maestros.exportarNotasCorte', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado, 'cortePeriodo' => $corte['id_db']]) }}"
                                    class="btn btn-sm btn-outline-success">
                                    <i class="ti ti-file-spreadsheet me-1"></i> Excel {{ $corte['nombre'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if ($alumnosParaDashboard->isNotEmpty())
                        {{-- INICIO DE LA SECCIÓN MODIFICADA --}}

                        {{-- Encabezados para la vista de escritorio (md y superior) --}}
                        {{-- Estos encabezados deben reflejar las columnas de la sección de detalles --}}
                        <div class="row d-none d-md-flex fw-bold mb-3  pb-2 align-items-center">
                            <div style="width:20px;" class="col-md-1 text-center">#</div>
                            <div class="title-encabezado col-md-3">Nombre del alumno</div>
                            {{-- Los siguientes encabezados corresponden a lo que estará DENTRO del acordeón en móvil --}}
                            @if ($cortesDefinidos->isNotEmpty())
                                @foreach ($cortesDefinidos as $corte)
                                    <div class="col-md-1 text-center title-encabezado">
                                        {{ $corte['nombre'] }}
                                        <small
                                            class="d-block text-muted">({{ number_format($corte['porcentaje_materia'], 0) }}%)</small>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-md-1 text-center">Cortes</div>
                            @endif
                            <div class="title-encabezado col-md-1 text-center">Asist.</div>
                            <div class="title-encabezado col-md-1 text-center">Inasist.</div>
                            <div class="title-encabezado col-md-1 text-center">Prom. final</div>
                            <div class="title-encabezado col-md-1 text-center">Estado</div>
                            <div class="title-encabezado col-md-1 text-center"></div>
                        </div>

                        @foreach ($alumnosParaDashboard as $alumno)
                            <div style="@if ($loop->iteration % 2 == 0) background-color: #f3f3f3; @endif"
                                class="student-item-card card mb-2  ">
                                <div style="min-height:70px" class="card-body py-2 px-3">
                                    {{-- Fila para Nombre y Botón de Acordeón (móvil) --}}
                                    <div class="row align-items-center py-2">
                                        <div style="width:20px;" class="col-md-1 d-none d-md-block"> {{-- # Visible solo en MD+ --}}
                                            {{ $loop->iteration }}
                                        </div>
                                        <div style="width:350px;" class="col-12 col-md-3"> {{-- Nombre del alumno --}}
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
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
                                                <div>

                                                    <div class="fw-medium">{{ $alumno['nombre_completo'] }}</div>
                                                    @if (isset($alumno['user_model']->identificacion))
                                                        <small class="text-muted d-md-block">ID:
                                                            {{ $alumno['user_model']->identificacion }}</small>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto col-md-8 ms-auto d-md-none"> {{-- Botón de Acordeón visible solo en móvil --}}
                                            <button style="margin-top:-70px" class="btn" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#studentDetails_{{ $alumno['id_db'] }}_{{ $loop->iteration }}"
                                                aria-expanded="false">
                                                <i class="ti ti-circle-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Sección Colapsable para Detalles --}}
                                    {{-- `collapse` la hace colapsable, `d-md-block` la muestra como bloque en MD+ --}}
                                    <div style="margin-top:-60px !important" class="row collapse d-md-block"
                                        id="studentDetails_{{ $alumno['id_db'] }}_{{ $loop->iteration }}">

                                        <div class="pt-2 pt-md-0">
                                            {{-- Fila interna para alinear detalles con encabezados de escritorio --}}
                                            {{-- En móvil (cuando está expandido), estos se apilarán o distribuirán según sus clases 'col-X' --}}
                                            <div class="row  gy-2 student-details-row align-items-center  pb-3">
                                                {{-- Espaciadores para alinear con encabezados en escritorio --}}
                                                <div style="width:20px;" class="col-md-1 d-none d-md-block">

                                                </div>
                                                <div class="col-md-3  d-md-block">
                                                    @if ($alumno['ultimo_traslado'])
                                                        <a style="margin-top:50px !important;font-size: 10px;"
                                                            href="javascript:void(0);"
                                                            onclick="abrirDetalleTraslado({{ $alumno['ultimo_traslado']->id }})"
                                                            class="badge bg-label-info rounded-pill ms-6 mt-6"
                                                            data-bs-toggle="tooltip" title="Ver detalle del traslado">
                                                            Traslado <i class="ti ti-chevron-down"></i>
                                                        </a>
                                                    @endif

                                                </div>

                                                {{-- Columnas de Detalles Reales --}}
                                                @if ($cortesDefinidos->isNotEmpty())
                                                    @foreach ($cortesDefinidos as $corteLoop)
                                                        <div
                                                            class="col-12 col-md-1 text-start @if ($alumno['ultimo_traslado']) mt-n5 @endif ">
                                                            <strong class="d-md-none">{{ $corteLoop['nombre'] }}:
                                                            </strong>
                                                            {{ $alumno['promedios_por_corte'][$corteLoop['id_html']] ?? '0.00' }}
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div
                                                        class="col-12 col-md-1  text-start  @if ($alumno['ultimo_traslado']) mt-n5 @endif ">
                                                        <strong class="d-md-none">Cortes: </strong>N/A
                                                    </div>
                                                @endif

                                                <div
                                                    class="col-12 col-md-1  text-start  @if ($alumno['ultimo_traslado']) mt-n5 @endif ">
                                                    <strong class="d-md-none">Asistencias:
                                                    </strong>{{ $alumno['asistencias'] }}
                                                </div>
                                                <div
                                                    class="col-12 col-md-1   text-start  @if ($alumno['ultimo_traslado']) mt-n5 @endif ">
                                                    <strong class="d-md-none">Inasistencias:
                                                    </strong>{{ $alumno['inasistencias'] }}
                                                </div>
                                                <div
                                                    class="col-12 col-md-1 text-start @if ($alumno['ultimo_traslado']) mt-n5 @endif ">
                                                    <strong class="d-md-none">Promedio final: </strong>
                                                    <span
                                                        class="fw-bold {{ $alumno['ha_aprobado'] ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($alumno['promedio_final_materia'] ?? 0, 2) }}
                                                    </span>
                                                </div>
                                                <div
                                                    class="col-12 col-md-1 text-start @if ($alumno['ultimo_traslado']) mt-n5 @endif ">
                                                    <strong class="d-md-none">Estado: </strong>
                                                    <span
                                                        class="badge text-white {{ $alumno['ha_aprobado'] ? ($alumno['estado_materia'] === 'Aprobado' ? 'bg-success' : 'bg-warning') : 'bg-danger' }}">
                                                        {{ $alumno['estado_materia'] }}
                                                    </span>
                                                </div>
                                                <div id="col-btn-perfil"
                                                    class="col-12 col-md-1  text-start  @if ($alumno['ultimo_traslado']) mt-n5 @endif">

                                                    <a style="color:#1977E5"
                                                        href="{{ route('maestros.gestionarAlumno', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado, 'alumno' => $alumno['user_model']]) }}"
                                                        class="btn btn-outline-secondary rounded-pill"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Ver perfil del alumno">
                                                        Perfil

                                                    </a>
                                                    @can('escuelas.bloquear_matricula')
                                                        @if (! $alumno['matricula_model']->bloqueado && $horarioAsignado->materiaPeriodo->periodo->estado)
                                                            <form class="mt-1"
                                                                action="{{ route('maestros.bloquearMatricula', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado, 'matricula' => $alumno['matricula_model']]) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('¿Deseas bloquear la matrícula de este alumno? Al finalizar el período quedará reprobado aunque cumpla las notas o asistencias.');">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="btn btn-outline-danger rounded-pill">
                                                                    Bloquear
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        {{-- FIN DE LA SECCIÓN MODIFICADA --}}
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


            // Código para cambiar el ícono del botón de acordeón
            var collapseStudentDetailElements = document.querySelectorAll(
                '[id^="studentDetails_"]'); // Selecciona todos los colapsables de estudiantes
            collapseStudentDetailElements.forEach(function(collapseEl) {
                var button = document.querySelector('[data-bs-target="#' + collapseEl.id + '"]');
                if (button) {
                    var iconElement = button.querySelector('.icon-toggle');

                    collapseEl.addEventListener('show.bs.collapse', function() {
                        if (iconElement) iconElement.textContent = '-';
                        // Puedes añadir una clase a la card-body para cambiar el fondo si está expandido
                        // this.closest('.card-body').classList.add('expanded-student');
                    });

                    collapseEl.addEventListener('hide.bs.collapse', function() {
                        if (iconElement) iconElement.textContent = '+';
                        // this.closest('.card-body').classList.remove('expanded-student');
                    });
                }
            });


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
