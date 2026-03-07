<div>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filtros de Búsqueda</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @php
                // Helpers para fechas rápidas
                $hoy = \Carbon\Carbon::now()->format('Y-m-d');
                $ayer = \Carbon\Carbon::yesterday()->format('Y-m-d');
                $inicioMes = \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
                $finMes = \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');
                @endphp

                <!-- Rango de Fechas -->
                <div class="col-md-4">
                    <label class="form-label">Rango de Fechas</label>
                    <div class="input-group">
                        <input type="date" wire:model.live="fechaInicio" class="form-control">
                        <span class="input-group-text">a</span>
                        <input type="date" wire:model.live="fechaFin" class="form-control">
                    </div>
                     <div class="mt-1">
                        <button type="button" class="btn btn-xs btn-outline-secondary" wire:click="$set('fechaInicio', '{{ $hoy }}'); $set('fechaFin', '{{ $hoy }}')">Hoy</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" wire:click="$set('fechaInicio', '{{ $ayer }}'); $set('fechaFin', '{{ $ayer }}')">Ayer</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" wire:click="$set('fechaInicio', '{{ $inicioMes }}'); $set('fechaFin', '{{ $finMes }}')">Este Mes</button>
                    </div>
                </div>

                <!-- Selección de Cajas -->
                <div class="col-md-4">
                    <label class="form-label">Cajas</label>
                    <div wire:ignore>
                        <select id="selectCajas" class="select2 form-select" multiple>
                            @foreach($todasLasCajas as $caja)
                            <option value="{{ $caja->id }}" {{ in_array($caja->id, $cajasSeleccionadas) ? 'selected' : '' }}>
                                {{ $caja->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @push('scripts')
                <script>
                    document.addEventListener('livewire:initialized', () => {
                        const selectCajas = $('#selectCajas');

                        // Inicializar Select2
                        selectCajas.select2({
                            placeholder: 'Selecciona las cajas',
                            closeOnSelect: false,
                            width: '100%'
                        });

                        // Evento cambio para actualizar Livewire
                        selectCajas.on('change', function (e) {
                            @this.set('cajasSeleccionadas', $(this).val());
                        });
                    });
                </script>
                @endpush

                <!-- Estado -->
                <div class="col-md-4">
                    <label class="form-label">Estado de Transacción</label>
                    <select wire:model.live="estadoTransaccion" class="form-select">
                        <option value="todos">Todas</option>
                        <option value="aprobada">Aprobadas</option>
                        <option value="anulada">Anuladas</option>
                        <option value="pendiente">Pendientes</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 text-end">
            <button wire:click="exportarExcel" wire:loading.attr="disabled" class="btn btn-outline-secondary rounded-pill waves-effect">
                <span wire:loading.remove wire:target="exportarExcel"><i class="ti ti-file-spreadsheet me-2"></i>Exportar Excel</span>
                <span wire:loading wire:target="exportarExcel"><i class="ti ti-loader animate-spin me-2"></i>Generando...</span>
            </button>
        </div>
    </div>

    <!-- Resumen de Totales -->
    <div class="row g-4 mb-4">
        <!-- Tarjeta Total General -->
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Ingresos</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">${{ number_format($totalIngresos, 0, ',', '.') }}</h4>
                            </div>
                            <small class="text-success">({{ $totalTransacciones }} transacciones)</small>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-currency-dollar ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Resumen por Caja -->
        <div class="col-sm-6 col-xl-8">
            <div class="card h-100">
                <div class="card-header pb-2">
                    <h5 class="card-title mb-0">Detalle por Caja</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-sm table-borderless">
                            <thead>
                                <tr>
                                    <th>Caja</th>
                                    <th class="text-end">Transacciones</th>
                                    <th class="text-end">Total Recaudado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($totalPorCaja as $id => $datos)
                                <tr>
                                    <td>{{ $datos['nombre'] }}</td>
                                    <td class="text-end">{{ $datos['cantidad'] }}</td>
                                    <td class="text-end fw-bold">${{ number_format($datos['total'], 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No hay datos para mostrar</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado Detallado -->
    <!-- Listado Detalles (Vista Tarjetas) -->
    <div class="row equal-height-row g-4 mb-4">
        <div class="col-12">
             <h5 class="mb-0">Detalle de Transacciones</h5>
        </div>

        @forelse ($transacciones as $pago)
        <div class="col equal-height-col col-lg-4 col-md-6 col-sm-12">
            <div class="card h-100 border rounded p-0">
                <div class="card-header">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="d-flex flex-column">
                            <h5 class="mb-0 fw-semibold text-black lh-sm" title="{{ $pago->compra->nombre_completo_comprador ?? 'N/A' }}">
                                {{ $pago->compra->nombre_completo_comprador ?? 'N/A' }}
                            </h5>
                            <small class="text-dark mt-1">Pago #{{ $pago->id }}
                                @if($pago->codigo_vaucher) <br> Voucher: {{ $pago->codigo_vaucher }} @endif
                            </small>
                        </div>

                        {{-- Dropdown de Acciones (Opcional) --}}
                        <div class="dropdown zindex-2 p-1">
                            <button style="border-radius: 20px;" class="btn p-1 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical text-black"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                {{-- Enlace al recibo de compra global --}}
                                @if($pago->compra)
                                <a class="dropdown-item" href="{{ route('taquilla.compraFinalizada', $pago->compra->id) }}" target="_blank">
                                    Ver Recibo Compra
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-row mt-3">
                        <div class="d-flex flex-column col-12 col-md-6">
                            <small class="text-dark"><i class="ti ti-id me-2"></i>Identificación:</small>
                            <small class="fw-semibold text-black">{{ $pago->compra->identificacion_comprador ?? 'N/A' }}</small>
                        </div>
                        <div class="d-flex flex-column col-12 col-md-6">
                            <small class="text-dark"><i class="ti ti-calendar-event me-2"></i>Fecha Pago:</small>
                            <small class="fw-semibold text-black">
                                {{ $pago->created_at->format('d/m/Y h:i A') }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row justify-content-between mb-2">
                        <div class="d-flex flex-row mt-3">
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-activity me-2"></i>Actividad:</small>
                                <small class="fw-semibold text-black" title="{{ $pago->compra->actividad->nombre ?? 'N/A' }}">
                                    {{ $pago->compra->actividad->nombre ?? 'N/A' }}
                                </small>
                            </div>
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-box me-2"></i>Caja:</small>
                                <small class="fw-semibold text-black">
                                    {{ $pago->caja->nombre ?? 'N/A' }}
                                </small>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    {{ $pago->caja->usuario->nombre(1) ?? '' }}
                                </small>
                            </div>
                        </div>
                         {{-- Categorías (De la Compra) --}}
                         <div class="d-flex flex-row mt-2">
                            <div class="d-flex flex-column col-12">
                                <small class="text-dark"><i class="ti ti-category-2 me-2"></i>Categoría(s):</small>
                                @if($pago->compra && $pago->compra->inscripciones)
                                    @foreach ($pago->compra->inscripciones as $inscripcion)
                                        <small class="fw-semibold text-black d-block">
                                            - {{ $inscripcion->categoriaActividad->nombre ?? 'N/A' }}
                                        </small>
                                    @endforeach
                                @endif
                            </div>
                         </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-dark">Valor Pago</small>
                                <h5 class="mb-0 text-primary">
                                    ${{ number_format($pago->valor, 0, ',', '.') }}
                                </h5>
                                <small class="text-muted d-block">{{ $pago->tipoPago->nombre ?? 'N/A' }}</small>
                            </div>
                            <div>
                                @if($pago->compra)
                                    @if ($pago->compra->estado == 1)
                                        <span class="badge bg-success text-white">Aprobada</span>
                                    @elseif($pago->compra->estado == 4 || $pago->compra->estado == 6)
                                        <span class="badge bg-danger text-white">Anulada</span>
                                    @elseif($pago->compra->estado == 5)
                                        <span class="badge bg-warning text-white">Pend. Anulación</span>
                                    @else
                                        <span class="badge bg-warning text-white">Pendiente</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Sin Compra</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center p-5">
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>
                        No se encontraron transacciones en el rango seleccionado.
                    </div>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <div class="row mt-4">
        <div class="col-12">
            {{ $transacciones->links() }}
        </div>
    </div>
</div>
