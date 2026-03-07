<div wire:key="gestionar-bloques-main-view">
    <div class="row">
        <!-- Columna Izquierda: Lista de Bloques y Creación -->
        <div class="col-12 col-md-5 border-end py-2">
            
            <div class="mb-3">
                <label class="form-label text-black">Nuevo bloque</label>
                <div class="input-group">
                    <input type="text" wire:model="nombre" class="form-control" placeholder="Nombre del bloque" onkeydown="if(event.key === 'Enter'){ @this.call('crearBloque'); }">
                    <button class="btn btn-primary" wire:click="crearBloque" type="button">
                        <i class="ti ti-plus"></i>
                    </button>
                </div>
                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <div class="list-group overflow-auto" style="max-height: 600px;" wire:key="listado-bloques">
                @foreach($bloques as $bloque)
                    <a type="button" 
                        wire:key="bloque-item-{{ $bloque->id }}-{{ $bloque->sedes_count }}"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $bloqueSeleccionadoId == $bloque->id ? 'active' : '' }}"
                        wire:click="seleccionarBloque({{ $bloque->id }})">
                        
                        <div>
                            <span class="fw-semibold text-black">{{ $bloque->nombre }}</span>
                            <br>
                            <small class="text-black">{{ $bloque->sedes_count }} sedes</small>
                        </div>

                        <div class="btn-group">
                            <button type="button" wire:click.stop="eliminarBloque({{ $bloque->id }})" class="btn btn-sm btn-icon ">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </a>
                @endforeach
                @if($bloques->isEmpty())
                     <div class="h-100 d-flex flex-column justify-content-center align-items-center text-muted" style="min-height: 400px;">
                        <i class="ti ti-apps fs-1 mb-2 text-black"></i>
                        <p class="text-black">No hay bloques creados</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Gestión de Sedes del Bloque Seleccionado -->
        <div class="col-12 col-md-7 py-2 px-3">
            @if($bloqueSeleccionado)
                <h4 class="fw-bold mb-3 text-primary">{{ $bloqueSeleccionado->nombre }}</h4>

                <!-- Asignar Sedes -->
                <div class="mb-4 rounded" wire:key="form-asignacion-{{ $bloqueSeleccionado->id }}">
                    <label class="form-label fw-semibold">Agregar sedes disponibles</label>
                    <div class="d-flex gap-2">
                        <div class="flex-grow-1">
                            <select class="select2 form-select" multiple id="selectSedesDisponibles" wire:model="sedesAAsignar">
                                @foreach($sedesDisponibles as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" wire:click="asignarSedes" wire:loading.attr="disabled">
                            <i class="ti ti-check"></i>
                        </button>
                    </div>
                    <small class="text-black">Solo se muestran sedes que no pertenecen a ningún bloque.</small>
                </div>

                <!-- Lista de Sedes Asignadas -->
                <h6 class="fw-bold mt-4">Sedes Asignadas ({{ $sedesAsignadas->count() }})</h6>
                <div class="table-responsive border rounded" style="max-height: 400px;" wire:key="tabla-sedes-{{ $bloqueSeleccionado->id }}-{{ $sedesAsignadas->count() }}">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sede</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sedesAsignadas as $sede)
                                <tr wire:key="sede-asignada-{{ $sede->id }}">
                                    <td>{{ $sede->nombre }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-icon" 
                                            wire:click="desvincularSede({{ $sede->id }})"
                                            title="Desvincular">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-3 text-muted">
                                        No hay sedes asignadas a este bloque.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @else
                <div class="h-100 d-flex flex-column justify-content-center align-items-center text-muted" style="min-height: 400px;">
                    <i class="ti ti-click fs-1 mb-2 text-black"></i>
                    <p class="text-black">Selecciona un bloque de la lista para gestionar sus sedes</p>
                </div>
            @endif
        </div>
    </div>

    @script
    <script>
        $(document).ready(function() {
             initSelect2();
        });

        // Escuchar evento explícito desde PHP para recargar Select2
        Livewire.on('refresh-select2', () => {
            setTimeout(function() {
                initSelect2();
            }, 100);
        });
        
        Livewire.hook('morph.updated',  ({ el }) => {
            initSelect2();
        });

        function initSelect2() {
            var $select = $('#selectSedesDisponibles');
            if ($select.length === 0) return;

            if ($select.hasClass("select2-hidden-accessible")) {
                try {
                    $select.select2('destroy');
                } catch(e) {}
            }
            
            $select.select2({
                placeholder: "Seleccione sedes...",
                allowClear: true,
                width: '100%'
            });

            $select.off('change'); 
            $select.on('change', function (e) {
                var data = $(this).val();
                @this.set('sedesAAsignar', data);
            });
        }

        window.addEventListener('swal:success', event => {
            Swal.fire({
                title: event.detail[0].title,
                text: event.detail[0].text,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        });

        window.addEventListener('swal:error', event => {
            Swal.fire({
                title: event.detail[0].title,
                text: event.detail[0].text,
                icon: 'error'
            });
        });
    </script>
    @endscript
</div>
