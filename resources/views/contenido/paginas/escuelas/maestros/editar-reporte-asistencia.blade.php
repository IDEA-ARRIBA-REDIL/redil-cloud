{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}

{{-- resources/views/maestros/horarios_asignados.blade.php (o el nombre que corresponda a esta vista) --}}
@extends('layouts/blankLayout')

@section('title', 'Horarios Asignados a ' . ($maestro->user->name ?? 'Maestro'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    {{-- Esta sección permanece vacía según tu vista original --}}
@endsection





@section('content')
    @include('layouts.status-msn')
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
        <div class="col-3 text-start">
            <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
                <span class="ti-xs ti ti-arrow-left me-2"></span>
                <span class="d-none d-md-block fw-normal">Volver</span>
            </button>
        </div>
        <div class="col-6 pl-5 text-center">
            <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Editar Reporte
            </h5>
        </div>
        <div class="col-3 text-end">
            <a href="{{ route('maestros.reporteAsistencia', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"type="button"
                class="btn rounded-pill waves-effect waves-light text-white">
                <span class="d-none d-md-block fw-normal">Salir</span>
                <span class="ti-xs ti ti-x mx-2"></span>
            </a>
        </div>
    </nav>

    <div class="row mb-4">

    </div>
    <div class="pt-5 px-7 px-sm-0" style="padding-bottom: 100px;">

        <div class="col-12 col-sm-8 offset-sm-2 col-lg-8  offset-lg-2">
            <div class="col-12 mb-5">
                <h4 class="mb-1 fw-semibold text-primary">
                    Editando Asistencia: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
                </h4>
                <p class="mb-0 text-black"><small>{{ $infoClase }}</small></p>
                <p class="mb-0 text-black"><small>Fecha del reporte: <span
                            class="fw-bold">{{ \Carbon\Carbon::parse($reporte->fecha_clase_reportada)->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span></small>
                </p>
            </div>
            {{-- Encabezado con información de la clase --}}
            {{-- Aquí llamamos al nuevo componente Livewire de edición --}}
            {{-- Le pasamos las variables necesarias que recibimos del controlador --}}
            @livewire('maestros.editar-reporte-asistencia', [
                'maestro' => $maestro,
                'horarioAsignado' => $horarioAsignado,
                'reporte' => $reporte,
            ])
        </div>
    </div>

@endsection
