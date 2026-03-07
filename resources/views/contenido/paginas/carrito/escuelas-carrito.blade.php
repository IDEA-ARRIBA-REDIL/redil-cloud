@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Actividades')

<!-- Vendor Style -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/rateyo/rateyo.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

<!-- Vendor Script -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/rateyo/rateyo.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/js/app.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

<!-- Page Style -->
@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/wizard-ex-checkout.scss'])
@endsection

<!-- Page Script -->
@section('page-script')
    @vite(['resources/assets/js/wizard-ex-checkout.js'])

    <script>
        $(document).ready(function() {



            $(".fecha-picker").flatpickr({
                dateFormat: "Y-m-d",
                disableMobile: true
            });
        });
    </script>
@endsection

@section('page-script')
@endsection


@section('content')

    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
        <div class="col-3 text-start">
            <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
                <span class="ti-xs ti ti-arrow-left me-2"></span>
                <span class="d-none d-md-block fw-normal">Volver</span>
            </button>
        </div>
        <div class="col-6 pl-5 text-center">
            <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Carrito de compras escuelas</h5>
        </div>
        <div class="col-3 text-end">
            <a href="{{ route('dashboard') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
                <span class="d-none d-md-block fw-normal">Salir</span>
                <span class="ti-xs ti ti-x mx-2"></span>
            </a>
        </div>
    </nav>
    <!-- Secciones -->
    <div class="col-12 col-sm-8 offset-sm-2 col-lg-8  offset-lg-2">
        <div class="step row " id="step-1">
            <div class="p-4 col-12">
                <div class="d-flex align-items-start p-2 mt-1">
                    <div class="badge rounded rounded-circle bg-label-primary p-3 me-1 rounded">
                        <i class="ti ti-shopping-cart ti-md"></i>
                    </div>
                    <div class="my-auto ms-1 ">
                        <small class="text-muted">Paso {{ $contador }} de {{ $totalSecciones }} </small>

                        <h6 class="mb-0">Carrito </h6>
                    </div>
                </div>
                <div class="progress mx-2">
                    <div id="progress-bar" class="progress-bar" role="progressbar"
                        style="width: {{ ($contador / $totalSecciones) * 100 }}%;"
                        aria-valuenow="{{ ($contador / $totalSecciones) * 100 }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>


        @livewire('Carrito.escuelasCarrito', [
            'actividad' => $actividad,
            'compraActual' => $compra,
            'primeraVez' => $primeraVez,
            'categoriasHabilitadas' => $categoriasHabilitadas,
        ])

    </div>
@endsection
