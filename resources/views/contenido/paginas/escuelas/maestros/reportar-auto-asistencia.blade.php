@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Mi asistencia')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
    <div class="d-flex align-items-center min-vh-100">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-12 d-flex align-items-center">
                    <div class=" mx-auto my-auto text-center">

                        {{-- La acción del formulario ahora apunta a la nueva ruta POST --}}
                        <form id="formulario" role="form" class="forms-sample" method="POST"
                            action="{{ route('maestros.registrarAutoAsistenciaEstudiante', ['horarioAsignado' => $horarioAsignado->id, 'reporte' => $reporte->id]) }}"
                            enctype="multipart/form-data">
                            @csrf
                            <img src="{{ Storage::url('generales/img/otros/dibujo_respuesta.png') }}"
                                class="img-fluid w-50 p-0">

                            @if ($puedeReportar == false)
                                <h2 class="text-black fw-bold mb-0 lh-sm">Link de asistencia</h2>
                                <p class="text-black mt-1 mb-5">
                                    ¡Ups! El link de asistencia ha caducado o no está disponible en este momento.
                                </p>

                                <div class="p-3 d-flex mb-3"
                                    style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
                                    <i class="ti ti-bulb text-secondary me-2"></i>
                                    <p class="m-0">Si no alcanzaste a reportar tu asistencia, por favor, contacta a tu
                                        maestro.</p>
                                </div>
                            @else
                                <h2 class="text-black fw-bold mb-0 lh-sm">Reportar Mi Asistencia</h2>
                                <p class="text-black mt-1 mb-2">
                                    Clase: {{ $horarioAsignado->materiaPeriodo->materia->nombre ?? 'N/A' }} <br>
                                    Fecha:
                                    {{ \Carbon\Carbon::parse($reporte->fecha_clase_reportada)->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                                </p>
                                <p class="text-black mt-1 mb-5">
                                    Ingresa tu número de documento o nombre completo.
                                </p>

                                <div class="row text-start mt-3 ">
                                    <div class="mb-3 col-12 offset-md-2 col-md-8">
                                        <input id="buscar" name="buscar" value="{{ old('buscar') }}" type="text"
                                            placeholder="No. Documento o Nombre Completo" class="form-control" autofocus />

                                        {{-- Mostrar errores de validación de 'buscar' --}}
                                        @error('buscar')
                                            <div class="text-danger ti-12px mt-2"> <i
                                                    class="ti ti-circle-x"></i>{{ $message }}</div>
                                        @enderror

                                        {{-- Mostrar errores generales o mensajes de éxito pasados con withErrors --}}
                                        @if ($errors->has('error'))
                                            <div class="text-danger ti-12px mt-2"> <i
                                                    class="ti ti-circle-x"></i>{{ $errors->first('error') }}</div>
                                        @endif

                                        @if ($errors->has('success'))
                                            <div class="text-success ti-12px mt-2"> <i
                                                    class="ti ti-circle-check"></i>{{ $errors->first('success') }}</div>
                                        @endif
                                        @if ($errors->has('no_existe'))
                                            <div class="text-danger ti-12px mt-2"> <i
                                                    class="ti ti-circle-x"></i>{{ $errors->first('no_existe') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-sm-flex justify-content-center mt-3">
                                    <button type="submit" class="btn btn-primary rounded-pill px-10 py-3">
                                        <span class="align-middle me-sm-1 me-0 ">Confirmar asistencia</span>
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
