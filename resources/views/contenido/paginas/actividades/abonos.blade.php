@php
$configData = Helper::appClasses();

use App\Helpers\Helpers;

use Illuminate\Support\Number;

@endphp

@extends('layouts/layoutMaster')

@section('title', 'Abonos')

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

    @media screen and (max-width:550px) {
        #container-left {
            display: none
        }

        ;
    }

</style>


@vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection


@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/js/app.js'])
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

<script type="module">
    flatpickr(".fecha-picker", {
            dateFormat: "Y-m-d",
            disableMobile: true,
            locale: 'es'
        });
    </script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Crear abonos</h4>


@include('layouts.status-msn')

@if ($actividad->activa == false)
<div class="alert alert-danger" role="alert" bis_skin_checked="1">
    La actividad se encuentra inactiva
</div>
@endif
<p class="mb-4 text-dark">Gestiona los abonos para tu actividad: <b>{{ $actividad->nombre }}</b></p>

@livewire('Actividades.abonos', [
'actividad' => $actividad,
])



@endsection
