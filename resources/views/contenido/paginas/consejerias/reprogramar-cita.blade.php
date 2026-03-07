@extends('layouts/blankLayout')

@section('title', 'Reprogramar Cita')

@section('page-style')
<style>
  body {
    overflow-x: hidden;
  }

 .day-scroll-container {
  overflow-x: auto;
  white-space: nowrap;
  flex-grow: 1;
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.day-scroll-container::-webkit-scrollbar {
  display: none;
}
</style>
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
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Reprogramar cita</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ url()->previous() }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
    </nav>

     @livewire('consejeria.reprogramar-cita', ['citaId' => $cita->id, 'origen' => $origen])
  </div>

@endsection
