@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Campos usuario')

<!-- Page -->
@section('vendor-style')

@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
])

@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection


@section('page-script')

@endsection

@section('content')
  <h4 class="mb-1 fw-semibold text-primary">Listado de campos del formulario</h4>
  <p class="mb-8">Aquí podrás gestionar los campos para los formularios de usuario.</p>

  @livewire('CamposFormularioUsuario.gestionar-campos')

@endsection
