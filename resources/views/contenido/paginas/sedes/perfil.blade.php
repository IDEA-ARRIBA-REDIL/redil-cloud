@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Sedes')

<!-- Page -->
@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
    <script type="module">
        let cardColor, headingColor, labelColor, borderColor, legendColor;

        if (isDarkStyle) {
            cardColor = config.colors_dark.cardColor;
            headingColor = config.colors_dark.headingColor;
            labelColor = config.colors_dark.textMuted;
            legendColor = config.colors_dark.bodyColor;
            borderColor = config.colors_dark.borderColor;
        } else {
            cardColor = config.colors.cardColor;
            headingColor = config.colors.headingColor;
            labelColor = config.colors.textMuted;
            legendColor = config.colors.bodyColor;
            borderColor = config.colors.borderColor;
        }

        const chartColors = {
            column: {
                series1: '#826af9',
                series2: '#d2b0ff',
                bg: '#f8d3f9'
            },
            donut: {
                series1: '#fee802',
                series2: '#3fd0bd',
                series3: '#826bf8',
                series4: '#2b9bf4',
                series5: '#f56954',
                series6: '#d2b0ff',
                series7: '#00a65a"',
                series9: '#f56900',
                series10: '#d2b050',

            },
            area: {
                series1: '#29dac7',
                series2: '#60f2ca',
                series3: '#a5f8cd'
            },
            sexo: {
                series1: '#2b9bf4',
                series2: '#826bf8'
            }
        };

        // grafico crecimiento de grupos
        const graficoCrecimientoGrupos = document.querySelector('#graficoCrecimientoGrupos'),
            dataCrecimientoGrupos = JSON.parse(<?php print json_encode(json_encode($dataCrecimientoGrupos)); ?>),
            serieCrecimientoGrupos = JSON.parse(<?php print json_encode(json_encode($serieCrecimientoGrupos)); ?>),
            graficoCrecimientoGruposConfig = {
                series: [{
                    name: "Grupos",
                    data: dataCrecimientoGrupos
                }],
                chart: {
                    height: 300,
                    type: 'line',
                    zoom: {
                        enabled: true
                    },
                    parentHeightOffset: 0,
                    toolbar: {
                        show: true,
                        offsetX: -20,
                        offsetY: 0,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: false,
                            reset: true | '<img src="/static/icons/reset.png" width="20">',
                            customIcons: []
                        },
                        export: {
                            svg: {
                                filename: 'Gráfico_crecimiento_{{ $sede->nombre }}',
                            },
                            csv: {
                                filename: 'Gráfico_crecimiento_{{ $sede->nombre }}',
                            },
                            png: {
                                filename: 'Gráfico_crecimiento_{{ $sede->nombre }}',
                            }
                        },
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                markers: {
                    size: 5
                },
                grid: {
                    borderColor: borderColor,
                    xaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                colors: [chartColors.area.series1],
                xaxis: {
                    categories: serieCrecimientoGrupos,
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            colors: labelColor,
                            fontSize: '13px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    labels: {
                        formatter: function(val) {
                            return val.toFixed(0)
                        },
                        style: {
                            colors: labelColor,
                            fontSize: '13px'
                        }
                    }
                },
                fill: {
                    opacity: 1,
                    type: 'solid'
                },
                tooltip: {
                    shared: false
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'start',
                    labels: {
                        colors: legendColor,
                        useSeriesColors: false
                    }
                },
            };
        if (typeof graficoCrecimientoGrupos !== undefined && graficoCrecimientoGrupos !== null) {
            var areaChartCrecimientoGrupos = new ApexCharts(graficoCrecimientoGrupos, graficoCrecimientoGruposConfig);
            areaChartCrecimientoGrupos.render();
        }
        // grafico crecimiento de grupos

        // grafico crecimiento de personas
        const graficoCrecimientoPersonas = document.querySelector('#graficoCrecimientoPersonas'),
            dataCrecimientoPersonas = JSON.parse(<?php print json_encode(json_encode($dataCrecimientoPersonas)); ?>),
            serieCrecimientoPersonas = JSON.parse(<?php print json_encode(json_encode($serieCrecimientoPersonas)); ?>),
            graficoCrecimientoPersonasConfig = {
                series: [{
                    name: "Personas",
                    data: dataCrecimientoPersonas
                }],
                chart: {
                    height: 300,
                    type: 'line',
                    zoom: {
                        enabled: true
                    },
                    parentHeightOffset: 0,
                    toolbar: {
                        show: true,
                        offsetX: -20,
                        offsetY: 0,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: false,
                            reset: true | '<img src="/static/icons/reset.png" width="20">',
                            customIcons: []
                        },
                        export: {
                            svg: {
                                filename: 'Gráfico_crecimiento_personas_{{ $sede->nombre }}',
                            },
                            csv: {
                                filename: 'Gráfico_crecimiento_personas_{{ $sede->nombre }}',
                            },
                            png: {
                                filename: 'Gráfico_crecimiento_personas_{{ $sede->nombre }}',
                            }
                        },
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                markers: {
                    size: 5
                },
                grid: {
                    borderColor: borderColor,
                    xaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                colors: [chartColors.area.series1],
                xaxis: {
                    categories: serieCrecimientoPersonas,
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            colors: labelColor,
                            fontSize: '13px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    labels: {
                        formatter: function(val) {
                            return val.toFixed(0)
                        },
                        style: {
                            colors: labelColor,
                            fontSize: '13px'
                        }
                    }
                },
                fill: {
                    opacity: 1,
                    type: 'solid'
                },
                tooltip: {
                    shared: false
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'start',
                    labels: {
                        colors: legendColor,
                        useSeriesColors: false
                    }
                },
            };
        if (typeof graficoCrecimientoPersonas !== undefined && graficoCrecimientoPersonas !== null) {
            var areaChartCrecimientoPersonas = new ApexCharts(graficoCrecimientoPersonas, graficoCrecimientoPersonasConfig);
            areaChartCrecimientoPersonas.render();
        }
        // grafico crecimiento de personas

        // Grafico por edades
        const rangoEdadesGrafico = document.querySelector('#rangoEdades'),
            seriesRangoEdades = JSON.parse(<?php print json_encode(json_encode($seriesRangoEdades)); ?>),
            labelsRangoEdades = JSON.parse(<?php print json_encode(json_encode($labelsRangoEdades)); ?>),
            rangoEdadesConfig = {
                chart: {
                    height: 390,
                    type: 'donut',
                    toolbar: {
                        show: true,
                        offsetX: -20,
                        offsetY: 0,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: false,
                            reset: true | '<img src="/static/icons/reset.png" width="20">',
                            customIcons: []
                        },
                        export: {
                            svg: {
                                filename: 'Gráfico_edades_{{ $sede->nombre }}',
                            },
                            csv: {
                                filename: 'Gráfico_edades_{{ $sede->nombre }}',
                            },
                            png: {
                                filename: 'Gráfico_edades_{{ $sede->nombre }}',
                            }
                        },
                    },
                },
                labels: labelsRangoEdades,
                series: seriesRangoEdades,
                colors: [
                    chartColors.donut.series1,
                    chartColors.donut.series2,
                    chartColors.donut.series3,
                    chartColors.donut.series4,
                    chartColors.donut.series5,
                    chartColors.donut.series6,
                    chartColors.donut.series7,
                    chartColors.donut.series8,
                    chartColors.donut.series9,
                    chartColors.donut.series10,
                ],
                stroke: {
                    show: false,
                    curve: 'straight'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opt) {
                        return parseInt(val, 10) + '%';
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom',
                    markers: {
                        offsetX: -3
                    },
                    itemMargin: {
                        vertical: 3,
                        horizontal: 10
                    },
                    labels: {
                        colors: legendColor,
                        useSeriesColors: false
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                name: {
                                    fontSize: '2rem',
                                    fontFamily: 'Public Sans'
                                },
                                value: {
                                    fontSize: '1.2rem',
                                    color: legendColor,
                                    fontFamily: 'Public Sans',
                                    formatter: function(val) {
                                        return parseInt(val, 10) + '%';
                                    }
                                }
                            }
                        }
                    }
                },
                responsive: [{
                        breakpoint: 992,
                        options: {
                            chart: {
                                height: 380
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    colors: legendColor,
                                    useSeriesColors: false
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 576,
                        options: {
                            chart: {
                                height: 320
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            show: true,
                                            name: {
                                                fontSize: '1.5rem'
                                            },
                                            value: {
                                                fontSize: '1rem'
                                            },
                                            total: {
                                                fontSize: '1.5rem'
                                            }
                                        }
                                    }
                                }
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    colors: legendColor,
                                    useSeriesColors: false
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 420,
                        options: {
                            chart: {
                                height: 280
                            },
                            legend: {
                                show: false
                            }
                        }
                    },
                    {
                        breakpoint: 360,
                        options: {
                            chart: {
                                height: 250
                            },
                            legend: {
                                show: false
                            }
                        }
                    }
                ]
            };
        if (typeof rangoEdadesGrafico !== undefined && rangoEdadesGrafico !== null) {
            const rangoEdades = new ApexCharts(rangoEdadesGrafico, rangoEdadesConfig);
            rangoEdades.render();
        }
        // Grafico por edades

        // Grafico por tipos de usuario
        const tiposUsuariosGrafico = document.querySelector('#tiposDeUsuarios'),
            seriesTiposUsuarios = JSON.parse(<?php print json_encode(json_encode($seriesTiposUsuarios)); ?>),
            labelsTiposUsuarios = JSON.parse(<?php print json_encode(json_encode($labelsTiposUsuarios)); ?>),
            tiposUsuariosConfig = {
                chart: {
                    height: 390,
                    type: 'donut',
                    toolbar: {
                        show: true,
                        offsetX: -20,
                        offsetY: 0,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: false,
                            reset: true | '<img src="/static/icons/reset.png" width="20">',
                            customIcons: []
                        },
                        export: {
                            svg: {
                                filename: 'Gráfico_tipos_usuarios_{{ $sede->nombre }}',
                            },
                            csv: {
                                filename: 'Gráfico_tipos_usuarios_{{ $sede->nombre }}',
                            },
                            png: {
                                filename: 'Gráfico_tipos_usuarios_{{ $sede->nombre }}',
                            }
                        },
                    },
                },
                labels: labelsTiposUsuarios,
                series: seriesTiposUsuarios,
                colors: [
                    chartColors.donut.series1,
                    chartColors.donut.series2,
                    chartColors.donut.series3,
                    chartColors.donut.series4,
                    chartColors.donut.series5,
                    chartColors.donut.series6,
                    chartColors.donut.series7,
                    chartColors.donut.series8,
                    chartColors.donut.series9,
                    chartColors.donut.series10,
                ],
                stroke: {
                    show: false,
                    curve: 'straight'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opt) {
                        return parseInt(val, 10) + '%';
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom',
                    markers: {
                        offsetX: -3
                    },
                    itemMargin: {
                        vertical: 3,
                        horizontal: 10
                    },
                    labels: {
                        colors: legendColor,
                        useSeriesColors: false
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                name: {
                                    fontSize: '1.5rem',
                                    fontFamily: 'Public Sans'
                                },
                                value: {
                                    fontSize: '1.2rem',
                                    color: legendColor,
                                    fontFamily: 'Public Sans',
                                    formatter: function(val) {
                                        return parseInt(val, 10) + '%';
                                    }
                                }
                            }
                        }
                    }
                },
                responsive: [{
                        breakpoint: 992,
                        options: {
                            chart: {
                                height: 380
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    colors: legendColor,
                                    useSeriesColors: false
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 576,
                        options: {
                            chart: {
                                height: 320
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            show: true,
                                            name: {
                                                fontSize: '1.5rem'
                                            },
                                            value: {
                                                fontSize: '1rem'
                                            },
                                            total: {
                                                fontSize: '1.5rem'
                                            }
                                        }
                                    }
                                }
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    colors: legendColor,
                                    useSeriesColors: false
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 420,
                        options: {
                            chart: {
                                height: 280
                            },
                            legend: {
                                show: false
                            }
                        }
                    },
                    {
                        breakpoint: 360,
                        options: {
                            chart: {
                                height: 250
                            },
                            legend: {
                                show: false
                            }
                        }
                    }
                ]
            };
        if (typeof tiposUsuariosGrafico !== undefined && tiposUsuariosGrafico !== null) {
            const tiposUsuarios = new ApexCharts(tiposUsuariosGrafico, tiposUsuariosConfig);
            tiposUsuarios.render();
        }
        // Grafico por tipos de usuario

        // Grafico por sexos
        const tiposSexosGrafico = document.querySelector('#tiposDeSexos'),
            seriesTiposSexos = JSON.parse(<?php print json_encode(json_encode($seriesTiposSexos)); ?>),
            labelsTiposSexos = JSON.parse(<?php print json_encode(json_encode($labelsTiposSexos)); ?>),
            tiposSexosConfig = {
                chart: {
                    height: 390,
                    type: 'donut',
                    toolbar: {
                        show: true,
                        offsetX: -20,
                        offsetY: 0,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: false,
                            reset: true | '<img src="/static/icons/reset.png" width="20">',
                            customIcons: []
                        },
                        export: {
                            svg: {
                                filename: 'Gráfico_sexos_{{ $sede->nombre }}',
                            },
                            csv: {
                                filename: 'Gráfico_sexos_{{ $sede->nombre }}',
                            },
                            png: {
                                filename: 'Gráfico_sexos_{{ $sede->nombre }}',
                            }
                        },
                    },
                },
                labels: labelsTiposSexos,
                series: seriesTiposSexos,
                colors: [
                    chartColors.sexo.series1,
                    chartColors.sexo.series2,
                ],
                stroke: {
                    show: false,
                    curve: 'straight'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opt) {
                        return parseInt(val, 10) + '%';
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom',
                    markers: {
                        offsetX: -3
                    },
                    itemMargin: {
                        vertical: 3,
                        horizontal: 10
                    },
                    labels: {
                        colors: legendColor,
                        useSeriesColors: false
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                name: {
                                    fontSize: '1.5rem',
                                    fontFamily: 'Public Sans'
                                },
                                value: {
                                    fontSize: '1.2rem',
                                    color: legendColor,
                                    fontFamily: 'Public Sans',
                                    formatter: function(val) {
                                        return parseInt(val, 10) + '%';
                                    }
                                }
                            }
                        }
                    }
                },
                responsive: [{
                        breakpoint: 992,
                        options: {
                            chart: {
                                height: 380
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    colors: legendColor,
                                    useSeriesColors: false
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 576,
                        options: {
                            chart: {
                                height: 320
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            show: true,
                                            name: {
                                                fontSize: '1.5rem'
                                            },
                                            value: {
                                                fontSize: '1rem'
                                            },
                                            total: {
                                                fontSize: '1.5rem'
                                            }
                                        }
                                    }
                                }
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    colors: legendColor,
                                    useSeriesColors: false
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 420,
                        options: {
                            chart: {
                                height: 280
                            },
                            legend: {
                                show: false
                            }
                        }
                    },
                    {
                        breakpoint: 360,
                        options: {
                            chart: {
                                height: 250
                            },
                            legend: {
                                show: false
                            }
                        }
                    }
                ]
            };
        if (typeof tiposSexosGrafico !== undefined && tiposSexosGrafico !== null) {
            const tiposSexos = new ApexCharts(tiposSexosGrafico, tiposSexosConfig);
            tiposSexos.render();
        }
        // Grafico por sexos
    </script>

    <script type="module">
        $('.confirmacionEliminar').on('click', function() {
            let nombre = $(this).data('nombre');
            let id = $(this).data('id');

            Swal.fire({
                title: "¿Estás seguro que deseas eliminar a <b>" + nombre + "</b>?",
                html: "Esta acción no es reversible.",
                icon: "warning",
                showCancelButton: false,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#eliminarSede').attr('action', "/sede/" + id + "/eliminar");
                    $('#eliminarSede').submit();
                }
            })
        });
    </script>
