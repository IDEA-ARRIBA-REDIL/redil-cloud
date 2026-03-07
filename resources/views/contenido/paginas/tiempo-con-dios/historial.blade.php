@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
use Carbon\Carbon;
@endphp


@extends('layouts/blankLayout')

@section('title', 'Historial tiempo con Dios')

@section('vendor-style')

<style>
  body {
    overflow-x: hidden;
  }
</style>

@section('page-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
])
@endsection


@section('page-script')
@vite([
'resources/assets/js/form-basic-inputs.js'
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

@endsection

@section('content')

<div class="col-12">
  <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
    <div class="col-3 text-start">
      <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
        <span class="ti-xs ti ti-arrow-left me-2"></span>
        <span class="d-none d-md-block fw-normal">Volver</span>
      </button>
    </div>
    <div class="col-6 pl-5 text-center">
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Mis tiempos con Dios</h5>
    </div>
    <div class="col-3 text-end">
    <a href="{{ route('dashboard')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
        <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
      </a>
    </div>
  </nav>



  <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2 p-5">

   <div class="d-flex flex-column flex-md-row justify-content-md-between ">
     <!-- boton nuevo -->
     <div class="pt-3 px-7 px-sm-0 mt-10 pb-5 pb-mb-9 d-flex justify-content-center ">
      @if($tiempoConDiosHoy > 0)
        <button disabled style="border: solid 1px #CFD1D3 !important" type="button" class="btn btn-sm border py-2 border-2 rounded-3 shadow-sm box-waves-effect">
         <i class="ti ti-pray ti-xl"></i>
         <h6 style="color:#333" class="mb-0 p-2 fw-semibold">  Realizar mi tiempo con Dios</h6>
         <i class="ms-3 ti ti-chevron-right"></i>
       </button>
      @else
       <a href="{{route('tiempoConDios.bienvenida')}}" style="border: solid 1px #CFD1D3 !important" type="button" class="btn btn-sm border py-2 border-2 rounded-3 shadow-sm box-waves-effect">
         <i class="ti ti-pray ti-xl"></i>
         <h6 style="color:#333" class="mb-0 p-2 fw-semibold">  Realizar mi tiempo con Dios</h6>
         <i class="ms-3 ti ti-chevron-right"></i>
       </a>
       @endif
     </div>
     <!-- /boton nuevo -->


     @livewire('TiempoConDios.racha-semanal')

   </div>


    <h5 class="mb-0 p-2 fw-semibold text-black"> Tú historial </h5>
    <hr style="margin-top:10px; margin-bottom:30px" >

    <div class="row mt-5">
      <form id="filtro" class="forms-sample" method="GET" action="{{ route('tiempoConDios.historial') }}">
        <div class="row">
          <!-- Por rango de fechas  -->
          <div class="col-12 col-lg-6">
            <div class="input-group input-group-merge">
              <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="Seleciona el rengo de fechas" />
               <span class="input-group-text"><i class="ti ti-calendar"></i></span>
            </div>

            <input type="text" id="filtroFechaIni" name="filtroFechaIni" value="{{ $filtroFechaIni }}" class="form-control d-none" placeholder="">
            <input type="text" id="filtroFechaFin" name="filtroFechaFin" value="{{ $filtroFechaFin }}" class="form-control d-none" placeholder="">
          </div>
        </div>
      </form>
    </div>

    <div id="panel-historiales">
      @foreach($tiemposConDios as $tiempoConDios)
      <div class="d-flex boxShadow my-4 py-5">

        <div class="col-lg-6 col-md-8 col-8 my-auto">
          <p class="text-black fw-bold m-0"> {{ $tiempoConDios->fecha }}</p>
        </div>
        <div class="col-lg-6 col-md-4 col-4 my-auto">
          <a class="fw-semibold  float-end" style="text-decoration: underline;color:#1977E5" href="{{route('tiempoConDios.resumen', $tiempoConDios)}}"> Ver detalles </a>
        </div>
      </div>
      @endforeach
    </div>

  </div>
</div>

@endsection
