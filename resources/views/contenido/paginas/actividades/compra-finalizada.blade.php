@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Extitosamente RV')

@section('vendor-style')


@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/apex-charts/apex-charts.scss'])

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

    <div class="d-flex align-items-center min-vh-100">

        <div class="container">

            <div class="row">
                <div class="col-12 col-lg-12 d-flex align-items-center">
                    <div class=" mx-auto my-auto text-center">
                        <img src="{{ Storage::url('generales/img/otros/dibujo_formulario_usuario_respuesta.png') }}"
                            class="w-50 p-0">
                        <h2 class="text-black fw-bold mb-0">Compra exitosa</h2>



                        <p class="text-black mt-1 mb-5"><b></b>
                        </p>
                        <div class="col-12 d-grid gap-5 d-sm-flex justify-content-center ">


                            <a href="{{ route('actividades.proximas') }}" type="button"
                                class="btn btn-primary rounded-pill px-7 py-2">
                                <span class="align-middle">Salir</span>
                            </a>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
