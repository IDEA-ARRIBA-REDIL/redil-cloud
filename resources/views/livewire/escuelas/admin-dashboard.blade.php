<div>
    @section('title', 'Dashboard Administrativo de Escuelas')


@section('isEscuelasModule', true)

    <h4 class="mb-1 fw-semibold text-primary">Dashboard administrativo</h4>
    <p class="text-black">Busque y acceda a cualquier horario del sistema para gestionarlo.</p>

    {{-- Panel de Filtros --}}
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit="buscarHorarios">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="periodo" class="form-label">1. Seleccione período</label>
                        <select id="periodo" class="form-select" wire:model.live="selectedPeriodoId">
                            <option value="">-- Elige un período --</option>
                            @foreach($periodos as $periodo)
                                <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                            @endforeach
                        </select>
                        @error('selectedPeriodoId') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="sede" class="form-label">2. Seleccione sede</label>
                        <select id="sede" class="form-select" wire:model.live="selectedSedeId" @if(empty($sedes)) disabled @endif>
                            <option value="">-- Elige una sede --</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                        @error('selectedSedeId') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="materia" class="form-label">3. Filtre por materia (Opcional)</label>
                        <select id="materia" class="form-select" wire:model="selectedMateriaPeriodoId" @if(empty($materiasPeriodo)) disabled @endif>
                            <option value="">-- Todas las materias --</option>
                            @foreach($materiasPeriodo as $mp)
                                <option value="{{ $mp->id }}">{{ $mp->materia->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary rounded-pill">Buscar horarios</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Panel de Resultados --}}
    @if($horarios !== null)
    <div class="card">
        <div class="card-header"><h5 class="card-title">Resultados de la búsqueda ({{ $horarios->count() }})</h5></div>
        <div class="card-body">
             @forelse ($horarios as $horario)
                <div class="card mb-3" style="{{ $loop->even ? 'background-color: #f3f3f3;' : '' }}">
                    <div class="card-body p-0">
                        <div class="row align-items-center p-3">
                            <div class="col-md-3"><strong>Materia:</strong> {{ $horario->materiaPeriodo->materia->nombre ?? 'N/A' }}</div>
                            <div class="col-md-3"><strong>Horario:</strong> {{ $horario->horarioBase->dia_semana ?? 'N/A' }} {{ $horario->horarioBase->hora_inicio_formato ?? '' }}</div>
                            <div class="col-md-2"><strong>Sede:</strong> {{ $horario->horarioBase->aula->sede->nombre ?? 'N/A' }}</div>
                            <div class="col-md-2"><strong>Aula:</strong> {{ $horario->horarioBase->aula->nombre ?? 'N/A' }}</div>
                            <div class="col-md-2 text-md-end">
                                @if($horario->maestros->isNotEmpty())
                                    <a class="btn btn-outline-secondary rounded-pill"
                                        href="{{ route('maestros.dashboardClase', ['maestro' => $horario->maestros->first(), 'horarioAsignado' => $horario]) }}">
                                        Acceder
                                    </a>
                                @else
                                    <a class="btn btn-outline-secondary rounded-pill"
                                        href="{{ route('maestros.dashboardClase', ['maestro' => $user->id, 'horarioAsignado' => $horario]) }}">
                                        Acceder
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info text-center">No se encontraron horarios con los filtros seleccionados.</div>
            @endforelse
        </div>
    </div>
    @endif
</div>
