{{-- Este es el archivo "anfitrión" que carga tu controlador --}}

@extends('layouts/layoutMaster')

{{-- Define el título de la página --}}
@section('title', 'Gestionar Taquillas')

{{-- Carga los estilos JS/CSS necesarios para esta página --}}
@section('page-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js'])
@endsection

{{-- En el contenido, simplemente llama al componente Livewire --}}
@section('content')

    {{-- 
      AQUÍ ESTÁ LA MAGIA:
      Esta vista (cargada por el controlador) ahora carga el componente Livewire
      y le pasa la variable $tipo que recibió del controlador.
    --}}
    @livewire('puntos-de-pago.gestionar-taquillas', ['tipo' => $tipo])

@endsection
