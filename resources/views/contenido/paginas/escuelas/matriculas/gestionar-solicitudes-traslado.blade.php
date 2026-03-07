@section('isEscuelasModule', true)

@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Solicitudes de Traslado')

@section('vendor-style')
    <style>
        .color-picker-container {
            width: 100px;
        }
        .pickr .pcr-button {
            height: 38px !important;
            width: 40px !important;
            border: solid 1px #3e3e3e;
        }
        @media screen and (max-width:550px) {
            #container-left {
                display: none
            }
        }
    </style>
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/js/app.js'])
@endsection

@section('page-script')
    <script>
        // Listeners globales para SweetAlert
        Livewire.on('swal:success', data => {
            Swal.fire({
                title: data[0].title,
                text: data[0].text,
                icon: 'success',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        });

        Livewire.on('swal:error', data => {
            Swal.fire({
                title: data[0].title,
                text: data[0].text,
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        });

        Livewire.on('recargarPagina', () => {
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        });
    </script>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @livewire('matricula.gestionar-solicitudes-traslado')
    </div>
@endsection
