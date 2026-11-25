@extends('layouts.layoutMaster')
@section('isEscuelasModule', true)
@section('title', 'Recursos para Alumnos')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
    @include('layouts.status-msn')

    {{-- Encabezado principal arriba de la barra de pestañas --}}
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-1 fw-semibold text-primary">
                Gestión de recursos: <span class="text-black fw-normal">{{ $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'Materia no disponible' }}</span>
            </h4>
            <p class="mb-0 text-black"><small>{{ $horarioAsignado->resumenEncabezadoClase() }}</small></p>
        </div>
    </div>

    @include('contenido.paginas.escuelas.maestros.nav-modulo')

    @livewire('Maestros.gestion-recursos', [
        'horarioAsignado' => $horarioAsignado,
        'maestro' => $maestro,
    ])
@endsection
