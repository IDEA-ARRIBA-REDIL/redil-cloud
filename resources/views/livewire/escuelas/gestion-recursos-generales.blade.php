<div>
    {{-- Botón para abrir el modal de creación --}}
    <div class="d-flex justify-content-end mb-3">
        <button wire:click="abrirModalCrear" class="btn btn-primary rounded-pill">
            <i class="ti ti-plus me-1"></i> Crear nuevo recurso
        </button>
    </div>

    {{-- Lista de Recursos --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Recursos generales de la escuela</h5>
        </div>
     
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Roles asignados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($recursos as $recurso)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        @if ($recurso->tipo === 'Video') <i class="ti ti-video fs-2"></i>
                                        @elseif ($recurso->tipo === 'Documento') <i class="ti ti-file-text fs-2"></i>
                                        @elseif ($recurso->tipo === 'Enlace') <i class="ti ti-link fs-2"></i>
                                        @else <i class="ti ti-file fs-2"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $recurso->nombre }}</strong>
                                        <p class="text-muted mb-0">{{ Str::limit($recurso->descripcion, 50) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-label-secondary">{{ $recurso->tipo }}</span></td>
                            <td>
                                @forelse ($recurso->roles as $rol)
                                    <span class="badge bg-label-info me-1">{{ $rol->name }}</span>
                                @empty
                                    <span class="badge bg-label-light">Ninguno</span>
                                @endforelse
                            </td>
                            <td>
                                <div class="d-flex">
                                    <button wire:click="abrirModalEditar({{ $recurso->id }})" class="btn me-1 btn-sm btn-icon btn-outline-primary item-edit" data-bs-toggle="tooltip" title="Editar Contenido"><i class="ti ti-pencil"></i></button>
                                    {{-- Botón actualizado para llamar al nuevo método del modal de roles --}}
                                    <button wire:click="abrirModalRoles({{ $recurso->id }})" class="btn me-1 btn-sm btn-outline-secondary btn-icon" data-bs-toggle="tooltip" title="Gestionar Roles"><i class="ti ti-users"></i></button>
                                    <button 
                                        wire:click="$dispatch('mostrarAlertaConfirmacion', {{ $recurso->id }})" 
                                        class="btn me-1 btn-sm btn-icon btn-outline-danger item-delete" 
                                        data-bs-toggle="tooltip" 
                                        title="Eliminar Recurso">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay recursos creados todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal para Crear y Editar Recursos --}}
    <div class="modal fade" id="recursoModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-primary fw-semibold">{{ $modoEdicion ? 'Editar recurso' : 'Crear nuevo recurso' }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="guardarRecurso">
                    <div class="modal-body row">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del recurso</label>
                            <input type="text" wire:model.defer="nombre" class="form-control" placeholder="Ej: Manual del Estudiante">
                            @error('nombre') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                         <div class="mb-3 col-md-6 col-sm-12">
                            <label for="tipo" class="form-label">Tipo de recurso</label>
                            <select wire:model.defer="tipo" class="form-select">
                                <option value="">Seleccione un tipo...</option>
                                <option value="Video">Video</option>
                                <option value="Documento">Documento</option>
                                <option value="Enlace">Enlace</option>
                                <option value="Libro">Libro</option>
                                <option value="Clase">Clase</option>
                                <option value="Predica">Predica</option>
                                
                            </select>
                            @error('tipo') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3 col-md-6 col-sm-12">
                            <label for="link_externo" class="form-label">Enlace externo (Opcional)</label>
                            <input type="url" wire:model.defer="link_externo" class="form-control" placeholder="https://...">
                        </div>
                        <div class="mb-3 col-md-6 col-sm-12">
                            <label for="link_youtube" class="form-label">Enlace de YouTube (Opcional)</label>
                            <input type="url" wire:model.defer="link_youtube" class="form-control" placeholder="https://youtube.com/watch?v=...">
                        </div>
                        <div class="mb-3 col-12">
                            <label for="archivo" class="form-label">Subir archivo (Opcional)</label>
                            
                            {{-- Si estamos editando Y existe un archivo, mostramos la vista previa y el botón de eliminar --}}
                                @if ($modoEdicion && $archivoExistente)
                                    <div class="d-flex align-items-center p-2 border rounded bg-light">
                                        <i class="ti ti-file-check fs-2 text-success me-3"></i>
                                        <a href="{{ $archivoUrl }}" target="_blank" class="text-dark fw-semibold me-auto text-truncate" style="max-width: 300px;" title="{{ $archivoExistente }}">
                                            {{ $archivoExistente }}
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-icon btn-sm"
                                            wire:click.prevent="eliminarArchivoAdjunto"
                                            wire:confirm="¿Estás seguro de que quieres eliminar este archivo permanentemente?"
                                            wire:loading.attr="disabled"
                                            wire:target="eliminarArchivoAdjunto"
                                            title="Eliminar Archivo">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                @else
                                    {{-- Si no, mostramos el input para subir un nuevo archivo --}}
                                    <input class="form-control" type="file" wire:model="archivo">
                                    @error('archivo') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <div wire:loading wire:target="archivo" class="text-muted small mt-1">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        Subiendo...
                                    </div>
                                @endif
                        </div>
                        <div class="mb-3 col-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea wire:model.defer="descripcion" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">{{ $modoEdicion ? 'Guardar Cambios' : 'Crear Recurso' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Modal para Gestionar Roles --}}
    <div class="modal fade" id="rolesModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestionar roles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($recursoSeleccionado)
                        <div class="mb-3">
                            <p>Asignar roles que podrán ver el recurso:</p>
                            <strong>{{ $recursoSeleccionado->nombre }}</strong>
                        </div>

                        {{-- ===== INICIO: Multi-Select Alpine (VERSIÓN FINAL CORREGIDA) ===== --}}
                    <div x-data="{
                            open: false,
                            options: {{ Js::from($listaDeRoles) }},
                            selected: @entangle('rolesAsignados') ?? [],
                            selectedLabels: [],
                            init() {
                                this.updateSelectedLabels();
                                this.$watch('selected', () => this.updateSelectedLabels());
                            },
                            updateSelectedLabels() {
                                if (Array.isArray(this.selected)) {
                                    this.selectedLabels = this.options
                                        // CORRECCIÓN: Comparamos directamente los IDs como números
                                        .filter(option => this.selected.includes(option.id))
                                        .map(option => option.name);
                                }
                            },
                            toggleOption(optionId) {
                                // CORRECCIÓN: Trabajamos directamente con el ID numérico
                                if (this.selected.includes(optionId)) {
                                    this.selected = this.selected.filter(id => id !== optionId);
                                } else {
                                    this.selected.push(optionId);
                                }
                            },
                            get unselectedOptions() {
                                // CORRECCIÓN: Comparamos directamente los IDs como números
                                return this.options.filter(option => !this.selected.includes(option.id));
                            }
                        }"
                    >
                        <label class="form-label">Roles</label>
                        <div @click.outside="open = false" class="position-relative">
                            {{-- Área que muestra los badges --}}
                            <div @click="open = !open" class.bind="form-select" style="min-height: 38px; cursor: pointer;border: solid 1px; border-radius: 4px;padding-top: 8px;padding-left: 3px;">
                                <template x-if="selected.length === 0">
                                    <span class="text-muted">Seleccione uno o más roles</span>
                                </template>
                                <template x-for="(label, index) in selectedLabels" :key="index">
                                    <span class="badge bg-label-secondary me-1 mb-1">
                                        <span x-text="label"></span>
                                        <span @click.stop="toggleOption(options.find(opt => opt.name === label).id)" class="ms-1" style="cursor: pointer;">&times;</span>
                                    </span>
                                </template>
                            </div>
                            {{-- Menú desplegable --}}
                            <div x-show="open" x-transition class="card position-absolute w-100 mt-1" style="z-index: 10;">
                                <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                                    <template x-for="option in unselectedOptions" :key="option.id">
                                        <a href="#" @click.prevent="toggleOption(option.id)" class="list-group-item list-group-item-action" x-text="option.name"></a>
                                    </template>
                                    <template x-if="unselectedOptions.length === 0">
                                        <span class="list-group-item">No hay más roles disponibles.</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- ===== FIN: Multi-Select Alpine ===== --}}

                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button wire:click="actualizarRoles" type="button" class="btn btn-primary rounded-pill">Actualizar Roles</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        
        // --- Gestión del Modal de Recursos ---
        Livewire.on('abrir-modal-recurso', () => {
            var modalEl = document.getElementById('recursoModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        });

        Livewire.on('cerrar-modal-recurso', () => {
            var modalEl = document.getElementById('recursoModal');
            var modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
        });

        // --- Gestión del Modal de Roles (ACTUALIZADO) ---
        Livewire.on('abrir-modal-roles', () => {
            var modalEl = document.getElementById('rolesModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        });

        Livewire.on('cerrar-modal-roles', () => {
            var modalEl = document.getElementById('rolesModal');
            var modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
        });


        // --- Notificaciones y Alertas (se mantienen igual) ---
        Livewire.on('notificacion', (event) => {
            const detail = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                icon: 'success',
                title: detail.titulo || '¡Realizado!',
                text: detail.texto,
                timer: detail.timer || 2500,
                showConfirmButton: false,
            });
        });

       @this.on('mostrarAlertaConfirmacion', (recursoId) => {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (result.isConfirmed) {
                    @this.call('eliminarRecurso', recursoId);
                }
            });
        });

        // Para inicializar los tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush