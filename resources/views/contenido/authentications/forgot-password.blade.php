@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', '¿Olvidaste tu contraseña?')

@section('vendor-style')
@endsection

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
                <div class="mx-auto my-auto text-center" style="max-width: 480px;">
                    <img src="{{ Storage::disk('global_media')->url('confirmacion-exitosa-email.png') }}" class="w-50 p-0 mb-3" alt="Recuperar contraseña">
                    <h2 class="text-black fw-bold mb-0">¿Olvidaste tu contraseña?</h2>

                    <p class="text-black mt-1 mb-3">
                      Ingresa tu email y te enviaremos instrucciones para restablecer tu contraseña.
                    </p>

                    @include('layouts.status-msn')

                    @error('email')
                        <div class="alert alert-danger ti-12px mt-2 py-2">
                            <i class="ti ti-circle-x me-1"></i> {{ $message }}
                        </div>
                    @enderror

                    <form id="formulario" role="form" class="forms-sample mt-3" method="POST" action="{{ route('password.email') }}">
                      @csrf

                      <div class="row text-start">
                        <!-- email -->
                        <div class="mb-3 col-12">
                          <label for="email" class="form-label text-muted">Correo electrónico</label>
                          <input id="email" name="email" value="{{ old('email') }}" type="email" placeholder="Ingresa tu correo" class="form-control" autofocus required/>
                        </div>
                      </div>

                      <div class="d-grid gap-2 d-sm-flex justify-content-center mt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2">
                          <span class="align-middle me-sm-1 me-0">Enviar instrucciones</span>
                        </button>

                        <a href="{{ route('login') }}" class="btn btn-outline-secondary waves-effect rounded-pill px-5 py-2">
                          <span class="align-middle me-sm-1 me-0">Volver</span>
                        </a>
                      </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
