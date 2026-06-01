@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Niveles - Escuela: ' . $escuela->nombre)

@section('page-script')
    <script type="module">
        // ... (otros scripts como inicialización de Select2, confirmación de eliminar) ...

        // --- Script para Cargar Datos en Offcanvas de Edición Rápida ---
        const offcanvasEditar = document.getElementById('offcanvasEditarMateriaSimple');

        if (offcanvasEditar) {
            offcanvasEditar.addEventListener('show.bs.offcanvas', function(event) {
                // Botón que disparó el modal
                const button = event.relatedTarget;

                // Extraer datos de los atributos data-* del botón
                const materiaNombre = button.getAttribute('data-materia-nombre');
                const materiaDescripcion = button.getAttribute('data-materia-descripcion');
                const updateUrl = button.getAttribute('data-update-url');

                // Obtener los elementos del formulario dentro del offcanvas
                const form = offcanvasEditar.querySelector('#formEditarMateriaSimple');
                const nombreInput = offcanvasEditar.querySelector('#materia_nombre_editar_rapido');
                const descripcionTextarea = offcanvasEditar.querySelector('#materia_descripcion_editar_rapido');

                // Poblar el formulario con los datos extraídos
                if (form) form.action = updateUrl; // Establecer la URL correcta para el submit
                if (nombreInput) nombreInput.value = materiaNombre;
                if (descripcionTextarea) descripcionTextarea.value = materiaDescripcion;
            });

            // Opcional: Limpiar el formulario cuando el offcanvas se cierre
            offcanvasEditar.addEventListener('hidden.bs.offcanvas', function() {
                const form = offcanvasEditar.querySelector('#formEditarMateriaSimple');
                if (form) {
                    form.reset(); // Resetea los campos del formulario
                    form.action = ""; // Limpia la action
                }
                // Limpiar manualmente errores de validación si usas AJAX o una librería específica
            });
        }
    </script>
@endsection

