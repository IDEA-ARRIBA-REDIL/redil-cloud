@extends('layouts.layoutMaster')

@section('isEscuelasModule', true)

@section('title', 'Informe estado materia / niveles')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/flatpickr/flatpickr.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    ])
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1 fw-semibold text-primary">Informe estado materia / niveles</h4>
            <p class="text-black mb-0">Consulte los estudiantes registrados, notas, estado y fechas por materia o nivel específico.</p>
        </div>
    </div>

    {{-- Renderizado del componente Livewire de informe de estado materia / niveles --}}
    @livewire('escuelas.informe-estado-materia-niveles')

@endsection
