@extends('layouts/layoutMaster')

@section('title', 'Mi Panel de Gamificación')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/flatpickr/flatpickr.js'
  ])
@endsection

@section('page-style')
<style>
  .gamificacion-user-header {
    margin-bottom: 1.5rem;
  }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="gamificacion-user-header">
    <h4 class="mb-1 fw-semibold text-primary">Mi panel de gamificación</h4>
    <p class="mb-0 text-black">Sumá puntos completando acciones y canjealos por premios.</p>
  </div>

  @livewire('gamificacion.panel-gamificacion')
</div>
@endsection
