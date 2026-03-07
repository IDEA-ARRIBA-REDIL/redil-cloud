<div>
    @php
        use Carbon\Carbon;
    @endphp
    {{-- Estilos específicos o globales si son necesarios --}}
    <style>
        /* Estilo para filas de corte */
        .corte-row {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .corte-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        @media (max-width: 767.98px) {
            .corte-row .form-label {
                margin-bottom: 0.25rem;
            }

            .corte-row>div[class*="col-"] {
                margin-bottom: 0.75rem;
            }

            .corte-row>div[class*="col-"]:last-child {
                margin-bottom: 0;
            }
        }

        /* Ocultar pasos no activos */
        .step:not(.active) {
            display: none;
        }

        .step.active {
            display: block;
        }
    </style>



    {{-- Contenedor Principal del Wizard --}}
    <div class="col-12 mb-5">

        {{-- Encabezado Fijo del Wizard --}}
        <div class="pt-3 px-md-0 px-3 mb-4">
            <div class="d-flex align-items-start p-2">
                <div class="badge rounded-circle bg-label-primary p-3 me-3">
                    <i class="ti ti-calendar-event ti-md"></i>
                </div>
                <div class="my-auto">
                    {{-- Actualizado dinámicamente por Livewire --}}
                    <small class="text-muted" id="step-counter">Paso {{ $currentStep }} de {{ $totalSteps }}</small>
                    {{-- Título dinámico basado en el paso actual --}}
                    <h6 class="mb-0" id="step-title">
                        @if ($currentStep == 1)
                            Información principal
                        @elseif($currentStep == 2)
                            Configuración de cortes
                        @endif
                    </h6>
                </div>
            </div>
            <div class="progress mx-2" style="height: 8px;">
                {{-- Barra de progreso actualizada dinámicamente --}}
                <div id="progress-bar" class="progress-bar" role="progressbar"
                    style="width: {{ ($currentStep / $totalSteps) * 100 }}%;"
                    aria-valuenow="{{ ($currentStep / $totalSteps) * 100 }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>

        {{-- INICIO: Formulario Livewire --}}
        {{-- wire:submit.prevent="save" llama al método save SÓLO si el botón es type="submit" --}}
        <form wire:submit.prevent="save" id="formularioCrearPeriodo">
            {{-- No necesitamos @csrf en formularios Livewire --}}


            {{-- PASO 1: Información Principal --}}
            <div class="step @if ($currentStep == 1) active @endif" id="step-1"
                data-title="Información Principal">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Configuración inicial</h5>
                    </div>
                    <div class="card-body row">

                        {{-- Campo Nombre --}}
                        <div class="mb-3 col-12 col-md-6">
                            <label for="nombre" class="form-label">Nombre </label>
                            <input required type="text" class="form-control" id="nombre" wire:model.defer="nombre"
                                placeholder="Ej: Semestre 2025-1">
                            @error('nombre')
                                <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Campo Escuela asociada --}}
                        <div class="mb-3 col-12 col-md-6">
                            <label for="escuelaId" class="form-label">Escuela asociada </label>
                            <select required id="escuelaId" class="form-select " wire:model.live="escuelaId">
                                <option value="">Selecciona una escuela</option>
                                @foreach ($escuelas as $escuela)
                                    <option value="{{ $escuela->id }}">{{ $escuela->nombre }}</option>
                                @endforeach
                            </select>
                            @error('escuelaId')
                                <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Campo Sistema de calificaciones --}}
                        <div class="mb-3 col-12 col-md-6">
                            <label for="sistema_calificacion_id" class="form-label">Sistema de calificaciones </label>
                            <select required id="sistema_calificacion_id" class="form-select "
                                wire:model.defer="sistema_calificacion_id">
                                <option value="">Selecciona un sistema</option>
                                @foreach ($sistemasCalificacion as $sistema)
                                    <option value="{{ $sistema->id }}">{{ $sistema->nombre }}</option>
                                @endforeach
                            </select>
                            @error('sistema_calificacion_id')
                                <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        {{-- Campo Fecha inicio periodo --}}
                        <div class="mb-3 col-12 col-md-6">
                            <label class="form-label" for="fecha_inicio">Fecha inicio periodo </label>
                            {{-- Usamos wire:model.defer para vincular directamente con Livewire --}}
                            <input required id="fecha_inicio" placeholder="YYYY-MM-DD"
                                class="form-control fecha-picker " type="text" wire:model.defer="fecha_inicio" />
                            @error('fecha_inicio')
                                <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        {{-- Campo Fecha finalización periodo --}}
                        <div class="mb-3 col-12 col-md-6">
                            <label class="form-label" for="fecha_fin">Fecha finalización periodo </label>
                            <input required id="fecha_fin" placeholder="YYYY-MM-DD" class="form-control fecha-picker "
                                type="text" wire:model.defer="fecha_fin" />
                            @error('fecha_fin')
                                <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        {{-- Campo Fecha limite calificaciones maestro --}}
                        <div class="mb-3 col-12 col-md-6">
                            <label class="form-label" for="fecha_limite_maestro">Fecha límite calificaciones
                                maestro</label>
                            <input required id="fecha_limite_maestro" placeholder="YYYY-MM-DD"
                                class="form-control fecha-picker " type="text"
                                wire:model.defer="fecha_limite_maestro" />
                            @error('fecha_limite_maestro')
                                <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- INICIO: Campo Sedes habilitadas (Alpine.js Multi-Select MODIFICADO) --}}
                        <div class="col-12 mb-3" x-data="{
                            open: false,
                            selectedSedes: @entangle('selectedSedes').live,
                            sedesDisponibles: {{ $sedes->map(fn($sede) => ['id' => $sede->id, 'nombre' => $sede->nombre])->toJson() }},
                            search: '',
                        
                            // --> INICIO: Lógica modificada en Alpine.js
                            // 1. Nueva propiedad computada para saber si todo está seleccionado
                            get allSedesSelected() {
                                // Devuelve true solo si el número de sedes seleccionadas es igual al total de sedes disponibles
                                return this.sedesDisponibles.length > 0 && this.selectedSedes.length === this.sedesDisponibles.length;
                            },
                        
                            // 2. Nueva función para el interruptor 'Seleccionar Todos'
                            toggleSelectAll() {
                                if (this.allSedesSelected) {
                                    // Si todo está seleccionado, vacía la selección
                                    this.selectedSedes = [];
                                } else {
                                    // Si no, selecciona todas las sedes disponibles
                                    this.selectedSedes = this.sedesDisponibles.map(sede => parseInt(sede.id));
                                }
                            },
                            // <-- FIN: Lógica modificada en Alpine.js
                        
                            get filteredSedes() {
                                if (this.search === '') { return this.sedesDisponibles; }
                                return this.sedesDisponibles.filter(sede =>
                                    sede.nombre.toLowerCase().includes(this.search.toLowerCase())
                                );
                            },
                            get selectedSedesNombres() {
                                if (this.selectedSedes.length === 0) return 'Selecciona una o más sedes...';
                                if (this.allSedesSelected) return 'Todas las sedes seleccionadas';
                                const nombres = this.sedesDisponibles
                                    .filter(sede => this.selectedSedes.map(String).includes(String(sede.id)))
                                    .map(sede => sede.nombre);
                                const maxNombres = 2;
                                if (nombres.length > maxNombres) {
                                    return nombres.slice(0, maxNombres).join(', ') + ` y ${nombres.length - maxNombres} más`;
                                }
                                return nombres.join(', ');
                            },
                            toggleSede(sedeId) {
                                const idStr = String(sedeId);
                                const index = this.selectedSedes.findIndex(id => String(id) === idStr);
                                if (index === -1) {
                                    this.selectedSedes.push(idStr);
                                } else {
                                    this.selectedSedes.splice(index, 1);
                                }
                                this.selectedSedes = this.selectedSedes.map(id => parseInt(id));
                            }
                        }" @click.away="open = false" wire:ignore>

                            {{-- Label (sin cambios) --}}
                            <label for="sedes-alpine-trigger" class="form-label mb-1">Sedes habilitadas</label>

                            {{-- 3. Los botones externos de "Todos" y "Ninguno" han sido eliminados de aquí. --}}

                            {{-- Botón/Input falso que muestra selección y abre el dropdown (sin cambios) --}}
                            <div class="form-control @error('selectedSedes') is-invalid @enderror @error('selectedSedes.*') is-invalid @enderror"
                                @click="open = !open"
                                style="min-height: 38px; cursor: pointer; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                id="sedes-alpine-trigger" x-text="selectedSedesNombres"
                                :class="{ 'is-invalid': {{ $errors->has('selectedSedes') || $errors->has('selectedSedes.*') ? 'true' : 'false' }} }">
                            </div>

                            {{-- Mensajes de error (sin cambios) --}}
                            @error('selectedSedes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('selectedSedes.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            {{-- Dropdown con opciones y búsqueda --}}
                            <div x-show="open" x-transition
                                class="position-absolute mt-1 w-100 bg-body border rounded shadow-lg overflow-auto"
                                style="max-height: 250px; z-index: 1050; background:#fff !important; width: 97% !important;">

                                {{-- Campo de búsqueda (sin cambios) --}}
                                <div class="p-2 border-bottom">
                                    <input type="text" x-model.debounce.300ms="search"
                                        placeholder="Buscar sede..." class="form-control form-control-sm">
                                </div>

                                {{-- Lista de opciones --}}
                                <ul class="list-unstyled p-2 mb-0">

                                    {{-- 4. INICIO: Nueva opción para "Seleccionar/Quitar Todos" --}}
                                    <li class="form-check mb-2 pb-2 border-bottom">
                                        <input class="form-check-input" type="checkbox" id="select-all-sedes"
                                            @click="toggleSelectAll()" :checked="allSedesSelected">
                                        <label class="form-check-label fw-medium" for="select-all-sedes">
                                            Seleccionar Todos / Quitar Selección
                                        </label>
                                    </li>
                                    {{-- FIN: Nueva opción --}}

                                    <template x-for="sede in filteredSedes" :key="sede.id">
                                        <li class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox"
                                                :id="'sede-opt-' + sede.id" :value="sede.id"
                                                @change="toggleSede(sede.id)"
                                                :checked="selectedSedes.map(String).includes(String(sede.id))">
                                            <label class="form-check-label" :for="'sede-opt-' + sede.id"
                                                x-text="sede.nombre"></label>
                                        </li>
                                    </template>

                                    {{-- Mensaje si no hay resultados (sin cambios) --}}
                                    <template x-if="filteredSedes.length === 0">
                                        <li class="text-muted text-center p-2"
                                            x-text="search === '' ? 'No hay sedes disponibles.' : 'No se encontraron sedes.'">
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        {{-- FIN: Campo Sedes habilitadas --}}

                    </div>
                </div>
            </div>
            {{-- FIN PASO 1 --}}

            {{-- PASO 2: Configuración de Cortes del Periodo --}}
            <div class="step @if ($currentStep == 2) active @endif" id="step-2-configuracion-cortes">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Cortes del periodo: {{ $periodoActual ? $periodoActual->nombre : 'N/A' }}
                        </h5>
                        <small class="text-muted">
                            Periodo:
                            {{ $periodoActual ? Carbon::parse($periodoActual->fecha_inicio)->format('d/m/Y') : '' }} -
                            {{ $periodoActual ? Carbon::parse($periodoActual->fecha_fin)->format('d/m/Y') : '' }}
                        </small>
                    </div>
                    <div class="card-body">
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif


                        @if (!empty($cortesDelPeriodo))
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Orden</th>
                                            <th>Nombre del corte</th>
                                            <th>Fecha inicio</th>
                                            <th>Fecha fin</th>
                                            <th>Porcentaje (%)</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        @foreach ($cortesDelPeriodo as $corte)
                                            <tr wire:key="corte-periodo-{{ $corte['id'] }}">
                                                <td>{{ $corte['orden'] }}</td>
                                                <td><strong>{{ $corte['nombre_display'] }}</strong></td>
                                                <td>{{ Carbon::parse($corte['fecha_inicio'])->format('d/m/Y') }}</td>
                                                <td>{{ Carbon::parse($corte['fecha_fin'])->format('d/m/Y') }}</td>
                                                <td>{{ $corte['porcentaje'] }}%</td>
                                                <td>
                                                    {{-- En la tabla de cortes del Paso 2 --}}
                                                    <button type="button" class="btn btn-md  btn-outline-secondary"
                                                        wire:click="editCorte({{ $corte['id'] }})"
                                                        title="Editar Corte">
                                                        Editar
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                No hay cortes definidos para este periodo o no se pudieron cargar.
                                @if ($periodoActual && $periodoActual->escuela && $periodoActual->escuela->cortesEscuela->isEmpty())
                                    <br>La escuela asociada no tiene cortes base configurados.
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- FIN PASO 2 --}}

            {{-- BOTONERA PRINCIPAL DEL WIZARD (FIJA AL FINAL) --}}
            <div class="w-100 fixed-bottom py-3 px-4 border-top bg-body">
                <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-4 d-grid gap-2 d-sm-flex">
                    @if ($currentStep > 1)
                        <button type="button" class="btn btn-label-secondary rounded-pill btn-outline-secondary px-4"
                            wire:click="previousStep">
                            <i class="ti ti-arrow-left me-1"></i>
                            <span class="align-middle">Volver al periodo</span>
                        </button>
                    @else
                        <span></span> {{-- Placeholder para mantener espacio --}}
                    @endif

                    @if ($currentStep == 1)
                        {{-- El botón de Continuar/Actualizar del Paso 1 ya está definido arriba dentro del <div class="step active"> --}}
                        {{-- Si lo quieres en la botonera fija, muévelo aquí y asegúrate que solo se muestre en el paso 1 --}}
                        <button type="button" class="btn btn-primary rounded-pill px-4 ms-auto"
                            wire:click="proceedToStep2OrCreatePeriodo" wire:loading.attr="disabled">
                            <span wire:loading wire:target="proceedToStep2OrCreatePeriodo"
                                class="spinner-border spinner-border-sm me-1" role="status"
                                aria-hidden="true"></span>
                            <span wire:loading.remove wire:target="proceedToStep2OrCreatePeriodo">
                                @if ($periodoId)
                                    Actualizar periodo
                                @else
                                    Continuar
                                @endif
                            </span>
                            <span wire:loading wire:target="proceedToStep2OrCreatePeriodo">Procesando...</span>
                            <i class="ti ti-arrow-right ms-1" wire:loading.remove
                                wire:target="proceedToStep2OrCreatePeriodo"></i>
                        </button>
                    @elseif($currentStep == $totalSteps)
                        {{-- Es decir, currentStep == 2 --}}
                        <button type="button" class="btn btn-primary rounded-pill px-4 ms-auto"
                            wire:click="finalizeConfiguration" wire:loading.attr="disabled">
                            <span wire:loading wire:target="finalizeConfiguration"
                                class="spinner-border spinner-border-sm me-1" role="status"
                                aria-hidden="true"></span>
                            Finalizar configuración
                        </button>
                    @endif
                </div>
            </div>

        </form> {{-- FIN Formulario Livewire --}}

    </div> {{-- Fin Contenedor Principal del Wizard --}}

    {{-- OFFCANVAS para Editar Corte --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditarCorte"
        aria-labelledby="offcanvasEditarCorteLabel" wire:ignore.self> {{-- IMPORTANTE: wire:ignore.self --}}
        <div class="offcanvas-header">
            <h5 id="offcanvasEditarCorteLabel" class="offcanvas-title fw-bold text-primary">
                {{-- Solo mostrar si hay un corte en edición para evitar errores --}}
                @if ($corteEnEdicionId)
                    Editar corte: {{ $corteEnEdicionData['nombre_display'] }}
                @else
                    Editar corte
                @endif
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0">
            {{-- Renderizar el formulario solo si hay un corte en edición activo --}}
            @if ($corteEnEdicionId)
                <form wire:submit.prevent="saveCorteEditado">
                    {{-- Flash message para éxito de guardado de corte --}}
                    @if (session()->has('success_corte'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success_corte') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Campo Fecha Inicio Corte --}}
                    <div class="mb-3">
                        <label for="edit_corte_fecha_inicio" class="form-label">Fecha inicio <span
                                class="text-danger">*</span></label>
                        <input type="date"
                            class="form-control @error('corteEnEdicionData.fecha_inicio') is-invalid @enderror"
                            id="edit_corte_fecha_inicio" wire:model.defer="corteEnEdicionData.fecha_inicio">
                        @error('corteEnEdicionData.fecha_inicio')
                            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Campo Fecha Fin Corte --}}
                    <div class="mb-3">
                        <label for="edit_corte_fecha_fin" class="form-label">Fecha fin <span
                                class="text-danger">*</span></label>
                        <input type="date"
                            class="form-control @error('corteEnEdicionData.fecha_fin') is-invalid @enderror"
                            id="edit_corte_fecha_fin" wire:model.defer="corteEnEdicionData.fecha_fin">
                        @error('corteEnEdicionData.fecha_fin')
                            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Campo Porcentaje Corte --}}
                    <div class="mb-3">
                        <label for="edit_corte_porcentaje" class="form-label">Porcentaje (%) <span
                                class="text-danger">*</span></label>
                        <input type="number"
                            class="form-control @error('corteEnEdicionData.porcentaje') is-invalid @enderror"
                            id="edit_corte_porcentaje" wire:model.defer="corteEnEdicionData.porcentaje"
                            min="0" max="100" step="0.01">
                        @error('corteEnEdicionData.porcentaje')
                            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    @error('corteEnEdicionData.general')
                        <div class="alert alert-danger py-2 mt-3">{{ $message }}</div>
                    @enderror


                    <div class="offcanvas-footer p-5 border-top border-2 px-8">
                        <div class="mt-4 d-flex justify-content-start">
                            <button type="submit" class="btn btn-primary me-2" wire:loading.attr="disabled"
                                wire:target="saveCorteEditado">
                                <span wire:loading wire:target="saveCorteEditado"
                                    class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span>
                                Guardar
                            </button>
                            {{-- Este botón "Cancelar" simplemente cierra el offcanvas vía Bootstrap.
                                    El evento 'hidden.bs.offcanvas' se encargará de llamar a clearCorteEnEdicion. --}}
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="offcanvas">Cancelar</button>
                        </div>
                    </div>
                </form>
            @else
                <p>Seleccione un corte para editar.</p>
            @endif
        </div>

    </div>

    @push('scripts')
        <script>
            // listeners para los sweet alert
            Livewire.on('abrirOffcanvas', () => {
                const nombreOffCanvas = event.detail.nombreModal;
                const backdrop = document.createElement('div');
                backdrop.className = 'offcanvas-backdrop fade show';
                document.body.appendChild(backdrop);

                var offcanvasElement = document.getElementById(nombreOffCanvas);
                var offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
                    backdrop: true
                });
                offcanvas.show();
                offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
                    backdrop.remove();
                });

            });

            Livewire.on('cerrarOffcanvas', () => {
                const nombreOffCanvas = event.detail.nombreModal;
                $('#' + nombreOffCanvas).offcanvas('hide');
                $('.offcanvas-backdrop').remove();
            });

            // --- Inicialización de Flatpickr (si se usa en Paso 1) ---
            if (typeof flatpickr !== 'undefined' && document.querySelector(".fecha-picker")) {
                flatpickr(".fecha-picker", {
                    dateFormat: "Y-m-d",
                    disableMobile: true, // Opcional
                });
            }
        </script>
    @endpush
</div>
