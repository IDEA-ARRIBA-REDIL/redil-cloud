@extends('layouts.layoutMaster')

@section('isEscuelasModule', true)

@section('title', 'Homologaciones Masivas')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1 fw-semibold text-primary">Homologaciones masivas</h4>
            <p class="text-black mb-0">Cargue y procese materias o niveles homologados para múltiples estudiantes a través de un archivo Excel.</p>
        </div>
    </div>

    {{-- Renderizado del componente Livewire de Homologaciones Masivas --}}
    @livewire('homologaciones.homologaciones-masivas')

@endsection
