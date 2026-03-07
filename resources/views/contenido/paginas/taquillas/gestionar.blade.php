@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Taquilla - Punto de Pago')


@section('vendor-style')
  @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection
@section('vendor-script')
 @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/js/app.js'])
@endsection

@section('content')



@livewire('taquilla.operacion-taquilla', [
   
      'cajaActiva' => $cajaActiva,
    
  ])



@endsection