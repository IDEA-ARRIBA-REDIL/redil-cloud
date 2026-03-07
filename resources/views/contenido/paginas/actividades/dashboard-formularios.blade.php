@php
$configData = Helper::appClasses();

use App\Helpers\Helpers;
use Illuminate\Support\Number;

@endphp

@extends('layouts/layoutMaster')

@section('title', 'Respuestas formularios')

<!-- Page -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/rateyo/rateyo.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
    <style>
        .color-picker-container {
            width: 100px;
        }
        .pickr .pcr-button {
            height: 38px !important;
            width: 40px !important;
            border: solid 1px #3e3e3e;
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/rateyo/rateyo.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/js/app.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/wizard-ex-checkout.scss'])
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
<h4 class="mb-1 fw-semibold text-primary">Categorías Actividad</h4>



@include('layouts.status-msn')

@if ($actividad->activa == false)
<div class="alert alert-danger" role="alert" bis_skin_checked="1">
    La actividad se encuentra inactiva
</div>
@endif
<p class="mb-4 mt-5 text-dark">Aquí gestionar las inscripciones y respuestas de los formularios de la actividad:
    <b>{{ $actividad->nombre }}</b>
</p>

@livewire('actividades.dashboard-formularios', ['actividad' => $actividad])

<hr>

@endsection
