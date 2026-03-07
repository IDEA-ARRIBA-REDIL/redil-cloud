@section('title', 'Solicitudes de Traslado')

<div>
    <h4 class="mb-4 fw-semibold text-primary">Solicitudes pendientes de traslado</h4>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Listado de solicitudes</h5>
            <div class="w-50 w-md-25">
                <input type="text" wire:model.live.debounce.300ms="filtroNombre" class="form-control" placeholder="Buscar estudiante...">
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($solicitudes as $solicitud)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex flex-column">
                                <h5 class="fw-semibold card-title mb-0">{{ $solicitud->user->nombre(3)}}</h5>
                                <small class="text-muted">{{ $solicitud->user->email }}</small>
                            </div>

                        </div>

                        <div class="row mb-3">
                          <div class="col-6">
                            <small class="text-black fw-semibold mb-1">Materia</small><br>
                            <small     class="mb-1 text-muted">{{ $solicitud->matricula->horarioMateriaPeriodo->materiaPeriodo->materia->nombre ?? 'N/A' }}</small>
                          </div>
                          <div class="col-6">
                             <small class="text-black fw-semibold mb-1">Fecha:</small><br>
                             <small class="mb-1 text-muted">{{ $solicitud->created_at->format('d/m/Y') }}</small>
                          </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-dark fw-semibold mb-0 pb-0">Origen</small><br>
                                <small class="text-muted">
                                    {{ $solicitud->horarioOrigen->horarioBase->dia_semana }}
                                    {{ $solicitud->horarioOrigen->horarioBase->hora_inicio_formato }}
                                </small>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-dark fs-8 fw-semibold mb-0 pb-0">Destino Solicitado</small><br>
                                <small class="text-muted">
                                    {{ $solicitud->horarioDestino->horarioBase->dia_semana }}
                                    {{ $solicitud->horarioDestino->horarioBase->hora_inicio_formato }}
                                </small>

                            </div>
                            <br>
                            <hr>
                            <div class="col-6">
                                <small class="text-dark fs-8 fw-semibold mb-0 pb-0">Cupos Disponibles</small><br>
                                <small class="text-muted">
                                    {{ $solicitud->horarioDestino->cupos_disponibles }}
                                </small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button class="btn btn-primary rounded-pill flex-grow-1"
                                wire:loading.attr="disabled"
                                onclick="confirmarAprobacion({{ $solicitud->id }})">
                                <i class="ti ti-check me-1"></i> Aprobar
                            </button>
                            <button class="btn btn-outline-secondary rounded-pill flex-grow-1"
                                wire:loading.attr="disabled"
                                onclick="confirmarRechazo({{ $solicitud->id }})">
                                <i class="ti ti-x me-1"></i> Rechazar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center">
                    <div class="mb-3">
                        <i class="ti ti-check text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h5>No hay solicitudes pendientes</h5>
                    <p class="text-muted">Todas las solicitudes de traslado han sido procesadas o no hay nuevas peticiones.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $solicitudes->links() }}
    </div>
</div>

@script
<script>
    window.confirmarAprobacion = (id) => {
        Swal.fire({
            title: '¿Aprobar Traslado?',
            text: "Se cambiará el horario del estudiante inmediatamente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-primary me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Usamos $wire directo para llamar al método
                $wire.aprobarSolicitud(id);
            }
        });
    }

    window.confirmarRechazo = (id) => {
        Swal.fire({
            title: 'Rechazar Solicitud',
            input: 'textarea',
            inputLabel: 'Motivo del rechazo',
            inputPlaceholder: 'Explica el motivo...',
            showCancelButton: true,
            confirmButtonText: 'Rechazar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false,
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes escribir un motivo'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.rechazarSolicitud(id, result.value);
            }
        });
    }
</script>
@endscript
