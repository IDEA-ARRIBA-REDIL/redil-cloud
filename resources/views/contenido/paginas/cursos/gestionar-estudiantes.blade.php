@extends('layouts/layoutMaster')

@section('title', 'Inscritos - ' . $curso->nombre)

@section('content')

    <h4 class="fw-semibold text-primary py-3 mb-2">
       Gestión de inscripciones
    </h4>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-4 rounded-4 border-0 ">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-xl me-4">
                        <img src="{{ $curso->imagen_portada ? Storage::url($configuracion->ruta_almacenamiento.'/img/cursos/portadas/'.$curso->imagen_portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png') }}" alt="{{ $curso->nombre }}" class="rounded-3 object-fit-cover w-100 h-100 border shadow-sm">
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-primary">{{ $curso->nombre }}</h4>
                        <p class="mb-0 text-muted d-flex align-items-center">
                            <i class="ti ti-books me-1"></i> {{ $curso->carrera ? $curso->carrera->nombre : 'Curso General' }} &bull; 
                            <i class="ti ti-users mx-1"></i> Listado Oficial de inscripciones
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido de estudiantes -->

    <div class="row mt-4">
        <div class="col-12">
            <!-- Cargamos el componente Livewire aquí -->
            @livewire('cursos.listado-estudiantes-curso', ['curso' => $curso])
        </div>
    </div>

@endsection
