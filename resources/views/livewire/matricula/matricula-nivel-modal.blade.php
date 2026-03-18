<div>
    @if ($showModal)
        {{-- MODAL ESTILO BOOTSTRAP 5 / TABLER --}}
        <div class="modal fade show" tabindex="-1"
            style="display: block; background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);" role="dialog"
            aria-modal="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow-lg border-0">

                    {{-- Header del Modal --}}
                    <div class="modal-header border  py-3">
                        <h4 class="modal-title d-flex align-items-center">

                            <span class="text-primary">Matrícula por Grado: <span
                                    class="fw-bold">{{ $nivel->nombre }}</span></span>
                        </h4>
                        <button type="button" class="border btn-close " wire:click="closeModal"
                            aria-label="Close"></button>
                    </div>

                    {{-- Cuerpo del Modal --}}
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar avatar-md me-3">
                                <img src="{{ $estudiante->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($estudiante->nombre(3)) . '&color=7F9CF5&background=EBF4FF' }}"
                                    alt="Avatar" class="rounded-circle">
                            </div>
                            <div>
                                <h6 class="mb-0 text-dark">{{ $estudiante->nombre(3) }}</h6>
                                <small class="text-muted">Estudiante seleccionado para matriculación por nivel</small>
                            </div>
                        </div>

                        <div class="alert alert-info border-info border-opacity-25 bg-info-subtle d-flex align-items-center mb-4"
                            role="alert">
                            <i class="ti ti-info-circle me-3 fs-3"></i>
                            <div>
                                <strong>Aviso:</strong> Para completar la matrícula de este nivel, debe seleccionar un
                                horario para cada una de las materias listadas a continuación.
                            </div>
                        </div>

                        <div class="row g-3">
                            @foreach ($materiasDelNivel as $materia)
                                <div class="col-md-6">
                                    <div
                                        class="card h-100 border border-2 @if (isset($seleccionHorarios[$materia->id]) && !empty($seleccionHorarios[$materia->id])) border-primary bg-primary-subtle @else border-light-subtle @endif">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-white rounded p-1 me-2 border">

                                                </div>
                                                <label
                                                    class="form-label mb-0 fw-bold text-uppercase small tracking-tight">
                                                    {{ $materia->nombre }}
                                                </label>
                                            </div>

                                            <select wire:model.live="seleccionHorarios.{{ $materia->id }}"
                                                class="form-select @if (isset($seleccionHorarios[$materia->id]) && !empty($seleccionHorarios[$materia->id])) border-primary @endif">
                                                <option value="">-- Seleccionar Horario --</option>
                                                @foreach ($materia->horariosDisponibles as $horario)
                                                    <option value="{{ $horario->id }}">
                                                        {{ $horario->horarioBase->dia }} |
                                                        {{ $horario->horarioBase->hora_inicio_formateada }} -
                                                        {{ $horario->horarioBase->aula->nombre }}
                                                        ({{ $horario->cupos_disponibles }} cupos)
                                                    </option>
                                                @endforeach
                                            </select>

                                            @if ($materia->horariosDisponibles->isEmpty())
                                                <div class="mt-2 d-flex align-items-center text-danger small italic">
                                                    <i class="ti ti-alert-triangle me-1"></i> No hay horarios para este
                                                    periodo.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Footer del Modal --}}
                    <div class="modal-footer bg-light-subtle border-top py-3">
                        <button class="btn btn-outline-secondary px-4 rounded-pill" type="button"
                            wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancelar
                        </button>
                        <button class="btn btn-primary px-5 rounded-pill shadow-sm" type="button"
                            wire:click="matricularNivel" wire:loading.attr="disabled">
                            <div wire:loading.remove>
                                <i class="ti ti-checklist me-1"></i> Confirmar Matrícula por Niveles
                            </div>
                            <div wire:loading>
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>
                                Procesando...
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Backdrop explícito para asegurar bloqueo de clics externos --}}
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
