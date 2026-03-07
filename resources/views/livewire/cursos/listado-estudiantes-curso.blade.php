<div>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0 text-primary fw-bold">Usuarios inscritos</h5>

        <div class="d-flex gap-2">
            {{-- Buscador General Rápido --}}
            <div class="input-group input-group-sm" style="max-width: 290px;">
                <span class="input-group-text bg-white border-end-0"></span>
                <input wire:model.live.debounce.500ms="search" type="text" class="form-control border-start-0 ps-0 shadow-none" placeholder="Buscar...">
            </div>

            <button type="button" class="btn btn-outline-secondary rounded btn-lg" data-bs-toggle="offcanvas" data-bs-target="#filtrosEstudiantesOffcanvas">
                Filtros <i class="ti ti-filter ms-1"></i>
            </button>
        </div>
    </div>

    {{-- Tags de Filtro Activos --}}
    <div class="filter-tags mb-3">
        @if(!empty($search) || !empty($filtroEstado) || !empty($filtroAno))
            <span class="text-muted small me-2">Filtros aplicados:</span>

            @if(!empty($search))
                <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1" wire:click="removeTag('search')" title="Quitar filtro de búsqueda">
                    <span class="align-middle">"{{ Str::limit($search, 10) }}" <i class="ti ti-x ti-xs ms-1"></i></span>
                </button>
            @endif

            @if(!empty($filtroEstado))
                <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1" wire:click="removeTag('filtroEstado')" title="Quitar filtro de estado">
                    <span class="align-middle border-0 text-capitalize">{{ strtolower($filtroEstado) }} <i class="ti ti-x ti-xs ms-1"></i></span>
                </button>
            @endif

            @if(!empty($filtroAno))
                <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1" wire:click="removeTag('filtroAno')" title="Quitar filtro de año">
                    <span class="align-middle border-0">Año: {{ $filtroAno }} <i class="ti ti-x ti-xs ms-1"></i></span>
                </button>
            @endif

            <button type="button" wire:click="limpiarFiltros" class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1 mt-1 ms-2" title="Quitar todos los filtros">
                <span class="align-middle">Quitar todos <i class="ti ti-x ti-xs ms-1"></i></span>
            </button>
        @endif
    </div>

    {{-- Grid de Estudiantes --}}
    <div class="row position-relative">
        <div wire:loading.flex wire:target="search, filtroEstado, filtroAno, limpiarFiltros, removeTag" class="position-absolute w-100 h-100 justify-content-center align-items-center bg-white" style="z-index: 10; opacity: 0.7;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        @forelse ($estudiantes as $inscripcion)
            @php $alumno = $inscripcion->user; @endphp
            <div class="col-12 col-xl-4 col-md-6 mb-7">
                <div class="card h-100 shadow-sm border-0 rounded-4">
                    <div class="card-body d-flex flex-column justify-content-between p-3">
                        <div>
                            {{-- Cabecera con avatar, nombre y menú --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-md me-3">
                                        @if($alumno->foto && !in_array($alumno->foto, ["default-m.png", "default-f.png"]))
                                            <img src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$alumno->foto) }}" alt="{{ $alumno->nombre(3) }}" class="avatar-initial rounded-circle border border-2 border-white bg-light object-fit-cover">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-primary bg-opacity-10 text-primary fw-bold">{{ $alumno->inicialesNombre() }}</span>
                                        @endif
                                    </div>
                                    <div style="max-width: 140px;">
                                        <h6 class="mb-0 fw-bold text-black text-truncate" title="{{ $alumno->nombre(3) }}">{{ $alumno->nombre(3) }}</h6>
                                    </div>
                                </div>

                                {{-- Opciones rápidas (Dropdown placeholder) --}}
                                <div class="dropdown">
                                    <button class="btn btn-sm border p-1 rounded-circle text-black" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item" href="{{ route('usuario.perfil', $alumno->id) }}"><i class="ti ti-user me-2 text-black"></i> Ver perfil</a></li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Info de Contacto en el Body usando diseño de grilla --}}
                            <div class="row justify-content-between mb-2 mt-3">
                                <div class="col-12 col-xl-12 col-md-12 align-items-center mb-2">
                                    <div class="d-flex flex-column text-start">
                                        <small class="text-black"><i class="ti ti-mail text-black me-2"></i>Correo:</small>
                                        <small class="fw-semibold text-black text-truncate" title="{{ $alumno->email }}">
                                            {{ strtolower($alumno->email) }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-between mb-2">
                                <div class="col-12 col-xl-6 col-md-6 align-items-center mb-2">
                                    <div class="d-flex flex-column text-start">
                                        <small class="text-black"><i class="ti ti-phone text-black me-2"></i>Teléfono:</small>
                                        <small class="fw-semibold text-black">
                                            {{ $alumno->telefono_movil ?? 'Sin teléfono' }}
                                        </small>
                                    </div>
                                </div>
                                <div class="col-12 col-xl-6 col-md-6 align-items-center mb-2">
                                    <div class="d-flex flex-column text-xl-end text-md-end text-sm-start">
                                        <small class="text-black"><i class="ti ti-calendar-event text-black me-2"></i>Inscripción:</small>
                                        <small class="fw-semibold text-black">
                                            {{ \Carbon\Carbon::parse($inscripcion->fecha_inscripcion)->format('Y-m-d') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-between mb-2">
                                <div class="col-12 col-xl-12 col-md-12 align-items-center">
                                    <div class="d-flex flex-column text-start">
                                        <small class="text-black"><i class="ti ti-user text-black me-2"></i>Rol:</small>
                                        <small class="fw-semibold text-black">
                                            {{ $alumno->tipoUsuario->Nombre ?? 'Sin tipo' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer: Progreso y Estado --}}
                        <div class="d-flex flex-column pt-3 border-top mt-auto">
                            {{-- Estado e información del curso actual --}}
                            <div class="d-flex justify-content-between align-items-center mb-2 px-2 py-1 rounded-2">
                                <span class="badge rounded-pill fw-medium text-capitalize {{
                                    $inscripcion->estado === 'activo' ? 'bg-success bg-opacity-5 text-white' :
                                    ($inscripcion->estado === 'finalizado' ? 'bg-primary bg-opacity-5 text  -white' :
                                    'bg-warning bg-opacity-5 text-white')
                                }}" style="font-size: 0.7rem;">
                                    @if($inscripcion->estado === 'activo')
                                        <i class="ti ti-player-play-filled me-1" style="font-size: 0.65rem;"></i> En curso
                                    @elseif($inscripcion->estado === 'finalizado')
                                        <i class="ti ti-certificate me-1" style="font-size: 0.65rem;"></i> Finalizado
                                    @elseif($inscripcion->estado === 'suspendido')
                                        <i class="ti ti-player-pause-filled me-1" style="font-size: 0.65rem;"></i> Suspendido
                                    @else
                                        {{ strtolower($inscripcion->estado) }}
                                    @endif
                                </span>

                                <span class="small fw-bold {{
                                    (int)$inscripcion->porcentaje_progreso <= 35 ? 'text-danger' :
                                    ((int)$inscripcion->porcentaje_progreso <= 80 ? 'text-warning' : 'text-success')
                                }}">
                                    {{ $inscripcion->porcentaje_progreso ?? 0 }}%
                                </span>
                            </div>

                            {{-- Barra de progreso --}}
                            <div class="progress mb-1" style="height: 6px;">
                                <div class="progress-bar {{
                                    (int)$inscripcion->porcentaje_progreso <= 35 ? 'bg-danger' :
                                    ((int)$inscripcion->porcentaje_progreso <= 80 ? 'bg-warning' : 'bg-success')
                                }}"
                                     role="progressbar"
                                     style="width: {{ $inscripcion->porcentaje_progreso ?? 0 }}%"
                                     aria-valuenow="{{ $inscripcion->porcentaje_progreso ?? 0 }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="ti ti-users text-muted fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark">No hay estudiantes</h5>
                <p class="text-muted">No encontramos estudiantes inscritos que coincidan con los filtros aplicados en este curso.</p>
                <button wire:click="limpiarFiltros" class="btn btn-primary btn-sm rounded-pill mt-2">Limpiar filtros</button>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="row mt-4">
        @if ($estudiantes && $estudiantes->hasPages())
            <div class="col-12 text-center text-muted small mb-2">

            </div>
            <div class="col-12 d-flex justify-content-center">
                {!! $estudiantes->links(data: ['scrollTo' => false]) !!}
            </div>
        @endif
    </div>

    {{-- Offcanvas de Filtros --}}
    <div wire:ignore.self class="offcanvas offcanvas-end" tabindex="-1" id="filtrosEstudiantesOffcanvas" aria-labelledby="filtrosEstudiantesLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="filtrosEstudiantesLabel" class="offcanvas-title fw-semibold text-primary">Filtros de Búsqueda</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mb-4">
                <label class="form-label text-black">Búsqueda General</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input wire:model="search" type="text" class="form-control" placeholder="Nombre, email o cédula...">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-black">Estado del Curso</label>
                <select wire:model="filtroEstado" class="form-select text-capitalize">
                    <option value="">Todos los Estados</option>
                    <option value="activo">Activo (En Curso)</option>
                    <option value="finalizado">Finalizado</option>
                    <option value="suspendido">Suspendido</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label text-black">Año de Inscripción</label>
                <select wire:model="filtroAno" class="form-select">
                    <option value="">Cualquier Año</option>
                    @foreach($anosDisponibles as $ano)
                        <option value="{{ $ano }}">{{ $ano }}</option>
                    @endforeach
                </select>
            </div>



            <div class="offcanvas-footer p-5 border-top">
                <button type="button" wire:click="aplicarFiltros" class="btn btn-primary text-white rounded-pill waves-effect" data-bs-dismiss="offcanvas">Filtrar</button>
                <button type="button" wire:click="limpiarFiltros" class="btn btn-outline-secondary rounded-pill waves-effect">Restablecer </button>
            </div>
        </div>
    </div>
</div>
