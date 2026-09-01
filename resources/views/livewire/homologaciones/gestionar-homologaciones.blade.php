<div>
    {{-- Paso 1 y 2: Selección de Alumno y Escuela --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">

                    {{-- El id 'alumno_id' es crucial para que nuestro JavaScript lo encuentre --}}
                    @livewire('usuarios.usuarios-para-busqueda', [
                        'id' => 'alumno_id',
                        'tipoBuscador' => 'unico',
                        'queUsuariosCargar' => 'todos',
                        'label' => '1. Busque y seleccione un alumno',
                        'placeholder' => 'Escriba el nombre o identificación del alumno...',
                    ])
                    @error('alumnoSeleccionadoId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror


                    <div class="mt-3">
                        <label for="escuela_id" class="form-label">2. Seleccione una escuela</label>
                        <select id="escuela_id" wire:model.live="escuelaSeleccionadaId" class="form-select">
                            <option value="">-- Elige una escuela --</option>
                            @foreach($escuelas as $escuela)
                                <option value="{{ $escuela->id }}">{{ $escuela->nombre }}</option>
                            @endforeach
                        </select>
                        @error('escuelaSeleccionadaId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Botón de Búsqueda --}}
    <div class="text-end mb-4">
        <button
            wire:click="buscar(document.getElementById('alumno_id').value)"
            class="btn btn-primary rounded-pill">

            <span wire:loading.remove wire:target="buscar">
                <i class="ti ti-search me-1"></i> Buscar
            </span>
            <span wire:loading wire:target="buscar">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Buscando...
            </span>
        </button>
    </div>

    {{-- Indicador de carga --}}
    <div wire:loading wire:target="buscar">Cargando materias...</div>

    {{-- Paso 3: Listado de Materias / Niveles (Cards estilo Grupos sin foto) --}}
    @if(!empty($materias))
    <div class="card bg-transparent shadow-none border-0 mb-4">
        <div class="card-header bg-transparent px-0 pt-0">
            <h5 class="card-title fw-bold text-primary">{{ $modo === 'materias' ? 'Materias' : 'Niveles' }} homologables</h5>
        </div>
        <div class="card-body px-0">
            <div class="row g-4">

                @forelse($materias as $materia)
                    <div class="col-12 col-xl-4 col-md-6">
                        <div class="card h-100 border rounded-3 shadow-sm">
                            {{-- Header de la Card con Título y Dropdown --}}
                            <div class="card-header pb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="me-2">
                                        <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $materia->nombre ?? $materia->materia_nombre }}</h5>
                                    </div>
                                    <div class="ms-auto">
                                        <div class="dropdown zindex-2 float-end">
                                            <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);" wire:click="abrirModalHomologacion({{ $materia->id }})">
                                                        Gestionar homologación
                                                    </a>
                                                </li>
                                                @if(isset($materia->estado))
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);" wire:click="prepararEliminacion({{ $materia->id }})">
                                                            <i class="ti ti-trash me-1"></i> Eliminar
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Body de la Card con Badges e Información Clave --}}
                            <div class="card-body">
                                {{-- Badge de Estado --}}
                                <div class="d-flex my-2 mb-3">
                                    @if(isset($materia->estado))
                                        @if((string)$materia->estado === '1')
                                            <span class="badge rounded-pill bg-label-success">Aprobada / Homologada</span>
                                        @elseif((string)$materia->estado === '2')
                                            <span class="badge rounded-pill bg-label-warning">En proceso</span>
                                        @elseif((string)$materia->estado === '0')
                                            <span class="badge rounded-pill bg-label-danger">Reprobada</span>
                                        @endif
                                    @else
                                        <span class="badge rounded-pill bg-label-secondary">Disponible para homologar</span>
                                    @endif
                                </div>

                                {{-- Informacion Clave --}}
                                <div class="d-flex flex-row justify-content-between mb-2">
                                    <div class="d-flex flex-row align-items-center me-2">
                                        <i class="ti ti-calendar-event text-black me-2 fs-5"></i>
                                        <div class="d-flex flex-column">
                                            <small class="text-black">Fecha homologación:</small>
                                            <small class="fw-semibold text-black">
                                                {{ $materia->fecha_homologacion ? \Carbon\Carbon::parse($materia->fecha_homologacion)->format('Y-m-d') : 'No registrada' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-row justify-content-between mb-2">
                                    <div class="d-flex flex-row align-items-center me-2">
                                        <i class="ti ti-calendar-check text-black me-2 fs-5"></i>
                                        <div class="d-flex flex-column">
                                            <small class="text-black">Fecha aprobación:</small>
                                            <small class="fw-semibold text-black">
                                                {{ $materia->fecha_homologacion_aprobacion ? \Carbon\Carbon::parse($materia->fecha_homologacion_aprobacion)->format('Y-m-d') : 'No aprobada' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-row justify-content-between mb-2">
                                    <div class="d-flex flex-row align-items-center me-2">
                                        <i class="ti ti-award text-black me-2 fs-5"></i>
                                        <div class="d-flex flex-column">
                                            <small class="text-black">Nota final:</small>
                                            <small class="fw-semibold text-black">
                                                {{ $materia->nota_final !== null ? $materia->nota_final : 'Sin nota' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                @if(isset($materia->estado) && (string)$materia->estado === '1' && $modo === 'materias')
                                <div class="d-flex flex-row justify-content-between mb-2">
                                    <div class="d-flex flex-row align-items-center me-2">
                                        <i class="ti ti-certificate text-black me-2 fs-5"></i>
                                        <div class="d-flex flex-column">
                                            <small class="text-black">Créditos aprobados:</small>
                                            <small class="fw-semibold text-black">
                                                {{ $materia->creditos_aprobados ?? $materia->creditos ?? 'Sin créditos' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Footer de la Card con Acciones --}}
                            <div class="card-footer" style="background-color:#ededed!important">
                                <div class="d-flex mt-4">
                                    <button wire:click="abrirModalHomologacion({{ $materia->id }})" class="btn btn-sm rounded-pill w-100  {{ isset($materia->estado) ? 'btn-outline-secondary' : 'btn-primary' }} waves-effect waves-light py-2 fw-medium">
                                        {{ isset($materia->estado) ? 'Gestionar homologación' : 'Homologar' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted text-center">No se encontraron {{ $modo === 'materias' ? 'materias' : 'niveles' }} para los criterios seleccionados.</p>
                    </div>
                @endforelse

            </div>
        </div>
    </div>
    @endif

    {{-- Offcanvas de Homologación --}}
    <div wire:ignore.self class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasHomologacion" aria-labelledby="offcanvasHomologacionLabel" data-bs-backdrop="true" data-bs-scroll="false">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasHomologacionLabel">
                Homologar {{ $modo === 'materias' ? 'Materia' : 'Nivel' }}:
                <span class="text-black fw-semibold d-block fs-6 mt-1">{{ $materiaParaHomologar?->nombre ?? $nivelParaHomologar?->nombre }}</span>
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <form x-data="{ estado: @entangle('estadoHomologacion') }" wire:submit.prevent="confirmarGuardado" class="d-flex flex-column h-100">
            <div class="offcanvas-body flex-grow-1">
                {{-- Selector de Estado (Estilo Theme Native Custom Option) --}}
                <div class="col-12 mb-4">
                    <label class="form-label text-black fw-semibold">Estado de homologación</label>
                    <div class="row g-2">
                        <div class="col-12 col-sm-12">
                            <div class="form-check custom-option custom-option-basic" :class="estado == 2 ? 'checked' : ''">
                                <label class="form-check-label custom-option-content d-flex justify-content-between align-items-center py-2 px-3" for="estado_en_proceso">
                                    <span class="custom-option-header p-0 border-0">
                                        <span class="fw-medium text-black small">En proceso</span>
                                    </span>
                                    <input name="estadoHomologacion" wire:model="estadoHomologacion" x-model="estado" class="form-check-input" type="radio" value="2" id="estado_en_proceso" />
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12">
                            <div class="form-check custom-option custom-option-basic" :class="estado == 1 ? 'checked' : ''">
                                <label class="form-check-label custom-option-content d-flex justify-content-between align-items-center py-2 px-3" for="estado_aprobado">
                                    <span class="custom-option-header p-0 border-0">
                                        <span class="fw-medium text-black small">Aprobado</span>
                                    </span>
                                    <input name="estadoHomologacion" wire:model="estadoHomologacion" x-model="estado" class="form-check-input" type="radio" value="1" id="estado_aprobado" />
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12">
                            <div class="form-check custom-option custom-option-basic" :class="estado == 0 ? 'checked' : ''">
                                <label class="form-check-label custom-option-content d-flex justify-content-between align-items-center py-2 px-3" for="estado_reprobado">
                                    <span class="custom-option-header p-0 border-0">
                                        <span class="fw-medium text-black small">Reprobado</span>
                                    </span>
                                    <input name="estadoHomologacion" wire:model="estadoHomologacion" x-model="estado" class="form-check-input" type="radio" value="0" id="estado_reprobado" />
                                </label>
                            </div>
                        </div>
                    </div>
                    @error('estadoHomologacion')<span class="text-danger small mt-1 d-block">{{ $message }}</span>@enderror
                </div>

                {{-- Campo Nota Condicional con Alpine --}}
                <div class="mb-3" x-show="estado == 1" x-cloak>
                    <label for="notaHomologacion" class="form-label fw-semibold text-black">Nota final</label>
                    <input type="text" inputmode="decimal" id="notaHomologacion" wire:model="notaHomologacion" class="form-control @error('notaHomologacion') is-invalid @enderror" placeholder="Ingrese la nota (ej. 4.50 o 90)">
                    <small class="text-muted">Acepta valores de 0 a 100 con hasta 2 decimales.</small>
                    @error('notaHomologacion')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                </div>

                <div class="mb-3">
                    <label for="sede_id" class="form-label fw-semibold text-black">Sede</label>
                    <select id="sede_id" wire:model="sedeHomologacionId" class="form-select @error('sedeHomologacionId') is-invalid @enderror">
                        <option value="">-- Seleccione una sede --</option>
                        @foreach($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                    @error('sedeHomologacionId')<span class="invalid-feedback d-block">{{$message}}</span>@enderror
                </div>

                <div class="mb-3">
                    <label for="obs" class="form-label fw-semibold text-black">Observación</label>
                    <textarea id="obs" wire:model="observacionHomologacion" class="form-control @error('observacionHomologacion') is-invalid @enderror" rows="4" placeholder="Ingrese las observaciones de la homologación..."></textarea>
                    @error('observacionHomologacion')<span class="invalid-feedback d-block">{{$message}}</span>@enderror
                </div>
            </div>
            <div class="offcanvas-footer border-top p-3 text-end bg-light">
                <button type="button" data-bs-dismiss="offcanvas" class="btn btn-outline-secondary rounded-pill me-2">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-pill">
                    <i class="ti ti-device-floppy me-1"></i> Guardar
                </button>
            </div>
        </form>
    </div>

    {{-- Offcanvas de Ajustes al Eliminar o Cambiar Estado de Homologación --}}
    <div wire:ignore.self class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEliminarHomologacion" aria-labelledby="offcanvasEliminarHomologacionLabel" data-bs-backdrop="true" data-bs-scroll="false">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold {{ $esAjustePorCambioEstado ? 'text-primary' : 'text-danger' }}" id="offcanvasEliminarHomologacionLabel">
                {{ $esAjustePorCambioEstado ? '¿Deseas hacer un ajuste tras cambiar el estado de la homologación?' : 'Eliminar homologación:' }}
                <span class="text-black fw-semibold d-block fs-6 mt-1">{{ $materiaAEliminar?->nombre ?? $nivelAEliminar?->nombre }}</span>
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <form wire:submit.prevent="ejecutarEliminacionYReversion" class="d-flex flex-column h-100">
            <div class="offcanvas-body flex-grow-1">

                {{-- Sección 1: Tareas de Consolidación --}}
                @php
                    $itemsTareas = $modo === 'materias' ? $materiaAEliminar?->tareasCulminadas : $nivelAEliminar?->tareasCulminadas;
                @endphp
                @if($itemsTareas && $itemsTareas->isNotEmpty())
                    <div class="mb-4">
                        <h6 class="fw-bold text-black mb-3">¿Desea ajustar alguna tarea?</h6>
                        <div class="row g-3">
                            @foreach($itemsTareas as $tareaConfig)
                                @php
                                    $tareaId = $tareaConfig->tarea_consolidacion_id;
                                    $nombreTarea = $tareaConfig->tareaConsolidacion?->nombre ?? 'Tarea #'.$tareaId;
                                @endphp
                                <div class="col-12">
                                    <div class="p-1">
                                        <label class="form-label text-black fw-semibold d-block mb-1">{{ $nombreTarea }}</label>
                                        <select wire:model="ajustesTareas.{{ $tareaId }}" class="form-select text-white fw-medium rounded-pill border-0" style="background-color: #00b5ad;">
                                            <option value="" class="bg-white text-dark">Sin asignar</option>
                                            @foreach($estadosTareasDisponibles as $estTarea)
                                                <option value="{{ $estTarea->id }}" class="bg-white text-dark">{{ $estTarea->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <hr class="my-3" />
                @endif

                {{-- Sección 2: Pasos de Crecimiento --}}
                @php
                    $itemsPasos = $modo === 'materias' ? $materiaAEliminar?->pasosCrecimiento : $nivelAEliminar?->pasosCrecimiento;
                @endphp
                @if($itemsPasos && $itemsPasos->isNotEmpty())
                    <div class="mb-4">
                        <h6 class="fw-bold text-black mb-3">¿Desea ajustar algún paso de crecimiento?</h6>
                        <div class="row g-3">
                            @foreach($itemsPasos as $paso)
                                <div class="col-12">
                                    <div class="p-1">
                                        <label class="form-label text-black fw-semibold d-block mb-1">{{ $paso->nombre }}</label>
                                        <select wire:model="ajustesPasos.{{ $paso->id }}" class="form-select text-white fw-medium rounded-pill border-0" style="background-color: #00b5ad;">
                                            <option value="" class="bg-white text-dark">Sin asignar</option>
                                            @foreach($estadosPasosDisponibles as $estPaso)
                                                <option value="{{ $estPaso->id }}" class="bg-white text-dark">{{ $estPaso->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <hr class="my-3" />
                @endif

                {{-- Sección 3: Tipo de Usuario --}}
                @if(($modo === 'materias' && $materiaAEliminar?->tipo_usuario_objetivo_id) || ($modo === 'niveles' && $nivelAEliminar?->tipo_usuario_objetivo_id))
                    <div class="mb-3">
                        <h6 class="fw-bold text-black mb-3">¿Desea ajustar el tipo de usuario?</h6>
                        <select wire:model="ajusteTipoUsuarioId" class="form-select">
                            <option value="">-- Sin cambio de tipo de usuario --</option>
                            @foreach($tiposUsuariosDisponibles as $tu)
                                <option value="{{ $tu->id }}">{{ $tu->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

            </div>
            <div class="offcanvas-footer border-top p-3 text-end bg-light">
                <button type="button" data-bs-dismiss="offcanvas" class="btn btn-outline-secondary rounded-pill me-2">Cancelar</button>
                <button type="submit" class="btn {{ $esAjustePorCambioEstado ? 'btn-primary' : 'btn-danger' }} rounded-pill">
                    <i class="ti {{ $esAjustePorCambioEstado ? 'ti-device-floppy' : 'ti-trash' }} me-1"></i>
                    {{ $esAjustePorCambioEstado ? 'Guardar' : 'Eliminar' }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {

            Livewire.on('abrir-offcanvas-homologacion', () => {
                const offcanvasEl = document.getElementById('offcanvasHomologacion');
                if (offcanvasEl) {
                    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl, {
                        backdrop: true,
                        scroll: false
                    });
                    bsOffcanvas.show();
                }
            });

            Livewire.on('cerrar-offcanvas-homologacion', () => {
                const offcanvasEl = document.getElementById('offcanvasHomologacion');
                if (offcanvasEl) {
                    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (bsOffcanvas) {
                        bsOffcanvas.hide();
                    }
                }
            });

            Livewire.on('abrir-offcanvas-eliminar', () => {
                const offcanvasEl = document.getElementById('offcanvasEliminarHomologacion');
                if (offcanvasEl) {
                    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl, {
                        backdrop: true,
                        scroll: false
                    });
                    bsOffcanvas.show();
                }
            });

            Livewire.on('cerrar-offcanvas-eliminar', () => {
                const offcanvasEl = document.getElementById('offcanvasEliminarHomologacion');
                if (offcanvasEl) {
                    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (bsOffcanvas) {
                        bsOffcanvas.hide();
                    }
                }
            });

            Livewire.on('confirmar-homologacion', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                const badgeClass = data.estadoId === 1 ? 'bg-label-success' : (data.estadoId === 2 ? 'bg-label-warning' : 'bg-label-danger');
                let htmlContent = `<div class="text-start fs-6 lh-base p-2">` +
                                  `<p class="mb-1"><strong>Estudiante:</strong> ${data.alumno}</p>` +
                                  `<p class="mb-1"><strong>${data.tipo}:</strong> ${data.item}</p>` +
                                  `<p class="mb-1"><strong>Estado:</strong> <span class="badge rounded-pill ${badgeClass}">${data.estado}</span></p>`;
                if (data.nota !== null && data.nota !== undefined && data.nota !== '') {
                    htmlContent += `<p class="mb-1"><strong>Nota Final:</strong> ${data.nota}</p>`;
                }
                htmlContent += `<p class="mb-0"><strong>Sede:</strong> ${data.sede}</p></div>`;

                Swal.fire({
                    title: '¿Confirmar homologación?',
                    html: htmlContent,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, confirmar homologación',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill me-2',
                        cancelButton: 'btn btn-outline-secondary rounded-pill'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('guardarHomologacion');
                    }
                });
            });

            Livewire.on('confirmar-eliminacion-directa', (event) => {
                const data = Array.isArray(event) ? event[0] : event;

                Swal.fire({
                    title: '¿Eliminar homologación?',
                    text: `Está a punto de eliminar la homologación de ${data.tipo.toLowerCase()}: ${data.nombre}.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-danger rounded-pill me-2',
                        cancelButton: 'btn btn-outline-secondary rounded-pill'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarHomologacionDirecta', data.id);
                    }
                });
            });

            Livewire.on('notificacion', (event) => {
                const data = Array.isArray(event) ? event[0] : event;

                Swal.fire({
                    icon: data.tipo || 'success',
                    title: data.titulo || '¡Realizado!',
                    text: data.mensaje,
                    timer: 2500,
                    showConfirmButton: false
                });
            });

        });
    </script>
    @endpush
</div>
