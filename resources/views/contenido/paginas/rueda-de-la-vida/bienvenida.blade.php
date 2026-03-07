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
</style>
@endsection

@section('vendor-script')
@endsection


@section('page-script')
<script>
  // Obtener la altura del <nav>
  const navHeight = document.querySelector('nav').offsetHeight;

  // Obtener el elemento con la imagen de fondo
  const imagenContainer = document.querySelector('#imagen');
  const textoContainer = document.querySelector('#texto');

  // Aplicar la altura calculada al elemento
  imagenContainer.style.height = `calc(100vh - ${navHeight}px)`;
  textoContainer.style.height = `calc(100vh - ${navHeight}px)`;
</script>
@endsection

@section('content')
  <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
      <div class="col-3 text-start">
        <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
          <span class="ti-xs ti ti-arrow-left me-2"></span>
          <span class="d-none d-md-block fw-normal">Volver</span>
        </button>
      </div>
      <div class="col-6 pl-5 text-center">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">{{$configuracionRv->nombre_general}} - Bienvenida</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ url()->previous() }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
  </nav>

  <div class="row pt-0 px-0 px-sm-0">
    <!-- texto -->
    <div id="texto" class="col-lg-6 col-md-6 col-12 d-flex align-items-center">
      <div class="col-10 offset-1 col-md-8 offset-md-2">
        <h3 class="text-primary fw-semibold pb-3"> {{$configuracionRv->nombre_general}} </h3>
        <p class="fw-bold text-black fs-6 pb-3"> Dios te ama y desea tener una relación íntima contigo</p>
        <p class="fs-6 text-black"> Comienza a realizar tu diagnóstico, establece metas y hábitos para tu vida.</p>
        <ul class="fs-6 text-black pb-4">
            <li> Califica de 1 a 10 tus hábitos </li>
            <li> Analiza tu promedio </li>
            <li> Establece nuevas metas y hábitos </li>
        </ul>

        <div class="p-3 d-flex mb-7" style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
          <i class="ti ti-book text-secondary me-2"></i>
          <p class="m-0"> La información que suministres solo podrás acceder tú</p>
        </div>

        <a href="{{route('ruedaDeLaVida.nueva')}}">
          <button type="button" class="btn btn-primary rounded-pill px-7 py-2">
            <span class="align-middle me-sm-1 me-0 px-7 ">Comenzar</span>
          </button>
        </a>
      </div>
    </div>
    <!-- /texto -->

    <!-- imagen -->
    <div id="imagen" class="col-lg-6 col-md-6 col-sm-12 d-none d-md-block" style="background-image: url('{{Storage::url($configuracion->ruta_almacenamiento.'/img/rueda-vida/bienvenida.png')}}'); background-size: cover;  background-position: center;" >
    </div>
     <!-- /imagen -->
  </div>
@endsection
