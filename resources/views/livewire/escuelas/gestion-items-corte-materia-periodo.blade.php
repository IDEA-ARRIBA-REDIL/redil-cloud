<div>
    {{-- Mensajes Flash --}}
    @if (session()->has('mensaje_exito'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('mensaje_exito') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('mensaje_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('mensaje_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
     <div class="col-12 mb-3">
                                <div class="alert alert-warning d-flex align-items-center" role="alert">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    <div>
                                        <strong>Nota Importante:</strong> Para editar un ítem que ya tiene calificaciones asignadas, solo se permitirá modificar su <strong>fecha</strong> y <strong>contenido</strong>. Los valores críticos (porcentaje, tipo, etc.) estarán bloqueados para mantener la consistencia de las notas.
                                    </div>
                                </div>
                            </div>

    <div class="accordion" id="accordionCortes">
        @foreach($cortes as $corte)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $corte->id }}">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $corte->id }}" aria-expanded="true" aria-controls="collapse{{ $corte->id }}">
                        {{ $corte->corteEscuela->nombre ?? 'Corte Sin Nombre' }}
                        <span class="badge bg-label-primary ms-2">{{ $corte->porcentaje }}%</span>
                        <span class="text-muted ms-auto me-3 fst-italic" style="font-size: 0.8rem;">
                            (Suma items: {{ $this->itemsPorCorte[$corte->id]->sum('porcentaje') }}%)
                        </span>
                    </button>
                </h2>
                <div id="collapse{{ $corte->id }}" class="accordion-collapse collapse show" aria-labelledby="heading{{ $corte->id }}" data-bs-parent="#accordionCortes">
                    <div class="accordion-body">

                        <div class="mb-3 text-end">
                            <button class="btn btn-sm btn-primary" wire:click="abrirModalCrear({{ $corte->id }})">
                                <i class="ti ti-plus me-1"></i> Nuevo Item
                            </button>
                        </div>

                        <div class="row">

                            @forelse($itemsPorCorte[$corte->id] as $item)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-start border-3 border-{{ $item->visible ? 'success' : 'secondary' }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title fw-bold mb-0 text-truncate" title="{{ $item->nombre }}">
                                                    {{ $item->nombre }}
                                                </h6>
                                                <div class="dropdown border rounded-circle p-1">
                                                    <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="javascript:void(0);" wire:click="abrirModalEditar({{ $item->id }})">Editar</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmarEliminacion({{ $item->id }})">Eliminar</a></li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="row justify-content-between mb-2">
                                                <div class="col-6 mb-2">
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted"><i class="ti ti-percentage me-1"></i>Porcentaje:</small>
                                                        <small class="fw-semibold text-black">{{ $item->porcentaje }}%</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                     <div class="d-flex flex-column">
                                                        <small class="text-muted"><i class="ti ti-template me-1"></i>Origen:</small>
                                                        <small class="fw-semibold text-black">{{ $item->item_plantilla_id ? 'Plantilla' : 'Manual' }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted"><i class="ti ti-calendar me-1"></i>Inicio:</small>
                                                        <small class="fw-semibold text-black">{{ $item->fecha_inicio ? \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y') : 'N/A' }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted"><i class="ti ti-calendar me-1"></i>Fin:</small>
                                                        <small class="fw-semibold text-black">{{ $item->fecha_fin ? \Carbon\Carbon::parse($item->fecha_fin)->format('d/m/Y') : 'N/A' }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted"><i class="ti ti-eye me-1"></i>Visible:</small>
                                                        <small class="fw-semibold text-black">{{ $item->visible ? 'Sí' : 'No' }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted"><i class="ti ti-clipboard-check me-1"></i>Calificable:</small>
                                                        <small class="fw-semibold text-black">{{ $item->calificable ? 'Sí' : 'No' }}</small>
                                                    </div>
                                                </div>
                                                 <div class="col-6 mb-2">
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted"><i class="ti ti-upload me-1"></i>Entregable:</small>
                                                        <small class="fw-semibold text-black">{{ $item->habilitar_entregable ? 'Sí' : 'No' }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-3 text-muted">
                                    No hay items creados para este corte.
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Crear -->
    <div class="modal fade" id="modalCrearItem" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Item de Evaluación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="guardarItem">
                        <div class="row g-2">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nombre del Item</label>
                                <input type="text" class="form-control" wire:model="nombre" placeholder="Ej. Examen Parcial" />
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" wire:model="tipo_item_id">
                                    <option value="">Seleccione...</option>
                                    @foreach($tiposItem as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_item_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Porcentaje (%)</label>
                                <input type="number" class="form-control" wire:model="porcentaje" min="0" max="100" step="0.1" />
                                @error('porcentaje') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Orden</label>
                                <input type="number" class="form-control" wire:model="orden" min="0" />
                                @error('orden') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Contenido / Descripción</label>
                                <div wire:ignore>
                                    <div id="editor-container-crear" style="height: 200px;"></div>
                                </div>
                                @error('contenido') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control fecha-picker" wire:model="fecha_inicio" />
                                @error('fecha_inicio') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                             <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control fecha-picker" wire:model="fecha_fin" />
                                @error('fecha_fin') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" wire:model="visible">
                                    <label class="form-check-label">Visible</label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" wire:model="habilitar_entregable">
                                    <label class="form-check-label">Entregable</label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" wire:model="calificable">
                                    <label class="form-check-label">Calificable</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" wire:click="guardarItem">Guardar Item</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditarItem" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Item de Evaluación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="guardarItem">
                        <div class="row g-2">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nombre del Item</label>
                                <input type="text" class="form-control" wire:model="nombre" @if($bloqueoEdicion) disabled @endif />
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" wire:model="tipo_item_id" @if($bloqueoEdicion) disabled @endif>
                                    <option value="">Seleccione...</option>
                                    @foreach($tiposItem as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_item_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Porcentaje (%)</label>
                                <input type="number" class="form-control" wire:model="porcentaje" min="0" max="100" step="0.1" @if($bloqueoEdicion) disabled @endif />
                                @error('porcentaje') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                             <div class="col-md-6 mb-3">
                                <label class="form-label">Orden</label>
                                <input type="number" class="form-control" wire:model="orden" min="0" @if($bloqueoEdicion) disabled @endif />
                                @error('orden') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Contenido / Descripción</label>
                                <div wire:ignore>
                                    <div id="editor-container-editar" style="height: 200px;"></div>
                                </div>
                                @error('contenido') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control fecha-picker" wire:model="fecha_inicio" />
                                @error('fecha_inicio') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                             <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control fecha-picker" wire:model="fecha_fin" />
                                @error('fecha_fin') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" wire:model="visible">
                                    <label class="form-check-label">Visible</label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" wire:model="habilitar_entregable" @if($bloqueoEdicion) disabled @endif>
                                    <label class="form-check-label">Entregable</label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" wire:model="calificable" @if($bloqueoEdicion) disabled @endif>
                                    <label class="form-check-label">Calificable</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" wire:click="guardarItem">Actualizar Item</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {

        // Función para convertir links de YouTube a iframes
        function convertirYouTube(quill) {
            let content = quill.root.innerHTML;
            const youtubeRegex = /(?:https?:\/\/)?(?:www\.|m\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{11})(?:\S+)?/g;

            // Iteramos sobre las coincidencias
            let match;
            while ((match = youtubeRegex.exec(content)) !== null) {
                const url = match[0];
                const videoId = match[1];
                const embedUrl = `https://www.youtube.com/embed/${videoId}`;
                const iframe = `<iframe width="100%" height="315" src="${embedUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;

                // Verificamos si ya está envuelto en un iframe para no duplicar
                if (!content.includes(embedUrl)) {
                     content = content.replace(url, iframe);
                     quill.root.innerHTML = content;
                }
            }
        }

        // --- QUILL CREAR ---
        const editorCrear = document.getElementById('editor-container-crear');
        let quillCrear = null;
        if (editorCrear) {
             quillCrear = new Quill('#editor-container-crear', {
                theme: 'snow',
                placeholder: 'Escriba el contenido del nuevo ítem...',
                modules: {
                    toolbar: [
                         ['bold', 'italic', 'underline', 'strike'],
                         [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                         ['link', 'clean']
                    ]
                }
            });
            quillCrear.on('text-change', function(delta, oldDelta, source) {
                if (source === 'user') {
                    const range = quillCrear.getSelection();
                    convertirYouTube(quillCrear);
                    if(range) quillCrear.setSelection(range); // Restaurar cursor (puede saltar)
                }
                @this.set('contenido', quillCrear.root.innerHTML);
            });
        }

        // --- QUILL EDITAR ---
        const editorEditar = document.getElementById('editor-container-editar');
        let quillEditar = null;
        if (editorEditar) {
             quillEditar = new Quill('#editor-container-editar', {
                theme: 'snow',
                placeholder: 'Edite el contenido...',
                modules: {
                    toolbar: [
                         ['bold', 'italic', 'underline', 'strike'],
                         [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                         ['link', 'clean']
                    ]
                }
            });
            quillEditar.on('text-change', function(delta, oldDelta, source) {
                 if (source === 'user') {
                    const range = quillEditar.getSelection();
                    convertirYouTube(quillEditar);
                    if(range) quillEditar.setSelection(range);
                }
                @this.set('contenido', quillEditar.root.innerHTML);
            });
        }

        // --- MANEJO DE MODALES ---

        // Abrir Modal CREAR
        Livewire.on('abrir-modal-crear', () => {
             const modalEl = document.getElementById('modalCrearItem');
             if(modalEl) new bootstrap.Modal(modalEl).show();
        });

        // Abrir Modal EDITAR
        Livewire.on('abrir-modal-editar', () => {
             const modalEl = document.getElementById('modalEditarItem');
             if(modalEl) new bootstrap.Modal(modalEl).show();
        });

        // Cerrar Modal CREAR
        Livewire.on('cerrar-modal-crear', () => {
            const modalEl = document.getElementById('modalCrearItem');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });

         // Cerrar Modal EDITAR
        Livewire.on('cerrar-modal-editar', () => {
            const modalEl = document.getElementById('modalEditarItem');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });

        // --- CARGA DE DATOS A EDITORES ---

        // Limpiar editor crear
        Livewire.on('limpiar-editor-crear', () => {
            if (quillCrear) quillCrear.root.innerHTML = '';
        });

        // Cargar editor editar
        Livewire.on('cargar-contenido-editor-editar', (event) => {
             if (!quillEditar) return;
             const content = event.contenido !== undefined ? event.contenido : (event[0]?.contenido ?? '');
             if (quillEditar.root.innerHTML !== content) {
                quillEditar.root.innerHTML = content;
             }
        });

        // --- CONFIRMACIONES ---

        // --- CONFIRMACIONES CON SWEETALERT ---

        window.confirmarEliminacion = function(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el ítem y sus calificaciones. ¡No se puede deshacer!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarItem', id);
                }
            })
        }

        // Listener para errores desde el backend (ej: no se puede eliminar)
        Livewire.on('swal:error', (event) => {
            // event es un array, accedemos al primer elemento o usamos destructuring
             const data = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                icon: 'error',
                title: data.title || 'Error',
                text: data.text || 'Ocurrió un error inesperado.',
            });
        });
    });
</script>
