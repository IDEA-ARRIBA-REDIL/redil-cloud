{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)
{{-- resources/views/maestros/horarios_asignados.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'Gestionar Clase')
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')



@endsection

@section('content')
    @include('layouts.status-msn')

    <div class="row mb-5 mt-5">
        <div class="me-auto ">
            <h4 class="mb-1  fw-semibold text-primary">
                Materia:
            </h4>
            <p class="mb-0">Aquí puedes revisar todo el contenido de tu clase (alumnos, asistencias, calificaciones etc.)
            </p>

        </div>

        <div class="row mb-5 mt-5">
            <div class="col-md-12">
                <div class="card mb-0 p-1 border-1">
                    <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">

                        <li class="nav-item flex-fill"><a id="tap-principal"
                                href=" {{ route('maestros.gestionarClase', ['horarioAsignado' => $horarioAsignado, 'maestro' => $maestro]) }}"
                                class="nav-link p-3 waves-effect
                                    waves-light active"
                                data-tap="principal"><i class="ti-xs ti me-2 ti-info-hexagon "></i>
                                Listado de alumnos</a>
                        </li>

                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_detallada'))
                        <li class="nav-item flex-fill"><a id="tap-horarios"
                                href=" {{ route('maestros.calificacionMultiple', ['horarioAsignado' => $horarioAsignado, 'maestro' => $maestro]) }}"
                                class="nav-link p-3 waves-effect waves-light" data-tap="horarios"><i
                                    class="ti-xs ti me-2 ti-clock"></i> Calificación Multiple</a>
                        </li>
                        @endif

                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_reportes_asistencia'))
                        <li class="nav-item flex-fill"><a id="tap-modelo"
                                href=" {{ route('maestros.reporteAsistencia', ['horarioAsignado' => $horarioAsignado, 'maestro' => $maestro]) }}"
                                class="nav-link p-3 waves-effect waves-light" data-tap="modelo"><i
                                    class="ti-xs ti me-2 ti-template"></i> Reportes de asistencia</a>

                        </li>
                        @endif
                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_recursos_alumnos'))
                        <li class="nav-item flex-fill">
                         <a href="{{ route('maestros.recursosAlumnos', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}" class="nav-link module-nav-link p-3 waves-effect waves-light ">
                            <i class="mdi mdi-folder-multiple-outline me-1"></i> Recursos alumnos
                        </a>
                        </li>
                        @endif
                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_dashboard_general'))
                        <li class="nav-item flex-fill"><a id="tap-modelo"
                                href="{{ route('maestros.dashboardClase', ['horarioAsignado' => $horarioAsignado, 'maestro' => $maestro]) }}"
                                class="nav-link p-3 waves-effect waves-light" data-tap="modelo"><i
                                    class="ti-xs ti me-2 ti-template"></i> Informe general periodo</a>

                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="row equal-height-row ">
            @forelse ($users as $user)
                <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-4 equal-height-col">
                    <div class="card h-100 border rounded">
                        <img class="card-img-top object-fit-cover" style="height: 100px;"
                            src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img//usuarios/banner-usuario/' . $user->portada) : Storage::url($configuracion->ruta_almacenamiento . '/img//usuarios/banner-usuario/' . $user->portada) }}"
                            alt="portada {{ $user->primer_nombre }}" />

                        <div class="card-body">
                            <div class="user-profile-header d-flex flex-row text-start mb-2 ">
                                <div class="flex-grow-1 mt-n10 mx-auto text-start">
                                    @if ($user->foto == 'default-m.png' || $user->foto == 'default-f.png')
                                        <div class="avatar avatar-xl">
                                            <span
                                                class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                                {{ $user->inicialesNombre() }} </span>
                                        </div>
                                    @else
                                        <div class="avatar avatar-xl">
                                            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $user->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $user->foto }}"
                                                alt="{{ $user->foto }}"
                                                class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                        </div>
                                    @endif

                                    <div class="d-flex align-items-start justify-content-between">

                                        <div class="align-items-center">
                                            <h5 class="mb-0 fw-semibold">
                                                {{ $user->nombre(3) ?? 'Usuario no encontrado' }}
                                            </h5>
                                            <small class="text-muted">{{ $user->email ?? '' }}</small>
                                        </div>
                                        <div class="dropdown zindex-2 border rounded p-1">
                                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical text-black"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a target="_blank"
                                                        href="{{ route('maestros.gestionarAlumno', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado, 'alumno' => $user]) }}"
                                                        class="dropdown-item">
                                                        Gestionar alumno
                                                    </a>
                                                </li>


                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>



                        </div>
                        <div class="card-footer">

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i> No se encontraron maestros registrados. ¡Crea el primero!
                    </div>
                </div>
            @endforelse
        </div>

    </div>




@endsection
