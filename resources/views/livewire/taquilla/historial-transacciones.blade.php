<div x-data="{ actionUrl: '' }">
    <div class=" mb-4">
        <div class="">
            <div class="row gx-3 gy-2 align-items-center">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="fecha">Fecha</label>
                    <input type="text" id="fecha" class="form-control fecha-picker" wire:model.live="fecha"
                        placeholder="YYYY-MM-DD" />
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="busqueda">Buscar</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" placeholder="Buscar por nombre, cédula o email..."
                            wire:model.live.debounce.500ms="busqueda" aria-label="Buscar..."
                            aria-describedby="basic-addon-search31" />
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('layouts.status-msn')

    <div class="row equal-height-row g-4">
        <div class="col-12 text-start">
            <small class="text-dark">Mostrando transacciones del: <strong>{{ $fecha }}</strong></small>
        </div>
        @forelse ($transacciones as $compra)
            <div class="col equal-height-col col-lg-4 col-md-6 col-sm-12">
                <div class="card h-100 border rounded p-0">
                    <div class="card-header">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="d-flex flex-column">
                                <h5 class="mb-0 fw-semibold text-black lh-sm "
                                    title="{{ $compra->nombre_completo_comprador }}">
                                    {{ $compra->nombre_completo_comprador }}
                                </h5>
                                <small class="text-dark mt-1">Recibo #{{ $compra->id }}</small>
                            </div>


                            <div class="dropdown zindex-2 p-1">
                                <button style="border-radius: 20px;" class="btn p-1 border" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical text-black"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('taquilla.compraFinalizada', $compra) }}"
                                        target="_blank">
                                        Ver Recibo
                                    </a>

                                    {{-- Opción Solicitar Anulación (Solo si no está anulada ni pendiente) --}}
                                    @if ($compra->estado != 4 && $compra->estado != 5 && $compra->estado != 6)
                                        <button class="dropdown-item text-black"
                                            @click="actionUrl = '{{ route('taquilla.solicitarAnulacion', $compra->id) }}'; new bootstrap.Modal(document.getElementById('modalSolicitarAnulacion')).show()">
                                            Solicitar Anulación
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-row  mt-3">
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-id me-2"></i>Identificación:</small>
                                <small class="fw-semibold text-black">{{ $compra->identificacion_comprador }}</small>
                            </div>
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-calendar-event me-2"></i>Fecha
                                    Compra:</small>
                                <small class="fw-semibold text-black">
                                    {{ $compra->created_at->format('d/m/Y h:i A') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row justify-content-between mb-2">

                            <div class="d-flex flex-row  mt-3">
                                <div class="d-flex flex-column col-12 col-md-6">
                                    <small class="text-dark"><i class="ti ti-activity me-2"></i>Actividad:</small>
                                    <small class="fw-semibold text-black "
                                        title="{{ $compra->actividad->nombre ?? 'N/A' }}">
                                        {{ $compra->actividad->nombre ?? 'Actividad no encontrada' }}
                                    </small>
                                </div>
                                <div class="d-flex flex-column col-12 col-md-6">
                                    <small class="text-dark"><i class="ti ti-category-2 me-2"></i>Categoria:</small>
                                    @foreach ($compra->inscripciones as $inscripcion)
                                        <small
                                            class="fw-semibold text-black">{{ $inscripcion->categoriaActividad->nombre ?? 'Categoría' }}
                                        </small>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                        <div class="border-top pt-3">
                            @php
                                $permiteAbonos = $compra->actividad->tipo->permite_abonos ?? false;
                                $esEscuela = $compra->actividad->tipo->tipo_escuelas ?? false;
                                // Filtrar pagos no anulados
                                $pagosActivos = $compra->pagos->where('anulado_pdp', false);
                                $totalPagado = $pagosActivos->sum('valor');
                                $valorTotalCompra = $compra->valor;
                                $saldoPendiente = $valorTotalCompra - $totalPagado;
                                $porcentajePagado =
                                    $valorTotalCompra > 0 ? ($totalPagado / $valorTotalCompra) * 100 : 0;
                            @endphp

                            @if ($permiteAbonos && !$esEscuela && $saldoPendiente > 0)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-dark">Progreso de Pago</small>
                                        <small class="fw-bold">{{ number_format($porcentajePagado, 0) }}%</small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ $porcentajePagado }}%"
                                            aria-valuenow="{{ $porcentajePagado }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <small class="text-dark d-block">Abonado</small>
                                        <span
                                            class="text-primary fw-semibold">${{ number_format($totalPagado, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-dark d-block">Restante</small>
                                        <span
                                            class="text-danger fw-semibold">${{ number_format($saldoPendiente, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                @if ($pagosActivos->count() > 1)
                                    <div class="mt-2">
                                        <small class="text-dark d-block mb-1">Detalle de Abonos:</small>
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($pagosActivos as $pago)
                                                <li class="d-flex justify-content-between font-small-2">
                                                    <span
                                                        class="text-dark">{{ \Carbon\Carbon::parse($pago->fecha)->format('d/m') }}</span>
                                                    <span>${{ number_format($pago->valor, 0, ',', '.') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @if ($compra->pagos()->where('anulado_pdp', true)->count() > 0)
                                        <div class="d-flex flex-column">
                                            <small class="badge bg-danger   text-white"> Anulado</small>
                                        </div>
                                    @elseif($compra->estado == 5)
                                        <div class="d-flex flex-column">
                                            <small class="badge bg-warning text-white">Pendiente Anulación</small>
                                        </div>
                                    @elseif($compra->estado == 6 || $compra->estado == 4)
                                        <div class="d-flex flex-column">
                                            <small class="badge bg-danger text-white">Anulada</small>
                                        </div>
                                    @endif
                                @endif
                            @else
                                {{-- Vista Normal (Pago Completo o No Aplica Abonos) --}}
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-dark">Total Pagado</small>
                                        <h5 class="mb-0 text-primary">
                                            ${{ number_format($compra->valor, 0, ',', '.') }}</h5>
                                    </div>
                                    <div>
                                        @if ($compra->estado == 1)
                                            <span class="badge bg-success text-white">Pagado</span>
                                        @elseif($compra->estado == 4 || $compra->estado == 6)
                                            <span class="badge bg-danger   text-white">Anulada</span>
                                        @elseif($compra->estado == 5)
                                            <span class="badge bg-warning text-white">Pendiente Anulación</span>
                                        @else
                                            <span class="badge bg-warning text-white">Pendiente</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
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
                            No se encontraron transacciones.
                            No hay registros para la fecha seleccionada o el criterio de búsqueda.

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

    <!-- Modal Solicitud Anulacion -->
    <div class="modal fade" id="modalSolicitarAnulacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" :action="actionUrl">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Solicitar Anulación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        Esta acción enviará una solicitud de anulación para su aprobación.
                    </div>
                    <label class="form-label">Motivo de la anulación (Obligatorio)</label>
                    <textarea name="motivo" class="form-control" rows="3" required minlength="5"
                        placeholder="Describa por qué se debe anular..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>

@script
    <script>
        window.addEventListener('mostrarToast', event => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: event.detail[0].icon,
                title: event.detail[0].title
            });
        });
    </script>
@endscript
