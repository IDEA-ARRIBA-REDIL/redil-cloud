<div>
    <div id="container-listado-materias">
        <!-- Botón para abrir el modal de creación -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Materias asociadas</h4>
            <button type="button" class="btn btn-primary" wire:click.prevent="openModal">
                <i class="ti ti-plus me-1"></i> Nueva materia
            </button>
        </div>
        <!-- Listado de Materias -->
        <div class="row">
            @forelse($materias as $materia)
                <div class="col-12 col-xl-4 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-label-primary rounded-pill p-1 me-2">
                                        <i class="ti ti-book ti-xl"></i>
                                    </div>
                                    <h5 class="mb-0">{{ $materia->nombre }}</h5>
                                </div>
                                <!-- Opciones de gestión (puedes incluir enlaces o acciones Livewire para editar/eliminar) -->
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#">Gestionar</a>
                                        </li>
                                        <li>
                                            <form action="#" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item confirmacionEliminar"
                                                    data-nombre="{{ $materia->nombre }}">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-label-secondary me-2 p-2">
                                    <i class="ti ti-calendar"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Creado:</small>
                                    <p class="mb-0">{{ $materia->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        No hay materias registradas para esta escuela.
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Modal de creación de materia -->
        <div wire:ignore.self class="modal fade" id="modalCrearMateria" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear nueva materia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="createMateria">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre de la materia</label>
                                <input type="text" class="form-control" id="nombre" wire:model="nombre">
                                @error('nombre')
                                    <div class="text-danger ti-12px mt-2"> <i
                                            class="ti ti-circle-x"></i>{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="nivel_id" class="form-label">Nivel (opcional)</label>
                                    <input type="number" class="form-control" id="nivel_id" wire:model="nivel_id">
                                    @error('nivel_id')
                                        <div class="text-danger ti-12px mt-2"> <i
                                                class="ti ti-circle-x"></i>{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Campo oculto para escuela_id, se asigna automáticamente -->
                                    <input type="hidden" wire:model="escuelaId">

                                    <div class="mb-3">
                                        <label class="form-label">Habilitar calificaciones</label>
                                        <div>
                                            <label class="form-check">
                                                <input type="radio" class="form-check-input"
                                                    wire:model="habilitar_calificaciones" value="1">
                                                <span class="form-check-label">Sí</span>
                                            </label>
                                            <label class="form-check ms-3">
                                                <input type="radio" class="form-check-input"
                                                    wire:model="habilitar_calificaciones" value="0">
                                                <span class="form-check-label">No</span>
                                            </label>
                                        </div>
                                        @error('habilitar_calificaciones')
                                            <div class="text-danger ti-12px mt-2"> <i
                                                    class="ti ti-circle-x"></i>{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Habilitar asistencias</label>
                                            <div>
                                                <label class="form-check">
                                                    <input type="radio" class="form-check-input"
                                                        wire:model="habilitar_asistencias" value="1">
                                                    <span class="form-check-label">Sí</span>
                                                </label>
                                                <label class="form-check ms-3">
                                                    <input type="radio" class="form-check-input"
                                                        wire:model="habilitar_asistencias" value="0">
                                                    <span class="form-check-label">No</span>
                                                </label>
                                            </div>
                                            @error('habilitar_asistencias')
                                                <div class="text-danger ti-12px mt-2"> <i
                                                        class="ti ti-circle-x"></i>{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="asistencias_minimas" class="form-label">Asistencias
                                                    mínimas
                                                    (opcional)</label>
                                                <input type="number" class="form-control" id="asistencias_minimas"
                                                    wire:model="asistencias_minimas">
                                                @error('asistencias_minimas')
                                                    <div class="text-danger ti-12px mt-2"> <i
                                                            class="ti ti-circle-x"></i>{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label for="descripcion" class="form-label">Descripción
                                                        (opcional)</label>
                                                    <textarea class="form-control" id="descripcion" rows="3" wire:model="descripcion"></textarea>
                                                    @error('descripcion')
                                                        <div class="text-danger ti-12px mt-2"> <i
                                                                class="ti ti-circle-x"></i>{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Habilitar alerta de
                                                            inasistencias</label>
                                                        <div>
                                                            <label class="form-check">
                                                                <input type="radio" class="form-check-input"
                                                                    wire:model="habilitar_alerta_inasistencias"
                                                                    value="1">
                                                                <span class="form-check-label">Sí</span>
                                                            </label>
                                                            <label class="form-check ms-3">
                                                                <input type="radio" class="form-check-input"
                                                                    wire:model="habilitar_alerta_inasistencias"
                                                                    value="0">
                                                                <span class="form-check-label">No</span>
                                                            </label>
                                                        </div>
                                                        @error('habilitar_alerta_inasistencias')
                                                            <div class="text-danger ti-12px mt-2"> <i
                                                                    class="ti ti-circle-x"></i>{{ $message }}</span>
                                                            @enderror
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Habilitar traslado</label>
                                                            <div>
                                                                <label class="form-check">
                                                                    <input type="radio" class="form-check-input"
                                                                        wire:model="habilitar_traslado"
                                                                        value="1">
                                                                    <span class="form-check-label">Sí</span>
                                                                </label>
                                                                <label class="form-check ms-3">
                                                                    <input type="radio" class="form-check-input"
                                                                        wire:model="habilitar_traslado"
                                                                        value="0">
                                                                    <span class="form-check-label">No</span>
                                                                </label>
                                                            </div>
                                                            @error('habilitar_traslado')
                                                                <div class="text-danger ti-12px mt-2"> <i
                                                                        class="ti ti-circle-x"></i>{{ $message }}</span>
                                                                @enderror
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Carácter obligatorio</label>
                                                                <div>
                                                                    <label class="form-check">
                                                                        <input type="radio" class="form-check-input"
                                                                            wire:model="caracter_obligatorio"
                                                                            value="1">
                                                                        <span class="form-check-label">Sí</span>
                                                                    </label>
                                                                    <label class="form-check ms-3">
                                                                        <input type="radio" class="form-check-input"
                                                                            wire:model="caracter_obligatorio"
                                                                            value="0">
                                                                        <span class="form-check-label">No</span>
                                                                    </label>
                                                                </div>
                                                                @error('caracter_obligatorio')
                                                                    <div class="text-danger ti-12px mt-2"> <i
                                                                            class="ti ti-circle-x"></i>{{ $message }}</span>
                                                                    @enderror
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary "
                                                                        data-bs-dismiss="modal"
                                                                        wire:click="closeModal">Cancelar</button>
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Crear materia</button>
                                                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para controlar la apertura y cierre del modal con Livewire -->
    @push('scripts')
        <script>
            window.addEventListener('materia-created', event => {
                Swal.fire({
                    title: event.detail.titulo,
                    text: event.detail.mensaje,
                    icon: event.detail.tipo,
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
                // Cerrar el modal manualmente con Bootstrap si es necesario
                var myModalEl = document.getElementById('modalCrearMateria');
                var modal = bootstrap.Modal.getInstance(myModalEl);
                if (modal) {
                    modal.hide();
                }
            });

            // Si se abre el modal desde Livewire, podemos inicializarlo
            Livewire.on('openModal', () => {
                var myModal = new bootstrap.Modal(document.getElementById('modalCrearMateria'));
                myModal.show();
            });
        </script>
    @endpush

</div>
