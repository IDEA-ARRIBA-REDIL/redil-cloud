@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Actividades')



<!-- Vendor Style -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
  'resources/assets/vendor/libs/rateyo/rateyo.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/pickr/pickr-themes.scss', 
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

<!-- Vendor Script -->
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
  'resources/assets/vendor/libs/rateyo/rateyo.js',
  'resources/assets/vendor/libs/pickr/pickr.js', 
  'resources/assets/vendor/libs/cleavejs/cleave.js',
  'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/js/app.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

<!-- Page Style -->
@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/wizard-ex-checkout.scss'
])
@endsection

<!-- Page Script -->
@section('page-script')
@vite([

  'resources/assets/js/wizard-ex-checkout.js'
])

<script>
   $(document).ready(function()
  {
    $('.select2').select2({
      dropdownParent: $('#wizard-checkout')
    });

    $(".fecha-picker").flatpickr({
            dateFormat: "Y-m-d",
            disableMobile: true
        });
  });
  </script>
@endsection

@section('content')
@include('_partials/wizard-ex-checkout')


@endsection
