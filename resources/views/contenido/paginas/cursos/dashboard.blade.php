@extends('layouts/layoutMaster')

@section('title', 'Dashboard de Cursos')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker();
            $('.select2').select2();

            // Definición manual del español para evitar problemas de carga de archivos
            flatpickr.l10ns.es = {
                weekdays: {
                    shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov',
                        'Dic'
                    ],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                        'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ],
                },
                ordinal: () => {
                    return 'º';
                },
                firstDayOfWeek: 1, // Lunes inicia la semana
                rangeSeparator: ' a ',
                time_24hr: true,
            };

            const fp = flatpickr(".flatpickr-range", {
                mode: "range",
                dateFormat: "Y-m-d",
                locale: "es",
                onChange: function(selectedDates, dateStr, instance) {
                    if (document.getElementById('filtro_rapido')) {
                        document.getElementById('filtro_rapido').value = "";
                    }
                }
            });

            // Función para el filtro rápido de fechas
            window.seleccionarRango = function(tipo) {
                if (!tipo) return;

                let inicio, fin;
                const hoy = new Date();

                if (tipo === 'este_mes') {
                    inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                    fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
                } else if (tipo === 'mes_pasado') {
                    inicio = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
                    fin = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
                } else if (tipo === 'este_ano') {
                    inicio = new Date(hoy.getFullYear(), 0, 1);
                    fin = new Date(hoy.getFullYear(), 11, 31);
                } else if (tipo === 'trimestre_actual') {
                    const mesActual = hoy.getMonth();
                    const inicioMesTrimestre = Math.floor(mesActual / 3) * 3;
                    inicio = new Date(hoy.getFullYear(), inicioMesTrimestre, 1);
                    fin = new Date(hoy.getFullYear(), inicioMesTrimestre + 3, 0);
                }

                const fpInstance = document.querySelector("#rango_fechas")._flatpickr;
                if (fpInstance) {
                    fpInstance.setDate([inicio, fin], true);
                }
            };
            // Datos de Sexo (0=Masculino, 1=Femenino)
            const generoData = @json($datosGenero);
            const generoSeries = generoData.map(item => item.total);
            const generoLabels = generoData.map(item => item.genero == 0 ? 'Masculino' : (item.genero == 1 ?
                'Femenino' : 'Otro'));

            if (generoSeries.length > 0) {
                const optionsSexo = {
                    chart: {
                        type: 'donut',
                        height: 300,
                        toolbar: {
                            show: false
                        }
                    },
                    series: generoSeries,
                    labels: generoLabels,
                    colors: ['#2196F3', '#E91E63', '#FFC107'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true
                    }
                };
                new ApexCharts(document.querySelector("#chartSexo"), optionsSexo).render();
            }

            // Datos de Roles
            const rolesData = @json($datosRoles);
            const rolesLabels = rolesData.map(item => item.rol);
            const rolesSeries = [{
                data: rolesData.map(item => item.total)
            }];

            if (rolesData.length > 0) {
                const optionsRoles = {
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: false
                        }
                    },
                    series: rolesSeries,
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true
                        }
                    },
                    xaxis: {
                        categories: rolesLabels
                    },
                    colors: ['#673AB7']
                };
                new ApexCharts(document.querySelector("#chartRoles"), optionsRoles).render();
            }

            // Datos de Entidades Apilados por Carrera
            const stackEntidadData = @json($datosCarrerasEntidades);

            if (stackEntidadData.labels.length > 0) {
                const optionsEntidades = {
                    chart: {
                        type: 'bar',
                        height: 350,
                        stacked: true,
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '50%',
                            borderRadius: 4,
                            dataLabels: {
                                total: {
                                    enabled: true,
                                    style: {
                                        fontSize: '13px',
                                        fontWeight: 900
                                    }
                                }
                            }
                        },
                    },
                    xaxis: {
                        categories: stackEntidadData.labels,
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left'
                    },
                    fill: {
                        opacity: 1
                    },
                    series: stackEntidadData.series,
                    colors: ['#00BCD4', '#673AB7', '#FF9800', '#E91E63', '#4CAF50', '#FFC107']
                };
                new ApexCharts(document.querySelector("#chartEntidades"), optionsEntidades).render();
            }

            // Datos por Curso
            const cursosData = @json($inscritosPorCurso);
            const cursosLabels = cursosData.map(item => item.nombre);
            const cursosSeries = [{
                name: 'Inscritos',
                data: cursosData.map(item => item.total)
            }];

            if (cursosData.length > 0) {
                const optionsCursos = {
                    chart: {
                        type: 'bar',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    series: cursosSeries,
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '45%'
                        }
                    },
                    xaxis: {
                        categories: cursosLabels
                    },
                    colors: ['#FF9800'],
                    title: {
                        text: 'Total inscritos por curso',
                        align: 'center'
                    }
                };
                new ApexCharts(document.querySelector("#chartCursos"), optionsCursos).render();
            }

            // Gráficos Detallados por Curso (Acordeón)
            @foreach ($cursosDetalle as $curso)
                // Sexo para Curso {{ $curso->id }}
                const genData{{ $curso->id }} = @json($curso->stats_genero);
                if (genData{{ $curso->id }}.length > 0) {
                    new ApexCharts(document.querySelector("#chartSexo-{{ $curso->id }}"), {
                        chart: {
                            type: 'donut',
                            height: 200,
                            toolbar: {
                                show: false
                            }
                        },
                        series: genData{{ $curso->id }}.map(i => i.total),
                        labels: genData{{ $curso->id }}.map(i => i.genero == 0 ? 'Masculino' : (i
                            .genero == 1 ? 'Femenino' : 'Otro')),
                        colors: ['#2196F3', '#E91E63', '#FFC107'],
                        legend: {
                            position: 'bottom',
                            fontSize: '10px'
                        },
                        dataLabels: {
                            enabled: false
                        }
                    }).render();
                }

                // Roles para Curso {{ $curso->id }}
                const rolData{{ $curso->id }} = @json($curso->stats_roles);
                if (rolData{{ $curso->id }}.length > 0) {
                    new ApexCharts(document.querySelector("#chartRoles-{{ $curso->id }}"), {
                        chart: {
                            type: 'bar',
                            height: 200,
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                            data: rolData{{ $curso->id }}.map(i => i.total)
                        }],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                horizontal: true
                            }
                        },
                        xaxis: {
                            categories: rolData{{ $curso->id }}.map(i => i.rol)
                        },
                        colors: ['#673AB7'],
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '10px'
                            }
                        }
                    }).render();
                }

                // Entidades para Curso {{ $curso->id }} (Stacked Style)
                const entidadData{{ $curso->id }} = @json($curso->stats_entidades);
                if (entidadData{{ $curso->id }}.length > 0) {
                    const seriesDetalle{{ $curso->id }} = entidadData{{ $curso->id }}.map(item => ({
                        name: item.entidad,
                        data: [item.total]
                    }));

                    new ApexCharts(document.querySelector("#chartEntidades-{{ $curso->id }}"), {
                        chart: {
                            type: 'bar',
                            height: 250,
                            stacked: true,
                            toolbar: {
                                show: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '40%',
                                borderRadius: 4,
                                dataLabels: {
                                    total: {
                                        enabled: true,
                                        style: {
                                            fontSize: '11px',
                                            fontWeight: 900
                                        }
                                    }
                                }
                            },
                        },
                        series: seriesDetalle{{ $curso->id }},
                        xaxis: {
                            categories: ['Inscritos'],
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'center',
                            fontSize: '10px'
                        },
                        colors: ['#00BCD4', '#673AB7', '#FF9800', '#E91E63', '#4CAF50', '#FFC107'],
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '10px'
                            }
                        }
                    }).render();
                }
            @endforeach
        });
    </script>
