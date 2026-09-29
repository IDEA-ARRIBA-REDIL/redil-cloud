@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Restablecer contraseña')

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
                    <img src="{{ Storage::disk('global_media')->url('restablecimiento-contraseña.png') }}" class="w-50 p-0 mb-3" alt="Restablecer contraseña">
                    <h2 class="text-black fw-bold mb-0">Restablecer contraseña</h2>

                    <p class="text-black mt-1 mb-2">
                      Ingresa una nueva contraseña diferente a las usadas anteriormente.
                    </p>

                    @include('layouts.status-msn')

                    <form id="formulario" role="form" class="forms-sample text-start mt-3" method="POST" action="{{ route('password.store') }}">
                      @csrf

                        <div class="ti-12px mb-3 text-muted">
                          <i class="text-info ti ti-info-circle me-1"></i>Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial (*, -, ., ?, &, $, #).
                        </div>

                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        @error('password')
                            <div class="alert alert-danger ti-12px py-2 mb-3"> <i class="ti ti-circle-x me-1"></i> {{ $message }}</div>
                        @enderror
                        @error('email')
                            <div class="alert alert-danger ti-12px py-2 mb-3"> <i class="ti ti-circle-x me-1"></i> {{ $message }}</div>
                        @enderror

                        <input type="hidden" id="email" name="email" value="{{ $request->email ?? old('email') }}" required readonly />

                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input id="password" name="password" type="password" placeholder="Ingresa la nueva contraseña" class="form-control" autofocus required autocomplete="new-password"/>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirma la nueva contraseña" class="form-control" required autocomplete="new-password"/>
                        </div>

                      <div class="d-grid gap-2 d-sm-flex justify-content-center mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2">
                          <span class="align-middle me-sm-1 me-0">Restablecer contraseña</span>
                        </button>

                        <a href="{{ route('login') }}" class="btn btn-outline-secondary waves-effect rounded-pill px-5 py-2">
                          <span class="align-middle me-sm-1 me-0">Salir</span>
                        </a>
                      </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
