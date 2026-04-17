@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia Infantil — Administración')

@section('page-style')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    ])
@endsection

@section('page-script')
<script>
    // Confirmación SweetAlert2 para eliminar salón
    function confirmarEliminarSalon(formId, nombre) {
        Swal.fire({
            title: '¿Eliminar el salón <b>' + nombre + '</b>?',
            html: 'Esta acción no es reversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">
    <i class="ti ti-baby-carriage me-2"></i>Iglesia Infantil — Administración
</h4>
<p class="mb-4 text-black">Gestiona los salones y estaciones disponibles para la iglesia infantil.</p>

@include('layouts.status-msn')

<div class="row g-4">

    {{-- ================================================================ --}}
    {{-- PANEL SALONES --}}
    {{-- ================================================================ --}}
    <div class="col-lg-12">
        <div class="card shadow-none bg-transparent border-0">
            <div class="card-header d-flex justify-content-between align-items-center p-0 mb-3 bg-transparent">
                <h5 class="mb-0 text-black fw-bold"><i class="ti ti-door me-2 text-primary"></i>Configuraci&oacute;n de Salones</h5>
                <button class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalCrearSalon">
                    <i class="ti ti-plus me-1"></i>Nuevo sal&oacute;n
                </button>
            </div>
            
            <div class="row g-3">
                @forelse ($salones as $salon)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border shadow-sm" style="border-top: 3px solid var(--bs-primary) !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fw-bold text-black">{{ $salon->nombre }}</h5>
                                        <span class="badge bg-label-{{ $salon->activo ? 'success' : 'secondary' }} mb-2">
                                            {{ $salon->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle hide-arrow waves-effect" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical text-black"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalEditarSalon{{ $salon->id }}">
                                                    <i class="ti ti-edit me-2"></i>Editar nombre/descripci&oacute;n
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalEstaciones{{ $salon->id }}">
                                                    <i class="ti ti-settings me-2"></i>Configurar estaciones
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger"
                                                    onclick="confirmarEliminarSalon('formEliminarSalon{{ $salon->id }}', '{{ $salon->nombre }}')">
                                                    <i class="ti ti-trash me-2"></i>Eliminar sal&oacute;n
                                                </button>
                                                <form id="formEliminarSalon{{ $salon->id }}" method="POST"
                                                    action="{{ route('iglesiaInfantil.salones.eliminar', $salon) }}" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <p class="text-black small mb-3">{{ $salon->descripcion ?: 'Sin descripción adicional.' }}</p>

                                <div class="mt-auto pt-2 border-top">
                                    <small class="text-black fw-bold d-block mb-1">Estaciones vinculadas:</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse ($salon->estaciones as $estacion)
                                            <span class="badge bg-label-primary">{{ $estacion->nombre }}</span>
                                        @empty
                                            <span class="text-muted small">Ninguna estaci&oacute;n configurada</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal editar salón --}}
                    <div class="modal fade" id="modalEditarSalon{{ $salon->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title text-black fw-bold">Editar sal&oacute;n</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('iglesiaInfantil.salones.actualizar', $salon) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label text-black fw-bold">Nombre <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre" class="form-control"
                                                value="{{ $salon->nombre }}" required maxlength="150">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-black fw-bold">Descripci&oacute;n</label>
                                            <textarea name="descripcion" class="form-control" rows="2">{{ $salon->descripcion }}</textarea>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="activo"
                                                value="1" {{ $salon->activo ? 'checked' : '' }}>
                                            <label class="form-check-label text-black">Sal&oacute;n habilitado</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Modal asignar estaciones al salón --}}
                    <div class="modal fade" id="modalEstaciones{{ $salon->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title text-black fw-bold">
                                        <i class="ti ti-settings me-2 text-primary"></i>Estaciones: {{ $salon->nombre }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('iglesiaInfantil.salones.estaciones.asignar', $salon) }}">
                                    @csrf
                                    <div class="modal-body">
                                        <p class="text-black small mb-3">Marca las estaciones que est&aacute;n disponibles en este sal&oacute;n:</p>
                                        @foreach ($estaciones as $estacion)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox"
                                                    name="estaciones[]"
                                                    value="{{ $estacion->id }}"
                                                    id="est{{ $salon->id }}_{{ $estacion->id }}"
                                                    {{ $salon->estaciones->contains($estacion->id) ? 'checked' : '' }}>
                                                <label class="form-check-label text-black" for="est{{ $salon->id }}_{{ $estacion->id }}">
                                                    <strong>{{ $estacion->nombre }}</strong>
                                                    @if ($estacion->descripcion)
                                                        <small class="text-black d-block opacity-75">{{ $estacion->descripcion }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                        @if ($estaciones->isEmpty())
                                            <p class="text-center py-3 text-black opacity-50">No hay estaciones registradas para seleccionar.</p>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Vincular estaciones</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5 text-black bg-light rounded-3 border border-dashed">
                            <i class="ti ti-door-off d-block mb-2" style="font-size:3rem; opacity: 0.3;"></i>
                            <h5 class="text-black">No hay salones configurados</h5>
                            <p class="mb-0">Comienza creando el primer sal&oacute;n de clase para la iglesia infantil.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PANEL ESTACIONES --}}
    {{-- ================================================================ --}}
    <div class="col-lg-12 pt-4">
        <hr class="my-4">
        <div class="card shadow-none bg-transparent border-0">
            <div class="card-header d-flex justify-content-between align-items-center p-0 mb-3 bg-transparent">
                <h5 class="mb-0 text-black fw-bold"><i class="ti ti-layout-grid me-2 text-info"></i>Estaciones Globales</h5>
                <button class="btn btn-info waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalCrearEstacion">
                    <i class="ti ti-plus me-1"></i>Nueva estaci&oacute;n
                </button>
            </div>
            
            <div class="row g-3">
                @forelse ($estaciones as $estacion)
                    <div class="col-md-4 col-xl-3">
                        <div class="card h-100 border shadow-sm" style="border-left: 3px solid var(--bs-info) !important;">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-1 fw-bold text-black text-truncate">{{ $estacion->nombre }}</h6>
                                        <p class="text-black small mb-0">{{ $estacion->descripcion ?: 'Sin descripción.' }}</p>
                                    </div>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-info dropdown-toggle hide-arrow waves-effect" data-bs-toggle="dropdown">
                                            <i class="ti ti-settings text-black"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalEditarEstacion{{ $estacion->id }}">
                                                    <i class="ti ti-edit me-2"></i>Editar estaci&oacute;n
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal editar estación --}}
                    <div class="modal fade" id="modalEditarEstacion{{ $estacion->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title text-black fw-bold">Editar estaci&oacute;n</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('iglesiaInfantil.estaciones.actualizar', $estacion) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label text-black fw-bold">Nombre <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre" class="form-control"
                                                value="{{ $estacion->nombre }}" required maxlength="150">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-black fw-bold">Descripci&oacute;n</label>
                                            <textarea name="descripcion" class="form-control" rows="2">{{ $estacion->descripcion }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 text-black opacity-50">
                        <i class="ti ti-layout-grid-remove d-block mb-2" style="font-size:2.5rem;"></i>
                        No hay estaciones configuradas.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>{{-- fin row --}}

{{-- Modal: Crear salón --}}
<div class="modal fade" id="modalCrearSalon" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-black fw-bold"><i class="ti ti-door me-2 text-primary"></i>Nuevo sal&oacute;n</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('iglesiaInfantil.salones.crear') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-black fw-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" required maxlength="150"
                            placeholder="Ej: Sala Beb&eacute;s">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-black fw-bold">Descripci&oacute;n</label>
                        <textarea name="descripcion" class="form-control" rows="2"
                            placeholder="Descripci&oacute;n opcional del sal&oacute;n..."></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="activo" value="1" checked>
                        <label class="form-check-label text-black fw-bold">Sal&oacute;n habilitado</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                        <i class="ti ti-check me-1"></i>Crear sal&oacute;n
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Crear estación --}}
<div class="modal fade" id="modalCrearEstacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-black fw-bold"><i class="ti ti-layout-grid me-2 text-info"></i>Nueva estaci&oacute;n</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('iglesiaInfantil.estaciones.crear') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-black fw-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" required maxlength="150"
                            placeholder="Ej: Cambio de Pa&ntilde;ales">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-black fw-bold">Descripci&oacute;n</label>
                        <textarea name="descripcion" class="form-control" rows="2"
                            placeholder="Descripci&oacute;n opcional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info waves-effect waves-light text-white">
                        <i class="ti ti-check me-1"></i>Crear estaci&oacute;n
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