@endsection

@section('content')

    <!-- Header -->
    <div class="row">
      <div class="col-12">
        <div class="card mb-6">
          <div class="user-profile-header-banner">
            <img src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento. '/img/sedes/banners/' . $sede->foto) : Storage::url($configuracion->ruta_almacenamiento. '/img/sedes/banners/' . $sede->foto)}}" alt="Banner image" class="rounded-top">
          </div>
          <div class="user-profile-header d-flex flex-column flex-md-row text-sm-start text-center mb-5">
            <div class="flex-shrink-0 mt-n2 mx-0 mx-auto">
              <div class="card rounded-pill bg-primary  icon-card text-center mb-0 mx-3 p-2">
                <div class="card-body text-white"> <i class="ti ti-building ti-xl"></i>
                </div>
              </div>
            </div>
            <div class="flex-grow-1 mt-3 mt-md-5">
              <div class="d-flex align-items-md-end align-items-md-start align-items-center justify-content-md-between justify-content-start mx-2 flex-md-row flex-column gap-4">
                <div class="user-profile-info">
                  <h4 class="mb-0 mt-md-4 fw-bold">{{ $sede->nombre }}</h4>
                  <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-md-start justify-content-center gap-4 my-0 mt-1">
                    <li class="list-inline-item d-flex gap-2 align-items-center">
                      <span class="fw-medium"> {{ $sede->tipo->nombre }}</span>
                    </li>
                  </ul>
                </div>
                <div id="divBotonOpciones" class="d-flex flex-row ">


                  <div class="dropdown">
                    <button type="button" class="btn btn-sm p-2 rounded-3 btn-outline-primary waves-effectdropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                      <span class="mx-1">Opciones</span>
                      <i class="pl-5 ti ti-edit"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      @if($rolActivo->hasPermissionTo('sedes.opcion_modificar_sede'))
                        <li><a class="dropdown-item" href="{{ route('sede.modificar', $sede)}}">Modificar</a></li>
                      @endif
                      <hr class="dropdown-divider">
                      @if($rolActivo->hasPermissionTo('sedes.opcion_eliminar_sede'))
                        <li><a class="dropdown-item confirmacionEliminar text-danger" data-nombre="{{ $sede->nombre }}" data-id="{{ $sede->id }}" href="javascript:;">Eliminar</a></li>
                      @endif
                    </ul>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Header -->

    @include('layouts.status-msn')

    <div id="div-principal" class="row">

        <div class="col-lg-6 col-md-6">

            <!-- Información principal -->
            <div class="card mb-4">
                <div class="card-header align-items-center">
                    <p class="card-text text-uppercase fw-bold"> Información principal </p>
                </div>
                <div class="card-body pb-3">
                    <ul class="list-unstyled mb-4 mt-2">

                        <li class="d-flex align-items-center mb-1">
                            <i class="ti ti-map"></i>
                            <span class="fw-medium mx-2 text-heading">Dirección: </span>
                            <span>{{ $sede->direccion ? $sede->direccion : 'Sin dato' }}</span>
                        </li>

                        <li class="d-flex align-items-center mb-1">
                            <i class="ti ti-phone"></i>
                            <span class="fw-medium mx-2 text-heading">Teléfono: </span>
                            <span>{{ $sede->telefono ? $sede->telefono : 'Sin dato' }}</span>
                        </li>

                        <li class="d-flex align-items-center mb-1">
                            <i class="ti ti-confetti"></i>
                            <span class="fw-medium mx-2 text-heading">Fecha de creación: </span>
                            <span>{{ $sede->fecha_creacion ? $sede->fecha_creacion : 'Sin dato' }}</span>
                        </li>

                        <li class="d-flex align-items-center mb-1">
                            <i class="ti ti-armchair"></i>
                            <span class="fw-medium mx-2 text-heading">Capacidad de sillas: </span>
                            <span>{{ $sede->capacidad ? $sede->capacidad : 'Sin dato' }}</span>
                        </li>

                        <li class="d-flex align-items-center mb-1">
                            <i class="ti ti-users-group"></i>
                            <span class="fw-medium mx-2 text-heading">Cantidad de grupos: </span>
                            <span>{{ $sede->grupos()->select('grupos.id')->count() }}</span>
                        </li>

                        <li class="d-flex align-items-center mb-1">
                            <i class="ti ti-users"></i>
                            <span class="fw-medium mx-2 text-heading">Cantidad de personas: </span>
                            <span>{{ $sede->usuarios()->select('users.id')->count() }}</span>
                        </li>

                        <li class="d-flex align-items-center mb-1">
                            <i class="ti ti-point"></i>
                            <span class="fw-medium mx-2 text-heading">Descripción: </span>
                            <span>{{ $sede->descripcion ? $sede->descripcion : 'Sin información' }}</span>
                        </li>

                    </ul>
                </div>
            </div>
            <!--/ Información principal -->

            <!-- Grupo y encargados -->
            <div class="card mb-4">
                <div class="card-header align-items-center">
                    <p class="card-text text-uppercase fw-bold"> GRUPO PRINCIPAL Y ENCARGADOS </p>
                </div>
                <div class="card-body pb-3">
                    <ul class="list-unstyled mb-0">
                        @if ($grupoPrincipal)
                            <small class="card-text text-uppercase">Grupo principal</small>

                            <li class="mb-1 mt-1 p-2 border rounded">
                                <div class="d-flex align-items-start">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar me-2">
                                            <i class="ti ti-users-group me-2 fs-1"></i>
                                        </div>
                                        <div class="me-2 ms-1">
                                            <h6 class="mb-0">{{ $grupoPrincipal->nombre }}</h6>
                                            <small class="text-black"><b>Tipo:</b>
                                                {{ $grupoPrincipal->tipoGrupo ? $grupoPrincipal->tipoGrupo->nombre : 'Sin información' }}</small>
                                        </div>
                                    </div>
                                    <div class="ms-auto pt-1">
                                        @if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos'))
                                            <a href="{{ route('grupo.perfil', $grupoPrincipal) }}" target="_blank"
                                                class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil"
                                                data-bs-original-title="Ver perfil del grupo">
                                                <i class="ti ti-id me-2 ti-sm"></i></a>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endif
                    </ul>
                    <ul class="list-unstyled mb-0 mt-3">
                        @if (count($sede->encargados()))
                            <small class="card-text text-uppercase">Encargados</small>
                            @foreach ($sede->encargados() as $encargado)
                                <li class="mb-1 mt-1 p-2 border rounded">
                                    <div class="d-flex align-items-start">
                                        <div class="d-flex align-items-start">
                                            <div class="avatar me-2">
                                                <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $encargado->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $encargado->foto }}"
                                                    alt="Avatar" class="rounded-circle" />
                                            </div>
                                            <div class="me-2 ms-1">
                                                <h6 class="mb-0">{{ $encargado->nombre }}</h6>
                                                <small class="text-black"><i
                                                        class="ti {{ $encargado->icono }} text-heading fs-6"></i>
                                                    {{ $encargado->tipo_usuario }}</small>
                                            </div>
                                        </div>

                                        <div class="ms-auto pt-1">
                                            @if ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos'))
                                                <a href="{{ route('usuario.perfil', $encargado) }}" target="_blank"
                                                    class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil"
                                                    data-bs-original-title="Ver perfil">
                                                    <i class="ti ti-user-check me-2 ti-sm"></i></a>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            <!--/ Grupo y encargados -->

            <!-- Crecimiento de grupos en el último año -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-uppercase mb-0 fw-bold">Crecimiento de grupos en el último año</h6>
                        <small class="text-black">
                            Esta gráfica muestra el crecimiento mensual de los grupos de la sede, durante los últimos 12
                            meses.
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="graficoCrecimientoGrupos"></div>

                </div>
            </div>
            <!-- /Crecimiento de grupos en el último año -->

            <!-- Crecimiento de personas en el último año -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-uppercase mb-0 fw-bold">Crecimiento de personas en el último año</h6>
                        <small class="text-black">
                            Esta gráfica muestra el crecimiento mensual de las personas de la sede, durante los últimos 12
                            meses.
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="graficoCrecimientoPersonas"></div>

                </div>
            </div>
            <!-- /Crecimiento de personas en el último año -->

        </div>

        <div class="col-lg-6 col-md-6">


            <!-- Grafico por edades -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-uppercase mb-0 fw-bold">Personas de la sede por rango de edad</h6>
                        <small class="text-black">
                            Esta gráfica muestra la cantidad de personas clasificándola según su edad.
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="rangoEdades"></div>

                    <div class="table-responsive text-nowrap mt-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="fw-bold text-center">Nombre</th>
                                    <th class="fw-bold text-center">Rango</th>
                                    <th class="fw-bold text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($rangoEdades as $rangoEdad)
                                    <tr>
                                        <td class="text-center">{{ $rangoEdad->nombre }}</td>
                                        <td class="text-center">{{ $rangoEdad->edad_minima }} a
                                            {{ $rangoEdad->edad_maxima }}</td>
                                        <td class="text-center">{{ $rangoEdad->cantidad }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- /Grafico por edades -->

            <!-- Grafico por tipo de usuario -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-uppercase mb-0 fw-bold">Personas de la sede por tipo usuario</h6>
                        <small class="text-black">
                            Esta gráfica muestra la cantidad de personas clasificándola según su tipo de usuario.
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="tiposDeUsuarios"></div>

                    <div class="table-responsive text-nowrap mt-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="fw-bold text-center">Nombre</th>
                                    <th class="fw-bold text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($tiposUsuarios as $tipoUsuario)
                                    <tr>
                                        <td class="text-center">{{ $tipoUsuario->nombre }}</td>
                                        <td class="text-center">{{ $tipoUsuario->cantidad }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- /Grafico por tipo de usuario -->

            <!-- Grafico por sexo -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-uppercase mb-0 fw-bold">Personas de la sede por sexo</h6>
                        <small class="text-black">
                            Esta gráfica muestra la cantidad de personas clasificándola según su sexo.
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="tiposDeSexos"></div>

                    <div class="table-responsive text-nowrap mt-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="fw-bold text-center">Nombre</th>
                                    <th class="fw-bold text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($tiposDeSexo as $tipoSexo)
                                    <tr>
                                        <td class="text-center">{{ $tipoSexo->nombre }}</td>
                                        <td class="text-center">{{ $tipoSexo->cantidad }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- /Grafico por sexo -->
        </div>

    </div>

    <form id="eliminarSede" method="POST" action="">
        @csrf
    </form>
@endsection
