@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Listado Alumnos')

@section('vendor-style')

@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss', // Si decides usar select2 para filtros
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',

'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js', // Si decides usar select2
'resources/assets/vendor/libs/flatpickr/flatpickr.js',

'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
])

@endsection

@section('page-script')

@endsection


@section('content')
    @include('layouts.status-msn')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-semibold text-primary">Listado de Alumnos</h4>
            <p class="text-black mb-0">Periodo: <span class="fw-bold">{{ $periodo->nombre }}</span></p>
        </div>
      
    </div>

    {{-- 
      Aquí es donde ocurre la magia.
      1. Llamamos al componente Livewire por su nombre.
      2. Le pasamos la variable $periodo (que viene del controlador) como una "propiedad" o "parámetro".
         La sintaxis :periodo="$periodo" es la forma de pasar un objeto PHP.
    --}}
    <livewire:periodo.listado-alumnos-periodo :periodo="$periodo" />
  <a href="{{ route('periodo.gestionar') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Volver a Periodos
        </a>
@endsection