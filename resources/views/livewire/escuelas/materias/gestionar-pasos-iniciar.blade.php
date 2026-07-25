@php
    $hasItems = $draftMode ? (count($draftItems) > 0) : ($pasosIniciar->count() > 0);
@endphp

<div class="mb-5" x-data="{ formVisible: {{ $hasItems ? 'false' : 'true' }} }">
    <h5 class="fw-bold text-primary mb-1">Agregar Paso al Iniciar</h5>
    <p class="text-dark small mb-3">Configura los pasos de crecimiento que se deben cambiar al iniciar esta materia</p>

    {{-- Formulario para agregar --}}
    <div x-show="formVisible" x-transition class="row g-3 mb-4 align-items-end">
        <div class="col-md-5 col-sm-12" wire:ignore>
            <label class="form-label text-dark small">Paso de Crecimiento</label>
            <select id="select-materia-paso-iniciar" class="form-select select2 border-1">
                <option value="">Selecciona una opción</option>
                @foreach($pasosDisponibles as $paso)
                    <option value="{{ $paso->id }}">{{ $paso->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-5 col-sm-12" wire:ignore>
            <label class="form-label text-dark small">Estado a Asignar</label>
            <select id="select-materia-estado-iniciar" class="form-select select2 border-1">
                <option value="">Selecciona una opción</option>
                @foreach($estadosDisponibles as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 col-sm-12">
            <button type="button" wire:click="agregarPaso" class="btn btn-outline-secondary rounded-pill w-100">
                Agregar
            </button>
        </div>
    </div>

    {{-- Errores de validación --}}
    <div class="col-12 mt-1 mb-2">
         @error('pasoSeleccionado')
            <span class="text-danger small d-block">{{ $message }} (Paso)</span>
        @enderror
        @error('estadoSeleccionado')
            <span class="text-danger small d-block">{{ $message }} (Estado)</span>
        @enderror
    </div>

    {{-- Tabla de pasos configurados --}}
    @if($hasItems)
        <div class="border rounded-3 p-3 dashed-border" style="border-style: dashed !important; border-color: #e5e7eb !important;">
            <div class="table-responsive">
                <table class="table table-borderless table-hover mb-0">
                    <thead class="text-dark border-bottom">
                        <tr>
                            <th width="50" class="fw-normal">#</th>
                            <th class="fw-normal">Paso</th>
                            <th class="fw-normal">Estado a Asignar</th>
                            <th width="100" class="text-center fw-normal">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($draftMode)
                            @foreach($draftItems as $index => $item)
                                <input type="hidden" name="pasos_iniciar[]" value="{{ $item['paso_id'] }}|{{ $item['estado_id'] }}">
                                <tr>
                                    <td class="align-middle fw-bold">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        <span class="fw-medium text-dark">{{ $item['paso_nombre'] }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge rounded-pill bg-{{ $item['estado_color'] }} text-white" style="font-weight: normal; padding: 0.5em 1em;">
                                            {{ $item['estado_nombre'] }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button
                                            type="button"
                                            wire:click="eliminarPaso('{{ $item['temp_id'] }}')"
                                            class="btn btn-link text-danger p-0">
                                            <i class="ti ti-trash fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @foreach($pasosIniciar as $index => $paso)
                                <tr>
                                    <td class="align-middle fw-bold">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        <span class="fw-medium text-dark">{{ $paso->nombre }}</span>
                                    </td>
                                    <td class="align-middle">
                                        @php
                                            $estado = collect($estadosDisponibles)->firstWhere('id', $paso->pivot->estado_paso_crecimiento_usuario_id);
                                        @endphp
                                        <span class="badge rounded-pill bg-{{ $estado->color ?? 'success' }} text-white" style="font-weight: normal; padding: 0.5em 1em;">
                                            {{ $estado->nombre ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button
                                            type="button"
                                            @click="confirmarEliminacionMateriaPasoIniciar({{ $paso->id }})"
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
                    <i class="ti ti-circle-plus"></i> <span x-text="formVisible ? 'Ocultar formulario' : 'Agregar paso al iniciar'"></span>
                </a>
            </div>
        </div>
    @else
        <div style="border: 2px solid #95CDDF;" class="rounded-3 p-3">
            <div class="d-flex align-items-center text-black mb-2">
                <i class="ti ti-info-circle fs-4 me-2"></i>
                <span class="small">No hay pasos de crecimiento configurados al iniciar. Los usuarios podrán inscribirse sin restricciones de pasos.</span>
            </div>
        </div>
    @endif
</div>


@script
<script>
    $(document).ready(function() {
        // Init Select2 for Paso
        $('#select-materia-paso-iniciar').select2({
            width: '100%',
            placeholder: 'Seleccionar Paso...',
            allowClear: true
        }).on('change', function (e) {
            @this.set('pasoSeleccionado', $(this).val());
        });

        // Init Select2 for Estado
        $('#select-materia-estado-iniciar').select2({
            width: '100%',
             placeholder: 'Seleccionar Estado...',
            allowClear: true
        }).on('change', function (e) {
            @this.set('estadoSeleccionado', $(this).val());
        });

        // Listen for livewire events (e.g. after adding, reset select2)
        Livewire.on('paso-agregado', () => {
             $('#select-materia-paso-iniciar').val(null).trigger('change.select2');
             $('#select-materia-estado-iniciar').val(null).trigger('change.select2');
        });

        window.confirmarEliminacionMateriaPasoIniciar = function(id) {
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
                    @this.call('eliminarPaso', id);
                }
            })
        }

        Livewire.on('msn', (data) => {
            const payload = data[0] ?? data;
            const texto   = payload.msnTexto  ?? payload.msn  ?? '';
            const titulo  = payload.msnTitulo ?? '';
            const icono   = payload.msnIcono  ?? payload.icon ?? 'info';

            Swal.fire({
                icon: icono,
                title: titulo || (icono === 'success' ? '¡Listo!' : icono === 'warning' ? 'Atención' : 'Información'),
                text: texto,
                toast: icono === 'success',
                position: icono === 'success' ? 'top-end' : 'center',
                showConfirmButton: icono !== 'success',
                timer: icono === 'success' ? 2500 : undefined,
                timerProgressBar: icono === 'success',
            });
        });
    });
</script>
@endscript
