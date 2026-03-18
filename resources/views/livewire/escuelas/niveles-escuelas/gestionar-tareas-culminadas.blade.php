<div class="mb-5" x-data="{ formVisible: {{ $tareasCulminadas->count() == 0 ? 'true' : 'false' }} }">
    <h5 class="fw-bold text-primary mb-1">Tareas de Consolidación - Al Culminar</h5>
    <p class="text-dark small mb-3">Configura las tareas que se marcarán como completados cuando el usuario apruebe este
        grado</p>

    {{-- Formulario para agregar --}}
    <div x-show="formVisible" x-transition class="row g-3 mb-4 align-items-end">
        <div class="col-md-5 col-sm-12" wire:ignore>
            <label class="form-label text-dark small">Tarea de Consolidación</label>
            <select id="select-nivel-tarea-culminada" class="form-select select2-livewire border-1">
                <option value="">Selecciona una opción</option>
                @foreach ($tareas as $tarea)
                    <option value="{{ $tarea->id }}">{{ $tarea->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-5 col-sm-12" wire:ignore>
            <label class="form-label text-dark small">Estado a Asignar</label>
            <select id="select-nivel-estado-tarea-culminada" class="form-select select2-livewire border-1">
                <option value="">Selecciona una opción</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado->id }}">
                        {{ $estado->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 col-sm-12">
            <button type="button" wire:click="agregarTarea" class="btn btn-outline-secondary rounded-pill w-100">
                Agregar
            </button>
        </div>
    </div>

    {{-- Errores de validación --}}
    <div class="col-12 mt-1">
        @error('tareaSeleccionada')
            <span class="text-danger small d-block">{{ $message }} (Tarea)</span>
        @enderror
        @error('estadoSeleccionado')
            <span class="text-danger small d-block">{{ $message }} (Estado)</span>
        @enderror
    </div>

    {{-- Tabla de tareas configuradas --}}
    @if ($tareasCulminadas->count() > 0 || count($draftItems) > 0)
        <div class="border rounded-3 p-3 dashed-border"
            style="border-style: dashed !important; border-color: #e5e7eb !important;">
            <div class="table-responsive">
                <table class="table table-borderless table-hover mb-0">
                    <thead class="text-dark border-bottom">
                        <tr>
                            <th width="50" class="fw-normal">#</th>
                            <th class="fw-normal">Tarea</th>
                            <th class="fw-normal">Estado a Asignar</th>
                            <th width="100" class="text-center fw-normal">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($draftMode)
                            @foreach ($draftItems as $index => $item)
                                <input type="hidden" name="tareas_culminadas[]"
                                    value="{{ $item['tarea_id'] }}|{{ $item['estado_id'] }}">
                                <tr>
                                    <td class="align-middle fw-bold">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        <span class="fw-medium text-dark">{{ $item['tarea_nombre'] }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge rounded-pill bg-{{ $item['estado_color'] }} text-white"
                                            style="font-weight: normal; padding: 0.5em 1em;">
                                            {{ $item['estado_nombre'] }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" wire:click="eliminarTarea('{{ $item['temp_id'] }}')"
                                            class="btn btn-link text-danger p-0">
                                            <i class="ti ti-trash fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($tareasCulminadas as $index => $tarea)
                                <tr>
                                    <td class="align-middle fw-bold">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        <span
                                            class="fw-medium text-dark">{{ $tarea->tareaConsolidacion->nombre }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span
                                            class="badge rounded-pill bg-{{ $tarea->estadoTarea->color ?? 'success' }} text-white"
                                            style="font-weight: normal; padding: 0.5em 1em;">
                                            {{ $tarea->estadoTarea->nombre ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button"
                                            @click="confirmarEliminacionNivelTareaCulminada({{ $tarea->id }})"
                                            class="btn btn-link text-danger p-0">
                                            <i class="ti ti-trash fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="#" @click.prevent="formVisible = !formVisible" style="text-decoration: underline;">
                    <i class="ti ti-circle-plus"></i> <span
                        x-text="formVisible ? 'Ocultar formulario' : 'Agregar tarea a culminar'"></span>
                </a>
            </div>
        </div>
    @else
        <div style="border: 2px solid #95CDDF;" class="rounded-3 p-3 ">
            <div class="d-flex align-items-center text-black mb-2">
                <i class="ti ti-info-circle fs-4 me-2"></i>
                <span class="small">No hay tareas configuradas para marcarse al culminar el grado.</span>
            </div>

        </div>
    @endif
</div>

@script
    <script>
        $(document).ready(function() {
            function initSelect2() {
                $('.select2-livewire').select2({
                    width: '100%',
                    placeholder: 'Selecciona una opción',
                    allowClear: true,
                    dropdownParent: $('#formNuevoNivel').length ? $('#formNuevoNivel') : $('#formActualizarNivel')
                });
            }

            initSelect2();

            $('#select-nivel-tarea-culminada').on('change', function(e) {
                @this.set('tareaSeleccionada', $(this).val());
            });

            $('#select-nivel-estado-tarea-culminada').on('change', function(e) {
                @this.set('estadoSeleccionado', $(this).val());
            });

            Livewire.on('tarea-agregada', () => {
                $('.select2-livewire').val(null).trigger('change');
            });

            window.confirmarEliminacionNivelTareaCulminada = function(id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '¡Sí, eliminar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarTarea', id);
                    }
                })
            }

            Livewire.on('msn', (data) => {
                let msn = data.msn || (data[0] ? data[0].msn : null);
                let icon = data.icon || (data[0] ? data[0].icon : 'info');

                if (icon === 'success' && msn && msn.includes('eliminad')) {
                    Swal.fire(
                        '¡Eliminado!',
                        msn,
                        'success'
                    )
                }
            });
        });
    </script>
@endscript
