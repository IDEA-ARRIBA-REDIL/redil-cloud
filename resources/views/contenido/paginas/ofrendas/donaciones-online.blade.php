@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
    $colorPrimario = \App\Models\ThemeSetting::where('class', 'primary')->value('value') ?? '#2e7d32';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Donaciones y Ofrendas Online')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('page-style')
<style>
    .donaciones-online-wrapper {
        --donacion-primary: {{ $colorPrimario }};
    }

    /* Banner por defecto con el color primario del tenant (evita el morado por defecto de Vuexy) */
    .donacion-header-fallback {
        background: linear-gradient(135deg, {{ $colorPrimario }} 0%, {{ $colorPrimario }}cc 100%) !important;
        color: #ffffff !important;
    }

    /* Pestañas superiores */
    .donaciones-online-wrapper .donacion-tab-btn {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        border: 0 !important;
        border-bottom: 3px solid transparent !important;
        border-radius: 0 !important;
        transition: all 0.2s ease-in-out;
        text-transform: none !important;
    }

    .donaciones-online-wrapper .donacion-tab-btn.completed {
        background-color: #ffffff !important;
        color: #111827 !important;
        border-bottom: 3px solid {{ $colorPrimario }}66 !important;
    }

    .donaciones-online-wrapper .donacion-tab-btn.active {
        background-color: {{ $colorPrimario }}18 !important;
        color: #111827 !important;
        border-bottom: 3px solid {{ $colorPrimario }} !important;
        font-weight: 700 !important;
    }

    /* Círculo numérico en las pestañas (sin conflicto con .badge ni .bg-primary) */
    .donaciones-online-wrapper .donacion-step-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-size: 0.85rem;
        font-weight: 700;
        background-color: #e2e8f0 !important;
        color: #334155 !important;
        flex-shrink: 0;
    }

    .donaciones-online-wrapper .donacion-tab-btn.active .donacion-step-circle,
    .donaciones-online-wrapper .donacion-tab-btn.completed .donacion-step-circle {
        background-color: {{ $colorPrimario }} !important;
        color: #ffffff !important;
    }

    .donaciones-online-wrapper .donacion-tab-btn.active .donacion-step-circle i,
    .donaciones-online-wrapper .donacion-tab-btn.completed .donacion-step-circle i {
        color: #ffffff !important;
    }

    /* Tarjetas de selección de modalidad (Pestaña 1) */
    .donaciones-online-wrapper .donacion-card-option {
        background-color: #ffffff !important;
        border: 2px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

    .donaciones-online-wrapper .donacion-card-option:hover {
        border-color: {{ $colorPrimario }}99 !important;
        background-color: {{ $colorPrimario }}0c !important;
    }

    .donaciones-online-wrapper .donacion-card-option.active {
        background-color: {{ $colorPrimario }}1f !important;
        border: 2px solid {{ $colorPrimario }} !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06) !important;
    }

    .donaciones-online-wrapper .donacion-card-option .donacion-card-title {
        color: #111827 !important;
    }

    .donaciones-online-wrapper .donacion-card-option .donacion-card-desc {
        color: #4b5563 !important;
    }

    .donaciones-online-wrapper .donacion-card-option.active .donacion-card-desc {
        color: #1f2937 !important;
    }

    /* Ícono circular dentro de cada tarjeta */
    .donaciones-online-wrapper .donacion-card-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        flex-shrink: 0;
        transition: all 0.2s ease-in-out;
    }

    .donaciones-online-wrapper .donacion-card-option.active .donacion-card-icon {
        background-color: {{ $colorPrimario }} !important;
        color: #ffffff !important;
    }

    /* Etiqueta resumen de modalidad en Pestaña 3 */
    .donaciones-online-wrapper .donacion-pill-tag {
        display: inline-block;
        padding: 0.35rem 0.85rem;
        border-radius: 50rem;
        font-size: 0.8rem;
        font-weight: 600;
        background-color: {{ $colorPrimario }}22 !important;
        color: #111827 !important;
        border: 1px solid {{ $colorPrimario }}66 !important;
    }

    /* Ajustes visuales de Select2 para que coincida con los inputs del formulario */
    .donaciones-online-wrapper .select2-container .select2-selection--single {
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
        border-color: #dbdade !important;
    }

    .donaciones-online-wrapper .select2-container--disabled .select2-selection--single {
        background-color: #f1f0f2 !important;
        color: #6f6b7d !important;
        cursor: not-allowed !important;
    }

    .donaciones-online-wrapper .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827 !important;
        line-height: 36px !important;
        padding-left: 0.875rem !important;
    }

    .donaciones-online-wrapper .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
<script>
    window.initDonacionSelect2 = function (el, wire, propName, placeholder) {
        const setup = () => {
            if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
                setTimeout(setup, 50);
                return;
            }
            const $el = window.jQuery(el);
            if (!$el.length) {
                return;
            }
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                width: '100%',
                placeholder: placeholder || 'Selecciona una opción',
                language: {
                    noResults: function () {
                        return 'No se encontraron resultados';
                    },
                    searching: function () {
                        return 'Buscando...';
                    }
                }
            }).off('change.donacionSelect2').on('change.donacionSelect2', function () {
                wire.set(propName, window.jQuery(this).val());
            });
        };
        setup();
    };
</script>
@endsection

@section('content')
<div class="min-vh-100 bg-body donaciones-online-wrapper">
    {{-- Encabezado superior con logo de la iglesia --}}
    <nav class="navbar navbar-expand-lg bg-white border-bottom py-3 shadow-xs">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                @include('_partials.logo_iglesia', ['logo_negro' => true, 'width' => '150px'])
            </a>
            <div class="d-flex align-items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="ti ti-layout-dashboard me-1"></i> Ir a mi cuenta
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="ti ti-login me-1"></i> Iniciar sesión
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Banner 1600x600 configurado en Configuraciones Generales --}}
    @if(!empty($configuracion?->banner_donacion_ofrenda_url))
        <div class="w-100 overflow-hidden bg-dark" style="max-height: 450px;">
            <img
                src="{{ $configuracion->banner_donacion_ofrenda_url }}"
                alt="Banner Donaciones {{ $iglesia?->nombre ?? '' }}"
                class="w-100 d-block"
                style="aspect-ratio: 1600 / 600; max-height: 450px; object-fit: cover;"
            />
        </div>
    @else
        <div class="donacion-header-fallback py-5">
            <div class="container text-center">
                <h2 class="text-white fw-bold mb-1">Donaciones y Ofrendas Online</h2>
                <p class="mb-0 text-white opacity-75">{{ $iglesia?->nombre ?? config('app.name') }}</p>
            </div>
        </div>
    @endif

    {{-- Contenedor del componente Livewire en 3 pestañas --}}
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9 col-xl-8">
                @if(session('error'))
                    <div class="alert alert-danger mb-4">
                        <i class="ti ti-alert-circle me-1"></i> {{ session('error') }}
                    </div>
                @endif

                @livewire('ofrendas.donaciones-online')
            </div>
        </div>
    </div>
</div>
@endsection
