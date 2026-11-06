<div>
    {{-- Mensajes de sesión Flash --}}
    @if (session()->has('mensaje_exito_hmp'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('mensaje_exito_hmp') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('mensaje_error_hmp'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('mensaje_error_hmp') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('mensaje_info_hmp'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('mensaje_info_hmp') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mt-4">
        <div class="card-header justify-content-between">

            <div class="mt-2 mt-md-0">
                <button wire:click="abrirFormularioNuevo" class="btn  btn-primary rounded-pill" type="button">
                    <i class="ti ti-plus me-1"></i> Añadir horario
                </button>

                <button type="button" class="btn btn-outline-secondary  float-end ms-2"
                    wire:click="abrirFiltros">
                    Filtros <i class="ti ti-filter ms-1"></i>
                </button>
            </div>
        </div>

        {{-- Tags de Búsqueda --}}
        @if (count($this->tagsBusqueda) > 0)
            <div class="card-body border-bottom py-2 bg-light-subtle">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="fw-bold text-muted small me-2">Filtros activos:</span>
                    @foreach ($this->tagsBusqueda as $tag)
                        <button type="button" class="btn btn-xs rounded-pill btn-outline-primary active ps-2 pe-1"
                            wire:click="removerFiltro('{{ $tag['field'] }}')">
                            <span class="align-middle">{{ $tag['label'] }} <i class="ti ti-x ms-1"></i></span>
                        </button>
                    @endforeach
                    <a href="javascript:void(0)" class="btn btn-outline-secondary rounded-pill" wire:click="resetFiltros">
                        Limpiar todo
                    </a>
                </div>
            </div>
        @endif

        <div class="card-body">
            <div class="row">
                @forelse ($horariosMP as $hmp)
                    <div class="col-lg-4 col-md-6 col-12 mb-4">
                        <div class="card horario-card border rounded position-relative h-100 shadow-sm">
                            <div class="position-absolute top-0 end-0 mt-2 me-2 z-1">
                                <div class="dropdown zindex-2 border rounded">
                                    <button class="btn btn-sm  p-1" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">

                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="toggleHabilitado({{ $hmp->id }})">
                                                @if ($hmp->habilitado)
                                                    Deshabilitar
                                                @else
                                                    Habilitar
                                                @endif
                                            </button>
                                        </li>

                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="confirmarEliminarHorarioMP({{ $hmp->id }})">
                                                Eliminar
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body text-start">
                                @if ($hmp->horarioBase)
                                    <span
                                        class="fw-bold text-black">{{ $hmp->horarioBase->dia_semana ?? 'Día no definido' }}</span><br>
                                    <span class="text-muted">
                                        {{ $hmp->horarioBase->hora_inicio_formato ?? $hmp->horarioBase->hora_inicio }}
                                        - {{ $hmp->horarioBase->hora_fin_formato ?? $hmp->horarioBase->hora_fin }}
                                        <span style="font-size:10px"> ( Id: {{ $hmp->id }})</span>
                                    </span>
                                    <br>
                                    <span
                                        class="badge rounded-pill my-3 fs-xs {{ $hmp->habilitado ? 'btn-success' : 'btn-danger' }}">
                                        {{ $hmp->habilitado ? 'Activo' : 'inactivo' }}
                                    </span>

                                    <div class="row justify-content-between mb-2">

                                        @if ($hmp->horarioBase->aula)
                                            <div class="col-12 col-md-6">
                                                <p class="mb-1 text-black"><i
                                                        class="ti ti-building-arch me-2 "></i><strong>Tipo
                                                        aula:</strong>
                                                    ({{ $hmp->horarioBase->aula->tipo->nombre ?? 'Tipo N/A' }})
                                                </p>
                                                <p class="mb-1 text-black"><i
                                                        class="ti ti-map-pin me-2"></i><strong>Sede:</strong>
                                                    {{ $hmp->horarioBase->aula->sede->nombre ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <p class="mb-1 text-black"><i
                                                        class="ti ti-map-pin me-2"></i><strong>Aula:</strong>
                                                    {{ $hmp->horarioBase->aula->nombre ?? 'N/A' }}
                                                </p>
                                                <p class="mb-1 text-black"><i
                                                        class="ti ti-users-group me-2"></i><strong>Cupos
                                                        iniciales:</strong> {{ $hmp->horarioBase->capacidad ?? 'N/A' }}
                                                </p>
                                            </div>
                                        @else
                                            <div class="col-12 col-md-6">
                                                <p class=""">Aula no definida para horario base.</p>
                                            </div>
                                        @endif
                                        <div class="col-12 col-md-6">
                                            <p class="mb-1 text-black"><i class="ti ti-users me-2"></i><strong>Cupos
                                                    limite:</strong> {{ $hmp->capacidad }} @if ($hmp->ampliar_cupos_limite)
                                                    / Lím: {{ $hmp->capacidad_limite }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <p class="mb-1 text-black"><i
                                                    class="ti ti-user-check me-2"></i><strong>Cupos
                                                    disponibles:</strong> {{ $hmp->cupos_disponibles ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-danger mt-3">Error: Horario base no asociado.</p>
                                @endif

                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="avatar avatar-xl mb-2">
                            <div class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="ti ti-calendar-off fs-2"></i>
                            </div>
                        </div>
                        <h5 class="text-muted mt-2">No hay horarios asignados</h5>
                        <p class="text-muted">Aún no se han vinculado horarios base a esta materia en este período.</p>
                    </div>
                @endforelse
            </div>
            @if ($horariosMP->hasPages())
                <div class="card-footer d-flex justify-content-center border-top pt-3">
                    {{ $horariosMP->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Offcanvas para Nuevo HorarioMateriaPeriodo --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNuevoHorarioMP"
        aria-labelledby="offcanvasNuevoHorarioMPLabel" wire:ignore.self>
        <form wire:submit.prevent="guardarHorario" id="formNuevoHorarioMP">
            <div class="offcanvas-header">
                <h4 id="offcanvasNuevoHorarioMPLabel" class="offcanvas-title text-primary">Vincular horario a materia
                </h4>
                <button type="button" class="btn-close" wire:click="cancelar" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-grow-0 py-4">

                {{-- Selección de Sede --}}
                <div class="mb-3">
                    <label for="sede_id_formulario_nuevo" class="form-label">1. Seleccionar Sede </label>
                    <select required wire:model.live="sede_id_formulario"
                        class="form-select @error('sede_id_formulario') is-invalid @enderror"
                        id="sede_id_formulario_nuevo">
                        <option value="">-- Seleccionar sede --</option>
                        @foreach ($sedes as $sede)
                            {{-- $sedes está disponible desde mount() --}}
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                    @error('sede_id_formulario')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Selección de Aula (dependiente de Sede) --}}
                <div class="mb-3">
                    <label for="aula_id_formulario_nuevo" class="form-label">2. Seleccionar aula </label>
                    <select required wire:model.live="aula_id_formulario"
                        class="form-select @error('aula_id_formulario') is-invalid @enderror"
                        id="aula_id_formulario_nuevo" @if (!$sede_id_formulario || $aulas_formulario->isEmpty()) disabled @endif>
                        <option value="">-- Seleccionar aula --</option>
                        @if ($sede_id_formulario && $aulas_formulario->count() > 0)
                            @foreach ($aulas_formulario as $aula)
                                <option value="{{ $aula->id }}">{{ $aula->nombre }} (Capacidad:
                                    {{ $aula->capacidad }})</option>
                            @endforeach
                        @elseif($sede_id_formulario && $aulas_formulario->isEmpty())
                            <option value="" disabled>No hay aulas en esta sede.</option>
                        @endif
                    </select>
                    @error('aula_id_formulario')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Selección Múltiple de Horarios Base (dependiente de Aula) --}}
                <div class="mb-3">
                    <label class="form-label">3. Seleccionar horario(s) base</label>

                    @if (!$aula_id_formulario)
                        <select class="form-select" disabled>
                            <option value="">-- Seleccionar un horario base --</option>
                        </select>
                    @elseif ($horarios_base_formulario->isEmpty())
                        <select class="form-select" disabled>
                            <option value="">No hay horarios base activos para esta materia en esta aula, o ya están asignados.</option>
                        </select>
                    @else
                        @php
                            $totalDisponibles = $horarios_base_formulario->count();
                            $totalSeleccionados = count($horarios_base_seleccionados);
                            $todosSeleccionados = $totalDisponibles > 0 && $totalSeleccionados === $totalDisponibles;
                        @endphp

                        <div class="position-relative" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button"
                                class="form-select text-start d-flex justify-content-between align-items-center @error('horarios_base_seleccionados') is-invalid @enderror"
                                @click="open = !open">
                                <span class="text-truncate">
                                    @if ($totalSeleccionados === 0)
                                        -- Seleccionar uno o más horarios base --
                                    @elseif ($todosSeleccionados)
                                        Todos los horarios seleccionados ({{ $totalSeleccionados }})
                                    @elseif ($totalSeleccionados === 1)
                                        1 horario seleccionado
                                    @else
                                        {{ $totalSeleccionados }} horarios seleccionados
                                    @endif
                                </span>
                            </button>

                            <div x-show="open" x-transition
                                class="border rounded bg-white shadow-sm mt-1 p-2 w-100"
                                style="max-height: 260px; overflow-y: auto; z-index: 1060;">
                                <div class="form-check pb-2 mb-2 border-bottom">
                                    <input class="form-check-input" type="checkbox"
                                        id="hb_select_all"
                                        wire:click="toggleSeleccionarTodosHorariosBase"
                                        @checked($todosSeleccionados)>
                                    <label class="form-check-label fw-semibold text-primary" for="hb_select_all" style="cursor: pointer;">
                                        Seleccionar todos ({{ $totalDisponibles }})
                                    </label>
                                </div>

                                @foreach ($horarios_base_formulario as $hb)
                                    <div class="form-check py-1" wire:key="hb-option-{{ $hb->id }}">
                                        <input class="form-check-input" type="checkbox"
                                            value="{{ $hb->id }}"
                                            id="hb_opt_{{ $hb->id }}"
                                            wire:model.live="horarios_base_seleccionados">
                                        <label class="form-check-label text-dark small" for="hb_opt_{{ $hb->id }}" style="cursor: pointer;">
                                            {{ $hb->display_info }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if ($totalSeleccionados > 0)
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @foreach ($horarios_base_formulario->whereIn('id', $horarios_base_seleccionados) as $hbSel)
                                    <span class="badge bg-label-primary rounded-pill">
                                        {{ $hbSel->dia_semana }} {{ $hbSel->hora_inicio_formato ?? $hbSel->hora_inicio }} - {{ $hbSel->hora_fin_formato ?? $hbSel->hora_fin }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    @error('horarios_base_seleccionados')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('horarios_base_seleccionados.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- En la sección del footer del offcanvas: -->
            <div class="offcanvas-footer p-5 border-top border-2 px-8">
                <div class="mt-4 d-flex justify-content-start">
                    <button type="submit" class="btn rounded-pill btn-primary me-2" wire:loading.attr="disabled"
                        wire:target="guardarHorario">
                        Crear
                    </button>

                    <button type="button" class="btn btn-outline-secondary me-2 rounded-pill"
                        wire:click="cancelar">Cancelar</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Offcanvas para Editar HorarioMateriaPeriodo --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditarHorarioMP"
        aria-labelledby="offcanvasEditarHorarioMPLabel" wire:ignore.self>
        <div class="offcanvas-header">
            <h5 id="offcanvasEditarHorarioMPLabel" class="offcanvas-title">Editar Capacidades del Horario en Período
            </h5>
            <button type="button" class="btn-close" wire:click="cancelar" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-0 py-4">
            {{-- El wire:submit.prevent="actualizarHorario" es crucial --}}
            <form wire:submit.prevent="actualizarHorario" id="formEditarHorarioMP">
                <div class="alert alert-light-secondary bg-light-secondary text-secondary border-0 mb-4">
                    {{-- Campos Editables --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hmp_capacidad_editar" class="form-label">Capacidad </label>
                            <input wire:model="hmp_capacidad" type="number" min="0"
                                class="form-control @error('hmp_capacidad') is-invalid @enderror"
                                id="hmp_capacidad_editar">
                            @error('hmp_capacidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hmp_capacidad_limite_editar" class="form-label">Capacidad límite </label>
                            <input wire:model="hmp_capacidad_limite" type="number" min="0"
                                class="form-control @error('hmp_capacidad_limite') is-invalid @enderror"
                                id="hmp_capacidad_limite_editar">
                            @error('hmp_capacidad_limite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="hmp_ampliar_cupos_limite_editar"
                            wire:model="hmp_ampliar_cupos_limite">
                        <label class="form-check-label" for="hmp_ampliar_cupos_limite_editar">Habilitar capacidad
                            limite</label>
                        @error('hmp_ampliar_cupos_limite')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="offcanvas-footer p-3 border-top">
                    {{-- Solo mostrar botones de acción si hay un ID para editar --}}

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-label-secondary me-2 rounded-pill"
                            wire:click="cancelar">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill" {{-- El form ID ya está asociado --}}
                            wire:loading.attr="disabled" wire:target="actualizarHorario">
                            <span wire:loading wire:target="actualizarHorario"
                                class="spinner-border spinner-border-sm me-1" role="status"
                                aria-hidden="true"></span>
                            Actualizar
                        </button>
                    </div>

                </div>
            </form> {{-- Cierre del form --}}
        </div>
    </div>

    {{-- Offcanvas para Filtros --}}
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltrosHorarioMP"
            aria-labelledby="offcanvasFiltrosHorarioMPLabel" wire:ignore.self>
            <div class="offcanvas-header">
                <h4 id="offcanvasFiltrosHorarioMPLabel" class="offcanvas-title fw-semibold text-primary">Filtrar Horarios del Período</h4>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form wire:submit.prevent="aplicarFiltros" id="formFiltrosHorarioMP">
                    <div class="mb-3">
                        <label for="sedeFiltro" class="form-label">Sede</label>
                        <select wire:model.live="sedeFiltro" class="form-select" id="sedeFiltro">
                            <option value="">Todas las sedes</option>
                            @foreach ($sedesFiltroLista as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tipoAulaFiltro" class="form-label">Tipo de aula</label>
                        <select wire:model.live="tipoAulaFiltro" class="form-select" id="tipoAulaFiltro">
                            <option value="">Todos los tipos</option>
                            @foreach ($tiposAulaFiltroLista as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-between mt-4">

                        <button type="submit" class="btn btn-primary rounded-pill text-start" wire:loading.attr="disabled"
                            wire:target="aplicarFiltros">
                            <span wire:loading wire:target="aplicarFiltros"
                                class="spinner-border spinner-border-sm me-1" role="status"
                                aria-hidden="true"></span>
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                // Abrir offcanvas
                Livewire.on('abrirOffcanvas', (event) => {
                    const detail = Array.isArray(event) ? event[0] : (event?.detail || event);
                    const nombreOffCanvas = detail?.nombreModal;
                    if (!nombreOffCanvas) return;

                    const offcanvasElement = document.getElementById(nombreOffCanvas);
                    if (!offcanvasElement) return;

                    var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement, {
                        backdrop: true
                    });
                    offcanvas.show();
                });

                // Cerrar offcanvas
                Livewire.on('cerrarOffcanvas', (event) => {
                    const detail = Array.isArray(event) ? event[0] : (event?.detail || event);
                    const nombreOffCanvas = detail?.nombreModal;
                    if (!nombreOffCanvas) return;

                    const offcanvasElement = document.getElementById(nombreOffCanvas);
                    if (offcanvasElement) {
                        const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                        if (bsOffcanvas) {
                            bsOffcanvas.hide();
                        }
                    }
                    document.querySelectorAll('.offcanvas-backdrop').forEach(el => el.remove());
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                });

                ['offcanvasNuevoHorarioMP', 'offcanvasEditarHorarioMP'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.addEventListener('hidden.bs.offcanvas', () => {
                            document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove());
                            @this.call('resetearFormulario');
                        });
                    }
                });

                // Notificaciones SweetAlert2
                Livewire.on('notificacionHMP', (event) => {
                    const data = Array.isArray(event) ? event[0] : (event?.detail || event);
                    Swal.fire({
                        title: data?.titulo || (data?.tipo === 'success' ? '¡Éxito!' : 'Aviso'),
                        text: data?.mensaje || '',
                        icon: data?.tipo || 'info',
                        confirmButtonText: 'Aceptar',
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-pill px-4'
                        },
                        buttonsStyling: false
                    });
                });

                // Confirmación SweetAlert2 para eliminar HorarioMateriaPeriodo sin matrículas
                Livewire.on('confirmarEliminarHorarioMPEvento', (event) => {
                    const data = Array.isArray(event) ? event[0] : (event?.detail || event);
                    const detalleHorario = [data?.dia, data?.hora, data?.aula].filter(Boolean).join(' • ');

                    Swal.fire({
                        title: '¿Eliminar horario del período?',
                        html: detalleHorario
                            ? `Estás a punto de desvincular el horario <b>${detalleHorario}</b> de este período.<br>Esta acción no elimina el horario base (plantilla).`
                            : '¿Estás seguro de eliminar este horario del período?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            confirmButton: 'btn btn-danger rounded-pill me-2 px-4',
                            cancelButton: 'btn btn-outline-secondary rounded-pill px-4'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed && data?.id) {
                            @this.call('eliminarHorarioMP', data.id);
                        }
                    });
                });
            });
        </script>
    @endpush
</div>
