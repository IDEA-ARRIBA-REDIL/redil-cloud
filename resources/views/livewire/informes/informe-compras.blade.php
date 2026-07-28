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
            @php
                // 1. Categoría de la compra obtenida prioritariamente desde los pagos de la compra
                $categoriaTexto = '';
                $pagoConCat = $compra->pagos->first(fn($p) => !empty($p->actividadCategoria?->nombre));
                if ($pagoConCat) {
                    $categoriaTexto = $pagoConCat->actividadCategoria->nombre;
                }

                if (empty($categoriaTexto) && $compra->categorias && $compra->categorias->isNotEmpty()) {
                    $catCompra = $compra->categorias->first(fn($c) => !empty($c->actividadCategoria?->nombre));
                    if ($catCompra) {
                        $categoriaTexto = $catCompra->actividadCategoria->nombre;
                    }
                }

                if (empty($categoriaTexto) && $compra->inscripciones && $compra->inscripciones->isNotEmpty()) {
                    $insc = $compra->inscripciones->first(fn($i) => !empty($i->actividadCategoria?->nombre) || !empty($i->categoriaActividad?->nombre));
                    if ($insc) {
                        $categoriaTexto = $insc->actividadCategoria?->nombre ?? $insc->categoriaActividad?->nombre ?? '';
                    }
                }

                // 2. Filtrar únicamente pagos efectivos (excluir transacciones rechazadas/anuladas)
                $pagosValidos = $compra->pagos->filter(function ($pago) {
                    if (!$pago->estadoPago) return true;
                    // Excluir si el estado del pago está anulado o rechazado
                    return !$pago->estadoPago->estado_anulado_inscripcion;
                });

                $totalPagado = $pagosValidos->sum('valor');

                // Si la compra está pagada/finalizada y no tenía abonos parciales, asegurar consistencia
                if ($compra->estadoPago && $compra->estadoPago->estado_final_inscripcion && $totalPagado < $compra->valor) {
                    $totalPagado = $compra->valor;
                }

                $porcentajePagado = $compra->valor > 0 ? ($totalPagado / $compra->valor) * 100 : 0;
                $porcentajeVisual = min(100, max(0, $porcentajePagado));

                // 3. Medios de pago únicos registrados en la compra
                $mediosDePago = $compra->pagos->map(function ($pago) {
                    return $pago->tipoPago?->nombre;
                })->filter()->unique()->implode(', ');

                if (empty($mediosDePago)) {
                    $mediosDePago = 'N/A';
                }
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border shadow-none">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            {{-- Comprador & Estado --}}
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

                            {{-- Actividad & Categoría --}}
                            <div class="mb-3 mt-2">
                                <h6 class="fw-semibold mb-1 text-dark text-truncate" title="{{ $compra->actividad ? $compra->actividad->nombre : 'Actividad Desconocida' }}">
                                    <i class="ti ti-calendar me-1 text-primary"></i>{{ $compra->actividad ? $compra->actividad->nombre : 'Actividad Desconocida' }}
                                </h6>

                                @if(!empty($categoriaTexto))
                                    <div class="mb-2">
                                        <span class="badge bg-label-primary text-wrap text-start" title="{{ $categoriaTexto }}">
                                            <i class="ti ti-tag me-1"></i>Cat: {{ Str::limit($categoriaTexto, 35) }}
                                        </span>
                                    </div>
                                @endif

                                <div style="background-color: #E8F3FF;" class="rounded p-2 d-flex justify-content-between align-items-center rounded-pill px-3">
                                    <small class="text-dark fw-semibold">Compra interna: #{{ $compra->id }}</small>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($compra->fecha)->format('Y-m-d') }}</small>
                                </div>
                            </div>

                            {{-- Resumen de Valores --}}
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <small class="d-block text-muted">Valor Total</small>
                                    <span class="fw-bold text-dark fs-6">${{ number_format($compra->valor, 0, ',', '.') }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="d-block text-muted">Medio(s) de pago</small>
                                    <span class="fw-semibold text-dark small" title="{{ $mediosDePago }}">
                                        {{ Str::limit($mediosDePago, 22) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Desglose de Pagos Registrados (Acordeón que inicia cerrado) --}}
                            <div class="accordion accordion-flush mb-3" id="accordionPagos{{ $compra->id }}">
                                <div class="accordion-item border rounded">
                                    <h2 class="accordion-header" id="headingPagos{{ $compra->id }}">
                                        <button class="accordion-button collapsed py-2 px-3 text-dark fw-semibold small bg-light rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePagos{{ $compra->id }}" aria-expanded="false" aria-controls="collapsePagos{{ $compra->id }}">
                                            <i class="ti ti-credit-card me-1 text-primary"></i> Pagos asociados ({{ $compra->pagos->count() }})
                                        </button>
                                    </h2>
                                    <div id="collapsePagos{{ $compra->id }}" class="accordion-collapse collapse" aria-labelledby="headingPagos{{ $compra->id }}" data-bs-parent="#accordionPagos{{ $compra->id }}">
                                        <div class="accordion-body p-2 bg-light rounded-bottom">
                                            @forelse($compra->pagos as $pago)
                                                <div class="d-flex align-items-center justify-content-between p-2 mb-1 bg-white rounded border-start border-3 border-primary shadow-xs">
                                                    <div>
                                                        <div class="fw-semibold text-dark small">
                                                            Pago #{{ $pago->id }}
                                                            @if($pago->tipoPago)
                                                                <span class="text-muted fw-normal">({{ $pago->tipoPago->nombre }})</span>
                                                            @endif
                                                        </div>
                                                        <small class="text-muted fs-tiny d-block">
                                                            {{ $pago->created_at ? \Carbon\Carbon::parse($pago->created_at)->format('d/m/Y H:i') : '' }}
                                                        </small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="fw-bold text-dark d-block small">${{ number_format($pago->valor, 0, ',', '.') }}</span>
                                                        @if($pago->estadoPago)
                                                            <span class="badge rounded-pill text-white py-0 px-2 fs-tiny" style="background-color: {{ $pago->estadoPago->color }}; font-size: 10px;">
                                                                {{ $pago->estadoPago->nombre }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <span class="text-muted small fst-italic d-block text-center py-1">Sin registros de pago individuales</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Progreso de Pago --}}
                        <div class="mt-2 text-start pt-2 border-top">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-black fw-bold">Progreso del pago:</small>
                                <small class="fw-bold text-primary">{{ number_format($porcentajePagado, 0) }}%</small>
                            </div>
                            <div class="progress" style="height: 7px;">
                                <div class="progress-bar rounded" role="progressbar" style="width: {{ $porcentajeVisual }}%; background-color: #00BAD1;" aria-valuenow="{{ $porcentajeVisual }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-dark fw-semibold">Pagado: ${{ number_format($totalPagado, 0, ',', '.') }}</small>
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
