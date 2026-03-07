<div>
    @if ($showModal && $matriculaActual)
        <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Trasladar matrícula</h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1 text-black"><strong>Estudiante:</strong> {{ $matriculaActual->user->nombre(3) }}
                        </p>
                        <p><strong>Materia:</strong>
                            {{ $matriculaActual->horarioMateriaPeriodo->materiaPeriodo->materia->nombre }}</p>
                        <hr>
                        <h6 class="text-black small">Horario actual</h6>
                        <p>{{ $matriculaActual->horarioMateriaPeriodo->horarioBase->aula->sede->nombre }} /
                            {{ $matriculaActual->horarioMateriaPeriodo->horarioBase->aula->nombre }}<br>
                            {{ $matriculaActual->horarioMateriaPeriodo->horarioBase->dia_semana }},
                            {{ $matriculaActual->horarioMateriaPeriodo->horarioBase->hora_inicio_formato }}</p>
                        <hr>
                        <h6 class="text-black small">Nuevo horario</h6>

                        <div class="mb-3">
                            <label for="sedeId" class="form-label">1. Seleccione la nueva sede</label>
                            <select required id="sedeId" wire:model.live="sedeId" class="form-select">
                                <option value="">-- Seleccionar sede --</option>
                                @foreach ($sedesDisponibles as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($sedeId)
                            <div class="mb-3">
                                <label for="horarioDestinoId" class="form-label">2. Seleccione el nuevo horario</label>
                                <div wire:loading wire:target="sedeId" class="text-muted small">Cargando horarios...
                                </div>
                                <select required id="horarioDestinoId" wire:model="horarioDestinoId" class="form-select"
                                    wire:loading.remove>
                                    <option value="">-- Seleccionar horario --</option>
                                    @foreach ($horariosDisponibles as $horario)
                                        @php
                                            $nombresMaestros = $horario->maestros->pluck('user.name')->implode(', ');
                                        @endphp
                                        <option value="{{ $horario->id }}">
                                            {{ $horario->horarioBase->aula->nombre }} |
                                            {{ $horario->horarioBase->dia_semana }}
                                            ({{ $horario->horarioBase->hora_inicio_formato }})
                                            @if (!empty($nombresMaestros))
                                                | M: {{ $nombresMaestros }}
                                            @endif
                                            <span class="text-success fw-bold">[{{ $horario->cupos_disponibles }}
                                                cupos]</span>
                                        </option>
                                    @endforeach
                                </select>
                                @error('horarioDestinoId')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer justify-content-start">
                        <button type="button" wire:click="closeModal"
                            class="btn btn-outline-secondary mb-3  rounded-pill">Cancelar</button>
                        <button type="button" wire:click="trasladar" class="btn mb-3  btn-primary rounded-pill">
                            <span wire:loading.remove>Confirmar traslado</span>
                            <span wire:loading>Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>


    @endif
</div>
