@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Inscripción exitosa')

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
                    <h2 class="text-black fw-bold mb-0">Inscripción exitosa</h2>

                    @if(isset($mensajeTipo) && $mensajeTipo== 1)
                    <p class="text-black mt-1 mb-5"><b>{{ $usuario->nombre(3) }}</b> fue creado de manera exitosa. ¿Deseas agregar otro hijo?
                    </p>
                    <div class="col-12 d-grid gap-5 d-sm-flex justify-content-center ">
                      <a href="{{ url()->previous() }}" type="button" class="btn btn-primary rounded-pill px-7 py-2" >
                        <span class="align-middle me-sm-1 me-0 ">Sí, crear otro hijo</span>
                      </a>

                      <a href="{{ route('login') }}" type="button" class="btn btn-label-secondary  rounded-pill btn-outline-secondary px-7 py-2 prev-step" >
                        <span class="align-middle">No, salir</span>
                      </a>
                    </div>
                    @else
                    <p class="text-black mt-1 mb-5">En tu correo encontrarás el nombre de usuario y contraseña.</p>
                    <div class="col-12 d-grid gap-2 d-sm-flex justify-content-center ">
                      <a href="{{ route('login') }}" type="button" class="btn btn-primary rounded-pill px-7 py-2" >
                        <span class="align-middle me-sm-1 me-0 ">Ingresar</span>
                      </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
