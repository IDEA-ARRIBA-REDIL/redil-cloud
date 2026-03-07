@section('isEscuelasModule', true)

@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Materias')

<!-- Page -->

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
  

@endsection


@section('content')

 @include('layouts.status-msn')
    <!-- botonera -->
    <div class="row mb-5 mt-5">
   
        <div class="me-auto ">
            <h4 class="mb-1 fw-semibold text-primary">Materias asociadas </h4>
            <p class="mb-4 text-black">aqui podras crear y gestionar tus materias asociadas </p>
            @if( $rolActivo->hasPermissionTo('escuelas.opcion_anadir_materia_escuela'))
            <a href="{{ route('materias.crear', $escuela) }}" class="btn rounded-pill btn-primary">
                <i class="ti ti-plus me-1"></i> Nueva materia
            </a>
            @endif
        </div>
    </div>
    <!-- /botonera -->

    <div id="formActualizarEscuela" class="row">

        <!-- Sección de Materias -->
           @if( $rolActivo->hasPermissionTo('escuelas.listar_opciones_materia'))
            <div class="row equal-height-row">
                @forelse($materias as $materia)
                    <div class="col equal-height-col  col-12 col-xl-4 col-md-6 mb-4">
                        <div class="h-100 card">
                            <img id="preview-foto" style="height: 100px;" class="card-img-top object-fit-cover"
                                src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/materias/' . $materia->portada) }}"
                                alt="Portada {{ $materia->nombre }}">
                            <div class="card-header">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex align-items-center">

                                        <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $materia->nombre }}</h5>
                                    </div>

                                    <div class="dropdown zindex-2 ">
                                        <button style="border-radius: 20px;" class="btn p-1 border " type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical text-black"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                         @if( $rolActivo->hasPermissionTo('escuelas.opcion_modificar_materia'))
                                            <li>
                                                <a href="{{ route('materias.gestionar', $materia->id) }}" class="dropdown-item"
                                                    href="#">
                                                    Gestionar materia
                                                </a>
                                            </li>
                                        @endif
                                        @if( $rolActivo->hasPermissionTo('escuelas.opcion_eliminar_materia'))
                                            <li>
                                               <form action="{{ route('materias.eliminar', $materia->id) }}" method="POST">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="dropdown-item"
                                                        data-nombre="{{ $materia->nombre }}">
                                                        Eliminar 
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección de caracteristicas-->
                            <div class="card-body">
                                <div class="row justify-content-between mb-2">

                                    <div class="col-12 col-md-6">
                                        <i class="ti ti-circle-dashed-percentage text-black"></i>
                                        <div class="d-flex flex-column text-star">
                                            <small class="text-black ms-1">Calificaciones: </small>
                                            <small
                                                class="fw-semibold ms-1 text-black ">{{ $materia->habilitar_calificaciones ? 'Habilitado' : 'Inhabilitado' }}</small>
                                        </div>
                                    </div>



                                    <div class="col-12 col-md-6">
                                        <i class="ti ti-user-check text-black"></i>
                                        <div class="d-flex flex-column text-star">
                                            <small class="text-black ms-1">Asistencias: </small>
                                            <small
                                                class="fw-semibold ms-1 text-black ">{{ $materia->habilitar_asistencias ? 'Habilitado' : 'Inhabilitado' }}</small>
                                        </div>
                                    </div>

                                </div>
                                <div class="row  justify-content-between mb-2">
                                    <div class="col-12 col-md-6">
                                        <i class="ti ti-user-cancel text-black"></i>
                                        <div class="d-flex flex-column text-star">
                                            <small class="text-black ms-1">Inasistencias: </small>
                                            <small class="fw-semibold ms-1 text-black ">
                                                {{ $materia->habilitar_inasistencias ? 'Habilitado' : 'Inhabilitado' }}
                                            </small>
                                        </div>
                                    </div>
                                    @if ($materia->habilitar_asistencias)
                                        <div class="col-12 col-md-6">
                                            <i class="ti ti-users-minus text-black"></i>
                                            <div class="d-flex flex-column text-star">
                                                <small class="text-black ms-1">Asistencias minimas: </small>
                                                <small class="fw-semibold ms-1 text-black ">
                                                    {{ $materia->asistencias_minimas }}
                                                </small>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="row justify-content-between mb-2">
                                    <div class="d-flex flex-column mb-3">
                                        <div class="col-12 col-md-6">
                                            <i class="ti ti-user-cancel text-black"></i>
                                            <div class="d-flex flex-column text-star">
                                                <small class="text-black ms-1">Alerta inasistencias: </small>
                                                <small
                                                    class="fw-semibold ms-1 text-black ">{{ $materia->habilitar_inasistencias ? 'Habilitado' : 'Inhabilitado' }}</small>
                                            </div>

                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            No hay materias registradas para esta escuela
                        </div>
                    </div>
                @endforelse
            </div>
           @endif

    </div>


@endsection
