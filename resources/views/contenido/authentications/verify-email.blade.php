










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
                    <h2 class="text-black fw-bold mb-0">Confirma tu email para continuar</h2>

                    <p class="text-black mt-1 mb-2"> Te enviamos un mensaje de verificación a <b>{{ auth()->user()->email }}</b>.<br> Completa este paso para activar tu cuenta.
                    <br><br>¿No llegó? Revisa promociones o spam...
                    </p>

                    <form method="POST" action="{{ route('verification.send') }}">
                      @csrf
                      <button type="submit" class="btn btn-text-primary waves-effect fw-semibold mb-3" >
                        <span class="align-middle me-sm-1 me-0 ">Reenviar correo <i class="ti ti-reload"></i></span>
                      </button>
                    </form>

                    <div class="col-12 d-grid gap-5 d-sm-flex justify-content-center ">
                      <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary rounded-pill px-7 py-2" >
                        <span class="align-middle me-sm-1 me-0 ">Salir</span>
                        </button>
                      </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