@endsection

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-primary fw-semibold">Cursos Dashboard Administrativo</span>
    </h4>

    <!-- Barra de Filtros -->
    <form action="{{ route('cursos.dashboard') }}" method="GET">
        <div class="row bg-white rounded-3 p-0 m-0 mb-4 shadow-sm border border-gray">
            <div class="row col-12 col-md-11 p-0 m-0">

                <!-- Rango Predefinido -->
                <div class="col-12 col-md-3 border-end border-gray p-0 d-flex">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-transparent border-none"><i
                                class="ti ti-calendar text-black"></i></span>
                        <select class="form-select text-black border-none" id="filtro_rapido"
                            onchange="seleccionarRango(this.value)">
                            <option value="">Rango de fecha</option>
                            <option value="este_mes">Este mes</option>
                            <option value="mes_pasado">Mes pasado</option>
                            <option value="este_ano">Este año</option>
                            <option value="trimestre_actual">Trimestre actual</option>
                        </select>
                    </div>
                </div>

                <!-- Flatpickr Range -->
                <div class="col-12 col-md-3 border-end border-gray p-0 d-flex">
                    <input type="text" class="form-control border-none flatpickr-range text-center" id="rango_fechas"
                        name="rango_fechas" value="{{ $rangoFechas }}" placeholder="DD/MM/AAAA - DD/MM/AAAA">
                </div>

                <!-- Select2 Carrera -->
                <div class="col-12 col-md-6 border-end border-gray p-0 d-flex align-items-center">
                    <select name="carrera_id[]" class="select2 border-none form-select w-100" data-allow-clear="true"
                        data-placeholder="Todas las Carreras" multiple>
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id }}"
                                {{ (is_array($carreraId) && in_array($carrera->id, $carreraId)) || $carreraId == $carrera->id ? 'selected' : '' }}>
                                {{ $carrera->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <!-- Botón Filtrar -->
            <div class="col-12 col-md-1 p-0">
                <button type="submit"
                    class="btn btn-xl btn-primary w-100 rounded-0 rounded-end h-100 px-auto fs-6">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- KPIs Generales -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-black shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="mb-0 text-uppercase mb-0 fw-semibold text-primary">{{ $totalInscritos }}</h4>
                        <small>Total inscritos</small>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('cursos.exportar-inscritos', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'carrera_id' => $carreraId]) }}"
                            class="btn btn-sm btn-white text-primary border-primary" title="Exportar a Excel">
                            <i class="ti ti-file-spreadsheet fs-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card  text-black shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h4 class="mb-0 text-uppercase mb-0 fw-semibold text-primary">{{ $promedioAvance }}%</h4>
                        <small>Progreso promedio global</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card  text-black shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h4 class="mb-0 text-uppercase mb-0 fw-semibold text-primary">{{ $totalCompletados }}</h4>
                        <small>Cursos completados (100%)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Globales -->
    <div class="row mb-5">
        <div class=" col-md-6 col-sm-12 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Sexo (Global)</h5>
                </div>
                <div class="card-body">
                    <div id="chartSexo"></div>
                    @if (count($datosGenero) == 0)
                        <div class="text-center text-muted py-5">Sin datos</div>
                    @endif
                </div>
            </div>
        </div>
        <div class=" col-md-6 col-sm-12 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Roles (Global)</h5>
                </div>
                <div class="card-body">
                    <div id="chartRoles"></div>
                    @if (count($datosRoles) == 0)
                        <div class="text-center text-muted py-5">Sin datos</div>
                    @endif
                </div>
            </div>
        </div>
        <div class=" col-md-6 col-sm-12 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Usuarios por organización (Global)</h5>
                </div>
                <div class="card-body">
                    <div id="chartEntidades"></div>
                    @if (count($datosCarrerasEntidades['labels']) == 0)
                        <div class="text-center text-muted py-5">Sin datos</div>
                    @endif
                </div>
            </div>
        </div>
        <!-- Ranking General de Inscritos -->
        <div class="col-md-6 col-sm-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Ranking general: Inscripciones por Curso</h5>
                </div>
                <div class="card-body">
                    <div id="chartCursos"></div>
                    @if (count($inscritosPorCurso) == 0)
                        <div class="text-center text-muted py-5">Sin datos</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-4 text-primary fw-semibold">Desglose detallado por curso</h5>

    <!-- Acordeón de Cursos -->
    <div class="accordion accordion-flush shadow-sm rounded" id="accordionCursos">
        @forelse($cursosDetalle as $curso)
            <div class="accordion-item border mb-2 rounded">
                <h2 class="accordion-header" id="heading-{{ $curso->id }}">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse-{{ $curso->id }}" aria-expanded="false">
                        <div class="d-flex justify-content-between w-100 me-3">
                            <span>{{ $curso->nombre }}</span>
                            <div class="d-flex gap-2">
                                <span class="badge bg-label-info">{{ $curso->stats_count }} Inscritos</span>
                                <span class="badge bg-label-success">{{ $curso->stats_completados }} Finalizados</span>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapse-{{ $curso->id }}" class="accordion-collapse collapse"
                    data-bs-parent="#accordionCursos">
                    <div class="accordion-body ">
                        <!-- KPIs del Curso -->
                        <div class="row g-3 my-4">
                            <div class="col-md-4">
                                <div class="card card-body p-3 shadow-none border">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar  p-2 me-3"><i class="ti ti-user-check"></i></div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-0 text-uppercase mb-0 fw-semibold text-primary">{{ $curso->stats_count }}</h5>
                                            <small class="text-black">Inscritos</small>
                                        </div>
                                        <div class="ms-auto text-end">
                                            <a href="{{ route('cursos.exportar-inscritos', ['curso' => $curso->id, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Exportar Inscritos de este curso">
                                                <i class="ti ti-file-spreadsheet fs-5"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-body p-3 shadow-none border">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar  p-2 me-3"><i class="ti ti-trending-up"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 text-uppercase mb-0 fw-semibold text-primary">{{ $curso->stats_progreso }}%</h5>
                                            <small class="text-black">Avance Promedio</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-body p-3 shadow-none border">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar  p-2 me-3"><i class="ti ti-certificate"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 text-uppercase mb-0 fw-semibold text-primary">{{ $curso->stats_completados }}</h5>
                                            <small class="text-black">Completado 100%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráficos del Curso -->
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card card-body shadow-none border">
                                    <h6 class="fw-bold mb-3 border-bottom pb-2">Distribución por Sexo</h6>
                                    <div id="chartSexo-{{ $curso->id }}"></div>
                                    @if (count($curso->stats_genero) == 0)
                                        <div class="text-center text-black py-4 small">Sin datos demográficos</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card card-body shadow-none border">
                                    <h6 class="fw-bold mb-3 border-bottom pb-2">Distribución por Roles</h6>
                                    <div id="chartRoles-{{ $curso->id }}"></div>
                                    @if (count($curso->stats_roles) == 0)
                                        <div class="text-center text-black py-4 small">Sin datos de roles</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card card-body shadow-none border">
                                    <h6 class="fw-bold mb-3 border-bottom pb-2">Distribución por organización</h6>
                                    <div id="chartEntidades-{{ $curso->id }}"></div>
                                    @if (count($curso->stats_entidades) == 0)
                                        <div class="text-center text-black py-4 small">Sin datos de organizaciones</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info text-black">No se encontraron cursos con los filtros seleccionados.</div>
        @endforelse
    </div>


@endsection
