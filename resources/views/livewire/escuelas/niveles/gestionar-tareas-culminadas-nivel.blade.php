<div>
    <h6 class="mb-3">Tareas de Consolidación a Culminar al Cerrar Grado</h6>
    <div class="row g-3 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label">Tarea</label>
            <select wire:model="tareaSeleccionada" class="form-select">
                <option value="">Seleccione tarea...</option>
                @foreach($tareasDisponibles as $tarea)
                    <option value="{{ $tarea->id }}">{{ $tarea->nombre }}</option>
                @endforeach
            </select>
            @error('tareaSeleccionada') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-5">
            <label class="form-label">Estado Final</label>
            <select wire:model="estadoSeleccionado" class="form-select">
                <option value="">Seleccione estado...</option>
                @foreach($estadosDisponibles as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
            @error('estadoSeleccionado') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-2">
            <button type="button" wire:click="agregarTarea" class="btn btn-primary w-100">
                <i class="ti ti-plus"></i> Agregar
            </button>
        </div>
    </div>

    @if($draftMode && !empty($draftItems))
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Tarea</th>
                        <th>Estado Final</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($draftItems as $item)
                        <tr>
                            <td>{{ $item['tarea_nombre'] }}</td>
                            <td><span class="badge bg-{{ $item['estado_color'] }} text-white">{{ $item['estado_nombre'] }}</span></td>
                            <td>
                                <button type="button" wire:click="confirmarEliminacionNivelTareaCulminada('{{ $item['temp_id'] }}')" class="btn btn-sm btn-icon btn-text-secondary text-danger rounded-pill">
                                    <i class="ti ti-trash"></i>
                                </button>
                                <input type="hidden" name="tareas_culminar[{{ $loop->index }}][tarea_id]" value="{{ $item['tarea_id'] }}">
                                <input type="hidden" name="tareas_culminar[{{ $loop->index }}][estado_id]" value="{{ $item['estado_id'] }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!$draftMode && $tareasCulminadas->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Tarea</th>
                        <th>Estado Final</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tareasCulminadas as $tarea)
                        <tr>
                            <td>{{ $tarea->tareaConsolidacion->nombre }}</td>
                            <td><span class="badge bg-{{ $tarea->estadoTareaConsolidacion->color ?? 'primary' }} text-white">{{ $tarea->estadoTareaConsolidacion->nombre }}</span></td>
                            <td>
                                <button type="button" wire:click="confirmarEliminacionNivelTareaCulminada({{ $tarea->id }})" class="btn btn-sm btn-icon  text-danger rounded-pill">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