@section('content')

    {{-- Título y Descripción Principal de la Gestión del Nivel --}}
    <div class="row mt-4 mb-4 align-items-center">
        <div class="col-md-8">
            <h4 class="text-primary mb-1">Gestionar Nivel: {{ $nivel->nombre }}</h4>
            <p class="mb-0">Administra la información, prerrequisitos y materias de este nivel académico.</p>
        </div>
        <div class="col-md-4 text-md-end">
            {{-- Botón para volver a la lista de niveles --}}
            <a href="{{ route('niveles.listar', $escuela) }}" class="btn btn-outline-secondary rounded-pill">
                <i class="ti ti-arrow-left me-1 ti-xs"></i>Volver a Niveles
            </a>
            {{-- Aquí podrías poner el botón para editar la INFO del nivel si esta vista es solo para materias --}}
            <a href="{{ route('niveles.editar', $nivel) }}" class="btn btn-primary rounded-pill ms-2">
                Editar Nivel
            </a>
        </div>
    </div>


    {{-- Notificaciones (éxito/error) --}}
    @include('layouts.status-msn') {{-- Incluir tu partial de mensajes --}}


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

    {{-- MODIFICACIÓN: Botón para abrir el Offcanvas de creación rápida --}}
    <button class="btn mb-5 btn-primary rounded-pill" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasCrearMateriaSimple" aria-controls="offcanvasCrearMateriaSimple">
        <i class="ti ti-plus me-1"></i> Crear materia
    </button>


    <div class="row equal-height-row">
        @if ($materias->count() > 0)
            @foreach ($materias as $materia)
                <div class="col equal-height-col  col-12 col-xl-3 col-md-6 mb-4">
                    <div class="h-100 card">


                        <div class="card-header">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="d-flex align-items-center">

                                    <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $materia->nombre }}</h5>
                                </div>

                                <div class="dropdown zindex-2 border rounded p-1">
                                    <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical text-black"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" data-bs-toggle="offcanvas"
                                                data-bs-target="#offcanvasEditarMateriaSimple" {{-- Datos de la materia específica --}}
                                                data-materia-id="{{ $materia->id }}"
                                                data-materia-nombre="{{ $materia->nombre }}"
                                                data-materia-descripcion="{{ $materia->descripcion ?? '' }}"
                                                data-update-url="{{ route('materias.actualizarRapido', $materia) }}"
                                                {{-- URL para actualizar esta materia específica --}}
                                                href="{{ route('materias.actualizarRapido', $materia) }}"> Actualizar
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('niveles.horariosMateria', $materia) }}"
                                                class="dropdown-item">
                                                Gestionar nivel
                                            </a>
                                        </li>
                                        <li>
                                            <form action="" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item confirmacionEliminar"
                                                    data-nombre="">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de caracteristicas-->
                        <div class="card-body">
                            <div class="d-flex flex-column mb-3">
                                <div class="d-flex flex-row">
                                    <i class="ti ti-circle-dashed-percentage text-black"></i>
                                    <div class="d-flex flex-column">
                                        <small class="text-black ms-1">Cantidad sedes </small>
                                        <small class="fw-semibold ms-1 text-black "></small>
                                    </div>
                                </div>
                            </div>


                            <div class="d-flex flex-row justify-content-between mb-2">
                                <div class="d-flex flex-row">
                                    <i class="ti ti-user-cancel text-black"></i>
                                    <div class="d-flex flex-column text-star">
                                        <small class="text-black ms-1">Cantidad horarios </small>
                                        <small class="fw-semibold ms-1 text-black ">

                                        </small>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-secondary text-center" role="alert">
                <i class="ti ti-info-circle ti-lg me-2"></i> Aún no hay materias creadas para este nivel.
            </div>
        @endif
    </div>


    {{-- ================================================== --}}
    {{--      INICIO: Offcanvas para Crear Materia Rápida   --}}
    {{-- ================================================== --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCrearMateriaSimple"
        aria-labelledby="offcanvasLabelCrearMateriaSimple">

        {{-- Formulario dentro del Offcanvas --}}
        {{-- La acción apunta a una NUEVA ruta que crearemos --}}
        <form id="formCrearMateriaSimple" action="{{ route('niveles.crearMateria', $nivel) }}" method="POST">
            @csrf

            {{-- Cabecera del Offcanvas --}}
            <div class="offcanvas-header">
                <h4 id="offcanvasLabelCrearMateriaSimple" class="offcanvas-title text-primary">Crear materia </h4>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            {{-- Cuerpo del Offcanvas con los campos del formulario --}}
            <div class="offcanvas-body mx-0 flex-grow-0">

                {{-- Campo Nombre Materia --}}
                <div class="mb-3">
                    <label for="materia_nombre_rapido" class="form-label">Nombre de la materia <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="materia_nombre_rapido" name="nombre"
                        placeholder="Ej: Matemáticas I" required>
                    {{-- Mostrar error específico si lo hubiera --}}
                    @error('nombre', 'materiaRapida')
                        {{-- Usar un 'bag' de errores diferente si es necesario --}}
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo Descripción Materia --}}
                <div class="mb-3">
                    <label for="materia_descripcion_rapida" class="form-label">Descripción (Opcional)</label>
                    <textarea class="form-control" id="materia_descripcion_rapida" name="descripcion" rows="4"
                        placeholder="Objetivo general de la materia..."></textarea>
                    @error('descripcion', 'materiaRapida')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campos ocultos para pasar IDs necesarios --}}
                {{-- Estos valores se toman del nivel actual que se está gestionando --}}
                <input type="hidden" name="nivel_id" value="{{ $nivel->id }}">
                <input type="hidden" name="escuela_id" value="{{ $escuela->id }}"> {{-- O $nivel->escuela_id --}}

            </div>

            {{-- Pie del Offcanvas con botones --}}
            <div class="offcanvas-footer p-5 border-top border-2 px-8">
                <button type="submit" class="btn btn-primary rounded-pill me-2">Guardar materia</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill"
                    data-bs-dismiss="offcanvas">Cancelar</button>
            </div>

        </form> {{-- Fin del formulario del Offcanvas --}}
    </div>
    {{-- ================================================== --}}
    {{--         FIN: Offcanvas para Crear Materia Rápida   --}}
    {{-- ================================================== --}}

    {{-- ===================================================== --}}
    {{--   INICIO: Offcanvas para Editar Materia Rápida      --}}
    {{-- ===================================================== --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditarMateriaSimple"
        aria-labelledby="offcanvasLabelEditarMateriaSimple">

        {{-- Formulario dentro del Offcanvas --}}
        {{-- La 'action' se establecerá dinámicamente con JavaScript --}}
        <form id="formEditarMateriaSimple" action="" method="POST">
            @csrf
            @method('PUT') {{-- Usar PUT o PATCH para actualizar --}}

            {{-- Cabecera --}}
            <div class="offcanvas-header">
                <h4 id="offcanvasLabelEditarMateriaSimple" class="text-primary offcanvas-title">Editar materia rápida</h4>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>

            {{-- Cuerpo con los campos --}}
            <div class="offcanvas-body mx-0 flex-grow-0">

                {{-- Campo Nombre Materia --}}
                <div class="mb-3">
                    <label for="materia_nombre_editar_rapido" class="form-label">Nombre de la materia <span
                            class="text-danger">*</span></label>
                    {{-- El 'value' se establecerá dinámicamente con JavaScript --}}
                    <input type="text" class="form-control" id="materia_nombre_editar_rapido" name="nombre"
                        required>
                    @error('nombre', 'materiaRapidaUpdate')
                        {{-- Usar otro error bag si es necesario --}}
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo Descripción Materia --}}
                <div class="mb-3">
                    <label for="materia_descripcion_editar_rapido" class="form-label">Descripción (Opcional)</label>
                    {{-- El contenido se establecerá dinámicamente con JavaScript --}}
                    <textarea class="form-control" id="materia_descripcion_editar_rapido" name="descripcion" rows="4"></textarea>
                    @error('descripcion', 'materiaRapidaUpdate')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- No necesitamos campos ocultos para ID aquí, irá en la URL de la action --}}

            </div>

            {{-- Pie con botones --}}
            <div class="offcanvas-footer border-top p-3">
                <button type="submit" class="btn btn-primary rounded-pill me-2">Actualizar materia</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill"
                    data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </form>
    </div>
    {{-- ===================================================== --}}
    {{--      FIN: Offcanvas para Editar Materia Rápida        --}}
    {{-- ===================================================== --}}



@endsection
