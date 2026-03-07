<div>
    <h6 class="mb-3">Pasos de Crecimiento al Iniciar</h6>
    <div class="row g-3 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label">Paso de Crecimiento</label>
            <select wire:model="pasoSeleccionado" class="form-select">
                <option value="">Seleccione paso...</option>
                @foreach($pasosDisponibles as $paso)
                    <option value="{{ $paso->id }}">{{ $paso->nombre }}</option>
                @endforeach
            </select>
            @error('pasoSeleccionado') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-5">
            <label class="form-label">Estado Inicial</label>
            <select wire:model="estadoSeleccionado" class="form-select">
                <option value="">Seleccione estado...</option>
                @foreach($estadosDisponibles as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
            @error('estadoSeleccionado') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-2">
            <button type="button" wire:click="agregarPaso" class="btn btn-primary w-100">
                <i class="ti ti-plus"></i> Agregar
            </button>
        </div>
    </div>

    <!-- Lista de pasos (Draft) -->
    @if($draftMode && !empty($draftItems))
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Paso</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($draftItems as $item)
                        <tr>
                            <td>{{ $item['paso_nombre'] }}</td>
                            <td><span class="badge bg-{{ $item['estado_color'] }} text-white  ">{{ $item['estado_nombre'] }}</span></td>
                            <td>
                                <button type="button" wire:click="confirmarEliminacionNivelPasoIniciar('{{ $item['temp_id'] }}')" class="btn  btn-sm btn-icon  text-danger rounded-pill">
                                    <i class="ti ti-trash"></i>
                                </button>
                                <!-- Inputs ocultos para enviar en el formulario principal -->
                                <input type="hidden" name="pasos_iniciar[{{ $loop->index }}][paso_id]" value="{{ $item['paso_id'] }}">
                                <input type="hidden" name="pasos_iniciar[{{ $loop->index }}][estado_id]" value="{{ $item['estado_id'] }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Lista de pasos (DB) -->
    @if(!$draftMode && $pasosIniciar->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Paso</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pasosIniciar as $paso)
                        <tr>
                            <td>{{ $paso->pivot->indice }}</td>
                            <td>{{ $paso->nombre }}</td>
                            <td>
                                @php $estado = \App\Models\EstadoPasoCrecimientoUsuario::find($paso->pivot->estado_paso_crecimiento_usuario_id); @endphp
                                <span class="badge bg-{{ $item['estado_color'] }} text-white">{{ $estado->nombre ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <button type="button" wire:click="confirmarEliminacionNivelPasoIniciar({{ $paso->id }})" class="btn btn-sm btn-icon  text-danger rounded-pill">
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
