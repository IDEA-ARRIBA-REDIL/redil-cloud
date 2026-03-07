<div>
    <form wire:submit.prevent="guardar">


            {{-- Columna Derecha/Abajo: Qué aprenderás --}}
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-black fw-semibold mb-0">Qué aprenderás en este curso ({{ count($aprendizajes) }})</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="agregarAprendizaje">
                        <i class="ti ti-plus me-1"></i> Nuevo
                    </button>
                </div>

                <div class="form-text mb-3">
                    Agrega los puntos clave que el estudiante dominará al finalizar. Arrastra para reordenar.
                </div>

                <ul id="lista-aprendizajes" class="list-group list-group-flush">
                    @foreach($aprendizajes as $index => $item)
                        <li class="list-group-item d-flex align-items-center p-2 border rounded mb-2 draggable-item"
                            wire:key="aprendizaje-{{ $index }}"
                            data-index="{{ $index }}">

                            {{-- Handle para arrastrar --}}
                            <span class="cursor-move me-3 text-muted drag-handle">
                                <i class="ti ti-grip-vertical"></i>
                            </span>

                            <div class="flex-grow-1">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text border-0 ps-0 bg-transparent text-muted">0{{ $index + 1 }}</span>
                                    <input type="text"
                                           class="form-control border-0 bg-transparent"
                                           wire:model="aprendizajes.{{ $index }}.texto"
                                           placeholder="Ej: Aprenderás a dominar..."
                                           maxlength="120">
                                    <span class="input-group-text border-0 bg-transparent text-muted text-xs">
                                        {{ strlen($item['texto']) }}/120
                                    </span>
                                </div>
                            </div>

                            <button type="button" class="btn btn-icon btn-text-danger rounded-pill btn-sm ms-2"
                                    wire:click="eliminarAprendizaje({{ $index }})"
                                    title="Eliminar punto">
                                <i class="ti ti-trash"></i>
                            </button>
                        </li>
                    @endforeach

                    @if(count($aprendizajes) === 0)
                        <li class="text-center p-4 text-muted bg-lighter rounded border border-dashed">
                            No hay puntos de aprendizaje definidos. <a href="javascript:void(0)" wire:click="agregarAprendizaje">Agrega uno para comenzar.</a>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Agregar
                </button>
            </div>
        </div>
    </form>
</div>

@script
<script>
    document.addEventListener('livewire:initialized', () => {
        // --- Inicializar SortableJS ---
        const container = document.getElementById('lista-aprendizajes');
        if (container) {
            Sortable.create(container, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-label-primary',
                onEnd: function (evt) {
                    const items = container.querySelectorAll('.draggable-item');
                    let orderedIds = [];
                    items.forEach(item => {
                         orderedIds.push(item.getAttribute('data-index'));
                    });
                    $wire.call('reordenar', orderedIds);
                }
            });
        }

    });
</script>
@endscript
