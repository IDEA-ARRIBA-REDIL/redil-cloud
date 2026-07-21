@php
    use App\Models\Sede;
    use App\Models\User;
@endphp

@section('isEscuelasModule', true)

@extends('layouts.layoutMaster')

@section('title', 'Calificaciones Grilla')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
@endsection

@section('page-script')
    <script>
        document.addEventListener('livewire:navigated', () => {
             var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

        // Listener para notificación de éxito guardado
        document.addEventListener('notaGuardada', event => {
             Swal.fire({
                 position: 'top-end',
                 icon: 'success',
                 title: 'Nota guardada',
                 showConfirmButton: false,
                 timer: 1000,
                 toast: true,
                 timerProgressBar: true
             });
        });
    </script>
@endsection

@section('content')
    @include('layouts.status-msn')

    {{-- Encabezado --}}
    <div class="row mb-3">
        <div class="col-12 mb-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-semibold text-primary">
                        Calificación Grilla: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
                    </h4>
                    <p class="mb-0 text-black"><small>{{ $infoClase }}</small></p>
                </div>
            </div>
        </div>


        @include('contenido.paginas.escuelas.maestros.nav-modulo')

        {{-- Contenido Principal: Grilla Livewire --}}
        <div class="row">
            <div class="col-12">
                @livewire('Maestros.CalificacionGrillaAlumnos', ['horarioAsignado' => $horarioAsignado])
            </div>
        </div>
    @endsection
