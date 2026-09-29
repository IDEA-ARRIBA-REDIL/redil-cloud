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
  <div class="gamificacion-admin-header">
    <h4 class="mb-1 fw-semibold text-primary">Configuración gamificación</h4>
    <p class="mb-0 text-black">Gestiona las insignias y las reglas de asignación de puntos.</p>
  </div>

  @livewire('gamificacion.configuracion-gamificacion')
</div>
@endsection
