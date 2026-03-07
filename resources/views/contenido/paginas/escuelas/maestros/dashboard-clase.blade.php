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
        .module-nav-link {
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

    {{-- Encabezado (sin cambios) --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-semibold text-primary">
                        Dashboard clase: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
                    </h4>
                    <p class="mb-0 text-black"><small>{{ $infoClase }} </small></p>
                </div>
                <span class="badge bg-label-info fs-6">Total matriculados: {{ $totalAlumnos }}</span>
            </div>
        </div>
    </div>

    {{-- Barra de Navegación (sin cambios) --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card mb-0 p-0 border-0 shadow-sm">
                <ul class="nav nav-pills nav-fill justify-content-start flex-column flex-md-row gap-1 px-2 py-1">
                    @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_dashboard_general'))
                    <li class="nav-item">
                        <a href="{{ route('maestros.dashboardClase', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                            class="nav-link module-nav-link p-3 waves-effect waves-light {{ request()->routeIs('maestros.dashboardClase') ? 'active' : '' }}">
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
                    @if(isset($rolActivo))
                    <li class="nav-item">
                        <a href="{{ route('maestros.gestionarItems', ['horarioAsignado' => $horarioAsignado]) }}"
                            class="nav-link module-nav-link p-3 waves-effect waves-light">
                            <i class="mdi mdi-list-box-outline me-1"></i> Gestionar Items
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
                </ul>
            </div>
        </div>
    </div>

    {{-- Gráficos (sin cambios) --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6 col-md-12">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Distribución por género</h5>
                </div>
                <div class="card-body">
                    <div id="genderDistributionChart" class="chart-container"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estado de aprobación (general)</h5>
                </div>
                <div class="card-body">
                    <div id="approvalStatusChart" class="chart-container"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Alumnos --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="mdi mdi-account-details-outline me-2"></i>Resumen de
                        calificaciones</h5>
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
    @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_grilla'))
    <li class="nav-item">
        <a href="{{ route('maestros.calificacionGrilla', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
            class="nav-link module-nav-link p-3 waves-effect waves-light">
            <i class="mdi mdi-grid me-1"></i> Calificación Grilla
        </a>
    </li>
    @endif
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

            const genderChartEl = document.querySelector('#genderDistributionChart');
            if (genderChartEl) {
                const genderData = @json($datosGenero);
                const hasValidGenderData = genderData && genderData.series && genderData.series[0] &&
                    genderData.series[0].data &&
                    genderData.series[0].data.reduce((a, b) => a + b, 0) > 0;
                if (hasValidGenderData) {
                    const genderChartConfig = {
                        series: genderData.series[0].data,
                        labels: genderData.categorias,
                        chart: {
                            type: 'pie',
                            height: 380,
                            toolbar: {
                                show: false
                            }
                        },
                        colors: [primaryColor, infoColor, secondaryColor],
                        dataLabels: {
                            enabled: true,
                            formatter: function(val, opts) {
                                const count = opts.w.globals.series[opts.seriesIndex];
                                const label = opts.w.globals.labels[opts.seriesIndex];
                                return `${label}: ${count} (${val.toFixed(1)}%)`;
                            },
                            style: {
                                fontSize: '12px',
                                colors: ['#333']
                            },
                            dropShadow: {
                                enabled: true,
                                top: 1,
                                left: 1,
                                blur: 1,
                                color: '#000',
                                opacity: 0.3
                            }
                        },
                        legend: {
                            show: true,
                            position: 'bottom',
                            horizontalAlign: 'center',
                            labels: {
                                colors: legendColor,
                                useSeriesColors: false
                            },
                            markers: {
                                width: 10,
                                height: 10,
                                offsetX: -5,
                                offsetY: 0
                            },
                            itemMargin: {
                                horizontal: 10,
                                vertical: 5
                            }
                        },
                        stroke: {
                            width: 2,
                            colors: [getColor('--bs-body-bg', '#ffffff')]
                        },
                        tooltip: {
                            fillSeriesColor: true,
                            y: {
                                formatter: function(value, {
                                    seriesIndex,
                                    w
                                }) {
                                    const label = w.globals.labels[seriesIndex];
                                    return `${label}: ${value}`;
                                }
                            }
                        },
                        responsive: [{
                            breakpoint: 576,
                            options: {
                                chart: {
                                    height: 320
                                },
                                legend: {
                                    position: 'bottom',
                                    itemMargin: {
                                        vertical: 2
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
                    const genderChart = new ApexCharts(genderChartEl, genderChartConfig);
                    genderChart.render();
                } else {
                    genderChartEl.innerHTML =
                        `<div class="d-flex justify-content-center align-items-center h-100 text-center"><div><i class="mdi mdi-gender-male-female mdi-48px text-muted"></i><p class="text-muted mt-2 mb-0">No hay datos de género.</p></div></div>`;
                }
            }

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

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });


        });
    </script>
@endpush
