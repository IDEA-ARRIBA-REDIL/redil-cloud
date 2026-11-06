<div>
    {{-- Barra Superior de Búsqueda y Acciones --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        {{-- Buscador reactivo rápido --}}
        <div class="input-group input-group-merge shadow-none border rounded" style="max-width: 380px;">
            <span class="input-group-text bg-transparent border-0 text-muted">
                <i class="ti ti-search"></i>
            </span>
            <input 
                type="text" 
                class="form-control border-0 ps-0 shadow-none" 
                placeholder="Buscar por nombre, documento o email..." 
                wire:model.live.debounce.400ms="search"
            >
            @if(!empty($search))
                <button class="btn btn-sm btn-icon btn-outline-secondary border-0 my-auto me-1" type="button" wire:click="$set('search', '')" title="Limpiar búsqueda">
                    <i class="ti ti-x ti-xs"></i>
                </button>
            @endif
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary shadow-none" wire:click="exportarExcel" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="exportarExcel">
                    <i class="ti ti-file-spreadsheet me-1"></i> Excel
                </span>
                <span wire:loading wire:target="exportarExcel">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span> Exportando...
                </span>
            </button>

            <button type="button" class="btn btn-outline-primary shadow-none" data-bs-toggle="offcanvas" data-bs-target="#filtrosAlumnosOffcanvas">
                <i class="ti ti-filter me-1"></i> Filtros
                @if(!empty($filtroMateriaPeriodo) || !empty($filtroSedeMatricula) || !empty($filtroSedeAlumno))
                    <span class="badge bg-primary rounded-circle ms-1 px-1" style="font-size: 0.65rem;">
                        {{ (empty($filtroMateriaPeriodo) ? 0 : 1) + count($filtroSedeMatricula) + count($filtroSedeAlumno) }}
                    </span>
                @endif
            </button>
        </div>
    </div>

    {{-- Sección de Tags de Filtro Activos --}}
    @if(!empty($tagsBusqueda))
        <div class="filter-tags pb-3 d-flex flex-wrap align-items-center gap-1">
            <span class="text-muted small me-1">Filtros aplicados:</span>
            @foreach($tagsBusqueda as $tag)
                <button type="button"
                        class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1"
                        wire:click.prevent="removeTag('{{ $tag->field }}', '{{ $tag->value }}')"
                        wire:loading.attr="disabled"
                        title="Quitar este filtro">
                    <span class="align-middle small">{{ $tag->label }} <i class="ti ti-x ti-xs ms-1"></i></span>
                </button>
            @endforeach
            <button type="button"
                    wire:click.prevent="limpiarFiltros"
                    class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1"
                    title="Quitar todos los filtros">
                <span class="align-middle small">Quitar todos <i class="ti ti-x ti-xs ms-1"></i></span>
            </button>
        </div>
    @endif

    {{-- Spinner de carga reactiva --}}
    <div wire:loading.flex wire:target="search, filtroMateriaPeriodo, filtroSedeMatricula, filtroSedeAlumno, limpiarFiltros, removeTag, gotoPage, previousPage, nextPage" class="justify-content-center align-items-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando alumnos...</span>
        </div>
    </div>

    {{-- Contenedor principal para la grilla de tarjetas --}}
    <div class="row" wire:loading.remove wire:target="search, filtroMateriaPeriodo, filtroSedeMatricula, filtroSedeAlumno, limpiarFiltros, removeTag">
        @forelse ($alumnos as $alumno)
            @php
                $primeraMatricula = $alumno->matriculas->first();
                $sedeMatricula = $primeraMatricula?->sede?->nombre 
                    ?? $primeraMatricula?->horarioMateriaPeriodo?->horarioBase?->aula?->sede?->nombre 
                    ?? 'Sin sede matrícula';
            @endphp
            {{-- Grilla: 1 columna en móvil, 2 en tablet, 3 en pantallas grandes --}}
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-none border rounded-3 position-relative">
                    <div class="card-body d-flex flex-column justify-content-between p-3">

                        <div>
                            {{-- Cabecera: Avatar, Nombre completo y Menú de opciones --}}
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center me-2 overflow-hidden">
                                    {{-- Avatar con foto o iniciales --}}
                                    <div class="avatar avatar-md me-2 flex-shrink-0">
                                        @if($alumno->foto && !in_array($alumno->foto, ['default-m.png', 'default-f.png']))
                                            <img src="{{ $alumno->foto_url }}" alt="{{ $alumno->nombre(3) }}" class="rounded-circle object-fit-cover w-100 h-100 border border-1 border-light shadow-sm">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-primary fw-bold border border-1 border-light shadow-sm">
                                                {{ $alumno->inicialesNombre() }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="overflow-hidden">
                                        <h6 class="mb-0 fw-bold text-dark text-truncate" title="{{ $alumno->nombre(3) }}">
                                            {{ $alumno->nombre(3) }}
                                        </h6>
                                        <div class="d-flex align-items-center gap-1 flex-wrap mt-0">
                                            @if($alumno->identificacion)
                                                <small class="text-muted" style="font-size: 0.72rem;">
                                                    <i class="ti ti-id me-0 text-secondary"></i> {{ $alumno->identificacion }}
                                                </small>
                                            @endif
                                            @if($alumno->fecha_nacimiento && $alumno->edad())
                                                <small class="text-muted" style="font-size: 0.72rem;">
                                                    • {{ $alumno->edad() }} años
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Menú contextual de acciones --}}
                                <div class="dropdown flex-shrink-0">
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle shadow-none p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 30px; height: 30px;">
                                        <i class="ti ti-dots-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="{{ route('usuario.perfil', $alumno) }}">
                                                <i class="ti ti-user me-2 text-primary"></i> Ver Perfil
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="{{ route('escuelas.historialCalificaciones') }}">
                                                <i class="ti ti-certificate me-2 text-info"></i> Ver Calificaciones
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Badge con tipo de usuario / rol --}}
                            @if ($alumno->tipoUsuario)
                                <div class="mb-3">
                                    <span class="badge rounded-pill px-2 py-1 fw-medium" style="background-color: {{ $alumno->tipoUsuario->color }}20; color: {{ $alumno->tipoUsuario->color }}; border: 1px solid {{ $alumno->tipoUsuario->color }}50; font-size: 0.7rem;">
                                        <i class="{{ $alumno->tipoUsuario->icono ?? 'ti ti-user' }} me-1" style="font-size: 0.75rem;"></i>
                                        {{ $alumno->tipoUsuario->nombre }}
                                    </span>
                                </div>
                            @endif

                            {{-- Panel destacado de Sedes: Sede Alumno vs Sede Matrícula --}}
                            <div class="p-2 mb-3 rounded-2 bg-light border">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted small d-flex align-items-center">
                                        <i class="ti ti-user-pin text-primary me-1" style="font-size: 0.95rem;"></i> Sede Alumno:
                                    </span>
                                    <span class="badge bg-label-primary fw-semibold text-truncate" style="max-width: 150px;" title="{{ $alumno->sede?->nombre ?? 'Sin sede asignada' }}">
                                        {{ $alumno->sede?->nombre ?? 'Sin sede' }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted small d-flex align-items-center">
                                        <i class="ti ti-building text-info me-1" style="font-size: 0.95rem;"></i> Sede Matrícula:
                                    </span>
                                    <span class="badge bg-label-info fw-semibold text-truncate" style="max-width: 150px;" title="{{ $sedeMatricula }}">
                                        {{ $sedeMatricula }}
                                    </span>
                                </div>
                            </div>

                            {{-- Información de Materia, Aula y Horario donde está matriculado --}}
                            <div class="matriculas-periodo mb-3">
                                @forelse($alumno->matriculas as $mat)
                                    @php
                                        $materiaNombre = $mat->horarioMateriaPeriodo?->materiaPeriodo?->materia?->nombre ?? 'Materia no definida';
                                        $horarioBase = $mat->horarioMateriaPeriodo?->horarioBase;
                                        $aulaNombre = $horarioBase?->aula?->nombre ?? 'Aula no asignada';
                                        $horarioTexto = $horarioBase 
                                            ? $horarioBase->dia_semana . ($horarioBase->hora_inicio_formato ? ' ('.$horarioBase->hora_inicio_formato.' - '.$horarioBase->hora_fin_formato.')' : '')
                                            : 'Horario no asignado';
                                    @endphp
                                    <div class="p-2 mb-2 rounded-2 border bg-white shadow-none">
                                        <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom">
                                            <div class="d-flex align-items-center text-truncate me-1" title="{{ $materiaNombre }}">
                                                <i class="ti ti-book text-warning me-1"></i>
                                                <span class="fw-bold small text-dark text-truncate">{{ $materiaNombre }}</span>
                                            </div>
                                            @if($mat->bloqueado)
                                                <span class="badge bg-label-danger py-0 px-2 flex-shrink-0" style="font-size: 0.65rem;">
                                                    <i class="ti ti-lock me-1"></i>Bloqueado
                                                </span>
                                            @else
                                                <span class="badge bg-label-success py-0 px-2 flex-shrink-0" style="font-size: 0.65rem;">
                                                    <i class="ti ti-check me-1"></i>Activo
                                                </span>
                                            @endif
                                        </div>
                                        <div class="small text-muted d-flex align-items-center mb-1">
                                            <i class="ti ti-door text-secondary me-1" style="font-size: 0.85rem;"></i>
                                            <span class="text-truncate" title="{{ $aulaNombre }}">
                                                <strong class="text-secondary">Aula:</strong> {{ $aulaNombre }}
                                            </span>
                                        </div>
                                        <div class="small text-muted d-flex align-items-center">
                                            <i class="ti ti-clock text-secondary me-1" style="font-size: 0.85rem;"></i>
                                            <span class="text-truncate" title="{{ $horarioTexto }}">
                                                <strong class="text-secondary">Horario:</strong> {{ $horarioTexto }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-2 text-muted small bg-light rounded-2 border border-dashed">
                                        <i class="ti ti-info-circle me-1"></i> Sin horario/materia asignada
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Footer de contacto: Email y Teléfono con acceso directo a WhatsApp --}}
                        <div class="d-flex flex-column mt-auto pt-2 border-top gap-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center text-truncate me-2 small">
                                    <i class="ti ti-mail text-muted me-2" style="font-size: 0.85rem;"></i>
                                    <span class="text-secondary text-truncate" title="{{ $alumno->email }}">{{ $alumno->email ?? 'No registrado' }}</span>
                                </div>
                                @if($alumno->email)
                                    <a href="mailto:{{ $alumno->email }}" class="text-muted" title="Enviar correo">
                                        <i class="ti ti-send" style="font-size: 0.8rem;"></i>
                                    </a>
                                @endif
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center small">
                                    <i class="ti ti-phone text-muted me-2" style="font-size: 0.85rem;"></i>
                                    <span class="text-secondary">{{ $alumno->telefono_movil ?? 'No registrado' }}</span>
                                </div>
                                @if($alumno->telefono_movil)
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $alumno->telefono_movil);
                                        if (strlen($cleanPhone) === 10) {
                                            $cleanPhone = '57' . $cleanPhone;
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener noreferrer" class="text-success small fw-semibold d-flex align-items-center" title="Abrir WhatsApp">
                                        <i class="ti ti-brand-whatsapp me-1"></i> WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            {{-- Estado vacío --}}
            <div class="col-12">
                <div class="card border shadow-none text-center py-5">
                    <div class="card-body">
                        <i class="ti ti-users-minus text-muted mb-3" style="font-size: 3rem;"></i>
                        <h5 class="fw-semibold text-dark">No se encontraron alumnos</h5>
                        <p class="text-muted small mb-3">
                            @if($filtroMateriaPeriodo || !empty($search) || !empty($filtroSedeAlumno) || !empty($filtroSedeMatricula))
                                No hay resultados con los filtros aplicados. Intenta restablecer los filtros para ver todos los alumnos.
                            @else
                                No hay alumnos matriculados en este periodo académico.
                            @endif
                        </p>
                        @if($filtroMateriaPeriodo || !empty($search) || !empty($filtroSedeAlumno) || !empty($filtroSedeMatricula))
                            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="limpiarFiltros">
                                <i class="ti ti-rotate me-1"></i> Restablecer filtros
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Resumen y enlaces de Paginación --}}
    <div class="row my-4">
        @if ($alumnos && $alumnos->hasPages())
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <p class="text-muted small mb-0">
                    Mostrando <strong class="text-dark">{{ $alumnos->firstItem() }}</strong> - <strong class="text-dark">{{ $alumnos->lastItem() }}</strong> de <strong class="text-dark">{{ $alumnos->total() }}</strong> resultados
                </p>
                <div>
                    {!! $alumnos->links() !!}
                </div>
            </div>
        @endif
    </div>

    {{-- Offcanvas para los filtros (con Alpine.js integrado) --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filtrosAlumnosOffcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title text-primary fw-semibold">Filtros de Alumnos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form wire:submit="buscarMatriculas">

                <div class="mb-3">
                    <label for="filtroMateriaPeriodo" class="form-label fw-semibold">Materia (Obligatorio para filtrar)</label>
                    <select id="filtroMateriaPeriodo" class="form-select" wire:model="filtroMateriaPeriodo">
                        <option value="">-- Seleccione una materia --</option>
                        @foreach($materiasPeriodo as $materiaP)
                            <option value="{{ $materiaP->id }}">{{ $materiaP->materia->nombre }}</option>
                        @endforeach
                    </select>
                    @error('filtroMateriaPeriodo') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <hr class="my-3">
                <p class="small text-muted mb-3">Filtros de Sede (Opcionales): Se mostrarán alumnos que cumplan con cualquiera de las sedes seleccionadas.</p>

                {{-- Multi-Select Alpine para Sede de Matrícula --}}
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
                    <label class="form-label fw-semibold">Sede de la Matrícula</label>
                    <div @click.outside="open = false" class="position-relative">
                        <div @click="open = !open" class="form-select" style="min-height: 38px; cursor: pointer;">
                            <template x-if="selected.length === 0">
                                <span class="text-muted small">Seleccione una o más opciones</span>
                            </template>
                            <template x-for="(label, index) in selectedLabels" :key="index">
                                <span class="badge bg-label-info me-1 mb-1">
                                    <span x-text="label"></span>
                                    <span @click.stop="toggleOption(options.find(opt => opt.nombre === label).id, label)" class="ms-1" style="cursor: pointer;">&times;</span>
                                </span>
                            </template>
                        </div>
                        <div class="d-flex justify-content-between p-2 border-bottom bg-light">
                            <a href="#" @click.prevent="selectAll()" class="small text-primary fw-semibold">Seleccionar Todas</a>
                            <a href="#" @click.prevent="deselectAll()" class="small text-primary fw-semibold">Quitar Todas</a>
                        </div>
                        <div x-show="open" x-transition class="card position-absolute w-100 mt-1 shadow-sm" style="z-index: 10;">
                            <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                                <template x-for="option in unselectedOptions" :key="option.id">
                                    <a href="#" @click.prevent="toggleOption(option.id, option.nombre)" class="list-group-item list-group-item-action py-2 small" x-text="option.nombre"></a>
                                </template>
                                <template x-if="unselectedOptions.length === 0">
                                    <span class="list-group-item py-2 small text-muted">No hay más opciones disponibles.</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Multi-Select Alpine para Sede del Alumno --}}
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
                    <label class="form-label fw-semibold">Sede del Alumno</label>
                    <div @click.outside="open = false" class="position-relative">
                        <div @click="open = !open" class="form-select" style="min-height: 38px; cursor: pointer;">
                            <template x-if="selected.length === 0">
                                <span class="text-muted small">Seleccione una o más opciones</span>
                            </template>
                            <template x-for="(label, index) in selectedLabels" :key="index">
                                <span class="badge bg-label-primary me-1 mb-1">
                                    <span x-text="label"></span>
                                    <span @click.stop="toggleOption(options.find(opt => opt.nombre === label).id, label)" class="ms-1" style="cursor: pointer;">&times;</span>
                                </span>
                            </template>
                        </div>
                        <div class="d-flex justify-content-between p-2 border-bottom bg-light">
                            <a href="#" @click.prevent="selectAll()" class="small text-primary fw-semibold">Seleccionar Todas</a>
                            <a href="#" @click.prevent="deselectAll()" class="small text-primary fw-semibold">Quitar Todas</a>
                        </div>
                        <div x-show="open" x-transition class="card position-absolute w-100 mt-1 shadow-sm" style="z-index: 10;">
                            <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                                <template x-for="option in unselectedOptions" :key="option.id">
                                    <a href="#" @click.prevent="toggleOption(option.id, option.nombre)" class="list-group-item list-group-item-action py-2 small" x-text="option.nombre"></a>
                                </template>
                                <template x-if="unselectedOptions.length === 0">
                                    <span class="list-group-item py-2 small text-muted">No hay más opciones disponibles.</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary w-100" data-bs-dismiss="offcanvas">
                        <i class="ti ti-search me-1"></i> Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
