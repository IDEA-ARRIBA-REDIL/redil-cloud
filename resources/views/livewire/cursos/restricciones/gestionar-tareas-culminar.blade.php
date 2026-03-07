<div class="mb-5" x-data="{ formVisible: {{ $tareasCulminar->count() == 0 ? 'true' : 'false' }} }">
    <h5 class="fw-bold text-primary mb-1">Culminación: Tareas de Consolidación</h5>
    <p class="text-dark small mb-3">Al finalizar el curso, se asignarán estas tareas a los usuarios con el estado seleccionado.</p>

    {{-- Formulario --}}
    <div x-show="formVisible" x-transition class="row g-3 mb-4 align-items-end">
        <div class="col-md-5 col-sm-12" wire:ignore>
            <label class="form-label text-dark small">Tarea a Asignar</label>
            <select id="select-tarea-culminar" class="form-select select2 border-1">
                <option value="">Selecciona una opción</option>
                @foreach($tareas as $tarea)
                    <option value="{{ $tarea->id }}">{{ $tarea->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5 col-sm-12" wire:ignore>
            <label class="form-label text-dark small">Estado a Asignar</label>
             <select id="select-estado-tarea-culminar" class="form-select select2 border-1">
                <option value="">Selecciona una opción</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-12">
            <button type="button" wire:click="agregarTarea" class="btn btn-outline-secondary rounded-pill w-100">
                Agregar
            </button>
        </div>
    </div>

    <div class="col-12 mt-1">
        @error('tareaSeleccionada') <span class="text-danger small d-block">{{ $message }}</span> @enderror
        @error('estadoSeleccionado') <span class="text-danger small d-block">{{ $message }}</span> @enderror
    </div>

    {{-- Tabla --}}
    @if($tareasCulminar->count() > 0)
        <div class="border rounded-3 p-3 dashed-border" style="border-style: dashed !important; border-color: #28c76f !important;">
            <div class="table-responsive">
                <table class="table table-borderless table-hover mb-0">
                    <thead class="text-dark border-bottom">
                        <tr>
                            <th class="fw-normal">Tarea</th>
                            <th class="fw-normal">Estado a Asignar</th>
                            <th width="100" class="text-center fw-normal">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tareasCulminar as $tarea)
                            @php
                                $estado = $estados->firstWhere('id', $tarea->pivot->estado_tarea_consolidacion_id);
                            @endphp
                            <tr>
                                <td class="align-middle fw-medium">{{ $tarea->nombre }}</td>
                                <td class="align-middle">
                                    <span class="badge bg-label-success">
                                        {{ $estado ? $estado->nombre : 'Indefinido' }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <button
                                        type="button"
                                        wire:confirm="¿Estás seguro de eliminar esta tarea a culminar?"
                                        wire:click="eliminarTarea({{ $tarea->id }})"
                                        class="btn btn-link text-danger p-0">
                                        <i class="ti ti-trash fs-5"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">
             <a href="#" @click.prevent="formVisible = !formVisible" style="text-decoration: underline;" class="text-secondary">
                <i class="ti ti-circle-plus"></i> <span x-text="formVisible ? 'Ocultar formulario' : 'Agregar tarea a culminar'"></span>
            </a>
        </div>
    @else
         <div style="border: 2px solid #95CDDF;" class="rounded-3 p-3 ">
             <div class="d-flex align-items-center text-black mb-2">
                <i class="ti ti-info-circle fs-4 me-2 text-black"></i>
                <span class="small">No se asignarán tareas de consolidación al finalizar el curso.</span>
            </div>
        </div>
    @endif
</div>

@script
<script>
    $(document).ready(function() {
        $('#select-tarea-culminar').select2({
            width: '100%',
            placeholder: 'Selecciona una opción',
            allowClear: true
        }).on('change', function (e) {
            @this.set('tareaSeleccionada', $(this).val());
        });

        $('#select-estado-tarea-culminar').select2({
            width: '100%',
            placeholder: 'Selecciona una opción',
            allowClear: true
        }).on('change', function (e) {
            @this.set('estadoSeleccionado', $(this).val());
        });
    });
</script>
@endscript
