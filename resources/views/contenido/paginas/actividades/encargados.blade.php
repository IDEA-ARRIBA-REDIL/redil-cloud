@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Actividades')

<!-- Page -->
@section('page-style')


@section('vendor-style')
<style>
    .color-picker-container {
        width: 100px;
        /* Ajusta este valor al tamaño que necesites */

    }

    .pickr .pcr-button {
        height: 38px !important;
        width: 40px !important;
        border: solid 1px #3e3e3e;
    }

</style>


@vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss', 'resources/assets/vendor/libs/quill/editor.scss'])


@endsection


@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/js/app.js'])

@endsection


@section('page-script')

<script type="module">
    function sinComillas(e) {
            tecla = (document.all) ? e.keyCode : e.which;
            patron = /[\x5C'"]/;
            te = String.fromCharCode(tecla);
            return !patron.test(te);
        }
    </script>


@endsection


@section('content')


<h4 class="mb-1 fw-semibold text-primary">Encargados actividad</h4>
<p class="mb-4 text-dark">Gestiona los encargados y los cargos para tu actividad.: <b>{{ $actividad->nombre }}</b></p>

@include('layouts.status-msn')


@livewire('Actividades.cargosActividad', [
'actividad' => $actividad,
])





@endsection
