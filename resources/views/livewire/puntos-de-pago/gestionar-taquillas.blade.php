<div>
    {{-- ================================================================== --}}
    {{-- 1. TÍTULO Y SECCIÓN DE CONTADORES (Sin cambios)                  --}}
    {{-- ================================================================== --}}
    <h4 class="mb-1 fw-semibold text-primary"> Gestionar cajas</h4>

    <div class="row pt-5">
        <div class="col-lg-3 col-6">
            <a href="{{ route('taquillas.gestionar', ['tipo' => 'todos']) }}">
                <div
                    class="h-100 card border rounded-3 shadow-sm {{ $tipo == 'todos' ? 'border-primary border-2' : '' }}">
                    <div class="card-body d-flex flex-row p-3">
                        <div class="card-title mb-0 lh-sm">
                            <p class="text-black mb-0" style="font-size: .8125rem">Todas</p>
                            <h5 class="mb-0 me-2">{{ $contadorTodos }}</h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-6">
            <a href="{{ route('taquillas.gestionar', ['tipo' => 'dados-de-baja']) }}">
                <div
                    class="h-100 card border rounded-3 shadow-sm {{ $tipo == 'dados-de-baja' ? 'border-primary border-2' : '' }}">
                    <div class="card-body d-flex flex-row p-3">
                        <div class="card-title mb-0 lh-sm">
                            <p class="text-black mb-0" style="font-size: .8125rem">Dadas de baja</p>
                            <h5 class="mb-0 me-2">{{ $contadorBaja }}</h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>



    {{-- ================================================================== --}}
    {{-- 2. SECCIÓN DE BOTONES Y FILTROS (Sin cambios)                    --}}
    {{-- ================================================================== --}}
    <div class="row mt-md-n9 ">
        <div class="col-12 mb-3 d-flex justify-content-end">
            <button wire:click="abrirModalCrearCaja" type="button"
                class="btn btn-primary waves-effect   w-md-auto me-2">
                <i class="ti ti-plus me-1"></i>
                <span class="fw-semibold">Crear caja</span>
            </button>

            <button wire:click="exportarExcel" type="button"
                class="btn btn-outline-secondary waves-effect  w-md-auto me-2">
                <i class="ti ti-file-spreadsheet me-1"></i>
                <span class="fw-semibold">Exportar</span>
            </button>

            <button class="btn btn-outline-secondary  w-md-auto" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasFiltrosTaquillas" aria-controls="offcanvasFiltrosTaquillas">
                <i class="ti ti-filter me-1"></i> Filtros
            </button>
        </div>
    </div>

    <hr>
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
                            class="btn btn-xs rounded-pill btn-outline-secondary remove-tag-taquilla ps-2 pe-1 mt-1"
                            data-field="{{ $tag->field }}" data-value="{{ $tag->value }}">
                            <span class="align-middle">{{ $tag->label }} <i class="ti ti-x ti-xs"
                                    style="margin-bottom: 2px;"></i></span>
                        </button>
                    @endforeach
                    @if (isset($banderaFiltros) && $banderaFiltros == 1)
                        <a href="{{ route('taquillas.gestionar', ['tipo' => $tipo]) }}"
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
    {{-- 3. LISTA DE CAJAS (¡DISEÑO DE TARJETA ACTUALIZADO!)               --}}
    {{-- ================================================================== --}}
    <div class="row g-4 mt-1 equal-height-row"> {{-- Añadido equal-height-row --}}
        {{-- Indicador de Carga --}}
        <div wire:loading.flex class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        @forelse($cajas as $caja)
            <div class="col equal-height-col col-12 col-md-6 col-lg-4" wire:loading.remove
                wire:key="caja-{{ $caja->id }}">
                <div class="card rounded-3 shadow h-100"> {{-- Añadido h-100 --}}

                    {{-- 1. Encabezado (Nombre, Estado, Acciones) --}}
                    <div class="card-header border-bottom d-flex px-4 pt-3 pb-1"
                        style="background-color:#F9F9F9!important">
                        <div class="flex-fill row">
                            <div class=" d-flex justify-content-between align-items-center">
                                <h5 class="fw-semibold ms-1 text-black m-0">
                                    {{ $caja->nombre }}
                                </h5>
                                <div>
                                    @if ($caja->trashed())
                                        <span class="badge bg-label-danger mt-1">Dada de baja</span>
                                    @else
                                        {{-- ¡TU ESTILO DE BADGE ACTUALIZADO! --}}
                                        <span
                                            class="{{ $caja->estado ? 'badge bg-label-primary' : 'badge bg-label-secondary' }} rounded-pill">
                                            {{ $caja->estado ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="">
                            <div class="ms-auto">
                                <div class="dropdown zindex-2 p-1  float-end">
                                    {{-- Botón de dropdown (estilo "consejeros") --}}
                                    <button type="button"
                                        class="btn btn-sm rounded-pill btn-icon border rounded-circle "
                                        data-bs-toggle="dropdown" aria-expanded="false"><i
                                            class="ti ti-dots-vertical "></i> </button>

                                    {{-- Acciones (son las de Livewire de tu archivo original) --}}
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                wire:click="abrirModalEditarCaja({{ $caja->id }})">
                                                Editar caja
                                            </a>
                                        </li>
                                        @if (!$caja->trashed())
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);"
                                                    wire:click="toggleEstado({{ $caja->id }})"
                                                    wire:loading.attr="disabled">
                                                    <span wire:loading.remove
                                                        wire:target="toggleEstado({{ $caja->id }})">
                                                        {{ $caja->estado ? 'Inactivar' : 'Activar' }}
                                                    </span>
                                                    <span wire:loading wire:target="toggleEstado({{ $caja->id }})">
                                                        Cambiando...
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-dark" href="javascript:void(0);"
                                                    wire:click="confirmarEliminacionCaja({{ $caja->id }})">
                                                    Eliminar
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item text-warning" href="javascript:void(0);"
                                                wire:click="reiniciarContadorDinero({{ $caja->id }})"
                                                onclick="confirm('¿Está seguro de reiniciar el contador de dinero acumulado?') || event.stopImmediatePropagation()">
                                                Reiniciar contador
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Cuerpo Principal (Detalle Primario: Punto de Pago) --}}
                    <div class="card-body">
                        <div class="row mt-2">
                            <div class="col-12 d-flex flex-column">
                                <small class="text-black">Punto de pago:</small>
                                <small class="fw-semibold text-black ">{{ $caja->puntoDePago->nombre }}</small>
                            </div>
                        </div>

                        {{-- 3. Cuerpo Colapsable (Detalles Secundarios: Sede y Cajero) --}}
                        <div class="collapse" id="cardBodyCaja{{ $caja->id }}">
                            <div class="col-12">
                                <hr class="my-3 border-1">
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 d-flex flex-column mt-1">
                                    <small class="text-black">Sede:</small>
                                    <small
                                        class="fw-semibold text-black ">{{ $caja->puntoDePago->sede->nombre }}</small>
                                </div>

                                <div class="col-12 col-md-6 d-flex flex-column mt-2">
                                    <small class="text-black">Cajero asignado:</small>
                                    {{-- Usamos la relación 'usuario' que ya estabas cargando --}}
                                    <small class="fw-semibold text-black ">
                                        @if ($caja->usuario)
                                            {{ $caja->usuario->primer_nombre }} {{ $caja->usuario->primer_apellido }}
                                        @else
                                            No asignado
                                        @endif
                                    </small>
                                </div>
                                <div class="col-12 col-md-6 d-flex flex-column mt-2">
                                    <small class="text-black"> Apertura transaccional</small>
                                    {{-- Usamos la relación 'usuario' que ya estabas cargando --}}
                                    <small class="fw-semibold text-black ">
                                        @if ($caja->hora_apertura)
                                            {{ $caja->hora_apertura }}
                                        @else
                                            No asignado
                                        @endif
                                    </small>
                                </div>
                                <div class="col-12 col-md-6 d-flex flex-column mt-2">
                                    <small class="text-black">Cierre transaccional</small>
                                    {{-- Usamos la relación 'usuario' que ya estabas cargando --}}
                                    <small class="fw-semibold text-black ">
                                        @if ($caja->hora_cierre)
                                            {{ $caja->hora_cierre }}
                                        @else
                                            No asignado
                                        @endif
                                    </small>
                                </div>
                                <div class="col-12 mt-3">
                                    <small class="text-black">Dinero acumulado / Límite</small>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-semibold text-primary">
                                            ${{ number_format($caja->dinero_acumulado, 0) }}
                                        </small>
                                        <small class="text-muted">
                                            /
                                            {{ $caja->limite_dinero_acumulado ? '$' . number_format($caja->limite_dinero_acumulado, 0) : 'Sin límite' }}
                                        </small>
                                    </div>
                                    @if ($caja->limite_dinero_acumulado > 0)
                                        @php
                                            $porcentaje = min(
                                                100,
                                                ($caja->dinero_acumulado / $caja->limite_dinero_acumulado) * 100,
                                            );
                                            $colorBarra =
                                                $porcentaje >= 90
                                                    ? 'bg-danger'
                                                    : ($porcentaje >= 75
                                                        ? 'bg-warning'
                                                        : 'bg-success');
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar {{ $colorBarra }}" role="progressbar"
                                                style="width: {{ $porcentaje }}%"
                                                aria-valuenow="{{ $porcentaje }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Footer con Botón para Colapsar --}}
                    <div class="card-footer border-top p-1">
                        <div class="d-flex justify-content-center">
                            <button type="button"
                                class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
                                data-bs-toggle="collapse" data-bs-target="#cardBodyCaja{{ $caja->id }}">
                                <span class="ti ti-plus"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12" wire:loading.remove>
                <div class="alert alert-info text-center">
                    No se encontraron cajas que coincidan con los filtros.
                </div>
            </div>
        @endforelse
    </div>

    {{-- ================================================================== --}}
    {{-- 4. PAGINACIÓN (Sin cambios)                                      --}}
    {{-- ================================================================== --}}
    <div class="row my-3 mt-5">
        @if ($cajas)
            <p> {{ $cajas->lastItem() }} <b>de</b> {{ $cajas->total() }} <b>cajas - Página</b>
                {{ $cajas->currentPage() }} </p>
            {!! $cajas->appends($filtrosActuales)->links() !!}
        @endif
    </div>

    {{-- ================================================================== --}}
    {{-- 5. MODAL PARA CRUD DE CAJAS (Sin cambios)                        --}}
    {{-- ================================================================== --}}
    <div class="modal fade" id="modalCaja" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $esEdicion ? 'Editar' : 'Crear' }} caja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="cerrarModalCaja"></button>
                </div>

                <form wire:submit="guardarCaja">
                    <div class="modal-body">

                        {{-- Campo Nombre (sin cambios) --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="nombreCaja" class="form-label">Nombre</label>
                                <input type="text" id="nombreCaja" class="form-control"
                                    placeholder="Ej: Taquilla 1" wire:model="nombreCaja">
                                @error('nombreCaja')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Campo Punto de Pago (sin cambios) --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="puntoDePagoId" class="form-label">Punto de pago</label>
                                <div wire:ignore>
                                    <select id="puntoDePagoId" class="form-select" wire:model="puntoDePagoId">
                                        <option value="">Seleccione un punto...</option>
                                        @foreach ($puntosDePago as $punto)
                                            <option value="{{ $punto->id }}">{{ $punto->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('puntoDePagoId')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Campo Cajero Asignado (sin cambios, pero $usuarios ahora está filtrado) --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="cajeroId" class="form-label">Cajero asignado (Opcional)</label>
                                <div wire:ignore>
                                    <select id="cajeroId" class="form-select" wire:model="cajeroId">
                                        <option value="">Seleccione un cajero...</option>
                                        {{-- ¡Esta lista $usuarios ahora solo contiene 'es_cajero' = true! --}}
                                        @foreach ($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('cajeroId')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- ¡NUEVOS CAMPOS DE HORA! --}}
                        {{-- Usamos g-2 para un espaciado (gutter) más pequeño entre columnas --}}
                        <div class="row g-2">
                            <div class="col-6 mb-3">
                                <label for="horaApertura" class="form-label"> Apertura transaccional
                                    (Opcional)</label>
                                {{-- El input type="time" es ideal para el formato H:i --}}
                                <input type="time" id="horaApertura" class="form-control"
                                    wire:model="horaApertura">
                                @error('horaApertura')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label for="horaCierre" class="form-label">Cierre transaccional (Opcional)</label>
                                <input type="time" id="horaCierre" class="form-control" wire:model="horaCierre">
                                @error('horaCierre')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Campo Límite de Dinero --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="limiteDinero" class="form-label">Límite de dinero acumulado
                                    (Opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="limiteDinero" class="form-control"
                                        placeholder="Ej: 5000000" wire:model="limiteDinero">
                                </div>
                                <div class="form-text">Deje en blanco o 0 para no establecer límite.</div>
                                @error('limiteDinero')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- ¡BLOQUE DE SWITCHES MODIFICADO! --}}
                        <div class="row">
                            <div class="col-6 mb-0">
                                {{-- Switch de Estado (existente) --}}
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="estadoCaja"
                                        wire:model="estadoCaja">
                                    <label class="form-check-label" for="estadoCaja">Caja activa</label>
                                </div>
                            </div>
                            <div class="col-6 mb-0">
                                {{-- ¡NUEVO SWITCH! --}}
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="permiteModificar"
                                        wire:model="permiteModificar">
                                    <label class="form-check-label" for="permiteModificar">Permite modificar
                                        registros</label>
                                </div>
                                @error('permiteModificar')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        {{-- ... (Botones de footer sin cambios) ... --}}
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal"
                            wire:click="cerrarModalCaja">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <span wire:loading.remove wire:target="guardarCaja">
                                {{ $esEdicion ? 'Actualizar' : 'Guardar' }}
                            </span>
                            <span wire:loading wire:target="guardarCaja" class="spinner-border spinner-border-sm"
                                role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ================================================================== --}}
    {{-- 6. OFFCANVAS PARA FILTROS (Sin cambios)                          --}}
    {{-- ================================================================== --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltrosTaquillas"
        aria-labelledby="offcanvasFiltrosTaquillasLabel">
        <div class="offcanvas-header">
            <h4 id="offcanvasFiltrosTaquillasLabel" class="offcanvas-title text-primary fw-semibold">Filtrar cajas
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 pt-0">
            <form id="formFiltrosTaquillas" method="GET"
                action="{{ route('taquillas.gestionar', ['tipo' => $tipo]) }}">

                <div class="mb-3">
                    <label for="filtro_busqueda_general" class="form-label">Buscar por nombre, Punto o cajero</label>
                    <input type="text" class="form-control" id="filtro_busqueda_general" name="buscar"
                        value="{{ $filtrosActuales['buscar'] ?? '' }}" placeholder="Escribe aquí...">
                </div>

                {{-- El script de Select2 para este ya existe --}}
                <div class="mb-3" wire:ignore>
                    <label for="filtroSedeSelectOffcanvas" class="form-label">Sede</label>
                    <select id="filtroSedeSelectOffcanvas" name="filtroSede[]" class="form-select" multiple>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}"
                                {{ in_array($sede->id, $filtrosActuales['filtroSede']) ? 'selected' : '' }}>
                                {{ $sede->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- El script de Select2 para este ya existe --}}
                <div class="mb-3" wire:ignore>
                    <label for="filtroPuntoDePagoSelectOffcanvas" class="form-label">Punto de pago</label>
                    <select id="filtroPuntoDePagoSelectOffcanvas" name="filtroPuntoDePago[]" class="form-select"
                        multiple>
                        @foreach ($puntosDePago as $punto)
                            <option value="{{ $punto->id }}"
                                {{ in_array($punto->id, $filtrosActuales['filtroPuntoDePago']) ? 'selected' : '' }}>
                                {{ $punto->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-start pt-3">
                    <button type="submit" class="btn btn-primary rounded-pill me-2">Aplicar filtros</button>
                    <a href="{{ route('taquillas.gestionar', ['tipo' => $tipo]) }}"
                        class="btn btn-outline-secondary rounded-pill">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- 7. SCRIPTS (¡MODIFICADO!)                                        --}}
    {{-- ================================================================== --}}
    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {

                // --- 1. CONFIGURACIÓN DE ELEMENTOS (SIN CAMBIOS) ---
                const modalEl = document.getElementById('modalCaja');
                const modalCaja = new bootstrap.Modal(modalEl);

                // Selects del Modal
                const modalPuntoSelect = $('#puntoDePagoId');
                const modalCajeroSelect = $('#cajeroId');

                // Selects del Offcanvas de Filtros
                const filtroSedeOffcanvas = $('#filtroSedeSelectOffcanvas');
                const filtroPuntoOffcanvas = $('#filtroPuntoDePagoSelectOffcanvas');

                // --- 2. INICIALIZACIÓN DE SELECT2 (SIN CAMBIOS) ---

                // Filtros
                filtroSedeOffcanvas.select2({
                    dropdownParent: $('#offcanvasFiltrosTaquillas')
                });
                filtroPuntoOffcanvas.select2({
                    dropdownParent: $('#offcanvasFiltrosTaquillas')
                });

                // Modal (Select2 se inicializa con el modal como padre)
                modalPuntoSelect.select2({
                    placeholder: 'Seleccione un punto...',
                    allowClear: true,
                    dropdownParent: $(modalEl)
                });
                modalCajeroSelect.select2({
                    placeholder: 'Seleccione un cajero...',
                    allowClear: true,
                    dropdownParent: $(modalEl)
                });

                // --- 3. EVENTOS DE CAMBIO (SIN CAMBIOS) ---
                // (Enviar datos de Select2 a Livewire cuando cambian)
                modalPuntoSelect.on('change', function(e) {
                    @this.set('puntoDePagoId', e.target.value);
                });
                modalCajeroSelect.on('change', function(e) {
                    @this.set('cajeroId', e.target.value);
                });

                // --- 4. LÓGICA PARA QUITAR TAGS (SIN CAMBIOS) ---
                document.querySelectorAll('.remove-tag-taquilla').forEach(button => {
                    button.addEventListener('click', function() {
                        /* ... (lógica de quitar tags) ... */
                    });
                });

                // ===================================================================
                // ¡INICIO DE LA CORRECCIÓN!
                // ===================================================================

                // --- 5. CONTROL DE APERTURA/CIERRE DE MODALES (MODIFICADO) ---

                // ¡ELIMINADO!
                // Ya no necesitamos un listener separado para 'abrir-modal-caja'.
                // @this.on('abrir-modal-caja', () => { ... });

                // ¡UNIFICADO!
                // Este listener ahora maneja TANTO 'Crear' como 'Editar'.
                @this.on('abrirModalEditarCaja', (data) => {
                    // 'data' contendrá los IDs o será nulo (si es 'Crear')

                    // 1. Establecemos los valores de Select2
                    //    Si 'data.puntoDePagoId' es nulo, .val(null) lo limpiará.
                    modalPuntoSelect.val(data.puntoDePagoId).trigger('change');
                    modalCajeroSelect.val(data.cajeroId).trigger('change');

                    // 2. Mostramos el modal de Bootstrap
                    modalCaja.show();
                });

                // ¡MODIFICADO!
                // Usamos el evento de Bootstrap 'hidden.bs.modal' para resetear.
                // Esto es MÁS SEGURO porque se ejecuta DESPUÉS de que el modal se oculta.
                modalEl.addEventListener('hidden.bs.modal', (event) => {
                    // Reseteamos los Select2
                    modalPuntoSelect.val(null).trigger('change');
                    modalCajeroSelect.val(null).trigger('change');

                    // Opcional: Llamar al método de reseteo de Livewire aquí
                    // si aún no lo haces en 'cerrarModalCaja'.
                    @this.call('resetearFormularioCaja');
                });

                // ¡ELIMINADO!
                // El listener 'cerrar-modal-caja' ya no necesita resetear los Select2.
                @this.on('cerrar-modal-caja', () => {
                    modalCaja.hide();
                    // (Las líneas de .val(null).trigger('change') se quitaron de aquí)
                });

                // ===================================================================
                // ¡FIN DE LA CORRECCIÓN!
                // ===================================================================


                // --- 6. NOTIFICACIONES (SIN CAMBIOS) ---
                @this.on('notificacion', (event) => {
                    /* ... */
                });
                @this.on('confirmarEliminacion', (event) => {
                    /* ... */
                });

            });
        </script>
    @endpush
</div>
