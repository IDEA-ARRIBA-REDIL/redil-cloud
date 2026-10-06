@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gestionar ' . ($configuracionRv->nombre_general ?? 'Rueda de la Vida'))

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
      <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
      </a>
      <h4 class="mb-0 fw-semibold text-primary">{{ $configuracionRv->nombre_general ?? 'Rueda de la Vida' }}</h4>
      </div>
      <p class="text-black mb-0 small">
      Administra las áreas de autoevaluación, los hábitos fijos y abiertos, y los parámetros globales del módulo.
      </p>
    </div>
</div>

@include('layouts.status-msn')

@livewire('rueda-de-la-vida.gestionar-rueda-de-la-vida')

@endsection
