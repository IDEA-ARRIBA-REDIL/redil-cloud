<div class="consulta-transacciones">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center gap-3">
            <div class="avatar avatar-sm bg-label-primary rounded p-1">
                <i class="ti ti-search fs-4"></i>
            </div>
            <div>
                <h5 class="card-title mb-0 fw-semibold">Consultar transacciones</h5>
                <small class="text-muted">Define el período y el origen de los movimientos.</small>
            </div>
        </div>
        <div class="card-body">
            <form wire:submit="buscar" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="sap-fecha-inicio" class="form-label">Fecha inicial</label>
                    <input id="sap-fecha-inicio" type="date" class="form-control" wire:model="fechaInicio" required>
                    @error('fechaInicio') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="sap-fecha-fin" class="form-label">Fecha final</label>
                    <input id="sap-fecha-fin" type="date" class="form-control" wire:model="fechaFin" required>
                    @error('fechaFin') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="sap-tipo-ingreso" class="form-label">Tipo de ingreso</label>
                    <select id="sap-tipo-ingreso" class="form-select" wire:model="tipoIngreso">
                        <option value="todos">Todos</option>
                        <option value="ofrenda">Ofrendas</option>
                        <option value="actividad">Actividades</option>
                    </select>
                    @error('tipoIngreso') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary rounded-pill w-100" wire:loading.attr="disabled" wire:target="buscar">
                        <i class="ti ti-search me-1"></i> Consultar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($mostrarResultados)
            <div class="card border-0 shadow-sm mb-4" wire:key="filtros-{{ $versionFiltros }}-{{ $tipoIngreso }}">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center gap-3">
                    <div class="avatar avatar-sm bg-label-info rounded p-1">
                        <i class="ti ti-adjustments-horizontal fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">Afinar resultados</h5>
                        <small class="text-muted">Selecciona varias opciones por filtro. Sin selección se incluyen todas.</small>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit="filtrar" class="row g-3 align-items-end">
                        <div class="col-12 col-lg-6">
                            <label for="sap-metodo-pago" class="form-label">Método de pago</label>
                            <div wire:ignore x-data x-init="$nextTick(() => window.initConsultaSapSelect2($refs.select, $wire, 'metodoPagoIds', 'Todos los métodos'))">
                                <select id="sap-metodo-pago" x-ref="select" class="form-select" multiple>
                                    @foreach ($metodosPago as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label for="sap-forma-pago" class="form-label">Forma (pasarela)</label>
                            <div wire:ignore x-data x-init="$nextTick(() => window.initConsultaSapSelect2($refs.select, $wire, 'formasPago', 'Todas las formas'))">
                                <select id="sap-forma-pago" x-ref="select" class="form-select" multiple>
                                    <option value="sin_codigo">Sin código (efectivo y otros)</option>
                                    <option value="29">PSE</option>
                                    <option value="32">Tarjeta (32)</option>
                                    <option value="51">Tarjeta (51)</option>
                                </select>
                            </div>
                        </div>
                        @if ($tipoIngreso !== 'ofrenda')
                            <div class="col-12 col-lg-6">
                                <label for="sap-actividad" class="form-label">Actividad</label>
                                <div wire:ignore x-data x-init="$nextTick(() => window.initConsultaSapSelect2($refs.select, $wire, 'actividadIds', 'Todas las actividades'))">
                                    <select id="sap-actividad" x-ref="select" class="form-select" multiple>
                                        @foreach ($actividades as $actividad)
                                            <option value="{{ $actividad->id }}">{{ $actividad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label for="sap-punto-pago" class="form-label">Punto de pago</label>
                                <div wire:ignore x-data x-init="$nextTick(() => window.initConsultaSapSelect2($refs.select, $wire, 'puntoPagoIds', 'Todos los puntos'))">
                                    <select id="sap-punto-pago" x-ref="select" class="form-select" multiple>
                                        @foreach ($puntosPago as $punto)
                                            <option value="{{ $punto->id }}">{{ $punto->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="col-12 d-grid d-lg-flex justify-content-lg-end">
                            <button type="submit" class="btn btn-outline-primary rounded-pill" wire:loading.attr="disabled" wire:target="filtrar">
                                <i class="ti ti-filter me-1"></i> Filtrar
                            </button>
                        </div>
                    </form>
                    @foreach (['metodoPagoIds', 'formasPago', 'actividadIds', 'puntoPagoIds'] as $campo)
                        @error($campo) <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    @endforeach
                </div>
            </div>

        <div class="row g-4 mb-4">
            <div class="col-sm-6">
                <div class="card h-100 border-start border-4 border-primary shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar avatar-md bg-label-primary rounded p-2">
                                <i class="ti ti-currency-dollar fs-3"></i>
                            </div>
                            <span class="badge bg-label-primary rounded-pill">Importe registrado</span>
                        </div>
                        <h3 class="mb-1 mt-3 text-primary fw-bold">${{ number_format((float) $total, 2, ',', '.') }}</h3>
                        <small class="text-muted">Suma de los movimientos de todos los estados del filtro.</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card h-100 border-start border-4 border-info shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar avatar-md bg-label-info rounded p-2">
                                <i class="ti ti-receipt fs-3"></i>
                            </div>
                            <span class="badge bg-label-info rounded-pill">Transacciones</span>
                        </div>
                        <h3 class="mb-1 mt-3 text-info fw-bold">{{ number_format($cantidad, 0, ',', '.') }}</h3>
                        <small class="text-muted">Cada pago o cada ofrenda cuenta como un movimiento.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="card-title mb-1 fw-semibold"><i class="ti ti-download me-2 text-primary"></i>Exportar resultados</h5>
                    <small class="text-muted">El Excel contiene la consulta filtrada. Los TXT son un borrador para comparar con Visión.</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-success rounded-pill" wire:click="exportarExcel" wire:loading.attr="disabled" wire:target="exportarExcel">
                        <i class="ti ti-file-spreadsheet me-1"></i> Exportar Excel
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill" wire:click="exportarBorradorSap" wire:loading.attr="disabled" wire:target="exportarBorradorSap">
                        <i class="ti ti-file-zip me-1"></i> Exportar archivos planos
                    </button>
                </div>
            </div>
        </div>
        @if ($mensajeExportacion !== '' || $faltantesSap !== [])
            <div class="alert alert-warning" role="alert">
                @if ($mensajeExportacion !== '')
                    <strong>{{ $mensajeExportacion }}</strong>
                @endif
                @if ($faltantesSap !== [])
                    <ul class="mb-0">
                        @foreach ($faltantesSap as $campo => $cantidadFaltante)
                            <li>{{ $campo }}: {{ number_format($cantidadFaltante, 0, ',', '.') }} líneas</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center gap-3">
                <div class="avatar avatar-sm bg-label-primary rounded p-1">
                    <i class="ti ti-list-details fs-4"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0 fw-semibold">Detalle de transacciones</h5>
                    <small class="text-muted">Se muestran hasta 25 movimientos por página.</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Fecha</th><th>Origen</th><th>ID</th><th>Referencia</th><th>Persona</th><th>Identificación</th><th>Concepto</th><th>Método</th><th>Forma</th><th>Punto de pago</th><th>Estado</th><th class="text-end">Valor</th></tr></thead>
                        <tbody>
                            @forelse ($movimientos as $movimiento)
                                <tr wire:key="{{ $movimiento->origen }}-{{ $movimiento->id }}">
                                    <td>{{ $movimiento->fecha }}</td>
                                    <td><span class="badge {{ $movimiento->origen === 'ofrenda' ? 'bg-label-success' : 'bg-label-primary' }} rounded-pill">{{ $movimiento->origen === 'ofrenda' ? 'Ofrenda' : 'Actividad' }}</span></td>
                                    <td>{{ $movimiento->id }}</td>
                                    <td>{{ $movimiento->referencia ?: '—' }}</td>
                                    <td>{{ $movimiento->persona ?: '—' }}</td>
                                    <td>{{ $movimiento->identificacion ?: '—' }}</td>
                                    <td class="fw-semibold">{{ $movimiento->concepto ?: '—' }}</td>
                                    <td>{{ $movimiento->metodo_pago ?: '—' }}</td>
                                    <td>{{ match ((int) $movimiento->int_id_forma_pago) { 29 => 'PSE', 32, 51 => 'Tarjeta', default => $movimiento->int_id_forma_pago ?: 'Sin código' } }}</td>
                                    <td>{{ $movimiento->punto_pago ?: '—' }}</td>
                                    <td><span class="badge {{ $movimiento->estado === 'Eliminado' ? 'bg-label-danger' : 'bg-label-secondary' }} rounded-pill">{{ $movimiento->estado ?: '—' }}</span></td>
                                    <td class="text-end fw-semibold text-nowrap">${{ number_format((float) $movimiento->valor, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted py-5"><i class="ti ti-info-circle fs-3 d-block mb-2"></i>No hay transacciones para estos filtros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($movimientos)
                <div class="card-footer bg-transparent border-top">{{ $movimientos->links() }}</div>
            @endif
        </div>
    @endif
</div>
