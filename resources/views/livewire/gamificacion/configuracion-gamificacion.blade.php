<div>
  <style>
    .gamificacion-admin-card {
      border-radius: 16px;
      border: 1px solid #eef2f6;
      background: #ffffff;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    }

    .table-gamificacion {
      margin-bottom: 0;
    }

    .table-gamificacion thead th {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #525862;
      font-weight: 700;
      border-bottom: 1px solid #e2e8f0;
      padding-top: 1rem;
      padding-bottom: 1rem;
      background-color: transparent;
    }

    .table-gamificacion tbody td {
      padding: 1.15rem 0.75rem;
      vertical-align: middle;
      border-bottom: 1px solid #f1f5f9;
      font-size: 0.9rem;
    }
  
    .badge-puntos-primary {
      background-color: rgba(var(--bs-primary-rgb), 0.12);
      color: var(--bs-primary);
      font-weight: 700;
      border-radius: 50rem;
      padding: 0.35rem 0.9rem;
      font-size: 0.85rem;
      display: inline-block;
    }

    .btn-action-icon {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      border: none;
      background: transparent;
      transition: all 0.2s;
    }


    .offcanvas-gamificacion {
      width: 440px !important;
      border-left: 1px solid #eef2f6;
    }

    .card-offcanvas-section {
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 1.15rem;
    }

    .form-check-input:checked {
      background-color: var(--bs-primary);
      border-color: var(--bs-primary);
    }
  </style>

  <!-- Tabs de Navegación Estilo Dashboard Grupos -->
  <div class="card mb-4 p-1 border-1">
    <ul class="nav nav-pills justify-content-start flex-column flex-md-row gap-2" role="tablist">
      <li class="nav-item flex-fill">
        <button type="button" class="nav-link p-3 waves-effect waves-light w-100 fw-semibold {{ $tabActivo === 'reglas' ? 'active' : '' }}" wire:click="cambiarTab('reglas')">
          Reglas
        </button>
      </li>
      <li class="nav-item flex-fill">
        <button type="button" class="nav-link p-3 waves-effect waves-light w-100 fw-semibold {{ $tabActivo === 'insignias' ? 'active' : '' }}" wire:click="cambiarTab('insignias')">
          Catálogo de insignias
        </button>
      </li>
    </ul>
  </div>

  <!-- Tarjeta Principal de Configuración -->
  <div class="card gamificacion-admin-card p-4">

    <!-- ===================================================================== -->
    <!-- TAB 1: REGLAS ACTIVAS -->
    <!-- ===================================================================== -->
    @if($tabActivo === 'reglas')
      <div>
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="fw-semibold text-black mb-0">Reglas activas</h5>
          <button class="btn btn-primary rounded-pill d-flex align-items-center gap-2 fw-semibold px-3 py-2 shadow-sm" wire:click="abrirModalRegla">
            <i class="ti ti-plus"></i>
            <span>Nueva regla</span>
          </button>
        </div>

        <!-- Filtros por Select para Reglas -->
        <div class="row g-3 mb-4 align-items-center">
          <!-- Filtro por Acción -->
          <div class="col-12 col-md-3">
            <select class="form-select" wire:model.live="filtroAccion">
              <option value="">Todas las acciones</option>
              @foreach($triggers as $codigo => $label)
                <option value="{{ $codigo }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <!-- Filtro por Frecuencia -->
          <div class="col-12 col-md-3">
            <select class="form-select" wire:model.live="filtroFrecuencia">
              <option value="">Todas las frecuencias</option>
              <option value="cada_vez">Cada vez</option>
              <option value="meta">Al alcanzar una meta</option>
              <option value="unica_vez">Única vez</option>
            </select>
          </div>

          <!-- Filtro por Insignia -->
          <div class="col-12 col-md-3">
            <select class="form-select" wire:model.live="filtroInsignia">
              <option value="">Todas las insignias</option>
              <option value="con_insignia">Con cualquier insignia</option>
              <option value="sin_insignia">Sin insignia</option>
              @if(isset($todasInsignias) && $todasInsignias->isNotEmpty())
                <optgroup label="Insignias específicas">
                  @foreach($todasInsignias as $ins)
                    <option value="{{ $ins->id }}">{{ $ins->nombre }}</option>
                  @endforeach
                </optgroup>
              @endif
            </select>
          </div>

          <!-- Botón Limpiar Filtros -->
          <div class="col-12 col-md-3">
            @if(!empty($filtroAccion) || !empty($filtroFrecuencia) || !empty($filtroInsignia))
              <button class="btn btn-outline-secondary rounded-pill w-100 d-flex align-items-center justify-content-center gap-1" wire:click="limpiarFiltrosReglas" title="Limpiar filtros">
                <i class="ti ti-rotate-clockwise fs-6"></i>
                <span>Limpiar</span>
              </button>
            @endif
          </div>
        </div>

        @if($reglas->isEmpty())
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="ti ti-list-check fs-1 text-black"></i>
            </div>
            @if(!empty($filtroAccion) || !empty($filtroFrecuencia) || !empty($filtroInsignia))
              <h6 class="text-black fw-semibold">No se encontraron reglas con los filtros seleccionados.</h6>
              <p class="text-muted small">Intenta cambiando los criterios de búsqueda.</p>
              <button class="btn btn-outline-primary btn-sm rounded-pill mt-2" wire:click="limpiarFiltrosReglas">
                <i class="ti ti-rotate-clockwise me-1"></i> Limpiar filtros
              </button>
            @else
              <h6 class="text-black fw-semibold">No hay reglas de asignación configuradas.</h6>
              <p class="text-black small">Crea reglas para definir qué acciones premian con puntos o insignias a los usuarios.</p>
            @endif
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-gamificacion">
              <thead>
                <tr>
                  <th>Acción (Trigger)</th>
                  <th>Nombre</th>
                  <th>Frecuencia</th>
                  <th class="text-center">Puntos otorgados</th>
                  <th>Insignia otorgada</th>
                  <th class="text-end">Opciones</th>
                </tr>
              </thead>
              <tbody>
                @foreach($reglas as $regla)
                  @php
                    $nombreAccion = $triggers[$regla->accion_codigo] ?? $regla->accion_codigo;
                  @endphp
                  <tr>
                    <td>
                      <span class="fw-semibold text-black">{{ $nombreAccion }}</span>
                    </td>
                    <td>
                      <span class="text-black">{{ $regla->nombre }}</span>
                    </td>
                    <td>
                      @if($regla->frecuencia === 'unica_vez')
                      <span class="text-black">Única vez</span>
                      @elseif($regla->frecuencia === 'cada_vez')
                      <span class="text-black">Cada vez</span>
                      @elseif($regla->frecuencia === 'meta')
                      <span class="text-black">Al llegar a {{ $regla->meta_cantidad }}</span>
                      @endif 
                    </td>
                    <td class="text-center">
                      @if($regla->puntos_premio > 0)
                        <span class="badge rounded-pill bg-label-light-primary border border-black border-1">+ {{ number_format($regla->puntos_premio) }} pts</span>
                      @else
                        <span class="text-muted small">0 pts</span>
                      @endif
                    </td>
                    <td>
                      @if($regla->insignia)
                        <div class="d-flex align-items-center gap-2">
                          @if($regla->insignia->imagen_url && $regla->insignia->getRawOriginal('imagen_url'))
                            <img src="{{ $regla->insignia->imagen_url }}" class="rounded-circle shadow-sm" style="width: 22px; height: 22px; object-fit: cover;">
                          @else
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; background-color: {{ $regla->insignia->icono_color ?: 'var(--bs-primary)' }}20; color: {{ $regla->insignia->icono_color ?: 'var(--bs-primary)' }};">
                              <i class="{{ $regla->insignia->icono_completo }} fs-6"></i>
                            </div>
                          @endif
                          <span class="fw-semibold text-dark">{{ $regla->insignia->nombre }}</span>
                        </div>
                      @else
                        <span class="text-muted small">Ninguna</span>
                      @endif
                    </td>
                    <td class="text-end">
                      <button class="btn btn-text-success rounded-pill waves-effect me-1" wire:click="abrirModalRegla({{ $regla->id }})" title="Editar Regla">
                        <i class="ti ti-edit fs-5"></i>
                      </button>
                      <button class="btn btn-text-danger rounded-pill waves-effect" onclick="confirmarEliminarRegla({{ $regla->id }})" title="Eliminar Regla">
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
    @endif

    <!-- ===================================================================== -->
    <!-- TAB 2: CATÁLOGO DE INSIGNIAS -->
    <!-- ===================================================================== -->
    @if($tabActivo === 'insignias')
      <div>
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="fw-semibold text-black mb-0">Catálogo de insignias</h5>
          <button class="btn btn-primary rounded-pill d-flex align-items-center gap-2 fw-semibold px-3 py-2 shadow-sm" wire:click="abrirModalInsignia">
            <i class="ti ti-plus"></i>
            <span>Crear insignia</span>
          </button>
        </div>

        <!-- Buscador de Insignias -->
        <div class="row">
          <div class="col-12 col-md-6 mb-4 ">
            <div class="input-group input-group-merge shadow-none">
              <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
              <input type="text" class="form-control" placeholder="Buscar insignia por nombre..." wire:model.live.debounce.250ms="busquedaInsignias">
              @if(!empty($busquedaInsignias))
                <button class="btn btn-outline-secondary" type="button" wire:click="$set('busquedaInsignias', '')" title="Limpiar búsqueda">
                <i class="ti ti-x"></i>
              </button>
            @endif
          </div>
        </div>

        @if($insignias->isEmpty())
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="ti ti-award-off fs-1 text-black"></i>
            </div>
            @if(!empty($busquedaInsignias))
              <h6 class="text-black fw-semibold">No se encontraron insignias para "{{ $busquedaInsignias }}".</h6>
              <p class="text-muted small">Intenta buscar con otro término.</p>
              <button class="btn btn-outline-primary btn-sm rounded-pill mt-2" wire:click="$set('busquedaInsignias', '')">
                <i class="ti ti-rotate-clockwise me-1"></i> Limpiar búsqueda
              </button>
            @else
              <h6 class="text-black fw-semibold">No hay insignias registradas en el catálogo.</h6>
              <p class="text-black small">Crea las insignias y logros visuales que los usuarios podrán desbloquear.</p>
            @endif
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-gamificacion">
              <thead>
                <tr>
                  <th style="width: 80px;">Ícono</th>
                  <th>Nombre de Insignia</th>
                  <th>Descripción (Para el Usuario)</th>
                  <th class="text-end">Opciones</th>
                </tr>
              </thead>
              <tbody>
                @foreach($insignias as $insignia)
                  <tr>
                    <td>
                      @if($insignia->imagen_url && $insignia->getRawOriginal('imagen_url'))
                        <img src="{{ $insignia->imagen_url }}" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">
                      @else
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: {{ $insignia->icono_color ?: 'var(--bs-primary)' }}20; color: {{ $insignia->icono_color ?: 'var(--bs-primary)' }}; font-weight: 700;">
                          @if($insignia->icono_clase)
                            <i class="{{ $insignia->icono_completo }} fs-5"></i>
                          @else
                            {{ strtoupper(substr($insignia->nombre, 0, 2)) }}
                          @endif
                        </div>
                      @endif
                    </td>
                    <td>
                      <span class="fw-semibold text-black">{{ $insignia->nombre }}</span>
                    </td>
                    <td>
                      <span class="text-black">{{ $insignia->descripcion ?: 'Sin descripción' }}</span>
                    </td>
                    <td class="text-end">
                      <button class="btn btn-text-success rounded-pill waves-effect me-1" wire:click="abrirModalInsignia({{ $insignia->id }})" title="Editar Insignia">
                        <i class="ti ti-edit fs-5"></i>
                      </button>
                      <button class="btn btn-text-danger rounded-pill waves-effect" onclick="confirmarEliminarInsignia({{ $insignia->id }})" title="Eliminar Insignia">
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
    @endif

  </div>

  <!-- ===================================================================== -->
  <!-- OFFCANVAS: CONFIGURAR / EDITAR REGLA DE ACCIÓN -->
  <!-- ===================================================================== -->
  <div class="offcanvas offcanvas-end offcanvas-gamificacion" tabindex="-1" id="offcanvasRegla" aria-labelledby="offcanvasReglaLabel" wire:ignore.self>
    <div class="offcanvas-header border-bottom py-3">
      <h5 id="offcanvasReglaLabel" class="offcanvas-title fw-bold text-primary">
        {{ $reglaId ? 'Editar regla de acción' : 'Configurar regla de acción' }}
      </h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
      <form wire:submit.prevent="guardarRegla">
        
        <!-- Acción a Premiar (Trigger) -->
        <div class="mb-3">
          <label class="form-label fw-bold text-dark">Acción a Premiar (Trigger del Sistema)</label>
          <select class="form-select @error('accion_codigo') is-invalid @enderror" wire:model="accion_codigo">
            <option value="">Selecciona una acción programada...</option>
            @foreach($triggers as $codigo => $label)
              <option value="{{ $codigo }}">{{ $label }}</option>
            @endforeach
          </select>
          <small class="text-muted d-block mt-1">Estas son las acciones que el sistema reconoce automáticamente.</small>
          @error('accion_codigo') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <!-- Nombre de la Regla -->
        <div class="mb-3">
          <label class="form-label fw-bold text-dark">Nombre</label>
          <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre" placeholder="Ej: Realizar tiempo con Dios diario">
          <small class="text-muted d-block mt-1">Nombre descriptivo con el que se identificará esta regla en el sistema y las misiones.</small>
          @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <!-- Frecuencia de Recompensa -->
        <div class="card-offcanvas-section mb-3">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="ti ti-refresh text-primary fs-5"></i>
            <span class="fw-bold text-dark">Frecuencia de Recompensa</span>
          </div>
          <select class="form-select @error('frecuencia') is-invalid @enderror mb-2" wire:model.live="frecuencia">
            <option value="cada_vez">Cada vez que suceda (Ej: Puntos por cada devocional)</option>
            <option value="meta">Al alcanzar una meta</option>
            <option value="unica_vez">Única vez</option>
          </select>
          @error('frecuencia') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror

          <!-- Campo condicional cuando frecuencia es meta -->
          @if($frecuencia === 'meta')
            <div class="mt-3 pt-2 border-top">
              <label class="form-label small fw-semibold text-dark mb-1">¿Cuántas veces debe repetir la acción para ganar el premio?</label>
              <div class="input-group">
                <input type="number" min="1" class="form-control @error('meta_cantidad') is-invalid @enderror" wire:model="meta_cantidad" placeholder="20">
                <span class="input-group-text bg-light text-muted">veces</span>
              </div>
              <small class="text-muted d-block mt-1">Ejemplo: Dar insignia al llegar a 20 devocionales.</small>
              @error('meta_cantidad') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
            </div>
          @endif
        </div>

        <!-- Premio Otorgado -->
        <div class="card-offcanvas-section mb-4">
          <div class="d-flex align-items-center gap-2 mb-3">
            <i class="ti ti-gift text-primary fs-5"></i>
            <span class="fw-bold text-dark">¿Qué premio otorga esta acción?</span>
          </div>

          <!-- Puntos a sumar -->
          <div class="mb-3">
            <label class="form-label small text-black mb-1">Puntos a sumar</label>
            <div class="input-group">
              <span class="input-group-text bg-light">
                <span class="fs-6">🪙</span>
              </span>
              <input type="number" min="0" class="form-control @error('puntos_premio') is-invalid @enderror" wire:model="puntos_premio" placeholder="0">
            </div>
            <small class="text-muted d-block mt-1">Deja en 0 si la acción solo da una insignia.</small>
            @error('puntos_premio') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
          </div>

          <!-- Insignia a otorgar -->
          <div>
            <label class="form-label small text-black mb-1">Insignia a otorgar (Opcional)</label>
            <select class="form-select @error('insignia_id') is-invalid @enderror" wire:model="insignia_id">
              <option value="">Ninguna insignia</option>
              @foreach(($todasInsignias ?? $insignias) as $ins)
                <option value="{{ $ins->id }}">{{ $ins->nombre }}</option>
              @endforeach
            </select>
            @error('insignia_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
          </div>
        </div>

        <!-- Botones de Acción -->
        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="offcanvas">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
            <span wire:loading.remove>{{ $reglaId ? 'Guardar Regla' : 'Guardar Regla' }}</span>
            <span wire:loading><i class="ti ti-loader rotate"></i> Guardando...</span>
          </button>
        </div>

      </form>
    </div>
  </div>

  <!-- ===================================================================== -->
  <!-- OFFCANVAS: CREAR / EDITAR INSIGNIA -->
  <!-- ===================================================================== -->
  <div class="offcanvas offcanvas-end offcanvas-gamificacion" tabindex="-1" id="offcanvasInsignia" aria-labelledby="offcanvasInsigniaLabel" wire:ignore.self>
    <div class="offcanvas-header border-bottom py-3">
      <h5 id="offcanvasInsigniaLabel" class="offcanvas-title fw-semibold text-primary">
        {{ $insigniaId ? 'Editar insignia' : 'Crear nueva insignia' }}
      </h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
      <form wire:submit.prevent="guardarInsignia">

        <!-- Nombre de la Insignia -->
        <div class="mb-3">
          <label class="form-label fw-bold text-dark">Nombre de la Insignia</label>
          <input type="text" class="form-control @error('insigniaNombre') is-invalid @enderror" wire:model="insigniaNombre" placeholder="Ej: Lector Constante">
          @error('insigniaNombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <!-- Mensaje para el usuario (Descripción) -->
        <div class="mb-3">
          <label class="form-label fw-bold text-dark">Mensaje para el usuario (Descripción)</label>
          <textarea class="form-control @error('insigniaDescripcion') is-invalid @enderror" wire:model="insigniaDescripcion" rows="3" placeholder="Ej: Completaste 10 tiempos con Dios"></textarea>
          @error('insigniaDescripcion') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <!-- Diseño de la Insignia -->
        <div class="card-offcanvas-section mb-4">
          <label class="form-label fw-bold text-dark mb-2">Diseño de la Insignia</label>
          
          <div class="d-flex align-items-center gap-4 mb-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" value="icono" id="disenoIcono" wire:model.live="tipo_diseno">
              <label class="form-check-label fw-semibold text-dark" for="disenoIcono">
                Usar Ícono
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" value="imagen" id="disenoImagen" wire:model.live="tipo_diseno">
              <label class="form-check-label fw-semibold text-dark" for="disenoImagen">
                Subir Imagen
              </label>
            </div>
          </div>

          <!-- Modo Subir Imagen -->
          @if($tipo_diseno === 'imagen')
            <div class="mb-3" wire:ignore>
              <label class="form-label small text-black mb-2 fw-semibold">Imagen de la Insignia</label>
              
              <!-- Input oculto para selección de foto -->
              <input type="file" id="inlineCropperUploadInsignia" class="d-none" accept="image/png,image/jpeg,image/webp" onchange="window.handleCropperInsigniaChange(this)">

              <!-- Vista previa y botón para disparar selección -->
              <div id="inlinePreviewSectionInsignia" class="d-flex align-items-center gap-3">
                <div class="position-relative d-inline-block">
                  <img id="inlineThumbInsignia" src="{{ $imagen_recortada ?: ($imagen_existente ? tenant_asset('img/insignias/' . $imagen_existente) : Storage::disk('global_media')->url('placeholder.jpg')) }}" class="rounded-circle shadow-sm border" style="width: 75px; height: 75px; object-fit: cover; {{ (!$imagen_recortada && !$imagen_existente) ? 'opacity: 0.6;' : '' }}">
                </div>

                <div>
                  <button type="button" class="btn btn-outline-primary btn-sm rounded-pill d-flex align-items-center gap-1 mb-1" onclick="document.getElementById('inlineCropperUploadInsignia').click()">
                    <i class="ti ti-photo fs-5"></i>
                    <span>{{ ($imagen_recortada || $imagen_existente) ? 'Cambiar imagen' : 'Seleccionar foto' }}</span>
                  </button>
                  <small class="text-muted d-block" style="font-size: 0.78rem;">Recorte cuadrado (1:1), formato PNG (256×256px).</small>
                </div>
              </div>

              <!-- Contenedor del Cropper en línea (aparece directamente en el offcanvas al elegir foto) -->
              <div id="inlineCropperContainerInsignia" class="mt-3 p-3 border rounded-3 bg-light" style="display: none;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="fw-bold small text-dark d-flex align-items-center gap-1">
                    <i class="ti ti-crop text-primary"></i> Encuadra tu imagen (1:1)
                  </span>
                  <button type="button" class="btn btn-xs btn-outline-secondary rounded-circle" onclick="window.limpiarCropperInsignia()" title="Cancelar recorte">
                    <i class="ti ti-x"></i>
                  </button>
                </div>

                <div class="border rounded bg-white d-flex align-items-center justify-content-center p-1 mb-3" style="min-height: 200px; max-height: 280px; overflow: hidden;">
                  <img id="inlineCroppingImageInsignia" class="img-fluid" alt="Cropper Insignia" style="max-height: 260px; display: block; max-width: 100%;">
                </div>

                <div class="d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.limpiarCropperInsignia()">Cancelar</button>
                  <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-1" onclick="window.confirmarRecorteInsignia()">
                    <i class="ti ti-check"></i>
                    <span>Aplicar recorte</span>
                  </button>
                </div>
              </div>

              @error('imagen_recortada') <span class="invalid-feedback d-block mt-2">{{ $message }}</span> @enderror
            </div>
          @endif

          <!-- Modo Ícono -->
          @if($tipo_diseno === 'icono')
            <div class="mb-3">
              <label class="form-label small text-black mb-1">Clase del ícono (Tabler Icon)</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-dark">
                  <i class="{{ $icono_clase ?: 'ti ti-award' }}"></i>
                </span>
                <input type="text" class="form-control @error('icono_clase') is-invalid @enderror" wire:model.live="icono_clase" placeholder="ti ti-award ó fa-solid fa-star">
              </div>
              <small class="text-muted d-block mt-1">Ej: ti ti-award, ti ti-flame, ti ti-user-check, ti ti-chart-pie</small>
              @error('icono_clase') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
            </div>

            <div>
              <label class="form-label small text-black mb-1">Color del ícono</label>
              <div class="d-flex align-items-center gap-2">
                <input type="color" class="form-control form-control-color" wire:model.live="icono_color" style="width: 45px; height: 38px;">
                <input type="text" class="form-control @error('icono_color') is-invalid @enderror" wire:model.live="icono_color" placeholder="#166534">
              </div>
              @error('icono_color') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
            </div>
          @endif

        </div>

        <!-- Botones de Acción -->
        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="offcanvas">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
            <span wire:loading.remove>{{ $insigniaId ? 'Guardar Insignia' : 'Guardar Insignia' }}</span>
            <span wire:loading><i class="ti ti-loader rotate"></i> Guardando...</span>
          </button>
        </div>

      </form>
    </div>
  </div>

  <!-- Scripts para Offcanvas, Cropper en línea y SweetAlert2 -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Función para abrir offcanvas con backdrop persistente
      function abrirOffcanvasConBackdrop(idModal) {
        const el = document.getElementById(idModal);
        if (!el) return;

        let backdrop = document.querySelector('.offcanvas-backdrop');
        if (!backdrop) {
          backdrop = document.createElement('div');
          backdrop.className = 'offcanvas-backdrop fade show';
          document.body.appendChild(backdrop);
        }

        let bsOffcanvas = bootstrap.Offcanvas.getInstance(el);
        if (!bsOffcanvas) {
          bsOffcanvas = new bootstrap.Offcanvas(el, {
            backdrop: false,
            scroll: true
          });
        }
        bsOffcanvas.show();

        el.addEventListener('hidden.bs.offcanvas', function() {
          const bd = document.querySelector('.offcanvas-backdrop');
          if (bd) {
            bd.remove();
          }
          limpiarCropperInsignia();
        }, { once: true });
      }

      // Función para cerrar offcanvas y limpiar backdrop
      function cerrarOffcanvasConBackdrop(idModal) {
        const el = document.getElementById(idModal);
        if (el) {
          const bsOffcanvas = bootstrap.Offcanvas.getInstance(el);
          if (bsOffcanvas) {
            bsOffcanvas.hide();
          }
        }
        const bd = document.querySelector('.offcanvas-backdrop');
        if (bd) {
          bd.remove();
        }
        limpiarCropperInsignia();
      }

      // Cerrar offcanvas si el usuario hace clic sobre el backdrop
      document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('offcanvas-backdrop')) {
          ['offcanvasRegla', 'offcanvasInsignia'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el && el.classList.contains('show')) {
              const bsOffcanvas = bootstrap.Offcanvas.getInstance(el);
              if (bsOffcanvas) {
                bsOffcanvas.hide();
              }
            }
          });
          e.target.remove();
          limpiarCropperInsignia();
        }
      });

      // Eventos Livewire para abrir/cerrar
      Livewire.on('abrirOffcanvasRegla', () => {
        abrirOffcanvasConBackdrop('offcanvasRegla');
      });

      Livewire.on('cerrarOffcanvasRegla', () => {
        cerrarOffcanvasConBackdrop('offcanvasRegla');
      });

      Livewire.on('abrirOffcanvasInsignia', (data) => {
        const payload = Array.isArray(data) ? data[0] : (data || {});
        limpiarCropperInsignia();

        const placeholder = payload.placeholderUrl || '';
        const currentImg = payload.imagenUrl || placeholder;
        const isPlaceholder = !payload.imagenUrl;

        const thumb = document.getElementById('inlineThumbInsignia');
        if (thumb) {
          thumb.src = currentImg;
          thumb.style.opacity = isPlaceholder ? '0.6' : '1';
        }

        abrirOffcanvasConBackdrop('offcanvasInsignia');
      });

      Livewire.on('cerrarOffcanvasInsignia', () => {
        cerrarOffcanvasConBackdrop('offcanvasInsignia');
      });

      // =========================================================================
      // CROPPER JS EN LÍNEA - INSIGNIA (FUNCIONES GLOBALES)
      // =========================================================================
      let cropperInsignia = null;

      window.limpiarCropperInsignia = function() {
        if (cropperInsignia) {
          cropperInsignia.destroy();
          cropperInsignia = null;
        }
        const container = document.getElementById('inlineCropperContainerInsignia');
        if (container) {
          container.style.display = 'none';
        }
        const upload = document.getElementById('inlineCropperUploadInsignia');
        if (upload) {
          upload.value = '';
        }
      };

      window.handleCropperInsigniaChange = function(input) {
        if (input.files && input.files.length) {
          const file = input.files[0];
          if (['image/gif', 'image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            if (cropperInsignia) {
              cropperInsignia.destroy();
              cropperInsignia = null;
            }
            const reader = new FileReader();
            reader.onload = function(evt) {
              const croppingImg = document.getElementById('inlineCroppingImageInsignia');
              const container = document.getElementById('inlineCropperContainerInsignia');
              if (croppingImg && container) {
                croppingImg.src = evt.target.result;
                container.style.display = 'block';

                setTimeout(() => {
                  if (cropperInsignia) {
                    cropperInsignia.destroy();
                  }
                  cropperInsignia = new Cropper(croppingImg, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 0.95,
                    responsive: true,
                    zoomable: true,
                    cropBoxResizable: true
                  });
                }, 80);
              }
            };
            reader.readAsDataURL(file);
          } else {
            Swal.fire({
              icon: 'warning',
              title: 'Formato no soportado',
              text: 'Por favor selecciona un archivo PNG, JPG o WEBP.',
              confirmButtonText: 'Aceptar',
              customClass: { confirmButton: 'btn btn-primary rounded-pill px-4' },
              buttonsStyling: false
            });
          }
        }
      };

      window.confirmarRecorteInsignia = function() {
        if (!cropperInsignia) return;

        const canvas = cropperInsignia.getCroppedCanvas({
          width: 256,
          height: 256,
          imageSmoothingQuality: 'high'
        });

        if (canvas) {
          const imgSrc = canvas.toDataURL('image/png');
          @this.set('imagen_recortada', imgSrc);

          const thumb = document.getElementById('inlineThumbInsignia');
          if (thumb) {
            thumb.src = imgSrc;
            thumb.style.opacity = '1';
          }

          window.limpiarCropperInsignia();
        }
      };

      // Notificaciones SweetAlert2
      Livewire.on('msn', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        Swal.fire({
          icon: payload.icono || 'success',
          title: payload.titulo || '¡Éxito!',
          text: payload.texto || '',
          confirmButtonText: 'Aceptar',
          customClass: {
            confirmButton: 'btn btn-primary rounded-pill px-4'
          },
          buttonsStyling: false
        });
      });
    });

    // Confirmación global para eliminar Regla
    function confirmarEliminarRegla(id) {
      Swal.fire({
        title: '¿Eliminar esta regla?',
        text: 'Los puntos y recompensas automáticas asociados a esta acción ya no se asignarán.',
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
          @this.call('eliminarRegla', id);
        }
      });
    }

    // Confirmación global para eliminar Insignia
    function confirmarEliminarInsignia(id) {
      Swal.fire({
        title: '¿Eliminar esta insignia?',
        text: 'La insignia ya no estará disponible para nuevas asignaciones en el catálogo.',
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
          @this.call('eliminarInsignia', id);
        }
      });
    }
  </script>
</div>
