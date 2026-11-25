@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Cobertura geográfica - ' . $sede->nombre)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h4 class="mb-1 fw-semibold text-primary">Cobertura geográfica</h4>
    <p class="mb-0 text-black">
      Configura en qué ubicaciones se mostrará la sede <span class="fw-semibold">{{ $sede->nombre }}</span> dentro de los formularios.
    </p>
  </div>
  <a href="{{ route('sede.lista') }}" class="btn btn-outline-secondary rounded-pill">
    <i class="ti ti-arrow-left me-1"></i> Volver a sedes
  </a>
</div>

@livewire('Sedes.configurar-cobertura-geografica', ['sede' => $sede])
@endsection
