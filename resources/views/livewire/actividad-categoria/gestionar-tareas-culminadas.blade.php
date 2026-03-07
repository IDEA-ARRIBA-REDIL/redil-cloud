<div>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Tareas de Consolidación - A Culminar (Nivel Categoría)</h5>
            <p class="text-muted small mb-0">Configura las tareas que se asignarán/actualizarán al confirmar asistencia en esta categoría</p>
        </div>
        <div class="card-body">
            {{-- Formulario para agregar --}}
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label">Tarea de Consolidación <span class="text-danger">*</span></label>
                    <select wire:model="tareaSeleccionada" class="form-select">
                        <option value="">Seleccionar tarea...</option>
                        @foreach($tareas as $tarea)
                            <option value="{{ $tarea->id }}">{{ $tarea->nombre }}</option>
                        @endforeach
                    </select>
                    @error('tareaSeleccionada')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-5">
                    <label class="form-label">Estado a Asignar <span class="text-danger">*</span></label>
                    <select wire:model="estadoSeleccionado" class="form-select">
                        <option value="">Seleccionar estado...</option>
                        @foreach($estados as $estado)
                            <option value="{{ $estado->id }}">
                                {{ $estado->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('estadoSeleccionado')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button wire:click="agregarTarea" class="btn btn-outline-secondary rounded-pill  w-100">
                        <i class="ti ti-plus me-1"></i> Agregar
                    </button>
                </div>
            </div>

            {{-- Tabla de tareas configuradas --}}
            @if($tareasCulminadas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Tarea</th>
                                <th>Estado a Asignar</th>
                                <th width="100" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tareasCulminadas as $index => $tarea)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $tarea->tareaConsolidacion->nombre }}</strong>
                                        @if($tarea->tareaConsolidacion->descripcion)
                                            <br><small class="text-muted">{{ $tarea->tareaConsolidacion->descripcion }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $tarea->estadoTarea->color }}">
                                            {{ $tarea->estadoTarea->nombre }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button
                                            wire:click="eliminarTarea({{ $tarea->id }})"
                                            wire:confirm="¿Estás seguro de eliminar esta tarea a culminar?"
                                            class="btn btn-sm btn-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="ti ti-info-circle me-2"></i>
                    No hay tareas de consolidación configuradas para culminar en esta categoría.
                </div>
            @endif
        </div>
    </div>
</div>
