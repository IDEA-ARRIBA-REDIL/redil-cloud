<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card mb-4">


        {{-- Filter Tags Area --}}
        <div class="card-body border-bottom">
            <div class="row g-3 align-items-center">
                {{-- Tags Area (Left on desktop, stacked on mobile) --}}
                <div class="col-12 col-md-7 order-2 order-md-1">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="text-black me-2">Filtros activos:</span>
                        @if(count($this->tags) > 0)
                            @foreach($this->tags as $tag)
                                <span class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1">
                                    {{ $tag['label'] }}
                                    <i class="ti ti-x ms-1 cursor-pointer" wire:click="limpiarFiltro('{{ $tag['field'] }}')"></i>
                                </span>
                            @endforeach
                            <button class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1" wire:click="limpiarFiltro('todos')">
                                Limpiar todos
                            </button>
                        @else
                            <span class="text-muted fst-italic">Ningún filtro aplicado</span>
                        @endif
                    </div>
                </div>

                {{-- Buttons Area (Right on desktop, stacked on mobile) --}}
                <div class="col-12 col-md-5 d-flex justify-content-md-end justify-content-start gap-2 order-1 order-md-2">
                    <button class="btn btn-outline-secondary waves-effect" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltros" aria-controls="offcanvasFiltros">
                        <span class="d-none d-md-block fw-semibold">Filtros</span><i class="ti ti-filter ms-1"></i>
                    </button>
                    <button wire:click="exportarExcel" class="btn btn-outline-secondary waves-effect waves-light">
                        <span class="d-none d-md-block fw-semibold">Exportar Excel</span><i class="ti ti-file-spreadsheet ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Offcanvas Filters --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltros" aria-labelledby="offcanvasFiltrosLabel" wire:ignore.self>
        <div class="offcanvas-header">
            <h4 id="offcanvasFiltrosLabel" class="offcanvas-title fw-semibold text-primary">Filtros avanzados</h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0">
            <div class="row g-3">
                {{-- Filtro Grupo --}}
                <div class="col-12">
                    <label class="form-label">Grupo</label>
                    @livewire('Grupos.grupos-para-busqueda', [
                        'id' => 'buscador-grupo-informe',
                        'class' => 'w-100',
                        'placeholder' => 'Buscar grupo...',
                        'conDadosDeBaja' => 'si',
                        'multiple' => false,
                        'grupoSeleccionadoId' => $grupo_id,
                    ], key('buscador-grupo-inst-' . $resetToken))
                </div>

                {{-- Filtro Asistente --}}
                <div class="col-12">
                    <label class="form-label">Asistente</label>
                    @livewire('Usuarios.usuarios-para-busqueda', [
                        'id' => 'buscador-usuario-informe',
                        'tipoBuscador' => 'unico',
                        'conDadosDeBaja' => 'si',
                        'class' => 'w-100',
                        'placeholder' => 'Buscar asistente...',
                        'queUsuariosCargar' => 'todos',
                        'modulo' => 'reportes',
                        'usuarioSeleccionadoId' => $user_id,
                    ], key('buscador-users-inst-' . $resetToken))
                </div>

                {{-- Filtro Actividad (Select2) --}}
                <div class="col-12" wire:ignore>
                    <label class="form-label">Actividad</label>
                    <select id="select2-actividad" class="select2 form-select" data-placeholder="Seleccione una actividad...">
                        <option value="">Todas las actividades</option>
                        @foreach($actividades as $act)
                            <option value="{{ $act->id }}">{{ $act->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro Sucursal (Select2 Multiple) --}}
                <div class="col-12" wire:ignore>
                    <label class="form-label">Sucursal</label>
                    <select id="select2-destinatario" class="select2 form-select" multiple data-placeholder="Seleccione sucursales...">
                        @foreach($destinatarios as $dest)
                            <option value="{{ $dest->id }}">{{ $dest->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Fecha Inicio --}}
                <div class="col-12">
                    <label class="form-label">Desde</label>
                    <input type="date" wire:model="fecha_inicio" class="form-control fecha-picker">
                </div>

                {{-- Fecha Fin --}}
                <div class="col-12">
                    <label class="form-label">Hasta</label>
                    <input type="date" wire:model="fecha_fin" class="form-control fecha-picker">
                </div>

                {{-- Moneda --}}
                <div class="col-12">
                    <label class="form-label">Moneda</label>
                    <select wire:model="moneda_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($monedas as $m)
                            <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Estado --}}
                <div class="col-12">
                    <label class="form-label">Estado</label>
                    <select wire:model="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Pendiente</option>
                        <option value="2">Pagada</option>
                        <option value="3">Anulada</option>
                        <option value="4">Abonada</option>
                    </select>
                </div>

                <div class="col-12">
                     <button type="button" class="btn btn-primary text-start rounded-pill  waves-effect" wire:click="aplicarFiltros" wire:loading.attr="disabled">Aplicar Filtros</button>

                </div>
            </div>
        </div>
    </div>




    {{-- Resumen Cards --}}
    @if(count($totales) > 0)
    <div class="row mb-4">
        @foreach($totales as $index => $stats)
        <div class="col-12 mb-4">
             <div class="text-center">
                <div class="row g-3 align-items-center h-100">
                    <div class="col-12">
                      <h1 class="fw-semibold mb-0 text-dark">${{ number_format($stats['total'], 0, ',', '.') }}</h1>
                        <h6 class="mb-0 fw-regular text-muted"> Total: {{ $stats['moneda'] }}</h6>
                        <h5 class="text-muted mb-0 small">({{ $stats['count'] }} Transacciones)</h5>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif



    {{-- Tabla de Resultados --}}
    {{-- Grid de Resultados (Estilo Cards) --}}
    <div class="row equal-height-row g-4 mb-4">
        @forelse($compras as $compra)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border shadow-none">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="mb-0 fw-bold text-dark lh-sm" title="{{ $compra->nombre_completo_comprador }}">
                                {{ $compra->nombre_completo_comprador }}
                            </h5>

                            @if($compra->estadoPago)
                                <span class="badge rounded-pill text-white fs-9" style="background-color: {{ $compra->estadoPago->color }};">
                                    {{ $compra->estadoPago->nombre }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-secondary text-white">Sin Estado</span>
                            @endif

                        </div>
  <p class="text-muted small mb-3">Identificación: {{ $compra->identificacion_comprador }}</p>


                        <div class="mb-3 mt-3">
                             <h6 class="fw-semibold mb-2 text-dark text-truncate" title="{{ $compra->actividad ? $compra->actividad->nombre : 'Actividad Desconocida' }}">
                                {{ $compra->actividad ? $compra->actividad->nombre : 'Actividad Desconocida' }}
                            </h6>
                            <div style="background-color: #E8F3FF;" class=" rounded p-2 d-flex justify-content-between align-items-center rounded-pill">
                                 <small class="text-dark fw-regular">Compra interna: #{{ $compra->id }}</small>

                            </div>
                        </div>

                        <div class="row g-2">
                             <div class="col-6">
                                <small class="d-block text-dark">Fecha</small>
                                <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($compra->fecha)->format('Y-m-d') }}</span>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="d-block text-dark">ID de pago</small>
                                <span class="fw-bold text-dark">{{ $compra->id }}</span>
                            </div>
                            <hr>
                            <div class="col-6">
                                <small class="d-block text-dark">Valor</small>
                                <span class="fw-bold text-dark">${{ number_format($compra->valor, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-dark">Medio de pago</small>
                                <span class="fw-semibold text-dark" title="{{ $compra->metodoPago->nombre ?? '' }}">
                                    {{ Str::limit($compra->metodoPago->nombre ?? 'N/A', 20) }}
                                </span>
                            </div>
                        </div>

                        {{-- Progreso de Pago --}}
                        @php
                            $permiteAbonos = $compra->actividad && $compra->actividad->tipo && $compra->actividad->tipo->permite_abonos;
                            $pagosActivos = $compra->pagos;
                            $totalPagado = $pagosActivos->sum('valor');
                            $porcentajePagado = $compra->valor > 0 ? ($totalPagado / $compra->valor) * 100 : 0;
                            // Ensure percentage doesn't exceed 100 visually if overpaid?
                            $porcentajeVisual = min($porcentajePagado, 100);
                        @endphp

                        {{-- Always show progress for 'Abonos' or generally? The image shows progress. Let's show it if it allows abonos or if there is partial payment. --}}
                        <hr>
                         <div class="mt-3 text-start">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-black fw-bold">Progreso:</small>
                                <small class="fw-bold">{{ number_format($porcentajePagado, 0) }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $porcentajeVisual }}%; background-color: #00BAD1;" aria-valuenow="{{ $porcentajeVisual }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-dark fw-semibold">Pagado ${{ number_format($totalPagado, 0, ',', '.') }}</small>
                                <small class="text-dark fw-semibold">Pend: ${{ number_format(max(0, $compra->valor - $totalPagado), 0, ',', '.') }}</small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @empty
             <div class="col-12">
                <div class="card">
                    <div class="card-body text-center p-5">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="ti ti-search text-muted display-4 mb-3"></i>
                             <span class="text-muted fw-semibold">No se encontraron compras con los filtros seleccionados.</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="mb-4">
         {{ $compras->links() }}
    </div>

    @push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {

        // --- Select2 Initialization ---
        function initSelects() {
            // Actividad
            var selectActividad = $('#select2-actividad');
            if (selectActividad.length) {
                selectActividad.select2({
                    placeholder: "Seleccione una actividad",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#offcanvasFiltros')
                });

                selectActividad.on('change', function (e) {
                    var data = $(this).val();
                    @this.set('actividad_id', data, false);
                });
            }

            // Sucursal
            var selectDestinatario = $('#select2-destinatario');
            if (selectDestinatario.length) {
                selectDestinatario.select2({
                    placeholder: "Seleccione sucursales...",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#offcanvasFiltros')
                });

                selectDestinatario.on('change', function (e) {
                    var data = $(this).val();
                    @this.set('destinatario_ids', data, false);
                });
            }
        }

        initSelects();

        // --- Event Listeners ---
        Livewire.on('limpiarFiltroActividad', () => {
             $('#select2-actividad').val(null).trigger('change');
        });

        Livewire.on('limpiarFiltroSucursales', () => {
             $('#select2-destinatario').val(null).trigger('change');
        });

        Livewire.on('close-offcanvas', () => {
            var el = document.getElementById('offcanvasFiltros');
            if (el) {
                var offcanvas = bootstrap.Offcanvas.getInstance(el);
                if (offcanvas) offcanvas.hide();
            }
        });
    });
</script>
@endpush
</div>
