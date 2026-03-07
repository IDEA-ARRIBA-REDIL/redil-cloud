{{-- Archivo: resources/views/contenido/paginas/puntos-de-pago/gestionar.blade.php --}}
@extends('layouts/layoutMaster')

@section('title', 'Gestionar Puntos de Pago')

@section('page-style')
  {{-- Estilos que tenías en listar.blade.php --}}
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
  ])
@endsection

@section('vendor-script')
  {{-- Scripts que tenías en listar.blade.php --}}
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/moment/moment.js',
  ])
@endsection

@section('content')
<h4 class=" mb-1 fw-semibold text-primary"> Puntos de pago</h4>

@include('layouts.status-msn')

    {{-- Aquí cargamos nuestro nuevo componente Livewire --}}
    {{-- Pasamos el 'tipo' (todos, dados-de-baja) desde la URL al componente --}}
    @livewire('puntos-de-pago.gestionar-puntos-de-pago', ['tipo' => request('tipo', 'todos')])
@endsection