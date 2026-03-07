@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Reporte de grupo')

@section('page-style')
<style>
  body {
    overflow-x: hidden;
  }

 .day-scroll-container {
  overflow-x: auto;   /* Permite scroll horizontal */
  white-space: nowrap;/* Evita que los botones salten a la línea de abajo */
  flex-grow: 1;       /* IMPORTANTE: Ocupa todo el espacio sobrante */

  /* Ocultar la barra de scroll (estética) */
  -ms-overflow-style: none;  /* IE y Edge */
  scrollbar-width: none;     /* Firefox */
}

/* Ocultar la barra de scroll para Chrome, Safari y Opera */
.day-scroll-container::-webkit-scrollbar {
  display: none;
}

</style>
@endsection

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/form-basic-inputs.js'])
@endsection


@section('content')

  <div class="min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
      <div class="col-3 text-start">
        <a href="{{  url()->previous() }}" type="button" class="d-none btn rounded-pill waves-effect waves-light text-white prev-step">
          <span class="ti-xs ti ti-arrow-left me-2"></span>
          <span class="d-none d-md-block fw-normal">Volver</span>
        </a>
      </div>
      <div class="col-6 pl-5 text-center">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Mi cita</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ url()->previous() }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
    </nav>



    @livewire('consejeria.nueva-cita', ['pacienteId' => $usuario->id])
  </div>



@endsection
