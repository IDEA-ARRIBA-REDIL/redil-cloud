@extends('layouts.layoutMaster')

{{-- Puedes crear una sección específica para el módulo de escuelas si lo necesitas --}}
@section('isEscuelasModule', true)

@section('title', 'Gestionar Banners ')

{{-- Si tu componente Livewire usa SweetAlert, manten estas secciones --}}
@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/cropperjs/cropper.css',
    ])
@endsection

@section('vendor-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/cropperjs/cropper.js',
    ])
@endsection

    @include('layouts.status-msn')

@section('content')
    <h4 class="mb-1 fw-semibold text-primary">Gestión de banners</h4>
    <p class="text-black">Crea, actualiza y administra los banners informativos que se mostrarán a los alumnos.</p>

    {{-- Aquí se renderizará nuestro nuevo componente de Livewire --}}
   
     @livewire('BannerEscuela.GestionarBanners')

@endsection