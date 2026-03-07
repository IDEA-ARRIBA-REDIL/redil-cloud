<div>

     <div class="d-flex justify-content-end mb-3 gap-2">
        {{-- BOTÓN NUEVO --}}
        <button type="button" class="btn btn-outline-secondary" wire:click="exportarExcel">
          <i class="ti ti-file-spreadsheet ms-1"></i> Excel
        </button>

        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="offcanvas" data-bs-target="#filtrosAlumnosOffcanvas">
            Filtros <i class="ti ti-filter ms-1"></i>
        </button>
    </div>

    {{-- Sección de Tags de Filtro Activos --}}
    <div class="filter-tags py-3">
        @if(!empty($tagsBusqueda))
            <span class="text-muted me-2">Filtros aplicados:</span>
            @foreach($tagsBusqueda as $tag)
                <button type="button"
                        class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1"
                        wire:click.prevent="removeTag('{{ $tag->field }}', '{{ $tag->value }}')"
                        wire:loading.attr="disabled"
                        title="Quitar este filtro">
                    <span class="align-middle">{{ $tag->label }}<i class="ti ti-x ti-xs" style="margin-bottom: 2px;"></i></span>
                </button>
            @endforeach
            <button type="button"
                    wire:click.prevent="limpiarFiltros"
                    class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1 mt-1"
                    title="Quitar todos los filtros">
                <span class="align-middle">Quitar todos <i class="ti ti-x ti-xs" style="margin-bottom: 2px;"></i></span>
            </button>
        @endif
    </div>

    {{-- Contenedor principal para la grilla de tarjetas --}}
    <div class="row">
        @forelse ($alumnos as $alumno)
            {{-- Clases para responsividad: 1 tarjeta por fila en móvil, 2 en tablets, 3 en escritorios --}}
            <div class="col-12 col-xl-3 col-md-6 mb-4">

                <div class="card h-100 w-100">
                    <div class="card-body d-flex flex-column justify-content-between">

                        <div>
                            {{-- Cabecera con avatar, nombre y menú de acciones --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    {{-- Lógica del Avatar: Foto o Iniciales --}}
                                    <div class="avatar avatar-md me-3">
                                        @if($alumno->foto && !in_array($alumno->foto, ["default-m.png", "default-f.png"]))
                                            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$alumno->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$alumno->foto }}" alt="{{ $alumno->foto }}" class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-primary">{{ $alumno->inicialesNombre() }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold text-black lh-sm">{{ $alumno->nombre(3) }}</h6>
                                        @if($alumno->fecha_nacimiento)
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            {{ $alumno->edad() ? $alumno->edad().' años' : '' }}
                                        </small>
                                        @endif
                                    </div>
                                </div>

                                {{-- Menú de acciones (placeholder) --}}
                                <div class="dropdown">
                                    <button  style="border-radius: 20px;" class="btn p-1 border shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('usuario.perfil', $alumno) }}">Ver Perfil</a></li>
                                        <li><a class="dropdown-item" href="{{ route('escuelas.historialCalificaciones') }}">Ver Calificaciones</a></li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Badge con el tipo de usuario/rol --}}
                            @if ($alumno->tipoUsuario)
                            <div class="mb-3">
                                <span class="badge rounded-pill px-2 py-1 fw-normal text-white" style="background-color: {{ $alumno->tipoUsuario->color }}; font-size: 0.7rem;">
                                    <i class="{{ $alumno->tipoUsuario->icono ?? 'ti ti-user' }} me-1" style="font-size: 0.7rem;"></i>
                                    {{ $alumno->tipoUsuario->nombre }}
                                </span>
                            </div>
                            @endif
                        </div>

                        {{-- Información de contacto básica (Footer de la card en el bottom) --}}
                        <div class="d-flex flex-column mt-2 pt-2 border-top">
                            <div class="d-flex align-items-center mb-1">
                                <i class="ti ti-mail text-muted me-2" style="font-size: 0.8rem;"></i>
                                <span class="text-secondary small text-truncate">{{ $alumno->email }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="ti ti-phone text-muted me-2" style="font-size: 0.8rem;"></i>
                                <span class="text-secondary small">{{ $alumno->telefono_movil ?? 'No registrado' }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            {{-- Mensaje si no se encuentran alumnos --}}
            <div class="col-12">
                <div class="alert alert-info text-center">
                    @if($filtroMateriaPeriodo)
                        No se encontraron alumnos que coincidan con los filtros aplicados.
                    @else
                        No hay alumnos matriculados en este periodo. Para buscar, utilice el panel de filtros.
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    {{-- Renderizar los links de paginación debajo de la grilla --}}
   {{-- CÓDIGO RECONSTRUIDO (similar a tu ejemplo de Actividades) --}}
<div class="row my-3 mt-5">
    @if ($alumnos && $alumnos->hasPages())
        {{-- 1. Añadimos el resumen de paginación --}}
        <p> Mostrando {{ $alumnos->firstItem() }} - {{ $alumnos->lastItem() }} <b>de</b> {{ $alumnos->total() }} resultados </p>

        {{-- 2. Renderizamos los links. Livewire no necesita appends(), lo hace automáticamente. --}}
        {!! $alumnos->links() !!}
    @endif
</div>

    {{-- Offcanvas para los filtros (con Alpine.js integrado) --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="filtrosAlumnosOffcanvas">
    <div class="offcanvas-header">
        <h4 class="offcanvas-title text-primary fw-semibold">Filtros de Alumnos</h4>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form wire:submit="buscarMatriculas">

            <div class="mb-3">
                <label for="filtroMateriaPeriodo" class="form-label">Materia (Obligatorio)</label>
                <select id="filtroMateriaPeriodo" class="form-select" wire:model="filtroMateriaPeriodo">
                    <option value="">-- Seleccione una materia --</option>
                    @foreach($materiasPeriodo as $materiaP)
                        <option value="{{ $materiaP->id }}">{{ $materiaP->materia->nombre }}</option>
                    @endforeach
                </select>
                @error('filtroMateriaPeriodo') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <hr>
            <p class="small text-muted">Filtros de Sede (Opcionales): Se mostrarán alumnos que cumplan con cualquiera de las sedes seleccionadas.</p>

            {{-- ===== INICIO: Multi-Select Alpine para Sede de Matrícula ===== --}}
            <div x-data="{
                    open: false,
                    options: {{ $sedes }},
                    selected: @entangle('filtroSedeMatricula'),
                    selectedLabels: [],
                    init() {
                        this.updateSelectedLabels();
                        this.$watch('selected', () => this.updateSelectedLabels());
                    },
                    updateSelectedLabels() {
                        this.selectedLabels = this.options.filter(option => this.selected.includes(String(option.id))).map(option => option.nombre);
                    },
                    toggleOption(optionId, optionLabel) {
                        optionId = String(optionId);
                        if (this.selected.includes(optionId)) {
                            this.selected = this.selected.filter(id => id !== optionId);
                        } else {
                            this.selected.push(optionId);
                        }
                    },
                    selectAll() {
                        this.selected = this.options.map(option => String(option.id));
                    },
                    deselectAll() {
                        this.selected = [];
                    },
                    get unselectedOptions() {
                        return this.options.filter(option => !this.selected.includes(String(option.id)));
                    }
                }"
                class="mb-3"
            >
                <label class="form-label">Sede de la Matrícula</label>
                <div @click.outside="open = false" class="position-relative">
                    <div @click="open = !open" class="form-select" style="min-height: 38px; cursor: pointer;">
                        <template x-if="selected.length === 0">
                            <span class="text-muted">Seleccione una o más opciones</span>
                        </template>
                        <template x-for="(label, index) in selectedLabels" :key="index">
                            <span class="badge bg-label-secondary me-1 mb-1">
                                <span x-text="label"></span>
                                <span @click.stop="toggleOption(options.find(opt => opt.nombre === label).id, label)" class="ms-1" style="cursor: pointer;">&times;</span>
                            </span>
                        </template>
                    </div>
                    <div class="d-flex justify-content-between p-2 border-bottom bg-light">
                            <a href="#" @click.prevent="selectAll()" class="small text-primary fw-semibold">Seleccionar Todas</a>
                            <a href="#" @click.prevent="deselectAll()" class="small text-primary fw-semibold">Quitar Todas</a>
                        </div>
                    <div x-show="open" x-transition class="card position-absolute w-100 mt-1" style="z-index: 10;">

                        <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                            <template x-for="option in unselectedOptions" :key="option.id">
                                <a href="#" @click.prevent="toggleOption(option.id, option.nombre)" class="list-group-item list-group-item-action" x-text="option.nombre"></a>
                            </template>
                            <template x-if="unselectedOptions.length === 0">
                                <span class="list-group-item">No hay más opciones disponibles.</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ===== FIN: Multi-Select Alpine para Sede de Matrícula ===== --}}

            {{-- ===== INICIO: Multi-Select Alpine para Sede del Alumno ===== --}}
            <div x-data="{
                    open: false,
                    options: {{ $sedes }},
                    selected: @entangle('filtroSedeAlumno'),
                    selectedLabels: [],
                    init() {
                        this.updateSelectedLabels();
                        this.$watch('selected', () => this.updateSelectedLabels());
                    },
                    updateSelectedLabels() {
                        this.selectedLabels = this.options.filter(option => this.selected.includes(String(option.id))).map(option => option.nombre);
                    },
                    toggleOption(optionId, optionLabel) {
                        optionId = String(optionId);
                        if (this.selected.includes(optionId)) {
                            this.selected = this.selected.filter(id => id !== optionId);
                        } else {
                            this.selected.push(optionId);
                        }
                    },
                    selectAll() {
                        this.selected = this.options.map(option => String(option.id));
                    },
                    deselectAll() {
                        this.selected = [];
                    },
                    get unselectedOptions() {
                        return this.options.filter(option => !this.selected.includes(String(option.id)));
                    }
                }"
                class="mb-3"
            >
                <label class="form-label">Sede del Alumno</label>
                <div @click.outside="open = false" class="position-relative">
                    <div @click="open = !open" class="form-select" style="min-height: 38px; cursor: pointer;">
                        <template x-if="selected.length === 0">
                            <span class="text-muted">Seleccione una o más opciones</span>
                        </template>
                        <template x-for="(label, index) in selectedLabels" :key="index">
                            <span class="badge bg-label-secondary me-1 mb-1">
                                <span x-text="label"></span>
                                <span @click.stop="toggleOption(options.find(opt => opt.nombre === label).id, label)" class="ms-1" style="cursor: pointer;">&times;</span>
                            </span>
                        </template>
                    </div>
                    <div class="d-flex justify-content-between p-2 border-bottom bg-light">
                            <a href="#" @click.prevent="selectAll()" class="small text-primary fw-semibold">Seleccionar Todas</a>
                            <a href="#" @click.prevent="deselectAll()" class="small text-primary fw-semibold">Quitar Todas</a>
                        </div>
                    <div x-show="open" x-transition class="card position-absolute w-100 mt-1" style="z-index: 10;">

                        <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                            <template x-for="option in unselectedOptions" :key="option.id">
                                <a href="#" @click.prevent="toggleOption(option.id, option.nombre)" class="list-group-item list-group-item-action" x-text="option.nombre"></a>
                            </template>
                            <template x-if="unselectedOptions.length === 0">
                                <span class="list-group-item">No hay más opciones disponibles.</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ===== FIN: Multi-Select Alpine para Sede del Alumno ===== --}}

            <div class="mt-4">
                <button type="submit" class="btn btn-primary w-100" data-bs-dismiss="offcanvas">
                    Buscar Matrículas
                </button>
            </div>
        </form>
    </div>
</div>
</div>
