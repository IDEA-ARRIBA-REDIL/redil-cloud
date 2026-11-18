@extends('layouts/layoutMaster')

@section('title', 'Acciones del Dashboard')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    ])
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header de Navegación y Acciones -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('configuracion.index') }}"
                        class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm"
                        title="Volver a Configuración">
                        <i class="ti ti-arrow-left"></i>
                    </a>
                    <h4 class="mb-0 fw-semibold text-primary">Acciones del dashboard</h4>
                </div>
                <p class="text-black mb-0 small">
                    Configura los enlaces rápidos de la sección "¿Qué deseas hacer hoy?", sus íconos o imágenes PNG y los usuarios que pueden visualizarlos.
                </p>
            </div>
        </div>

        @livewire('dashboard.gestionar-acciones-dashboard')
    </div>
@endsection
