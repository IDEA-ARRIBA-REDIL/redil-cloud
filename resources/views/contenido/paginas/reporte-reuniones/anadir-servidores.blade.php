@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/js/app.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection
@section('page-script')
<script type="module">
</script>

<style>
</style>

@endsection

@section('content')

<ul class="nav nav-pills mb-3 d-flex justify-content-end" role="tablist">
  <li class="nav-item">
    <a href="{{ route('reporteReunion.editar', $reporteReunion) }}">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
        <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">1</span>
        Información principal
      </button>
    </a>
  </li>

  <li class="nav-item">
    <a href="{{ route('reporteReunion.añadirServidores', $reporteReunion) }}">
      <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
        <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">2</span>
        Añadir servidores
      </button>
    </a>
  </li>

  <li class="nav-item">
    <a href="{{ route('reporteReunion.añadirAsistentes', $reporteReunion) }}">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
        <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">3</span>
        Añadir asistentes
      </button>
    </a>
  </li>

  <li class="nav-item">
    <a href="{{ route('reporteReunion.añadirIngresos', $reporteReunion) }}">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
        <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">4</span>
        Añadir ingresos
      </button>
    </a>
  </li>
</ul>

<h4 class="mb-1">Actualizar Reporte Reunión</h4>
<p class="mb-4">Aqui se podrán actualizar los reportes de reuniones.</p>

@include('layouts.status-msn')

@livewire('reporte-reuniones.resumen-financiero', [
'reporteReunion' => $reporteReunion,
])


@endsection