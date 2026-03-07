@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Periodos')

@section('vendor-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss', // Si decides usar select2 para filtros
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/quill/typography.scss',
'resources/assets/vendor/libs/quill/editor.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js', // Si decides usar select2
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/quill/quill.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
])
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')
<script>
    // Script para quitar tags (similar al de Temas)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.remove-tag-periodo').forEach(button => {
            button.addEventListener('click', function() {
                const field = this.dataset.field;
                const value = this.dataset
                    .value; // Necesario para selects múltiples, no tanto para simples/texto

                const form = document.getElementById(
                    'formFiltrosPeriodos'); // ID del formulario de filtros del Offcanvas
                const input = form.querySelector('[id="' + field + '"]');

                if (input) {
                    if (input.tagName === 'SELECT') {
                        // Para selects simples, simplemente resetea el valor
                        // Si fuera un select2 múltiple, la lógica es más compleja como en el ejemplo de Temas
                        input.value =
                            ''; // Resetea a la opción por defecto (ej. "Todas las sedes")
                        // Si usas select2, necesitarías $(input).val(null).trigger('change');
                    } else { // Input de texto
                        input.value = '';
                    }
                }
                form.submit();
            });
        });

        // Opcional: Inicializar Select2 si los usas en los filtros del Offcanvas
        // $('.select2-filtros').select2({ dropdownParent: $('#addEventSidebarFiltros') });
    });


    document.querySelectorAll('.form-finalizar-periodo').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción iniciará el cálculo final de notas y asistencias para todos los alumnos de este periodo. No se puede revertir.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar periodo',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection

@section('content')
@include('layouts.status-msn')
<h4 class="mb-1 fw-semibold text-primary">Gestionar periodos</h4>
<p class="text-black">Aquí podrás crear y gestionar tus periodos.</p>

