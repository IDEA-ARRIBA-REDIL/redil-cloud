@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Modo de lectura')

@section('page-style')
<style>
  body {
    overflow-x: hidden;
  }
  .circle-card {
    width: 170px;
    height: 170px;
    border-radius: 50%;
    border: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: box-shadow 0.3s, transform 0.3s;
    background-color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  }
  .circle-card:hover {
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
  }
  .circle-card img {
    width: 60px;
    height: auto;
    margin-bottom: 15px;
  }
  .circle-card span {
    font-weight: 700;
    color: #4a4a4a;
    font-size: 15px;
    text-align: center;
  }
</style>
@endsection

@section('content')
  <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
      <div class="col-3 text-start">
        <a href="{{ route('tiempoConDios.bienvenida') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="ti-xs ti ti-arrow-left me-2"></span>
          <span class="d-none d-md-block fw-normal">Volver</span>
        </a>
      </div>
      <div class="col-6 pl-5 text-center">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Tiempo con Dios</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ route('dashboard') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
  </nav>

  <div class="container mt-5">
    <div class="row justify-content-center text-center mt-5 pt-4">
      <div class="col-12 col-md-8 col-lg-7">
        <h4 class="fw-semibold mb-10 text-black">Elige tu modo de lectura</h4>
        <p class="text-black   mb-10 px-3">
          Puedes elegir la forma en que quieres realizar tu tiempo con Dios. <strong class="text-dark">Plan lector</strong> te impulsará a conocer más de Dios con temas que hemos preparado para ti, En la <strong class="text-dark">Lectura propia</strong> podrás elegir que lectura de la Biblia realizar el día de hoy.
        </p>

        <div class="d-flex justify-content-center gap-4 mt-5">
          <a href="{{ route('tiempoConDios.nuevo', ['modo' => 'plan']) }}" class="text-decoration-none" id="btn-plan-lector">
            <div class="circle-card">
              <img src="{{ Storage::disk('global_media')->url('Plan-lector.png') }}" alt="Plan lector" onerror="this.src='{{ Storage::disk('global_media')->url('tiempo_con_Dios_respuesta.png') }}'">
              <span>Plan lector</span>
            </div>
          </a>

          <a href="{{ route('tiempoConDios.nuevo', ['modo' => 'propia']) }}" class="text-decoration-none">
            <div class="circle-card">
              <img src="{{ Storage::disk('global_media')->url('Lectura-propia.png') }}" alt="Lectura propia" onerror="this.src='{{ Storage::disk('global_media')->url('tiempo_con_Dios_respuesta.png') }}'">
              <span>Lectura propia</span>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
