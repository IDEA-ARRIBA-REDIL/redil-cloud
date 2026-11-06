<div>
    <style>
        .novedad-clean-card {
            background: #ffffff;
            border: 1px solid #e5e7eb !important;
            border-radius: 12px !important;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .novedad-clean-card:hover {
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05) !important;
        }

        .kpi-clean-card {
            background: #ffffff;
            border: 1px solid #e5e7eb !important;
            border-radius: 10px !important;
            transition: all 0.15s ease;
        }

        .kpi-clean-card.kpi-active {
            border-color: var(--bs-primary) !important;
            box-shadow: 0 0 0 1px var(--bs-primary) !important;
        }

        .kpi-icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f3f4f6;
            color: #4b5563;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .kpi-clean-card.kpi-active .kpi-icon-circle {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }

        .status-pill-subtle {
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.3rem 0.75rem;
            border-radius: 50rem;
            border: 1px solid transparent;
        }

        .status-pill-no_revisado {
            background-color: #fef2f2 !important;
            color: #b91c1c !important;
            border-color: #fecaca !important;
        }

        .status-pill-iniciado {
            background-color: #f0f9ff !important;
            color: #0369a1 !important;
            border-color: #bae6fd !important;
        }

        .status-pill-finalizado {
            background-color: #f0fdf4 !important;
            color: #15803d !important;
            border-color: #bbf7d0 !important;
        }

        .novedad-field-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .novedad-field-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
        }

        .novedad-desc-box {
            border-left: 3px solid #d1d5db;
            padding-left: 0.75rem;
            color: #4b5563;
            font-size: 0.85rem;
            max-height: 85px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>

    <!-- Encabezado y Acciones Superiores -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-semibold text-primary">Gestión de Novedades</h4>
            <p class="text-muted mb-0">Revisa, atiende y responde las novedades generales y de actividades reportadas por
                los usuarios.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('novedades.publica.crear') }}" target="_blank" class="btn btn-outline-secondary rounded-pill"
                title="Abrir el formulario público de registro de novedades">
                <i class="ti ti-external-link me-1"></i> Formulario público
            </a>
            @if ($puedeGestionar)
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-toggle="modal"
                    data-bs-target="#modalTiposNovedad">
                    <i class="ti ti-settings me-1"></i> Tipos de novedad
                </button>
            @endif
            <button type="button" class="btn btn-outline-secondary rounded-pill" wire:click="exportarExcel"
                wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="exportarExcel"><i class="ti ti-file-spreadsheet me-1"></i>
                    Exportar a Excel</span>
                <span wire:loading wire:target="exportarExcel"><i class="ti ti-loader rotate me-1"></i>
                    Exportando...</span>
            </button>
        </div>
    </div>

    <!-- Pestañas de Estado Rápidas (Diseño sobrio y uniforme) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card cursor-pointer shadow-none kpi-clean-card {{ $filtroEstado === '' ? 'kpi-active' : '' }}"
                wire:click="$set('filtroEstado', '')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block">Todas las novedades</small>
                        <h4 class="mb-0 fw-bold text-dark mt-1">{{ $conteoTotal }}</h4>
                    </div>
                    <div class="kpi-icon-circle">
                        <i class="ti ti-list-details fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card cursor-pointer shadow-none kpi-clean-card {{ $filtroEstado === 'no_revisado' ? 'kpi-active' : '' }}"
                wire:click="$set('filtroEstado', 'no_revisado')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block">No revisadas</small>
                        <h4 class="mb-0 fw-bold text-dark mt-1">{{ $conteoNoRevisados }}</h4>
                    </div>
                    <div class="kpi-icon-circle">
                        <i class="ti ti-clock-pause fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card cursor-pointer shadow-none kpi-clean-card {{ $filtroEstado === 'iniciado' ? 'kpi-active' : '' }}"
                wire:click="$set('filtroEstado', 'iniciado')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block">En proceso / Iniciadas</small>
                        <h4 class="mb-0 fw-bold text-dark mt-1">{{ $conteoIniciados }}</h4>
                    </div>
                    <div class="kpi-icon-circle">
                        <i class="ti ti-rotate-clockwise fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card cursor-pointer shadow-none kpi-clean-card {{ $filtroEstado === 'finalizado' ? 'kpi-active' : '' }}"
                wire:click="$set('filtroEstado', 'finalizado')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block">Finalizadas / Respondidas</small>
                        <h4 class="mb-0 fw-bold text-dark mt-1">{{ $conteoFinalizados }}</h4>
                    </div>
                    <div class="kpi-icon-circle">
                        <i class="ti ti-circle-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="card shadow-none border rounded-3 mb-4" style="border-color: #e5e7eb !important;">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2 col-12">
                    <label class="form-label small text-muted mb-1" for="busqueda">Buscador</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                        <input type="text" id="busqueda" wire:model.live.debounce.400ms="busqueda"
                            class="form-control form-control-sm" placeholder="Nombre, cédula, correo...">
                    </div>
                </div>

                <div class="col-md-2 col-12">
                    <label class="form-label small text-muted mb-1" for="filtroSede">Sede</label>
                    <select id="filtroSede" wire:model.live="filtroSedeId" class="form-select form-select-sm">
                        <option value="">Todas las sedes</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-12">
                    <label class="form-label small text-muted mb-1" for="filtroActividad">Origen / Actividad</label>
                    <select id="filtroActividad" wire:model.live="filtroActividadId" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="general">Novedad General (Sin actividad)</option>
                        @foreach ($actividades as $act)
                            <option value="{{ $act->id }}">{{ $act->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-12">
                    <label class="form-label small text-muted mb-1" for="filtroTipo">Tipo de novedad</label>
                    <select id="filtroTipo" wire:model.live="filtroTipoNovedadId" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        @foreach ($tiposNovedad as $tn)
                            <option value="{{ $tn->id }}">{{ $tn->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label small text-muted mb-1" for="fechaInicio">Fecha desde</label>
                    <input type="date" id="fechaInicio" wire:model.live="filtroFechaInicio"
                        class="form-control form-control-sm">
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label small text-muted mb-1" for="fechaFin">Fecha hasta</label>
                    <div class="d-flex gap-1">
                        <input type="date" id="fechaFin" wire:model.live="filtroFechaFin"
                            class="form-control form-control-sm">
                        @if (
                            $filtroEstado ||
                                $filtroActividadId ||
                                $filtroSedeId ||
                                $filtroTipoNovedadId ||
                                $filtroFechaInicio ||
                                $filtroFechaFin ||
                                $busqueda)
                            <button type="button" wire:click="limpiarFiltros"
                                class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                                <i class="ti ti-x"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Novedades en Grid de Cards -->
    <div class="row g-4 mt-1">
        @forelse ($novedades as $nov)
            <div class="col-12 col-md-6" id="novedad-card-{{ $nov->id }}">
                <div class="card h-100 shadow-none novedad-clean-card">
                    <!-- Cabecera limpia sobre fondo blanco -->
                    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 w-100">
                            <div class="d-flex flex-column pe-2">
                                <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2 flex-wrap">
                                    <span style="word-break: break-word;">{{ $nov->nombre }}</span>
                                    <span class="text-muted fw-normal fs-6">#{{ $nov->id }}</span>
                                </h5>
                                <small class="text-muted mt-1">
                                    <i
                                        class="ti ti-calendar-time me-1"></i>{{ $nov->created_at ? $nov->created_at->format('d/m/Y H:i A') : 'Sin fecha' }}
                                </small>
                            </div>

                            <div class="ms-auto">
                                @if ($puedeGestionar)
                                    <div class="btn-group">
                                        <button type="button"
                                            class="btn btn-sm status-pill-subtle status-pill-{{ $nov->estado }} dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ $nov->estado_nombre }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="javascript:void(0);"
                                                    wire:click="cambiarEstado({{ $nov->id }}, 'no_revisado')">No
                                                    revisado</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);"
                                                    wire:click="cambiarEstado({{ $nov->id }}, 'iniciado')">Iniciado</a>
                                            </li>
                                            <li><a class="dropdown-item" href="javascript:void(0);"
                                                    wire:click="cambiarEstado({{ $nov->id }}, 'finalizado')">Finalizado</a>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <span class="status-pill-subtle status-pill-{{ $nov->estado }}">
                                        {{ $nov->estado_nombre }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Cuerpo de la Card con tipografía limpia y sin bloques de color -->
                    <div class="card-body px-4 py-3">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 d-flex flex-column">
                                <span class="novedad-field-label">Origen / Actividad</span>
                                @if ($nov->actividad)
                                    <span class="novedad-field-value" title="{{ $nov->actividad->nombre }}"
                                        style="word-break: break-word;">
                                        {{ $nov->actividad->nombre }}
                                    </span>
                                    @if ($nov->actividad->tipo && $nov->actividad->tipo->tipo_escuelas)
                                        <small class="text-muted mt-1" style="word-break: break-word;">
                                            <i
                                                class="ti ti-school me-1"></i>{{ $nov->materia->nombre ?? ($nov->materia_nombre ?? 'Escuela') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="novedad-field-value">Novedad General</span>
                                @endif
                            </div>

                            <div class="col-12 col-sm-6 d-flex flex-column">
                                <span class="novedad-field-label">Tipo de novedad</span>
                                <span class="novedad-field-value" style="word-break: break-word;">
                                    {{ $nov->tipoNovedad->nombre ?? 'General' }}
                                </span>
                            </div>

                            <div class="col-6 d-flex flex-column">
                                <span class="novedad-field-label">Identificación</span>
                                <span class="novedad-field-value text-break">
                                    <i
                                        class="ti ti-id me-1 text-muted"></i>{{ $nov->identificacion ?: 'No indicada' }}
                                </span>
                            </div>

                            <div class="col-6 d-flex flex-column">
                                <span class="novedad-field-label">Teléfono / WhatsApp</span>
                                @if ($nov->telefono)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $nov->telefono) }}"
                                        target="_blank" class="novedad-field-value text-decoration-none text-break">
                                        <i class="ti ti-brand-whatsapp me-1 text-muted"></i>{{ $nov->telefono }}
                                    </a>
                                @else
                                    <span class="text-muted small">No indicado</span>
                                @endif
                            </div>

                            <div class="col-6 d-flex flex-column">
                                <span class="novedad-field-label">Sede</span>
                                <span class="novedad-field-value text-break">
                                    <i
                                        class="ti ti-building-church me-1 text-muted"></i>{{ $nov->sede->nombre ?? 'No indicada' }}
                                </span>
                            </div>

                            <div class="col-6 d-flex flex-column">
                                <span class="novedad-field-label">Correo electrónico</span>
                                <span class="novedad-field-value text-break" title="{{ $nov->email }}">
                                    <i class="ti ti-mail me-1 text-muted"></i>{{ $nov->email }}
                                </span>
                            </div>

                            <div class="col-12">
                                <hr class="my-1 text-muted opacity-25">
                            </div>

                            <div class="col-12 d-flex flex-column">
                                <span class="novedad-field-label">Asunto</span>
                                <span class="novedad-field-value mb-2 text-break">{{ $nov->asunto }}</span>
                                <div class="novedad-desc-box">{{ $nov->descripcion }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pie de la Card limpio -->
                    <div
                        class="card-footer border-top bg-transparent px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            @if ($nov->fecha_respuesta)
                                <small class="text-muted">
                                    <i class="ti ti-check me-1"></i>Respondida el
                                    {{ $nov->fecha_respuesta->format('d/m/Y H:i') }}
                                    @if ($nov->respondidoPor)
                                        · {{ $nov->respondidoPor->primer_nombre }}
                                    @endif
                                </small>
                            @else
                                <small class="text-muted">Sin respuesta enviada</small>
                            @endif
                        </div>
                        <button type="button" wire:click="verNovedad({{ $nov->id }})"
                            class="btn btn-sm btn-primary rounded-pill px-4 waves-effect">
                            <i class="ti ti-mail-forward me-1"></i> Ver / Contestar
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center shadow-none border rounded-3">
                    <div class="mb-3">
                        <i class="ti ti-messages-off text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-semibold text-dark">No se encontraron novedades</h5>
                    <p class="text-muted mb-0">No hay registros que coincidan con los filtros seleccionados.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($novedades->hasPages())
        <div class="card shadow-none border rounded-3 mt-4">
            <div class="card-body py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">Mostrando {{ $novedades->firstItem() }} a {{ $novedades->lastItem() }} de
                    {{ $novedades->total() }} registros</small>
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
                        <h5 class="modal-title fw-bold text-dark mb-0">
                            Detalle de la Novedad <span
                                class="text-muted fw-normal">#{{ $novedadSeleccionada?->id }}</span>
                        </h5>
                        <small class="text-muted">
                            Origen:
                            <b>{{ $novedadSeleccionada?->actividad ? $novedadSeleccionada->actividad->nombre : 'Novedad General' }}</b>
                        </small>
                    </div>
                    <button type="button" class="btn-close" wire:click="cerrarModalDetalle"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body pt-3">
                    @if ($novedadSeleccionada)
                        <!-- Información del Usuario y Sede (Borde sutil sobre fondo blanco) -->
                        <div class="row g-3 p-3 border rounded-3 mb-3 bg-white">
                            <div class="col-md-6 col-12">
                                <span class="novedad-field-label d-block">Usuario</span>
                                <span class="novedad-field-value">{{ $novedadSeleccionada->nombre }}</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="novedad-field-label d-block">Cédula / Identificación</span>
                                <span class="novedad-field-value">{{ $novedadSeleccionada->identificacion }}</span>
                            </div>
                            <div class="col-md-4 col-12">
                                <span class="novedad-field-label d-block">Teléfono</span>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $novedadSeleccionada->telefono) }}"
                                    target="_blank" class="novedad-field-value text-decoration-none">
                                    <i
                                        class="ti ti-brand-whatsapp me-1 text-muted"></i>{{ $novedadSeleccionada->telefono }}
                                </a>
                            </div>
                            <div class="col-md-4 col-12">
                                <span class="novedad-field-label d-block">Sede</span>
                                <span
                                    class="novedad-field-value">{{ $novedadSeleccionada->sede->nombre ?? 'No indicada' }}</span>
                            </div>
                            <div class="col-md-4 col-12">
                                <span class="novedad-field-label d-block">Correo electrónico</span>
                                <span class="novedad-field-value text-break">{{ $novedadSeleccionada->email }}</span>
                            </div>

                            @if (
                                $novedadSeleccionada->actividad &&
                                    $novedadSeleccionada->actividad->tipo &&
                                    $novedadSeleccionada->actividad->tipo->tipo_escuelas)
                                <div class="col-12 border-top pt-2 mt-2">
                                    <span class="novedad-field-label d-block">Materia / Escuela deseada</span>
                                    <span class="novedad-field-value">
                                        <i
                                            class="ti ti-school me-1 text-muted"></i>{{ $novedadSeleccionada->materia->nombre ?? ($novedadSeleccionada->materia_nombre ?? 'No especificada') }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Clasificación y Detalle -->
                        <div class="mb-3 p-3 border rounded-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <span class="text-muted small">
                                    Tipo: <strong
                                        class="text-dark">{{ $novedadSeleccionada->tipoNovedad->nombre ?? 'General' }}</strong>
                                </span>
                                <small
                                    class="text-muted">{{ $novedadSeleccionada->created_at ? $novedadSeleccionada->created_at->format('d/m/Y H:i A') : '' }}</small>
                            </div>
                            <h6 class="fw-bold text-dark mb-2 text-break">{{ $novedadSeleccionada->asunto }}</h6>
                            <div class="novedad-desc-box" style="max-height: none;">
                                {{ $novedadSeleccionada->descripcion }}</div>
                        </div>

                        <!-- Historial de Respuesta si ya existe -->
                        @if ($novedadSeleccionada->fecha_respuesta)
                            <div class="p-3 border rounded-3 mb-3 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark small"><i
                                            class="ti ti-check me-1 text-primary"></i>Respuesta enviada
                                        anteriormente</span>
                                    <small
                                        class="text-muted">{{ $novedadSeleccionada->fecha_respuesta->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="small text-secondary mb-2" style="white-space: pre-wrap;">
                                    {{ $novedadSeleccionada->respuesta }}</div>
                                <small class="text-muted">Respondido por:
                                    <b>{{ $novedadSeleccionada->respondidoPor ? $novedadSeleccionada->respondidoPor->primer_nombre . ' ' . $novedadSeleccionada->respondidoPor->primer_apellido : 'Personal Administrativo' }}</b></small>
                            </div>
                        @endif

                        <!-- Formulario de Respuesta -->
                        @if ($puedeGestionar)
                            <div class="border-top pt-3">
                                <h6 class="fw-semibold text-dark mb-1">
                                    Redactar Respuesta al Usuario
                                </h6>
                                <p class="small text-muted mb-3">
                                    Esta respuesta se enviará al correo <b>{{ $novedadSeleccionada->email }}</b>
                                    utilizando la plantilla oficial de la iglesia.
                                </p>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold" for="mensajeRespuesta">Mensaje de
                                        respuesta <span class="text-danger">*</span></label>
                                    <textarea id="mensajeRespuesta" wire:model="mensajeRespuesta" rows="4" class="form-control"
                                        placeholder="Escribe aquí la respuesta formal para el usuario..."></textarea>
                                    @error('mensajeRespuesta')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row align-items-center g-2">
                                    <div class="col-md-6 col-12">
                                        <label class="form-label small fw-semibold" for="nuevoEstado">Nuevo estado de
                                            la novedad</label>
                                        <select id="nuevoEstado" wire:model="nuevoEstado"
                                            class="form-select form-select-sm">
                                            <option value="iniciado">Iniciado (En proceso de revisión)</option>
                                            <option value="finalizado">Finalizado (Resuelto / Completado)</option>
                                            <option value="no_revisado">No revisado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-12 text-end pt-3">
                                        <button type="button" wire:click="enviarRespuesta"
                                            wire:loading.attr="disabled" class="btn btn-primary rounded-pill px-4">
                                            <span wire:loading.remove wire:target="enviarRespuesta"><i
                                                    class="ti ti-send me-1"></i> Enviar respuesta por email</span>
                                            <span wire:loading wire:target="enviarRespuesta"><i
                                                    class="ti ti-loader rotate me-1"></i> Enviando correo...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill"
                        wire:click="cerrarModalDetalle">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Administrar Tipos de Novedad -->
    <div wire:ignore.self class="modal fade" id="modalTiposNovedad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-dark mb-0">
                        Tipos de Novedad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-3">
                    <!-- Formulario Nuevo Tipo -->
                    @if ($puedeGestionar)
                        <div class="p-3 border rounded-3 mb-3 bg-white">
                            <h6 class="fw-semibold mb-2 small text-dark">Agregar nuevo tipo de novedad</h6>
                            <div class="mb-2">
                                <input type="text" wire:model="nuevoTipoNombre"
                                    class="form-control form-control-sm"
                                    placeholder="Nombre (ej: Certificado pendiente)">
                                @error('nuevoTipoNombre')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <input type="text" wire:model="nuevoTipoDescripcion"
                                    class="form-control form-control-sm" placeholder="Descripción breve (opcional)">
                            </div>
                            <button type="button" wire:click="crearTipoNovedad"
                                class="btn btn-sm btn-primary rounded-pill">
                                <i class="ti ti-plus me-1"></i> Agregar opción
                            </button>
                        </div>
                    @endif

                    <!-- Listado de Tipos -->
                    <h6 class="fw-semibold mb-2 text-dark small">Opciones configuradas</h6>
                    <ul class="list-group list-group-flush border rounded-3">
                        @foreach ($tiposNovedad as $t)
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 gap-2">
                                <div class="me-2 text-break" style="word-break: break-word;">
                                    <div
                                        class="fw-semibold small {{ !$t->activo ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                        {{ $t->nombre }}
                                    </div>
                                    @if ($t->descripcion)
                                        <small class="text-muted d-block">{{ $t->descripcion }}</small>
                                    @endif
                                </div>
                                @if ($puedeGestionar)
                                    <button type="button" wire:click="toggleTipoNovedad({{ $t->id }})"
                                        class="btn btn-xs rounded-pill flex-shrink-0 {{ $t->activo ? 'btn-outline-primary' : 'btn-outline-secondary' }}">
                                        {{ $t->activo ? 'Activo' : 'Inactivo' }}
                                    </button>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm"
                        data-bs-dismiss="modal">Cerrar</button>
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
