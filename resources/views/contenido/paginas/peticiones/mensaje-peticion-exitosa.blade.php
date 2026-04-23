@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Petición enviada')

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
            <img src="{{ Storage::disk('global_media')->url('Felicidades.png') }}" class="img-fluid p-0" style="width: 120px; height: 120px; object-fit: contain;">
            <h2 class="text-black fw-bold mb-0 lh-sm mt-3">Petición recibida</h2>
            <p class="text-black mt-1">
              ¡Muy bien!, tu petición ha sido recibida con éxito. Estaremos orando por ti.
            </p>
          </div>


          <!-- CARD DE INFORMACIÓN DE LA PETICIÓN -->
          <div class="card mb-5" style="background-color: #f8f7fa">
              <div class="card-body">
       

                    <div class="row text-start shadow rounded p-4">

                      <p class="col-12 fw-semibold text-black mb-2"> Detalle de la petición </p>

                      <div class="col-12 col-sm-6 d-flex flex-column px-2 my-1">
                        <small class="text-black">Tipo</small>
                        <small class="fw-semibold text-black ">{{$peticion->tipoPeticion->nombre}}</small>
                      </div>
                      <div class="col-12 col-sm-6 d-flex flex-column px-2  my-1">
                        <small class="text-black">Nombre</small>
                        <small class="fw-semibold text-black ">
                          {{ $peticion->user_id ? $peticion->usuario->nombre(3) : $peticion->nombre_externo }}
                        </small>
                      </div>
                      <div class="col-12 d-flex flex-column px-2 my-1">
                        <small class="text-black">Fecha</small>
                        <small class="fw-semibold text-black ">{{ $peticion->created_at->isoFormat('D [de] MMMM [de] YYYY - HH:mm A') }}</small>
                      </div>

                      <div class="col-12 d-flex flex-column  my-1">
                        <small class="text-black">Descripción</small>
                        <small class="fw-semibold text-black ">{{ $peticion->descripcion }}</small>
                      </div>
                    </div>
              </div>
          </div>

          <div class=" mx-auto my-auto text-center">
            <div class="d-grid gap-2 d-sm-flex justify-content-center pt-3">
              @if(auth()->check())
                <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-10 py-3" >
                  <span class="align-middle me-sm-1 me-0 ">Ir al inicio</span>
                </a>
                <a href="{{ route('peticion.nueva') }}" class="btn btn-outline-secondary rounded-pill px-10 py-3" >
                  <span class="align-middle me-sm-1 me-0 ">Nueva petición</span>
                </a>
              @else
                <a href="/" class="btn btn-primary rounded-pill px-10 py-3" >
                  <span class="align-middle me-sm-1 me-0 ">Volver</span>
                </a>
                <a href="{{ route('peticion.publica.nueva') }}" class="btn btn-outline-secondary rounded-pill px-10 py-3" >
                  <span class="align-middle me-sm-1 me-0 ">Nueva petición</span>
                </a>
              @endif
            </div>
          </div>
      </div>
    </div>
  </div>
</div>

@endsection
