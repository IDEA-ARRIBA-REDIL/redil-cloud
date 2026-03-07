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
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
])

@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js',
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
  <ul class="nav nav-pills mb-3 d-flex justify-content-end" role="tablist">

    <li class="nav-item me-1">
      <a href="{{ route('formularioUsuario.modificar', $formulario) }}">
        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
          <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">1</span>
          Datos principales
        </button>
      </a>
    </li>

    <li class="nav-item me-1">
      <a href="{{ route('formularioUsuario.seccionesCampos', $formulario) }}">
        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
          <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">2</span>
          Secciones y campos
        </button>
      </a>
    </li>

  </ul>
</div>


<h4 class="mb-1 fw-semibold text-primary">Secciones y campos</h4>
<p class="mb-8">Aquí podrás gestionar las secciones y campos del formulario.</p>

@include('layouts.status-msn')

  @livewire('FormulariosParaUsuarios.gestionar-secciones-y-campos', [
    'formulario' => $formulario
  ])

@endsection
