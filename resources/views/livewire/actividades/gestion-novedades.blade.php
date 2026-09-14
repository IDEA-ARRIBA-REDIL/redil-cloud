<div>
    <!-- Encabezado y Métricas Rápidas -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-primary">
                <i class="ti ti-messages me-2"></i> Gestión de Novedades de Actividades
            </h4>
            <p class="text-muted mb-0">Revisa, atiende y responde las incidencias de inscripción reportadas por los feligreses.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @can('actividades.gestionar_novedades')
                <button type="button" class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTiposNovedad">
                    <i class="ti ti-settings me-1"></i> Tipos de novedad
                </button>
            @endcan
            <button type="button" class="btn btn-success rounded-pill" wire:click="exportarExcel" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="exportarExcel"><i class="ti ti-file-spreadsheet me-1"></i> Exportar a Excel</span>
                <span wire:loading wire:target="exportarExcel"><i class="ti ti-loader rotate me-1"></i> Exportando...</span>
            </button>
        </div>
    </div>

    <!-- Pestañas de Estado Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card cursor-pointer border shadow-none {{ $filtroEstado === '' ? 'border-primary' : '' }}" wire:click="$set('filtroEstado', '')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block fw-semibold">Todas las novedades</small>
                        <h4 class="mb-0 fw-bold">{{ $conteoTotal }}</h4>
                    </div>
                    <div class="badge rounded bg-label-primary p-2">
                        <i class="ti ti-list-details fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card cursor-pointer border shadow-none {{ $filtroEstado === 'no_revisado' ? 'border-danger' : '' }}" wire:click="$set('filtroEstado', 'no_revisado')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block fw-semibold text-danger">No revisadas</small>
                        <h4 class="mb-0 fw-bold text-danger">{{ $conteoNoRevisados }}</h4>
                    </div>
                    <div class="badge rounded bg-label-danger p-2">
                        <i class="ti ti-clock-pause fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card cursor-pointer border shadow-none {{ $filtroEstado === 'iniciado' ? 'border-info' : '' }}" wire:click="$set('filtroEstado', 'iniciado')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block fw-semibold text-info">En proceso / Iniciadas</small>
                        <h4 class="mb-0 fw-bold text-info">{{ $conteoIniciados }}</h4>
                    </div>
                    <div class="badge rounded bg-label-info p-2">
                        <i class="ti ti-rotate-clockwise fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card cursor-pointer border shadow-none {{ $filtroEstado === 'finalizado' ? 'border-success' : '' }}" wire:click="$set('filtroEstado', 'finalizado')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-success d-block fw-semibold text-success">Finalizadas / Respondidas</small>
                        <h4 class="mb-0 fw-bold text-success">{{ $conteoFinalizados }}</h4>
                    </div>
                    <div class="badge rounded bg-label-success p-2">
                        <i class="ti ti-circle-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="card border rounded mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3 col-12">
                    <label class="form-label small fw-semibold" for="busqueda">Buscador</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" id="busqueda" wire:model.live.debounce.400ms="busqueda" class="form-control form-control-sm" placeholder="Nombre, cédula, correo o asunto...">
                    </div>
                </div>

                <div class="col-md-3 col-12">
                    <label class="form-label small fw-semibold" for="filtroActividad">Actividad</label>
                    <select id="filtroActividad" wire:model.live="filtroActividadId" class="form-select form-select-sm">
                        <option value="">-- Todas las actividades --</option>
                        @foreach ($actividades as $act)
                            <option value="{{ $act->id }}">{{ $act->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-12">
                    <label class="form-label small fw-semibold" for="filtroTipo">Tipo de novedad</label>
                    <select id="filtroTipo" wire:model.live="filtroTipoNovedadId" class="form-select form-select-sm">
                        <option value="">-- Todos los tipos --</option>
                        @foreach ($tiposNovedad as $tn)
                            <option value="{{ $tn->id }}">{{ $tn->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label small fw-semibold" for="fechaInicio">Fecha desde</label>
                    <input type="date" id="fechaInicio" wire:model.live="filtroFechaInicio" class="form-control form-control-sm">
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label small fw-semibold" for="fechaFin">Fecha hasta</label>
                    <div class="d-flex gap-1">
                        <input type="date" id="fechaFin" wire:model.live="filtroFechaFin" class="form-control form-control-sm">
                        @if ($filtroEstado || $filtroActividadId || $filtroTipoNovedadId || $filtroFechaInicio || $filtroFechaFin || $busqueda)
                            <button type="button" wire:click="limpiarFiltros" class="btn btn-sm btn-outline-danger" title="Limpiar filtros">
                                <i class="ti ti-x"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Novedades en Grid de Cards (Responsive Móvil y Desktop) -->
    <div class="row g-4 mt-1">
        @forelse ($novedades as $nov)
            <div class="col-12 col-md-6" id="novedad-card-{{ $nov->id }}">
                <div class="card h-100 rounded-3 shadow-sm border">
                    <!-- Cabecera de la Card con fondo suave -->
                    <div class="card-header border-bottom px-4 pt-3 pb-2" style="background-color: #F9F9F9 !important;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
                            <div class="d-flex flex-column pe-2">
                                <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2 flex-wrap">
                                    <span style="word-break: break-word;">{{ $nov->nombre }}</span>
                                    <span class="badge bg-label-primary fs-tiny">#{{ $nov->id }}</span>
                                </h5>
                                <small class="text-muted mt-1">
                                    <i class="ti ti-calendar-time me-1"></i>{{ $nov->created_at ? $nov->created_at->format('d/m/Y H:i A') : 'Sin fecha' }}
                                </small>
                            </div>

                            <div class="ms-auto">
                                @can('actividades.gestionar_novedades')
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm rounded-pill {{ $nov->estado_badge }} dropdown-toggle py-1 px-3" data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ $nov->estado_nombre }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="javascript:void(0);" wire:click="cambiarEstado({{ $nov->id }}, 'no_revisado')"><span class="badge badge-dot bg-danger me-2"></span>No revisado</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" wire:click="cambiarEstado({{ $nov->id }}, 'iniciado')"><span class="badge badge-dot bg-info me-2"></span>Iniciado</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" wire:click="cambiarEstado({{ $nov->id }}, 'finalizado')"><span class="badge badge-dot bg-success me-2"></span>Finalizado</a></li>
                                        </ul>
                                    </div>
                                @else
                                    <span class="badge rounded-pill {{ $nov->estado_badge }} py-1 px-3">
                                        {{ $nov->estado_nombre }}
                                    </span>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <!-- Cuerpo de la Card con información en Grid -->
                    <div class="card-body px-4 py-3">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 d-flex flex-column">
                                <small class="text-muted">Actividad</small>
                                <span class="fw-semibold text-primary" title="{{ $nov->actividad->nombre ?? 'N/A' }}" style="word-break: break-word;">
                                    {{ $nov->actividad->nombre ?? 'N/A' }}
                                </span>
                                @if ($nov->actividad->tipo && $nov->actividad->tipo->tipo_escuelas)
                                    <small class="text-info mt-1" style="word-break: break-word;">
                                        <i class="ti ti-school me-1"></i> {{ $nov->materia->nombre ?? ($nov->materia_nombre ?? 'Escuela') }}
                                    </small>
                                @else
                                    <small class="text-muted mt-1" style="word-break: break-word;">
                                        <i class="ti ti-tag me-1"></i> {{ $nov->actividad->tipo->nombre ?? 'General' }}
                                    </small>
                                @endif
                            </div>

                            <div class="col-12 col-sm-6 d-flex flex-column">
                                <small class="text-muted">Tipo de novedad</small>
                                <div class="mt-1" style="max-width: 100%;">
                                    <span class="badge bg-label-dark text-wrap text-start lh-sm" style="white-space: normal !important; word-break: break-word !important; overflow-wrap: anywhere !important; max-width: 100% !important; display: inline-block !important;">
                                        {{ $nov->tipoNovedad->nombre ?? 'General' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="my-1 border-1">
                            </div>

                            <div class="col-6 d-flex flex-column">
                                <small class="text-muted">Identificación</small>
                                <span class="fw-semibold text-dark text-break">
                                    <i class="ti ti-id me-1 text-muted"></i>{{ $nov->identificacion ?: 'No indicada' }}
                                </span>
                            </div>

                            <div class="col-6 d-flex flex-column">
                                <small class="text-muted">Teléfono / WhatsApp</small>
                                @if ($nov->telefono)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $nov->telefono) }}" target="_blank" class="fw-semibold text-success text-break">
                                        <i class="ti ti-brand-whatsapp me-1"></i>{{ $nov->telefono }}
                                    </a>
                                @else
                                    <span class="fw-semibold text-muted">No indicado</span>
                                @endif
                            </div>

                            <div class="col-12 d-flex flex-column">
                                <small class="text-muted">Correo electrónico</small>
                                <span class="fw-semibold text-dark text-break" title="{{ $nov->email }}">
                                    <i class="ti ti-mail me-1 text-muted"></i>{{ $nov->email }}
                                </span>
                            </div>

                            <div class="col-12">
                                <hr class="my-1 border-1">
                            </div>

                            <div class="col-12 d-flex flex-column">
                                <small class="text-muted">Asunto</small>
                                <span class="fw-bold text-dark text-break">{{ $nov->asunto }}</span>
                                <div class="p-2 rounded bg-light small text-dark mt-1 text-break" style="max-height: 90px; overflow-y: auto; white-space: pre-wrap; word-break: break-word;">{{ $nov->descripcion }}</div>
                            </div>

                            @if ($nov->fecha_respuesta)
                                <div class="col-12">
                                    <div class="p-2 rounded bg-label-success small d-flex justify-content-between align-items-center flex-wrap gap-1">
                                        <span><i class="ti ti-circle-check me-1"></i> Respondida el {{ $nov->fecha_respuesta->format('d/m/Y H:i') }}</span>
                                        @if ($nov->respondidoPor)
                                            <small class="text-white">Por: {{ $nov->respondidoPor->primer_nombre }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Pie de la Card con acción rápida -->
                    <div class="card-footer border-top bg-transparent px-4 py-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            @if($nov->estado === 'no_revisado')
                                <span class="text-danger fw-semibold"><i class="ti ti-clock-pause me-1"></i>Pendiente</span>
                            @elseif($nov->estado === 'iniciado')
                                <span class="text-info fw-semibold"><i class="ti ti-rotate-clockwise me-1"></i>En atención</span>
                            @else
                                <span class="text-success fw-semibold"><i class="ti ti-check me-1"></i>Finalizado</span>
                            @endif
                        </small>
                        <button type="button" wire:click="verNovedad({{ $nov->id }})" class="btn btn-sm btn-primary rounded-pill px-4 waves-effect shadow-sm">
                            <i class="ti ti-mail-forward me-1"></i> Ver / Contestar
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center shadow-sm border rounded-3">
                    <div class="mb-3">
                        <i class="ti ti-messages-off text-muted" style="font-size: 3.5rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No se encontraron novedades</h5>
                    <p class="text-muted mb-0">No hay incidencias que coincidan con los filtros seleccionados.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($novedades->hasPages())
        <div class="card border rounded mt-4">
            <div class="card-body py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">Mostrando {{ $novedades->firstItem() }} a {{ $novedades->lastItem() }} de {{ $novedades->total() }} registros</small>
                <div>{{ $novedades->links() }}</div>
            </div>
        </div>
    @endif

    <!-- Modal: Detalle y Respuesta a la Novedad -->
    <div wire:ignore.self class="modal fade" id="modalDetalleNovedad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom pb-3">
                    <div>
                        <h5 class="modal-title fw-bold text-primary mb-0">
                            <i class="ti ti-file-text me-1"></i> Detalle de la Novedad #{{ $novedadSeleccionada?->id }}
                        </h5>
                        <small class="text-muted">Actividad: <b>{{ $novedadSeleccionada?->actividad->nombre }}</b></small>
                    </div>
                    <button type="button" class="btn-close" wire:click="cerrarModalDetalle" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-3">
                    @if ($novedadSeleccionada)
                        <!-- Información del Usuario y Actividad -->
                        <div class="row g-3 p-3 bg-light rounded mb-3">
                            <div class="col-md-6 col-12">
                                <small class="text-muted d-block">Feligrés:</small>
                                <span class="fw-bold text-dark">{{ $novedadSeleccionada->nombre }}</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <small class="text-muted d-block">Cédula / Identificación:</small>
                                <span class="fw-bold text-dark">{{ $novedadSeleccionada->identificacion }}</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <small class="text-muted d-block">Teléfono:</small>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $novedadSeleccionada->telefono) }}" target="_blank" class="fw-semibold text-success">
                                    <i class="ti ti-brand-whatsapp me-1"></i> {{ $novedadSeleccionada->telefono }}
                                </a>
                            </div>
                            <div class="col-md-6 col-12">
                                <small class="text-muted d-block">Correo electrónico:</small>
                                <span class="fw-semibold text-dark">{{ $novedadSeleccionada->email }}</span>
                            </div>

                            @if ($novedadSeleccionada->actividad->tipo && $novedadSeleccionada->actividad->tipo->tipo_escuelas)
                                <div class="col-12 border-top pt-2 mt-2">
                                    <small class="text-muted d-block">Materia / Escuela deseada:</small>
                                    <span class="badge bg-label-info fw-bold fs-6">
                                        <i class="ti ti-school me-1"></i> {{ $novedadSeleccionada->materia->nombre ?? ($novedadSeleccionada->materia_nombre ?? 'No especificada') }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Clasificación y Detalle -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <span class="badge bg-label-dark text-wrap text-start lh-sm" style="white-space: normal !important; word-break: break-word !important; overflow-wrap: anywhere !important; max-width: 100% !important; display: inline-block !important;">
                                    {{ $novedadSeleccionada->tipoNovedad->nombre ?? 'General' }}
                                </span>
                                <small class="text-muted">{{ $novedadSeleccionada->created_at ? $novedadSeleccionada->created_at->format('d/m/Y H:i A') : '' }}</small>
                            </div>
                            <h6 class="fw-bold text-dark mb-1 text-break">{{ $novedadSeleccionada->asunto }}</h6>
                            <div class="p-3 border rounded bg-white text-dark small text-break" style="white-space: pre-wrap; word-break: break-word;">{{ $novedadSeleccionada->descripcion }}</div>
                        </div>

                        <!-- Historial de Respuesta si ya existe -->
                        @if ($novedadSeleccionada->fecha_respuesta)
                            <div class="alert alert-success p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="alert-heading mb-0 fw-bold"><i class="ti ti-check me-1"></i> Respuesta enviada anteriormente</h6>
                                    <small>{{ $novedadSeleccionada->fecha_respuesta->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="small mb-1">{{ $novedadSeleccionada->respuesta }}</div>
                                <small class="text-muted">Respondido por: <b>{{ $novedadSeleccionada->respondidoPor ? ($novedadSeleccionada->respondidoPor->primer_nombre . ' ' . $novedadSeleccionada->respondidoPor->primer_apellido) : 'Personal Administrativo' }}</b></small>
                            </div>
                        @endif

                        <!-- Formulario de Respuesta -->
                        @can('actividades.gestionar_novedades')
                            <div class="border-top pt-3">
                                <h6 class="fw-bold text-dark mb-2">
                                    <i class="ti ti-send me-1 text-primary"></i> Redactar Respuesta al Usuario
                                </h6>
                                <p class="small text-muted mb-2">
                                    Esta respuesta se enviará inmediatamente al correo <b>{{ $novedadSeleccionada->email }}</b> utilizando la plantilla oficial de la iglesia.
                                </p>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold" for="mensajeRespuesta">Mensaje de respuesta <span class="text-danger">*</span></label>
                                    <textarea id="mensajeRespuesta" wire:model="mensajeRespuesta" rows="4" class="form-control" placeholder="Escribe aquí la respuesta formal para el feligrés (ej: 'Hemos actualizado tu historial y ya puedes ingresar a matricularte...')..."></textarea>
                                    @error('mensajeRespuesta') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="row align-items-center g-2">
                                    <div class="col-md-6 col-12">
                                        <label class="form-label small fw-semibold" for="nuevoEstado">Nuevo estado de la novedad</label>
                                        <select id="nuevoEstado" wire:model="nuevoEstado" class="form-select form-select-sm">
                                            <option value="iniciado">Iniciado (En proceso de revisión)</option>
                                            <option value="finalizado">Finalizado (Resuelto / Completado)</option>
                                            <option value="no_revisado">No revisado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-12 text-end pt-3">
                                        <button type="button" wire:click="enviarRespuesta" wire:loading.attr="disabled" class="btn btn-primary rounded-pill px-4">
                                            <span wire:loading.remove wire:target="enviarRespuesta"><i class="ti ti-send me-1"></i> Enviar respuesta por email</span>
                                            <span wire:loading wire:target="enviarRespuesta"><i class="ti ti-loader rotate me-1"></i> Enviando correo...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endcan
                    @endif
                </div>

                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" wire:click="cerrarModalDetalle">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Administrar Tipos de Novedad -->
    <div wire:ignore.self class="modal fade" id="modalTiposNovedad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-primary mb-0">
                        <i class="ti ti-category me-1"></i> Tipos de Novedad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-3">
                    <!-- Formulario Nuevo Tipo -->
                    @can('actividades.gestionar_novedades')
                        <div class="p-3 border rounded  mb-3">
                            <h6 class="fw-bold mb-2 small text-dark">Agregar nuevo tipo de novedad</h6>
                            <div class="mb-2">
                                <input type="text" wire:model="nuevoTipoNombre" class="form-control form-control-sm" placeholder="Nombre (ej: Certificado pendiente)">
                                @error('nuevoTipoNombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-2">
                                <input type="text" wire:model="nuevoTipoDescripcion" class="form-control form-control-sm" placeholder="Descripción breve (opcional)">
                            </div>
                            <button type="button" wire:click="crearTipoNovedad" class="btn btn-sm btn-primary rounded-pill">
                                <i class="ti ti-plus me-1"></i> Agregar opción
                            </button>
                        </div>
                    @endcan

                    <!-- Listado de Tipos -->
                    <h6 class="fw-bold mb-2 text-black">Opciones configuradas</h6>
                    <ul class="list-group list-group-flush border rounded">
                        @foreach ($tiposNovedad as $t)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 gap-2">
                                <div class="me-2 text-break" style="word-break: break-word;">
                                    <div class="fw-semibold small {{ !$t->activo ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                        {{ $t->nombre }}
                                    </div>
                                    @if ($t->descripcion)
                                        <small class="text-muted d-block">{{ $t->descripcion }}</small>
                                    @endif
                                </div>
                                @can('actividades.gestionar_novedades')
                                    <button type="button" wire:click="toggleTipoNovedad({{ $t->id }})" class="btn btn-xs rounded-pill flex-shrink-0 {{ $t->activo ? 'btn-label-success' : 'btn-label-secondary' }}">
                                        {{ $t->activo ? 'Activo' : 'Inactivo' }}
                                    </button>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-primary rounded-pill btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Livewire.on('abrirModal', (data) => {
        const modalEl = document.getElementById(data.nombreModal);
        if (modalEl) {
            let modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(modalEl);
            }
            modalInstance.show();
        }
    });

    Livewire.on('cerrarModal', (data) => {
        const modalEl = document.getElementById(data.nombreModal);
        if (modalEl) {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
    });

    Livewire.on('msn', (data) => {
        Swal.fire({
            title: data.msnTitulo || 'Información',
            text: data.msnTexto || '',
            icon: data.msnIcono || 'info',
            customClass: {
                confirmButton: 'btn btn-primary rounded-pill px-4'
            },
            buttonsStyling: false
        });
    });
</script>
@endscript
