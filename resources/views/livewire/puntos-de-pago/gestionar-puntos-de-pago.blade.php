<div>
    {{-- ================================================================== --}}
    {{-- 1. TÍTULO Y SECCIÓN DE CONTADORES (Sin cambios)                  --}}
    {{-- ================================================================== --}}
    <div class="row pt-5">
        <div class="col-lg-3 col-6">
            <a href="{{ route('puntosDePago.gestionar', ['tipo' => 'todos']) }}">
                <div
                    class="h-100 card border rounded-3 shadow-sm {{ $tipo == 'todos' ? 'border-primary border-2' : '' }}">
                    <div class="card-body d-flex flex-row p-3">
                        <div class="card-title mb-0 lh-sm">
                            <p class="text-black mb-0" style="font-size: .8125rem">Todos</p>
                            <h5 class="mb-0 me-2">{{ $contadorTodos }}</h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-6">
            <a href="{{ route('puntosDePago.gestionar', ['tipo' => 'dados-de-baja']) }}">
                <div
                    class="h-100 card border rounded-3 shadow-sm {{ $tipo == 'dados-de-baja' ? 'border-primary border-2' : '' }}">
                    <div class="card-body d-flex flex-row p-3">
                        <div class="card-title mb-0 lh-sm">
                            <p class="text-black mb-0" style="font-size: .8125rem">Dados de baja</p>
                            <h5 class="mb-0 me-2">{{ $contadorBaja }}</h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <hr>

    {{-- ================================================================== --}}
    {{-- 2. SECCIÓN DE BÚSQUEDA Y ACCIONES (Sin cambios)                  --}}
    {{-- ================================================================== --}}
    <div class="row mt-5">
        {{-- Input de Búsqueda --}}
        <div class="col-12 col-md-6 mb-3">
            <label for="buscar" class="form-label">Buscar por nombre, sede o encargado</label>
            <div class="input-group input-group-merge bg-white">
                <input wire:model.live.debounce.500ms="buscar" type="text" class="form-control"
                    placeholder="Escribe aquí...">
                <span class="input-group-text"><i class="ti ti-search"></i></span>
            </div>
        </div>

        {{-- Botones de Acción --}}
        <div class="col-12 col-md-6 mb-3 d-flex justify-content-start justify-content-md-end align-items-end">
            <button wire:click="abrirModalCrearPunto" type="button" class="btn btn-primary waves-effect  me-2">
                <i class="ti ti-plus me-1"></i>
                <span class="fw-semibold">Crear punto</span>
            </button>
            <button wire:click="exportarExcel" type="button" class="btn btn-outline-secondary waves-effect  me-2">
                <i class="ti ti-file-spreadsheet me-1"></i>
                <span class="fw-semibold">Exportar</span>
            </button>
            <button class="btn btn-outline-secondary " type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasFiltrosPuntos" aria-controls="offcanvasFiltrosPuntos">
                <i class="ti ti-filter me-1"></i> Filtros
            </button>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- 3. SECCIÓN DE TAGS DE FILTRO (Sin cambios)                       --}}
    {{-- ================================================================== --}}
    <div class="row mb-3">
        <div class="col-12">
            @if (isset($tagsBusqueda) && count($tagsBusqueda) > 0)
                <div class="filter-tags py-3">
                    <span class="text-muted me-2">Filtros aplicados:</span>
                    @foreach ($tagsBusqueda as $tag)
                        <button type="button"
                            class="btn btn-xs rounded-pill btn-outline-secondary remove-tag-punto ps-2 pe-1 mt-1"
                            data-field="{{ $tag->field }}" data-value="{{ $tag->value }}">
                            <span class="align-middle">{{ $tag->label }} <i class="ti ti-x ti-xs"
                                    style="margin-bottom: 2px;"></i></span>
                        </button>
                    @endforeach
                    @if (isset($banderaFiltros) && $banderaFiltros == 1)
                        <a href="{{ route('puntosDePago.gestionar', ['tipo' => $tipo]) }}"
                            class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1 mt-1">
                            <span class="align-middle">Quitar todos <i class="ti ti-x ti-xs"
                                    style="margin-bottom: 2px;"></i></span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>


    {{-- ================================================================== --}}
    {{-- 3. LISTA DE PUNTOS DE PAGO (¡DISEÑO DE TARJETA ACTUALIZADO!)      --}}
    {{-- ================================================================== --}}
    <div class="row g-4 mt-1 equal-height-row"> {{-- Añadido equal-height-row --}}
        {{-- Indicador de Carga --}}
        <div wire:loading.flex class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        @forelse($puntosDePago as $puntoDePago)
            {{--
      Aquí aplicamos el diseño de "consejeros" a la tarjeta de "puntoDePago".
      Usamos wire:key en el elemento raíz del bucle.
    --}}
            <div class="col equal-height-col col-12 col-md-4 col-lg-4" wire:loading.remove
                wire:key="punto-{{ $puntoDePago->id }}">
                <div class="card rounded-3 shadow h-100"> {{-- Añadido h-100 --}}

                    {{-- 1. Encabezado (Nombre, Estado, Acciones) --}}
                    <div class="card-header border-bottom d-flex px-4 pt-3 pb-1"
                        style="background-color:#F9F9F9!important">
                        <div class="flex-fill row">
                            <div class=" d-flex justify-content-between align-items-center">
                                <h5 class="fw-semibold ms-1 text-black m-0">
                                    {{ $puntoDePago->nombre }}
                                </h5>
                                <span
                                    class="badge px-3 py-1 rounded-pill bg-label-{{ $puntoDePago->estado ? 'primary' : 'secondary' }}">
                                    {{ $puntoDePago->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>

                        <div class="">
                            <div class="ms-auto">
                                <div class="dropdown zindex-2 p-1 float-end">
                                    {{-- Botón de dropdown (estilo "consejeros") --}}
                                    <button type="button"
                                        class="btn btn-sm rounded-pill btn-icon border rounded-circle "
                                        data-bs-toggle="dropdown" aria-expanded="false"><i
                                            class="ti ti-dots-vertical"></i> </button>

                                    {{-- Acciones (son las de Livewire de tu archivo original) --}}
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('puntos_de_pago.ver_informe', $puntoDePago->id) }}">
                                                Ver Informe
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                wire:click="abrirModalEditarPunto({{ $puntoDePago->id }})">
                                                Editar punto
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                wire:click="toggleEstado({{ $puntoDePago->id }})"
                                                wire:loading.attr="disabled">
                                                <span wire:loading.remove
                                                    wire:target="toggleEstado({{ $puntoDePago->id }})">
                                                    {{ $puntoDePago->estado ? 'Inactivar' : 'Activar' }}
                                                </span>
                                                <span wire:loading wire:target="toggleEstado({{ $puntoDePago->id }})">
                                                    Cambiando...
                                                </span>
                                            </a>
                                        </li>
                                        @if (!$puntoDePago->trashed())
                                            <li>
                                                <a class="dropdown-item text-dark" href="javascript:void(0);"
                                                    wire:click="confirmarEliminacionPunto({{ $puntoDePago->id }})">
                                                    Dar de baja
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Cuerpo Principal (Detalle Primario: Sede) --}}
                    <div class="card-body">
                        <div class="row mt-2">
                            <div class="col-12 d-flex flex-column">
                                <small class="text-black">Sede:</small>
                                <small class="fw-semibold text-black ">{{ $puntoDePago->sede->nombre }}</small>
                            </div>
                        </div>

                        {{-- 3. Cuerpo Colapsable (Detalle Secundario: Encargado) --}}
                        <div class="collapse" id="cardBodyPunto{{ $puntoDePago->id }}">
                            <div class="col-12">
                                <hr class="my-3 border-1">
                            </div>

                            <div class="col-12 d-flex flex-column mt-1">
                                <small class="text-black">Encargado:</small>
                                <small class="fw-semibold text-black ">
                                    @if ($puntoDePago->encargado)
                                        {{ $puntoDePago->encargado->primer_nombre }}
                                        {{ $puntoDePago->encargado->primer_apellido }}
                                    @else
                                        No asignado
                                    @endif
                                </small>
                            </div>

                            {{-- Puedes añadir más detalles aquí si los cargas en el controlador --}}
                            {{--
            <div class="col-12 d-flex flex-column mt-2">
              <small class="text-black">Cajas asignadas:</small>
              <small class="fw-semibold text-black ">...</small>
            </div>
            --}}

                        </div>
                    </div>

                    {{-- 4. Footer con Botón para Colapsar --}}
                    <div class="card-footer border-top p-1">
                        <div class="d-flex justify-content-center">
                            <button type="button"
                                class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
                                data-bs-toggle="collapse" data-bs-target="#cardBodyPunto{{ $puntoDePago->id }}">
                                <span class="ti ti-plus"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Mensaje si no hay resultados (Sin cambios) --}}
            <div class="col-12" wire:loading.remove>
                <div class="alert alert-info text-center">
                    No se encontraron puntos de pago que coincidan con los filtros.
                </div>
            </div>
        @endforelse
    </div>

    {{-- ================================================================== --}}
    {{-- 4. PAGINACIÓN (Sin cambios)                                      --}}
    {{-- ================================================================== --}}
    <div class="row my-3 mt-5" wire:loading.remove>
        @if ($puntosDePago->hasPages())
            <p> {{ $puntosDePago->lastItem() }} <b>de</b> {{ $puntosDePago->total() }} <b>puntos de pago - Página</b>
                {{ $puntosDePago->currentPage() }} </p>
            {!! $puntosDePago->appends($filtrosActuales)->links() !!}
        @endif
    </div>

    {{-- ================================================================== --}}
    {{-- 5. MODAL PARA CRUD (Sin cambios)                                 --}}
    {{-- ================================================================== --}}
    <div class="modal fade" id="modalPuntoDePago" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $esEdicion ? 'Editar' : 'Crear' }} Punto de pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="cerrarModalPuntoDePago"></button>
                </div>

                <form wire:submit="guardarPuntoDePago">
                    <div class="modal-body">
                        {{-- Nombre --}}
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nombrePunto" class="form-label">Nombre</label>
                                <input type="text" id="nombrePunto" class="form-control"
                                    placeholder="Ej: Punto Sede Norte" wire:model="nombrePunto">
                                @error('nombrePunto')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        {{-- Sede (Select2 Modal) --}}
                        <div class="row">
                            <div class="col mb-3" wire:ignore>
                                <label for="selectSedeModal" class="form-label">Sede</label>
                                <select id="selectSedeModal" class="form-select">
                                    <option value="">Seleccione una sede...</option>
                                    @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('sedeId')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- Encargado (Select2 Modal) --}}
                        <div class="row">
                            <div class="col mb-0" wire:ignore>
                                <label for="selectEncargadoModal" class="form-label">Encargado (opcional)</label>
                                <select id="selectEncargadoModal" class="form-select">
                                    <option value="">Seleccione un encargado...</option>
                                    @foreach ($listaEncargados as $encargado)
                                        <option value="{{ $encargado->id }}">{{ $encargado->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('encargadoId')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal"
                            wire:click="cerrarModalPuntoDePago">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <span wire:loading.remove wire:target="guardarPuntoDePago">
                                {{ $esEdicion ? 'Actualizar' : 'Guardar' }}
                            </span>
                            <span wire:loading wire:target="guardarPuntoDePago"
                                class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- 6. OFFCANVAS PARA FILTROS (Sin cambios)                          --}}
    {{-- ================================================================== --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltrosPuntos"
        aria-labelledby="offcanvasFiltrosPuntosLabel">
        <div class="offcanvas-header">
            <h4 id="offcanvasFiltrosPuntosLabel" class="offcanvas-title text-primary fw-semibold">Filtrar puntos de
                pago</h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 pt-0">
            <form id="formFiltrosPuntos" method="GET"
                action="{{ route('puntosDePago.gestionar', ['tipo' => $tipo]) }}">

                <input type="hidden" name="buscar" value="{{ $filtrosActuales['buscar'] ?? '' }}">

                <div class="mb-3" wire:ignore>
                    <label for="filtroSedeSelectOffcanvas" class="form-label">Filtrar por sede</label>
                    <div>
                        <a href="javascript:;" id="select-all-sedes"> <span class="fw-medium small">Todos</span></a>
                        |
                        <a href="javascript:;" id="deselect-all-sedes"><span
                                class="fw-medium small">Ninguno</span></a>
                    </div>
                    <select id="filtroSedeSelectOffcanvas" name="filtroSede[]" class="form-select" multiple>
                        @php $availableSedeIds = []; @endphp
                        @foreach ($sedes as $sede)
                            @php $availableSedeIds[] = $sede->id; @endphp
                            <option value="{{ $sede->id }}"
                                {{ in_array($sede->id, $filtrosActuales['filtroSede']) ? 'selected' : '' }}>
                                {{ $sede->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-start pt-3">
                    <button type="submit" class="btn btn-primary rounded-pill me-2">Aplicar filtros</button>
                    <a href="{{ route('puntosDePago.gestionar', ['tipo' => $tipo]) }}"
                        class="btn btn-outline-secondary rounded-pill">Limpiar</a>
                </div>
            </form>
        </div>
    </div>


    {{-- ================================================================== --}}
    {{-- 7. SCRIPTS (Sin cambios)                                         --}}
    {{-- ================================================================== --}}
    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {

                // --- Select2 para el Offcanvas de Filtros ---
                const filtroSedeOffcanvas = $('#filtroSedeSelectOffcanvas');
                const btnSelectAllSedes = $('#select-all-sedes');
                const btnDeselectAllSedes = $('#deselect-all-sedes');
                const availableSedeIds = @json($sedes->pluck('id'));

                filtroSedeOffcanvas.select2({
                    placeholder: 'Filtrar por Sede',
                    allowClear: true,
                    dropdownParent: $('#offcanvasFiltrosPuntos')
                });
                btnSelectAllSedes.on('click', function() {
                    filtroSedeOffcanvas.val(availableSedeIds).trigger('change');
                });
                btnDeselectAllSedes.on('click', function() {
                    filtroSedeOffcanvas.val(null).trigger('change');
                });


                // --- Lógica para quitar tags de filtro ---
                document.querySelectorAll('.remove-tag-punto').forEach(button => {
                    button.addEventListener('click', function() {
                        const field = this.dataset.field;
                        const value = this.dataset.value;
                        const urlParams = new URLSearchParams(window.location.search);

                        if (field === 'buscar') {
                            urlParams.delete('buscar');
                        } else {
                            let values = urlParams.getAll(field + '[]');
                            if (values.length > 0) {
                                values = values.filter(v => v != value);
                                urlParams.delete(field + '[]');
                                values.forEach(v => urlParams.append(field + '[]', v));
                            }
                        }
                        const baseUrl = window.location.origin + window.location.pathname;
                        window.location.href = baseUrl + '?' + urlParams.toString();
                    });
                });


                // --- Control de Modales CRUD ---
                const modalPuntoDePagoEl = document.getElementById('modalPuntoDePago');
                const modalPuntoDePago = new bootstrap.Modal(modalPuntoDePagoEl);
                const modalSedeSelect = $('#selectSedeModal');
                const modalEncargadoSelect = $('#selectEncargadoModal');

                modalSedeSelect.select2({
                    placeholder: 'Seleccione una sede...',
                    allowClear: true,
                    dropdownParent: $(modalPuntoDePagoEl)
                });
                modalSedeSelect.on('change', function(e) {
                    @this.set('sedeId', e.target.value);
                });

                modalEncargadoSelect.select2({
                    placeholder: 'Seleccione un encargado...',
                    allowClear: true,
                    dropdownParent: $(modalPuntoDePagoEl)
                });
                modalEncargadoSelect.on('change', function(e) {
                    @this.set('encargadoId', e.target.value);
                });

                @this.on('abrir-modal-punto', () => {
                    modalSedeSelect.val(null).trigger('change');
                    modalEncargadoSelect.val(null).trigger('change');
                    modalPuntoDePago.show();
                });

                @this.on('cerrar-modal-punto', () => {
                    modalPuntoDePago.hide();
                    modalSedeSelect.val(null).trigger('change');
                    modalEncargadoSelect.val(null).trigger('change');
                });

                @this.on('abrir-modal-punto-edit', (data) => {
                    modalSedeSelect.val(data.sedeId).trigger('change');
                    modalEncargadoSelect.val(data.encargadoId).trigger('change');
                    modalPuntoDePago.show();
                });


                // --- Notificaciones y Confirmaciones ---
                @this.on('notificacion', (event) => {
                    Swal.fire({
                        title: event.titulo || '¡Hecho!',
                        text: event.mensaje,
                        icon: event.tipo,
                        confirmButtonText: 'Aceptar',
                        showCancelButton: false,
                    });
                });

                @this.on('confirmarEliminacion', (event) => {
                    Swal.fire({
                        title: event.titulo,
                        text: event.texto,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, ¡dar de baja!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.dispatch(event.evento, {
                                id: event.id
                            });
                        }
                    });
                });

            });
        </script>
    @endpush
</div>
