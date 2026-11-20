<div class="row align-items-center mx-1 mx-sm-3 my-2">
    <div class="p-2 p-sm-4 col-12">


        <div class="row redil-carrito-content">
            @if($pasoActual == 1)
                <h3 class="fw-semibold p-0 mb-3">Configura tu carrito de matrículas</h3>

            {{-- === Selector global de monedas === --}}

            <h4 class="fw-semibold p-0 mb-3">Elige una moneda</h4>

            <div class="mb-4 p-0 row g-3 container-monedas">
                @foreach ($monedasActividad as $mon)
                    <div class="col-12 col-md-6 ps-0 pe-md-3">
                        <label class="container-moneda-{{ $mon->id }} d-flex align-items-center justify-content-between p-4 border border-2 rounded w-100 cursor-pointer {{ $monedaSeleccionada == $mon->id ? 'border-primary bg-light' : '' }}">
                            <span class="d-flex align-items-center">
                                <i class="fis fi fi-{{ $mon->codigo_alpha }} rounded-circle fs-2"></i>
                                <span class="ms-3 fw-medium text-black">{{ $mon->nombre }}</span>
                            </span>
                            <input type="radio" data-id="{{ $mon->id }}"
                                name="monedaActividadRadio"
                                @checked($monedaSeleccionada == $mon->id)
                                class="radioMoneda form-check-input m-0" wire:click="setMoneda({{ $mon->id }})">
                        </label>
                    </div>
                @endforeach
            </div>

            <!-- Listado de categorías habilitadas -->
            <div id="card-materias" class="row card px-3 px-md-6 py-4 py-md-6 mb-4 shadow border-top-0 border-1">
                <h4 class="fw-semibold p-0 mb-3">Elige tu materia a matricular</h4>

                @foreach ($categoriasHabilitadas as $categoria)
                    @php
                        $esMateriaActiva = $selectedMateriaPeriodo == $categoria->materia_periodo_id;
                    @endphp
                    <div id="categoria-container{{ $categoria->id }}"
                        class="col-md-6 col-12 mb-3 p-0 px-md-2 categoria-container"
                        wire:click="loadSedes({{ $categoria->materia_periodo_id }})">

                        <div class="card-body text-center border rounded shadow-sm h-100 cursor-pointer {{ $esMateriaActiva ? 'border-primary border-2' : '' }}">
                            <div class="container-name text-start d-flex justify-content-between align-items-start gap-2">
                                <label style="font-size:18px;"
                                    class="pb-1 text-black cursor-pointer mb-0"><b>{{ $categoria->nombre }}</b></label>
                                <input name="categoriaSeleccionadaRadio" type="radio"
                                    @checked($esMateriaActiva)
                                    class="form-check-input flex-shrink-0 mt-1">
                            </div>

                            <div class="d-flex flex-wrap gap-3 justify-content-between my-4">
                                <div class="d-flex flex-row align-items-start">
                                    <i class="ti ti-calendar-week mt-1"></i>
                                    <div class="d-flex flex-column text-start">
                                        <small class="text-black ms-1">Fecha inicio periodo: </small>
                                        <small class="fw-semibold ms-1 text-black">
                                            {{ \Carbon\Carbon::parse($actividad->fecha_inicio)->locale('es')->translatedFormat('d \d\e F Y') }}
                                        </small>
                                    </div>
                                </div>

                                <div class="d-flex flex-row align-items-start">
                                    <i class="ti ti-calendar-week mt-1"></i>
                                    <div class="d-flex flex-column text-start">
                                        <small class="text-black ms-1">Fecha fin periodo: </small>
                                        <small class="fw-semibold ms-1 text-black">
                                            {{ \Carbon\Carbon::parse($actividad->fecha_finalizacion)->locale('es')->translatedFormat('d \d\e F Y') }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-row justify-content-between mt-3">
                                <div class="d-flex flex-row align-items-start">
                                    <i class="ti ti-receipt-2 mt-1"></i>
                                    <div class="d-flex flex-column text-start">
                                        <small class="text-black text-start ms-1">Costo: </small>
                                        <small class="fw-semibold ms-1 text-black text-start">
                                            @php
                                                $mon = $monedasActividad->firstWhere('id', $monedaSeleccionada);
                                                $valor = $this->precioCategoria($categoria);
                                            @endphp
                                            <div class="fw-semibold">
                                                <small>{{ $mon?->nombre }}</small><br>
                                                {{ Number::currency($valor) }}
                                            </div>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bloque para mostrar el select de sedes -->

            @if (count($sedes) > 0)
                <div id="container-sedes"
                    class="card row py-3 px-0 shadow border-top-0 border-1 mb-4 col-12 redil-card-select @if (empty($sedes)) d-none @endif"
                    wire:key="alpine-sedes-{{ $selectedMateriaPeriodo ?? 'initial' }}">

                    <ul class="list-group mb-2">
                        <li class="list-group-item border-0 px-3 px-md-6 py-2">
                            <div class="d-flex gap-4">
                                <div class="flex-grow-1">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <h4 class="fw-semibold p-0 mb-2">Selecciona la sede donde deseas matricularte</h4>
                                                <label class="form-label fw-medium" for="sede-button">Sede</label>

                                                <div x-data="{
                                                    open: false,
                                                    search: '',
                                                    options: @js($sedes),
                                                    selected: @entangle('sedeSeleccionada').live,
                                                    selectedLabel: 'Elige una sede',

                                                    normalize(str) {
                                                        return String(str || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                                                    },
                                                    get filteredOptions() {
                                                        if (this.search.trim() === '') return this.options;
                                                        const q = this.normalize(this.search);
                                                        return this.options.filter(option =>
                                                            this.normalize(option.nombre).includes(q)
                                                        );
                                                    },
                                                    toggle() {
                                                        this.open = !this.open;
                                                        if (this.open) {
                                                            this.$nextTick(() => {
                                                                this.$refs.searchInput?.focus({ preventScroll: true });
                                                                const rect = this.$el.getBoundingClientRect();
                                                                if (rect.bottom > window.innerHeight - 260) {
                                                                    this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                                                }
                                                            });
                                                        }
                                                    },
                                                    selectOption(id, nombre) {
                                                        if (this.selected !== id) {
                                                            this.selected = id;
                                                            this.selectedLabel = nombre;
                                                        }
                                                        this.open = false;
                                                        this.search = '';
                                                    },
                                                    updateLabel() {
                                                        let found = this.options.find(o => o.id == this.selected);
                                                        this.selectedLabel = found ? found.nombre : 'Elige una sede';
                                                    }
                                                }" x-init="updateLabel();
                                                $watch('selected', () => updateLabel());
                                                $watch('options', () => updateLabel())"
                                                    class="position-relative p-0"
                                                    :class="{ 'redil-select-open': open }">

                                                    {{-- Botón selector --}}
                                                    <button type="button" id="sede-button"
                                                        @click="toggle()"
                                                        class="form-select redil-select-trigger text-start d-flex align-items-center justify-content-between"
                                                        :aria-expanded="open"
                                                        aria-haspopup="listbox"
                                                        :class="{
                                                            'border-danger': {{ $errors->has('sedeSeleccionada') ? 'true' : 'false' }},
                                                            'border-primary shadow-sm': open
                                                        }">
                                                        <span class="text-truncate pe-2"
                                                            :class="{ 'text-muted': !selected, 'fw-medium text-dark': selected }"
                                                            x-text="selectedLabel"></span>
                                                    </button>

                                                    {{-- Panel desplegable con encabezado fijo y lista con scroll --}}
                                                    <div x-show="open"
                                                        x-cloak
                                                        @click.outside="open = false"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="opacity-0"
                                                        x-transition:enter-end="opacity-100"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="opacity-100"
                                                        x-transition:leave-end="opacity-0"
                                                        class="redil-select-dropdown"
                                                        aria-labelledby="sede-button"
                                                        role="listbox">

                                                        {{-- Buscador fijo en la parte superior del dropdown --}}
                                                        <div class="redil-select-search">
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text bg-white border-end-0 text-muted">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="search"
                                                                    x-model="search"
                                                                    x-ref="searchInput"
                                                                    placeholder="Buscar sede..."
                                                                    class="form-control border-start-0 ps-0"
                                                                    @keydown.escape.prevent="open = false">
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                                                <small class="text-muted" style="font-size: 0.75rem;"
                                                                    x-text="filteredOptions.length + (filteredOptions.length === 1 ? ' sede disponible' : ' sedes disponibles')"></small>
                                                                <button type="button"
                                                                    x-show="search.length > 0"
                                                                    @click="search = ''; $refs.searchInput?.focus()"
                                                                    class="btn btn-link btn-xs p-0 text-decoration-none"
                                                                    style="font-size: 0.75rem;">
                                                                    Limpiar
                                                                </button>
                                                            </div>
                                                        </div>

                                                        {{-- Lista de opciones con altura máxima y scroll propio --}}
                                                        <ul class="redil-select-list list-unstyled p-0 m-0">
                                                            <template x-if="filteredOptions.length === 0 && search.length > 0">
                                                                <li class="px-3 py-3 text-center text-muted small">
                                                                    <i class="ti ti-Mood-empty me-1"></i> No se encontraron sedes con "<span x-text="search"></span>".
                                                                </li>
                                                            </template>

                                                            <template x-for="option in filteredOptions" :key="option.id">
                                                                <li @click="selectOption(option.id, option.nombre)"
                                                                    @keydown.enter.prevent="selectOption(option.id, option.nombre)"
                                                                    @keydown.space.prevent="selectOption(option.id, option.nombre)"
                                                                    @keydown.escape.prevent="open = false"
                                                                    role="option"
                                                                    :aria-selected="selected == option.id"
                                                                    tabindex="0"
                                                                    class="redil-select-option d-flex align-items-center justify-content-between"
                                                                    :class="{ 'is-selected': selected == option.id }">
                                                                    <span class="me-2" x-text="option.nombre"></span>
                                                                    <i class="ti ti-check text-primary flex-shrink-0" x-show="selected == option.id"></i>
                                                                </li>
                                                            </template>

                                                            <template x-if="options.length === 0">
                                                                <li class="px-3 py-3 text-center text-muted small">
                                                                    No hay sedes disponibles.
                                                                </li>
                                                            </template>
                                                        </ul>
                                                    </div>

                                                    @error('sedeSeleccionada')
                                                        <div class="text-danger mt-1"><small>{{ $message }}</small></div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            @endif

            <!-- Bloque para mostrar tipos de aula y horarios -->
            <div id="container-horarios"
                class="row card px-3 px-md-6 py-4 py-md-6 redil-card-select @if (!$tiposAula) d-none @endif mb-5 shadow border-top-0 border-1">

                {{-- Selector Tipo Aula --}}
                <div class="mb-3 mt-1 row">
                    <h4 class="fw-semibold p-0 mb-2">Configura tu horario</h4>
                    <label class="form-label p-0 mb-2">Elige un tipo de aula</label>
                    @forelse ($tiposAula as $tipo)
                        <div class="ps-0 pe-md-3 mb-3 col-md-6 col-12">
                            <label class="w-100 p-4 border border-2 rounded d-flex align-items-center justify-content-between cursor-pointer {{ $tipoAulaSeleccionado == $tipo['id'] ? 'border-primary bg-light' : '' }}">
                                <span style="font-size:17px" class="fw-medium text-black">{{ $tipo['nombre'] }}</span>
                                <input type="radio" name="tipoAulaRadio"
                                    class="form-check-input m-0" wire:click="setTipoAula({{ $tipo['id'] }})"
                                    wire:model.live="tipoAulaSeleccionado"
                                    value="{{ $tipo['id'] }}">
                            </label>
                        </div>
                    @empty
                        <p class="text-muted p-0">No hay tipos de aula disponibles para la sede seleccionada.</p>
                    @endforelse
                </div>

                <!-- Bloque para mostrar los horarios filtrados -->
                <div class="mb-2 mt-2 row @if (empty($horarios) || !$tipoAulaSeleccionado) d-none @endif"
                    wire:key="alpine-horarios-{{ $tipoAulaSeleccionado ?? 'none' }}-{{ $sedeSeleccionada ?? 'none' }}">
                    <label class="form-label p-0 fw-medium" for="horario-button">Horario</label>

                    <div x-data="{
                        open: false,
                        search: '',
                        options: @js($horarios),
                        selected: @entangle('horarioSeleccionado').live,
                        selectedLabel: 'Seleccione un horario',

                        normalize(str) {
                            return String(str || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                        },
                        get filteredOptions() {
                            if (this.search.trim() === '') return this.options;
                            const q = this.normalize(this.search);
                            return this.options.filter(option =>
                                this.normalize(option.label).includes(q)
                            );
                        },
                        toggle() {
                            this.open = !this.open;
                            if (this.open) {
                                this.$nextTick(() => {
                                    this.$refs.searchHorarioInput?.focus({ preventScroll: true });
                                    const rect = this.$el.getBoundingClientRect();
                                    if (rect.bottom > window.innerHeight - 260) {
                                        this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                });
                            }
                        },
                        selectOption(id, label) {
                            this.selected = id;
                            this.selectedLabel = label;
                            this.open = false;
                            this.search = '';
                        },
                        updateLabel() {
                            let found = this.options.find(o => o.id == this.selected);
                            this.selectedLabel = found ? found.label : 'Seleccione un horario';
                        }
                    }" x-init="updateLabel(); $watch('selected', () => updateLabel()); $watch('options', () => updateLabel())"
                        class="position-relative p-0"
                        :class="{ 'redil-select-open': open }">

                        <button type="button" id="horario-button"
                            @click="toggle()"
                            class="form-select redil-select-trigger text-start d-flex align-items-center justify-content-between"
                            :aria-expanded="open"
                            aria-haspopup="listbox"
                            :class="{
                                'border-danger': {{ $errors->has('horarioSeleccionado') ? 'true' : 'false' }},
                                'border-primary shadow-sm': open
                            }">
                            <span class="pe-2"
                                :class="{ 'text-muted': !selected, 'fw-medium text-dark': selected }"
                                x-text="selectedLabel"></span>
                        </button>

                        <div x-show="open"
                            x-cloak
                            @click.outside="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="redil-select-dropdown"
                            aria-labelledby="horario-button"
                            role="listbox">

                            <div class="redil-select-search" x-show="options.length > 4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="ti ti-search"></i>
                                    </span>
                                    <input type="search"
                                        x-model="search"
                                        x-ref="searchHorarioInput"
                                        placeholder="Filtrar por día, hora, aula o maestro..."
                                        class="form-control border-start-0 ps-0"
                                        @keydown.escape.prevent="open = false">
                                </div>
                            </div>

                            <ul class="redil-select-list list-unstyled p-0 m-0">
                                <template x-if="filteredOptions.length === 0 && search.length > 0">
                                    <li class="px-3 py-3 text-center text-muted small">
                                        No se encontraron horarios con "<span x-text="search"></span>".
                                    </li>
                                </template>

                                <template x-for="option in filteredOptions" :key="option.id">
                                    <li @click="selectOption(option.id, option.label)"
                                        @keydown.enter.prevent="selectOption(option.id, option.label)"
                                        @keydown.space.prevent="selectOption(option.id, option.label)"
                                        @keydown.escape.prevent="open = false"
                                        role="option"
                                        :aria-selected="selected == option.id"
                                        tabindex="0"
                                        class="redil-select-option d-flex align-items-center justify-content-between"
                                        :class="{ 'is-selected': selected == option.id }">
                                        <span class="me-2" x-text="option.label"></span>
                                        <i class="ti ti-check text-primary flex-shrink-0" x-show="selected == option.id"></i>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        @error('horarioSeleccionado')
                            <div class="text-danger mt-1"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                </div>
            </div>
            @endif

            @if ($pasoActual == 2)
                <div id="card-formulario" class="row card px-6 py-9 mb-5 shadow border-top-0 border-1">
                    <h3 class="fw-semibold p-0">Información Adicional</h3>
                    <p class="text-muted p-0">Por favor completa los siguientes datos para finalizar tu matrícula.</p>

                    <div class="row mt-4">
                        @foreach ($elementosFormulario as $elemento)
                            @php
                                $tipo = $elemento->tipoElemento->getRawOriginal('clase') ?? $elemento->tipoElemento->clase;
                            @endphp

                            <div class="col-12 mb-4 px-0">
                                @if ($tipo == 'informativo')
                                    <div class="alert alert-info border-0 shadow-sm">
                                        <h5 class="fw-bold mb-1">{{ $elemento->titulo }}</h5>
                                        <div>{!! $elemento->descripcion !!}</div>
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label class="form-label fw-bold @if($elemento->required) required @endif">
                                            {{ $elemento->titulo }}
                                        </label>

                                        @if ($tipo == 'corta')
                                            <input type="text" wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control" placeholder="{{ $elemento->descripcion }}">
                                        @elseif ($tipo == 'larga')
                                            <textarea wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control" rows="3" placeholder="{{ $elemento->descripcion }}"></textarea>
                                        @elseif ($tipo == 'numero')
                                            <input type="number" wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control" placeholder="{{ $elemento->descripcion }}">
                                        @elseif ($tipo == 'fecha')
                                            <input type="date" wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control">
                                        @elseif ($tipo == 'si_no')
                                            <select wire:model.defer="respuestas.{{ $elemento->id }}" class="form-select">
                                                <option value="">Seleccione...</option>
                                                <option value="1">Sí</option>
                                                <option value="0">No</option>
                                            </select>
                                        @elseif ($tipo == 'unica_respuesta')
                                            @php $opciones = explode(',', $elemento->opciones); @endphp
                                            @foreach ($opciones as $opcion)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" wire:model.defer="respuestas.{{ $elemento->id }}" value="{{ trim($opcion) }}" id="opt-{{ $elemento->id }}-{{ $loop->index }}">
                                                    <label class="form-check-label" for="opt-{{ $elemento->id }}-{{ $loop->index }}">{{ trim($opcion) }}</label>
                                                </div>
                                            @endforeach
                                        @endif

                                        @if ($elemento->required)
                                            <small class="text-muted italic">Este campo es obligatorio.</small>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Botones de acción -->
            <div class="w-100 fixed-bottom py-3 py-md-4 px-3 px-sm-0 border-top shadow-lg" style="background-color: #FFF; z-index: 1040;">
                <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2 d-flex justify-content-between align-items-center gap-2">
                    <div>
                        @if ($pasoActual > 1)
                            <button type="button" class="btn btn-label-secondary rounded-pill px-3 px-sm-4" wire:click="volverPaso">
                                <i class="ti ti-chevron-left me-1"></i> Anterior
                            </button>
                        @endif
                    </div>

                    <div>
                        @if ($pasoActual < $totalPasos)
                            <button type="button" class="btn btn-primary rounded-pill px-4 px-sm-5 fw-bold" wire:click="siguientePaso">
                                Siguiente <i class="ti ti-chevron-right ms-1"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-success rounded-pill px-4 px-sm-5 fw-bold" wire:click="crearMatricula">
                                <i class="ti ti-check me-1"></i> Finalizar Matrícula
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- no eliminar este div por favor-->
        <style>
            [x-cloak] {
                display: none !important;
            }

            .redil-carrito-content {
                margin-bottom: 180px;
                padding-bottom: 60px;
            }

            .cursor-pointer {
                cursor: pointer;
            }

            .form-check-input {
                border: 2px solid #000;
            }

            /* === Sistema de Select Buscable (Desktop & Mobile) === */
            .redil-card-select {
                position: relative;
                overflow: visible !important;
            }

            .redil-card-select:has(.redil-select-open) {
                z-index: 1035;
            }

            .redil-select-open {
                z-index: 1050;
            }

            .redil-select-trigger {
                min-height: 48px;
                height: auto !important;
                padding: 0.625rem 2.5rem 0.625rem 0.875rem;
                white-space: normal;
                word-break: break-word;
                line-height: 1.35;
                cursor: pointer;
                background-color: #fff;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }

            .redil-select-dropdown {
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                right: 0;
                width: 100%;
                background-color: #ffffff;
                border: 1px solid #d9dee3;
                border-radius: 0.5rem;
                box-shadow: 0 0.75rem 1.75rem rgba(34, 48, 62, 0.18);
                z-index: 1055;
                overflow: hidden;
            }

            .redil-select-search {
                position: sticky;
                top: 0;
                z-index: 2;
                background-color: #ffffff;
                padding: 0.625rem;
                border-bottom: 1px solid #eceef1;
            }

            .redil-select-list {
                max-height: 230px;
                overflow-y: auto;
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }

            .redil-select-list::-webkit-scrollbar {
                width: 6px;
            }

            .redil-select-list::-webkit-scrollbar-track {
                background: #f5f5f9;
            }

            .redil-select-list::-webkit-scrollbar-thumb {
                background-color: #c4c8d0;
                border-radius: 10px;
            }

            .redil-select-option {
                padding: 0.6rem 0.875rem;
                min-height: 42px;
                cursor: pointer;
                font-size: 0.9375rem;
                color: #384551;
                border-bottom: 1px solid #f5f6f8;
                transition: background-color 0.12s ease, color 0.12s ease;
                word-break: break-word;
            }

            .redil-select-option:last-child {
                border-bottom: none;
            }

            .redil-select-option:hover,
            .redil-select-option:focus {
                background-color: #f2f4f8;
                color: #1d262d;
                outline: none;
            }

            .redil-select-option.is-selected {
                background-color: #eef3ff;
                color: #1977E5;
                font-weight: 600;
            }

            .minus {
                border: solid 2px #1977E5 !important;
                border-radius: 20px;
                padding: 2px !important;
                width: 31px;
                height: 30px;
                margin-right: 6px;
                color: #1977E5;
                background: transparent;
            }

            .plus {
                border: solid 2px #1977E5 !important;
                border-radius: 20px;
                padding: 2px !important;
                width: 31px;
                height: 30px;
                margin-left: 6px;
                color: #1977E5;
                background: transparent;
            }

            input[type=number]::-webkit-inner-spin-button,
            input[type=number]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            input[type=number] {
                -moz-appearance: textfield;
            }

            body {
                overflow-x: hidden;
                background: #FFF;
            }

            /* Estilos personalizados para el stepper */
            .step {
                margin: 20px;
            }

            .bs-stepper-icon {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                transition: all 0.3s ease;
            }

            .step.active .bs-stepper-icon {
                box-shadow: 0 4px 6px rgba(59, 113, 254, 0.2);
            }

            .step.active .bs-stepper-icon svg {
                fill: white;
                width: 60%;
                height: 60%;
            }

            #card-materias,
            #container-sedes,
            #container-horarios {
                margin-left: 0px !important;
            }

            @media (max-width: 768px) {
                .redil-carrito-content {
                    margin-bottom: 220px;
                    padding-bottom: 80px;
                }

                .redil-select-list {
                    max-height: 200px;
                }

                .redil-select-option {
                    padding: 0.7rem 0.875rem;
                    font-size: 0.9rem;
                }

                .container-monedas {
                    margin-left: 0px !important;
                }

                .bs-stepper-icon {
                    width: 50px;
                    height: 50px;
                }

                .line {
                    display: none !important;
                }

                .bs-stepper-label {
                    font-size: 0.8rem;
                }
            }
        </style>


        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: 'Seleccionar opciones',
                    dropdownParent: $('#container-sedes'),
                    allowClear: true
                });
            });

            window.addEventListener('horario-selected', event => {
                Swal.fire({
                    title: '¡Felicidades!',
                    text: 'Has seleccionado el horario con ID: ' + event.detail.horarioId,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });

            Livewire.on('msn', (data) => {
                Swal.fire({
                    title: data[0].msnTitulo,
                    html: data[0].msnTexto,

                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });

            Livewire.on('mostrarMensaje', (data) => {
                Swal.fire({
                    title: data[0].msnTitulo,
                    html: data[0].msnTexto,

                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });


            document.addEventListener('livewire:initialized', () => {
                Livewire.on('mostrarMensajeMoneda', (data) => {
                    Swal.fire({
                        title: data[0].titulo,
                        text: data[0].mensaje,
                        icon: data[0].tipo,
                        confirmButtonText: 'OK',
                        cancelButton: true
                    }).then((result) => {

                        // location.reload(); // Recargar la página

                    });
                });
            });

            $(function() {
                // variable donde guardamos la moneda seleccionada
                let monedaActual = null;

                // al hacer clic en cualquier radio con clase radioMoneda
                $(document).on('click', '.radioMoneda', function() {
                    const nuevoId = $(this).data('id'); // id de la moneda del radio clicado

                    // si es la misma moneda, no hacemos nada
                    if (monedaActual === nuevoId) {
                        $(this).prop('checked', true); // nos aseguramos que siga marcado
                        return;
                    }

                    // si cambia, desmarcamos todos y marcamos solo el clicado
                    $('.radioMoneda').prop('checked', false);
                    $(this).prop('checked', true);

                    // actualizamos la variable
                    monedaActual = nuevoId;
                });

                // opcional: marcar la primera moneda al cargar
                const primera = $('.radioMoneda').first();
                if (primera.length) {
                    primera.trigger('click');
                }
            });
        </script>

    </div>
