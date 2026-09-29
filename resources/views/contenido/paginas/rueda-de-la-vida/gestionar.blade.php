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
    <h4 class="mb-1 fw-semibold text-primary">
      <i class="ti ti-circle-dashed-check me-2"></i>{{ $configuracionRv->nombre_general ?? 'Rueda de la Vida' }}
    </h4>
    <p class="text-muted mb-0">
      Administra las áreas de autoevaluación, los hábitos fijos y abiertos, y los parámetros globales del módulo.
    </p>
  </div>
  <div>
    <a href="{{ route('ruedaDeLaVida.historial') }}" class="btn btn-outline-secondary rounded-pill px-4">
      <i class="ti ti-arrow-left me-1"></i> Ir al Historial
    </a>
  </div>
</div>

@include('layouts.status-msn')

@livewire('rueda-de-la-vida.gestionar-rueda-de-la-vida')

@endsection
