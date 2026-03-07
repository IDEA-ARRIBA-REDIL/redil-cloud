{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)

@extends('layouts.layoutMaster')

{{-- Título de la página --}}
@section('title', 'Gestionar Maestros')

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
        // --- INICIO: CÓDIGO CORREGIDO PARA SWEETALERT DE ELIMINACIÓN ---
        const deleteForms = document.querySelectorAll('.form-eliminar-maestro');

        deleteForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Detiene el envío normal del formulario

                // Obtiene el nombre del maestro desde el atributo 'data-nombre' del formulario
                const maestroNombre = this.dataset.nombre || 'este maestro'; // Usa un texto genérico si el nombre no está

                Swal.fire({
                    title: '¿Estás seguro?',
                    // ----> AQUÍ SE USA LA VARIABLE maestroNombre <----
                    text: `¿Realmente deseas eliminar a ${maestroNombre}? ¡No podrás revertir esto!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, ¡eliminar!',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false // Deshabilita estilos por defecto para usar los de Bootstrap
                }).then((result) => {
                    // Si el usuario confirma, envía el formulario
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
        // --- FIN: CÓDIGO CORREGIDO ---

        // Limpiar formulario del offcanvas de creación al cerrarse (tu código existente)
        const offcanvasCrear = document.getElementById('offcanvasCrearMaestro');
        if (offcanvasCrear) {
            offcanvasCrear.addEventListener('hidden.bs.offcanvas', event => {
                const formCrear = document.getElementById('formCrearMaestro');
                if(formCrear) {
                    formCrear.reset();
                    // Si usas Select2 u otros plugins, necesitas resetearlos también aquí
                    // Ejemplo: $('#select-id-livewire').val(null).trigger('change');
                }
                // Limpia errores de validación de Laravel si los muestras
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            });
        }

        // Script para quitar tags de filtro (tu código existente)
        document.querySelectorAll('.remove-tag-maestro').forEach(button => {
            button.addEventListener('click', function() {
                const field = this.dataset.field;
                const form = document.getElementById('formFiltrosMaestros');
                const input = form.querySelector('[name="' + field + '"]');
                if (input) {
                    if (input.tagName === 'SELECT') input.value = '';
                    else input.value = '';
                }
                form.submit();
            });
        });

         // --- INICIO: CÓDIGO PARA CONFIRMACIÓN GENERAL DE ACCIONES (ACTIVAR/DESACTIVAR) ---
         // Este código es genérico y funcionará para cualquier form con la clase 'form-confirmar-accion'
         // y el atributo 'data-msj-confirmacion'
         const confirmActionForms = document.querySelectorAll('.form-confirmar-accion');
         confirmActionForms.forEach(form => {
             form.addEventListener('submit', function(event) {
                 event.preventDefault(); // Detiene el envío normal

                 const mensajeConfirmacion = this.dataset.msjConfirmacion || '¿Estás seguro de realizar esta acción?'; // Mensaje por defecto

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
                         this.submit(); // Envía el formulario si se confirma
                     }
                 });
             });
         });
         // --- FIN: CÓDIGO PARA CONFIRMACIÓN GENERAL ---

    }); // Fin del DOMContentLoaded
</script>
@endsection

@section('content')
 @include('layouts.status-msn')

{{-- Cabecera y Botón de Nuevo Maestro y Filtros --}}
<div class="row mb-4 mt-4 align-items-center">
    <div class="col-md-6">
        <h4 class="mb-1 fw-semibold text-primary">Gestionar maestros</h4>
        <p class="mb-0 text-black">Aquí podrás crear y gestionar tus maestros.</p>
    </div>

</div>
<div class="row">
    <div class="col-12 text-start my-4 mt-md-0">
        <button class="btn btn-primary rounded-pill me-2" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasCrearMaestro" aria-controls="offcanvasCrearMaestro">
            <i class="ti ti-plus me-1"></i> Nuevo maestro
        </button>
        {{-- NUEVO BOTÓN DE FILTROS --}}
        <button class="btn btn-outline-secondary rounded-pill" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasFiltrosMaestros" aria-controls="offcanvasFiltrosMaestros">
            <i class="ti ti-filter me-1"></i> Filtros
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
                class="btn btn-xs rounded-pill btn-outline-secondary remove-tag-maestro ps-2 pe-1 mt-1"
                data-field="{{ $tag->field }}" data-value="{{ $tag->value }}">
                <span class="align-middle">{{ $tag->label }} <i class="ti ti-x ti-xs"
                        style="margin-bottom: 2px;"></i></span>
            </button>
            @endforeach
            @if (isset($banderaFiltros) && $banderaFiltros == 1)
            <a href="{{ route('maestros.gestionar') }}"
                class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1 mt-1">
                <span class="align-middle">Quitar todos <i class="ti ti-x ti-xs"
                        style="margin-bottom: 2px;"></i></span>
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
{{-- Listado de Maestros (tu código existente con ajustes menores) --}}
<div class="row equal-height-row ">
    @forelse ($maestros as $maestro)
    <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-4 equal-height-col">
        <div class="card h-100 border rounded">
            {{-- ... (contenido de la tarjeta del maestro como lo tenías) ... --}}
            <img class="card-img-top object-fit-cover" style="height: 100px;"
                src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img//usuarios/banner-usuario/' . $maestro->user->portada) : Storage::url($configuracion->ruta_almacenamiento . '/img//usuarios/banner-usuario/' . $maestro->user->portada) }}"
                alt="portada {{ $maestro->user->primer_nombre ?? '' }}" />

            <div class="card-body">
                <div class="user-profile-header d-flex flex-row text-start mb-2 ">
                    <div class="flex-grow-1 mt-n10 mx-auto text-start">
                        @if (in_array($maestro->user->foto, ['default-m.png', 'default-f.png']))
                        <div class="avatar avatar-xl">
                            <span class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                {{ $maestro->user->inicialesNombre() }} </span>
                        </div>
                        @else
                        <div class="avatar avatar-xl">
                            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $maestro->user->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $maestro->user->foto }}"
                                alt="{{ $maestro->user->foto ?? 'foto' }}"
                                class="avatar-initial rounded-circle border border-3 border-white bg-info">
                        </div>
                        @endif

                        <div class="d-flex align-items-start justify-content-between mt-2">
                            <div class="align-items-center">
                                <h5 class="mb-0 fw-semibold">
                                    {{ $maestro->user->nombre(3) ?? 'Usuario no encontrado' }}
                                </h5> <span
                                    class="badge px-3 py-1 rounded-pill bg-label-{{ $maestro->activo ? 'primary' : 'danger' }}">
                                    {{ $maestro->activo ? 'Activo' : 'Inactivo' }}
                                </span><br>
                                <small class="text-muted">{{ $maestro->user->email ?? '' }}</small>

                            </div>
                            <div class="dropdown zindex-2 border rounded p-1">
                                <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical text-black"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a href="{{ route('maestros.horariosAsignados', $maestro->id) }}"
                                            class="dropdown-item">
                                            Horarios asignados
                                        </a>
                                    </li>
                                     @if ($maestro->activo)
                                        {{-- Si el maestro está ACTIVO, mostramos la opción para DESACTIVAR --}}
                                        <li>
                                            <form action="{{ route('maestros.desactivar', $maestro->id) }}" method="POST" class="form-confirmar-accion" data-msj-confirmacion="¿Seguro que deseas desactivar a este maestro?">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-dark">Desactivar</button>
                                            </form>
                                        </li>
                                    @else
                                        {{-- Si el maestro está INACTIVO, mostramos la opción para ACTIVAR --}}
                                        <li>
                                            <form action="{{ route('maestros.activar', $maestro->id) }}" method="POST" class="form-confirmar-accion" data-msj-confirmacion="¿Seguro que deseas activar a este maestro?">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-dark">Activar</button>
                                            </form>
                                        </li>
                                    @endif
                                    <li>
                                        <form action="{{ route('maestros.eliminar') }}" method="POST"
                                            class="form-eliminar-maestro"
                                            data-nombre="{{ $maestro->user->nombre(3) ?? 'Maestro ID ' . $maestro->id }}">
                                            @csrf
                                            <input type="hidden" name="maestro_id" value="{{ $maestro->id }}">
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
                <div class="d-flex flex-column mb-2">
                    <div class="d-flex flex-row align-items-center">
                        <i class="ti ti-phone-call text-black me-2"></i>
                        <div class="d-flex flex-column">
                            <small class="text-muted">Teléfono:</small>
                            <small
                                class="fw-semibold text-black">{{ $maestro->user->telefono_movil ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column">
                    <div class="d-flex flex-row align-items-center">
                        <i class="ti ti-id text-black me-2"></i>
                        <div class="d-flex flex-column">
                            <small class="text-muted">Identificación:</small>
                            <small
                                class="fw-semibold text-black">{{ $maestro->user->identificacion ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer mb-2 p-2">
                <div class="d-flex justify-content-center">

                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-warning text-center" role="alert">
            <i class="ti ti-alert-triangle me-2"></i> No se encontraron maestros registrados o que coincidan con
            los filtros.
        </div>
    </div>
    @endforelse
</div>

{{-- Paginación --}}
<div class="d-flex justify-content-center mt-4">
    @if (isset($maestros) && $maestros->count())
    {{ $maestros->appends(request()->query())->links() }}
    @endif
</div>

{{-- Offcanvas para Crear Nuevo Maestro --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCrearMaestro"
    aria-labelledby="offcanvasCrearMaestroLabel">
    <div class="offcanvas-header">
        <h4 id="offcanvasCrearMaestroLabel" class="offcanvas-title text-primary fw-semibold">Crear nuevo maestro</h4>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- El formulario ahora incluye un offcanvas-footer para los botones --}}
    <form id="formCrearMaestro" class="d-flex flex-column" method="POST" action="{{ route('maestros.guardar') }}">
        @csrf
        <div class="offcanvas-body flex-grow-1">
            {{-- Buscador de Usuarios --}}
            <div class="mb-3">
                @livewire('usuarios.usuarios-para-busqueda', [
                'id' => 'buscador-usuario',
                'tipoBuscador' => 'unico',
                'conDadosDeBaja' => 'no',
                'label' => 'Seleccionar Usuario',
                'placeholder' => 'Buscar por nombre o identificación...',
                'queUsuariosCargar' => 'todos',
                ])
            </div>

            {{-- Descripción --}}
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="4"></textarea>
            </div>

                 {{-- Filtro Escuela --}}
            <div class="mb-3">
                <label for="edit-role-id" class="form-label">Rol de Maestro (*)</label>
                <select required class="form-select" id="edit-role-id" name="role_id">
                    <option value="">Selecciona un rol...</option>
                    @foreach($rolesMaestro as $rol)
                        {{-- Marcamos el rol actual del maestro como seleccionado --}}
                        <option value="{{ $rol->id }}" {{-- Se rellenará con JS --}}>
                            {{ $rol->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- ========================================================== --}}
            {{-- === INICIO DE LA SECCIÓN AÑADIDA                        === --}}
            {{-- ========================================================== --}}

            {{-- Input para el estado Activo/Inactivo --}}
            <div class="mb-3">
                <label class="form-label">Estado</label>
                <div class="form-check form-switch form-switch-lg">
                    {{-- Este input oculto asegura que siempre se envíe un valor (0 si está desactivado) --}}
                    <input type="hidden" name="activo" value="0">
                    <input class="form-check-input" type="checkbox" name="activo" id="switchActivo" value="1" checked>
                    <label class="form-check-label" for="switchActivo">Activo</label>
                </div>
            </div>




            {{-- ========================================================== --}}
            {{-- === FIN DE LA SECCIÓN AÑADIDA                           === --}}
            {{-- ========================================================== --}}
        </div>

        {{-- Footer con los botones de acción --}}
        <div class="offcanvas-footer border-top p-3">
            <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Guardar maestro</button>
            <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect " data-bs-dismiss="offcanvas">Cancelar</button>
        </div>
    </form>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltrosMaestros"
    aria-labelledby="offcanvasFiltrosMaestrosLabel">
    <div class="offcanvas-header">
        <h4 id="offcanvasFiltrosMaestrosLabel" class="offcanvas-title text-primary fw-semibold">Filtrar maestros</h4>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 pt-0">
        <form id="formFiltrosMaestros" method="GET" action="{{ route('maestros.gestionar') }}">
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
                <label for="filtro_estado_maestro" class="form-label">Estado del maestro</label>
                <select class="form-select" id="filtro_estado_maestro" name="filtro_estado_maestro">
                    <option value="">Todos</option>
                    <option value="1"
                        {{ isset($filtroEstadoMaestroActual) && $filtroEstadoMaestroActual == '1' ? 'selected' : '' }}>
                        Activo</option>
                    <option value="0"
                        {{ isset($filtroEstadoMaestroActual) && $filtroEstadoMaestroActual == '0' && $filtroEstadoMaestroActual !== null ? 'selected' : '' }}>
                        Inactivo</option>
                </select>
            </div>

            {{-- Filtro Sede --}}
            <div class="mb-3">
                <label for="filtro_sede" class="form-label">Sede (Horarios Asignados)</label>
                <select class="form-select" id="filtro_sede" name="filtro_sede">
                    <option value="">Todas las sedes</option>
                    @if (isset($sedesParaFiltro))
                    @foreach ($sedesParaFiltro as $sede)
                    <option value="{{ $sede->id }}"
                        {{ isset($filtroSedeIdActual) && $filtroSedeIdActual == $sede->id ? 'selected' : '' }}>
                        {{ $sede->nombre }}
                    </option>
                    @endforeach
                    @endif
                </select>
            </div>

            {{-- Filtro Periodo --}}
            <div class="mb-3">
                <label for="filtro_periodo" class="form-label">Periodo (Horarios Asignados)</label>
                <select class="form-select" id="filtro_periodo" name="filtro_periodo">
                    <option value="">Todos los periodos</option>
                    @if (isset($periodosParaFiltro))
                    @foreach ($periodosParaFiltro as $periodo)
                    <option value="{{ $periodo->id }}"
                        {{ isset($filtroPeriodoIdActual) && $filtroPeriodoIdActual == $periodo->id ? 'selected' : '' }}>
                        {{ $periodo->nombre }}
                    </option>
                    @endforeach
                    @endif
                </select>
            </div>

            {{-- Filtro Escuela --}}
            <div class="mb-3">
                <label for="filtro_escuela" class="form-label">Escuela (Horarios Asignados)</label>
                <select class="form-select" id="filtro_escuela" name="filtro_escuela">
                    <option value="">Todas las escuelas</option>
                    @if (isset($escuelasParaFiltro))
                    @foreach ($escuelasParaFiltro as $escuela)
                    <option value="{{ $escuela->id }}"
                        {{ isset($filtroEscuelaIdActual) && $filtroEscuelaIdActual == $escuela->id ? 'selected' : '' }}>
                        {{ $escuela->nombre }}
                    </option>
                    @endforeach
                    @endif
                </select>
            </div>
            <div class="d-flex justify-content-start pt-3">
                <button type="submit" class="btn btn-primary rounded-pill me-2">Aplicar filtros</button>
                <a href="{{ route('maestros.gestionar') }}"
                    class="btn btn-outline-secondary rounded-pill">Limpiar</a>
            </div>
        </form>
    </div>
</div>
@endsection
