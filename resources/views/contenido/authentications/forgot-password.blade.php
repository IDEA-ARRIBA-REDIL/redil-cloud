










@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Mensaje verificar Email')

@section('vendor-style')

@section('page-style')
@endsection

@section('vendor-script')
@endsection


@section('page-script')
@endsection

@section('content')

<div class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-12 d-flex align-items-center">
                <div class=" mx-auto my-auto text-center">
                    <img src="{{ Storage::url('generales/img/otros/dibujo_formulario_usuario_respuesta.png') }}" class="w-50 p-0">
                    <h2 class="text-black fw-bold mb-0">¿Olvidaste tu contraseña?</h2>

                    <p class="text-black mt-1 mb-2">
                      Ingresa tu email y te enviaremos instrucciones para restablecer tu contraseña
                    </p>

                    <form id="formulario" role="form" class="forms-sample" method="POST" action="{{route('password.email')}}" enctype="multipart/form-data">
                      @csrf

                      @if (session('status'))
                          <div class="text-success ti-12px mt-2">
                              <i class="ti ti-circle-check"></i> {{ session('status') }}
                          </div>
                      @endif

                      @error('email')
                          <div class="text-danger ti-12px mt-2">
                              <i class="ti ti-circle-x"></i> {{ $message }}
                          </div>
                      @enderror
                      <div class="row text-start mt-3 ">
                        <!-- email -->
                        <div class="mb-3 col-12 offset-md-2 col-md-8">
                          <input id="email" name="email" value="{{ old('email') }}" type="email" placeholder="Ingresa el email" class="form-control" autofocus required/>
                        </div>
                        <!-- email -->
                      </div>

                      <div class="d-grid gap-2 d-sm-flex justify-content-center mt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-10 py-3" >
                          <span class="align-middle me-sm-1 me-0 ">Enviar instrucciones</span>
                        </button>

                        <a href="/" type="submit" class="btn btn-outline-secondary waves-effect rounded-pill px-7 py-2" >
                          <span class="align-middle me-sm-1 me-0 ">Salir</span>
                        </a>
                      </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
