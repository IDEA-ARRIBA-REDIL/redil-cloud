@extends('layouts.layoutMaster')

@section('isEscuelasModule', true)

@section('title', 'Consolidado académico')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1 fw-semibold text-primary">Consolidado académico</h4>
            <p class="text-black mb-0">Visualice el avance, notas y materias/niveles de los estudiantes por escuela.</p>
        </div>
    </div>

    {{-- Renderizado del componente Livewire de consolidado académico --}}
    @livewire('escuelas.consolidado-academico')

@endsection
