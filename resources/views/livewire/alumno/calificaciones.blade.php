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
                                                    {{-- Botón para EDITAR: Emite un evento 'openEditModal' --}}
                                                    <button
                                                        wire:click="$dispatch('openEditModal', { itemId: {{ $item->id }} })"
                                                        class="btn btn-primary rounded-pill btn-sm">
                                                       <i class="ti ti-edit"></i>
                                                        Editar Respuesta
                                                    </button>
                                                @else
                                                    {{-- Botón para RESPONDER: Emite un evento 'openCreateModal' --}}
                                                    <button
                                                        wire:click="$dispatch('openCreateModal', { itemId: {{ $item->id }} })"
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
    {{-- === MODAL PARA CREAR RESPUESTA            === --}}
    {{-- ============================================= --}}
    {{-- Este bloque solo se renderiza en el HTML si $showCreateModal es true en el componente --}}
    @if($showCreateModal)
        <div class="modal fade show" style="display: block;" tabindex="-1" >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Responder a: {{ $selectedItem?->nombre }}</h5>
                        <button wire:click="$set('showCreateModal', false)" type="button" class="btn-close"></button>
                    </div>
                    <form wire:submit="crearRespuesta">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="respuestaTextoCreate" class="form-label">Tu respuesta:</label>
                                <textarea wire:model="respuestaTexto" id="respuestaTextoCreate" class="form-control" rows="8"></textarea>
                                @error('respuestaTexto') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="archivoCreate" class="form-label">Adjuntar archivo (opcional):</label>
                                <input wire:model="archivo" type="file" id="archivoCreate" class="form-control">
                                <div wire:loading wire:target="archivo" class="small text-muted mt-1">Subiendo...</div>
                                @error('archivo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button wire:click="$set('showCreateModal', false)" type="button" class="btn btn-outline-secondary rounded-pill">Cancelar</button>
                            <button type="submit" class="btn btn-primary rounded-pill">
                                <span wire:loading wire:target="crearRespuesta" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Guardar Respuesta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    {{-- ============================================= --}}
    {{-- === MODAL PARA EDITAR RESPUESTA           === --}}
    {{-- ============================================= --}}
    {{-- Este bloque solo se renderiza si $showEditModal es true --}}
    @if($showEditModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar respuesta para: {{ $selectedItem?->nombre }}</h5>
                    <button wire:click="$set('showEditModal', false)" type="button" class="btn-close"></button>
                </div>
                <form wire:submit="editarRespuesta">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tu respuesta:</label>
                            <textarea wire:model="respuestaTexto" class="form-control" rows="8"></textarea>
                            @error('respuestaTexto') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- === INICIO DE LA LÓGICA CONDICIONAL PARA ARCHIVOS === --}}
                        <div class="mb-3">
                            <label class="form-label">Archivo Adjunto:</label>

                            @if ($existingResponse->enlace_documento_alumno)
                                {{-- Si SÍ existe un archivo, mostramos el enlace y el botón de eliminar --}}
                                <div class="d-flex align-items-center">
                                    <a href="{{ $existingResponse->archivo_url }}" target="_blank" class="btn btn-outline-secondary btn-sm me-2">
                                        <i class="mdi mdi-paperclip me-1"></i>
                                        Ver Archivo Subido
                                    </a>
                                    <button
                                        wire:click.prevent="eliminarArchivo"
                                        wire:confirm="¿Estás seguro de que quieres eliminar este archivo permanentemente?"
                                        type="button" class="btn btn-danger btn-icon btn-sm">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                            @else
                                {{-- Si NO existe un archivo, mostramos el input para subir uno nuevo --}}
                                <input wire:model="archivo" type="file" class="form-control">
                                <div wire:loading wire:target="archivo" class="small text-muted mt-1">Subiendo...</div>
                                @error('archivo') <span class="text-danger small">{{ $message }}</span> @enderror
                            @endif
                        </div>
                        {{-- === FIN DE LA LÓGICA CONDICIONAL PARA ARCHIVOS === --}}

                    </div>
                    <div class="modal-footer">
                        <button wire:click="$set('showEditModal', false)" type="button" class="btn btn-outline-secondary rounded-pill">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">Actualizar Respuesta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>

    @endif

</div>
