<div>
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Permisos</h4>
      </div>
      <p class="text-black mb-0 small">
        Administra y ajusta masivamente la asignación de permisos a los diferentes roles del sistema.
      </p>
    </div>

  </div>

  <!-- Card Principal con Buscador y Filtros -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-center">
        <!-- Buscador -->
        <div class="col-12 col-md-5 col-lg-4">
          <label class="form-label small text-black mb-1">Buscar permiso</label>
          <div class="input-group rounded-3 overflow-hidden border border-primary">
            <span class="input-group-text bg-white border-0 text-muted">
              <i class="ti ti-search"></i>
            </span>
            <input 
              type="text" 
              class="form-control border-0 bg-white ps-1 shadow-none" 
              placeholder="Buscar por título o slug..." 
              wire:model.live.debounce.300ms="search"
            >
            @if(!empty($search))
              <button 
                class="input-group-text bg-white border-0 text-muted cursor-pointer" 
                type="button" 
                wire:click="limpiarBusqueda"
                title="Limpiar búsqueda"
              >
                <i class="ti ti-x ti-xs"></i>
              </button>
            @endif
          </div>
        </div>

        <!-- Filtro por Bloque / Categoría -->
        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
          <label class="form-label small text-black mb-1">Módulo</label>
          <select class="form-select" wire:model.live="bloqueSeleccionado">
            <option value="todos">Todos los bloques</option>
            @foreach($bloques as $nombre => $etiqueta)
              <option value="{{ $etiqueta }}">{{ $nombre }}</option>
            @endforeach
            <option value="otros">Otros Permisos</option>
          </select>
        </div>

        <!-- Filtro por Cobertura -->
        <div class="col-12 col-sm-6 col-md-2 col-lg-3">
          <label class="form-label small text-black mb-1">Cobertura de roles</label>
          <select class="form-select" wire:model.live="filtroCobertura">
            <option value="todos">Todos los estados</option>
            <option value="con_roles">Con roles asignados</option>
            <option value="sin_roles">Sin roles asignados</option>
            <option value="todos_roles">Asignados a todos</option>
          </select>
        </div>

        <!-- Paginación por página -->
        <div class="col-12 col-sm-6 col-md-2 col-lg-2">
          <label class="form-label small text-black mb-1">Mostrar</label>
          <select class="form-select" wire:model.live="porPagina">
            <option value="12">12 por pág.</option>
            <option value="24">24 por pág.</option>
            <option value="48">48 por pág.</option>
            <option value="96">96 por pág.</option>
          </select>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top text-black small">
        <div>
          Total de permisos: <strong class="text-dark">{{ $permisos->total() }}</strong>
          @if($totalRolesCount > 0)
            <span class="text-black ms-2">• {{ $totalRolesCount }} roles disponibles</span>
          @endif
        </div>
        <div wire:loading class="text-primary small">
          <span class="spinner-border spinner-border-sm me-1" role="status"></span>
          Cargando...
        </div>
      </div>
    </div>
  </div>

  <!-- Grid de Tarjetas de Permisos -->
  <div class="row g-3">
    @forelse($permisos as $permiso)
      @php
        $rolesCount = $permiso->roles->count();
        $estaEnTodos = ($totalRolesCount > 0 && $rolesCount >= $totalRolesCount);
      @endphp
      <div class="col-12 col-md-6 col-xl-4" wire:key="permiso-card-{{ $permiso->id }}">
        <div class="card h-100 border shadow-sm  transition-all">
          <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div>
              <!-- Encabezado de la tarjeta -->
              <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <div class="d-flex align-items-start gap-2">
                  <div class="avatar avatar-sm flex-shrink-0">
                    <span class="avatar-initial rounded bg-label-primary">
                      <i class="ti ti-shield-lock"></i>
                    </span>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-semibold text-dark text-break">
                      {{ ucfirst(str_replace('_', ' ', $permiso->titulo ?: $permiso->name)) }}
                    </h6>
                    <code class="text-muted small text-break">{{ $permiso->name }}</code>
                  </div>
                </div>
              </div>

              <!-- Roles asignados (Avatar Group estilo Formulario) -->
              <div class="d-flex align-items-center avatar-group my-3">
                @if($rolesCount > 0)
                  @foreach ($permiso->roles->take(5) as $rol)
                    <div class="avatar avatar-sm" wire:key="rol-avatar-{{ $permiso->id }}-{{ $rol->id }}">
                      <span class="avatar-initial rounded-circle pull-up text-heading bg-light border" 
                            data-bs-toggle="tooltip" 
                            data-popup="tooltip-custom" 
                            data-bs-placement="top" 
                            title="{{ $rol->name }}">
                        <i class="{{ $rol->icono ?: 'ti ti-user' }}"></i>
                      </span>
                    </div>
                  @endforeach

                  @if($rolesCount > 5)
                    <div class="avatar avatar-sm">
                      <span class="avatar-initial rounded-circle pull-up text-heading bg-label-secondary border small" 
                            data-bs-toggle="tooltip" 
                            data-popup="tooltip-custom" 
                            data-bs-placement="bottom" 
                            title="{{ $permiso->roles->slice(5)->pluck('name')->implode(', ') }}">
                        +{{ $rolesCount - 5 }}
                      </span>
                    </div>
                  @endif
                @else
                  <div class="avatar avatar-sm">
                    <span class="avatar-initial rounded-circle pull-up text-heading bg-light border" 
                          data-bs-toggle="tooltip" 
                          data-bs-placement="bottom" 
                          title="Sin roles asignados">
                      <i class="ti ti-help-hexagon"></i>
                    </span>
                  </div>
                  <span class="text-muted small ms-2">Roles no definidos</span>
                @endif   
                  
              </div>
              
            </div>

            <!-- Footer con Botón Ajustar Roles (Sin texto redundante) -->
            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
              
              <span class="badge flex-shrink-0 ">
                {{ $rolesCount }}/{{ $totalRolesCount }} roles
              </span>

              <button 
                type="button" 
                class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                wire:click="abrirAjustePermiso({{ $permiso->id }})"
                wire:loading.attr="disabled"
              >
                <i class="ti ti-adjustments-horizontal me-1"></i> Ajustar Roles
              </button>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="card border-0 shadow-sm text-center py-5">
          <div class="card-body">
            <div class="avatar avatar-xl mx-auto mb-3">
              <span class="avatar-initial rounded-circle bg-label-secondary">
                <i class="ti ti-shield-off ti-lg"></i>
              </span>
            </div>
            <h5 class="fw-semibold text-dark mb-1">No se encontraron permisos</h5>
            <p class="text-muted small mb-3">
              No hay permisos que coincidan con los filtros o término de búsqueda aplicado.
            </p>
            @if(!empty($search) || $bloqueSeleccionado !== 'todos' || $filtroCobertura !== 'todos')
              <button type="button" class="btn btn-outline-primary rounded-pill btn-sm" wire:click="limpiarBusqueda">
                <i class="ti ti-refresh me-1"></i> Restablecer filtros
              </button>
            @endif
          </div>
        </div>
      </div>
    @endforelse
  </div>

  <!-- Paginación -->
  <div class="d-flex justify-content-between align-items-center mt-4 pagination-clean">
    <div class="small text-muted">
      Mostrando del {{ $permisos->firstItem() ?? 0 }} al {{ $permisos->lastItem() ?? 0 }} de {{ $permisos->total() }} registros
    </div>
    <div>
      {{ $permisos->links() }}
    </div>
  </div>

  <!-- Estilos de Paginación Limpia -->
  <style>
    .pagination-clean nav > div:first-child {
      display: none !important;
    }
    .pagination-clean nav > div:last-child {
      display: flex !important;
      justify-content: flex-end !important;
      margin-bottom: 0 !important;
    }
    .pagination-clean nav > div:last-child > div:first-child {
      display: none !important;
    }
    .pagination-clean .pagination {
      margin-bottom: 0 !important;
    }
    .hover-shadow:hover {
      box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45) !important;
      border-color: #696cff !important;
    }
    .transition-all {
      transition: all 0.2s ease-in-out;
    }
    #offcanvasAjustePermiso {
      z-index: 1090 !important;
    }
    #backdrop-gestionar-permisos {
      z-index: 1085 !important;
    }
    .swal2-container {
      z-index: 10000 !important;
    }
  </style>

  <!-- Offcanvas Lateral para Ajustar Permiso Masivamente a Roles -->
  <div 
    class="offcanvas offcanvas-end" 
    tabindex="-1" 
    id="offcanvasAjustePermiso" 
    data-bs-backdrop="false"
    wire:ignore.self 
    aria-labelledby="offcanvasAjustePermisoLabel"
    style="width: 500px; max-width: 95vw; z-index: 1090 !important;"
  >
    <div class="offcanvas-header border-bottom py-3">
      <div>
        <h5 id="offcanvasAjustePermisoLabel" class="offcanvas-title fw-semibold text-primary mb-0">
          <i class="ti ti-shield-lock me-1"></i> Ajustar roles del permiso
        </h5>
        <code class="small text-muted d-block text-break">{{ $permisoSeleccionadoNombre }}</code>
      </div>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-4">

        <!-- Mensaje informativo de guardado automático (Arriba del buscador) -->
      <div class="d-flex align-items-center gap-2 text-muted small mb-2 px-1">
        <i class="ti ti-check text-success"></i>
        <span class="text-black">Los cambios se guardan de manera automática</span>
      </div>

      <!-- Acciones Rápidas Masivas -->
      <div class="d-flex gap-2 mb-2">
        <button 
          type="button" 
          class="btn btn-sm btn-outline-success rounded-pill flex-fill"
          onclick="window.confirmarAsignarTodos()"
          wire:loading.attr="disabled"
          wire:target="activarEnTodos, desactivarDeTodos"
        >
          <span wire:loading.remove wire:target="activarEnTodos">
            <i class="ti ti-checks me-1"></i> Activar en todos
          </span>
          <span wire:loading wire:target="activarEnTodos">
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            Activando...
          </span>
        </button>
        <button 
          type="button" 
          class="btn btn-sm btn-outline-danger rounded-pill flex-fill"
          onclick="window.confirmarDesactivarTodos()"
          wire:loading.attr="disabled"
          wire:target="activarEnTodos, desactivarDeTodos"
        >
          <span wire:loading.remove wire:target="desactivarDeTodos">
            <i class="ti ti-x me-1"></i> Desactivar de todos
          </span>
          <span wire:loading wire:target="desactivarDeTodos">
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            Desactivando...
          </span>
        </button>
      </div>

      <!-- Indicador visual mientras se procesa la acción masiva -->
      <div wire:loading wire:target="activarEnTodos, desactivarDeTodos" class="w-100 text-center py-2 mb-2">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-label-primary rounded-pill small">
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <span>Actualizando roles masivamente, por favor espera...</span>
        </div>
      </div>

    

      <!-- Buscador de Roles dentro del Offcanvas -->
      <div class="mb-3">
        <div class="input-group input-group-sm rounded-pill overflow-hidden border">
          <span class="input-group-text bg-white border-0 ps-3 text-muted">
            <i class="ti ti-search ti-xs"></i>
          </span>
          <input 
            type="text" 
            class="form-control border-0 bg-white ps-1 shadow-none" 
            placeholder="Filtrar rol por nombre..." 
            wire:model.live.debounce.150ms="searchRol"
          >
          @if(!empty($searchRol))
            <button 
              class="input-group-text bg-white border-0 text-muted cursor-pointer pe-3" 
              type="button" 
              wire:click="$set('searchRol', '')"
            >
              <i class="ti ti-x ti-xs"></i>
            </button>
          @endif
        </div>
      </div>

      <!-- Lista de Roles con Switches (Fondo uniforme limpio, sin sombreados grises) -->
      <div class="list-group list-group-flush border rounded-3 overflow-hidden shadow-none mb-4">
        @forelse($rolesParaOffcanvas as $rol)
          @php
            $asignado = in_array($rol->id, $rolesAsignadosIds);
          @endphp
          <div 
            class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" 
            wire:key="offcanvas-permiso-{{ $permisoSeleccionadoId }}-rol-{{ $rol->id }}"
          >
            <div class="d-flex align-items-center gap-2 text-truncate me-2">
              <i class="{{ $rol->icono ?: 'ti ti-user' }} text-muted ti-sm"></i>
              <span class="fw-semibold text-dark text-truncate">{{ $rol->name }}</span>
            </div>

            <div class="form-check form-switch mb-0">
              <input 
                class="form-check-input cursor-pointer" 
                type="checkbox" 
                role="switch"
                id="switchRol_{{ $permisoSeleccionadoId }}_{{ $rol->id }}" 
                wire:click="toggleRolPermiso({{ $rol->id }})"
                {{ $asignado ? 'checked' : '' }}
              >
            </div>
          </div>
        @empty
          <div class="p-3 text-center text-muted small">
            No se encontraron roles que coincidan con el término.
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

