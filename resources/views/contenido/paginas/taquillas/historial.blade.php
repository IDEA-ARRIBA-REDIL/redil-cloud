@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Historial de Transacciones')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/js/app.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])

    <script>
        $(document).ready(function() {
            $('.flatpickr-date').flatpickr({
                dateFormat: "Y-m-d",
                disableMobile: true,
                mode: "range",
            });
        });
    </script>

@endsection

@section('content')
    <h4 class="text-primary fw-bold py-3 mb-4">
        Historial de transacciones - {{ $cajaActiva->nombre }}
    </h4>

    <div class="row">
        <div class="col-12">
            @livewire('taquilla.historial-transacciones', ['cajaActiva' => $cajaActiva])
        </div>
    </div>
@endsection
