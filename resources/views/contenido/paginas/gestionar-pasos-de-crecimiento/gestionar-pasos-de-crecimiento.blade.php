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


  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
    <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
        <i class="ti ti-arrow-left"></i>
    </a>
    <h4 class="mb-0 fw-semibold text-primary">Secciones y pasos de crecimiento</h4>
    </div>
    <p class="text-black mb-0 small">
    Aquí podrás gestionar los pasos de crecimientos.
    </p>
  </div>


@include('layouts.status-msn')

@livewire('gestionar-seccionesy-pasos-de-crecimiento.gestionar-secciones-y-pasos-de-crecimiento')

@endsection