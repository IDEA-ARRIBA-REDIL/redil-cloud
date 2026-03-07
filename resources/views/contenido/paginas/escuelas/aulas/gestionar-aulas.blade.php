@section('isEscuelasModule', true)
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Aulas')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection

@section('page-script')

    <script>
        // AJAX para editar aula (tu código existente)
        $(document).ready(function() {
            $(document).on('click', '.editar-aula', function(e) {
                e.preventDefault();
                var aulaId = $(this).data('id');
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "/aulas/" + aulaId + "/editar",
                    type: "POST", // Considera usar GET si solo estás obteniendo datos para editar
                    dataType: "json",
                    success: function(data) {
                        $('#editar_aula_id').val(data.id);
                        $('#editar_nombre').val(data.nombre);
                        $('#editar_descripcion').val(data.descripcion);
                        $('#editar_sede_id').val(data.sede_id);
                        $('#editar_tipo_aula_id').val(data.tipo_aula_id);
                        $('#editar_activo').prop('checked', data.activo);
                        var offcanvasEl = document.getElementById('editAulaSidebar');
                        var offcanvas = new bootstrap.Offcanvas(offcanvasEl);
                        offcanvas.show();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al cargar datos del aula:", error);
                    }
                });
            });
        });

        // Confirmación para eliminar (tu código existente)
        $(document).ready(function() {
            $(document).on('click', '.confirmacionEliminar', function(e) {
                e.preventDefault();
                var aulaId = $(this).data('id');
                Swal.fire({
                    title: '¿Estás seguro?',
                    html: 'Al eliminar el aula se eliminarán <strong>todos los horarios relacionados</strong>.<br>¿Deseas continuar?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Reemplaza esto con la ruta correcta si es diferente o si usas un form POST
                        // Ejemplo: $('#formEliminarAula-' + aulaId).submit();
                        window.location.href = '/aulas/' + aulaId + '/eliminar';
                    }
                });
            });
        });

        // Limpiar selects del offcanvas de filtros al cerrar (opcional, tu código existente)
        // $(document).ready(function() {
        //     $('#filtroAulasSidebar').on('hidden.bs.offcanvas', function() {
        //         $(this).find('select').prop('selectedIndex', 0);
        //         $(this).find('input[type="text"]').val(''); // Añadido para limpiar input de nombre
        //     });
        // });

        // --- NUEVO SCRIPT PARA QUITAR TAGS ---
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.remove-tag-aula').forEach(button => {
                button.addEventListener('click', function() {
                    const field = this.dataset.field;
                    // const value = this.dataset.value; // No es crucial para selects simples o inputs de texto

                    const form = document.getElementById(
                        'formFiltrosAulas'); // ID del formulario de filtros
                    const input = form.querySelector('[id="' + field + '"]');

                    if (input) {
                        if (input.tagName === 'SELECT') {
                            input.value =
                                ''; // Resetea a la opción por defecto (ej. "Todas las sedes")
                        } else { // Input de texto
                            input.value = '';
                        }
                        // Si usas select2 para los filtros, tendrías que usar:
                        // $(input).val(null).trigger('change');
                    }
                    form.submit();
                });
            });
        });
    </script>
@endsection

