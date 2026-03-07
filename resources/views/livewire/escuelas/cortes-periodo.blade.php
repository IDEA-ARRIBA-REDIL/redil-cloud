<div>
    {{-- Mensajes de sesión (éxito o error) --}}
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

    {{-- Sección para mostrar los cortes de período --}}


    <div class="row p-4">
        {{-- ** INICIO: Alerta Persistente de Porcentaje ** --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Cortes definidos para el periodo </h5>
            {{-- Botón Añadir Corte (opcional) --}}
        </div>
        @if (isset($sumaPorcentajesActual) && $sumaPorcentajesActual != 100)
            <div class="alert alert-warning d-flex align-items-center" role="alert">

                <div>
                    <strong>¡Atención!</strong> La suma actual de los porcentajes de los cortes es
                    <strong>{{ $sumaPorcentajesActual }}%</strong>.
                    @if ($sumaPorcentajesActual < 100)
                        Falta un <strong>{{ 100 - $sumaPorcentajesActual }}%</strong> para alcanzar el 100%.
                    @else
                        {{-- $sumaPorcentajesActual > 100 --}}
                        Supera el 100% por un <strong>{{ $sumaPorcentajesActual - 100 }}%</strong>.
                    @endif
                    Por favor, ajusta los porcentajes para que sumen exactamente 100%.
                </div>
            </div>
        @endif
        {{-- ** FIN: Alerta Persistente de Porcentaje ** --}}
        @forelse ($cortesPeriodo as $corteP)
            <div class="col-lg-4 col-md-6 col-sm-12 col-12 mb-4"> {{-- Ajustado para mejor visualización --}}
                <div class="card border rounded position-relative h-100">
                    {{-- Menú de Opciones (Dropdown) --}}
                    <div class="position-absolute top-0 end-0 mt-2 me-2 z-1">
                        <div class="dropdown zindex-2 ">
                            <button  style="border-radius: 20px;" class="btn p-1 border " type="button" id="dropdownMenuButton_{{ $corteP->id }}"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end"
                                aria-labelledby="dropdownMenuButton_{{ $corteP->id }}">
                                <li>
                                    <button class="dropdown-item btn-editar-corte" type="button"
                                        wire:click="prepararEdicionCortePeriodo({{ $corteP->id }})">
                                        Editar
                                    </button>
                                </li>
                                @if ($corteP->cerrado == true)
                                    <li>
                                        <button class="dropdown-item btn-editar-corte" type="button"
                                            wire:click="abrirCorte({{ $corteP->id }})">
                                            Abrir
                                        </button>
                                    </li>
                                @else
                                    <li>
                                        <button class="dropdown-item btn-editar-corte" type="button"
                                            wire:click="cerrarCorte({{ $corteP->id }})">
                                            Cerrar
                                        </button>

                                    </li>
                                @endif



                            </ul>
                        </div>
                    </div>

                    {{-- Contenido de la Tarjeta del Corte de Período --}}
                    <div class="border card-body">

                        <h5 class="mb-0 fw-semibold text-black lh-sm">
                            {{ $corteP->corteEscuela->nombre ?? 'Nombre no definido' }}</h5>
                        <!-- Badge de estado -->
                        <span
                            class="badge {{ $corteP->cerrado ? 'btn-danger' : 'btn-success' }} rounded-pill mt-2 mb-3">
                            {{ $corteP->cerrado ? 'Inactivo' : 'Activo' }}
                        </span>
                        <div class="row justify-content-between mb-2">
                            <div class="col-12 col-md-6">

                                <div class="d-flex flex-column">

                                    <small class="text-black ms-1"> <i class="ti ti-user-check text-black"></i>Orden:
                                    </small>
                                    <small class="fw-semibold ms-1 text-black ">
                                        {{ $corteP->corteEscuela->orden ?? 'N/A' }}</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">

                                <div class="d-flex flex-column text-star">
                                    <small class="text-black ms-1"> <i
                                            class="ti ti-circle-dashed-percentage"></i>Porcentaje: </small>
                                    <small class="fw-semibold ms-1 text-black ">
                                        {{ number_format($corteP->porcentaje ?? 0, 2) }}%
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-between mb-2">
                            <div class="col-12 col-md-6">

                                <div class="d-flex flex-column text-star">
                                    <small class="text-black ms-1"> <i class="ti ti-calendar-month"></i> Fecha Inicio:
                                    </small>
                                    <small class="fw-semibold ms-1 text-black ">
                                        {{ $corteP->fecha_inicio ? \Carbon\Carbon::parse($corteP->fecha_inicio)->format('d/m/Y') : 'N/A' }}
                                    </small>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">

                                <div class="d-flex flex-column text-star">
                                    <small class="text-black ms-1"> <i class="ti ti-calendar-month"></i> Fecha fin:
                                    </small>
                                    <small class="fw-semibold ms-1 text-black ">
                                        {{ $corteP->fecha_fin ? \Carbon\Carbon::parse($corteP->fecha_fin)->format('d/m/Y') : 'N/A' }}
                                    </small>
                                </div>
                            </div>

                        </div>



                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-secondary text-center" role="alert">
                    <i class="ti ti-info-circle me-2"></i> No hay cortes definidos para este período todavía.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Offcanvas para Editar Corte de Período --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditarCortePeriodo"
        aria-labelledby="offcanvasEditarCortePeriodoLabel" wire:ignore.self>
        <div class="offcanvas-header">
            <h4 id="offcanvasEditarCortePeriodoLabel" class="offcanvas-offcanvas-title fw-bold text-primary">Editar
                corte de período</h4>
            <button type="button" class="btn-close text-reset" wire:click="cerrarOffcanvasEdicion"
                {{-- Alternativa para cerrar desde el botón --}} data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0">
            <form wire:submit.prevent="actualizarCortePeriodo" id="formEditarCortePeriodo">

                {{-- Campo Fecha Inicio --}}
                <div class="mb-3">
                    <label for="editFechaInicio" class="form-label">Fecha de inicio</label>
                    <input type="date" class="form-control @error('fecha_inicio_editar') is-invalid @enderror"
                        id="editFechaInicio" wire:model.defer="fecha_inicio_editar">
                    @error('fecha_inicio_editar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo Fecha Fin --}}
                <div class="mb-3">
                    <label for="editFechaFin" class="form-label">Fecha de fin</label>
                    <input type="date" class="form-control @error('fecha_fin_editar') is-invalid @enderror"
                        id="editFechaFin" wire:model.defer="fecha_fin_editar">
                    @error('fecha_fin_editar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo Porcentaje --}}
                <div class="mb-3">
                    <label for="editPorcentaje" class="form-label">Porcentaje (%)</label>
                    <input type="number" class="form-control @error('porcentaje_editar') is-invalid @enderror"
                        id="editPorcentaje" wire:model.defer="porcentaje_editar" min="0" max="100"
                        step="0.01">
                    @error('porcentaje_editar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="form-text text-muted">La suma total de porcentajes no debe superar 100%.</small>
                    @enderror
                </div>



        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <div class="mt-4 d-flex justify-content-start">
                <button type="submit" class="btn btn-primary rounded-pill me-sm-3 me-1">
                    <span wire:loading wire:target="actualizarCortePeriodo" class="me-2" role="status"
                        aria-hidden="true"></span>
                    Guardar cambios
                </button>
                <button type="button" class="btn rounded-pill btn-outline-secondary"
                    wire:click="cerrarOffcanvasEdicion" data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                let offcanvasEditarCortePeriodoElement = document.getElementById('offcanvasEditarCortePeriodo');
                let offcanvasEditarCortePeriodoInstance;
                let backdropElement = null; // Para manejar el backdrop manualmente si es necesario

                if (offcanvasEditarCortePeriodoElement) {
                    // Inicializar la instancia de Bootstrap Offcanvas una sola vez
                    offcanvasEditarCortePeriodoInstance = new bootstrap.Offcanvas(offcanvasEditarCortePeriodoElement, {
                        backdrop: false // Deshabilitar backdrop de Bootstrap si lo manejaremos manualmente con el div
                    });

                    // Listener para abrir el offcanvas según tu especificación
                    Livewire.on('abrirOffcanvas', (eventData) => {
                        const nombreOffCanvas = eventData.nombreModal; // ej: 'offcanvasEditarCortePeriodo'
                        if (nombreOffCanvas === 'offcanvasEditarCortePeriodo') {
                            // Crear y mostrar el backdrop manualmente
                            if (!document.querySelector('.offcanvas-backdrop.show')) {
                                backdropElement = document.createElement('div');
                                backdropElement.className = 'offcanvas-backdrop fade';
                                document.body.appendChild(backdropElement);
                                // Forzar reflow para la transición
                                backdropElement.getBoundingClientRect();
                                backdropElement.classList.add('show');
                            }
                            offcanvasEditarCortePeriodoInstance.show();
                        }
                    });

                    // Listener para cerrar el offcanvas según tu especificación
                    Livewire.on('cerrarOffcanvas', (eventData) => {
                        const nombreOffCanvas = eventData.nombreModal;
                        if (nombreOffCanvas === 'offcanvasEditarCortePeriodo') {
                            offcanvasEditarCortePeriodoInstance.hide();
                            // El backdrop se eliminará con el evento 'hidden.bs.offcanvas'
                        }
                    });

                    // Listener para cuando Bootstrap oculta el offcanvas (por cualquier medio)
                    offcanvasEditarCortePeriodoElement.addEventListener('hidden.bs.offcanvas', () => {
                        // Eliminar el backdrop manualmente
                        const currentBackdrop = document.querySelector('.offcanvas-backdrop.show');
                        if (currentBackdrop) {
                            currentBackdrop.remove();
                        }
                        // Notificar a Livewire que el offcanvas se cerró
                        Livewire.dispatch('offcanvasFueCerrado', {
                            nombreModal: 'offcanvasEditarCortePeriodo'
                        });
                    });
                }

                // Listener para la confirmación de eliminación
                Livewire.on('mostrar-confirmacion-eliminacion', (eventData) => {
                    const idCortePeriodo = eventData.idCortePeriodo;
                    const nombreCorte = eventData.nombreCorte;

                    if (confirm(
                            `¿Estás seguro de que quieres eliminar el corte "${nombreCorte}"? Esta acción no se puede deshacer.`
                        )) {
                        Livewire.dispatch('eliminar-corte-periodo-confirmado', {
                            idCortePeriodo: idCortePeriodo
                        });
                    }
                });
            });
        </script>
    @endpush
</div>
