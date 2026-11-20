<div class="row align-items-center mx-1 mx-sm-3 my-2">

    <div class="p-2 p-sm-4 col-12">


        <div class="row redil-carrito-content">

            @if($pasoActual == 1)
            {{-- ==========================================
                 PASO 1: SELECCIÓN DE PRODUCTOS Y DATOS
                 ========================================== --}}
            <div class="col-12" wire:key="step-1">
                <!-- Listado del carrito -->
                @if (!$actividad->tipo->unica_compra || $actividad->tiene_invitados || $categoriasCompraPermitidas->count() > 1)
                <h3 class="fw-semibold p-0 mb-3">Selecciona tus ítems</h3>
                <div class="card p-3 p-md-5 shadow border-top-0 border-1 mb-4 col-12">
                    <ul class="list-group mb-2">
                        <div class="px-2 px-md-6 py-2">
                            @if ($actividad->tiene_invitados)
                                <div class="mb-3">
                                    <label for="cantidadInvitados" class="form-label fw-semibold">Número de personas que vienen contigo</label>
                                    <input type="number" id="cantidadInvitados" wire:model.live="cantidadInvitados" class="form-control @error('cantidadInvitados') is-invalid @enderror" min="0" placeholder="0" @if (isset($compraActual->id)) disabled @endif>
                                    @error('cantidadInvitados') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif
                        </div>

                        <div class="@if($actividad->tipo->unica_compra && $actividad->tipo->unica_inscripcion && $actividad->tipo->es_gratuita && $categoriasCompraPermitidas->count() === 1 ) d-none @endif">
                            @forelse ($categoriasCompraPermitidas as $categoria)
                            <li class="border-0 list-group-item px-2 px-md-6 py-2">
                                <div class="d-flex gap-3 gap-md-4">
                                    <div class="flex-grow-1">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <p class="me-3 mb-2">
                                                    <span class="text-heading fw-bold">{{ $categoria->nombre }}</span>
                                                </p>
                                                @if ($actividad->tipo->unica_compra)
                                                    @if (isset($carrito[$categoria->id]))
                                                    <button wire:click="eliminarDelCarrito({{ $categoria->id }})" class="btn btn-outline-danger fw-semibold">
                                                        <i class="ti ti-shopping-cart-x"></i> Eliminar
                                                    </button>
                                                    @else
                                                    <button wire:click="agregarAlCarrito({{ $categoria->id }})" class="btn btn-label-secondary fw-semibold btn-outline-secondary" @if (count($carrito)> 0 || isset($compraActual->id)) disabled @endif>
                                                        <i class="ti ti-shopping-cart-plus"></i> Agregar
                                                    </button>
                                                    @endif
                                                @else
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                            <button wire:click="decrementCantidad({{ $categoria->id }})" class="btn minus rounded-pill" @if (isset($compraActual->id) || $actividad->tipo->unica_compra || $actividad->tipo->unica_inscripcion) disabled @endif>-</button>
                                                            <input style="height:35px;width:60px; text-align: center;font-size:20px" type="number" wire:model="cantidades.{{ $categoria->id }}" min="1" max="{{ $categoria->limite_compras }}" class="form-control form-control-sm" @if (isset($compraActual->id) || $actividad->tipo->unica_compra || $actividad->tipo->unica_inscripcion) disabled @endif>
                                                            <button wire:click="incrementCantidad({{ $categoria->id }})" class="btn plus rounded-pill" @if (isset($compraActual->id) || $actividad->tipo->unica_compra || $actividad->tipo->unica_inscripcion) disabled @endif>+</button>
                                                    </div>
                                                    @if (isset($carrito[$categoria->id]))
                                                    <button wire:click="eliminarDelCarrito({{ $categoria->id }})" class="btn btn-outline-danger fw-semibold">
                                                        <i class="ti ti-shopping-cart-x"></i> Eliminar
                                                    </button>
                                                    @else
                                                    <button wire:click="agregarAlCarrito({{ $categoria->id }})" class="btn btn-outline-secondary fw-semibold" @if (isset($compraActual->id)) disabled @endif>
                                                        <i class="ti ti-shopping-cart-plus"></i> Agregar
                                                    </button>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end justify-content-start justify-content-md-end gap-2">
                                                <div class="me-3 mb-1 pt-3 pt-md-2">
                                                    @php
                                                    $monedaCategoria = $categoria->monedas()->where('moneda_id', $this->monedaSeleccionada)->first();
                                                    @endphp
                                                    @if ($monedaCategoria && $monedaCategoria->pivot->valor > 0)
                                                    <span class="text-black fw-normal fs-5">{{ Number::currency($monedaCategoria->pivot->valor, $monedaCategoria->nombre_corto) }}</span>
                                                    @else
                                                    <span class="success badge rounded-pill px-7 bg-label-claro-success mb-1">Gratis</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <hr>
                            @empty
                            <p>No hay categorías disponibles para esta actividad.</p>
                            @endforelse
                        </div>
                    </ul>
                </div>
                @endif

                <!-- Datos del Comprador (Si no está logueado) -->
                @if($actividad->requiere_inicio_sesion == false && !Auth::check())
                <div class="card p-3 p-md-5 shadow border-top-0 border-1 mb-4 col-12">
                    <h5>Completa los datos de contacto</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" wire:model.live="nombreComprador" class="form-control @error('nombreComprador') is-invalid @enderror">
                            @error('nombreComprador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número Identificación</label>
                            <input type="text" wire:model.live="identificacionComprador" class="form-control @error('identificacionComprador') is-invalid @enderror">
                            @error('identificacionComprador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model.live="EmailComprador" class="form-control @error('EmailComprador') is-invalid @enderror">
                            @error('EmailComprador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono / Celular</label>
                            <input type="text" wire:model.live="telefonoComprador" class="form-control @error('telefonoComprador') is-invalid @enderror">
                            @error('telefonoComprador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                @endif

                <!-- Sección de monedas, destinatarios y parientes (con selects buscables y scroll acotado) -->
                @php
                    $compraBloqueada = isset($compraActual->id);
                    $opcionesMonedas = $actividad->monedas->map(fn($m) => ['id' => $m->id, 'nombre' => $m->nombre])->values()->all();
                    $opcionesDestinatarios = $actividad->destinatarios
                        ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
                        ->values()
                        ->map(fn($d) => ['id' => $d->id, 'nombre' => $d->nombre])
                        ->all();
                    $opcionesParientes = [];
                    if ($usuario) {
                        $opcionesParientes[] = [
                            'id' => $usuario->id,
                            'nombre' => trim($usuario->primer_nombre . ' ' . $usuario->primer_apellido) . ' (Mí)',
                        ];
                    }
                    foreach ($relacionesFamiliares as $pariente) {
                        $opcionesParientes[] = [
                            'id' => $pariente->id,
                            'nombre' => trim($pariente->primer_nombre . ' ' . $pariente->primer_apellido),
                        ];
                    }
                @endphp

                @if (count($opcionesMonedas) > 1 || count($opcionesDestinatarios) > 0 || count($relacionesFamiliares) > 0)
                <div class="row mb-4 redil-card-select">
                    @if (count($opcionesMonedas) > 1)
                    <div class="col-md-6 col-12 mb-3">
                        <div class="form-group">
                            <h6 class="text-dark fw-normal mb-2">Elija su moneda de pago</h6>
                            <div x-data="{
                                open: false,
                                disabled: @js($compraBloqueada),
                                options: @js($opcionesMonedas),
                                selected: @entangle('monedaSeleccionada').live,
                                selectedLabel: 'Seleccione una moneda',
                                toggle() {
                                    if (this.disabled) return;
                                    this.open = !this.open;
                                },
                                selectOption(id, nombre) {
                                    if (this.disabled) return;
                                    this.selected = id;
                                    this.selectedLabel = nombre;
                                    this.open = false;
                                },
                                updateLabel() {
                                    let found = this.options.find(o => o.id == this.selected);
                                    this.selectedLabel = found ? found.nombre : 'Seleccione una moneda';
                                }
                            }" x-init="updateLabel(); $watch('selected', () => updateLabel())"
                                class="position-relative p-0"
                                :class="{ 'redil-select-open': open }">

                                <button type="button"
                                    @click="toggle()"
                                    :disabled="disabled"
                                    class="form-select redil-select-trigger text-start d-flex align-items-center justify-content-between"
                                    :aria-expanded="open"
                                    :class="{ 'border-primary shadow-sm': open }">
                                    <span class="text-truncate pe-2 fw-medium text-dark" x-text="selectedLabel"></span>
                                </button>

                                <div x-show="open" x-cloak @click.outside="open = false" class="redil-select-dropdown" role="listbox">
                                    <ul class="redil-select-list list-unstyled p-0 m-0">
                                        <template x-for="option in options" :key="option.id">
                                            <li @click="selectOption(option.id, option.nombre)"
                                                role="option"
                                                :aria-selected="selected == option.id"
                                                tabindex="0"
                                                class="redil-select-option d-flex align-items-center justify-content-between"
                                                :class="{ 'is-selected': selected == option.id }">
                                                <span class="me-2" x-text="option.nombre"></span>
                                                <i class="ti ti-check text-primary flex-shrink-0" x-show="selected == option.id"></i>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if (count($opcionesDestinatarios) > 0)
                    <div class="col-md-6 col-12 mb-3">
                        <div class="form-group">
                            <h6 class="text-dark fw-normal mb-2">Elija un destinatario</h6>
                            <div x-data="{
                                open: false,
                                search: '',
                                disabled: @js($compraBloqueada),
                                options: @js($opcionesDestinatarios),
                                selected: @entangle('destinatario').live,
                                selectedLabel: 'Seleccione un destinatario',

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
                                    if (this.disabled) return;
                                    this.open = !this.open;
                                    if (this.open) {
                                        this.$nextTick(() => {
                                            this.$refs.searchDestInput?.focus({ preventScroll: true });
                                            const rect = this.$el.getBoundingClientRect();
                                            if (rect.bottom > window.innerHeight - 260) {
                                                this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                            }
                                        });
                                    }
                                },
                                selectOption(id, nombre) {
                                    if (this.disabled) return;
                                    this.selected = id;
                                    this.selectedLabel = nombre;
                                    this.open = false;
                                    this.search = '';
                                },
                                updateLabel() {
                                    let found = this.options.find(o => o.id == this.selected);
                                    this.selectedLabel = found ? found.nombre : 'Seleccione un destinatario';
                                }
                            }" x-init="updateLabel(); $watch('selected', () => updateLabel())"
                                class="position-relative p-0"
                                :class="{ 'redil-select-open': open }">

                                <button type="button"
                                    @click="toggle()"
                                    :disabled="disabled"
                                    class="form-select redil-select-trigger text-start d-flex align-items-center justify-content-between"
                                    :aria-expanded="open"
                                    aria-haspopup="listbox"
                                    :class="{ 'border-primary shadow-sm': open }">
                                    <span class="text-truncate pe-2"
                                        :class="{ 'text-muted': !selected || selected == 0, 'fw-medium text-dark': selected && selected != 0 }"
                                        x-text="selectedLabel"></span>
                                </button>

                                <div x-show="open" x-cloak @click.outside="open = false" class="redil-select-dropdown" role="listbox">
                                    <div class="redil-select-search">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0 text-muted">
                                                <i class="ti ti-search"></i>
                                            </span>
                                            <input type="search"
                                                x-model="search"
                                                x-ref="searchDestInput"
                                                placeholder="Buscar destinatario / sede..."
                                                class="form-control border-start-0 ps-0"
                                                @keydown.escape.prevent="open = false">
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                            <small class="text-muted" style="font-size: 0.75rem;"
                                                x-text="filteredOptions.length + (filteredOptions.length === 1 ? ' opción disponible' : ' opciones disponibles')"></small>
                                            <button type="button"
                                                x-show="search.length > 0"
                                                @click="search = ''; $refs.searchDestInput?.focus()"
                                                class="btn btn-link btn-xs p-0 text-decoration-none"
                                                style="font-size: 0.75rem;">
                                                Limpiar
                                            </button>
                                        </div>
                                    </div>

                                    <ul class="redil-select-list list-unstyled p-0 m-0">
                                        <template x-if="filteredOptions.length === 0 && search.length > 0">
                                            <li class="px-3 py-3 text-center text-muted small">
                                                No se encontraron destinatarios con "<span x-text="search"></span>".
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
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if (count($relacionesFamiliares) > 0)
                    <div class="col-md-6 col-12 mb-3">
                        <div class="form-group">
                            <h6 class="text-dark fw-normal mb-2">Inscripción a nombre de</h6>
                            <div x-data="{
                                open: false,
                                search: '',
                                disabled: @js($compraBloqueada),
                                options: @js($opcionesParientes),
                                selected: @entangle('parienteSeleccionado'),
                                selectedLabel: 'Seleccione una persona',

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
                                    if (this.disabled) return;
                                    this.open = !this.open;
                                    if (this.open) {
                                        this.$nextTick(() => {
                                            this.$refs.searchParienteInput?.focus({ preventScroll: true });
                                            const rect = this.$el.getBoundingClientRect();
                                            if (rect.bottom > window.innerHeight - 260) {
                                                this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                            }
                                        });
                                    }
                                },
                                selectOption(id, nombre) {
                                    if (this.disabled) return;
                                    this.selected = id;
                                    this.selectedLabel = nombre;
                                    this.open = false;
                                    this.search = '';
                                },
                                updateLabel() {
                                    let found = this.options.find(o => o.id == this.selected);
                                    this.selectedLabel = found ? found.nombre : (this.options[0]?.nombre || 'Seleccione una persona');
                                }
                            }" x-init="updateLabel(); $watch('selected', () => updateLabel())"
                                class="position-relative p-0"
                                :class="{ 'redil-select-open': open }">

                                <button type="button"
                                    @click="toggle()"
                                    :disabled="disabled"
                                    class="form-select redil-select-trigger text-start d-flex align-items-center justify-content-between"
                                    :aria-expanded="open"
                                    aria-haspopup="listbox"
                                    :class="{ 'border-primary shadow-sm': open }">
                                    <span class="text-truncate pe-2 fw-medium text-dark" x-text="selectedLabel"></span>
                                </button>

                                <div x-show="open" x-cloak @click.outside="open = false" class="redil-select-dropdown" role="listbox">
                                    <div class="redil-select-search" x-show="options.length > 4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0 text-muted">
                                                <i class="ti ti-search"></i>
                                            </span>
                                            <input type="search"
                                                x-model="search"
                                                x-ref="searchParienteInput"
                                                placeholder="Buscar familiar..."
                                                class="form-control border-start-0 ps-0"
                                                @keydown.escape.prevent="open = false">
                                        </div>
                                    </div>

                                    <ul class="redil-select-list list-unstyled p-0 m-0">
                                        <template x-for="option in filteredOptions" :key="option.id">
                                            <li @click="selectOption(option.id, option.nombre)"
                                                role="option"
                                                :aria-selected="selected == option.id"
                                                tabindex="0"
                                                class="redil-select-option d-flex align-items-center justify-content-between"
                                                :class="{ 'is-selected': selected == option.id }">
                                                <span class="me-2" x-text="option.nombre"></span>
                                                <i class="ti ti-check text-primary flex-shrink-0" x-show="selected == option.id"></i>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Resumen del carrito (Step 1) -->
                <div class="card p-3 p-md-5 border-top-0 border-1 shadow">
                    <h5 class="fw-semibold">Resumen de la selección</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                @forelse ($carrito as $item)
                                    @if ($actividad->tiene_invitados)
                                        @if ($actividad->tipo->es_gratuita || $item['precio'] == 0)
                                            {{-- Caso Actividad Gratuita con Invitados: total de posibles inscripciones (titular + invitados) y "por invitación" --}}
                                            <tr>
                                                <td>
                                                    <span class="text-heading fw-bold">{{ $item['nombre'] }}</span>
                                                    <div class="text-muted small">
                                                        Total posibles inscripciones: <strong>{{ 1 + (int)$cantidadInvitados }}</strong>
                                                        (1 titular + {{ (int)$cantidadInvitados }} {{ (int)$cantidadInvitados == 1 ? 'persona que viene contigo' : 'personas que vienen contigo' }})
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold align-middle">
                                                    {{ 1 + (int)$cantidadInvitados }} por invitación
                                                </td>
                                            </tr>
                                        @else
                                            {{-- Caso Actividad de Pago con Invitados: separar la inscripción de pago del titular de los invitados --}}
                                            <tr>
                                                <td>
                                                    <span class="text-heading fw-bold">{{ $item['nombre'] }}</span>
                                                    <div class="text-muted small">Inscripción titular</div>
                                                </td>
                                                <td class="text-end fw-bold align-middle">
                                                    {{ $item['cantidad'] }} x {{ Number::currency($item['precio'], $monedaActual?->nombre_corto) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span class="text-heading fw-semibold">Personas que vienen contigo</span>
                                                    <div class="text-muted small">Acompañantes por invitación (sin costo adicional)</div>
                                                </td>
                                                <td class="text-end fw-bold align-middle text-success">
                                                    {{ (int)$cantidadInvitados }} por invitación
                                                </td>
                                            </tr>
                                        @endif
                                    @else
                                        {{-- Caso estándar sin invitados --}}
                                        <tr>
                                            <td>{{ $item['nombre'] }}</td>
                                            <td class="text-end fw-bold align-middle">
                                                {{ $item['cantidad'] }} x
                                                {{ $item['precio'] > 0 ? Number::currency($item['precio'], $monedaActual?->nombre_corto) : 'Gratis' }}
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                <tr><td colspan="2" class="text-muted italic">No has agregado ítems al carrito.</td></tr>
                                @endforelse
                                <tr class="border-top border-dark">
                                    <td class="h5">Total Estimado</td>
                                    <td class="h5 text-end text-primary fw-bold">
                                        @if ($total > 0)
                                            {{ Number::currency($total, $monedaActual?->nombre_corto) }}
                                        @elseif ($actividad->tiene_invitados && ($actividad->tipo->es_gratuita || collect($carrito)->every(fn($i) => ($i['precio'] ?? 0) == 0)))
                                            <span class="text-success">Por invitación</span>
                                        @else
                                            <span class="text-success">Gratis</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @elseif($pasoActual == 2)
            {{-- ==========================================
                 PASO 2: FORMULARIO DINÁMICO
                 ========================================== --}}
            <div class="col-12" wire:key="step-2">
                <h3 class="fw-semibold mb-3">Información Adicional</h3>
                <p class="text-muted mb-4 italic">Por favor, completa los campos marcados como requeridos para finalizar tu inscripción.</p>

                @foreach ($elementosFormulario as $elemento)
                    @php
                        $clase = $elemento->tipoElemento->getRawOriginal('clase') ?? $elemento->tipoElemento->clase;
                        $errorClass = $errors->has('respuestas.'.$elemento->id) ? 'is-invalid' : '';
                    @endphp

                    @if ($elemento->tipo_elemento_id == 1)
                        {{-- ENCABEZADO DE SECCIÓN --}}
                        <div class="col-12 my-4 border-bottom pb-2">
                            <h4 class="fw-bold mb-1">{{ $elemento->titulo }}</h4>
                            <p class="text-muted small mb-0">{{ $elemento->descripcion }}</p>
                        </div>
                    @else
                        {{-- CAMPOS DE PREGUNTA --}}
                        <div class="card shadow-sm border p-3 p-md-4 mb-4 rounded-3 bg-light bg-opacity-10 redil-card-select">
                            <label class="form-label fw-bold fs-6">
                                {{ $elemento->titulo }}
                                @if($elemento->required) <span class="text-danger">*</span> @endif
                            </label>
                            @if($elemento->descripcion)
                                <p class="small text-muted mb-2">{{ $elemento->descripcion }}</p>
                            @endif

                            @switch($clase)
                                @case('corta')
                                    <input type="text" wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control {{ $errorClass }}">
                                    @break
                                @case('larga')
                                    <textarea wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control {{ $errorClass }}" rows="3"></textarea>
                                    @break
                                @case('si_no')
                                    <div class="d-flex gap-4 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input {{ $errorClass }}"
                                                type="radio"
                                                wire:model.defer="respuestas.{{ $elemento->id }}"
                                                id="elemento-{{ $elemento->id }}-si"
                                                value="1">
                                            <label class="form-check-label" for="elemento-{{ $elemento->id }}-si">Sí</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input {{ $errorClass }}"
                                                type="radio"
                                                wire:model.defer="respuestas.{{ $elemento->id }}"
                                                id="elemento-{{ $elemento->id }}-no"
                                                value="0">
                                            <label class="form-check-label" for="elemento-{{ $elemento->id }}-no">No</label>
                                        </div>
                                    </div>
                                    @break
                                @case('unica_respuesta')
                                    @php
                                        $opcionesElemento = $elemento->opciones->map(fn($op) => [
                                            'id' => $op->id,
                                            'nombre' => $op->valor_texto,
                                        ])->values()->all();
                                    @endphp
                                    <div x-data="{
                                        open: false,
                                        search: '',
                                        options: @js($opcionesElemento),
                                        selected: @entangle('respuestas.'.$elemento->id),
                                        selectedLabel: 'Seleccione una opción',

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
                                                    this.$refs.searchOpInput?.focus({ preventScroll: true });
                                                    const rect = this.$el.getBoundingClientRect();
                                                    if (rect.bottom > window.innerHeight - 260) {
                                                        this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                                    }
                                                });
                                            }
                                        },
                                        selectOption(id, nombre) {
                                            this.selected = id;
                                            this.selectedLabel = nombre;
                                            this.open = false;
                                            this.search = '';
                                        },
                                        updateLabel() {
                                            let found = this.options.find(o => o.id == this.selected);
                                            this.selectedLabel = found ? found.nombre : 'Seleccione una opción';
                                        }
                                    }" x-init="updateLabel(); $watch('selected', () => updateLabel())"
                                        class="position-relative p-0"
                                        :class="{ 'redil-select-open': open }">

                                        <button type="button"
                                            @click="toggle()"
                                            class="form-select redil-select-trigger text-start d-flex align-items-center justify-content-between {{ $errorClass }}"
                                            :aria-expanded="open"
                                            aria-haspopup="listbox"
                                            :class="{ 'border-primary shadow-sm': open }">
                                            <span class="pe-2"
                                                :class="{ 'text-muted': !selected, 'fw-medium text-dark': selected }"
                                                x-text="selectedLabel"></span>
                                        </button>

                                        <div x-show="open" x-cloak @click.outside="open = false" class="redil-select-dropdown" role="listbox">
                                            <div class="redil-select-search" x-show="options.length > 5">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                                        <i class="ti ti-search"></i>
                                                    </span>
                                                    <input type="search"
                                                        x-model="search"
                                                        x-ref="searchOpInput"
                                                        placeholder="Buscar opción..."
                                                        class="form-control border-start-0 ps-0"
                                                        @keydown.escape.prevent="open = false">
                                                </div>
                                            </div>

                                            <ul class="redil-select-list list-unstyled p-0 m-0">
                                                <template x-if="filteredOptions.length === 0 && search.length > 0">
                                                    <li class="px-3 py-3 text-center text-muted small">
                                                        No se encontraron opciones con "<span x-text="search"></span>".
                                                    </li>
                                                </template>

                                                <template x-for="option in filteredOptions" :key="option.id">
                                                    <li @click="selectOption(option.id, option.nombre)"
                                                        role="option"
                                                        :aria-selected="selected == option.id"
                                                        tabindex="0"
                                                        class="redil-select-option d-flex align-items-center justify-content-between"
                                                        :class="{ 'is-selected': selected == option.id }">
                                                        <span class="me-2" x-text="option.nombre"></span>
                                                        <i class="ti ti-check text-primary flex-shrink-0" x-show="selected == option.id"></i>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                    @break
                                @case('multiple_respuesta')
                                    <div class="row">
                                        @foreach ($elemento->opciones as $opcion)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="{{ $opcion->id }}" wire:model.defer="respuestas.{{ $elemento->id }}" id="opt-{{ $opcion->id }}">
                                                    <label class="form-check-label" for="opt-{{ $opcion->id }}">{{ $opcion->valor_texto }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @break
                                @case('fecha')
                                    <input type="date" wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control {{ $errorClass }}">
                                    @break
                                @case('numero')
                                @case('moneda')
                                    <input type="number" wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control {{ $errorClass }}">
                                    @break
                                @case('archivo')
                                    @if(isset($respuestas[$elemento->id]) && is_string($respuestas[$elemento->id]))
                                        <div class="alert alert-secondary d-flex justify-content-between align-items-center">
                                            <span>
                                                <i class="ti ti-file fs-4 me-2"></i>
                                                {{ $respuestas[$elemento->id] }}
                                            </span>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                wire:click="eliminarRespuesta({{ $elemento->id }})">Cambiar</button>
                                        </div>
                                    @else
                                        {{-- Alpine.js hace el fetch al controlador directamente, sin wire:model ni WithFileUploads --}}
                                        <div x-data="uploadArchivo({{ $elemento->id }}, 'archivo', $wire)"
                                            x-init="init()">
                                            <input type="file"
                                                id="archivo_elemento_{{ $elemento->id }}"
                                                class="form-control {{ $errorClass }}"
                                                accept=".pdf,application/pdf"
                                                @change="subirArchivo($event)">
                                            <div x-show="subiendo" style="display: none;" class="mt-1 text-primary small">
                                                <span class="spinner-border spinner-border-sm"></span>
                                                Subiendo archivo...
                                            </div>
                                            <div x-show="errorMsg" style="display: none;" x-text="errorMsg" class="mt-1 text-danger small"></div>
                                            <div x-show="successMsg" x-transition style="display: none;" class="mt-2 alert alert-success p-2 small d-flex align-items-center">
                                                <i class="ti ti-check fs-5 me-2"></i> <span x-text="successMsg"></span>
                                            </div>
                                            <small class="text-muted d-block mt-1">Formato permitido: solo PDF (máx. 10 MB)</small>
                                        </div>
                                    @endif
                                    @break

                                @case('imagen')
                                    @if(isset($respuestas[$elemento->id]) && is_string($respuestas[$elemento->id]))
                                        <div class="mb-2">
                                            <img src="{{ Storage::disk('public')->url($respuestas[$elemento->id]) }}"
                                                class="img-fluid rounded border" style="max-height: 200px;">
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    wire:click="eliminarRespuesta({{ $elemento->id }})">
                                                    <i class="ti ti-trash me-1"></i> Cambiar imagen
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div x-data="uploadArchivo({{ $elemento->id }}, 'imagen', $wire)"
                                            x-init="init()">
                                            <input type="file"
                                                id="imagen_elemento_{{ $elemento->id }}"
                                                class="form-control {{ $errorClass }}"
                                                accept="image/png,image/jpeg,image/jpg,.png,.jpeg,.jpg"
                                                @change="subirArchivo($event)">
                                            <div x-show="subiendo" style="display: none;" class="mt-1 text-primary small">
                                                <span class="spinner-border spinner-border-sm"></span>
                                                Subiendo imagen...
                                            </div>
                                            <div x-show="errorMsg" style="display: none;" x-text="errorMsg" class="mt-1 text-danger small"></div>
                                            <div x-show="successMsg" x-transition style="display: none;" class="mt-2 alert alert-success p-2 small d-flex align-items-center">
                                                <i class="ti ti-check fs-5 me-2"></i> <span x-text="successMsg"></span>
                                            </div>
                                            <small class="text-muted d-block mt-1">Formatos permitidos: solo PNG, JPG o JPEG (máx. 5 MB)</small>
                                        </div>
                                    @endif
                                    @break
                            @endswitch

                            @error('respuestas.'.$elemento->id)
                                <div class="text-danger small mt-1 font-weight-bold">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                @endforeach
            </div>
            @endif
        </div>

        <!-- Barra de Navegación Inferior Fija -->
        <div class="w-100 fixed-bottom py-3 px-3 px-sm-4 border-top bg-white shadow-lg" style="z-index: 1040;">
            <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2 d-flex justify-content-between align-items-center gap-2">
                <div>
                    @if($pasoActual > 1)
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 px-sm-4" wire:click="volverPaso">
                        <i class="ti ti-arrow-left me-1"></i> Volver a Carrito
                    </button>
                    @endif
                </div>

                <div>
                    <button type="button"
                        wire:click="siguientePaso"
                        wire:loading.attr="disabled"
                        class="btn btn-primary rounded-pill px-4 px-sm-5 py-2 fw-bold"
                        @if (empty($carrito)) disabled @endif>

                        <span wire:loading wire:target="siguientePaso">
                            <span class="spinner-border spinner-border-sm me-2"></span> Procesando...
                        </span>

                        <span wire:loading.remove wire:target="siguientePaso">
                            @if($pasoActual < $totalPasos)
                                Continuar a Formulario <i class="ti ti-arrow-right ms-1"></i>
                            @else
                                Finalizar Inscripción <i class="ti ti-check ms-1"></i>
                            @endif
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay de Carga Global -->
    <div wire:loading wire:target="siguientePaso, procesarRegistro" class="loading-overlay flex-column" style="display: none;" wire:loading.class="d-flex">
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;"></div>
        <h4 class="text-white mt-4 fw-normal">Estamos guardando tu información y reservando tus cupos...</h4>
        <p class="text-white-50">Por favor, no cierres esta ventana.</p>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .redil-carrito-content {
            margin-bottom: 180px;
            padding-bottom: 60px;
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

        .redil-select-trigger:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
            opacity: 0.8;
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

        .loading-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
            text-align: center;
        }

        .btn[class*=btn-outline-]:disabled, .btn[class*=btn-outline-].disabled {
            background: #e9e9e9 !important;
        }

        .minus, .plus {
            border: solid 2px #1977E5 !important;
            border-radius: 50%;
            width: 35px; height: 35px;
            display: flex; justify-content: center; align-items: center;
            color: #1977E5; background: #fff;
            transition: all 0.2s;
        }
        .minus:hover, .plus:hover { background: #1977E5; color: #fff; }

        .step.active .bs-stepper-icon {
            box-shadow: 0 0 15px rgba(59, 113, 254, 0.4);
            transform: scale(1.1);
        }

        .bg-label-claro-success { background: #E8FADF; color: #71DD37; }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }

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
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('uploadArchivo', (elementoId, tipo, wire) => ({
                subiendo: false,
                errorMsg: null,
                successMsg: null,
                init() {},
                async subirArchivo(event) {
                    const file = event.target.files[0];
                    if (!file) {
                        this.successMsg = null;
                        return;
                    }

                    this.subiendo = true;
                    this.errorMsg = null;
                    this.successMsg = null;

                    const formData = new FormData();
                    formData.append('archivo', file);
                    formData.append('tipo', tipo);
                    // Agregamos el token CSRF para peticiones POST de Laravel
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    try {
                        const response = await fetch('{{ route('carrito.uploadArchivoFormulario') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            // Si se subió con éxito, pasamos solo el nombre del archivo de vuelta a Livewire
                            // en lugar de mandar todo el archivo en base64 y sobrecargar Livewire.
                            wire.set('archivosSubidos.' + elementoId, result.nombre);
                            this.successMsg = '¡Archivo subido correctamente!';
                        } else {
                            this.errorMsg = result.message || 'Error al subir el archivo.';
                        }
                    } catch (error) {
                        console.error('Upload Error:', error);
                        this.errorMsg = 'Error de conexión al subir el archivo.';
                    } finally {
                        this.subiendo = false;
                    }
                }
            }));
        });

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('mostrarMensaje', (data) => {
                Swal.fire({
                    title: data[0].titulo,
                    text: data[0].mensaje,
                    icon: data[0].tipo,
                    confirmButtonText: 'Entendido',
                    customClass: { confirmButton: 'btn btn-primary rounded-pill' }
                });
            });
        });
    </script>
</div>
