@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Peticiones')

<!-- Page -->
@section('page-style')
  @vite([
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
    'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/apex-charts/apexcharts.js',
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js'
  ])
@endsection

@section('page-script')
  @vite([
    'resources/assets/js/cards-actions.js'
  ])

  <script type="module">
    $(function() {
      //esta bandera impide que entre en un bucle cuando se ejecuta la funcion cb(start, end)
      let band=0;
      moment.locale('es');

      function cb(start, end) {

        $('#filtroFechaIni').val(start.format('YYYY-MM-DD'));
        $('#filtroFechaFin').val(end.format('YYYY-MM-DD'));

        $('#filtroFechas span').html(start.format('YYYY-MM-DD') + ' hasta ' + end.format('YYYY-MM-DD'));
        if(band==1)
        $("#filtro").submit();
        band=1;
      }

      //comprobamos si existe la fecha incio y fecha fin y creamos las fechas con el formato aceptado
      @if(isset($filtroFechaIni))
        var fecha_ini = moment('{{$filtroFechaIni}}');
        fecha_ini.format("YYYY-MM-DD");
      @endif

      @if(isset($filtroFechaFin))
        var fecha_fin = moment('{{$filtroFechaFin}}');
        fecha_fin.format("YYYY-MM-DD");
      @endif

      @if(isset($filtroFechaIni) && isset($filtroFechaFin))
        cb(fecha_ini, fecha_fin);
      @else
        cb(moment().startOf('month'), moment().endOf('month'));
      @endif

      $('#filtroFechas').daterangepicker({
          ranges: {
              'Hoy': [moment(), moment()],
              'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
              'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
              'Mes actual': [moment().startOf('month'), moment().endOf('month')],
              'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
              'Año actual': [moment().startOf('year'), moment().endOf('year')],
              'Año anterior': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
          },
          "locale": {
            "format": "YYYY-MM-DD",
            "separator": " hasta ",
            "applyLabel": "Aplicar",
            "cancelLabel": "Cancelar",
            "fromLabel": "Desde",
            "toLabel": "Hasta",
            "customRangeLabel": "Otro rango",
            "monthNames": JSON.parse(<?php print json_encode(json_encode($meses)); ?>),
            "firstDay": 1
          },
          @if(isset($filtroFechaIni))
          "startDate": fecha_ini,
          @endif
          @if(isset($filtroFechaIni))
          "endDate": fecha_fin,
          @endif
          showDropdowns: true
        }, cb);
    });
  </script>

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

    // Grafico por tipos de peticiones
    const tiposPeticionesGrafico = document.querySelector('#tiposDePeticiones'),
    seriesTiposPeticiones = JSON.parse(<?php print json_encode(json_encode($seriesTiposPeticiones)); ?>),
    labelsTiposPeticiones = JSON.parse(<?php print json_encode(json_encode($labelsTiposPeticiones)); ?>),
      tiposPeticionesConfig = {
        chart: {
          height: 390,
          type: 'donut'
        },
        labels: labelsTiposPeticiones,
        series: seriesTiposPeticiones,
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
          formatter: function (val, opt) {
            return parseInt(val, 10) + '%';
          }
        },
        legend: {
          show: true,
          position: 'bottom',
          markers: { offsetX: -3 },
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
                  fontSize: '1.8rem',
                  fontFamily: 'Public Sans'
                },
                value: {
                  fontSize: '1.2rem',
                  color: legendColor,
                  fontFamily: 'Public Sans',
                  formatter: function (val) {
                    return val;
                  }
                },
                total: {
                  show: true,
                  fontSize: '1.2rem',
                  color: headingColor,
                  label: 'Total',
                  formatter: function (w)  {
                    return '{{ $peticiones->total()}}';
                  }
                }
              }
            }
          }
        },
        responsive: [
          {
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
    if (typeof tiposPeticionesGrafico !== undefined && tiposPeticionesGrafico !== null) {
      const tiposPeticiones = new ApexCharts(tiposPeticionesGrafico, tiposPeticionesConfig);
      tiposPeticiones.render();
    }
    // Grafico por tipos de peticiones
  </script>

  <script type="text/javascript">
    function filtroTodos()
    {
      $('#paisId').val('');
      $('#tipoPeticionId').val('');
      $("#filtro").submit();
      var dsf = '{{ $filtroFechaIni }}';
    }

    function filtroPais(paisId)
    {
      $('#paisId').val(paisId);
      $('#tipoPeticionId').val('');
      $("#filtro").submit();
    }

    function filtroTipoPeticionPais(paisId,tipoPeticionId)
    {
      if(paisId)
      {
        $('#paisId').val(paisId);
      }
      $('#tipoPeticionId').val(tipoPeticionId);
      $("#filtro").submit();
    }

    function filtroTipoPeticion(tipoPeticionId)
    {
      $('#tipoPeticionId').val(tipoPeticionId);
      $('#paisId').val('');
      $("#filtro").submit();
    }
  </script>
@endsection

@section('content')
  <h4 class="mb-1">Panel de peticiones</h4>
  <p class="mb-4">Aquí podrás gestionar tus peticiones.</p>

  @include('layouts.status-msn')

  <div class="row mt-5">
    <form id="filtro" class="forms-sample" method="GET" action="{{ route('peticion.panel') }}">
    <div class="row m-0 p-0">

      <!-- Por rango de fechas  -->
      <div class="col-12 col-md-12 p-0 mb-2">
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-calendar"></i></span>
          <input type="text" id="filtroFechaIni" name="filtroFechaIni" value="{{ $filtroFechaIni }}" class="form-control d-none" placeholder="">
          <input type="text" id="filtroFechaFin" name="filtroFechaFin" value="{{ $filtroFechaFin }}" class="form-control d-none" placeholder="">
          <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
        </div>
      </div>
    </div>

    <input type="text" id="paisId" name="paisId" value="{{ $paisSeleccionado ? $paisSeleccionado->id : '' }}" class="form-control d-none" placeholder="">
    <input type="text" id="tipoPeticionId" name="tipoPeticionId" value="{{ $tipoPeticionSeleccionada ? $tipoPeticionSeleccionada->id : '' }}" class="form-control d-none" placeholder="">
    <div class="mb-3 mt-0">
          <button onclick="filtroTodos()" type="button" class="btn btn-xs rounded-pill btn-outline-primary waves-effect">Todas las peticiones</button>
          @if($paisSeleccionado)
          <i class="tf-icons ti ti-chevron-right scaleX-n1-rtl ti-sm"></i>
          <button onclick="filtroPais('{{$paisSeleccionado->id}}')" type="button" class="btn btn-xs rounded-pill btn-outline-primary waves-effect">{{$paisSeleccionado->nombre}}</button>
          @endif

          @if($tipoPeticionSeleccionada)
          <i class="tf-icons ti ti-chevron-right scaleX-n1-rtl ti-sm"></i>
            @if($paisSeleccionado)
            <button onclick="filtroTipoPeticionPais('{{$paisSeleccionado->id}}','{{$tipoPeticionSeleccionada->id}}')" type="button" class="btn btn-xs rounded-pill btn-outline-primary waves-effect">{{$tipoPeticionSeleccionada->nombre}}</button>
            @else
            <button onclick="filtroTipoPeticion('{{$tipoPeticionSeleccionada->id}}')" type="button" class="btn btn-xs rounded-pill btn-outline-primary waves-effect">{{$tipoPeticionSeleccionada->nombre}}</button>
            @endif
          @endif

        </div>
    </form>

  </div>

  <div class="row mb-3">
    <!-- Indicadores -->
    <div class="col-lg-12 col-md-12 mb-3 ">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <h6 class="card-title text-uppercase mb-0 fw-bold">Indicadores</h6>
        </div>
        <div class="card-body">
          <div class="row gy-3">
            @foreach( $indicadores as $indicador )
            <div class="{{$indicador->col}}">
              <div class="d-flex align-items-center">
                <div class="badge rounded {{ $indicador->color}}  me-4 p-2"><i class="{{ $indicador->icono}} ti-lg"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $indicador->cantidad }}</h5>
                  <small>{{ $indicador->nombre }}</small>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    <!--/ Indicadores -->

    <div class="col-md-4 mb-3">

      <!-- Por paises -->
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
          <h6 class="card-title text-uppercase mb-0 fw-bold">Peticiones por paises</h6>
        </div>
        <div class="card-body">
          @if($paises->count()>0)
            @foreach($paises as $pais)
            <div class="card card-action mb-2">
              <div class="card-header">
                <div class="card-action-title">
                <ul class="p-0 m-0">
                  <li class="d-flex align-items-center mb-0">
                    <div class="avatar flex-shrink-0 me-2">
                      <i class="fis fi fi-{{ strtolower($pais->codigo_alpha) }} rounded-circle fs-2"></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                      <div class="me-2">
                        <h5 class="mb-1 fw-bold">{{$pais->nombre}} </h5>
                        <p class="card-subtitle">Total: {{ $pais->cantidad}}</p>


                      </div>
                    </div>
                  </li>
                </ul>
                </div>
                <div class="card-action-element">

                  <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                      <button onclick="filtroPais('{{$pais->id}}')" class=" btn btn-outline-primary waves-effect btn-xs p-1" data-bs-toggle="tooltip" aria-label="Filtrar peticiones de {{$pais->nombre}}" data-bs-original-title="Filtrar peticiones de {{$pais->nombre}}">
                        <i class="ti ti-filter ti-xs"></i>
                      </button>
                    </li>
                    <li class="list-inline-item">
                      <a href="javascript:void(0);" class="card-collapsible"><i class="tf-icons ti ti-chevron-right scaleX-n1-rtl ti-sm"></i></a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="collapse">
                <div class="card-body pt-0 px-2 pb-2">
                  <ul class="list-unstyled mb-0">
                    @foreach($pais->tipos as $tipo)
                      <li class="mb-1 mt-1 p-2 border rounded">
                        <div class="d-flex align-items-start">
                          <div class="d-flex align-items-start">
                            <div class="me-2 ms-1">
                              <h6 class="mb-0">{{ $tipo->cantidad}}</h6>
                              <small class="text-muted">{{ $tipo->nombre }} </small>
                            </div>
                          </div>

                          <div class="ms-auto pt-1">
                            <button  onclick="filtroTipoPeticionPais('{{$pais->id}}','{{$tipo->id}}')" class="btn btn-outline-primary waves-effect btn-xs p-1" data-bs-toggle="tooltip" aria-label="Filtrar peticiones de {{$pais->nombre}} y tipo {{ $tipo->nombre }}" data-bs-original-title="Filtrar peticiones de {{$pais->nombre}} y tipo {{ $tipo->nombre }}">
                              <i class="ti ti-filter ti-xs"></i>
                            </button>
                          </div>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
            @endforeach
          @else
          <div class="mt-5 mb-5">
            <center>
            <i class="ti ti-world-pin ti-xl"></i>
            <p>En este momento no hay clasificación por paises.</p>
            </center>
          </div>
          @endif
        </div>
      </div>
      <!--/ Por paises -->

      <!-- Grafico por tipo de usuario -->
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title text-uppercase mb-0 fw-bold">Gráfico general por tipo de peticiones</h6>
          </div>
        </div>
        <div class="card-body">
          <div id="tiposDePeticiones"></div>

          <div class="table-responsive text-nowrap mt-3">
            <table class="table">
              <thead>
                <tr>
                  <th class="fw-bold text-center">Nombre</th>
                  <th class="fw-bold text-center">Cantidad</th>
                  <th class="fw-bold text-center"></th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                @foreach ($tiposPeticiones as $tipoPenticion)
                <tr>
                  <td class="text-center">{{ $tipoPenticion->nombre }}</td>
                  <td class="text-center">{{ $tipoPenticion->cantidad }}</td>
                  <td class="text-center">
                    <button  onclick="filtroTipoPeticion('{{$tipoPenticion->id}}')" class="btn btn-xs btn-outline-primary waves-effect btn-xs p-1" data-bs-toggle="tooltip" aria-label="Filtrar peticiones de tipo {{ $tipoPenticion->nombre }}" data-bs-original-title="Filtrar peticiones por tipo {{ $tipoPenticion->nombre }}">
                      <i class="ti ti-filter ti-xs"></i>
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

        </div>
      </div>
      <!-- /Grafico por tipo de usuario -->
    </div>

    <!-- Listado de peticiones -->
    <div class="col-md-8 mb-3 mt-1" >

      @if($peticiones->count() > 0)

      <h6 class="card-title text-uppercase mb-0 fw-bold text-center mb-3 mt-3">Listado de peticiones</h6>

      <div class="row my-1">
          @if($peticiones)
          <p> {{$peticiones->lastItem()}} <b>de</b> {{$peticiones->total()}} <b>peticiones - Página</b> {{ $peticiones->currentPage() }} </p>
          {!! $peticiones->appends(request()->input())->links() !!}
          @endif
        </div>
        <!-- lista de peticiones -->
        <div class="row g-3">
          @foreach($peticiones as $peticion)
          <div class="col-12 col-xl-6 col-lg-6 col-md-6">
            <div class="card border rounded p-2">

              <div class="card-header">
                <div class="d-flex align-items-start">
                  <div class="d-flex align-items-start">
                    <div class="px-1">
                      <button class="btn rounded-pill btn-icon btn-{{ $peticion->estado == 3 ? 'warning' : ($peticion->estado == 2 ? 'success' : 'primary' ) }} waves-effect waves-light btn-xl"><i class="ti ti-notes ti-xl mx-2"></i></button>
                    </div>
                    <div class="me-2 ms-1 mt-1">
                      <h5 class="mb-0"><a href="javascript:;" class="text-body"><b>Tipo:</b> {{ $peticion->tipoPeticion ? $peticion->tipoPeticion->nombre : 'No definido'}}</a></h5>
                      <div class="client-info"><span class="fw-medium"><i class="ti ti-calendar"></i> {{ $peticion->fecha }}</span></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">

              <div class="d-flex align-items-center">
                <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2">
                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{$peticion->nombreUsuario}}" class="avatar pull-up">
                      <img class="rounded-circle" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$peticion->fotoUsuario) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$peticion->fotoUsuario }}" alt="foto {{$peticion->nombreUsuario}}">
                    </li>
                    <span class="text-muted mx-1">{{ $peticion->nombreUsuario }}</span>
                </ul>
              </div>
              <div class="list-unstyled mb-4 mt-3">
                <ul class="list-unstyled mb-4 mt-3">
                  <li class="d-flex align-items-center mb-1"><i class="ti ti-phone-call text-heading"></i> <span class="mx-2">{{ $peticion->telefonosUsuario }}</span> </li>
                  <li class="d-flex align-items-center mb-1"><i class="ti ti-mail text-heading"></i> <span class="mx-2">{{ $peticion->emailUsuario }}</span></li>

                </ul>
              </div>

              <div class="accordion mt-3" id="accordionDePeticiones{{$peticion->id}}">

                <div class="card accordion-item">
                  <h2 class="accordion-header" id="headingPeticion{{$peticion->id}}">
                    <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionPeticion{{$peticion->id}}" aria-expanded="true" aria-controls="accordionPeticion{{$peticion->id}}">
                      Petición
                    </button>
                  </h2>

                  <div id="accordionPeticion{{$peticion->id}}" class="accordion-collapse collapse" data-bs-parent="#accordionDePeticiones{{$peticion->id}}">
                    <div class="accordion-body" style="height: 100px; overflow-y: scroll;">
                      <p class="text-secondary mt-0 mb-2"><b><i class="ti ti-user-circle"></i> Creada por:</b> {{$peticion->usuarioCreacion }}</p>
                      <p class="m-0">{!! $peticion->descripcion !!}</p>
                    </div>
                  </div>
                </div>

                @if($peticion->estado > 1)
                <div class="card accordion-item">
                  <h2 class="accordion-header" id="headingSeguimiengo{{$peticion->id}}">
                    <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionSeguimiengo{{$peticion->id}}" aria-expanded="false" aria-controls="accordionSeguimiengo{{$peticion->id}}">
                      Seguimiento
                    </button>
                  </h2>
                  <div id="accordionSeguimiengo{{$peticion->id}}" class="accordion-collapse collapse" aria-labelledby="headingSeguimiengo{{$peticion->id}}" data-bs-parent="#accordionDePeticiones{{$peticion->id}}">
                    <div class="accordion-body" style="height: 100px; overflow-y: scroll;">
                      @foreach($peticion->seguimientos as $seguimiento)
                      <p class="text-secondary mt-0 mb-2"><b><i class="ti ti-user-circle"></i> Creada por:</b> {{$seguimiento->usuarioCreacion ? $seguimiento->usuarioCreacion->nombre(3) : 'No definido' }}</p>
                      <p class="m-0">{!! $seguimiento->descripcion !!}</p>
                      <hr>
                      @endforeach
                    </div>
                  </div>
                </div>
                @endif

                @if($peticion->estado==2)
                <div class="card accordion-item">
                  <h2 class="accordion-header" id="headingRespuesta{{$peticion->id}}">
                    <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionRespuesta{{$peticion->id}}" aria-expanded="false" aria-controls="accordionRespuesta{{$peticion->id}}">
                      Respuesta
                    </button>
                  </h2>
                  <div id="accordionRespuesta{{$peticion->id}}" class="accordion-collapse collapse" aria-labelledby="headingRespuesta{{$peticion->id}}" data-bs-parent="#accordionDePeticiones{{$peticion->id}}">
                    <div class="accordion-body">
                      {!! $peticion->respuesta !!}
                    </div>
                  </div>
                </div>
                @endif
              </div>

              </div>
            </div>
          </div>
          @endforeach
        </div>
        <!--/ lista de peticiones -->

        <div class="row my-3">
          @if($peticiones)
          <p> {{$peticiones->lastItem()}} <b>de</b> {{$peticiones->total()}} <b>peticiones - Página</b> {{ $peticiones->currentPage() }} </p>
          {!! $peticiones->appends(request()->input())->links() !!}
          @endif
        </div>
      @else
        <div class="mt-5 mb-5 py-5">
          <center>
          <i class="ti ti-notes ti-xl"></i>
          <p>En este momento no hay ninguna petición.</p>
          </center>
        </div>
      @endif
    </div>
    <!-- /Listado de peticiones -->
  </div>
@endsection
