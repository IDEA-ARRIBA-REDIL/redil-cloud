<div>
    @if ($mostrarModal)
        <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(110, 110, 110, 0.5);"
            role="dialog">
            <div class="modal-dialog modal-xl modal-simple modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="asignarHorario">
                        <div class="modal-body">
                            <button type="button" class="btn-close" wire:click="cerrarModal"
                                aria-label="Close"></button>
                            <div class="text-center mb-4">
                                <h3 class="mb-2"><i class="ti ti-plus"></i> Asignar horario a
                                    {{ $maestro?->user?->name ?? 'Maestro' }}</h3>
                                <p class="text-muted">Todos los campos con son obligatorios </p>
                            </div>

                            <div class="mb-3">
                                <label for="periodoIdSeleccionado" class="form-label">1. Selecciona un periodo</label>
                                <select required wire:model.live="periodoIdSeleccionado" id="periodoIdSeleccionado"
                                    class="form-select @error('periodoIdSeleccionado') is-invalid @enderror">
                                    <option value="">Selecciona un periodo</option>
                                    @foreach ($periodos as $periodo)
                                        <option value="{{ $periodo['id'] }}">{{ $periodo['nombre'] }}
                                            ({{ \Carbon\Carbon::parse($periodo['fecha_inicio'])->format('d/m/Y') }} -
                                            {{ \Carbon\Carbon::parse($periodo['fecha_fin'])->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('periodoIdSeleccionado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="materiaPeriodoIdSeleccionada" class="form-label">2. Selecciona una materia
                                    del periodo</label>
                                <select required wire:model.live="materiaPeriodoIdSeleccionada"
                                    id="materiaPeriodoIdSeleccionada"
                                    class="form-select @error('materiaPeriodoIdSeleccionada') is-invalid @enderror"
                                    @if (empty($periodoIdSeleccionado) || empty($materiasPeriodo)) disabled @endif>
                                    <option value="">
                                        {{ empty($periodoIdSeleccionado) ? 'Selecciona un periodo primero' : (empty($materiasPeriodo) && $periodoIdSeleccionado ? 'No hay materias en este periodo' : 'Selecciona una materia') }}
                                    </option>
                                    @foreach ($materiasPeriodo as $materiaP)
                                        <option value="{{ $materiaP['id'] }}">{{ $materiaP['nombre_display'] }}</option>
                                    @endforeach
                                </select>
                                @error('materiaPeriodoIdSeleccionada')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="sedeIdSeleccionada" class="form-label">3. Selecciona una sede</label>
                                <select required wire:model.live="sedeIdSeleccionada" id="sedeIdSeleccionada"
                                    class="form-select @error('sedeIdSeleccionada') is-invalid @enderror"
                                    @if (empty($materiaPeriodoIdSeleccionada) || empty($sedesDisponibles)) disabled @endif>
                                    <option value="">
                                        {{ empty($materiaPeriodoIdSeleccionada) ? 'Selecciona una materia primero' : (empty($sedesDisponibles) && $materiaPeriodoIdSeleccionada ? 'No hay sedes para esta materia' : 'Selecciona una sede') }}
                                    </option>
                                    @foreach ($sedesDisponibles as $sede)
                                        <option value="{{ $sede['id'] }}">{{ $sede['nombre'] }}</option>
                                    @endforeach
                                </select>
                                @error('sedeIdSeleccionada')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="horarioMateriaPeriodoIdSeleccionado" class="form-label">4. Selecciona un
                                    horario</label>
                                <select required wire:model="horarioMateriaPeriodoIdSeleccionado"
                                    id="horarioMateriaPeriodoIdSeleccionado"
                                    class="form-select @error('horarioMateriaPeriodoIdSeleccionado') is-invalid @enderror"
                                    @if (empty($sedeIdSeleccionada) || empty($horariosMateriaPeriodoDisponibles)) disabled @endif> {{-- CAMBIO DE CONDICIÓN DISABLED (Comentario original mantenido) --}}
                                    <option value="">
                                        {{ empty($sedeIdSeleccionada) ? 'Selecciona una sede primero' : (empty($horariosMateriaPeriodoDisponibles) && $sedeIdSeleccionada ? 'No hay horarios disponibles para esta sede' : 'Selecciona un horario') }}
                                    </option>
                                    @foreach ($horariosMateriaPeriodoDisponibles as $horario)
                                        <option value="{{ $horario->id }}">{{ $horario->horarioBase->dia_semana }} //
                                            {{ $horario->horarioBase->hora_inicio }}
                                            -{{ $horario->horarioBase->hora_fin }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('horarioMateriaPeriodoIdSeleccionado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary rounded-pill" wire:loading.attr="disabled"
                                wire:target="asignarHorario">
                                <span wire:loading wire:target="asignarHorario" class="spinner-border spinner-border-sm"
                                    role="status" aria-hidden="true"></span>
                                <span wire:loading.remove wire:target="asignarHorario">Asignar horario</span>
                                <span wire:loading wire:target="asignarHorario">Asignando...</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary rounded-pill"
                                wire:click="cerrarModal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> {{-- La condición @if ($mostrarModal) aquí es redundante si el bloque principal ya la evalúa.
             Se mantiene según la estructura original. --}}
        @if ($mostrarModal)
            <div class="modal-backdrop fade show" id="backdrop" style="display: block;"></div>
        @endif
    @endif
</div>
