@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Niveles - Escuela: ' . $escuela->nombre)

@section('content')

    {{-- Botón para crear nuevo nivel --}}
    <div class="row mt-5">
        <div class="me-auto ">
            <h4 class="text-primary">Niveles asociados</h4>
            <p>aqui podras crear y gestionar tus niveles asociados </p>

        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('niveles.crear', $escuela) }}" class="btn btn-primary rounded-pill">
            <i class="ti ti-plus me-1"></i> Crear Nuevo Nivel
        </a>
    </div>

    {{-- Notificaciones --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <p><strong>Ocurrieron errores:</strong></p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif




    <div class="row equal-height-row">
        @if ($niveles->count() > 0)
            @foreach ($niveles as $nivel)
                <div class="col equal-height-col col-12 col-xl-3 col-md-6 mb-4">
                    <div class="h-100 card rounded rounded-3">
                        <img style="height: 150px; object-fit: cover;" class="card-img-top mb-2 rounded-top"
                             @if ($nivel->portada != '') src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/niveles/' . $nivel->portada) }}"
                        @else
                            src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/niveles/default.png') }}" @endif
                        alt="Portada ">

                        <div class="card-body p-4 pt-2">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $nivel->nombre }}</h5>
                                <div class="dropdown zindex-2">
                                    <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical text-black"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{{ route('niveles.editar', $nivel) }}" class="dropdown-item">
                                                Actualizar
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('niveles.materias', $nivel) }}" class="dropdown-item">
                                                Gestionar materias y horarios
                                            </a>
                                        </li>
                                        <li>
                                            <form action="" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item confirmacionEliminar"
                                                    data-nombre="{{ $nivel->nombre }}">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <p class="mb-3 text-muted small">{{ Str::limit($nivel->descripcion, 80) }}</p>

                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-black small">Calificaciones:</span>
                                    <span class="badge bg-label-secondary text-black">{{ $nivel->configuracion->habilitar_calificaciones ? 'Sí' : 'No' }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-black small">Asistencias:</span>
                                    <span class="badge bg-label-secondary text-black">{{ $nivel->configuracion->habilitar_asistencias ? 'Sí' : 'No' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <p>No se encontraron niveles para esta escuela.</p>
        @endif
    </div>

@endsection
