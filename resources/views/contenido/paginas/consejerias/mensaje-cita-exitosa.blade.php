@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Cita exitosa')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection


@section('content')

<div class="d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="container my-5" style="padding-bottom: 100px;">
      <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2">
          <div class=" mx-auto my-auto text-center">
            <img src="{{ Storage::url('generales/img/otros/tiempo_con_Dios_respuesta.png') }}" class="img-fluid w-25 p-0">
            <h2 class="text-black fw-bold mb-0 lh-sm mt-3">Mi cita</h2>
            <p class="text-black mt-1 mb-10">
              ¡Muy bien!, la cita fue agendada con éxito.
          </div>
          @include('layouts.status-msn')
          <!-- CARD DE INFORMACIÓN DE LA REUNIÓN -->
          <div class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
              <div class="card-header pb-1">
                  <h6 class="card-title mb-0 fw-semibold">Información de la cita</h6>
              </div>
              <div class="card-body">
                  <h4 class="fw-bold text-black">{{ $cita->tipoConsejeria->nombre }}</h4>
                  <hr>
                  <div class="row">
                      <div class="col-12 col-md-12 mb-3">
                          <small class="">Fecha y hora</small>
                          <p class="fw-semibold text-black mb-0">{{ $cita->fecha_hora_inicio->isoFormat('D [de] MMMM [de] YYYY - HH:mm A') }} ({{ config('app.timezone') }})</p>
                      </div>
                      <div class="col-12 col-md-6 mb-3">
                          <small class="">Consejero</small>
                          <p class="fw-semibold text-black mb-0">{{ $cita->consejero->usuario->nombre(3) }}</p>
                      </div>
                      <div class="col-12 col-md-6 mb-3">
                          <small class="">Medio</small>
                          <p class="fw-semibold text-black mb-0">{{ $cita->medio == 1 ? 'Presencial' : 'Reunión Virtual' }}</p>
                      </div>
                      @if($cita->medio == 1)
                      <div class="col-12 col-md-6 mb-3">
                          <small class="">Lugar</small>
                          <p class="fw-semibold text-black mb-0">{{ $cita->consejero->direccion }}</p>
                      </div>
                      @else
                        <div class="col-12 col-md-6 mb-3">
                          <small class="">Link</small>
                          @if($cita->enlace_virtual)
                              <a href="{{ $cita->enlace_virtual }}" target="_blank" class="fw-semibold">{{ $cita->enlace_virtual }}</a>
                          @else
                              <p class="fw-semibold text-black mb-0">No generado</p>
                          @endif
                      </div>
                      @endif


                      <hr>
                      <div class="col-12 col-md-6 mb-3 mb-md-0">
                          <small class="">Detalle</small>
                          <p class="fw-semibold text-black mb-0">{{ $cita->notas_paciente ?? 'Sin notas' }}</p>
                      </div>
                  </div>
              </div>
          </div>


          <div class=" mx-auto my-auto text-center">
            <div class="d-grid gap-2 d-sm-flex justify-content-center pt-3">
              <a href="{{ route('dashboard') }}" type="button" class="btn btn-primary rounded-pill px-10 py-3" >
                <span class="align-middle me-sm-1 me-0 ">Salir</span>
              </a>
            </div>
          </div>
      </div>
    </div>
  </div>
</div>


@endsection
