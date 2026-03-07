@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Formularios')

<!-- Page -->
@section('vendor-style')

@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
])

@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
])
@endsection


@section('page-script')
<script type="module">
  $('#formulario').submit(function() {
    $('.btnGuardar').attr('disabled', 'disabled');

    Swal.fire({
      title: "Espera un momento",
      text: "Ya estamos guardando...",
      icon: "info",
      showCancelButton: false,
      showConfirmButton: false,
      showDenyButton: false
    });
  });
</script>
@endsection

@section('content')
<div class="row mb-4">

</div>


<h4 class="mb-1 fw-semibold text-primary">Secciones y pasos de crecimiento</h4>
<p class="mb-8">Aquí podrás gestionar los pasos de crecimientos.</p>

@include('layouts.status-msn')

@livewire('gestionar-seccionesy-pasos-de-crecimiento.gestionar-secciones-y-pasos-de-crecimiento')

@endsection