<div>
    <h4 class="mb-1 fw-semibold text-primary">Solicitud de traslado de matrícula</h4>
    <p class="mb-4 text-muted">Aquí puedes solicitar cambios de horario para tus matrículas activas. Sujeto a aprobación y disponibilidad.</p>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">1. Selecciona la matrícula</h5>
        </div>
        <div class="card-body">
            @if($matriculasActivas->isEmpty())
                <div class="alert alert-warning">No tienes matrículas activas en este momento para las que puedas solicitar traslado.</div>
            @else
                <div class="row g-3">
                    @foreach($matriculasActivas as $matricula)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border {{ $matriculaSeleccionadaId == $matricula->id ? 'border-primary shadow-lg' : '' }}"
                                 style="cursor: pointer; transition: all 0.2s;"
                                 wire:click="$set('matriculaSeleccionadaId', {{ $matricula->id }})">
                                <div class="card-body">
                                    <h6 class="card-title text-primary fw-bold">{{ $matricula->horarioMateriaPeriodo->materiaPeriodo->materia->nombre }}</h6>
                                    <p class="mb-1 small"><i class="ti ti-calendar me-1"></i> {{ $matricula->periodo->nombre }}</p>
                                    <p class="mb-1 small"><i class="ti ti-clock me-1"></i> {{ $matricula->horarioMateriaPeriodo->horarioBase->dia_semana }} {{ $matricula->horarioMateriaPeriodo->horarioBase->hora_inicio_formato }}</p>
                                    <p class="mb-0 small"><i class="ti ti-map-pin me-1"></i> {{ $matricula->horarioMateriaPeriodo->horarioBase->aula->sede->nombre }}</p>

                                    @if($matriculaSeleccionadaId == $matricula->id)
                                        <div class="mt-2 text-primary fw-bold"><i class="ti ti-check-circle"></i> Seleccionada</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if($matriculaSeleccionadaId)
        <div class="card mb-4" id="paso2">
            <div class="card-header">
                <h5 class="card-title mb-0">2. Estado y selección de destino</h5>
            </div>
            <div class="card-body">
                @if(!$puedeSolicitar)
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="ti ti-ban me-2 fs-4"></i>
                        <div>
                            <strong>No puedes solicitar traslado:</strong> {{ $mensajeBloqueo }}
                        </div>
                    </div>
                @else
                    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                        <i class="ti ti-circle-check me-2 fs-4"></i>
                        <div>
                            <strong>Estás habilitado.</strong> Puedes seleccionar un nuevo horario de la lista.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Horarios Disponibles para Traslado</label>
                        @if($horariosDisponibles->isEmpty())
                            <p class="text-muted fst-italic">No hay otros horarios disponibles con cupos para esta materia en este periodo.</p>
                        @else
                            <select wire:model.live="horarioDestinoId" class="form-select">
                                <option value="">-- Selecciona un nuevo horario --</option>
                                @foreach($horariosDisponibles as $horario)
                                    <option value="{{ $horario->id }}">
                                        {{ $horario->horarioBase->aula->sede->nombre }} -
                                        {{ $horario->horarioBase->dia_semana }} {{ $horario->horarioBase->hora_inicio_formato }}
                                        (Cupos: {{ $horario->cupos_disponibles }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="mt-4 text-end">
                        <button wire:click="solicitar"
                                class="btn btn-primary"
                                @if(!$horarioDestinoId) disabled @endif>
                            <span wire:loading.remove wire:target="solicitar">Confirmar solicitud</span>
                            <span wire:loading wire:target="solicitar">Enviando...</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif


    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">3. Historial de solicitudes</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Materia</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($historialSolicitudes as $historial)
                        <tr>
                            <td>{{ $historial->created_at->format('d/m/Y') }}</td>
                            <td>{{ $historial->matricula->horarioMateriaPeriodo->materiaPeriodo->materia->nombre ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-label-secondary">
                                    {{ $historial->horarioOrigen->horarioBase->dia_semana }}
                                    {{ $historial->horarioOrigen->horarioBase->hora_inicio_formato }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">
                                    {{ $historial->horarioDestino->horarioBase->dia_semana }}
                                    {{ $historial->horarioDestino->horarioBase->hora_inicio_formato }}
                                </span>
                            </td>
                            <td>
                                @switch($historial->estado)
                                    @case('pendiente')
                                        <span class="badge bg-warning text-white">Pendiente</span>
                                        @break
                                    @case('aprobado')
                                        <span class="badge bg-success text-white">Aprobado</span>
                                        @break
                                    @case('rechazado')
                                        <span class="badge bg-danger text-white">Rechazado</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary text-white">{{ $historial->estado }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($historial->estado === 'rechazado' && $historial->motivo_rechazo)
                                    <button type="button" class="btn btn-sm btn-icon btn-label-danger"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Motivo: {{ $historial->motivo_rechazo }}">
                                        <i class="ti ti-info-circle"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No has realizado ninguna solicitud de traslado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