@script
<script>
  let offcanvasElement = document.getElementById('offcanvasAjustePermiso');
  let offcanvasInstance = null;

  function ensureBackdrop() {
    let backdrop = document.getElementById('backdrop-gestionar-permisos');
    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.id = 'backdrop-gestionar-permisos';
      backdrop.className = 'offcanvas-backdrop fade show';
      backdrop.style.zIndex = '1085';
      document.body.appendChild(backdrop);

      backdrop.addEventListener('click', () => {
        const instance = getOffcanvas();
        if (instance) instance.hide();
      });
    }
  }

  function removeBackdrop() {
    const backdrop = document.getElementById('backdrop-gestionar-permisos');
    if (backdrop) {
      backdrop.remove();
    }
  }

  function getOffcanvas() {
    if (!offcanvasElement) {
      offcanvasElement = document.getElementById('offcanvasAjustePermiso');
    }
    if (offcanvasElement && !offcanvasInstance) {
      offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement, {
        backdrop: false,
        scroll: false
      });

      // Escuchar el evento de cierre de Bootstrap para limpiar el backdrop del body
      offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
        removeBackdrop();
      });
    }
    return offcanvasInstance;
  }

  function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl);
    });
  }

  // Inicializar tooltips al cargar y después de morphing de Livewire
  initTooltips();
  Livewire.hook('morph.updated', () => {
    initTooltips();
  });

  // Eventos de control del Offcanvas
  Livewire.on('abrir-offcanvas-permiso', () => {
    ensureBackdrop();
    const instance = getOffcanvas();
    if (instance) {
      instance.show();
    }
  });

  Livewire.on('cerrar-offcanvas-permiso', () => {
    const instance = getOffcanvas();
    if (instance) {
      instance.hide();
    }
    removeBackdrop();
  });

  // Alerta Modal SweetAlert2 estándar únicamente para acciones masivas
  Livewire.on('msn', (data) => {
    const payload = Array.isArray(data) ? data[0] : data;
    Swal.fire({
      icon: payload.icono || 'success',
      title: payload.titulo || '¡Completado!',
      text: payload.texto || '',
      timer: payload.icono === 'success' ? 2500 : undefined,
      showConfirmButton: payload.icono !== 'success',
      confirmButtonText: 'Aceptar',
      customClass: {
        confirmButton: 'btn btn-primary rounded-pill px-4'
      },
      buttonsStyling: false
    });
  });

  // Confirmación masiva: Activar en todos los roles
  window.confirmarAsignarTodos = function() {
    Swal.fire({
      title: '¿Activar en todos los roles?',
      text: 'Este permiso se otorgará de inmediato a todos los roles del sistema.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, activar en todos',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-primary rounded-pill px-4 me-2',
        cancelButton: 'btn btn-label-secondary rounded-pill px-4'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Asignando permiso...',
          text: 'Otorgando el permiso a todos los roles, por favor espera.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        @this.call('activarEnTodos');
      }
    });
  };

  // Confirmación masiva: Desactivar de todos los roles
  window.confirmarDesactivarTodos = function() {
    Swal.fire({
      title: '¿Desactivar de todos los roles?',
      text: 'Este permiso se retirará de todos los roles del sistema.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, desactivar de todos',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-danger rounded-pill px-4 me-2',
        cancelButton: 'btn btn-label-secondary rounded-pill px-4'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Revocando permiso...',
          text: 'Retirando el permiso de todos los roles, por favor espera.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        @this.call('desactivarDeTodos');
      }
    });
  };
</script>
@endscript
