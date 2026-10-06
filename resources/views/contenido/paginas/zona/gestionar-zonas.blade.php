@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Zonas')


@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    @vite([
      'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
      'resources/assets/vendor/libs/select2/select2.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
      'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
      'resources/assets/vendor/libs/select2/select2.js'
    ])
@endsection

@section('page-script')

@endsection

@section('content')
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
      <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
      </a>
      <h4 class="mb-0 fw-semibold text-primary">Gestionar zonas</h4>
      </div>
      <p class="text-black mb-0 small">
      Administra las diferentes zonas.
      </p>
    </div>

    @livewire('Zonas.gestionar-zonas')

@endsection
