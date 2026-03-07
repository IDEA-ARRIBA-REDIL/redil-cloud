@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Resumen')

@section('vendor-style')

@section('page-style')
<style>
  body {
    overflow-x: hidden;
  }
</style>
@endsection

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/apex-charts/apexcharts.js'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
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
            seriesAsistencias = JSON.parse(<?php print json_encode(json_encode([ $reporte->cantidad_asistencias ? $reporte->cantidad_asistencias : 0 , $reporte->cantidad_inasistencias ? $reporte->cantidad_inasistencias : 0 ])); ?>),
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

  <div class="col-12 min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
      <div class="col-3 text-start">
        <a href="{{ route('reporteGrupo.lista') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step">
          <span class="ti-xs ti ti-arrow-left me-2"></span>
          <span class="d-none d-md-block fw-normal">Volver</span>
        </a>
      </div>
      <div class="col-6 pl-5 text-center">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Resumen</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ route('dashboard')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
    </nav>

    <div class="pt-5 px-7 px-sm-0" style="padding-bottom: 100px;">

      <div class="row g-2">

          <!-- Grafico  -->
          <div class="col-12 col-lg-6 offset-lg-3 d-flex align-items-center">
              <div class=" mx-auto my-auto text-center">
                  <div id="graficoAsistencia" class="my-5"></div>
              </div>
          </div>
          <!-- /Grafico  -->

          <div class="col-12 col-lg-4 offset-lg-2 p-5">
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

              @if( $reporte->informacion_encargado_grupo )
                @if( count($reporte->informacion_encargado_grupo) < 2  )
                <div class="col-12 d-flex flex-column">
                  <small class="text-black"> ¿El encargado asistio al grupo?</small>
                  @foreach ($reporte->informacion_encargado_grupo as $encargado)
                  <small class="fw-semibold text-black ">{{ $encargado['asistio'] ? 'Si' : 'No'  }}</small>
                  @endforeach
                  <hr class="my-3 border-2">
                </div>
                @else
                <div class="col-12 d-flex flex-column">
                  <small class="text-black">  ¿Los encargados asistieron al grupo?</small>
                  @foreach ($reporte->informacion_encargado_grupo as $encargado)
                  <small class="fw-semibold text-black ">{{ $encargado['asistio'] ? 'Si' : 'No'  }} - {{ $encargados->firstWhere('id', '6') ? $encargados->firstWhere('id', '6')->nombre(3) : 'No encontrado' }}</small>
                  @endforeach
                  <hr class="my-3 border-2">
                </div>
                @endif
              @else
                <div class="col-12 d-flex flex-column">
                  <small class="text-black"> Asitiste al grupo</small>
                  <small class="fw-semibold text-black ">Sin encargados</small>

                  <hr class="my-3 border-2">
                </div>
              @endif

              <div class="col-12 d-flex flex-column">
                <small class="text-black">¿Se realizó el grupo?</small>
                <small class="fw-semibold text-black ">{{ $reporte->finalizado ? 'Si' : 'No' }} {{ $reporte->finalizado == false && $reporte->motivo ? '- '.$reporte->motivo->nombre : '' }} </small>
                <hr class="my-3 border-2">
              </div>

              <div class="col-12 d-flex flex-column">
                <small class="text-black">¿Finalizado?</small>
                <small class="fw-semibold text-black ">{{ $reporte->finalizado ? 'Si' : 'No' }}</small>
                <hr class="my-3 border-2">
              </div>

              <div class="col-12 d-flex flex-column">
                <small class="text-black">¿Estado aprobación?</small>
                <small class="fw-semibold text-black ">{{ $reporte->aprobado === null ? 'Sin revisar' : ( $reporte->aprobado ? 'Aprobado' : 'Corregido') }} {{ $reporte->aprobado === false && $reporte->motivoDesaprobacion ? '- '.$reporte->motivoDesaprobacion->nombre : '' }} </small>
                <small class="text-black">{{  $reporte->observacion_desaprobacion }}</small>
                <hr class="my-3 border-2">
              </div>

              @foreach ($reporte->clasificaciones as $clasificacion)
              <div class="col-6 d-flex flex-column">
                <small class="text-black">{{ $clasificacion->nombre }}</small>
                <small class="fw-semibold text-black ">{{ $clasificacion->pivot->cantidad }}</small>
                <hr class="my-3 border-2">
              </div>
              @endforeach
            </div>

            <div class="row text-start mt-5 shadow rounded p-4">

              <p class="col-12 fw-semibold text-black mb-2"> Información del grupo </p>

              <div class="col-6 d-flex flex-column">
                <small class="text-black">Grupo</small>
                <small class="fw-semibold text-black ">{{ $infoGrupo['nombre'] ? $infoGrupo['nombre']  : 'Sin informacion' }}</small>
                <hr class="my-3 border-2">
              </div>
              <div class="col-6 d-flex flex-column">
                <small class="text-black">Tipo de grupo</small>
                <small class="fw-semibold text-black ">{{
                  $infoGrupo['tipo_grupo']
                  ? $infoGrupo['tipo_grupo']
                  : 'Sin informacion' }}</small>
                  <hr class="my-3 border-2">
              </div>

              <div class="col-6 d-flex flex-column">
                <small class="text-black">Día de reunión</small>
                <small class="fw-semibold text-black ">{{
                  $infoGrupo['dia']
                  ? $infoGrupo['dia']
                  : 'Sin informacion' }}</small>
                  <hr class="my-3 border-2">
              </div>


              <div class="col-6 d-flex flex-column">
                <small class="text-black">Teléfono</small>
                <small class="fw-semibold text-black ">{{
                  $infoGrupo['telefono']
                  ? $infoGrupo['telefono']
                  : 'Sin informacion' }}</small>
                  <hr class="my-3 border-2">
              </div>

              <div class="col-6 d-flex flex-column">
                <small class="text-black">Dirección</small>
                <small class="fw-semibold text-black ">{{
                  $infoGrupo['direccion']
                  ? $infoGrupo['direccion']
                  : 'Sin informacion' }}</small>
                  <hr class="my-3 border-2">
              </div>

            </div>


            <div class="row text-start mt-5 shadow rounded p-4">

              <p class="col-12 fw-semibold text-black mb-2"> Resumen financiero </p>
              @foreach ($ofrendasGenericas as $ofrenda)
              <div class="col-12 d-flex flex-column">
                <small class="text-black">{{ $ofrenda->tipoOfrenda->nombre }}</small>
                <small class="fw-semibold text-black ">$ {{ number_format($ofrenda->valor, 2) }} {{ $moneda->nombre_corto }}</small>
                @if($reporte->aprobado !== null)
                <small class="fw-semibold text-black">$ {{ number_format($ofrenda->valor_real, 2) }} {{ $moneda->nombre_corto }} (Valor Real)</small>
                @endif
                <hr class="my-3 border-2">
              </div>
              @endforeach

              @foreach ($totalOfrendasNoGenericas as $ofrenda)
              <div class="col-12 d-flex flex-column">
                <small class="text-black">{{ $ofrenda['nombre'] }}</small>
                <small class="fw-semibold text-black ">$  {{ number_format($ofrenda['valor'], 2)  }} {{ $moneda->nombre_corto }}</small>
                @if($reporte->aprobado !== null)
                <small class="fw-semibold text-black ">$  {{ number_format($ofrenda['valor_real'], 2)  }} {{ $moneda->nombre_corto }} (Valor Real)</small>
                @endif
                <hr class="my-3 border-2">
              </div>
              @endforeach

              <div class="col-12 d-flex flex-column">
                <small class="text-black">Total</small>
                <small class="fw-semibold text-black ">$ {{ number_format($reporte->totalOfrendas(), 2) }} {{ $moneda->nombre_corto }}</small>
                @if($reporte->aprobado !== null)
                <small class="fw-semibold text-black ">$ {{ number_format($reporte->totalOfrendas('valor_real'), 2) }} {{ $moneda->nombre_corto }} (Valor Real)</small>
                @endif
                <hr class="my-3 border-2">
              </div>
            </div>


            <div class="row text-start mt-5 shadow rounded p-4">

              <p class="col-12 fw-semibold text-black mb-2"> Encargados ascendentes </p>
              <div class="col-12">

                <ul class="p-0 m-0 mx-4 mx-md-0" style="overflow-y: auto; max-height: 500px;">
                    @if($encargadosAscendentes)
                      @foreach ($encargadosAscendentes as $persona )
                      <li class="d-flex flex-wrap mb-4 border-bottom pb-3 border-2">
                          <div class="avatar me-4">
                            @if($persona->foto == "default-m.png" || $persona->foto == "default-f.png")
                                <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $persona->inicialesNombre() }} </span>
                            @else
                                <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) }}" alt="{{ $persona->foto }}" alt="avatar" class="rounded-circle">
                            @endif
                          </div>
                          <div class="d-flex justify-content-between flex-grow-1">
                              <div class="me-2 my-auto">
                                  <p class="mb-0 text-heading text-black fw-semibold">{{ $persona->nombre(3) }}</p>
                                  <p class="small mb-0 d-none"><i class="{{ $persona->tipo_usuario_icono }} fs-6"></i> </p>
                              </div>
                          </div>
                      </li>
                      @endforeach
                    @else
                    <div class="py-5">
                      <center>
                        <i class="ti ti-file ti-xl pb-1"></i>
                        <h6 class="text-center">Sin información.</h6>
                      </center>
                    </div>
                    @endif
                </ul>
              </div>

            </div>



          </div>

          <div class="col-12 col-lg-4 p-5">
            <div class="row text-start mt-5 shadow rounded p-4">


              <div class="col-12">
                <p class="fw-semibold text-black mb-2"> Asistencias </p>
                <div class="p-0 m-0 mx-4 mx-md-0 d-flex justify-content-between my-5">
                  <span class="text-black fs-6">Personas</span>
                  <span class="text-black fs-6">¿Asistio?</span>
                </div>

                <ul class="p-0 m-0 mx-4 mx-md-0" style="overflow-y: auto; max-height: 500px;">

                      @foreach ($personas as $persona )
                      <li class="d-flex flex-wrap mb-4 border-bottom pb-3 border-2">
                          <div class="avatar me-4">
                            @if($persona->foto == "default-m.png" || $persona->foto == "default-f.png")
                                <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $persona->inicialesNombre() }} </span>
                            @else
                                <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) }}" alt="{{ $persona->foto }}" alt="avatar" class="rounded-circle">
                            @endif
                          </div>
                          <div class="d-flex justify-content-between flex-grow-1">
                              <div class="me-2 my-auto">
                                <p class="mb-0 text-heading text-black fw-semibold">{{ $persona->nombre(3) }}</p>
                                @if($persona->pivot->nombre_tipo_inasistencia)
                                <p class="small mb-0 text-black">Inasistencia: <b>{{ $persona->pivot->nombre_tipo_inasistencia }}</b> {{ $persona->pivot->observaciones ? '-'.$persona->pivot->observaciones : '' }}</p>
                                @endif
                                <p class="small mb-0 text-black"> $ {{ number_format( $reporte->totalOfrendasDeLaPersona($persona->id, 'valor'), 2) }} {{ $moneda->nombre_corto }}</p>
                                @if($reporte->aprobado !== null)
                                <p class="small mb-0 text-black"> $ {{ number_format( $reporte->totalOfrendasDeLaPersona($persona->id,'valor_real'), 2) }} {{ $moneda->nombre_corto }} (Valor real)</p>
                                @endif
                              </div>
                              <div class="my-auto">
                                @if($persona->pivot->asistio)
                                <span class="badge rounded-pill px-6 fw-light fw-bold" style="background-color: #29dac7">
                                  <span class="text-white"> Si</span>
                                </span>
                                @else
                                <span class="badge rounded-pill px-6 fw-light fw-bold" style="background-color: #f56954">
                                  <span class="text-white"> No</span>
                                </span>
                                @endif

                              </div>
                          </div>
                      </li>
                      @endforeach
                </ul>
              </div>

            </div>
          </div>


      </div>

    </div>
  </div>

@endsection
