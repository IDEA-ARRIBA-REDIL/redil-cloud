{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@extends('layouts.layoutMaster')

{{-- Título de la página --}}
@section('title', 'Gestionar Asesores de PDP')

{{-- Estilos específicos --}}
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

{{-- Scripts específicos --}}
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/ui-modals.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- Confirmación de Eliminación ---
            const deleteForms = document.querySelectorAll('.form-eliminar-asesor');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const asesorNombre = this.dataset.nombre || 'este asesor';

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: `¿Realmente deseas eliminar a ${asesorNombre}? ¡No podrás revertir esto!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, ¡eliminar!',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            confirmButton: 'btn btn-primary me-3',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            // --- Confirmación Genérica (Activar/Desactivar) ---
            const confirmActionForms = document.querySelectorAll('.form-confirmar-accion');
            confirmActionForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const mensajeConfirmacion = this.dataset.msjConfirmacion || '¿Estás seguro?';

                    Swal.fire({
                        title: 'Confirmar Acción',
                        text: mensajeConfirmacion,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            confirmButton: 'btn btn-primary me-3',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            // --- Limpiar Offcanvas de Creación al cerrar ---
            const offcanvasCrear = document.getElementById('offcanvasCrearAsesor');
            if (offcanvasCrear) {
                offcanvasCrear.addEventListener('hidden.bs.offcanvas', event => {
                    const formCrear = document.getElementById('formCrearAsesor');
                    if (formCrear) {
                        formCrear.reset();
                        // Aquí deberías resetear tu componente Livewire de búsqueda si es necesario
                        // Livewire.dispatch('resetear-buscador-usuario');
                    }
                    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));
                    document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                });
            }

            // --- Quitar Tags de Filtro ---
            document.querySelectorAll('.remove-tag-asesor').forEach(button => {
                button.addEventListener('click', function() {
                    const field = this.dataset.field;
                    const form = document.getElementById('formFiltrosAsesores');
                    const input = form.querySelector('[name="' + field + '"]');
                    if (input) {
                        input.value = '';
                    }
                    form.submit();
                });
            });

        });
    </script>
@endsection

@section('content')
    @include('layouts.status-msn') {{-- Para mostrar mensajes de éxito/error --}}

    {{-- Cabecera y Botones --}}
    <div class="row mb-4 mt-4 align-items-center">
        <div class="col-md-6">
            <h4 class="mb-1 fw-semibold text-primary">Gestionar asesores de puntos de pago (PDP)</h4>
            <p class="mb-0 text-black">Aquí podrás crear y gestionar tus asesores de cajas y puntos de pago.</p>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-end my-4 mt-md-0">
            <button class="btn btn-primary rounded-pill me-2" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasCrearAsesor" aria-controls="offcanvasCrearAsesor">
                <i class="ti ti-plus me-1"></i> Nuevo asesor
            </button>
            <button class="btn btn-outline-secondary rounded-pill" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasFiltrosAsesores" aria-controls="offcanvasFiltrosAsesores">
                <i class="ti ti-filter me-1"></i> Filtros
            </button>
        </div>
    </div>

    {{-- Tags de Filtros Aplicados --}}
    <div class="row mb-3">
        <div class="col-12">
            @if (isset($tagsBusqueda) && count($tagsBusqueda) > 0)
                <div class="filter-tags py-3">
                    <span class="text-dark me-2">Filtros aplicados:</span>
                    @foreach ($tagsBusqueda as $tag)
                        <button type="button"
                            class="btn btn-xs rounded-pill btn-outline-secondary remove-tag-asesor ps-2 pe-1 mt-1"
                            data-field="{{ $tag->field }}">
                            <span class="align-middle">{{ $tag->label }} <i class="ti ti-x ti-xs"
                                    style="margin-bottom: 2px;"></i></span>
                        </button>
                    @endforeach
                    @if (isset($banderaFiltros) && $banderaFiltros == 1)
                        <a href="{{ route('asesores_pdp.gestionar') }}"
                            class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1 mt-1">
                            <span class="align-middle">Quitar todos <i class="ti ti-x ti-xs"
                                    style="margin-bottom: 2px;"></i></span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Listado de Asesores (Tarjetas) --}}
    <div class="row equal-height-row ">
        @forelse ($asesores as $asesor)
            <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-4 equal-height-col">
                <div class="card h-100 border rounded">
                    {{-- Asumimos que $configuracion está disponible --}}
                    <img class="card-img-top object-fit-cover" style="height: 100px;"
                        src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img//usuarios/banner-usuario/' . $asesor->user->portada) : Storage::url($configuracion->ruta_almacenamiento . '/img//usuarios/banner-usuario/' . $asesor->user->portada) }}"
                        alt="portada {{ $asesor->user->primer_nombre ?? '' }}" />

                    <div class="card-body">
                        <div class="user-profile-header d-flex flex-row text-start mb-2 ">
                            <div class="flex-grow-1 mt-n10 mx-auto text-start">
                                @if (in_array($asesor->user->foto, ['default-m.png', 'default-f.png']))
                                    <div class="avatar avatar-xl">
                                        <span class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                            {{ $asesor->user->inicialesNombre() }} </span>
                                    </div>
                                @else
                                    <div class="avatar avatar-xl">
                                        <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $asesor->user->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $asesor->user->foto }}"
                                            alt="{{ $asesor->user->foto ?? 'foto' }}"
                                            class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                    </div>
                                @endif

                                <div class="d-flex align-items-start justify-content-between mt-2">
                                    <div class="align-items-center">
                                        <h5 class="mb-0 fw-semibold">
                                            {{ $asesor->user->nombre(3) ?? 'Usuario no encontrado' }}
                                        </h5>
                                        <span
                                            class="badge px-3 py-1 rounded-pill bg-label-{{ $asesor->activo ? 'primary' : 'secondary' }}">
                                            {{ $asesor->activo ? 'Activo' : 'Inactivo' }}
                                        </span><br>


                                    </div>
                                    <div style="border-radius: 50%" class="dropdown zindex-2 border rounded p-1">
                                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical text-black rounded-circle "></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if ($asesor->activo)
                                                <li>
                                                    <form action="{{ route('asesores_pdp.desactivar', $asesor->id) }}"
                                                        method="POST" class="form-confirmar-accion"
                                                        data-msj-confirmacion="¿Seguro que deseas desactivar a este asesor?">
                                                        @csrf
                                                        <button type="submit"
                                                            class="dropdown-item text-dark">Desactivar</button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('asesores_pdp.activar', $asesor->id) }}"
                                                        method="POST" class="form-confirmar-accion"
                                                        data-msj-confirmacion="¿Seguro que deseas activar a este asesor?">
                                                        @csrf
                                                        <button type="submit"
                                                            class="dropdown-item text-dark">Activar</button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li>
                                                <form action="{{ route('asesores_pdp.eliminar') }}" method="POST"
                                                    class="form-eliminar-asesor"
                                                    data-nombre="{{ $asesor->user->nombre(3) ?? 'Asesor ID ' . $asesor->id }}">
                                                    @csrf
                                                    <input type="hidden" name="asesor_id" value="{{ $asesor->id }}">
                                                    <button type="submit" class="dropdown-item">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4 mb-4 g-3">
                            <div class="col-6 p-0">

                                <div class="d-flex flex-column">

                                    <small class="text-dark"> <i
                                            class="ti ti-phone-call text-black me-2"></i>Teléfono:</small>
                                    <small
                                        class="fw-semibold text-black">{{ $asesor->user->telefono_movil ?? 'N/A' }}</small>
                                </div>


                            </div>
                            <div class="col-6 p-0">

                                <div class="d-flex flex-column">

                                    <small class="text-dark"> <i
                                            class="ti ti-id text-black me-2"></i>Identificación:</small>
                                    <small
                                        class="fw-semibold text-black">{{ $asesor->user->identificacion ?? 'N/A' }}</small>
                                </div>
                                {{-- ¡NUEVO! Badges de Tipo de Asesor --}}

                            </div>
                            <div class="row mt-4 mb-4 g-3">
                                <div class="col-6 p-0">

                                    <div class="d-flex flex-column">

                                        <small class="text-dark"><i class="ti ti-mail text-black me-2"></i> Email:</small>
                                        <small class="fw-semibold text-black">{{ $asesor->user->email ?? '' }}</small>
                                    </div>
                                </div>
                                <div class="col-6 p-0">
                                    <div class="d-flex flex-column">
                                        <small class="text-dark"><i class="ti ti-user text-black me-2"></i> Cargo:</small>
                                        @if ($asesor->es_cajero)
                                            <span class="w-50 badge rounded-pill bg-label-info mt-1">Cajero</span>
                                        @endif
                                        @if ($asesor->es_encargado)
                                            <span class="w-50 badge rounded-pill bg-label-success mt-1">Encargado</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>




                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center" role="alert">
                    <i class="ti ti-alert-triangle me-2"></i> No se encontraron asesores registrados o que coincidan con
                    los filtros.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="d-flex justify-content-center mt-4">
        @if (isset($asesores) && $asesores->count())
            {{ $asesores->appends(request()->query())->links() }}
        @endif
    </div>

    {{-- Offcanvas para Crear Nuevo Asesor --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCrearAsesor"
        aria-labelledby="offcanvasCrearAsesorLabel">
        <div class="offcanvas-header">
            <h4 id="offcanvasCrearAsesorLabel" class="offcanvas-title text-primary fw-semibold">Crear nuevo asesor</h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <form id="formCrearAsesor" class="d-flex flex-column" method="POST"
            action="{{ route('asesores_pdp.guardar') }}">
            @csrf
            <div class="offcanvas-body flex-grow-1">
                {{-- Buscador de Usuarios (Reutilizado) --}}
                <div class="mb-3">
                    @livewire('usuarios.usuarios-para-busqueda', [
                        'id' => 'buscador-usuario',
                        'tipoBuscador' => 'unico',
                        'conDadosDeBaja' => 'no',
                        'label' => 'Seleccionar usuario (*)',
                        'placeholder' => 'Buscar por nombre o identificación...',
                        'queUsuariosCargar' => 'todos',
                    ])
                    @error('buscador-usuario')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Rol del Asesor --}}
                <div class="mb-3">
                    <label for="role_id" class="form-label">Rol del asesor (*)</label>
                    <select required class="form-select" id="role_id" name="role_id">
                        <option value="">Selecciona un rol...</option>
                        @foreach ($rolesAsesor as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ¡NUEVOS CAMPOS DE TIPO! --}}
                <div class="mb-3">
                    <label class="form-label">Tipo de asesor </label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="es_cajero" value="1"
                            id="checkEsCajero">
                        <label class="form-check-label" for="checkEsCajero">
                            Es cajero
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="es_encargado" value="1"
                            id="checkEsEncargado">
                        <label class="form-check-label" for="checkEsEncargado">
                            Es encargado de PDP
                        </label>
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="4"></textarea>
                </div>

                {{-- Estado Activo/Inactivo --}}
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <div class="form-check form-switch form-switch-lg">
                        <input type="hidden" name="activo" value="0">
                        <input class="form-check-input" type="checkbox" name="activo" id="switchActivo" value="1"
                            checked>
                        <label class="form-check-label" for="switchActivo">Activo</label>
                    </div>
                </div>
            </div>

            {{-- Footer con los botones de acción --}}
            <div class="offcanvas-footer border-top p-3">
                <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Guardar asesor</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect "
                    data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </form>
    </div>

    {{-- Offcanvas para Filtros --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltrosAsesores"
        aria-labelledby="offcanvasFiltrosAsesoresLabel">
        <div class="offcanvas-header">
            <h4 id="offcanvasFiltrosAsesoresLabel" class="offcanvas-title text-primary fw-semibold">Filtrar asesores</h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 pt-0">
            <form id="formFiltrosAsesores" method="GET" action="{{ route('asesores_pdp.gestionar') }}">
                {{-- Filtro Búsqueda General --}}
                <div class="mb-3">
                    <label for="filtro_busqueda_general" class="form-label">Buscar por Nombre, Identificación o
                        Email</label>
                    <input type="text" class="form-control" id="filtro_busqueda_general"
                        name="filtro_busqueda_general" value="{{ $filtrosActuales['filtro_busqueda_general'] ?? '' }}"
                        placeholder="Escribe aquí...">
                </div>

                {{-- Filtro Estado --}}
                <div class="mb-3">
                    <label for="filtro_estado_asesor" class="form-label">Estado del asesor</label>
                    <select class="form-select" id="filtro_estado_asesor" name="filtro_estado_asesor">
                        <option value="">Todos</option>
                        <option value="1"
                            {{ ($filtrosActuales['filtro_estado_asesor'] ?? '') == '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0"
                            {{ ($filtrosActuales['filtro_estado_asesor'] ?? '') == '0' ? 'selected' : '' }}>Inactivo
                        </option>
                    </select>
                </div>

                {{-- ¡NUEVO FILTRO! Tipo de Asesor --}}
                <div class="mb-3">
                    <label for="filtro_tipo_asesor" class="form-label">Tipo de asesor</label>
                    <select class="form-select" id="filtro_tipo_asesor" name="filtro_tipo_asesor">
                        <option value="">Todos</option>
                        <option value="cajero"
                            {{ ($filtrosActuales['filtro_tipo_asesor'] ?? '') == 'cajero' ? 'selected' : '' }}>Solo cajeros
                        </option>
                        <option value="encargado"
                            {{ ($filtrosActuales['filtro_tipo_asesor'] ?? '') == 'encargado' ? 'selected' : '' }}>Solo
                            encargados</option>
                    </select>
                </div>

                <div class="d-flex justify-content-start pt-3">
                    <button type="submit" class="btn btn-primary rounded-pill me-2">Aplicar filtros</button>
                    <a href="{{ route('asesores_pdp.gestionar') }}"
                        class="btn btn-outline-secondary rounded-pill">Limpiar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
