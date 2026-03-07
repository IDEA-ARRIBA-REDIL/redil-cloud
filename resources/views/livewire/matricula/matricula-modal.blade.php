<div>
    @if ($showModal)
        <div class="modal  fade show" tabindex="-1" style="display: block; background-color: rgba(110, 110, 110, 0.5);"
            role="dialog">

            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Matricular a <span class="fw-bold">{{ $usuario->nombre(3) }}</span></h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-black">Materia: <span class="fw-bold">{{ $materia->nombre }}</span></p>

                        <hr>

                        {{-- ... El resto de tus selects (periodo, sede, horario) va aquí ... --}}
                        {{-- (El código interno no necesita cambios) --}}
                        <div class="mb-3">
                            <label for="periodoId" class="form-label">1. Seleccione el periodo</label>
                            <select id="periodoId" wire:model.live="periodoId" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                @foreach ($periodos as $periodo)
                                    <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('periodoId')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Este bloque solo se muestra si se ha seleccionado un periodo --}}
                        @if ($periodoId)
                            <div class="mb-3">
                                <label for="sedeId" class="form-label">2. Seleccione la sede</label>
                                <div wire:loading wire:target="periodoId" class="text-muted">Cargando sedes...</div>
                                <select id="sedeId" wire:model.live="sedeId" class="form-select" wire:loading.remove>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('sedeId')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Este bloque solo se muestra si se ha seleccionado una sede --}}
                        @if ($sedeId)
                            <div class="mb-3">
                                <label for="horarioId" class="form-label">3. Seleccione el horario</label>
                                <div wire:loading wire:target="sedeId" class="text-muted">Cargando horarios...</div>

                                <select id="horarioId" wire:model="horarioId" class="form-select" wire:loading.remove>
                                    <option value="">-- Seleccionar --</option>

                                    {{-- CAMBIO: Lógica para mostrar los nombres de los maestros --}}
                                    @foreach ($horarios as $horario)
                                        @php
                                            // Usamos la potencia de las colecciones de Laravel para obtener los nombres:
                                            // 1. 'map' recorre cada maestro.
                                            // 2. '->user->nombre(3)' obtiene el nombre completo del usuario asociado al maestro.
                                            // 3. 'implode' une todos los nombres en un solo string, separado por comas.
                                            $nombresMaestros = $horario->maestros
                                                ->map(function ($maestro) {
                                                    return optional($maestro->user)->nombre(3) ??
                                                        'Maestro no encontrado';
                                                })
                                                ->implode(', ');
                                        @endphp
                                        <option value="{{ $horario->id }}">
                                            {{-- Detalles del Horario --}}
                                            {{ $horario->horarioBase->aula->nombre }} |
                                            {{ $horario->horarioBase->dia_semana }}
                                            {{ $horario->horarioBase->hora_inicio_formato }} -
                                            {{ $horario->horarioBase->hora_fin_formato }}
                                            {{-- Mostramos los maestros si existen --}}
                                            @if (!empty($nombresMaestros))
                                                <span class="text-muted">| M: {{ $nombresMaestros }}</span>
                                            @else
                                                <span class="text-danger">| Sin maestro asignado</span>
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                @error('horarioId')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                        @if ($configuracion->opciones_extra_matriculas_escuelas == true)
                            <div class="mb-3">
                                <label for="estadoPago" class="form-label">Estado del pago (Opcional)</label>
                                <select id="estadoPago" wire:model="estadoPago" class="form-select">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="pagada">Pagada</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="observacion" class="form-label">Observación (Opcional)</label>
                                <textarea id="observacion" wire:model="observacion" class="form-control" rows="3"></textarea>
                            </div>
                        @endif
                        {{ $observacion }}


                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="button" wire:click="matricular" class="btn mb-3 btn-primary rounded-pill"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>Confirmar matrícula</span>
                            <span wire:loading>Procesando...</span>
                        </button>
                        <button type="button" wire:click="closeModal"
                            class="btn btn-outline-secondary mb-3  rounded-pill">Cancelar</button>

                    </div>
                </div>
            </div>
        </div>
        {{-- Y aquí un backdrop que sí funciona porque está fuera del div del modal --}}

    @endif


</div>
