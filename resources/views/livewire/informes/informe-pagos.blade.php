<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card mb-4">


        {{-- Filter Tags Area --}}
        <div class="card-body border-bottom">
            <div class="row g-3 align-items-center">
                {{-- Tags Area --}}
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

                {{-- Buttons Area --}}
                <div class="col-12 col-md-5 d-flex justify-content-md-end justify-content-start gap-2 order-1 order-md-2">
                     <button class="btn btn-outline-secondary waves-effect" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltrosPagos" aria-controls="offcanvasFiltrosPagos">
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
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltrosPagos" aria-labelledby="offcanvasFiltrosPagosLabel" wire:ignore.self>
        <div class="offcanvas-header">
            <h5 id="offcanvasFiltrosPagosLabel" class="offcanvas-title">Filtros Avanzados</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0">
            <div class="row g-3">
                 {{-- Filtro Grupo --}}
                <div class="col-12">
                    <label class="form-label">Grupo</label>
                    @livewire('Grupos.grupos-para-busqueda', [
                        'id' => 'buscador-grupo-informe-pagos',
                        'class' => 'w-100',
                        'placeholder' => 'Buscar grupo...',
                        'conDadosDeBaja' => 'si',
                        'multiple' => false,
                        'grupoSeleccionadoId' => $grupo_id,
                    ], key('buscador-grupo-inst-pagos-' . $resetToken))
                </div>

                {{-- Filtro Usuario --}}
                <div class="col-12">
                    <label class="form-label">Usuario</label>
                    @livewire('Usuarios.usuarios-para-busqueda', [
                        'id' => 'buscador-usuario-informe-pagos',
                        'tipoBuscador' => 'unico',
                        'conDadosDeBaja' => 'si',
                        'class' => 'w-100',
                        'placeholder' => 'Buscar usuario...',
                        'queUsuariosCargar' => 'todos',
                        'modulo' => 'reportes',
                        'usuarioSeleccionadoId' => $user_id,
                    ], key('buscador-users-inst-pagos-' . $resetToken))
                </div>

                {{-- Filtro Actividad (Select2) --}}
                <div class="col-12" wire:ignore>
                    <label class="form-label">Actividad</label>
                    <select id="select2-actividad-pagos" class="select2 form-select" data-placeholder="Seleccione una actividad...">
                        <option value="">Todas las actividades</option>
                        @foreach($actividades as $act)
                            <option value="{{ $act->id }}">{{ $act->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro Sucursal (Select2 Multiple) --}}
                <div class="col-12" wire:ignore>
                    <label class="form-label">Sucursal</label>
                    <select id="select2-destinatario-pagos" class="select2 form-select" multiple data-placeholder="Seleccione sucursales...">
                        @foreach($destinatarios as $dest)
                            <option value="{{ $dest->id }}">{{ $dest->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Fechas --}}
                <div class="col-12">
                    <label class="form-label">Desde</label>
                    <input type="date" wire:model="fecha_inicio" class="form-control fecha-picker">
                </div>
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

                {{-- Estado Pago --}}
                <div class="col-12">
                    <label class="form-label">Estado Pago</label>
                    <select wire:model="estado_pago_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($estados_pago as $ep)
                            <option value="{{ $ep->id }}">{{ $ep->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo Pago --}}
                <div class="col-12">
                    <label class="form-label">Tipo Pago</label>
                    <select wire:model="tipo_pago_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($tipos_pago as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                     <button type="button" class="btn btn-primary text-start rounded-pill waves-effect" wire:click="aplicarFiltros" wire:loading.attr="disabled">Aplicar Filtros</button>
                </div>
            </div>
        </div>
    </div>




    {{-- Resumen Cards --}}
    @if(count($totales) > 0)
    <div class="row mb-4">
        @foreach($totales as $index => $stats)
        <div class="col-12  mb-4">
             <div class="text-center">
                <div class="row g-3 align-items-center h-100">

                    <div class="col-12">
                      <h1 class="fw-semibold mb-0 text-dark">${{ number_format($stats['total'], 0, ',', '.') }}</h1>
                        <h6 class="mb-0 fw-regular text-muted"> Total: {{ $stats['moneda'] }}</h6>
                        <h5 class="text-muted mb-0 small">({{ $stats['count'] }} Pagos)</h5>

                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Grid de Resultados (Estilo Cards) --}}
    <div class="row equal-height-row g-4 mb-4">
        @forelse($pagos as $pago)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border shadow-none">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="mb-0 fw-bold text-dark lh-sm" title="{{ $pago->compra->user->nombre(3) ?? 'N/A' }}">
                                {{ $pago->compra->user->nombre(3) ?? 'Usuario Desconocido' }}
                            </h5>
                            @if($pago->estadoPago)
                                <span class="badge rounded-pill text-white fs-9 " style="background-color: {{ $pago->estadoPago->color ?? '#6c757d' }};">
                                    {{ $pago->estadoPago->nombre }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-secondary">Sin Estado</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-3">Identificación: {{ $pago->compra->user->identificacion ?? 'N/A' }}</p>

                        <div class="mb-3">
                             <h6 class="fw-semibold mb-2 text-dark text-truncate" title="{{ $pago->compra->actividad ? $pago->compra->actividad->nombre : 'Actividad Desconocida' }}">
                                {{ $pago->compra->actividad ? $pago->compra->actividad->nombre : 'Actividad Desconocida' }}
                            </h6>
                            <div style="background-color: #E8F3FF;" class=" rounded p-2 d-flex justify-content-between align-items-center rounded-pill">
                                 <small class="text-dark fw-semibold">Pago interno #{{ $pago->id }}</small>
                                 <small class="text-dark fw-semibold">Compra #{{ $pago->compra_id }}</small>
                            </div>
                        </div>

                        <div class="row g-2">
                             <div class="col-6">
                                <small class="d-block text-dark">Fecha</small>
                                <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($pago->fecha)->format('Y-m-d') }}</span>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="d-block text-dark">ID de pago</small>
                                <span class="fw-bold text-dark">{{ $pago->referencia_pago ?? $pago->id }}</span>
                            </div>
                            <hr>
                            <div class="col-6">
                                <small class="d-block text-dark">Valor</small>
                                <span class="fw-bold text-dark">${{ number_format($pago->valor, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-dark">Medio de pago</small>
                                <span class="fw-semibold text-dark" title="{{ $pago->tipoPago->nombre ?? '' }}">
                                    {{ Str::limit($pago->tipoPago->nombre ?? 'N/A', 20) }}
                                </span>
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
                             <span class="text-muted fw-semibold">No se encontraron pagos con los filtros seleccionados.</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="mb-4">
         {{ $pagos->links() }}
    </div>
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {

            // --- Select2 Initialization ---
            function initSelectsPagos() {
                // Actividad
                var selectActividadPagos = $('#select2-actividad-pagos');
                if (selectActividadPagos.length) {
                    selectActividadPagos.select2({
                        placeholder: "Seleccione una actividad",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#offcanvasFiltrosPagos')
                    });
                    selectActividadPagos.on('change', function (e) {
                        var data = $(this).val();
                        @this.set('actividad_id', data, false);
                    });
                }

                // Sucursal
                var selectDestinatarioPagos = $('#select2-destinatario-pagos');
                if (selectDestinatarioPagos.length) {
                    selectDestinatarioPagos.select2({
                        placeholder: "Seleccione sucursales...",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#offcanvasFiltrosPagos')
                    });
                    selectDestinatarioPagos.on('change', function (e) {
                        var data = $(this).val();
                        @this.set('destinatario_ids', data, false);
                    });
                }
            }

            initSelectsPagos();

            // --- Listeners ---
            Livewire.on('limpiarFiltroActividad', () => {
                 $('#select2-actividad-pagos').val(null).trigger('change');
            });
            Livewire.on('limpiarFiltroSucursales', () => {
                 $('#select2-destinatario-pagos').val(null).trigger('change');
            });

            Livewire.on('close-offcanvas', () => {
                var el = document.getElementById('offcanvasFiltrosPagos');
                if (el) {
                    var offcanvas = bootstrap.Offcanvas.getInstance(el);
                    if (offcanvas) offcanvas.hide();
                }
            });

            // --- Charts ---
            function renderChartsPagos() {
                const charts = document.querySelectorAll('.chart-donut-pagos');

                if (charts.length === 0) return;

                charts.forEach(el => {
                    if(el.getAttribute('data-rendered') === 'true') {
                        el.innerHTML = '';
                    }

                    const count = el.dataset.count;
                    // const index = el.dataset.index;

                    var options = {
                        series: [100],
                        chart: {
                            height: 100,
                            width: 100,
                            type: 'donut',
                            sparkline: { enabled: true },
                            animations: { enabled: false }
                        },
                        colors: ['#7367F0'],
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        name: { show: false },
                                        value: {
                                            show: true,
                                            fontSize: '14px',
                                            fontWeight: '600',
                                            offsetY: 5,
                                            color: '#5d596c',
                                            formatter: function (val) {
                                                return count;
                                            }
                                        },
                                        total: {
                                            show: true,
                                            showAlways: true,
                                            label: 'Total',
                                            fontSize: '12px',
                                            formatter: function (w) {
                                                return count;
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        stroke: { width: 0 },
                        tooltip: { enabled: false }
                    };

                    var chart = new ApexCharts(el, options);
                    chart.render();
                    el.setAttribute('data-rendered', 'true');
                });
            }

            renderChartsPagos();

            Livewire.hook('morph.updated', ({ el, component }) => {
                 setTimeout(() => {
                    if (document.querySelectorAll('.chart-donut-pagos').length > 0) {
                        renderChartsPagos();
                    }
                }, 100);
            });
        });
    </script>
    @endpush
</div>
