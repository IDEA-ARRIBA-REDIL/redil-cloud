@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Inscripción')

@section('vendor-style')


@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])

<style>
    body {
        margin: 0;
        /* Elimina los márgenes predeterminados del cuerpo y html */
        padding: 0;
    }

    #container-completo {
        min-height: 50vh;
        height: auto;
    }

    body {
        overflow-x: hidden;
    }

</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection


@section('page-script')
@vite(['resources/assets/js/form-basic-inputs.js'])



@endsection

@section('content')

<div class="row card">
    <div class="col-lg-10 col-xl-8 mx-auto">
        <div class="card p-4">

            <div class="card-header text-center row">

                @if ($configuracion->version == 1)
                <div class="text-center mb-4 col-12">

                    {{-- La imagen ahora usa la variable $icono que viene del controlador --}}
                    <img style="width: 240px; height: 240px;" src="{{ Storage::url('generales/img/otros/verificacion.png') }}" class="p-0">
                </div>
                @else
                <div class="col-md-6  text-center col-12">
                    <img style="width: 140px; height: 140px;" src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/iglesia/' . $iglesia->logo) }}" class="p-0">
                </div>
                <div class="text-center mb-4 col-md-6 col-12">
                    {{-- La imagen ahora usa la variable $icono que viene del controlador --}}
                    <img style="width: 140px; height: 140px;" src="{{ Storage::url('generales/img/otros/verificacion.png') }}" class="p-0">
                </div>
                @endif

                <h2 class="text-black fw-bold mb-0 lh-sm mt-3">{{ $titulo }} </h2>

                <p>{{ $mensaje }} {{ $actividad->nombre }} !</p>

            </div>
            <div class="card-body p-sm-5">
                <div class="row px-6 py-9 rounded shadow border-top-0 border-5">
                    <div class="text-center mb-4 border-bottom pb-4 col-12">
                        <h5 class="fw-semibold"> Actividad:{{ $actividad->nombre }}</h5>

                        @switch($inscripcion->estado)

                        @case(1) {{-- Estado: Iniciada --}}
                        <div class="alert alert-info" role="alert">
                            <b>Tu inscripción ha sido iniciada.</b> Una vez completes todos los pasos necesarios, recibirás tu confirmación final y tu código QR de acceso sera habilitado.
                        </div>
                        @break

                        @case(2) {{-- Estado: Pendiente --}}
                        <div class="alert alert-warning" role="alert">
                            <b>Tu inscripción está pendiente de aprobación.</b> Este no es tu QR de acceso final. Recibirás un correo de confirmación con tu código QR definitivo una vez que nuestro equipo haya revisado tu solicitud.
                        </div>
                        @break

                        @case(3) {{-- Estado: Finalizada --}}
                        <div class="alert alert-success" role="alert">
                            <b>Ten presente que debes guardar este código QR, porque debes presentarlo previo a tu ingreso al evento.</b>
                        </div>
                        @break

                        @default {{-- Un caso por si el estado es inesperado --}}
                        <div class="alert alert-secondary" role="alert">
                            El estado de tu inscripción está siendo procesado.
                        </div>

                        @endswitch
                        @if(($inscripcion->estado) > 2)
                        <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($datosParaQr . '', 'QRCODE') }}" style="width: 140px; height: 140px;" alt="barcode" />

                        <div class="mt-3">
                            <a href="{{ route('inscripcion.ticket', ['inscripcion' => $inscripcion->id]) }}"
                                class="btn btn-secondary rounded-pill"
                                target="_blank"> {{-- target="_blank" es opcional pero recomendado --}}
                                <i class="ti ti-download me-1"></i>
                                Descargar ticket PDF
                            </a>
                        </div>
                        @endif
                    </div>

                    <div class="col-md-6 col-12 text-center mt-5 ">
                        <p style="font-size:10px" class="mb-2"> {{ $iglesia->nombre }}</p>
                        <p style="font-size:10px" class="mb-2"> Nit: {{ $iglesia->identificacion }}</p>
                        <p style="font-size:10px" class="mb-2">Direccion: {{ $iglesia->direccion }}</p>


                    </div>
                    <div class="col-md-6 col-12 text-center mt-5 ">
                        <p style="font-size:10px" class="mb-2">Pbx : {{ $iglesia->telefono1 }}</p>
                        <p style="font-size:10px" class="mb-2"> E-mail: {{ $iglesia->email_soporte }}</p>
                    </div>

                </div>
            </div>


        </div>
        <div class="row mb-7">
            <div class="col-12 text-center">
                <a href="{{ route('actividades.proximas') }}" class="btn btn-primary rounded-pill ">
                    <i class="ti ti-arrow-left me-1"></i>
                    Volver a las actividades
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
