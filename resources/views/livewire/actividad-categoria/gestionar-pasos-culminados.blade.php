<div>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Pasos de Crecimiento - A Culminar (Nivel Categoría)</h5>
            <p class="text-muted small mb-0">Configura los pasos que se asignarán/actualizarán al confirmar asistencia en esta categoría</p>
        </div>
        <div class="card-body">
            {{-- Formulario para agregar --}}
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label">Paso de Crecimiento <span class="text-danger">*</span></label>
                    <select wire:model="pasoSeleccionado" class="form-select">
                        <option value="">Seleccionar paso...</option>
                        @foreach($pasos as $paso)
                            <option value="{{ $paso->id }}">{{ $paso->nombre }}</option>
                        @endforeach
                    </select>
                    @error('pasoSeleccionado')
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
                    <button wire:click="agregarPaso" class="btn btn-outline-secondary rounded-pill w-100">
                        <i class="ti ti-plus me-1"></i> Agregar
                    </button>
                </div>
            </div>

            {{-- Tabla de pasos configurados --}}
            @if($pasosCulminados->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Paso</th>
                                <th>Estado a Asignar</th>
                                <th width="100" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pasosCulminados as $index => $paso)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $paso->nombre }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $paso->pivot->estadoPasoCrecimiento->color ?? '#666' }}">
                                            {{ $paso->pivot->estadoPasoCrecimiento->nombre ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button
                                            wire:click="eliminarPaso({{ $paso->id }})"
                                            wire:confirm="¿Estás seguro de eliminar este paso a culminar?"
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
                    No hay pasos de crecimiento configurados para culminar en esta categoría.
                </div>
            @endif
        </div>
    </div>
</div>
