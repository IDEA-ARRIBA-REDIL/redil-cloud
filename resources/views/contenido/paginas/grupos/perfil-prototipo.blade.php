@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Perfil del grupo')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js'
])
@endsection

@section('page-script')

@vite(['resources/assets/js/dashboards-crm.js'])

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
<script>
  function darBajaAlta(grupoId, tipo)
  {
    Livewire.dispatch('abrirModalBajaAlta', { grupoId: grupoId, tipo: tipo });
  }

  function eliminacion(grupoId)
  {
    Livewire.dispatch('confirmarEliminacion', { grupoId: grupoId });
  }
</script>
@endsection

@section('content')

  @include('layouts.status-msn')

  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-6">
        <div class="user-profile-header-banner">
          <img src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/'.$grupo->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png')}}" alt="Banner image" class="rounded-top">
        </div>
        <div class="user-profile-header d-flex flex-column flex-md-row text-sm-start text-center mb-5">
          <div class="flex-shrink-0 mt-n2 mx-0 mx-auto">
            <div class="card rounded-pill icon-card text-center mb-0 mx-3 p-2" style="background-color: {{ $grupo->tipoGrupo->color }}">
              <div class="card-body text-white"> <i class="ti ti-users-group ti-xl"></i>
              </div>
            </div>
          </div>
          <div class="flex-grow-1 mt-3 mt-md-5">
            <div class="d-flex align-items-md-end align-items-md-start align-items-center justify-content-md-between justify-content-start mx-2 flex-md-row flex-column gap-4">
              <div class="user-profile-info">
                <h4 class="mb-0 mt-md-4 fw-bold">{{ $grupo->nombre }}</h4>
                <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-md-start justify-content-center gap-4 my-0">
                  <li class="list-inline-item d-flex gap-2 align-items-center">
                    <span class="fw-medium"> {{ $grupo->tipoGrupo->nombre }}</span>
                  </li>
                </ul>
              </div>
              <div class="d-flex mb-4">
                <div class="p-2 flex-grow-1 bd-highlight">
                </div>
                <div class="flex-shrink-1 ">
                  <div class="dropdown d-flex border rounded py-2 px-4 ">
                    <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false">Opciones <i class="ti ti-dots-vertical text-muted"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      @if($grupo->dado_baja == 0)
                        @if($rolActivo->hasPermissionTo('grupos.opcion_modificar_grupo'))
                          <li><a class="dropdown-item" href="{{ route('grupo.modificar', $grupo)}}">Modificar</a></li>
                        @endif

                        @if($rolActivo->hasPermissionTo('grupos.opcion_excluir_grupo'))
                          <form id="excluirGrupo" method="POST" action="{{ route('grupo.excluir', ['grupo' => $grupo]) }}">
                            @csrf
                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('excluirGrupo').submit();" >Excluir grupo</a></li>
                          </form>
                        @endif

                        @if($rolActivo->hasPermissionTo('grupos.opcion_dar_de_baja_alta_grupo'))
                          <li><a class="dropdown-item" href="javascript:void(0);" onclick="darBajaAlta('{{$grupo->id}}', 'baja')">Dar de baja</a></li>
                        @endif

                        @if($rolActivo->hasPermissionTo('grupos.opcion_eliminar_grupo'))
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="eliminacion('{{$grupo->id}}')">Eliminar</a></li>
                        @endif
                      @else
                        @if($rolActivo->hasPermissionTo('grupos.opcion_dar_de_baja_alta_grupo'))
                          <li><a class="dropdown-item" href="javascript:void(0);" onclick="darBajaAlta('{{$grupo->id}}', 'alta')">Dar de alta</a></li>
                        @endif
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
  </div>
  <!--/ Header -->

  @livewire('Grupos.modal-baja-alta-grupo')

  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12 ">
      <div class="nav-align-top">
        <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0 justify-content-center gap-2">

          <li class="nav-item"><a href="" class="tapControl nav-link waves-effect waves-light active"><i class='ti-xs ti ti-chart-bar me-1'></i> Dashboard </a></li>
          <li class="nav-item"><a href="" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-info-square-rounded me-1'></i> Información basica</a></li>
          <li class="nav-item"><a href="" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-users me-1'></i> Integrantes </a></li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->

  <div class="row mt-5">
    <form class="forms-sample" method="GET" action="">
    <div class="row mb-5 p-0">

      <!-- Por rango de fechas  -->
      <div class="col-12 col-md-5 mb-2">
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-calendar"></i></span>
          <input type="text" id="filtroFechaIni" name="filtroFechaIni" value="" class="form-control d-none" placeholder="">
          <input type="text" id="filtroFechaFin" name="filtroFechaFin" value="" class="form-control d-none" placeholder="">
          <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
        </div>
      </div>

      <!-- Por persona -->
      @livewire('Grupos.grupos-para-busqueda',[
        'id' => 'filtroGrupo',
        'class' => 'col-12 col-md-5 mb-3',
        'label' => '',
        'placeholder' => 'Selecciona el grupo',
        'conDadosDeBaja' => 'no',
        'grupoSeleccionadoId' => 4,
        'estiloSeleccion' => 'pequeno'
        ])

      <div class="col-12 col-md-2 mb-2">
        <button class="btn btn-outline-primary px-2 px-md-3" type="submit" id="button-addon2"> Filtar <i class=" ti ti-search"></i></button>
      </div>

    </div>
    </form>
  </div>

  <div id="div-principal" class="row">

    <!-- Orders by Countries tabs-->
    <div class="col-md-4 col-xxl-4 mb-6">

      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1">Orders by Countries</h5>
            <p class="card-subtitle">62 deliveries in progress</p>
          </div>
          <div class="dropdown">
            <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="salesByCountryTabs" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="ti ti-dots-vertical ti-md text-muted"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">
              <a class="dropdown-item" href="javascript:void(0);">Select All</a>
              <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
              <a class="dropdown-item" href="javascript:void(0);">Share</a>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="nav-align-top">
            <ul class="nav nav-tabs nav-fill rounded-0 timeline-indicator-advanced" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-new" aria-controls="navs-justified-new" aria-selected="true">New</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-link-preparing" aria-controls="navs-justified-link-preparing" aria-selected="false">Preparing</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-link-shipping" aria-controls="navs-justified-link-shipping" aria-selected="false">Shipping</button>
              </li>
            </ul>
            <div class="tab-content border-0  mx-1">
              <div class="tab-pane fade show active" id="navs-justified-new" role="tabpanel">
                <ul class="timeline mb-0">
                  <li class="timeline-item ps-6 border-left-dashed">
                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                      <i class='ti ti-circle-check'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-success text-uppercase">sender</small>
                      </div>
                      <h6 class="my-50">Myrtle Ullrich</h6>
                      <p class="text-body mb-0">101 Boulder, California(CA), 95959</p>
                    </div>
                  </li>
                  <li class="timeline-item ps-6 border-transparent">
                    <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                      <i class='ti ti-map-pin'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-primary text-uppercase">Receiver</small>
                      </div>
                      <h6 class="my-50">Barry Schowalter</h6>
                      <p class="text-body mb-0">939 Orange, California(CA), 92118</p>
                    </div>
                  </li>
                </ul>
                <div class="border-1 border-light border-top border-dashed my-4"></div>
                <ul class="timeline mb-0">
                  <li class="timeline-item ps-6 border-left-dashed">
                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                      <i class='ti ti-circle-check'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-success text-uppercase">sender</small>
                      </div>
                      <h6 class="my-50">Veronica Herman</h6>
                      <p class="text-body mb-0">162 Windsor, California(CA), 95492</p>
                    </div>
                  </li>
                  <li class="timeline-item ps-6 border-transparent">
                    <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                      <i class='ti ti-map-pin'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-primary text-uppercase">Receiver</small>
                      </div>
                      <h6 class="my-50">Helen Jacobs</h6>
                      <p class="text-body mb-0">487 Sunset, California(CA), 94043</p>
                    </div>
                  </li>
                </ul>
              </div>

              <div class="tab-pane fade" id="navs-justified-link-preparing" role="tabpanel">
                <ul class="timeline mb-0">
                  <li class="timeline-item ps-6 border-left-dashed">
                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                      <i class='ti ti-circle-check'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-success text-uppercase">sender</small>
                      </div>
                      <h6 class="my-50">Barry Schowalter</h6>
                      <p class="text-body mb-0">939 Orange, California(CA), 92118</p>
                    </div>
                  </li>
                  <li class="timeline-item ps-6 border-transparent border-left-dashed">
                    <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                      <i class='ti ti-map-pin'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-primary text-uppercase">Receiver</small>
                      </div>
                      <h6 class="my-50">Myrtle Ullrich</h6>
                      <p class="text-body mb-0">101 Boulder, California(CA), 95959 </p>
                    </div>
                  </li>
                </ul>
                <div class="border-1 border-light border-top border-dashed my-4"></div>
                <ul class="timeline mb-0">
                  <li class="timeline-item ps-6 border-left-dashed">
                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                      <i class='ti ti-circle-check'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-success text-uppercase">sender</small>
                      </div>
                      <h6 class="my-50">Veronica Herman</h6>
                      <p class="text-body mb-0">162 Windsor, California(CA), 95492</p>
                    </div>
                  </li>
                  <li class="timeline-item ps-6 border-transparent">
                    <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                      <i class='ti ti-map-pin'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-primary text-uppercase">Receiver</small>
                      </div>
                      <h6 class="my-50">Helen Jacobs</h6>
                      <p class="text-body mb-0">487 Sunset, California(CA), 94043</p>
                    </div>
                  </li>
                </ul>
              </div>
              <div class="tab-pane fade" id="navs-justified-link-shipping" role="tabpanel">
                <ul class="timeline mb-0">
                  <li class="timeline-item ps-6 border-left-dashed">
                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                      <i class='ti ti-circle-check'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-success text-uppercase">sender</small>
                      </div>
                      <h6 class="my-50">Veronica Herman</h6>
                      <p class="text-body mb-0">101 Boulder, California(CA), 95959</p>
                    </div>
                  </li>
                  <li class="timeline-item ps-6 border-transparent">
                    <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                      <i class='ti ti-map-pin'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-primary text-uppercase">Receiver</small>
                      </div>
                      <h6 class="my-50">Barry Schowalter</h6>
                      <p class="text-body mb-0">939 Orange, California(CA), 92118</p>
                    </div>
                  </li>
                </ul>
                <div class="border-1 border-light border-top border-dashed my-4"></div>
                <ul class="timeline mb-0">
                  <li class="timeline-item ps-6 border-left-dashed">
                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                      <i class='ti ti-circle-check'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-success text-uppercase">sender</small>
                      </div>
                      <h6 class="my-50">Myrtle Ullrich</h6>
                      <p class="text-body mb-0">162 Windsor, California(CA), 95492 </p>
                    </div>
                  </li>
                  <li class="timeline-item ps-6 border-transparent">
                    <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                      <i class='ti ti-map-pin'></i>
                    </span>
                    <div class="timeline-event ps-1">
                      <div class="timeline-header">
                        <small class="text-primary text-uppercase">Receiver</small>
                      </div>
                      <h6 class="my-50">Helen Jacobs</h6>
                      <p class="text-body mb-0">487 Sunset, California(CA), 94043</p>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Orders by Countries tabs -->

      <!-- Earning Reports Tabs-->
  <div class="col-md-8 col-12">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title m-0">
          <h5 class="mb-1">Earning Reports</h5>
          <p class="card-subtitle">Yearly Earnings Overview</p>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="earningReportsTabsId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-dots-vertical ti-md text-muted"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="earningReportsTabsId">
            <a class="dropdown-item" href="javascript:void(0);">View More</a>
            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <ul class="nav nav-tabs widget-nav-tabs pb-8 gap-4 mx-1 d-flex flex-nowrap" role="tablist">
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link btn active d-flex flex-column align-items-center justify-content-center" role="tab" data-bs-toggle="tab" data-bs-target="#navs-orders-id" aria-controls="navs-orders-id" aria-selected="true">
              <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-shopping-cart ti-md"></i></div>
              <h6 class="tab-widget-title mb-0 mt-2">Orders</h6>
            </a>
          </li>
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab" data-bs-toggle="tab" data-bs-target="#navs-sales-id" aria-controls="navs-sales-id" aria-selected="false">
              <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-chart-bar ti-md"></i></div>
              <h6 class="tab-widget-title mb-0 mt-2"> Sales</h6>
            </a>
          </li>
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab" data-bs-toggle="tab" data-bs-target="#navs-profit-id" aria-controls="navs-profit-id" aria-selected="false">
              <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-currency-dollar ti-md"></i></div>
              <h6 class="tab-widget-title mb-0 mt-2">Profit</h6>
            </a>
          </li>
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab" data-bs-toggle="tab" data-bs-target="#navs-income-id" aria-controls="navs-income-id" aria-selected="false">
              <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-chart-pie-2 ti-md"></i></div>
              <h6 class="tab-widget-title mb-0 mt-2">Income</h6>
            </a>
          </li>
        </ul>
        <div class="tab-content p-0 ms-0 ms-sm-2">
          <div class="tab-pane fade show active" id="navs-orders-id" role="tabpanel">
            <div id="earningReportsTabsOrders"></div>
          </div>
          <div class="tab-pane fade" id="navs-sales-id" role="tabpanel">
            <div id="earningReportsTabsSales"></div>
          </div>
          <div class="tab-pane fade" id="navs-profit-id" role="tabpanel">
            <div id="earningReportsTabsProfit"></div>
          </div>
          <div class="tab-pane fade" id="navs-income-id" role="tabpanel">
            <div id="earningReportsTabsIncome"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0 card-title">Project Status</h5>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="projectStatusId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-dots-vertical ti-md text-muted"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="projectStatusId">
            <a class="dropdown-item" href="javascript:void(0);">View More</a>
            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-start">
          <div class="badge rounded bg-label-warning p-2 me-3 rounded"><i class="ti ti-currency-dollar ti-lg"></i></div>
          <div class="d-flex justify-content-between w-100 gap-2 align-items-center">
            <div class="me-2">
              <h6 class="mb-0">$4,3742</h6>
              <small class="text-body">Your Earnings</small>
            </div>
            <h6 class="mb-0 text-success">+10.2%</h6>
          </div>
        </div>
        <div id="projectStatusChart"></div>
        <div class="d-flex justify-content-between mb-4">
          <h6 class="mb-0">Donates</h6>
          <div class="d-flex">
            <p class="mb-0 me-4">$756.26</p>
            <p class="mb-0 text-danger">-139.34</p>
          </div>
        </div>
        <div class="d-flex justify-content-between">
          <h6 class="mb-0">Podcasts</h6>
          <div class="d-flex">
            <p class="mb-0 me-4">$2,207.03</p>
            <p class="mb-0 text-success">+576.24</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between pb-4">
        <div class="card-title mb-0">
          <h5 class="mb-1">Sales</h5>
          <p class="card-subtitle">Last 6 Months</p>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="salesLastMonthMenu" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-dots-vertical ti-md text-muted"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesLastMonthMenu">
            <a class="dropdown-item" href="javascript:void(0);">View More</a>
            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="salesLastMonth"></div>
      </div>
    </div>

  </div>

  </div>



@endsection
