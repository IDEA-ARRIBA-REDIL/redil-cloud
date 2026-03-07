<div>
    @if ($showModal && $traslado)
        <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-primary fw-semibold">Detalle del traslado</h4>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body class="text-black"">
                        <p class="text-black">
                            El traslado fue realizado por: <br>
                            <strong>{{ $traslado->user->nombre(3) ?? 'N/A' }}</strong>
                            el <strong>{{ $traslado->created_at->format('d/m/Y \a \l\a\s h:i A') }}</strong>.
                        </p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-black small">HORARIO DE ORIGEN</h6>
                                <p class="mb-1">
                                    <i class="ti ti-building-community ti-xs me-1"></i>
                                    <strong class="text-black">Sede:</strong>
                                    {{ $traslado->horarioOrigen->horarioBase->aula->sede->nombre ?? 'N/A' }}
                                </p>
                                <p>
                                    <i class="ti ti-clock ti-xs me-1"></i>
                                    {{ $traslado->horarioOrigen->horarioBase->dia_semana ?? '' }},
                                    {{ $traslado->horarioOrigen->horarioBase->hora_inicio_formato ?? '' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-black small">HORARIO DE DESTINO (ACTUAL)</h6>
                                <p class="mb-1">
                                    <i class="ti ti-building-community ti-xs me-1"></i>
                                    <strong class="text-black">Sede:</strong>
                                    {{ $traslado->horarioDestino->horarioBase->aula->sede->nombre ?? 'N/A' }}
                                </p>
                                <p>
                                    <i class="ti ti-clock ti-xs me-1"></i>
                                    {{ $traslado->horarioDestino->horarioBase->dia_semana ?? '' }},
                                    {{ $traslado->horarioDestino->horarioBase->hora_inicio_formato ?? '' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="button" wire:click="closeModal"
                            class="btn btn-outline-secondary rounded-pill">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
