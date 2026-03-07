<div>
    <!-- Wizard Heads -->
    <div class="bs-stepper wizard-numbered mt-2">
        <div class="bs-stepper-header">
            <div class="step {{ $currentStep == 1 ? 'active' : '' }} {{ $currentStep > 1 ? 'crossed' : '' }}">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">1</span>
                    <span class="bs-stepper-label">
                        <span class="bs-stepper-title">Seleccionar nivel</span>
                        <span class="bs-stepper-title">Seleccionar grado</span>
                        <span class="bs-stepper-subtitle">Selección de grado</span>
                    </span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step {{ $currentStep == 2 ? 'active' : '' }} {{ $currentStep > 2 ? 'crossed' : '' }}">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">2</span>
                    <span class="bs-stepper-label">
                        <span class="bs-stepper-title">Horarios</span>
                        <span class="bs-stepper-subtitle">Arma tu agenda</span>
                    </span>
                </button>
            </div>
             <div class="line"></div>
            <div class="step {{ $currentStep == 3 ? 'active' : '' }}">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">
                        <span class="bs-stepper-title">Confirmación</span>
                        <span class="bs-stepper-subtitle">Finalizar</span>
                    </span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <!-- Paso 1: Selección de Grado -->
            @if ($currentStep == 1)
                <div class="row g-3">
                    <div class="col-12 text-center mb-3">
                        <h5 class="mb-3">Selecciona el grado académico</h5>
                    </div>
                    <div class="row g-3">
                        @forelse ($niveles as $nivel)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 cursor-pointer {{ $nivelSeleccionado == $nivel->id ? 'border-primary border-2 shadow-none' : '' }}"
                                     wire:click="seleccionarNivel({{ $nivel->id }})">
                                    <div class="card-body text-center">
                                        <i class="ti ti-school ti-xl mb-2 {{ $nivelSeleccionado == $nivel->id ? 'text-primary' : 'text-muted' }}"></i>
                                        <h5 class="card-title">{{ $nivel->nombre }}</h5>
                                        <p class="card-text text-muted small">{{ Str::limit($nivel->descripcion, 60) }}</p>
                                        @if($nivelSeleccionado == $nivel->id)
                                            <span class="badge bg-primary">Seleccionado</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center">
                                <p>No hay grados disponibles para inscripción en este momento.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Paso 2: Selección de Horarios -->
            @if ($currentStep == 2)
                <div class="row">
                    <div class="col-12 mb-3">
                        <button class="btn btn-label-secondary btn-sm mb-3" wire:click="irAPaso(1)">
                            <i class="ti ti-arrow-left me-1"></i> Cambiar nivel
                        </button>
                        <h5>Selecciona los horarios para tus materias</h5>
                        <p class="text-muted">Debes seleccionar un horario para cada materia obligatoria.</p>
                    </div>

                    @foreach ($materias as $materia)
                    <div class="col-12 mb-4">
                        <div class="card border {{ $materia->pivot->es_obligatoria ? 'border-label-primary' : '' }}">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <div class="d-flex align-items-center">
                                     <div class="avatar me-2">
                                        <img src="{{ Storage::url(config('app.ruta_almacenamiento') . '/img/materias/' . ($materia->portada ?? 'default.png')) }}" alt class="rounded-circle">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $materia->nombre }}</h6>
                                        @if($materia->pivot->es_obligatoria)
                                            <span class="badge bg-label-primary is-tiny">Obligatoria</span>
                                        @else
                                             <span class="badge bg-label-secondary is-tiny">Opcional</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-3">
                                <div class="row">
                                    @forelse ($materia->horariosDisponibles as $horario)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check custom-option custom-option-basic">
                                                <label class="form-check-label custom-option-content" for="horario_{{ $materia->id }}_{{ $horario->id }}">
                                                    <input name="materia_{{ $materia->id }}" class="form-check-input" type="radio" value="{{ $horario->id }}" id="horario_{{ $materia->id }}_{{ $horario->id }}" wire:model="seleccionHorarios.{{ $materia->id }}">
                                                    <span class="custom-option-header">
                                                        <span class="h6 mb-0">{{ $horario->dia }} - {{ $horario->hora_inicio }}</span>
                                                    </span>
                                                    <span class="custom-option-body">
                                                        <small class="text-muted">{{ $horario->sede->nombre ?? 'Sede Principal' }}</small><br>
                                                        <small>{{ $horario->maestro->name ?? 'Sin asignar' }}</small>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-warning mb-0">No hay horarios disponibles para esta materia.</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="col-12 text-end mt-3">
                        <button class="btn btn-success btn-lg" wire:click="confirmarMatricula" wire:loading.attr="disabled">
                            <span wire:loading.remove>Confirmar inscripción</span>
                            <span wire:loading>Procesando...</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Paso 3: Confirmación -->
             @if ($currentStep == 3)
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-circle-check text-success ti-xl" style="font-size: 5rem;"></i>
                    </div>
                    <h3>¡Inscripción Exitosa!</h3>
                    <p class="text-muted">Te has inscrito correctamente al nivel. Revisa tu horario en tu perfil.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">Ir al Dashboard</a>
                </div>
             @endif
        </div>
    </div>
</div>
