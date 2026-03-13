@section('isEscuelasModule', true)

@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Escuelas')

<!-- Page -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection


@section('content')



    <h4 class="mb-1 fw-semibold text-primary">Gestionar escuelas</h4>
    <p class="mb-4 text-black">aqui podras crear y gestionar tus escuelas </p>

    <!-- botonera -->
    @if ($rolActivo->hasPermissionTo('escuelas.subitem_nueva_escuela'))
        <div class="row mb-5 mt-5">
            <div class="me-auto ">
                <button type="button" class="btn rounded-pill float-star btn-primary rounded-pill waves-effect waves-light"
                    data-bs-toggle="offcanvas" data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
                    <i class="ti ti-plus"></i> Nueva escuela
                </button>
            </div>
        </div>
        <!-- /botonera -->
    @endif


    @include('layouts.status-msn')
    <div class="row equal-height-row ">
        @foreach ($escuelas as $escuela)
            <div class="col equal-height-col  col-12 col-xl-4 col-md-6 mb-3">

                <div class="h-100 card ">
                    <img id="preview-foto" style="height: 100px;" class="card-img-top object-fit-cover"
                        src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/escuelas/' . $escuela->portada) }}"
                        alt="Portada {{ $escuela->nombre }}">


                    <div class="card-header">
                        <div class="d-flex align-items-start">
                            <div class="d-flex align-items-start">
                                <div class="me-2 ms-1 mt-1 px-1">
                                    <h5 class="mb-0 fw-semibold text-black lh-sm">
                                        {{ $escuela->nombre }}
                                    </h5>
                                    <div class="client-info"><span class="fw-medium"></span></div>
                                </div>
                            </div>

                            <div class="ms-auto">
                                <div class="dropdown zindex-2 ">
                                    <button style="border-radius: 20px;" type="button"
                                        class="btn border dropdown-toggle hide-arrow p-1" data-bs-toggle="dropdown"
                                        aria-expanded="false"><i class="ti ti-dots-vertical text-black"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if ($rolActivo->hasPermissionTo('escuelas.opcion_actualizar_escuela'))
                                            <li><a href="{{ route('escuelas.actualizar', $escuela->id) }}"
                                                    class="dropdown-item">
                                                    Actualizar
                                                </a></li>
                                        @endif

                                        @if ($rolActivo->hasPermissionTo('escuelas.opcion_gestionar_pensum'))
                                            @if ($escuela->tipo_matricula == 'materias_independientes')
                                                <li><a href="{{ route('escuelas.materias', $escuela->id) }}"
                                                        class="dropdown-item">
                                                        Gestionar materias
                                                    </a></li>
                                            @else
                                                <li><a href="" class="dropdown-item">
                                                        Gestionar grados
                                                    </a></li>
                                            @endif
                                        @endif

                                        <li>
                                            <a href="{{ route('escuelas.exportarMatriculasActivas', $escuela->id) }}"
                                                class="dropdown-item">
                                                Exportar matrículas activas
                                            </a>
                                        </li>


                                        @if ($rolActivo->hasPermissionTo('escuelas.opcion_eliminar_escuela'))
                                            <li><a class="dropdown-item confirmacionEliminar" data-nombre=""
                                                    data-id="">Eliminar
                                                </a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="col-12 col-md-8">
                            <div class="d-flex flex-column mb-3">
                                <div class="d-flex flex-row">
                                    <i class="ti ti-notebook text-black"></i>
                                    <div class="d-flex flex-column">
                                        <small class="text-black ms-1">Tipo: </small>
                                        @if ($escuela->tipo_matricula == 'materias_independientes')
                                            <small class="fw-semibold ms-1 text-black ">Gestión por materias</small>
                                        @else
                                            <small class="fw-semibold ms-1 text-black ">Gestión por grados</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
        @endforeach
    </div>


    <!-- Off canvas nueva escuelas-->
    <form id="formularioOffset" class="forms-sample" method="POST" action="{{ route('escuelas.guardar') }}">
        @csrf
        <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar"
            aria-labelledby="addEventSidebarLabel">

            <div class="offcanvas-header my-1">
                <h4 class="offcanvas-title fw-bold text-primary" id="addEventSidebarLabel">Nueva Escuela</h4>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-0">
                {{-- Campo Nombre Escuela --}}
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre Escuela</label>
                    <input required type="text" class="form-control" id="nombre" name="nombre" required
                        placeholder="Ej: Escuela de Ciencias">
                </div>

                {{-- Campo Tipo Matrícula --}}
                <div class="mb-3">
                    <label for="tipo_matricula" class="form-label">Tipo de matrícula</label>
                    <select required class="form-select" id="tipo_matricula" name="tipo_matricula" required>
                        <option value="materias_independientes">Materias independientes</option>
                        <option value="niveles_agrupados">Niveles agrupados</option>
                    </select>
                </div>

                {{-- Campo Cantidad de Cortes --}}
                <div class="mb-3">
                    <label for="cortes" class="form-label">Cantidad de Cortes por Periodo</label>
                    <input required type="number" min="1" value="3" class="form-control" id="cortes"
                        name="cortes" required>
                    <small class="form-text text-muted">Número de evaluaciones principales (cortes, trimestres, etc.) que
                        tendrá cada periodo académico.</small>
                </div>

                {{-- Campo Nombre Base para Cortes --}}
                <div class="mb-3">
                    {{-- Corregido type="number" a type="text" --}}
                    <label for="nombreCortes" class="form-label">Nombre Base para Cortes</label>
                    <input required type="text" class="form-control" id="nombreCortes" name="nombreCortes" required
                        value="Corte" placeholder="Ej: Corte, Trimestre, Bloque">
                    <small class="form-text text-muted">Este nombre se usará para generar los nombres individuales (Ej:
                        Corte 1, Corte 2...).</small>
                </div>

                {{-- Campo Descripción --}}
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                        placeholder="Breve descripción de la escuela"></textarea>
                </div>

                {{-- Campo Habilitar Consolidación --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="habilitada_consilidacion"
                            name="habilitada_consilidacion" value="1">
                        <label class="form-check-label fw-semibold text-black" for="habilitada_consilidacion">Habilitar
                            Consolidación</label>
                    </div>
                    <small class="form-text text-muted">Indica si esta escuela tendrá habilitada la opción de consolidación
                        para informes.</small>
                </div>
            </div>
            <div class="offcanvas-footer p-5 border-top border-2 px-8">
                <button type="submit"
                    class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
                <button type="button" data-bs-dismiss="offcanvas"
                    class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
            </div>

        </div>
    </form>


@endsection
