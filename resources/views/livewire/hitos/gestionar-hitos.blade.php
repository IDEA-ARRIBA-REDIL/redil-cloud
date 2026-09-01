<div>
    {{-- Encabezado y Acciones Principales idéntico a Posts --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <h4 class="mb-0 fw-semibold text-black">Gestionar hitos</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('hitos.denuncias') }}" class="btn btn-outline-warning rounded-pill px-3 py-2">
                <i class="ti ti-flag me-1"></i> Moderación de Reportes
            </a>
            <a href="{{ route('hitos.crear') }}" class="btn btn-primary rounded-pill px-12 py-2">
                <i class="ti ti-plus me-1"></i> Nuevo
            </a>
        </div>
    </div>

    {{-- Filtros (Búsqueda insensible a mayúsculas/minúsculas, rango de fechas y filtros por tipo/estado) --}}
    <div class="row g-3 align-items-center mb-4">
        {{-- Búsqueda por texto --}}
        <div class="col-12 col-md-3">
            <label class="form-label text-black fw-semibold text-uppercase" style="font-size: 0.75rem;">Buscar por coincidencia</label>
            <div class="input-group">
                <span class="input-group-text"><i class="ti ti-search"></i></span>
                <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Título, descripción...">
                @if($search)
                    <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')">
                        <i class="ti ti-x"></i>
                    </button>
                @endif
            </div>
        </div>

        {{-- Filtro por Rango de Fecha (Flatpickr) --}}
        <div class="col-12 col-md-3" wire:ignore x-data="{
            init() {
                const fp = flatpickr(this.$refs.picker, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M, Y',
                    onClose: (selectedDates, dateStr, instance) => {
                        if (selectedDates.length === 2) {
                            const start = instance.formatDate(selectedDates[0], 'Y-m-d');
                            const end = instance.formatDate(selectedDates[1], 'Y-m-d');
                            @this.set('fecha_inicio', start);
                            @this.set('fecha_fin', end);
                        } else if (selectedDates.length === 0) {
                            @this.set('fecha_inicio', '');
                            @this.set('fecha_fin', '');
                        }
                    }
                });

                window.addEventListener('limpiarFlatpickr', () => {
                    fp.clear();
                });
            }
        }">
            <label class="form-label text-black fw-semibold text-uppercase" style="font-size: 0.75rem;">Filtrar por rango de fecha</label>
            <div class="input-group">
                <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                <input type="text" x-ref="picker" class="form-control" placeholder="Seleccionar rango" readonly>
            </div>
        </div>

        {{-- Filtro por Tipo de Hito --}}
        <div class="col-12 col-md-3">
            <label class="form-label text-black fw-semibold text-uppercase" style="font-size: 0.75rem;">Filtrar por tipo de hito</label>
            <select class="form-select" wire:model.live="tipoFiltro">
                <option value="">Todos los tipos</option>
                @foreach($tiposHito as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filtro por Estado --}}
        <div class="col-12 col-md-2">
            <label class="form-label text-black fw-semibold text-uppercase" style="font-size: 0.75rem;">Estado</label>
            <select class="form-select" wire:model.live="estadoFiltro">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>

        {{-- Botón para resetear todos los filtros si alguno está activo --}}
        <div class="col-12 col-md-1 d-flex align-items-end">
            @if($search || $tipoFiltro || $estadoFiltro !== '' || $fecha_inicio || $fecha_fin)
                <button type="button" class="btn btn-outline-danger w-100 p-2" wire:click="limpiarFiltros" title="Limpiar todos los filtros">
                    <i class="ti ti-filter-off"></i>
                </button>
            @endif
        </div>
    </div>

    {{-- Listado de Cards con diseño idéntico a Posts (Orden: Más reciente a más antigua) --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 g-4 mb-4">
        @forelse($hitos as $hito)
            <div class="col" wire:key="hito-card-{{ $hito->id }}">
                <div class="card h-100 shadow-sm border-0 overflow-hidden position-relative {{ $hito->activo ? '' : 'opacity-75' }}" style="border-radius: 15px;">

                    {{-- Imagen Portada Cuadrada 1:1 --}}
                    <div class="card-img-top position-relative overflow-hidden" style="width: 100%; height: 0; padding-bottom: 100%; background-color: #f8f9fa;">
                        @if($hito->portada_path)
                            <img src="{{ $hito->portada_url }}"
                                 alt="{{ $hito->titulo }}"
                                 class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; object-position: center;">
                        @else
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted">
                                <i class="ti ti-photo-off" style="font-size: 3rem;"></i>
                            </div>
                        @endif

                        {{-- Badge Tipo de Hito en la esquina superior izquierda --}}
                        <span class="badge position-absolute top-0 start-0 m-3 px-2 py-1 shadow-sm d-flex align-items-center gap-1 zindex-2"
                              style="background-color: {{ $hito->tipoHito->color ?? '#7c5cfc' }}; color: #fff !important; font-size: 0.75rem;">
                            <i class="{{ $hito->tipoHito->icono ?? 'ti ti-award' }}"></i>
                            {{ $hito->tipoHito->nombre ?? 'Hito' }}
                        </span>

                        {{-- Switch de Activo/Inactivo en la esquina superior derecha --}}
                        <div class="position-absolute top-0 end-0 m-3 zindex-2 bg-white bg-opacity-75 rounded-pill px-2 py-1 shadow-sm">
                            <div class="form-check form-switch form-switch-success mb-0 d-flex align-items-center">
                                <input class="form-check-input mt-0" type="checkbox" role="switch"
                                       wire:click="toggleActivo({{ $hito->id }})"
                                       {{ $hito->activo ? 'checked' : '' }} title="{{ $hito->activo ? 'Hito Activo' : 'Hito Inactivo' }}">
                            </div>
                        </div>
                    </div>

                    {{-- Contenido de la Card --}}
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            {{-- Fila de Cabecera con Título y Menú de Acciones (Grid Bootstrap que nunca se desborda) --}}
                            <div class="row g-2 align-items-start mb-2">
                                <div class="col" style="min-width: 0;">
                                    <h6 class="fw-bold mb-0 text-black text-truncate" title="{{ $hito->titulo }}" style="font-size: 0.95rem;">
                                        {{ $hito->titulo }}
                                    </h6>
                                </div>
                                <div class="col-auto">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @if($hito->portada_path)
                                                <a class="dropdown-item cursor-pointer" href="{{ $hito->portada_url }}" target="_blank" download>
                                                    <i class="ti ti-download me-1"></i> Descargar portada
                                                </a>
                                            @endif

                                            <a class="dropdown-item" href="{{ route('hitos.editar', $hito->id) }}">
                                                <i class="ti ti-pencil me-1"></i> Editar
                                            </a>

                                            @if($hito->esDeActividad() && $hito->requiere_asistencia)
                                                <a class="dropdown-item" href="{{ route('hitos.asistencia', $hito->id) }}">
                                                    <i class="ti ti-user-check me-1"></i> Control de Asistencias
                                                </a>
                                            @endif

                                            @if($hito->esAutomatico())
                                                <a class="dropdown-item cursor-pointer" href="javascript:void(0)" wire:click="migrarRetroactivo({{ $hito->id }})">
                                                    <i class="ti ti-history me-1"></i> Migrar Retroactivo
                                                </a>
                                            @endif

                                            <li><hr class="dropdown-divider"></li>

                                            <a class="dropdown-item text-danger cursor-pointer" href="javascript:void(0)"
                                               wire:click="eliminarHito({{ $hito->id }})"
                                               onclick="confirm('¿Estás seguro de eliminar este hito y sus registros asociados?') || event.stopImmediatePropagation();">
                                                <i class="ti ti-trash me-1"></i> Eliminar
                                            </a>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Descripción con truncado y Ver más --}}
                            <div class="description-container mb-2">
                                <div class="card-text mb-1 text-black" style="font-size: 0.85rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.8em;">
                                    {{ strip_tags($hito->descripcion) ?: 'Sin descripción detallada' }}
                                </div>
                                @if(strlen(strip_tags($hito->descripcion)) > 60)
                                    <a href="javascript:void(0);" class="fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#descModalHito{{ $hito->id }}" style="font-size: 0.85rem;"> Ver más</a>
                                @endif
                            </div>

                            {{-- Modal para descripción completa --}}
                            <div class="modal fade" id="descModalHito{{ $hito->id }}" tabindex="-1" aria-hidden="true" wire:ignore.self>
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $hito->titulo }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h6 class="fw-bold text-primary mb-2"><i class="ti ti-info-circle me-1"></i> Descripción del Hito:</h6>
                                            <div class="mb-3 text-black">
                                                {!! nl2br(e($hito->descripcion)) ?: '<em>Sin descripción</em>' !!}
                                            </div>

                                            @if($hito->mensaje_usuario)
                                                <h6 class="fw-bold text-success mb-2"><i class="ti ti-message-2 me-1"></i> Mensaje para el Usuario:</h6>
                                                <div class="p-3 bg-lighter rounded border text-black small">
                                                    "{{ $hito->mensaje_usuario }}"
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Autor y Fecha --}}
                            <div class="d-flex flex-column text-muted small mb-2">
                                <span class="text-black mb-1">
                                    <i class="ti ti-user me-1 text-secondary"></i> {{ $hito->autor ? $hito->autor->nombre(3) : 'Administración' }}
                                </span>
                                <span class="text-black">
                                    <i class="ti ti-calendar me-1 text-secondary"></i>
                                    {{ $hito->fecha_evento ? $hito->fecha_evento->format('d M, Y') : 'Fecha no definida' }}
                                </span>
                            </div>

                            {{-- Badges adicionales de activación --}}
                            <div class="mb-2 d-flex flex-wrap gap-1">
                                @if($hito->esAutomatico())
                                    <span class="badge bg-label-info" style="font-size: 0.7rem;">
                                        <i class="ti ti-bolt me-1"></i> {{ ucfirst(str_replace('_', ' ', $hito->trigger_modulo)) }}
                                    </span>
                                @elseif($hito->esDeActividad())
                                    <span class="badge bg-label-primary" style="font-size: 0.7rem;">
                                        <i class="ti ti-ticket me-1"></i> {{ $hito->actividad->nombre ?? 'Actividad' }}
                                    </span>
                                @elseif($hito->esManual())
                                    <span class="badge bg-label-warning" style="font-size: 0.7rem;">
                                        <i class="ti ti-award me-1"></i> Reconocimiento
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Footer de Interacción (Likes y Fotos) --}}
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-2">
                            <div class="d-flex align-items-center gap-1 text-black small">
                                <i class="ti ti-photo text-info"></i>
                                <span>{{ $hito->fotos_count }} fotos</span>
                            </div>
                            <div class="d-flex align-items-center gap-1 text-black">
                                <i class="ti ti-heart ti-sm @if($hito->likes_count > 0) text-danger @endif"></i>
                                <span style="font-size: 0.8rem;">{{ $hito->likes_count >= 1000 ? round($hito->likes_count / 1000, 1) . 'K' : $hito->likes_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 w-100 mt-5">
                <div class="text-center py-5">
                    <i class="ti ti-album-off ti-lg text-black mb-3 d-block" style="font-size: 4rem;"></i>
                    <h5 class="text-black">No se encontraron hitos.</h5>
                    <a href="{{ route('hitos.crear') }}" class="btn btn-primary mt-3 rounded-pill px-4">
                        <i class="ti ti-plus me-1"></i> Crear mi primer hito
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginación centrada --}}
    <div class="d-flex justify-content-center mt-5">
        {{ $hitos->links() }}
    </div>

    @script
    <script>
        $wire.on('msn', (data) => {
            let info = Array.isArray(data) ? data[0] : data;
            let icono = info?.msnIcono || info?.tipo || 'info';
            let titulo = info?.msnTitulo || (icono === 'success' ? '¡Éxito!' : 'Notificación');
            let texto = info?.msnTexto || info?.mensaje || '';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icono,
                    title: titulo,
                    html: texto,
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            } else {
                alert(titulo + ': ' + texto.replace(/<[^>]*>?/gm, ''));
            }
        });
    </script>
    @endscript
</div>
