<div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Materia</th>
                    <th>Obligatoria</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody wire:sortable="updateOrden">
                @forelse ($materias as $materia)
                    <tr wire:sortable.item="{{ $materia->id }}" wire:key="materia-{{ $materia->id }}">
                        <td>
                            <i class="ti ti-grip-vertical me-2 cursor-move" wire:sortable.handle></i>
                            {{ $materia->pivot->orden ?? '-' }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-2">
                                    <img src="{{ Storage::url(config('app.ruta_almacenamiento') . '/img/materias/' . ($materia->portada ?? 'default.png')) }}" alt class="rounded-circle">
                                </div>
                                <div>
                                    <strong>{{ $materia->nombre }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                    wire:click="toggleObligatoria({{ $materia->id }})"
                                    {{ $materia->pivot->es_obligatoria ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill text-danger"
                                wire:click="desvincular({{ $materia->id }})"
                                wire:confirm="¿Seguro que deseas desvincular esta materia del grado?">
                                <i class="ti ti-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <div class="text-muted">No hay materias asignadas a este grado.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-end mt-3">
    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#vincularMateriaModal">
        <i class="ti ti-link me-1"></i> Vincular Materia al Grado
    </button>
</div>
