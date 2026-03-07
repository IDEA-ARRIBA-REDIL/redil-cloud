<div>
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Mis citas de consejería</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Consejero</th>
                        <th>Tipo</th>
                        <th>Medio</th>
                        <th>Estado</th>
                        {{-- <th>Acciones</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse($citas as $cita)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $cita->fecha_hora_inicio->format('d/m/Y') }}</span>
                                    <small class="text-muted">{{ $cita->fecha_hora_inicio->format('h:i A') }} - {{ $cita->fecha_hora_fin->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                {{ $cita->consejero->usuario->nombre(3) ?? 'N/A' }}
                            </td>
                            <td>
                                <span class="badge bg-label-primary">{{ $cita->tipoConsejeria->nombre ?? 'General' }}</span>
                            </td>
                            <td>
                                @if($cita->medio == 1)
                                    <span class="badge bg-label-success"><i class="ti ti-map-pin me-1"></i> Presencial</span>
                                @else
                                    <span class="badge bg-label-info"><i class="ti ti-video me-1"></i> Virtual</span>
                                    @if($cita->enlace_virtual)
                                        <a href="{{ $cita->enlace_virtual }}" target="_blank" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" data-bs-toggle="tooltip" title="Ir a la reunión">
                                            <i class="ti ti-external-link"></i>
                                        </a>
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{-- TODO: Implementar estados si existen en el modelo --}}
                                <span class="badge bg-label-secondary">Programada</span>
                            </td>
                            {{-- 
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0);"><i class="ti ti-eye me-1"></i> Ver detalles</a>
                                        <a class="dropdown-item text-danger" href="javascript:void(0);"><i class="ti ti-trash me-1"></i> Cancelar</a>
                                    </div>
                                </div>
                            </td> 
                            --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ti ti-calendar-off fs-1 text-muted mb-2"></i>
                                    <h5 class="text-muted">No tienes citas programadas</h5>
                                    <a href="{{ route('consejeria.nuevaCita', auth()->user()->id) }}" class="btn btn-primary mt-2">
                                        <i class="ti ti-plus me-1"></i> Agendar Nueva Cita
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $citas->links() }}
        </div>
    </div>
</div>
