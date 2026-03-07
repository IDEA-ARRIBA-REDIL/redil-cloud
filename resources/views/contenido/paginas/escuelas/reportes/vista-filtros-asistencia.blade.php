{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)

@extends('layouts.layoutMaster')

{{-- Título de la página --}}
@section('title', 'Asistencia semanal')

{{-- Estilos específicos --}}
@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

{{-- Scripts específicos --}}
@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection





 @include('layouts.status-msn')


@section('content')

 <h4 class="mb-1 fw-semibold text-primary">Generar reporte de asistencia</h4>
    <p class="mb-4 text-black">Selecciona los filtros para generar el informe.</p>
    <div class="card">
        <div class="card-header">
            
        </div>
        <div class="card-body">
            {{-- Aquí renderizamos el componente de Livewire --}}
            @livewire('reportes.filtros-asistencia-periodo')
        </div>
    </div>
@endsection