<div class="row mb-3 mt-4">
    <div class="col-md-6">
        <a href="{{ route('periodo.crear') }}">
            <button type="button" class="btn btn-primary rounded-pill waves-effect waves-light">
                <i class="ti ti-plus me-1"></i> Nuevo periodo
            </button>
        </a>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" style="width:auto; padding:10px 15px;"
            class="btn btn-outline-secondary  waves-effect waves-light" id="btnFiltro" data-bs-toggle="offcanvas"
            data-bs-target="#addEventSidebarFiltros" aria-controls="addEventSidebarFiltros">
            Filtros <i class="ti ti-filter ms-1"></i>
        </button>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12">
        @if (isset($tagsBusqueda) && count($tagsBusqueda) > 0)
        <div class="filter-tags py-3">
            <span class="text-muted me-2">Filtros aplicados:</span>
            @foreach ($tagsBusqueda as $tag)
            <button type="button"
                class="btn btn-xs rounded-pill btn-outline-secondary remove-tag-periodo ps-2 pe-1 mt-1"
                data-field="{{ $tag->field }}" data-value="{{ $tag->value }}">
                <span class="align-middle">{{ $tag->label }} <i class="ti ti-x ti-xs"
                        style="margin-bottom: 2px;"></i></span>
            </button>
            @endforeach
            @if ($banderaFiltros == 1)
            <a href="{{ route('periodo.gestionar') }}"
                class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1 mt-1">
                <span class="align-middle">Quitar todos <i class="ti ti-x ti-xs"
                        style="margin-bottom: 2px;"></i></span>
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
<div class="row mb-5">
    @forelse($periodos as $periodo)
    <div class="col-equal-height-col col-12 col-xl-4 col-md-6 mb-4">
        <div class="h-100 card">
            <div class="card-header">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $periodo->nombre }}</h5><br>

                    </div>

                    <div  class="dropdown zindex-2 p-1">
                        <button  style="border-radius: 20px;" class="btn p-1 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical text-black"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                             @if( $rolActivo->hasPermissionTo('escuelas.opcion_modificar_periodo'))
                            <li>
                                <a href="{{ route('periodo.actualizar', $periodo->id) }}" class="dropdown-item">
                                    Gestionar
                                </a>
                            </li>
                            @endif
                             <li>
                                <a href="{{ route('periodo.alumnos', $periodo->id) }}" class="dropdown-item">
                                    Listado alumnos
                                </a>
                            </li>
                             <li>
                                <a href="{{ route('periodo.exportarHorarios', $periodo->id) }}" class="dropdown-item">
                                    Listado horarios
                                </a>
                            </li>


                            @if( $rolActivo->hasPermissionTo('escuelas.opcion_eliminar_periodo'))
                            <li>
                                <a href="" class="dropdown-item">
                                    Eliminar
                                </a>
                            </li>
                            @endif
                            @if (!$periodo->estado)
                            <li>
                                <form action="{{ route('periodo.activar', $periodo->id) }}" method="POST" class="form-activar-periodo">
                                    @csrf
                                    <button type="submit" class="dropdown-item ">
                                        Activar periodo
                                    </button>
                                </form>
                            </li>

                            <li>
                                <a href="{{ route('periodo.informe-final', $periodo->id) }}" class="dropdown-item">
                                    Informe final
                                </a>
                            </li>


                            @endif
                            {{-- === NUEVA SECCIÓN AÑADIDA === --}}
                            @if ($periodo->estado) {{-- Solo mostrar si el periodo está Inactivo --}}
                                @if( $rolActivo->hasPermissionTo('escuelas.opcion_finalizar_periodo'))
                                <li>
                                    <form action="{{ route('periodo.finalizar', $periodo->id) }}" method="POST" class="form-finalizar-periodo">
                                        @csrf
                                        <button type="submit" class="dropdown-item ">
                                            Finalizar periodo
                                        </button>
                                    </form>
                                </li>
                                @endif
                            @endif
                            {{-- === FIN DE LA NUEVA SECCIÓN === --}}
                            <li>
                                {{-- Asegúrate que la ruta de eliminación exista y maneje el método DELETE --}}
                                {{-- <form action="{{ route('periodo.eliminar', $periodo->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este periodo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger confirmacionEliminar"
                                    data-nombre="{{ $periodo->nombre }}">
                                    Eliminar
                                </button>
                                </form> --}}
                            </li>
                        </ul>
                    </div>

                </div>
                @if ($periodo->estado)
                <span class="badge bg-label-success rounded-pill my-2">Activo</span>
                @else
                <span class="badge bg-label-danger  rounded-pill my-2">Inactivo</span>
                @endif
                <div class="d-flex flex-row align-items-center mt-3">

                    <div class="d-flex flex-column">
                        <small class="text-muted"><i
                                class="menu-icon ti ti-building-skyscraper me-2"></i>Escuela:</small>
                        <small class="fw-semibold text-black">
                            {{ $periodo->escuela->nombre }}
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row justify-content-between mb-2">
                    <div class="col-12 col-md-6 align-items-center">

                        <div class="d-flex flex-column">
                            <small class="text-muted"> <i class="ti ti-id text-black me-2"></i>Total
                                matrículas:</small>
                            <small class="fw-semibold text-black">
                                {{ $periodo->matriculas ? $periodo->matriculas->count() : '0' }}
                            </small>
                        </div>

                    </div>
                     <div class="col-12 col-md-6 align-items-center">
                        <div class="d-flex flex-column text-star">
                            <small class="text-muted"> <i class="ti ti-calendar-month me-2"></i> Fecha
                                Lim maestros:</small>
                            <small class="fw-semibold text-black">
                                {{ \Carbon\Carbon::parse($periodo->fecha_maxima_entrega_notas)->format('d/m/Y') }}
                            </small>
                        </div>
                        </div>

                </div>
                <div class="row justify-content-between mb-2">
                    <div class="col-12 col-md-6 align-items-center">

                        <div class="d-flex flex-column text-star">
                            <small class="text-muted"> <i class="ti ti-calendar-month me-2"></i> Fecha
                                inicio:</small>
                            <small class="fw-semibold text-black">
                                {{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y') }}
                            </small>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 align-items-center">

                        <div class="d-flex flex-column text-star">
                            <small class="text-muted"> <i class="ti ti-calendar-month me-2"></i>Fecha fin:</small>
                            <small class="fw-semibold text-black">
                                {{ \Carbon\Carbon::parse($periodo->fecha_fin)->format('d/m/Y') }}
                            </small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">
            No hay periodos registrados que coincidan con los filtros aplicados.
        </div>
    </div>
    @endforelse
</div>

<div class="row mt-4">
    <div class="col-12 d-flex justify-content-center">
        {{ $periodos->appends(request()->query())->links() }}
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="addEventSidebarFiltros" aria-labelledby="addEventSidebarLabel"
    wire:ignore.self>
    {{--
            IMPORTANTE: Añadir id, method y action al formulario.
            Los nombres de los inputs (name="") deben coincidir con los que lees en el controlador.
            Los ids de los inputs (id="") deben coincidir con los valores "field" de los tags.
        --}}
    <form id="formFiltrosPeriodos" method="GET" action="{{ route('periodo.gestionar') }}">
        <div class="offcanvas-header">
            <h4 class="offcanvas-title fw-bold text-primary" id="addEventSidebarLabel">Filtros de periodos</h4>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">

            <div class="mb-3">
                <label class="form-label" for="filtro_nombre">Nombre del periodo</label>
                <input type="text" class="form-control" id="filtro_nombre" name="filtro_nombre"
                    value="{{ $filtroNombreActual ?? '' }}" placeholder="Ej: 2024-A">
            </div>

            <div class="mb-3">
                <label class="form-label" for="sedeFiltro">Sede</label>
                <select id="sedeFiltro" name="sedeFiltro" class="form-select">
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
                <label class="form-label" for="estado">Estado</label>
                <select id="estado" name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="1"
                        {{ isset($filtroEstadoActual) && $filtroEstadoActual == '1' ? 'selected' : '' }}>Activo
                    </option>
                    <option value="0"
                        {{ isset($filtroEstadoActual) && $filtroEstadoActual == '0' && $filtroEstadoActual !== null ? 'selected' : '' }}>
                        Inactivo</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" for="escuelaId">Escuela</label>
                <select id="escuelaId" name="escuelaId" class="form-select">
                    <option value="">Todas las escuelas</option>
                    @foreach ($escuelas as $escuela)
                    <option value="{{ $escuela->id }}"
                        {{ isset($filtroEscuelaIdActual) && $filtroEscuelaIdActual == $escuela->id ? 'selected' : '' }}>
                        {{ $escuela->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" for="anioFiltro">Año de inicio</label>
                <select id="anioFiltro" name="anioFiltro" class="form-select">
                    <option value="">Cualquier año</option>
                    @foreach ($anios as $anio)
                    <option value="{{ $anio }}"
                        {{ isset($filtroAnioActual) && $filtroAnioActual == $anio ? 'selected' : '' }}>
                        {{ $anio }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top">
            <button type="submit" class="btn btn-primary rounded-pill me-2">
                Aplicar filtros
            </button>
            <a href="{{ route('periodo.gestionar') }}" class="btn btn-outline-secondary rounded-pill waves-effect">
                Limpiar filtros
            </a>
        </div>
    </form>
</div>
@endsection
