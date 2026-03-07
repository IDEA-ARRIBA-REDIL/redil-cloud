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

@section('page-style')
  @vite([
  'resources/assets/vendor/scss/pages/page-profile.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
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
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Resumen</h5>
    </div>
    <div class="col-3 text-end">
    <a href="{{ route('tiempoConDios.historial')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
        <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
      </a>
    </div>
  </nav>

  <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2 p-5">


    <h5 class="mb-0 p-2 fw-semibold text-black"> Mi tiempo con Dios ({{$tiempoConDios->fecha}})</h5>
    <hr style="margin-top:10px; margin-bottom:30px" >


    <div id="panel-historiales">

     @foreach ($secciones as $seccion)
      <div class="col-md-12">
        <div class="card mb-4">
          <h5 class="card-header text-black fw-semibold">
            <i class="{{$seccion->icono}}"></i>
            {{ $seccion->titulo }}
          </h5>

          <div class="card-body">
            <div class="row p-5">
              @foreach ($campos->where('seccion_tiempo_con_dios_id',$seccion->id)->sortBy('orden') as $campo)
              <div class="mb-3 {{ $campo->class }}">
                @if ($campo->valor)
                  @if ($campo->tipo_campo_tiempo_con_dios_id == 5)
                    <div class="border p-5 rounded">
                      <h5 class="text-black fw-semibold mb-2"> Mis versiculos favoritos </h5>
                      @foreach (json_decode($campo->valor, true) as $data)

                        <b> {{ $data['cita'] }}</b> <br>
                        <p>
                        @foreach ($data['versiculos'] as $verso)

                            <span class="text-black fs-6 lh-lg" >
                              <sup><b>{{  $verso['numero'] }}</b></sup> {{ $verso['texto'] }}
                            </span>

                        @endforeach
                        </p>
                      @endforeach
                    </div>
                  @else
                      {!! $campo->html !!}
                      <p>{{ $campo->valor }}</p>
                  @endif
                @else
                    <p>El campo 'valor' está vacío.</p>
                @endif
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
     @endforeach
    </div>

  </div>

</div>
@endsection
