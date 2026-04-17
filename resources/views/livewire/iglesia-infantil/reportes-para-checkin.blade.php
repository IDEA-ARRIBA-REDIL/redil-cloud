<div wire:mouseover="desplegarListaBusqueda" wire:mouseout="ocultarListaBusqueda">

    <div class="{{ $verInputBusqueda ? '' : 'd-none' }}">
        <div class="input-group input-group-merge shadow-sm">
            <span class="input-group-text bg-light border-end-0">
                <i class="ti ti-calendar-event text-primary"></i>
            </span>
            <input wire:model.live.debounce.200ms="busqueda"
                type="text"
                class="form-control ps-2"
                placeholder="Buscar reporte..."
                spellcheck="false"
                style="font-size: 0.9rem;">
        </div>
    </div>

    {{-- Lista desplegable de resultados --}}
    <div class="divListaBusquedaReporte position-relative {{ $verListaBusqueda ? '' : 'd-none' }}">
        <div id="listaItemsBusquedaReporte" class="panel-busqueda position-absolute p-2 w-100 shadow-lg bg-white rounded-bottom border"
            style="max-height: 300px; overflow-y: auto; z-index: 1060; top: 100%;">
            @if ($reportes && $reportes->count() > 0)
                @foreach ($reportes as $reporte)
                    <a href="javascript:;"
                        wire:click="seleccionarReporte({{ $reporte->id }})"
                        class="dropdown-item d-flex align-items-center mb-1 border p-2 rounded">
                        <div class="d-flex align-items-center justify-content-center bg-primary me-2 rounded flex-shrink-0" style="width:32px; height:32px;">
                            <i class="ti ti-baby-carriage text-white" style="font-size: 1.1rem !important"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="mb-0 fw-bold text-black text-truncate" style="font-size: 0.85rem;">{{ $reporte->reunion?->nombre }}</p>
                            <p class="mb-0 text-black-50 small text-truncate" style="font-size: 0.75rem;">
                                {{ \Carbon\Carbon::parse($reporte->fecha)->translatedFormat('D d/m/y') }}
                                @if($reporte->reunion?->hora)
                                    — {{ \Carbon\Carbon::parse($reporte->reunion->hora)->format('g:i a') }}
                                @endif
                            </p>
                        </div>
                        <span class="badge bg-label-success d-none d-sm-inline-block ms-2">Infantil</span>
                    </a>
                @endforeach
            @else
                <div class="pt-3 text-center">
                    <p class="tx-12 text-muted">
                        <i class="ti ti-list-search fs-4"></i>
                        {{ strlen($busqueda) < 2 ? 'Escribe para buscar o haz scroll' : 'No se encontraron reportes.' }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Reporte seleccionado --}}
    @if ($reporteSeleccionado)
        <div class="col-12">
            <div class="dropdown-item w-100 d-flex flex-grow-1 m-0 border p-2 rounded bg-light border-primary">
                <div class="flex-fill d-flex align-items-center overflow-hidden">
                    <div class="d-flex align-items-center justify-content-center bg-primary me-2 rounded flex-shrink-0" style="width:32px; height:32px;">
                        <i class="ti ti-baby-carriage text-white" style="font-size: 1.1rem !important"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="mb-0 fw-bold text-black text-truncate" style="font-size: 0.85rem;">{{ $reporteSeleccionado->reunion?->nombre }}</p>
                        <p class="mb-0 text-black-50 small text-truncate" style="font-size: 0.75rem;">
                            {{ \Carbon\Carbon::parse($reporteSeleccionado->fecha)->translatedFormat('D d/m/y') }}
                            @if($reporteSeleccionado->reunion?->hora)
                                — {{ \Carbon\Carbon::parse($reporteSeleccionado->reunion->hora)->format('g:i a') }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <button type="button" wire:click="quitarSeleccion"
                        class="align-self-start btn btn-danger btn-xs p-1">
                        <i class="ti ti-x fs-6"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
