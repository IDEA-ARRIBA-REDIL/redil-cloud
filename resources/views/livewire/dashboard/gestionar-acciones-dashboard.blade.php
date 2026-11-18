<div>
    <style>
        .acciones-admin-card {
            border-radius: 16px;
            border: 1px solid #eef2f6;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .table-acciones-dashboard thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #525862;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .table-acciones-dashboard tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 0.9rem;
        }

        .offcanvas-acciones-dashboard {
            width: 500px !important;
            border-left: 1px solid #eef2f6;
        }

        .card-offcanvas-box {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.1rem;
        }

        .icono-plantilla-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(78px, 1fr));
            gap: 8px;
            max-height: 220px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
        }

        .icono-plantilla-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 4px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: center;
        }

        .icono-plantilla-item:hover {
            border-color: var(--bs-primary);
            transform: translateY(-2px);
        }

        .icono-plantilla-item.activo {
            border-color: var(--bs-primary);
            background: rgba(var(--bs-primary-rgb), 0.08);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .icono-plantilla-item i {
            font-size: 22px;
            margin-bottom: 3px;
            color: #334155;
        }

        .icono-plantilla-item span {
            font-size: 0.68rem;
            color: #475569;
            line-height: 1.1;
            display: block;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .preview-card-mini {
            width: 96px;
            height: 96px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #eef2f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6px;
        }

        .segmentacion-checklist {
            max-height: 165px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 12px;
            background: #f8fafc;
        }
    </style>

    <!-- Nota informativa sobre el botón automático de Cumpleaños -->
    <div class="alert alert-primary d-flex align-items-start gap-3 rounded-3 mb-4 border-0 shadow-sm">
        <i class="ti ti-cake fs-3 mt-1"></i>
        <div>
            <h6 class="fw-bold mb-1 text-primary">Opción automática de Cumpleaños</h6>
            <p class="mb-0 small text-dark">
                El botón de <strong>Cumpleaños</strong> se muestra siempre <strong>al final</strong> de la fila de forma automática únicamente cuando el usuario cumple dos condiciones en su rol activo:
                <strong>1)</strong> tener asignado el permiso <code>dashboard.ver_cumpleanos</code> y
                <strong>2)</strong> ser encargado de al menos un grupo activo con integrantes (o contar con el permiso global <code>personas.lista_asistentes_todos</code>).
            </p>
        </div>
    </div>

    <div class="card acciones-admin-card p-4">
        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3 mb-4">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-merge shadow-none">
                    <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="Buscar por nombre o enlace..."
                        wire:model.live.debounce.250ms="busqueda">
                    @if (!empty($busqueda))
                        <button class="btn btn-outline-secondary" type="button" wire:click="$set('busqueda', '')"
                            title="Limpiar búsqueda">
                            <i class="ti ti-x"></i>
                        </button>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="button"
                    class="btn btn-primary rounded-pill d-flex align-items-center gap-2 fw-semibold px-4 py-2 shadow-sm"
                    wire:click="abrirModalAccion">
                    <i class="ti ti-plus"></i>
                    <span>Nuevo enlace</span>
                </button>
            </div>
        </div>

        @if ($acciones->isEmpty())
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-apps-off fs-1 text-muted"></i>
                </div>
                <h6 class="text-black fw-semibold">No se encontraron enlaces configurados.</h6>
                <p class="text-muted small">Crea enlaces personalizados para la sección "¿Qué deseas hacer hoy?" del Dashboard.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-acciones-dashboard">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Orden</th>
                            <th style="width: 90px;">Diseño</th>
                            <th>Título</th>
                            <th>Enlace (Link)</th>
                            <th>Visibilidad</th>
                            <th class="text-center">Activo</th>
                            <th class="text-end">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($acciones as $accion)
                            <tr wire:key="fila-accion-{{ $accion->id }}">
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-label-secondary rounded-pill px-2">{{ $accion->orden }}</span>
                                        <div class="d-flex flex-column">
                                            <button type="button" class="btn btn-xs btn-icon btn-text-secondary p-0"
                                                wire:click="subirOrden({{ $accion->id }})" title="Subir posición">
                                                <i class="ti ti-chevron-up fs-6"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-icon btn-text-secondary p-0"
                                                wire:click="bajarOrden({{ $accion->id }})" title="Bajar posición">
                                                <i class="ti ti-chevron-down fs-6"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($accion->tipo_visual === 'icono')
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                                            style="width: 42px; height: 42px; background-color: {{ $accion->icono_color ?: '#2F5D50' }}; color: #ffffff;">
                                            <i class="{{ $accion->icono_completo }} fs-4"></i>
                                        </div>
                                    @else
                                        <div class="rounded-3 d-inline-flex align-items-center justify-content-center bg-light p-1 border"
                                            style="width: 44px; height: 44px;">
                                            <img src="{{ $accion->imagen_url }}" alt="{{ $accion->nombre }}"
                                                style="width: 36px; height: 36px; object-fit: contain;">
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold text-black">{{ $accion->nombre }}</span>
                                    <small class="d-block text-muted">
                                        {{ $accion->tipo_visual === 'icono' ? 'Ícono de plantilla' : 'Imagen PNG' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <code class="text-primary small">{{ $accion->url }}</code>
                                        @if ($accion->abrir_nueva_pestana)
                                            <span class="badge bg-label-info rounded-pill" title="Abre en nueva pestaña">
                                                <i class="ti ti-external-link"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($accion->visible_todos)
                                        <span class="badge bg-label-success rounded-pill">Todos los usuarios</span>
                                    @else
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($accion->tiposUsuarios as $tipo)
                                                <span class="badge bg-label-primary rounded-pill">
                                                    <i class="ti ti-users me-1"></i>{{ $tipo->nombre }}
                                                </span>
                                            @endforeach
                                            @foreach ($accion->roles as $rol)
                                                <span class="badge bg-label-warning rounded-pill">
                                                    <i class="ti ti-shield-check me-1"></i>{{ $rol->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block mb-0">
                                        <input class="form-check-input" type="checkbox"
                                            wire:click="toggleActivo({{ $accion->id }})"
                                            {{ $accion->activo ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-text-success rounded-pill waves-effect me-1"
                                        wire:click="abrirModalAccion({{ $accion->id }})" title="Editar enlace">
                                        <i class="ti ti-edit fs-5"></i>
                                    </button>
                                    <button type="button" class="btn btn-text-danger rounded-pill waves-effect"
                                        onclick="window.confirmarEliminarAccionDashboard({{ $accion->id }})"
                                        title="Eliminar enlace">
                                        <i class="ti ti-trash fs-5"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- ===================================================================== -->
    <!-- OFFCANVAS: CREAR / EDITAR ENLACE DEL DASHBOARD -->
    <!-- ===================================================================== -->
    <div class="offcanvas offcanvas-end offcanvas-acciones-dashboard" tabindex="-1" id="offcanvasAccionDashboard"
        aria-labelledby="offcanvasAccionDashboardLabel" wire:ignore.self>
        <div class="offcanvas-header border-bottom py-3">
            <h5 id="offcanvasAccionDashboardLabel" class="offcanvas-title fw-bold text-primary">
                {{ $accionId ? 'Editar enlace del dashboard' : 'Nuevo enlace del dashboard' }}
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-4">
            <form wire:submit.prevent="guardarAccion">

                <!-- 1. Título y Orden -->
                <div class="row g-3 mb-3">
                    <div class="col-8">
                        <label class="form-label fw-bold text-dark">Título del botón *</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                            wire:model.live="nombre" placeholder="Ej: Academia, Grupos, Donaciones">
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold text-dark">Orden</label>
                        <input type="number" min="0" class="form-control @error('orden') is-invalid @enderror"
                            wire:model="orden">
                        @error('orden')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- 2. Enlace (Link) de redirección -->
                <div class="card-offcanvas-box mb-3">
                    <label class="form-label fw-bold text-dark mb-1">Enlace (Link) de redirección *</label>
                    <input type="text" class="form-control @error('url') is-invalid @enderror mb-2"
                        wire:model="url" placeholder="Ej: /escuelas/dashboard o https://...">
                    @error('url')
                        <span class="invalid-feedback d-block mb-2">{{ $message }}</span>
                    @enderror

                    <div class="mb-2">
                        <small class="text-muted d-block mb-1">Atajos rápidos de la plataforma:</small>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill"
                                wire:click="$set('url', '/escuelas/dashboard')">Academia</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill"
                                wire:click="$set('url', '/grupos')">Grupos</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill"
                                wire:click="$set('url', '/actividades')">Actividades</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill"
                                wire:click="$set('url', '/tiempo-con-dios/historial')">Tiempo con Dios</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill"
                                wire:click="$set('url', '/peticion/nueva')">Nueva petición</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill"
                                wire:click="$set('url', '/donaciones')">Donaciones</button>
                        </div>
                    </div>

                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="switchNuevaPestana"
                            wire:model="abrirNuevaPestana">
                        <label class="form-check-label small text-dark" for="switchNuevaPestana">
                            Abrir enlace en una pestaña nueva
                        </label>
                    </div>
                </div>

                <!-- 3. Diseño Visual: Ícono de Plantilla vs Archivo PNG -->
                <div class="card-offcanvas-box mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <label class="form-label fw-bold text-dark mb-1 d-block">Imagen o Ícono del botón</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" value="icono" id="radioTipoIcono"
                                        wire:model.live="tipoVisual">
                                    <label class="form-check-label fw-semibold text-dark" for="radioTipoIcono">
                                        Ícono de plantilla
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" value="imagen" id="radioTipoImagen"
                                        wire:model.live="tipoVisual">
                                    <label class="form-check-label fw-semibold text-dark" for="radioTipoImagen">
                                        Subir archivo PNG
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Vista previa en vivo de la tarjeta -->
                        <div class="preview-card-mini flex-shrink-0" title="Vista previa">
                            <div class="d-flex align-items-center justify-content-center mb-1"
                                style="width: 45px; height: 45px;">
                                @if ($tipoVisual === 'icono')
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm"
                                        style="width: 42px; height: 42px; background-color: {{ $iconoColor ?: '#2F5D50' }};">
                                        <i class="{{ $iconoClase ?: 'ti ti-apps' }} fs-4"></i>
                                    </div>
                                @else
                                    @if ($archivoPng)
                                        <img src="{{ $archivoPng->temporaryUrl() }}" alt="Preview"
                                            style="width: 42px; height: 42px; object-fit: contain;">
                                    @elseif ($imagenExistenteUrl)
                                        <img src="{{ $imagenExistenteUrl }}" alt="Preview"
                                            style="width: 42px; height: 42px; object-fit: contain;">
                                    @else
                                        <i class="ti ti-photo text-muted fs-2"></i>
                                    @endif
                                @endif
                            </div>
                            <span class="fw-semibold text-dark text-truncate w-100 text-center"
                                style="font-size: 0.75rem;">
                                {{ $nombre ?: 'Título' }}
                            </span>
                        </div>
                    </div>

                    @if ($tipoVisual === 'icono')
                        <!-- Selector de Color + Clase de Ícono -->
                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="form-label small text-black mb-1">Clase del ícono (Tabler)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">
                                        <i class="{{ $iconoClase ?: 'ti ti-apps' }}"></i>
                                    </span>
                                    <input type="text"
                                        class="form-control @error('iconoClase') is-invalid @enderror"
                                        wire:model.live="iconoClase" placeholder="ti ti-school">
                                </div>
                                @error('iconoClase')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-5">
                                <label class="form-label small text-black mb-1">Color de fondo</label>
                                <div class="d-flex align-items-center gap-1">
                                    <input type="color" class="form-control form-control-color form-control-sm"
                                        wire:model.live="iconoColor" style="width: 38px; height: 31px;">
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model.live="iconoColor" placeholder="#2F5D50">
                                </div>
                            </div>
                        </div>

                        <!-- Buscador y catálogo visual de íconos de la plantilla -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-dark mb-0">
                                    Elige un ícono de la plantilla:
                                </label>
                                <input type="text" class="form-control form-control-sm w-50"
                                    placeholder="Filtrar íconos..." wire:model.live.debounce.200ms="busquedaIcono">
                            </div>

                            <div class="icono-plantilla-grid">
                                @foreach ($catalogoIconos as $itemIcono)
                                    <button type="button"
                                        class="icono-plantilla-item {{ $iconoClase === $itemIcono['clase'] ? 'activo' : '' }}"
                                        wire:click="seleccionarIconoPlantilla('{{ $itemIcono['clase'] }}')">
                                        <i class="{{ $itemIcono['clase'] }}"></i>
                                        <span>{{ $itemIcono['etiqueta'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <!-- Modo Subir Archivo PNG -->
                        <div>
                            <label class="form-label small fw-semibold text-dark mb-1">
                                Subir archivo PNG (.png) *
                            </label>
                            <input type="file"
                                class="form-control @error('archivoPng') is-invalid @enderror"
                                wire:model="archivoPng" accept="image/png">
                            <div wire:loading wire:target="archivoPng" class="text-primary small mt-1">
                                <i class="ti ti-loader rotate me-1"></i> Cargando vista previa del archivo PNG...
                            </div>
                            <small class="text-muted d-block mt-1">
                                Sube una imagen en formato <strong>PNG</strong> (recomendado 100×100 px con fondo transparente, máx. 2 MB).
                            </small>
                            @error('archivoPng')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </div>

                <!-- 4. Configuración de Visibilidad por Tipo de Usuario y Rol -->
                <div class="card-offcanvas-box mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-bold text-dark mb-0">
                            <i class="ti ti-user-check text-primary me-1"></i> ¿Qué usuarios pueden ver este elemento?
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="switchVisibleTodos"
                            wire:model.live="visibleTodos">
                        <label class="form-check-label fw-semibold text-dark" for="switchVisibleTodos">
                            Visible para todos los usuarios
                        </label>
                    </div>

                    @if (!$visibleTodos)
                        @error('segmentacion')
                            <div class="alert alert-danger py-2 px-3 small mb-3">{{ $message }}</div>
                        @enderror

                        <!-- Selección de Tipos de Usuario -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark mb-1">
                                Tipos de Usuario permitidos ({{ count($tiposUsuariosSeleccionados) }} seleccionados)
                            </label>
                            <div class="segmentacion-checklist">
                                @foreach ($tiposUsuarios as $tipo)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox"
                                            value="{{ $tipo->id }}"
                                            id="chk-tipo-usr-{{ $tipo->id }}"
                                            wire:model.live="tiposUsuariosSeleccionados">
                                        <label class="form-check-label small text-dark"
                                            for="chk-tipo-usr-{{ $tipo->id }}">
                                            {{ $tipo->nombre }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Selección de Roles de Plataforma -->
                        <div>
                            <label class="form-label small fw-bold text-dark mb-1">
                                Roles de plataforma permitidos ({{ count($rolesSeleccionados) }} seleccionados)
                            </label>
                            <div class="segmentacion-checklist">
                                @foreach ($roles as $rol)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox"
                                            value="{{ $rol->id }}"
                                            id="chk-rol-dash-{{ $rol->id }}"
                                            wire:model.live="rolesSeleccionados">
                                        <label class="form-check-label small text-dark"
                                            for="chk-rol-dash-{{ $rol->id }}">
                                            {{ $rol->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-1">
                                El enlace se mostrará si el usuario pertenece a alguno de los Tipos de Usuario o tiene activo alguno de los Roles marcados.
                            </small>
                        </div>
                    @endif
                </div>

                <!-- Estado Activo -->
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="switchActivoAccion" wire:model="activo">
                    <label class="form-check-label fw-semibold text-dark" for="switchActivoAccion">
                        Enlace habilitado y activo en el Dashboard
                    </label>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="offcanvas">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
                        <span wire:loading.remove>Guardar</span>
                        <span wire:loading><i class="ti ti-loader rotate"></i> Guardando...</span>
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        (function() {
            function abrirOffcanvasAccion() {
                const el = document.getElementById('offcanvasAccionDashboard');
                if (!el || typeof bootstrap === 'undefined') return;
                const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(el);
                bsOffcanvas.show();
            }

            function cerrarOffcanvasAccion() {
                const el = document.getElementById('offcanvasAccionDashboard');
                if (!el || typeof bootstrap === 'undefined') return;
                const bsOffcanvas = bootstrap.Offcanvas.getInstance(el);
                if (bsOffcanvas) {
                    bsOffcanvas.hide();
                }
            }

            function registrarListenersAccionesDashboard() {
                if (window.__accionesDashboardListenersRegistrados || typeof Livewire === 'undefined') {
                    return;
                }
                window.__accionesDashboardListenersRegistrados = true;

                Livewire.on('abrirOffcanvasAccionDashboard', () => {
                    abrirOffcanvasAccion();
                });

                Livewire.on('cerrarOffcanvasAccionDashboard', () => {
                    cerrarOffcanvasAccion();
                });

                Livewire.on('msn', (data) => {
                    const payload = Array.isArray(data) ? data[0] : data;
                    Swal.fire({
                        icon: payload.icono || 'success',
                        title: payload.titulo || '¡Completado!',
                        text: payload.texto || '',
                        confirmButtonText: 'Aceptar',
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-pill px-4'
                        },
                        buttonsStyling: false
                    });
                });
            }

            document.addEventListener('livewire:init', registrarListenersAccionesDashboard);
            document.addEventListener('DOMContentLoaded', registrarListenersAccionesDashboard);
        })();

        window.confirmarEliminarAccionDashboard = function(id) {
            Swal.fire({
                title: '¿Eliminar este enlace?',
                text: 'Esta opción dejará de mostrarse en el dashboard de los usuarios.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger rounded-pill px-4 me-2',
                    cancelButton: 'btn btn-outline-secondary rounded-pill px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarAccion', id);
                }
            });
        };
    </script>
</div>
