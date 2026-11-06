@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Campos usuario')

<!-- Page -->
@section('vendor-style')

@vite([
])

@endsection

@section('vendor-script')
  @vite([
  ])
@endsection


@section('page-script')

@endsection

@section('content')
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Lista de reproducción</h4>
      </div>
      <p class="text-black mb-0 small">
        Aquí podrás gestionar la lista de reproducción, canciones y álbumes.
      </p>
    </div>
  </div>

   @livewire('TiempoConDios.gestionar-lista-reproduccion')

@endsection
