@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Inscripción exitosa')

<!-- Page -->
@section('page-style')
    @vite([
     'resources/assets/vendor/scss/pages/page-profile.scss',
     'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
     'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
      'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
      'resources/assets/vendor/libs/apex-charts/apexcharts.js',
      'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
    ])
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
            colorAsistencias: {
                series1: '#29dac7',
                series2: '#f56954'
            }
        };


        // Grafico de asistencias
        const asistenciasGrafico = document.querySelector('#graficoAsistencia'),
            seriesAsistencias = JSON.parse(<?php print json_encode(json_encode([ $reporte->cantidad_asistencias , $reporte->cantidad_inasistencias ])); ?>),
            labelsAsistencias = JSON.parse(<?php print json_encode(json_encode(['Asistieron', 'No asistieron'])); ?>),
            asistenciasConfig = {
                chart: {
                  height: 300,
                  type: 'donut',
                },
                labels: labelsAsistencias,
                series: seriesAsistencias,
                colors: [
                    chartColors.colorAsistencias.series1,
                    chartColors.colorAsistencias.series2,
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
                                    fontSize: '1.2rem',
                                    fontFamily: 'Public Sans'
                                },
                                value: {
                                    fontSize: '1rem',
                                    color: legendColor,
                                    fontFamily: 'Public Sans',
                                    formatter: function(val) {
                                        if(val > 1){
                                          return parseInt(val, 10) + ' Personas';
                                        }
                                        else{
                                          return parseInt(val, 10) + ' Persona';
                                        }
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
        if (typeof asistenciasGrafico !== undefined && asistenciasGrafico !== null) {
            const tiposSexos = new ApexCharts(asistenciasGrafico, asistenciasConfig);
            tiposSexos.render();
        }
        // Grafico de asistencias
    </script>

@endsection

@section('content')

<div class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-6 offset-lg-3 d-flex align-items-center">
                <div class=" mx-auto my-auto text-center">

                    <div id="graficoAsistencia" class="my-5"></div>

                    <h2 class="text-black fw-bold mb-0 lh-sm">Reporte finalizado</h2>
                    <p class="text-black mt-1 mb-5">
                      Tu reporte de grupo ha sido finalizado con éxito.
                    </p>

                    <div class="row text-start mt-5 shadow rounded p-4">

                      <p class="col-12 fw-semibold text-black mb-2"> Información </p>

                      <div class="col-6 d-flex flex-column">
                        <small class="text-black">Se realizó el grupo</small>
                        <small class="fw-semibold text-black ">{{ $reporte->no_reporte ? 'No' : 'Si' }}</small>
                      </div>
                      <div class="col-6 d-flex flex-column">
                        <small class="text-black">Fecha</small>
                        <small class="fw-semibold text-black ">{{ $reporte->fecha }}</small>
                      </div>

                      <div class="col-12">
                        <hr class="my-3 border-2">
                      </div>

                      <div class="col-12 d-flex flex-column">
                        <small class="text-black">Tema</small>
                        <small class="fw-semibold text-black ">{{ $reporte->tema }}</small>
                      </div>

                      <div class="col-12">
                        <hr class="my-3 border-2">
                      </div>

                      @if( $reporte->informacion_encargado_grupo && count($reporte->informacion_encargado_grupo) < 2  )
                      <div class="col-12 d-flex flex-column">
                        <small class="text-black"> Asitiste al grupo</small>
                        @foreach ($reporte->informacion_encargado_grupo as $encargado)
                        <small class="fw-semibold text-black ">{{ $encargado['asistio'] ? 'Si' : 'No'  }}</small>
                        @endforeach
                        <hr class="my-3 border-2">
                      </div>
                      @else
                      <div class="col-12 d-flex flex-column">
                        <small class="text-black"> Asitiste al grupo</small>
                        @foreach ($reporte->informacion_encargado_grupo as $encargado)
                        <small class="fw-semibold text-black ">{{ $encargado['asistio'] ? 'Si' : 'No'  }} - {{ $encargados->firstWhere('id', '6') ? $encargados->firstWhere('id', '6')->nombre(3) : 'No encontrado' }}</small>
                        @endforeach
                        <hr class="my-3 border-2">
                      </div>
                      @endif

                      @foreach ($reporte->clasificaciones as $clasificacion)
                      <div class="col-6 d-flex flex-column">
                        <small class="text-black">{{ $clasificacion->nombre }}</small>
                        <small class="fw-semibold text-black ">{{ $clasificacion->pivot->cantidad }}</small>
                        <hr class="my-3 border-2">
                      </div>
                      @endforeach

                      @foreach ($ofrendasGenericas as $ofrenda)
                      <div class="col-12 d-flex flex-column">
                        <small class="text-black">{{ $ofrenda->tipoOfrenda->nombre }}</small>
                        <small class="fw-semibold text-black ">$ {{ $ofrenda->valor }} {{ $moneda->nombre_corto }}</small>
                        <hr class="my-3 border-2">
                      </div>
                      @endforeach

                      <div class="col-12 d-flex flex-column">
                        <small class="text-black">Total</small>
                        <small class="fw-semibold text-black ">$ {{ $reporte->totalOfrendas() }} {{ $moneda->nombre_corto }}</small>
                        <hr class="my-3 border-2">
                      </div>
                    </div>

                    <div class="d-grid gap-2 d-sm-flex justify-content-center my-5">
                      <a href="{{ route('grupo.lista') }}" type="button" class="btn btn-primary rounded-pill px-10 py-3" >
                        <span class="align-middle me-sm-1 me-0 ">Salir</span>
                      </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
