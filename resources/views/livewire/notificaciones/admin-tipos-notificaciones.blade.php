<div wire:init="loadData">

    {{-- Alertas de éxito --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible border-0 fade show mb-4" role="alert">
            <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        {{-- Cabecera --}}
        <div class="card-header bg-white border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 py-3">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="ti ti-bell-cog me-2 text-primary"></i>Tipos de Notificaciones
                </h5>
                <small class="text-muted">Gestiona los eventos y su alcance de envío.</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="ti ti-search text-muted"></i>
                    </span>
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           class="form-control border-start-0 ps-0"
                           placeholder="Buscar..."
                           style="min-width: 180px;">
                </div>
                <button wire:click="abrirModalCrear"
                        class="btn btn-primary btn-sm rounded-pill px-3 text-nowrap">
                    <i class="ti ti-plus me-1"></i>Nuevo
                </button>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;">Módulo</th>
                            <th class="text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;">Título / Slug</th>
                            <th class="d-none d-md-table-cell text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;">Alcance</th>
                            <th class="text-center text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;">Activo</th>
                            <th class="text-center text-uppercase small fw-semibold text-muted" style="font-size:0.7rem;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$readyToLoad)
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        @elseif($tipos->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="ti ti-bell-off ti-xl d-block mb-2"></i>
                                    No se encontraron tipos de notificaciones.
                                </td>
                            </tr>
                        @else
                            @php $lastModulo = null; @endphp
                            @foreach ($tipos as $tipo)
                                @if ($lastModulo !== $tipo->modulo)
                                    <tr class="table-light">
                                        <td colspan="5" class="fw-bold ps-4 text-primary small text-uppercase" style="font-size:0.7rem;letter-spacing:0.08em;">
                                            <i class="ti ti-folder me-1"></i>{{ $tipo->modulo }}
                                        </td>
                                    </tr>
                                    @php $lastModulo = $tipo->modulo; @endphp
                                @endif
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-label-primary">{{ $tipo->modulo }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark" style="font-size:0.9rem;">{{ $tipo->titulo }}</span>
                                            @if($tipo->descripcion)
                                                <span class="text-muted small">{{ $tipo->descripcion }}</span>
                                            @endif
                                            <code class="text-muted" style="font-size:0.7rem;">{{ $tipo->slug }}</code>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        @php
                                            $alcanceConfig = [
                                                'global'             => ['label' => 'Global',             'color' => 'bg-label-success'],
                                                'individual'         => ['label' => 'Individual',         'color' => 'bg-label-info'],
                                                'escala_ministerial' => ['label' => 'Escala Min.',        'color' => 'bg-label-warning'],
                                                'ministerio_directo' => ['label' => 'Min. Directo',       'color' => 'bg-label-secondary'],
                                            ];
                                            $alcances = is_array($tipo->alcance) ? $tipo->alcance : [$tipo->alcance];
                                        @endphp
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($alcances as $a)
                                                @php $cfg = $alcanceConfig[$a] ?? ['label' => $a, 'color' => 'bg-label-secondary']; @endphp
                                                <span class="badge {{ $cfg['color'] }}">{{ $cfg['label'] }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   wire:click="toggleActivo({{ $tipo->id }})"
                                                   @checked($tipo->activo)
                                                   style="cursor:pointer;width:2.5rem;height:1.25rem;">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button wire:click="abrirModalEditar({{ $tipo->id }})"
                                                    class="btn btn-sm btn-icon btn-label-primary rounded-circle"
                                                    title="Editar">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <button wire:click="eliminar({{ $tipo->id }})"
                                                    wire:confirm="¿Eliminar este tipo de notificación? Esta acción no se puede deshacer."
                                                    class="btn btn-sm btn-icon btn-label-danger rounded-circle"
                                                    title="Eliminar">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        @if ($readyToLoad && $tipos->hasPages())
            <div class="card-footer bg-white border-top d-flex justify-content-center py-3">
                {{ $tipos->links() }}
            </div>
        @endif
    </div>

    {{-- ===== MODAL CREAR / EDITAR ===== --}}
    <div wire:ignore.self
         class="modal fade"
         id="modalTipoNotificacion"
         tabindex="-1"
         aria-labelledby="modalTipoNotificacionLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header border-bottom-0 pb-1">
                    <h5 class="modal-title fw-bold" id="modalTipoNotificacionLabel">
                        <i class="ti ti-bell me-2 text-primary"></i>
                        {{ $editandoId ? 'Editar' : 'Nuevo' }} Tipo de Notificación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-2">
                    <div class="row g-3">

                        {{-- Módulo --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Módulo <span class="text-danger">*</span></label>
                            <select wire:model="modulo"
                                    class="form-select @error('modulo') is-invalid @enderror">
                                <option value="">-- Selecciona un módulo --</option>
                                <option value="Membresía">Membresía</option>
                                <option value="Grupos">Grupos</option>
                                <option value="Reporte de Grupos">Reporte de Grupos</option>
                                <option value="Novedades">Novedades</option>
                                <option value="Versículo Diario">Versículo Diario</option>
                                <option value="Publicaciones">Publicaciones</option>
                                <option value="Escuelas">Escuelas</option>
                                <option value="Actividades">Actividades</option>
                            </select>
                            @error('modulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Slug --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Slug <span class="text-danger">*</span></label>
                            <input wire:model="slug"
                                   type="text"
                                   class="form-control font-monospace @error('slug') is-invalid @enderror"
                                   placeholder="Ej: grupo_reporte_creado">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Identificador único en minúsculas y guiones bajos.</small>
                        </div>

                        {{-- Título --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Título <span class="text-danger">*</span></label>
                            <input wire:model="titulo"
                                   type="text"
                                   class="form-control @error('titulo') is-invalid @enderror"
                                   placeholder="Ej: Reporte de Grupo Enviado">
                            @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Descripción</label>
                            <textarea wire:model="descripcion"
                                      class="form-control @error('descripcion') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Describe cuándo se dispara esta notificación..."></textarea>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Alcance (múltiple) --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small d-block">
                                ¿A quién notificar? <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-column gap-2 p-3 border rounded-3 bg-light">
                                @foreach(\App\Models\TipoNotificacion::alcancesDisponibles() as $valor => $etiqueta)
                                    <div class="form-check mb-0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="alcance_{{ $valor }}"
                                               wire:model="alcance"
                                               value="{{ $valor }}">
                                        <label class="form-check-label small" for="alcance_{{ $valor }}">
                                            {{ $etiqueta }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('alcance')    <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @error('alcance.*')  <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- Días de Vigencia --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Días de Vigencia
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <div class="input-group">
                                <input wire:model="diasVigencia"
                                       type="number"
                                       min="1"
                                       max="3650"
                                       class="form-control @error('diasVigencia') is-invalid @enderror"
                                       placeholder="Ej: 7">
                                <span class="input-group-text">días</span>
                                @error('diasVigencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <small class="text-muted">
                                La notificación desaparecerá automáticamente después de este tiempo.
                                Déjalo vacío si no debe caducar.
                            </small>
                        </div>

                        {{-- Sedes --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small d-block">
                                ¿A qué sedes aplica?
                                <span class="text-muted fw-normal">(opcional — vacío = todas)</span>
                            </label>
                            <div class="d-flex flex-column gap-2 p-3 border rounded-3 bg-light" style="max-height:160px;overflow-y:auto;">
                                @forelse($sedes as $sede)
                                    <div class="form-check mb-0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="sede_{{ $sede->id }}"
                                               wire:model="sedesIds"
                                               value="{{ $sede->id }}">
                                        <label class="form-check-label small" for="sede_{{ $sede->id }}">
                                            {{ $sede->nombre }}
                                        </label>
                                    </div>
                                @empty
                                    <small class="text-muted">No hay sedes registradas.</small>
                                @endforelse
                            </div>
                            @if(empty($sedesIds))
                                <small class="text-success"><i class="ti ti-check me-1"></i>Aplica a todas las sedes.</small>
                            @endif
                            @error('sedesIds.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- Tipos de usuario --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small d-block">
                                ¿A qué tipos de usuario aplica?
                                <span class="text-muted fw-normal">(opcional — vacío = todos)</span>
                            </label>
                            <div class="d-flex flex-column gap-2 p-3 border rounded-3 bg-light" style="max-height:160px;overflow-y:auto;">
                                @forelse($tiposUsuario as $tipoU)
                                    <div class="form-check mb-0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="tipou_{{ $tipoU->id }}"
                                               wire:model="tiposUsuarioIds"
                                               value="{{ $tipoU->id }}">
                                        <label class="form-check-label small" for="tipou_{{ $tipoU->id }}">
                                            {{ $tipoU->nombre_plural ?: $tipoU->nombre }}
                                        </label>
                                    </div>
                                @empty
                                    <small class="text-muted">No hay tipos de usuario registrados.</small>
                                @endforelse
                            </div>
                            @if(empty($tiposUsuarioIds))
                                <small class="text-success"><i class="ti ti-check me-1"></i>Aplica a todos los tipos de usuario.</small>
                            @endif
                            @error('tiposUsuarioIds.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- Activo --}}
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input wire:model="activo"
                                       class="form-check-input"
                                       type="checkbox"
                                       id="switchActivo"
                                       style="width:2.5rem;height:1.25rem;cursor:pointer;">
                                <label class="form-check-label fw-semibold small ms-1" for="switchActivo">
                                    Activo
                                </label>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="modal-footer border-top-0 pt-0">
                    <button type="button"
                            class="btn btn-outline-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                            wire:loading.attr="disabled"
                            class="btn btn-primary rounded-pill px-4">
                        <span wire:loading wire:target="guardar"
                              class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ $editandoId ? 'Actualizar' : 'Crear' }}
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>
