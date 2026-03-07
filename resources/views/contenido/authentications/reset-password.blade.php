@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Restablecer contraseña')

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
                    <h2 class="text-black fw-bold mb-0">Restablecer contraseña</h2>

                    <p class="text-black mt-1 mb-2">
                      Ingresa una nueva contraseña diferente a las usadas anteriormente
                    </p>

                    <form id="formulario" role="form" class="forms-sample" method="POST" action="{{route('password.store')}}" enctype="multipart/form-data">
                      @csrf

                        <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial (*, -, ., ?, &, $, #).</div>
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        @error('password')
                            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i> {{ $message }}</div>
                        @enderror
                        @error('email')
                            <div class="text-center text-danger ti-12px mt-2 mb-2"> <i class="ti ti-circle-x"></i> {{ $message }}</div>
                        @enderror

                        <div class="row text-start mt-3 ">
                            <input type="hidden" id="email" name="email" value="{{ $request->email ?? old('email') }}" required readonly />

                            <div class="mb-3 col-12 offset-md-1 col-md-10">
                                <label for="password" class="form-label d-none">Nueva Contraseña</label>
                                <input id="password" name="password" type="password" placeholder="Ingresa la nueva contraseña" class="form-control" autofocus required autocomplete="new-password"/>
                            </div>


                            <div class="mb-3 col-12 offset-md-1 col-md-10">
                                <label for="password_confirmation" class="form-label  d-none">Confirmar Contraseña</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirma la nueva contraseña" class="form-control" required autocomplete="new-password"/>
                            </div>

                        </div>

                      <div class="d-grid gap-2 d-sm-flex justify-content-center mt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-10 py-3" >
                          <span class="align-middle me-sm-1 me-0 ">Restablecer contraseña</span>
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
