@extends('layouts/layoutMaster')

@section('title', 'Configuración de Gamificación')

@section('vendor-style')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

@section('vendor-script')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

@section('page-style')
<style>
  .gamificacion-admin-header {
    margin-bottom: 1.5rem; 
  }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Configuración gamificación</h4>
      </div>
      <p class="text-black mb-0 small">
        Gestiona las insignias, niveles y reglas de asignación de puntos.
      </p>
    </div>
  </div>

  @livewire('gamificacion.configuracion-gamificacion')
</div>
@endsection
