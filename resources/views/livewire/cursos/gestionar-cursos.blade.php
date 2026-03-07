<div>
    @php
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    @endphp

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Listado de cursos</h5>
            @if ($rolActivo && $rolActivo->hasPermissionTo('cursos.opcion_crear_curso'))
            <a href="{{ route('cursos.crear') }}" class="btn btn-primary"><i class="ti ti-plus me-1 text-white"></i> Nuevo curso</a>
            @endif
        </div>

        <div class="card-body p-5">
            <div class="d-flex justify-content-end align-items-center mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-stretch gap-2">
                    <div class="input-group" style="max-width: 290px;">
                        <span class="input-group-text bg-white border-end-0"></span>
                        <input wire:model.live.debounce.500ms="search" type="text" class="form-control border-start-0 ps-0 shadow-none" placeholder="Buscar cursos...">
                    </div>

                    <button style="width:auto; padding:10px 15px;" type="button" class="btn btn-outline-secondary rounded text-nowrap" data-bs-toggle="offcanvas" data-bs-target="#filtrosCursosOffcanvas">
                        Filtros <i class="ti ti-filter ms-1"></i>
                    </button>
                </div>
            </div>

            {{-- Tags de Filtro Activos --}}
            <div class="filter-tags mb-4">
                @if(!empty($search) || !empty($filtroEstado) || !empty($filtroDificultad) || !empty($filtroCarrera) || !empty($filtroCategoria))
                    <span class="text-muted small me-2">Filtros aplicados:</span>

                    @if(!empty($search))
                        <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1" wire:click="removeTag('search')" title="Quitar filtro de búsqueda">
                            <span class="align-middle">"{{ Str::limit($search, 10) }}" <i class="ti ti-x ti-xs ms-1"></i></span>
                        </button>
                    @endif

                    @if(!empty($filtroEstado))
                        <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1" wire:click="removeTag('filtroEstado')">
                            <span class="align-middle border-0 text-capitalize">{{ strtolower($filtroEstado) }} <i class="ti ti-x ti-xs ms-1"></i></span>
                        </button>
                    @endif

                    @if(!empty($filtroDificultad))
                        <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1" wire:click="removeTag('filtroDificultad')">
                            <span class="align-middle border-0 text-capitalize">{{ strtolower($filtroDificultad) }} <i class="ti ti-x ti-xs ms-1"></i></span>
                        </button>
                    @endif

                    @if(!empty($filtroCarrera))
                        @php $carreraName = collect($carrerasList)->firstWhere('id', $filtroCarrera)->nombre ?? 'Carrera'; @endphp
                        <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1" wire:click="removeTag('filtroCarrera')">
                            <span class="align-middle border-0">{{ Str::limit($carreraName, 15) }} <i class="ti ti-x ti-xs ms-1"></i></span>
                        </button>
                    @endif

                    @if(!empty($filtroCategoria))
                        @php $catName = collect($categoriasList)->firstWhere('id', $filtroCategoria)->nombre ?? 'Categoría'; @endphp
                        <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1" wire:click="removeTag('filtroCategoria')">
                            <span class="align-middle border-0">{{ Str::limit($catName, 15) }} <i class="ti ti-x ti-xs ms-1"></i></span>
                        </button>
                    @endif

                    <button type="button" wire:click="limpiarFiltros" class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1 mt-1 ms-2" title="Quitar todos los filtros">
                        <span class="align-middle">Quitar todos <i class="ti ti-x ti-xs ms-1"></i></span>
                    </button>
                @endif
            </div>

            <div class="row g-4 mt-1">
                @forelse($cursos as $curso)
                <div class="col-12 col-xl-4 col-md-6">
                    <div class="card h-100 border rounded">
                        <div class="position-relative">
                             <img class="card-img-top object-fit-cover" style="height: 180px;"
                                 src="{{ $curso->imagen_portada ? Storage::url($configuracion->ruta_almacenamiento.'/img/cursos/portadas/'.$curso->imagen_portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png') }}"
                                 alt="Portada {{ $curso->nombre }}"
                                 onerror="this.onerror=null; this.src='{{ Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png') }}';" />
                            @if($curso->es_gratuito)
                                <span class="badge bg-success text-white position-absolute top-0 end-0 m-2">Gratis</span>
                            @else
                                <span class="badge btn-secondary  text-white position-absolute top-0 end-0 m-2">${{ number_format($curso->precio, 0) }}</span>
                            @endif
                        </div>

                        <div class="card-header pb-2">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-start">
                                    <div class="me-2 mt-1">
                                        <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $curso->nombre }}</h5>
                                        <div class="client-info fw-semibold text-muted small mt-1">
                                          Carrera: {{ $curso->carrera ? $curso->carrera->nombre : 'Curso General' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="ms-auto">
                                    <div class="dropdown zindex-2 p-1 float-end">
                                        <button type="button" class="btn rounded-circle dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @php
                                                // Logica combinada: si el rol NO tiene permiso global, verificamos a nivel cargo de curso
                                                $esAdminGeneral = false;
                                                $puedeEditarCurso = false;
                                                $puedeEditarRestricciones = false;
                                                $puedeEditarContenido = false;
                                                $puedeGestionarEquipo = false;
                                                $puedeGestionarEstudiantes = false;

                                                if ($rolActivo) {
                                                    $esAdminGeneral = $rolActivo->hasPermissionTo('cursos.subitem_gestionar_cursos');
                                                }

                                                if ($esAdminGeneral) {
                                                    $puedeEditarCurso = $rolActivo->hasPermissionTo('cursos.opcion_editar_curso');
                                                    $puedeEditarRestricciones = $rolActivo->hasPermissionTo('cursos.opcion_restricciones_curso');
                                                    $puedeEditarContenido = $rolActivo->hasPermissionTo('cursos.opcion_contenido_curso');
                                                    $puedeGestionarEquipo = $rolActivo->hasPermissionTo('cursos.opcion_gestion_equipo_curso');
                                                    $puedeGestionarEstudiantes = $rolActivo->hasPermissionTo('cursos.opcion_gestion_inscritos_curso');
                                                } else {
                                                    // Chequeamos validaciones especificas del cargo del usuario para ESTE curso en iteración
                                                    $cargoParaEsteCurso = $curso->equipo()->where('usuario_id', auth()->id())->where('activo', true)->first();
                                                    if ($cargoParaEsteCurso && $cargoParaEsteCurso->tipoCargo) {
                                                        $puedeEditarCurso = $cargoParaEsteCurso->tipoCargo->puede_editar_curso;
                                                        $puedeEditarRestricciones = $cargoParaEsteCurso->tipoCargo->puede_editar_restricciones;
                                                        $puedeEditarContenido = $cargoParaEsteCurso->tipoCargo->puede_editar_contenido;
                                                        $puedeGestionarEquipo = $cargoParaEsteCurso->tipoCargo->puede_gestionar_equipo;
                                                        $puedeGestionarEstudiantes = $cargoParaEsteCurso->tipoCargo->puede_gestionar_estudiantes;
                                                    }
                                                }

                                                // Todos pueden "ver detalles" y "detalle aprendizaje" si están viendo esta tabla independientemente.
                                                // Eliminacion: Usualmente reservada SÓLO para admins globales puros.
                                                $puedeEliminarCurso = $rolActivo ? $rolActivo->hasPermissionTo('cursos.opcion_eliminar_curso') : false;
                                            @endphp

                                            <li><a class="dropdown-item" target="_blank" href="{{ route('cursos.previsualizar', $curso->slug) }}">Ver detalles</a></li>

                                            @if ($puedeEditarCurso)
                                            <li><a class="dropdown-item" href="{{ route('cursos.editar', $curso->id) }}">Editar</a></li>
                                            @endif

                                            @if ($puedeEditarRestricciones)
                                            <li><a class="dropdown-item" href="{{ route('cursos.restricciones', $curso->id) }}">Restricciones</a></li>
                                            @endif

                                            @if ($puedeEditarContenido)
                                            <li><a class="dropdown-item" href="{{ route('cursos.contenido', $curso->id) }}">Contenido</a></li>
                                            @endif

                                            <li><a class="dropdown-item" href="{{ route('cursos.detalle', $curso->id) }}">Detalle aprendizaje</a></li>

                                            @if ($puedeGestionarEquipo)
                                            <li><a class="dropdown-item" href="{{ route('cursos.equipo', $curso->id) }}">Gestión de equipo</a></li>
                                            @endif

                                            @if ($puedeGestionarEstudiantes)
                                            <li><a class="dropdown-item" href="{{ route('cursos.inscritos', $curso->id) }}">Gestión de inscritos</a></li>
                                            @endif

                                            @if ($puedeEliminarCurso)
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0);">Eliminar</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-label-{{ $curso->estado == 'Publicado' ? 'success' : ($curso->estado == 'Borrador' ? 'warning' : 'secondary') }} me-2">
                                    {{ $curso->estado }}
                                </span>
                                <span class="text-muted small"><i class="ti ti-chart-bar me-1"></i>{{ $curso->nivel_dificultad }}</span>
                            </div>

                            <p class="text-muted small mb-3">
                                {{ Str::limit($curso->descripcion_corta, 80) }}
                            </p>

                            <div class="d-flex flex-row justify-content-between mb-2">
                                <div class="d-flex flex-row" title="Duración Estimada">
                                    <i class="ti ti-clock text-black"></i>
                                    <div class="d-flex flex-column">
                                        <small class="fw-semibold ms-1 text-black">
                                            {{ $curso->duracion_estimada_dias > 0 ? $curso->duracion_estimada_dias . ' Días' : 'A su ritmo' }}
                                        </small>
                                    </div>
                                </div>

                                <div class="d-flex flex-row" title="Cupos Disponibles">
                                    <i class="ti ti-users text-black"></i>
                                    <div class="d-flex flex-column">
                                        <small class="fw-semibold ms-1 text-black">
                                            {{ $curso->cupos_totales ? $curso->cupos_totales . ' Cupos' : 'Ilimitado' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer" style="">

                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                     <div class="alert alert-warning text-center">
                        No se encontraron cursos con los filtros seleccionados.
                    </div>
                </div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $cursos->links() }}
            </div>
        </div>
    </div>

    {{-- Offcanvas de Filtros --}}
    <div wire:ignore.self class="offcanvas offcanvas-end" tabindex="-1" id="filtrosCursosOffcanvas" aria-labelledby="filtrosCursosLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="filtrosCursosLabel" class="offcanvas-title fw-bold text-primary">Filtros de Cursos</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">

            <div class="mb-4">
                <label class="form-label text-black">Búsqueda General</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input wire:model="search" type="text" class="form-control" placeholder="Buscar curso...">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-black">Estado</label>
                <select wire:model="filtroEstado" class="form-select text-capitalize">
                    <option value="">Todos los Estados</option>
                    <option value="Borrador">Borrador</option>
                    <option value="Publicado">Publicado</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label text-black">Dificultad</label>
                <select wire:model="filtroDificultad" class="form-select text-capitalize">
                    <option value="">Todas las Dificultades</option>
                    <option value="Principiante">Principiante</option>
                    <option value="Intermedio">Intermedio</option>
                    <option value="Avanzado">Avanzado</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label text-black">Carrera</label>
                <select wire:model="filtroCarrera" class="form-select">
                    <option value="">Todas las Carreras</option>
                    @foreach($carrerasList as $carrera)
                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label text-black">Categoría</label>
                <select wire:model="filtroCategoria" class="form-select">
                    <option value="">Todas las Categorías</option>
                    @foreach($categoriasList as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

           <div class="offcanvas-footer p-5 border-top">
                <button type="button" wire:click="aplicarFiltros" class="btn btn-primary btn-md rounded-pill waves-effect text-white" data-bs-dismiss="offcanvas">Filtrar</button>
                <button type="button" wire:click="limpiarFiltros" class="btn  btn-outline-secondary  btn-md rounded-pill waves-effect">Restablecer </button>
            </div>
        </div>
    </div>
</div>
