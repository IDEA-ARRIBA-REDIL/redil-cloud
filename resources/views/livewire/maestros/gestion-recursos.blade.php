<div>
    {{-- Encabezado de la página --}}
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1 text-primary">
                Gestión de Recursos: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
            </h4>
            <p class="mb-0"><small>Aquí puedes gestionar los recursos de para tus alumnos.</small></p>
        </div>
        <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary rounded-pill" wire:click="abrirModalCrear">
                <i class="mdi mdi-plus me-1"></i> Crear Nuevo Recurso
            </button>
            </div>
    </div>

    {{-- Tarjeta Principal --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="mdi mdi-book-open-variant-outline me-2"></i>Listado de Recursos</h5>
            
            
        </div>
        <div class="card-body">
            <div class="list-group">
                @forelse ($recursos as $recurso)
                    <div class="list-group-item list-group-item-action p-3">
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center">
                            
                            {{-- SECCIÓN IZQUIERDA: Ícono e Información --}}
                            <div class="d-flex align-items-center flex-grow-1">
                                {{-- Lógica de íconos de tu tabla original --}}
                                <div class="me-3">
                                    @if ($recurso->tipo === 'Video')
                                        <i class="ti ti-video fs-2 text-primary"></i>
                                    @elseif ($recurso->tipo === 'Documento')
                                        <i class="ti ti-file-text fs-2 text-primary"></i>
                                    @elseif ($recurso->tipo === 'Enlace')
                                        <i class="ti ti-link fs-2 text-primary"></i>
                                    @else
                                        <i class="ti ti-file fs-2 text-primary"></i>
                                    @endif
                                </div>
                                <div>
                                    {{-- Estilo de Título y Descripción del nuevo diseño --}}
                                    <h6 class="mb-1">{{ $recurso->nombre }}</h6>
                                    <small class="text-muted d-block mb-1">{{ Str::limit($recurso->descripcion, 80) }}</small>
                                    
                                   
                                </div>
                            </div>

                            {{-- SECCIÓN DERECHA: Botones de Acción --}}
                            {{-- Mantenemos tus botones originales con su funcionalidad exacta --}}
                            <div class="d-flex align-items-center mt-3 mt-sm-0 ms-sm-auto">
                                <button wire:click="abrirModalEditar({{ $recurso->id }})" class="btn me-1 btn-sm btn-icon btn-outline-secondary" data-bs-toggle="tooltip" title="Editar Contenido">
                                    <i class="ti ti-pencil"></i>
                                </button>
                               
                                <button 
                                    wire:click="eliminarRecurso( {{ $recurso->id }})" 
                                    class="btn btn-sm btn-icon btn-outline-danger" 
                                    data-bs-toggle="tooltip" 
                                    title="Eliminar Recurso">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                @empty
                    {{-- Mensaje de "vacío" mejorado del nuevo diseño --}}
                    <div class="text-center p-5">
                        <i class="ti ti-folder-off ti-lg text-muted"></i>
                        <h5 class="mt-3">Aún no hay recursos</h5>
                        <p class="text-muted">Haz clic en "Crear Nuevo Recurso" para empezar.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- MODAL PARA CREAR/EDITAR RECURSO --}}
    @if($showModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form wire:submit.prevent="guardarRecurso">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $recursoId ? 'Editar Recurso' : 'Crear Nuevo Recurso' }}</h5>
                        <button type="button" class="btn-close" wire:click="cerrarModal"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Contenido del modal --}}
                        <div class="mb-3">
                            <label class="form-label">Nombre del Recurso</label>
                            <input type="text" class="form-control" wire:model="nombre" placeholder="Ej: Guía de Estudio Corte 1">
                            @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" wire:model="descripcion" rows="3" placeholder="..."></textarea>
                            @error('descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo Recurso</label>
                                <select class="form-select" wire:model="tipo">
                                     <option value="">Seleccione un tipo...</option>
                                        <option value="Video">Video</option>
                                        <option value="Documento">Documento</option>
                                        <option value="Enlace">Enlace</option>
                                        <option value="Libro">Libro</option>
                                        <option value="Clase">Clase</option>
                                        <option value="Predica">Predica</option>
                                </select>
                                @error('tipo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Enlace de YouTube (Opcional)</label>
                                <input type="url" class="form-control" wire:model="link_youtube" placeholder="https://www.youtube.com/watch?v=...">
                                @error('link_youtube') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Enlace Externo (Opcional)</label>
                                <input type="text" class="form-control" wire:model="link_externo" placeholder="https://ejemplo.com/articulo">
                                @error('link_externo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                             <label class="form-label">Archivo Adjunto (Opcional)</label>
                                @if ($editingResource && $editingResource->nombre_archivo)
                                    <div class="d-flex align-items-center p-2 border rounded">
                                        <i class="mdi mdi-file-check-outline mdi-24px text-success me-2"></i>
                                        <a href="{{ $editingResource->archivo_url }}" target="_blank" class="text-black fw-bold me-auto">
                                            {{ $editingResource->nombre_archivo }}
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-icon btn-sm"
                                            wire:click.prevent="eliminarArchivoAdjunto"
                                            wire:confirm="¿Estás seguro de que quieres eliminar este archivo permanentemente?"
                                            wire:loading.attr="disabled"
                                            title="Eliminar Archivo">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                @else
                                    <input class="form-control" type="file" wire:model="archivo">
                                    @error('archivo') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <div wire:loading wire:target="archivo" class="text-muted small mt-1">Subiendo...</div>
                                @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" wire:click="cerrarModal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                             <span wire:loading wire:target="guardarRecurso" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    {{-- SCRIPT INTEGRADO DIRECTAMENTE EN LA VISTA --}}
    @script
    <script>
        // Listener para las notificaciones (tipo toast)
       Livewire.on('notificacion', (event) => {

            const detail = Array.isArray(event) ? event[0] :

            event; // Livewire 3 puede pasar el evento en un array
            Swal.fire({
            icon: 'success',
            title: detail.titulo || '¡Realizado!', // Título del modal
            text: detail.texto, // Texto del cuerpo del modal
            timer: detail.timer || 2500, // Duración antes de que se cierre solo (opcional)
            showConfirmButton: detail.showConfirmButton === undefined ? false : detail
            .showConfirmButton, // Mostrar botón de confirmación (por defecto no)
            // Estilos para un modal centrado (por defecto SweetAlert es centrado)
            // No necesitas 'toast: true' ni 'position: top-end' para un modal centrado

            });

            });

        // Listener para el DIÁLOGO DE CONFIRMACIÓN de borrado
        window.addEventListener('show-confirm-dialog', event => {
            const { id } = event.detail[0] || event.detail;

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si se confirma, se emite el evento final a Livewire
                    @this.dispatch('eliminar-recurso', { id: id });
                }
            });
        });
    </script>
    @endscript
</div>
