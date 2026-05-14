@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
use Carbon\Carbon;
@endphp


@extends('layouts/blankLayout')

@section('title', 'Historial RV')

@section('vendor-style')

<style>

  .boxShadow{
    padding: 19px;
    box-shadow: 0px 3px 7px #d4d4d4;
    border-radius: 9px;
    min-width: 280px;
    max-width: 100%;
    margin-bottom: 7px;
  }

  .texto-danger{
    color:#AA1A1E !important;
  }

  body {
    overflow-x: hidden;
  }
</style>

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
])
@endsection


@section('page-script')
@vite([
'resources/assets/js/form-basic-inputs.js',

])



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
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">{{$configuracionRv->nombre_general}} - Historial</h5>
    </div>
    <div class="col-3 text-end">
      <a href="{{ route('dashboard')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
        <span class="d-none d-md-block fw-normal">Salir</span>
        <span class="ti-xs ti ti-x mx-2"></span>
      </a>
    </div>
  </nav>

  <div class="pt-5 px-3 px-sm-7 mt-10">
    <div class="col-12 d-flex col-sm-8 offset-sm-2 col-lg-8 offset-lg-2 mb-7">
      <a href="/rueda-vida/nueva">
      <button style="border: solid 1px #CFD1D3 !important" type="button" class="btn btn-sm border py-2 border-2 rounded-3 shadow-sm box-waves-effect">
        <img style="width:50px" src="{{ Storage::url('generales/img/otros/dibujo_formulario_usuario_respuesta.png') }}" class="p-0"> <h6 style="color:#333" class="mb-0 p-2 fw-semibold">  Realizar {{$configuracionRv->nombre_general}}</h6>
        <i class="ms-3 ti ti-chevron-right"></i>
       </button>
      </a>
    </div>

    <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2">
      <h5 class="mb-0 p-2 fw-semibold text-black"> Tú historial </h5>
      <hr style="margin-top:10px; margin-bottom:30px" >
      @foreach($ruedasDeLaVida as $rueda)
      <div class="row boxShadow mb-3 pb-1 mt-3 justify-content-center align-items-center mx-0">
        <div class="col-lg-3 col-md-4 col-12 text-center text-md-start mb-2 mb-md-0">
          @php
              $fechaOriginal = $rueda->fecha; // Tu fecha en formato Y-m-d
              $fecha = new DateTime($fechaOriginal);

              // Formatear la fecha usando strftime()
              setlocale(LC_TIME, 'es_ES.UTF-8'); // Configurar el locale a español
              $fechaFormateada = strftime('%d-%B-%Y', $fecha->getTimestamp());

          @endphp
          <p style="text-transform: capitalize; color:#333;" class="mb-0"> {{$fechaFormateada}}</p>
        </div>
        <div class="col-lg-6 col-md-4 text-center col-12 mb-2 mb-md-0">
          <h6 class="fw-semibold mb-0 @if($rueda->promedio_general >= $configuracionRv->promedio_general)text-success @else texto-danger @endif"> {{$configuracionRv->label_promedio_general}} : {{$rueda->promedio_general}} </h6>
        </div>
        <div class="col-lg-3 col-md-4 col-12 text-center mb-3 text-md-end">
          <a class="fw-semibold" style="text-decoration: underline;color:#1977E5;" href="{{route('ruedaDeLaVida.resumen', $rueda->id)}}"> ver mis metas </a>
        </div>
      </div>
      @endforeach

      <div class="row my-10 text-black text-center text-md-start">
        @if($ruedasDeLaVida)
        <p> {{$ruedasDeLaVida->lastItem()}} <b>de</b> {{$ruedasDeLaVida->total()}} <b> - Página</b> {{ $ruedasDeLaVida->currentPage() }} </p>
        {!! $ruedasDeLaVida->appends(request()->input())->links() !!}
        @endif
      </div>
    </div>
  </div>
</div>


@endsection
