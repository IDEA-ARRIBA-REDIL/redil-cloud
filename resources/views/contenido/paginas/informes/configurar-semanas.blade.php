@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Configurar semanas')

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

@endsection

@section('content')

  <div class="col-12 min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
      <div class="col-3 text-start">
        <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
          <span class="ti-xs ti ti-arrow-left me-2"></span>
          <span class="d-none d-md-block fw-normal">Volver</span>
        </button>
      </div>
      <div class="col-6 pl-5 text-center">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Configurar semanas</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ url()->previous() }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
    </nav>

    <div class="pt-5 px-7 px-sm-0" style="padding-bottom: 100px;">
      <div class="col-12 col-sm-8 offset-sm-2 col-lg-8  offset-lg-2">
          @livewire('Generales.configurar-semanas')
      </div>
    </div>

    <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
      <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex justify-content-sm-end ">

        <a href="{{ url()->previous() }}" type="button" class="btn btn-primary rounded-pill px-7 py-2" >
          <span class="align-middle me-sm-1 me-0 ">Finalizar</span>
        </a>
      </div>
    </div>

  </div>

@endsection
