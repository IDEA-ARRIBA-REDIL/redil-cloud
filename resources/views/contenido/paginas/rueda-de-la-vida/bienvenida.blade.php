@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Bienvenida')

@section('vendor-style')


@section('page-style')
<style>
  body {
    overflow-x: hidden;
  }
  .main-content-row {
    min-height: calc(100vh - 72px); /* Ajuste basado en el padding del navbar */
  }
  #imagen {
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 400px; /* Asegura visibilidad en móviles si se muestra */
  }
  @media (min-width: 768px) {
    #imagen {
      min-height: 100%;
    }
  }
</style>
@endsection

@section('vendor-script')
@endsection


@section('page-script')

@endsection

@section('content')
  <nav class="navbar navbar-expand-lg navbar-dark bg-menu-theme p-3 row justify-content-md-center mx-0">
      <div class="col-3 text-start">
        <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
          <span class="ti-xs ti ti-arrow-left me-2"></span>
          <span class="d-none d-md-block fw-normal">Volver</span>
        </button>
      </div>
      <div class="col-6 text-center px-0">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">{{$configuracionRv->nombre_general}} - Bienvenida</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ url()->previous() }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
  </nav>

  <div class="row mx-0 pt-0 px-0 main-content-row">
    <!-- texto -->
    <div id="texto" class="col-lg-6 col-md-6 col-12 d-flex align-items-center justify-content-center py-5">
      <div class="col-10 col-md-8">
        <h3 class="text-primary fw-semibold pb-3"> {{$configuracionRv->nombre_general}} </h3>
        <p class="fw-bold text-black fs-6 pb-3"> Dios te ama y desea tener una relación íntima contigo</p>
        <p class="fs-6 text-black"> Comienza a realizar tu diagnóstico, establece metas y hábitos para tu vida.</p>
        <ul class="fs-6 text-black pb-4">
            <li> Califica de 1 a 10 tus hábitos </li>
            <li> Analiza tu promedio </li>
            <li> Establece nuevas metas y hábitos </li>
        </ul>

        <div class="p-4 d-flex mb-4" style="color:black; font-size:12px; border: solid 1.5px #95CDDF; border-radius: 14px; background-color: #f0faff;">
          <i class="ti ti-book text-secondary me-2 fs-4"></i>
          <p class="m-0 align-self-center"> La información que suministres solo podrás acceder tú</p>
        </div>

        <a href="{{route('ruedaDeLaVida.nueva')}}" class="d-inline-block mt-3">
          <button type="button" class="btn btn-primary rounded-pill px-5 py-2 waves-effect waves-light">
            <span class="align-middle px-3">Comenzar</span>
          </button>
        </a>
      </div>
    </div>
    <!-- /texto -->

    <!-- imagen -->
    <div id="imagen" class="col-lg-6 col-md-6 col-sm-12 d-none d-md-block px-0" style="background-image: url('{{ Storage::exists('img/rueda-de-la-vida/banner-bienvenida.png') ? tenant_asset('img/rueda-de-la-vida/banner-bienvenida.png') : Storage::disk('global_media')->url('/rueda-de-la-vida/banner-bienvenida.png')}}'); background-size: cover;  background-position: center;" >
    </div>
     <!-- /imagen -->
  </div>
@endsection
