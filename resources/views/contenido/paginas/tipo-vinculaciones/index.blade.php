@extends('layouts/layoutMaster')

@section('title', 'Gestionar Tipos de Vinculación')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  @livewire('configuracion.gestionar-tipo-vinculaciones')
</div>
@endsection