@section('content')
    <h4 class="mb-1 fw-semibold text-primary">Gestionar aulas</h4>
    <p class="text-black">Aquí puedes listar los detalles de las aulas y gestionarlas.</p>

    @include('layouts.status-msn')

    <div class="row mb-3 mt-4">
        <div class="col-md-6 mb-2 mb-md-0">
            <button type="button" class="btn btn-primary rounded-pill waves-effect waves-light" data-bs-toggle="offcanvas"
                data-bs-target="#addAulaSidebar">
                <i class="ti ti-plus me-1"></i> Nueva aula
            </button>
        </div>
        <div class="col-md-6 text-md-end">
            <button type="button" style="width:auto; padding:10px 15px;"
                class="btn btn-outline-secondary rounded-pill waves-effect waves-light" id="btnFiltroAulas"
                data-bs-toggle="offcanvas" data-bs-target="#filtroAulasSidebar" aria-controls="filtroAulasSidebar">
                Filtros <i class="ti ti-filter ms-1"></i>
            </button>
            {{-- El botón de Excel se puede mantener si la funcionalidad existe --}}
            @if (isset($aulas) && $aulas->count() > 0)

                <a href="{{ route('aulas.exportar', [
                        'filtro_nombre_aula' => $filtroNombreActual,
                        'filtro_sede' => $filtroSedeIdActual,
                        'filtro_tipo_aula' => $filtroTipoAulaIdActual
                    ]) }}"
                   style="width:auto; padding:10px 15px;"
                   class="btn btn-outline-secondary rounded-pill waves-effect waves-light ms-2"
                   id="btnXls">
                    Excel <i class="ti ti-file-type-xls ms-1"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            @if (isset($tagsBusqueda) && count($tagsBusqueda) > 0)
                <div class="filter-tags py-3">
                    <span class="text-muted me-2">Filtros aplicados:</span>
                    @foreach ($tagsBusqueda as $tag)
                        <button type="button"
                            class="btn btn-xs rounded-pill btn-outline-secondary remove-tag-aula ps-2 pe-1 mt-1"
                            data-field="{{ $tag->field }}" data-value="{{ $tag->value }}"> <span
                                class="align-middle">{{ $tag->label }} <i class="ti ti-x ti-xs"
                                    style="margin-bottom: 2px;"></i></span>
                        </button>
                    @endforeach
                    @if ($banderaFiltros == 1)
                        <a href="{{ route('aulas.gestionar') }}"
                            class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1 mt-1">
                            <span class="align-middle">Quitar todos <i class="ti ti-x ti-xs"
                                    style="margin-bottom: 2px;"></i></span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
    <div class="row equal-height-row">
        @forelse ($aulas as $aula)
            <div class="col equal-height-col col-12 col-xl-4 col-md-6 mb-3">
                <div class="h-100 card">
                    <div class="card-header">
                        <div class="d-flex align-items-start">
                            <div class="me-2 ms-1 mt-1 px-1">
                                <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $aula->nombre }}</h5><br>
                                <small
                                    class="badge rounded-pill {{ $aula->activo ? 'bg-label-success' : 'bg-label-danger' }}">{{ $aula->activo ? 'Activo' : 'Inactivo' }}</small>
                            </div>
                            <div class="ms-auto">
                                <div class="dropdown zindex-2 ">
                                    <button  style="border-radius: 20px;" class="btn p-1 border " type="button" class="btn border dropdown-toggle hide-arrow p-1"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical text-black"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item editar-aula" href="javascript:;"
                                                data-id="{{ $aula->id }}">
                                                Editar
                                            </a></li>
                                        <li><a class="dropdown-item confirmacionEliminar" href="javascript:;"
                                                data-id="{{ $aula->id }}">Eliminar</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column mb-3">
                            <div class="d-flex flex-row align-items-center">
                                <i class="ti ti-building-community text-black me-2"></i>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Sede:</small>
                                    <small class="fw-semibold text-black">{{ $aula->sede->nombre ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column mb-3">
                            <div class="d-flex flex-row align-items-center">
                                <i class="ti ti-school text-black me-2"></i>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Tipo aula:</small>
                                    <small class="fw-semibold text-black">{{ $aula->tipo->nombre ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </div>

                        @if ($aula->descripcion)
                            <div class="d-flex flex-column mb-3">
                                <div class="d-flex flex-row align-items-center">
                                    <i class="ti ti-file-info text-black me-2"></i>
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">Descripción:</small>
                                        <small class="fw-semibold text-black text-truncate" style="max-width: 250px;"
                                            title="{{ $aula->descripcion }}">{{ $aula->descripcion }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    No se encontraron aulas con los filtros aplicados.
                </div>
            </div>
        @endforelse
    </div>

    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            @if (isset($aulas) && $aulas->count())
                {{ $aulas->appends(request()->query())->links() }}
            @endif
        </div>
    </div>
    <form method="POST" action="{{ route('aulas.guardar') }}">
        @csrf
        <div class="offcanvas offcanvas-end" tabindex="-1" id="addAulaSidebar" aria-labelledby="addAulaSidebarLabel">
            <div class="offcanvas-header">
                <h4 id="addAulaSidebarLabel" class="offcanvas-title fw-bold text-primary">Nueva aula</h4>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body mx-0 flex-grow-0 pt-0">
                <div class="mb-3">
                    <label class="form-label" for="nombre_aula_crear">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre_aula_crear" name="nombre" required
                        placeholder="Ej: Salón 101">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="sede_id_crear">Sede <span class="text-danger">*</span></label>
                    <select class="form-select" id="sede_id_crear" name="sede_id" required>
                        <option value="" disabled selected>Selecciona una sede</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="tipo_aula_id_crear">Tipo de aula <span
                            class="text-danger">*</span></label>
                    <select class="form-select" id="tipo_aula_id_crear" name="tipo_aula_id" required>
                        <option value="" disabled selected>Selecciona un tipo</option>
                        @foreach ($tipos_aula as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="descripcion_aula_crear">Descripción</label>
                    <textarea class="form-control" id="descripcion_aula_crear" name="descripcion" rows="3"
                        placeholder="Detalles adicionales del aula"></textarea>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo_aula_crear" checked>
                        <label class="form-check-label" for="activo_aula_crear">Activo</label>
                    </div>
                </div>
            </div>
            <div class="offcanvas-footer p-3 border-top">
                <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Guardar</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect"
                    data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </div>
    </form>

    <form method="POST" id="formEditarAula" action="{{ route('aulas.actualizar') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" id="editar_aula_id">
        <div class="offcanvas offcanvas-end" tabindex="-1" id="editAulaSidebar" aria-labelledby="editAulaSidebarLabel">
            <div class="offcanvas-header">
                <h4 id="editAulaSidebarLabel" class="offcanvas-title fw-bold text-primary">Editar aula</h4>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body mx-0 flex-grow-0 pt-0">
                <div class="mb-3">
                    <label class="form-label" for="editar_nombre">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nombre" id="editar_nombre" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="editar_sede_id">Sede <span class="text-danger">*</span></label>
                    <select class="form-select" name="sede_id" id="editar_sede_id" required>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="editar_tipo_aula_id">Tipo de aula <span
                            class="text-danger">*</span></label>
                    <select class="form-select" name="tipo_aula_id" id="editar_tipo_aula_id" required>
                        @foreach ($tipos_aula as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="editar_descripcion">Descripción</label>
                    <textarea class="form-control" name="descripcion" id="editar_descripcion" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="activo" id="editar_activo">
                        <label class="form-check-label" for="editar_activo">Activo</label>
                    </div>
                </div>
            </div>
            <div class="offcanvas-footer p-3 border-top">
                <button type="submit" class="btn btn-primary rounded-pill me-2">Actualizar</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect"
                    data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </div>
    </form>

    {{-- Ajustar id, method, action y los inputs/selects --}}
    <form id="formFiltrosAulas" method="GET" action="{{ route('aulas.gestionar') }}">
        <div class="offcanvas offcanvas-end" tabindex="-1" id="filtroAulasSidebar"
            aria-labelledby="filtroAulasSidebarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title fw-bold text-primary" id="filtroAulasSidebarLabel">Filtros aulas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-0">
                {{-- NUEVO CAMPO PARA FILTRAR POR NOMBRE --}}
                <div class="mb-3">
                    <label for="filtro_nombre_aula" class="form-label">Nombre del aula</label>
                    <input type="text" class="form-control" id="filtro_nombre_aula" name="filtro_nombre_aula"
                        value="{{ $filtroNombreActual ?? '' }}" placeholder="Buscar por nombre">
                </div>

                <div class="mb-3">
                    <label for="filtro_sede" class="form-label">Sede</label>
                    <select name="filtro_sede" id="filtro_sede" class="form-select">
                        <option value="">Todas las sedes</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}"
                                {{ isset($filtroSedeIdActual) && $filtroSedeIdActual == $sede->id ? 'selected' : '' }}>
                                {{ $sede->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="filtro_tipo_aula" class="form-label">Tipo de aula</label>
                    <select name="filtro_tipo_aula" id="filtro_tipo_aula" class="form-select">
                        <option value="">Todos los tipos</option>
                        @foreach ($tipos_aula as $tipo)
                            <option value="{{ $tipo->id }}"
                                {{ isset($filtroTipoAulaIdActual) && $filtroTipoAulaIdActual == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="offcanvas-footer p-3 border-top">
                <div class="d-flex justify-content-start">
                    <button type="submit" class="btn btn-primary rounded-pill me-2">Aplicar filtros</button>
                    <a href="{{ route('aulas.gestionar') }}" class="btn btn-outline-secondary rounded-pill waves-effect">
                        Limpiar filtros
                    </a>
                    {{-- <button type="button" class="btn btn-outline-secondary ms-2 rounded-pill" data-bs-dismiss="offcanvas">Cancelar</button> --}}
                </div>
            </div>
        </div>
    </form>
@endsection
