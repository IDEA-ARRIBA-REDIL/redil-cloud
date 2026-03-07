<div style="margin: 2% 2%;" class="row align-items-center">
    <div class="p-4 col-lg-12 col-md-10 col-sm-12">
        <!-- Stepper (Paso a Paso) - Versión Mejorada -->
        @if ($configuracion->version == 2)
            <div class="m-lg-auto border-0 mb-4">
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 gap-md-2">
                    <!-- Paso 1 - Carrito -->
                    <div class="step {{ $pasoActual == 1 ? 'active' : '' }} d-flex align-items-center">
                        <button type="button" class="step-trigger bg-transparent border-0 p-0" wire:click="volverPaso">
                            <span class="bs-stepper-icon {{ $pasoActual == 1 ? 'btn-primary' : 'bg-light' }}">
                                <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                    <use xlink:href="{{ asset('assets/svg/icons/wizard-checkout-cart.svg#wizardCart') }}"></use>
                                </svg>
                            </span>
                            <span class="bs-stepper-label d-none d-md-block mt-2 {{ $pasoActual == 1 ? '' : 'text-muted' }}">Configuración Académica</span>
                        </button>
                    </div>

                    <div class="line d-none d-md-block">
                        <i class="ti ti-chevron-right fs-5 text-muted"></i>
                    </div>

                    <!-- Paso 2 - Formulario -->
                    <div class="step {{ $pasoActual == 2 ? 'active' : '' }} d-flex align-items-center">
                        <button type="button" class="step-trigger bg-transparent border-0 p-0" @if($pasoActual == 1) wire:click="siguientePaso" @endif>
                            <span class="bs-stepper-icon {{ $pasoActual == 2 ? 'btn-primary' : 'bg-light' }}">
                                <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                    <use xlink:href="{{ asset('assets/svg/icons/wizard-checkout-address.svg#wizardCheckoutAddress') }}"></use>
                                </svg>
                            </span>
                            <span class="bs-stepper-label d-none d-md-block mt-2 {{ $pasoActual == 2 ? '' : 'text-muted' }}">Información Adicional</span>
                        </button>
                    </div>

                    <div class="line d-none d-md-block">
                        <i class="ti ti-chevron-right fs-5 text-muted"></i>
                    </div>

                    <!-- Paso 3 - Checkout -->
                    <div class="step d-flex align-items-center">
                        <div class="step-trigger bg-transparent border-0 p-0 opacity-50">
                            <span class="bs-stepper-icon bg-light">
                                <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                    <use xlink:href="{{ asset('assets/svg/icons/wizard-checkout-payment.svg#wizardPayment') }}"></use>
                                </svg>
                            </span>
                            <span class="bs-stepper-label d-none d-md-block mt-2 text-muted">Checkout</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <!-- Fin del Stepper Mejorado -->

        <div style="margin-bottom: 100px;" class="row">
            @if($pasoActual == 1)
                <h3 class="fw-semibold p-0">Configura tu carrito de matriculas</h3>

            {{-- === Selector global de monedas === --}}

            <h4 class="fw-semibold p-0">Elije una moneda</h4>

            <div class="mb-4 p-0 row container-monedas">
                @foreach ($monedasActividad as $mon)
                    <div class="col-12 ms-sm-3  col-md-6 ps-0 pe-2 ">
                        <div class="container-moneda-{{ $mon->id }}  p-6 border  border-2 rounded ">

                            <i class="fis fi fi-{{ $mon->codigo_alpha }} rounded-circle fs-2"></i>

                            <label class="ms-5 me-3">
                                {{ $mon->nombre }}
                            </label>
                            <input type="radio" data-id="{{ $mon->id }}"
                                class="radioMoneda mt-2 form-check-input" wire:click="setMoneda({{ $mon->id }})">
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Listado de categorías habilitadas -->
            <div id="card-materias" class="row card px-6 py-9  mb-5 shadow border-top-0 border-1 ">
                <h4 class="fw-semibold p-0">Elije tu materia a matricular</h4>

                @foreach ($categoriasHabilitadas as $categoria)
                    <div id="categoria-container{{ $categoria->id }}"
                        class="col-md-6 col-12 shadow mb-9 p-0 card categoria-container"
                        wire:click="loadSedes({{ $categoria->materia_periodo_id }})">

                        <div class="card-body text-center border rounded">
                            <div class="container-name text-start">
                                <label style="font-size:18px;"
                                    class="pb-1 text-black"><b>{{ $categoria->nombre }}</b></label>
                                <input name="categoriaSeleccionada{{ $categoria->id }}" type="radio"
                                    class="form-check-input">
                            </div>

                            <div class="d-flex flex-row justify-content-between my-5">
                                <div class="d-flex flex-row">
                                    <i class="ti ti-calendar-week"></i>
                                    <div class="d-flex flex-column text-star">
                                        <small class="text-black ms-1">Fecha inicio periodo: </small>
                                        <small class="fw-semibold ms-1 text-black ">
                                            {{ \Carbon\Carbon::parse($actividad->fecha_inicio)->locale('es')->translatedFormat('d \d\e F Y') }}
                                        </small>
                                    </div>
                                </div>

                                <div class="d-flex flex-row">
                                    <i class="ti ti-calendar-week"></i>
                                    <div class="d-flex flex-column text-star">
                                        <small class="text-black ms-1">Fecha fin periodo: </small>
                                        <small class="fw-semibold ms-1 text-black ">
                                            {{ \Carbon\Carbon::parse($actividad->fecha_finalizacion)->locale('es')->translatedFormat('d \d\e F Y') }}

                                        </small>
                                    </div>
                                </div>

                            </div>


                            <div class="d-flex flex-row justify-content-between my-5">
                                <div class="d-flex flex-row">
                                    <i class="ti ti-receipt-2"></i>
                                    <div class="d-flex flex-column text-star">
                                        <small class="text-black text-start ms-1">Costo: </small>
                                        <small class="fw-semibold ms-1 text-black text-start">
                                            @php
                                                $mon = $monedasActividad->firstWhere('id', $monedaSeleccionada);
                                                $valor = $this->precioCategoria($categoria);
                                            @endphp
                                            <div class="fw-semibold">
                                                <small>{{ $mon->nombre }}</small><br>
                                                {{ Number::currency($valor) }}
                                            </div>
                                    </div>

                                    </small>
                                </div>
                            </div>


                        </div>


                    </div>
                @endforeach
            </div>

            <!-- Bloque para mostrar el select de sedes -->

            @if (count($sedes) > 0)
                <div id="container-sedes"
                    class="card row py-2 px-0 shadow border-top-0 border-1 mb-5 col-12 @if (empty($sedes)) d-none @endif"
                    wire:key="alpine-sedes-{{ $selectedMateriaPeriodo ?? 'initial' }}" {{-- Añadir wire:key para forzar reinicio de Alpine si cambia la materia --}}>

                    <ul class="list-group mb-4">
                        <li class="list-group-item border-0 px-6 py-3"> {{-- Cambiado border-none a border-0 --}}
                            <div class="d-flex gap-4">
                                <div class="flex-grow-1">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <h4 class="fw-semibold p-0">Selecciona la sede donde deseas matricularte
                                                </h4>
                                                <label class="form-label" for="sede-button">Sede</label>

                                                <div x-data="{
                                                    open: false,
                                                    search: '',
                                                    options: @js($sedes), // Obtiene las sedes desde Livewire
                                                    selected: @entangle('sedeSeleccionada').live, // Vincula con Livewire
                                                    selectedLabel: 'Elige una sede',

                                                    get filteredOptions() {
                                                        if (this.search === '') return this.options;
                                                        return this.options.filter(option =>
                                                            option.nombre.toLowerCase().includes(this.search.toLowerCase())
                                                        );
                                                    },
                                                    selectOption(id, nombre) {
                                                        if (this.selected !== id) { // Evita re-seleccionar lo mismo
                                                            this.selected = id; // Actualiza Alpine (y Livewire via entangle)
                                                            this.selectedLabel = nombre;
                                                            // NO necesitamos llamar a $wire.loadHorarios aquí,
                                                            // porque updatedSedeSeleccionada en Livewire lo hará.
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
                                                    {{-- Actualiza label al inicio y si cambia selected u options --}} class="position-relative p-0">

                                                    {{-- El Botón que muestra la selección y abre el dropdown --}}
                                                    <button style="height:50px" type="button" id="sede-button"
                                                        @click="open = !open" class="form-select text-start"
                                                        {{-- Estilo como un select --}} :aria-expanded="open"
                                                        aria-haspopup="listbox"
                                                        :class="{ 'border-danger': {{ $errors->has('sedeSeleccionada') ? 'true' : 'false' }} }"
                                                        {{-- Estilo de error --}}>
                                                        <span x-text="selectedLabel"></span>
                                                    </button>

                                                    {{-- El Dropdown con búsqueda --}}
                                                    <div x-show="open" @click.away="open = false"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="opacity-0 scale-95"
                                                        x-transition:enter-end="opacity-100 scale-100"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="opacity-100 scale-100"
                                                        x-transition:leave-end="opacity-0 scale-95"
                                                        class="position-absolute start-0 w-100 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-10 overflow-y-auto max-h-40"
                                                        x-trap.inert.noscroll="open" aria-labelledby="sede-button"
                                                        role="listbox">

                                                        {{-- Input de Búsqueda --}}
                                                        <div class="p-2">
                                                            <input type="search" x-model="search"
                                                                placeholder="Buscar sede..."
                                                                class="form-control form-control-sm"
                                                                @keydown.escape.prevent="open = false"
                                                                x-ref="searchInput"
                                                                @keydown.down.prevent="$focus.wrap().next()">
                                                        </div>

                                                        {{-- Lista de Opciones --}}
                                                        <ul class="list-unstyled p-0 m-0">
                                                            <template
                                                                x-if="filteredOptions.length === 0 && search.length > 0">
                                                                <li class="px-3 py-2 text-muted">No se encontraron
                                                                    sedes.
                                                                </li>
                                                            </template>

                                                            <template x-for="option in filteredOptions"
                                                                :key="option.id">
                                                                <li @click="selectOption(option.id, option.nombre)"
                                                                    @keydown.enter.prevent="selectOption(option.id, option.nombre)"
                                                                    @keydown.space.prevent="selectOption(option.id, option.nombre)"
                                                                    @keydown.escape.prevent="open = false"
                                                                    @keydown.up.prevent="$focus.wrap().previous()"
                                                                    @keydown.down.prevent="$focus.wrap().next()"
                                                                    role="option"
                                                                    :aria-selected="selected == option.id"
                                                                    tabindex="0" {{-- Hacer enfocable --}}
                                                                    class="px-3 py-2 hover-bg-primary hover-text-white cursor-pointer"
                                                                    :class="{
                                                                        'bg-label-primary text-white': selected ==
                                                                            option
                                                                            .id,
                                                                        'focus:bg-light': !(selected == option.id)
                                                                    }">
                                                                    <span x-text="option.nombre"></span>
                                                                </li>
                                                            </template>
                                                            <template x-if="options.length === 0">
                                                                <li class="px-3 py-2 text-muted">No hay sedes
                                                                    disponibles.
                                                                </li>
                                                            </template>
                                                        </ul>
                                                    </div>
                                                    {{-- Mensaje de error --}}
                                                    @error('sedeSeleccionada')
                                                        <div class="text-danger mt-1"><small>{{ $message }}</small>
                                                        </div>
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
                class="row card px-6 py-9 @if (!$tiposAula) d-none @endif  mb-5 shadow border-top-0 border-1 ">


                {{-- Selector Tipo Aula (Radios - Sin cambios significativos) --}}
                <div class="mb-4 mt-4 row">
                    <h4 class="fw-semibold p-0">Configura tu horario</h4>
                    <label class="form-label p-0">Elige un tipo de aula</label>
                    @forelse ($tiposAula as $tipo)
                        <div class=" ps-0 pe-3 mb-3 col-md-6 col-12">
                            <label class="w-100  p-6 border border-2 rounded " style="cursor: pointer;">
                                <span style="font-size:18px"> {{ $tipo['nombre'] }} </span>
                                <input style="float:right" type="radio" name="tipoAulaRadio"
                                    class="form-check-input me-2" wire:click="setTipoAula({{ $tipo['id'] }})"
                                    wire:model.live="tipoAulaSeleccionado" {{-- Usar wire:model para estado visual --}}
                                    value="{{ $tipo['id'] }}">

                            </label>
                        </div>
                    @empty
                        <p class="text-muted p-0">No hay tipos de aula disponibles para la sede seleccionada.</p>
                    @endforelse
                </div>

                <!-- Bloque para mostrar los horarios filtrados -->
                <div class="mb-4 mt-4 row @if (empty($horarios) || !$tipoAulaSeleccionado) d-none @endif"
                    wire:key="alpine-horarios-{{ $tipoAulaSeleccionado ?? 'none' }}-{{ $sedeSeleccionada ?? 'none' }}">
                    <label class="form-label p-0" for="horario-button">Horario</label>

                    <div x-data="{
                        open: false,
                        search: '',
                        options: @js($horarios),
                        selected: @entangle('horarioSeleccionado').live,
                        selectedLabel: 'Seleccione un horario',
                        updateLabel() {
                            let found = this.options.find(o => o.id == this.selected);
                            this.selectedLabel = found ? found.label : 'Seleccione un horario';
                        }
                    }" x-init="updateLabel(); $watch('selected', () => updateLabel()); $watch('options', () => updateLabel())"
                        class="position-relative p-0">

                        <button style="height:50px" type="button" @click="open = !open" class="form-select text-start">
                            <span x-text="selectedLabel"></span>
                        </button>

                        <div x-show="open" @click.away="open = false" class="position-absolute start-0 w-100 bg-white border rounded shadow z-10 overflow-y-auto max-h-40">
                            <ul class="list-unstyled p-0 m-0">
                                <template x-for="option in options" :key="option.id">
                                    <li @click="selected = option.id; selectedLabel = option.label; open = false;"
                                        class="px-3 py-2 hover-bg-primary hover-text-white cursor-pointer"
                                        :class="{ 'bg-label-primary text-white': selected == option.id }">
                                        <span x-text="option.label"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
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
            <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top shadow-lg" style="background-color: #FFF; z-index: 1000;">
                <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2 d-flex justify-content-between align-items-center">
                    <div>
                        @if ($pasoActual > 1)
                            <button type="button" class="btn btn-label-secondary rounded-pill px-4" wire:click="volverPaso">
                                <i class="ti ti-chevron-left me-1"></i> Anterior
                            </button>
                        @endif
                    </div>

                    <div>
                        @if ($pasoActual < $totalPasos)
                            <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold" wire:click="siguientePaso">
                                Siguiente <i class="ti ti-chevron-right ms-1"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-success rounded-pill px-5 fw-bold" wire:click="crearMatricula">
                                <i class="ti ti-check me-1"></i> Finalizar Matrícula
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- no eliminar este div por favor-->
        <style>
            .form-check-input {
                border: 2px solid #000;
                float: right;

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

            #card-materias {
                margin-left: 0px !important;
            }

            #container-sedes {
                margin-left: 0px !important;

            }

            #horario-button {}

            #container-horarios {
                margin-left: 0px !important;

            }

            @media (max-width: 768px) {
                #card-materias {
                    margin-left: 0px !important;
                }

                #container-sedes {
                    margin-left: 0px !important;
                    margin-bottom: 100px !important;
                }

                #horario-button {
                    height: 100px !important;
                }

                #container-horarios {
                    margin-left: 0px !important;
                    margin-bottom: 200px !important;
                }

                .container-monedas {
                    margin-left: 0px !important;
                }

                .container-monedas .col-12 {
                    margin-bottom: 20px !important;
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
