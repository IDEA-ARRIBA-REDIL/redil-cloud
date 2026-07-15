<div class="row">
    <div class="row mb-3">

        <h5 class="fw-semibold text-primary mb-1">Agregar Paso al Iniciar</h5>
         <p class="text-dark small mb-3">Configura los pasos de crecimiento que el usuario se deben cambiar al iniciar  esta materia</p>
        <div class="col-12 col-md-5" wire:ignore>
            <select id="select-materia-paso-iniciar" class="form-select select2 border-1">
                <option value="">Seleccionar Paso...</option>
                @foreach($pasosDisponibles as $paso)
                    <option value="{{ $paso->id }}">{{ $paso->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-5" wire:ignore>
            <select id="select-materia-estado-iniciar" class="form-select select2 border-1">
                <option value="">Seleccionar Estado...</option>
                @foreach($estadosDisponibles as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2">
            <button type="button" wire:click="agregarPaso" class="btn btn-outline-secondary rounded-pill w-100">
                Agregar
            </button>
        </div>


    @if(($draftMode && count($draftItems) > 0) || (!$draftMode && $pasosIniciar->count() > 0))
    <div class="col-12">
        <div class="table-responsive border rounded p-3 bg-white">
            <h6 class="mb-3">Pasos configurados al iniciar</h6>
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Paso</th>
                        <th>Estado a asignar</th>
                        <th class="text-center" style="width: 100px;">Acciones</th>
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
    </div>
    @else
     <div style="border: 2px solid #95CDDF;" class="rounded-3 p-3 m-3 ">
            <div class="d-flex align-items-center text-black mb-2">
                <i class="ti ti-info-circle fs-4 me-2"></i>
                <span class="small">No hay pasos de crecimiento configurados al iniciar. Los usuarios podrán inscribirse sin restricciones de pasos.</span>
            </div>

        </div>
    @endif
    </div>
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
