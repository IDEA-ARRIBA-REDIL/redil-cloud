 <div>
    {{-- Bucle principal para mostrar los cortes académicos --}}
    @foreach ($cortes as $corte)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ $corte->nombre_completo }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @forelse ($corte->itemInstancias as $item)
                        <div class="col-12">
                            <div class="card shadow h-100 card-item-calificacion status-{{ strtolower($item->estado) }}">
                                <div class="card-body">
                                    {{-- Encabezado de la tarjeta con nombre, fecha y estado --}}
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 text-truncate" title="{{ $item->nombre }}">{{ $item->nombre }}</h6>
                                            <small class="text-muted">
                                                Entrega: {{ $item->fecha_fin ? $item->fecha_fin->format('d/m/Y') : 'N/A' }} | Peso: {{ $item->porcentaje }}%
                                            </small>
                                        </div>
                                        <span class="badge bg-label-{{ strtolower($item->estado) == 'calificado' ? 'success' : (strtolower($item->estado) == 'entregado' ? 'info' : 'warning') }}">{{ $item->estado }}</span>
                                    </div>

                                    <div class="mt-3 mb-3 border-top pt-3">
                                        {!! $item->contenido !!}
                                    </div>

                                    {{-- Cuerpo de la tarjeta con la nota y los botones de acción --}}
                                    <div class="d-flex justify-content-between align-items-end mt-4 pt-2">
                                        <div>
                                            <small class="text-muted d-block mb-1">Nota</small>
                                            <h4 class="nota-valor mb-0 {{ $item->nota >= $notaMinimaAprobacion ? 'text-success' : 'text-danger' }}">
                                                {{ $item->nota ?? '--' }}
                                            </h4>
                                        </div>

                                        <div>
                                            {{-- Lógica de botones: La clave del funcionamiento del modal está aquí --}}
                                            @if (is_null($item->nota))
                                                @if ($item->entregado)
                                                    {{-- Botón para EDITAR --}}
                                                    <button
                                                        wire:click="abrirModal({{ $item->id }})"
                                                        class="btn btn-primary rounded-pill btn-sm">
                                                       <i class="ti ti-edit"></i>
                                                        Editar Respuesta
                                                    </button>
                                                @else
                                                    {{-- Botón para RESPONDER --}}
                                                    <button
                                                        wire:click="abrirModal({{ $item->id }})"
                                                        class="btn btn-primary rounded-pill btn-sm">
                                                       <i class="ti ti-text-wrap-disabled"></i>
                                                        Responder
                                                    </button>
                                                @endif
                                            @else
                                                {{-- Botón para VER RESPUESTA: Usa el sistema de modales de Bootstrap, no Livewire --}}
                                                <button class="btn btn-outline-secondary rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#verRespuestaModal"
                                                    data-item-titulo="{{ $item->nombre }}"
                                                    data-respuesta-alumno="{{ $item->respuesta_alumno }}"
                                                    data-feedback-maestro="{{ $item->feedback_maestro }}">
                                                    <i class="ti ti-eye"></i>
                                                    Ver Respuesta
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted fst-italic">Aún no hay actividades calificables para este corte.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach

    {{-- ============================================= --}}
    {{-- === MODAL UNIFICADO PARA RESPONDER/EDITAR === --}}
    {{-- ============================================= --}}
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Responder a: {{ $selectedItem?->nombre }}</h5>
                        <button wire:click="$set('showModal', false)" type="button" class="btn-close"></button>
                    </div>
                    <form wire:submit="guardarRespuesta">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="respuestaTexto" class="form-label">Tu respuesta:</label>
                                <textarea wire:model="respuestaTexto" id="respuestaTexto" class="form-control" rows="8"></textarea>
                                @error('respuestaTexto') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- Componente Alpine.js para la subida de archivos --}}
                            <div class="mb-3" x-data="{
                                subiendo: false,
                                errorMsg: '',
                                subirArchivo(event) {
                                    const file = event.target.files[0];
                                    if (!file) return;

                                    this.subiendo = true;
                                    this.errorMsg = '';

                                    const formData = new FormData();
                                    formData.append('archivo', file);
                                    formData.append('item_id', {{ $selectedItem->id }});
                                    formData.append('periodo_id', {{ $horario->materiaPeriodo->periodo_id }});

                                    fetch('{{ route("alumnos.uploadArchivoRespuesta") }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                            'Accept': 'application/json'
                                        },
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        this.subiendo = false;
                                        if (data.success) {
                                            $wire.set('nombreArchivoSubido', data.nombre);
                                        } else {
                                            this.errorMsg = data.message || 'Error al subir el archivo.';
                                            this.$refs.archivoInput.value = '';
                                        }
                                    })
                                    .catch(error => {
                                        this.subiendo = false;
                                        this.errorMsg = 'Error de conexión al subir el archivo.';
                                        this.$refs.archivoInput.value = '';
                                    });
                                },
                                eliminarArchivoLocal() {
                                    $wire.call('eliminarArchivo');
                                }
                            }">
                                <label class="form-label">Archivo Adjunto (opcional):</label>

                                <template x-if="!$wire.nombreArchivoSubido">
                                    <div>
                                        <input type="file" class="form-control" x-ref="archivoInput" @change="subirArchivo">
                                        <div x-show="subiendo" class="small text-muted mt-2 d-flex align-items-center">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Subiendo archivo...
                                        </div>
                                        <div x-show="errorMsg" class="small text-danger mt-1" x-text="errorMsg"></div>
                                    </div>
                                </template>

                                <template x-if="$wire.nombreArchivoSubido">
                                    <div class="alert alert-success d-flex align-items-center p-2 mb-0">
                                        <i class="mdi mdi-check-circle-outline me-2 fs-4"></i>
                                        <div class="flex-grow-1 text-truncate">
                                            <strong>Archivo adjunto:</strong> <span x-text="$wire.nombreArchivoSubido"></span>
                                        </div>
                                        <button type="button" class="btn-close ms-auto" @click="eliminarArchivoLocal" aria-label="Close"></button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button wire:click="$set('showModal', false)" type="button" class="btn btn-outline-secondary rounded-pill">Cancelar</button>
                            <button type="submit" class="btn btn-primary rounded-pill">
                                <span wire:loading wire:target="guardarRespuesta" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Guardar Respuesta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

</div>
