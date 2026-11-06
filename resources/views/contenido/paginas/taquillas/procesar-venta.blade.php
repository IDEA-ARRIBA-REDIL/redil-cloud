@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Procesar Venta')

@section('content')


@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/js/app.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])

@endsection

@if (! $cajaActiva->puedeOperar())
    <div class="alert alert-danger d-flex align-items-center p-3 mb-4" role="alert">
        <i class="ti ti-clock-off fs-2 me-3 text-danger flex-shrink-0"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1 text-danger">Operación no permitida en esta caja</h6>
            <p class="mb-0 text-black">
                {{ $cajaActiva->obtenerMensajeFueraDeHorario() }}
            </p>
        </div>
    </div>
    <div class="text-start">
        <a href="{{ route('taquilla.operar', $cajaActiva) }}" class="btn btn-primary rounded-pill">
            <i class="ti ti-arrow-left me-1"></i> Volver a la taquilla
        </a>
    </div>
@elseif ($esEscuela)
    {{-- 
    =========================================================
    CASO 1: ES UNA ESCUELA
    (MODIFICADO para pasar 'comprador' e 'inscrito')
    =========================================================
  --}}
    @livewire('taquilla.procesar-matricula-escuela', [
        'comprador' => $comprador, // ¡NUEVO!
        'usuario' => $inscrito, // ¡RENOMBRADO!
        'actividad' => $actividad,
        'cajaActiva' => $cajaActiva,
        'categoria' => $categoria,
        'moneda' => $moneda,
    ])
@elseif($esAbono)
    {{-- 
    =========================================================
    CASO 2: ES UN ABONO
    (MODIFICADO para pasar 'comprador' e 'inscrito')
    =========================================================
  --}}
    @livewire('taquilla.procesar-abono', [
        'comprador' => $comprador, // ¡NUEVO!
        'usuario' => $inscrito, // ¡RENOMBRADO!
        'actividad' => $actividad,
        'cajaActiva' => $cajaActiva,
        'categoria' => $categoria,
        'moneda' => $moneda,
    ])
@else
    {{-- 
    =========================================================
    CASO 3: ES UNA COMPRA NORMAL
    (MODIFICADO para pasar 'comprador' e 'inscrito')
    =========================================================
  --}}
    @livewire('taquilla.procesar-compra-normal', [
        'comprador' => $comprador, // ¡NUEVO!
        'inscrito' => $inscrito, // ¡NUEVO!
        'actividad' => $actividad,
        'cajaActiva' => $cajaActiva,
        'categoria' => $categoria,
        'moneda' => $moneda,
    ])
@endif

@endsection
