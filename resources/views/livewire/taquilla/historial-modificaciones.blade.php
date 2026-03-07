<div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="row gx-3 gy-2 align-items-center">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="busqueda">Buscar</label>
                    <input wire:model.live.debounce.300ms="busqueda" type="text" id="busqueda" class="form-control"
                        placeholder="Buscar...">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="puntoPago">Punto de pago</label>
                    <select wire:model.live="puntoPagoId" id="puntoPago" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($puntosDePago as $pdp)
                            <option value="{{ $pdp->id }}">{{ $pdp->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="caja">Caja</label>
                    <select wire:model.live="cajaId" id="caja" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($cajas as $caja)
                            <option value="{{ $caja->id }}">{{ $caja->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fecha-picker" for="fecha">Fecha</label>
                     <input type="text" id="fecha" class="form-control fecha-picker" wire:model.live="fecha"
                        placeholder="YYYY-MM-DD" />
                </div>
            </div>
             <div class="col-12 text-end mt-5">
                        
                        <button wire:click="exportarExcel" class="btn btn-outline-secondary" title="Exportar Excel">
                            <i class="ti ti-file-spreadsheet"></i> Exportar
                        </button>
                    </div>
        </div>
       
    </div>

    <div class="row equal-height-row g-4">
        @forelse ($modificaciones as $modificacion)
            <div class="col equal-height-col col-lg-4 col-md-6 col-sm-12">
                <div class="card h-100 border rounded p-0">
                    <div class="card-header">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="d-flex flex-column">
                                <h5 class="mb-0 fw-semibold text-black lh-sm"
                                    title="{{ $modificacion->usuarioAfectado->nombre(3) ?? 'Usuario Desconocido' }}">
                                    {{ $modificacion->usuarioAfectado->nombre(3) ?? 'Usuario Desconocido' }}
                                </h5>
                                 <small class="text-dark mt-1">{{ $modificacion->usuarioAfectado->tipoIdentificacion->nombre ?? 'N/A' }} - {{ $modificacion->usuarioAfectado->identificacion ?? 'N/A' }}</small>
                            </div>
                            <div class="d-flex flex-column">
                                <h5 class="mb-0 fw-semibold text-black lh-sm">Compra</h5>
                                <small class="text-dark mt-1">#{{ $modificacion->compra_id }}</small>
                                
                            </div>
                           
                        </div>

                        
                    </div>

                    <div class="card-body">
                        <div class="row justify-content-between mb-2">
                            <div class="d-flex flex-row mt-3">
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-calendar me-2"></i>Fecha Modificación:</small>
                                <small class="fw-semibold text-black">{{ $modificacion->created_at->format('d-m-Y h:i A') }}</small>
                               
                            </div>
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-calendar-event me-2"></i>Fecha Compra:</small>
                                <small class="fw-semibold text-black">
                                    {{ $modificacion->created_at->format('d-m-Y') }}
                                </small>
                            </div>
                        </div>

                        <div class="d-flex flex-row mt-3">
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-box me-2"></i>Caja:</small>
                                <small class="fw-semibold text-black">
                                    {{ $modificacion->caja->nombre ?? 'N/A' }}
                                </small>
                            </div>
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-user me-2"></i>Asesor:</small>
                                <small class="fw-semibold text-black">
                                    {{ $modificacion->caja->usuario->nombre(3) ?? 'N/A' }}
                                </small>
                            </div>
                        </div>
                            <div class="d-flex flex-row mt-3">
                                <div class="d-flex flex-column col-12 col-md-6">
                                    <small class="text-dark"><i class="ti ti-activity me-2"></i>Actividad:</small>
                                    <small class="fw-semibold text-black"
                                        title="{{ $modificacion->actividad->nombre ?? 'N/A' }}">
                                        {{ $modificacion->actividad->nombre ?? 'N/A' }}
                                    </small>
                                </div>
                                <div class="d-flex flex-column col-12 col-md-6">
                                    <small class="text-dark"><i class="ti ti-currency-dollar me-2"></i>Valor:</small>
                                    <small class="fw-semibold text-black">
                                        ${{ number_format($modificacion->valor, 0, ',', '.') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Sección Colapsable para el Motivo --}}
                        <div class="collapse" id="cardBodyModificacion{{ $modificacion->id }}">
                            <div class="col-12">
                                <hr class="my-3 border-1">
                            </div>
                            <div class="alert alert-danger mt-3 mb-0" role="alert">
                                <h6 class="alert-heading mb-1"><i class="ti ti-alert-circle me-2"></i>Motivo de anulación:</h6>
                                <p class="mb-0 small">{{ $modificacion->motivo }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer con Botón de Colapsar --}}
                    <div class="card-footer border-top p-1 d-flex justify-content-center align-items-center">
                        <button type="button"
                            class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
                            data-bs-toggle="collapse" data-bs-target="#cardBodyModificacion{{ $modificacion->id }}">
                            <span class="ti ti-plus"></span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center" role="alert">
                    <i class="ti ti-alert-triangle me-2"></i>
                    No se encontraron modificaciones registradas con los filtros seleccionados.
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $modificaciones->links() }}
    </div>
</div>
