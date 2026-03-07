@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Historial de Anulaciones')


@section('vendor-style')
  @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection
@section('vendor-script')
 @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/js/app.js'])
<script>
    $(document).ready(function() {
        $('.fecha-picker').flatpickr({
            dateFormat: "Y-m-d",
            disableMobile: true,

        });
    });
</script>
 @endsection

@section('content')
    <h4 class="text-primary fw-bold py-3 mb-4">
        Historial de anulaciones
    </h4>

    @livewire('taquilla.historial-modificaciones')
@endsection
