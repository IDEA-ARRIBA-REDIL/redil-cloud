<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    <div>
        {{-- Mensajes de sesión Flash --}}
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
        @if (session()->has('mensaje_info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('mensaje_info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">

                <button type="button" class="btn btn-primary my-5 float-start rounded-pill"
                    wire:click="abrirModalAnadirMaterias">
                    <i class="ti ti-plus me-1"></i> Añadir 
                </button>
            </div>

            <div class="card-body">
                <div class="row equal-height-row">
                    @if ($materiasDelPeriodo && $materiasDelPeriodo->count() > 0)

                        @foreach ($materiasDelPeriodo as $materiaPe)
                            <div class="col equal-height-col  col-12 col-xl-4 col-md-6 mb-4">
                                <div class="h-100 card border">
                                    <img id="preview-foto" style="height: 100px;" class="card-img-top object-fit-cover"
                                        src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/materias/' . $materiaPe->materia->portada) }}"
                                        alt="Portada {{ $materiaPe->materia->nombre }}">


                                    <div class="card-header">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="d-flex align-items-center">

                                                <h5 class="mb-0 fw-semibold text-black lh-sm">
                                                    {{ $materiaPe->materia->nombre }}  </h5>
                                                    
                                            </div>  

                                            <div class="dropdown zindex-2 ">
                                                <button   style="border-radius: 20px;" class="btn p-1 border " type="button" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical text-black"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a href="{{ route('periodo.horarios', $materiaPe) }}"
                                                            class="dropdown-item">
                                                            Gestionar horarios
                                                        </a>
                                                    </li>
                                                   @if (!$materiaPe->finalizado)
                                                        <li>
                                                            <button type="button" 
                                                                    wire:click="confirmarFinalizacion({{ $materiaPe->id }})"
                                                                    class="dropdown-item text-dark">
                                                                Finalizar Materia
                                                            </button>
                                                        </li>
                                                    @else
                                                        {{-- Si la materia SÍ está finalizada, muestra el botón para Reactivar --}}
                                                        <li>
                                                            <button type="button" 
                                                                    wire:click="confirmarReactivacion({{ $materiaPe->id }})"
                                                                    class="dropdown-item text-dark">
                                                                Activar Materia
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('periodo.materia.exportar-informe', $materiaPe) }}" class="dropdown-item">
                                                                
                                                                Exportar Informe
                                                            </a>
                                                        </li>
                                                    @endif
                                                     <li>
                                                        {{-- Este botón ahora llama a un método en el componente de Livewire --}}
                                                        <button type="button" 
                                                                wire:click="confirmarEliminacion({{ $materiaPe->id }})"
                                                                class="dropdown-item text-dark">
                                                            Eliminar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sección de caracteristicas-->
                                    <div class="card-body">
                                        @if ($materiaPe->finalizado)
                                                        <span class="badge bg-label-success rounded-pill  mb-2">Finalizada</span>
                                                    @else
                                                        <span class="badge bg-label-info rounded-pill mb-2">En Curso</span>
                                                    @endif
                                        <div class="d-flex flex-column mb-3">
                                            <div class="d-flex flex-row">
                                                <i class="ti ti-circle-dashed-percentage text-black"></i>
                                                <div class="d-flex flex-column">
                                                    <small class="text-black ms-1">Calificaciones: </small>
                                                    <small
                                                        class="fw-semibold ms-1 text-black ">{{ $materiaPe->materia->habilitar_calificaciones ? 'Habilitado' : 'Inhabilitado' }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column mb-3">
                                            <div class="d-flex flex-row">
                                                <i class="ti ti-user-check text-black"></i>
                                                <div class="d-flex flex-column">
                                                    <small class="text-black ms-1">Asistencias: </small>
                                                    <small
                                                        class="fw-semibold ms-1 text-black ">{{ $materiaPe->habilitar_asistencias ? 'Habilitado' : 'Inhabilitado' }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-row justify-content-between mb-2">
                                            <div class="d-flex flex-row">
                                                <i class="ti ti-user-cancel text-black"></i>
                                                <div class="d-flex flex-column text-star">
                                                    <small class="text-black ms-1">Inasistencias: </small>
                                                    <small class="fw-semibold ms-1 text-black ">
                                                        {{ $materiaPe->habilitar_inasistencias ? 'Habilitado' : 'Inhabilitado' }}
                                                    </small>
                                                </div>
                                            </div>
                                            @if ($materiaPe->habilitar_asistencias)
                                                <div class="d-flex flex-row">
                                                    <i class="ti ti-users-minus text-black"></i>
                                                    <div class="d-flex flex-column text-star">
                                                        <small class="text-black ms-1">Asistencias minimas: </small>
                                                        <small class="fw-semibold ms-1 text-black ">
                                                            {{ $materiaPe->materia->asistencias_minimas }}
                                                        </small>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="d-flex flex-column mb-3">
                                            <div class="d-flex flex-row">
                                                <i class="ti ti-user-cancel text-black"></i>
                                                <div class="d-flex flex-column">
                                                    <small class="text-black ms-1">Alerta inasistencias: </small>
                                                    <small
                                                        class="fw-semibold ms-1 text-black ">{{ $materiaPe->habilitar_inasistencias ? 'Habilitado' : 'Inhabilitado' }}</small>
                                                </div>

                                            </div>
                                        </div>



                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-secondary text-center" role="alert">
                            <i class="ti ti-info-circle me-2"></i> No hay materias asignadas a este período todavía.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- INICIO: Modal para Añadir Materias (con el nuevo selector) --}}
        @if ($mostrarModalAnadirMaterias)
            <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1"
                role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-semibold">Añadir materias al periodo</h5>
                            <button type="button" class="btn-close" wire:click="cerrarModalAnadirMaterias"
                                aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">

                            {{-- ** INICIO: Selector Múltiple con Alpine.js para Materias ** --}}
                            <div class="col-12 mb-3" x-data="{
                                open: false,
                                materiasSeleccionadas: @entangle('materiasSeleccionadasParaAnadir').live,
                                materiasDisponibles: {{ $materiasEscuelaDisponibles->map(fn($materia) => ['id' => $materia->id, 'nombre' => $materia->nombre])->toJson() }},
                                search: '',
                                get allMateriasSelected() {
                                    return this.materiasDisponibles.length > 0 && this.materiasSeleccionadas.length === this.materiasDisponibles.length;
                                },
                                toggleSelectAll() {
                                    if (this.allMateriasSelected) {
                                        this.materiasSeleccionadas = [];
                                    } else {
                                        this.materiasSeleccionadas = this.materiasDisponibles.map(materia => parseInt(materia.id));
                                    }
                                },
                                get filteredMaterias() {
                                    if (this.search === '') { return this.materiasDisponibles; }
                                    return this.materiasDisponibles.filter(materia =>
                                        materia.nombre.toLowerCase().includes(this.search.toLowerCase())
                                    );
                                },
                                get selectedMateriasNombres() {
                                    if (this.materiasSeleccionadas.length === 0) return 'Selecciona una o más materias...';
                                    if (this.allMateriasSelected) return 'Todas las materias seleccionadas';
                                    const nombres = this.materiasDisponibles
                                        .filter(materia => this.materiasSeleccionadas.map(String).includes(String(materia.id)))
                                        .map(materia => materia.nombre);
                                    const maxNombres = 2;
                                    if (nombres.length > maxNombres) {
                                        return nombres.slice(0, maxNombres).join(', ') + ` y ${nombres.length - maxNombres} más`;
                                    }
                                    return nombres.join(', ');
                                }
                            }" @click.away="open = false" wire:ignore>

                                <label for="materias-trigger" class="form-label">Materias a incluir</label>

                                <div class="form-control @error('materiasSeleccionadasParaAnadir') is-invalid @enderror"
                                    @click="open = !open"
                                    style="min-height: 38px; cursor: pointer; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                    id="materias-trigger" x-text="selectedMateriasNombres">
                                </div>
                                @error('materiasSeleccionadasParaAnadir')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <div x-show="open" x-transition
                                    class="position-absolute mt-1 w-100 bg-body border rounded shadow-lg overflow-auto"
                                    style="max-height: 250px; z-index: 1050; background:#fff !important; width: 97% !important;">

                                    <div class="p-2 border-bottom">
                                        <input type="text" x-model.debounce.300ms="search"
                                            placeholder="Buscar materia..." class="form-control form-control-sm">
                                    </div>

                                    <ul class="list-unstyled p-2 mb-0">
                                        <li class="form-check mb-2 pb-2 border-bottom">
                                            <input class="form-check-input" type="checkbox" id="select-all-materias"
                                                @click="toggleSelectAll()" :checked="allMateriasSelected">
                                            <label class="form-check-label fw-medium" for="select-all-materias">
                                                Seleccionar todas / Quitar selección
                                            </label>
                                        </li>

                                        <template x-for="materia in filteredMaterias" :key="materia.id">
                                            <li class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox"
                                                    :id="'materia-opt-' + materia.id" :value="materia.id"
                                                    x-model="materiasSeleccionadas">
                                                <label class="form-check-label" :for="'materia-opt-' + materia.id"
                                                    x-text="materia.nombre"></label>
                                            </li>
                                        </template>

                                        <template x-if="filteredMaterias.length === 0">
                                            <li class="text-muted text-center p-2">No se encontraron materias.</li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                            {{-- ** FIN: Selector Múltiple con Alpine.js para Materias ** --}}

                            <div class="mt-4">
                                <label class="form-label d-block mb-3">¿Deseas incluir los horarios base de estas
                                    materias?</label>
                                <div class="row">
                                    {{-- Opción "NO" --}}
                                    <div class="col-sm-6 mb-2">
                                        <div
                                            class="form-check custom-option custom-option-basic rounded-3 shadow-sm border h-100">
                                            <label class="form-check-label custom-option-content p-3 w-100"
                                                for="incluirHorariosBaseNo">
                                                <span class="custom-option-header m-0 pb-0">
                                                    <span class="h6 mb-0 d-flex align-items-center">No</span>
                                                    <input name="incluirHorariosBase" class="form-check-input"
                                                        type="radio" wire:model="incluirHorariosBase"
                                                        value="0" id="incluirHorariosBaseNo" />
                                                </span>
                                                <small class="d-block">Añadir solo las materias </small>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Opción "SI" --}}
                                    <div class="col-sm-6 mb-2">
                                        <div
                                            class="form-check custom-option custom-option-basic rounded-3 shadow-sm border h-100">
                                            <label class="form-check-label custom-option-content p-3 w-100"
                                                for="incluirHorariosBaseSi">
                                                <span class="custom-option-header m-0 pb-0">
                                                    <span class="h6 mb-0 d-flex align-items-center">Sí</span>
                                                    <input name="incluirHorariosBase" class="form-check-input"
                                                        type="radio" wire:model="incluirHorariosBase"
                                                        value="1" id="incluirHorariosBaseSi" />
                                                </span>
                                                <small class="d-block"> Vincular sus horarios </small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer justify-content-start p-0 mt-3 border-top py-5">
                                <button type="button" class="btn btn-outline-secondary rounded-pill"
                                    wire:click="cerrarModalAnadirMaterias">Cancelar</button>
                                <button type="button" class="btn btn-primary rounded-pill"
                                    wire:click="anadirMateriasSeleccionadas" wire:loading.attr="disabled">
                                    <span wire:loading wire:target="anadirMateriasSeleccionadas"
                                        class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    Añadir materias
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        @endif
        {{-- FIN: Modal --}}
    </div>

   @push('scripts')
    <script>
        // Asegúrate que este script se ejecute después de que Livewire se inicialice
        document.addEventListener('livewire:init', () => {
            
            // Escucha el evento 'mostrar-confirmacion-finalizar' emitido desde el componente
            Livewire.on('mostrar-confirmacion-finalizar', (event) => {
                // Extraemos los datos del evento
                const data = Array.isArray(event) ? event[0] : event;
                const materiaId = data.id;
                const materiaNombre = data.nombre;

                // Mostramos el SweetAlert
                Swal.fire({
                    title: '¿Estás seguro?',
                    html: `Se calcularán y guardarán las notas finales para todos los alumnos de la materia <strong>${materiaNombre}</strong>.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, ¡Finalizar!',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    // Si el usuario hace clic en "Sí, ¡Finalizar!"
                    if (result.isConfirmed) {
                        // Emitimos un nuevo evento de vuelta al componente para ejecutar la acción final.
                        Livewire.dispatch('finalizarMateriaConfirmado', { materiaPeriodoId: materiaId });
                    }
                });
            });

            Livewire.on('mostrar-confirmacion-reactivar', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            const materiaId = data.id;
            const materiaNombre = data.nombre;

            Swal.fire({
                title: '¿Reactivar Materia?',
                html: `La materia <strong>${materiaNombre}</strong> volverá al estado "En Curso". Se eliminarán los registros de notas finales para permitir un nuevo cálculo. ¿Deseas continuar?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, Reactivar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-outline-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si el usuario confirma, emitimos el evento de vuelta al componente
                    Livewire.dispatch('reactivarMateriaConfirmado', { materiaPeriodoId: materiaId });
                }
            });
        });

         // ==========================================================
        // === INICIO: NUEVO SCRIPT PARA ELIMINAR                 ===
        // ==========================================================
        Livewire.on('mostrar-confirmacion-eliminar', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            const materiaId = data.id;
            const materiaNombre = data.nombre;

            Swal.fire({
                title: '¿Estás seguro?',
                html: `Estás a punto de eliminar permanentemente la materia <strong>${materiaNombre}</strong> de este periodo.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, ¡Eliminar!',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33', // Botón de confirmación rojo
                customClass: {
                    confirmButton: 'btn btn-danger me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si el usuario confirma, emitimos el evento de vuelta al componente.
                    Livewire.dispatch('eliminarMateriaConfirmado', { materiaPeriodoId: materiaId });
                }
            });
        });

        // Listener para los errores (útil para el caso de no poder eliminar)
        Livewire.on('mostrar-error', event => {
            const data = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                icon: 'error',
                title: 'Acción no permitida',
                text: data.texto,
            });
        });
        });
    </script>
@endpush

</div>
