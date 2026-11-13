<div>
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Estados civiles</h4>
      </div>
      <p class="text-black mb-0 small">
        Administra las opciones de estado civil configuradas para el registro y perfil de usuarios.
      </p>
    </div>

    <div>
      <button type="button" class="btn btn-primary rounded-pill shadow-sm" wire:click="crear">
        <i class="ti ti-plus me-1"></i> Nuevo estado civil
      </button>
    </div>
  </div>

  <!-- Barra de Búsqueda y Ordenamiento -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
      <div class="row align-items-center justify-content-between g-3">
        <!-- Buscador en tiempo real -->
        <div class="col-12 col-md-5 col-lg-4">
          <div class="input-group input-group-merge border border-primary rounded-3">
            <span class="input-group-text bg-white text-muted">
              <i class="ti ti-search"></i>
            </span>
            <input 
              type="text" 
              class="form-control ps-1" 
              placeholder="Buscar estado civil..." 
              wire:model.live.debounce.300ms="search"
            >
            @if(!empty($search))
              <button 
                class="input-group-text bg-white text-muted cursor-pointer" 
                type="button" 
                wire:click="limpiarBusqueda"
                title="Limpiar búsqueda"
              >
                <i class="ti ti-x ti-xs"></i>
              </button>
            @endif
          </div>
        </div>

        <!-- Botones de ordenamiento y contador -->
        <div class="col-12 col-md-auto d-flex flex-wrap align-items-center justify-content-md-end gap-2">
          <div class="d-flex align-items-center gap-1 flex-wrap">
            <span class="text-muted small me-1"><i class="ti ti-arrows-sort ti-xs"></i> Ordenar:</span>

            <button 
              type="button" 
              class="btn btn-xs rounded-pill {{ $sortField === 'orden' ? 'btn-primary' : 'btn-label-secondary' }}"
              wire:click="sortBy('orden')"
            >
              Orden
              @if($sortField === 'orden')
                <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} ti-xs ms-1"></i>
              @endif
            </button>

            <button 
              type="button" 
              class="btn btn-xs rounded-pill {{ $sortField === 'nombre' ? 'btn-primary' : 'btn-label-secondary' }}"
              wire:click="sortBy('nombre')"
            >
              Nombre
              @if($sortField === 'nombre')
                <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} ti-xs ms-1"></i>
              @endif
            </button>

            <button 
              type="button" 
              class="btn btn-xs rounded-pill {{ $sortField === 'usuarios_count' ? 'btn-primary' : 'btn-label-secondary' }}"
              wire:click="sortBy('usuarios_count')"
            >
              Usuarios
              @if($sortField === 'usuarios_count')
                <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} ti-xs ms-1"></i>
              @endif
            </button>

            <button 
              type="button" 
              class="btn btn-xs rounded-pill {{ $sortField === 'activo' ? 'btn-primary' : 'btn-label-secondary' }}"
              wire:click="sortBy('activo')"
            >
              Estado
              @if($sortField === 'activo')
                <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} ti-xs ms-1"></i>
              @endif
            </button>
          </div>

          <span class="text-black small ms-md-2">
            Total de registros: <strong class="text-dark">{{ $estadosCiviles->total() }}</strong>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Listado de Cards de Estados Civiles -->
  <div class="row g-4 mb-4">
    @forelse($estadosCiviles as $estado)
      <div class="col-12 col-md-6 col-lg-4" wire:key="estado-civil-card-{{ $estado->id }}">
        <div class="card h-100 border-0 shadow-sm {{ !($estado->activo ?? true) ? 'opacity-75' : '' }}">
          <div class="card-body d-flex flex-column justify-content-between p-4">
            <div>
              <!-- Encabezado de la Card -->
              <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-label-primary rounded-pill px-2 py-1 fw-semibold" title="Orden de visualización">
                    #{{ $estado->orden ?? 0 }}
                  </span>
                  <h5 class="card-title text-dark mb-0 fw-bold">{{ $estado->nombre }}</h5>
                </div>

                <!-- Acciones -->
                <div class="d-inline-flex gap-1 flex-shrink-0">
                  <button 
                    type="button" 
                    class="btn btn-icon btn-sm btn-primary rounded-pill" 
                    wire:click="editar({{ $estado->id }})"
                    title="Editar estado civil"
                  >
                    <i class="ti ti-pencil ti-xs"></i>
                  </button>

                  <button 
                    type="button" 
                    class="btn btn-icon btn-sm btn-danger rounded-pill" 
                    onclick="confirmarEliminacion({{ $estado->id }}, '{{ addslashes($estado->nombre) }}', {{ $estado->usuarios_count }})"
                    title="Eliminar estado civil"
                  >
                    <i class="ti ti-trash ti-xs"></i>
                  </button>
                </div>
              </div>

              <!-- Métricas -->
              <div class="p-3 bg-light rounded-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-black small">
                    <i class="ti ti-users ti-xs text-primary me-1"></i> Usuarios vinculados:
                  </span>
                  <span class="fw-bold text-dark">{{ $estado->usuarios_count > 0 ? $estado->usuarios_count : 0 }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-black small">
                    <i class="ti ti-calendar ti-xs text-muted me-1"></i> Creado:
                  </span>
                  <span class="text-muted small">{{ $estado->created_at ? $estado->created_at->format('Y-m-d') : 'N/A' }}</span>
                </div>
              </div>
            </div>

            <!-- Badges de estado e interruptores rápidos -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
              <!-- Estado Activo / Inactivo -->
              <button 
                type="button" 
                class="btn btn-xs py-1 px-3 rounded-pill {{ ($estado->activo ?? true) ? 'btn-label-success' : 'btn-label-secondary' }}"
                wire:click="toggleActivo({{ $estado->id }})"
                title="Clic para cambiar estado activo/inactivo"
              >
                <i class="ti {{ ($estado->activo ?? true) ? 'ti-circle-check' : 'ti-circle-x' }} ti-xs me-1"></i>
                {{ ($estado->activo ?? true) ? 'Activo' : 'Inactivo' }}
              </button>

              <!-- ¿Es unión libre? -->
              <button 
                type="button" 
                class="btn btn-xs py-1 px-3 rounded-pill {{ $estado->es_union_libre ? 'btn-label-primary' : 'btn-label-secondary' }}"
                wire:click="toggleUnionLibre({{ $estado->id }})"
                title="Clic para cambiar si es unión libre"
              >
                <i class="ti {{ $estado->es_union_libre ? 'ti-check' : 'ti-x' }} ti-xs me-1"></i>
                Unión libre: {{ $estado->es_union_libre ? 'Sí' : 'No' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center py-5">
            <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
              <i class="ti ti-heart-off ti-lg text-white"></i>
            </div>
            <h6 class="fw-semibold mb-1 text-dark">No se encontraron estados civiles</h6>
            <p class="text-black small mb-3">
              @if(!empty($search))
                No hay resultados para la búsqueda "<strong class="text-primary">{{ $search }}</strong>".
              @else
                Aún no has registrado ningún estado civil en el sistema.
              @endif
            </p>
            @if(!empty($search))
              <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" wire:click="limpiarBusqueda">
                <i class="ti ti-arrow-back-up me-1"></i> Restablecer búsqueda
              </button>
            @else
              <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" wire:click="crear">
                <i class="ti ti-plus me-1"></i> Registrar el primero
              </button>
            @endif
          </div>
        </div>
      </div>
    @endforelse
  </div>

  <!-- Paginación -->
  @if($estadosCiviles->hasPages())
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 py-3">
        <span class="text-black small">
          Mostrando {{ $estadosCiviles->firstItem() }} a {{ $estadosCiviles->lastItem() }} de {{ $estadosCiviles->total() }} registros
        </span>
        <div class="pagination-clean">
          {{ $estadosCiviles->links() }}
        </div>
      </div>
    </div>
  @endif

  <style>
    /* Oculta los botones de texto « Anterior / Siguiente » del bloque mobile */
    .pagination-clean nav > div:first-child {
      display: none !important;
    }
    /* Muestra el contenedor de paginación numérico alineado a la derecha */
    .pagination-clean nav > div:last-child {
      display: flex !important;
      justify-content: flex-end !important;
      margin-bottom: 0 !important;
    }
    /* Oculta el texto interno "Mostrando 1 al 12 de 416 resultados" */
    .pagination-clean nav > div:last-child > div:first-child {
      display: none !important;
    }
    .pagination-clean .pagination {
      margin-bottom: 0 !important;
    }
  </style>

  <!-- Offcanvas para Crear / Editar Estado Civil -->
  <div 
    class="offcanvas offcanvas-end" 
    tabindex="-1" 
    id="offcanvasEstadoCivil" 
    wire:ignore.self 
    aria-labelledby="offcanvasEstadoCivilLabel"
  >
    <form wire:submit.prevent="guardar" class="d-flex flex-column h-100 m-0 overflow-hidden">
      <div class="offcanvas-header border-bottom flex-shrink-0">
        <h5 id="offcanvasEstadoCivilLabel" class="offcanvas-title fw-semibold text-primary">
          <i class="ti {{ $isEditing ? 'ti-pencil' : 'ti-plus' }} me-2"></i>
          {{ $isEditing ? 'Editar estado civil' : 'Nuevo estado civil' }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body flex-grow-1 overflow-y-auto p-4">
        <!-- Nombre -->
        <div class="mb-4">
          <label class="form-label fw-semibold" for="estadoCivilNombre">
            Nombre del estado civil <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-heart-handshake"></i></span>
            <input 
              type="text" 
              id="estadoCivilNombre" 
              class="form-control @error('nombre') is-invalid @enderror" 
              placeholder="Ej. Soltero(a), Casado(a), Unión Libre..." 
              wire:model="nombre"
              autocomplete="off"
            >
          </div>
          @error('nombre')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <small class="text-muted d-block mt-1">
            Indica el nombre descriptivo del estado civil.
          </small>
        </div>

        <!-- Orden -->
        <div class="mb-4">
          <label class="form-label fw-semibold" for="estadoCivilOrden">
            Orden de visualización <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-sort-ascending-numbers"></i></span>
            <input 
              type="number" 
              id="estadoCivilOrden" 
              class="form-control @error('orden') is-invalid @enderror" 
              placeholder="1, 2, 3..." 
              wire:model="orden"
              min="0"
            >
          </div>
          @error('orden')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <small class="text-muted d-block mt-1">
            Define la posición en la que se mostrará esta opción en listados y formularios.
          </small>
        </div>

        <!-- Switch Es Unión Libre -->
        <div class="mb-4">
          <div class="form-check form-switch ps-0">
            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border bg-light">
              <div>
                <label class="form-check-label fw-semibold text-dark d-block mb-1 cursor-pointer" for="switchUnionLibre">
                  ¿Es unión libre?
                </label>
                <small class="text-muted d-block">
                  Habilita este indicador para categorizarlo como convivencia o unión libre.
                </small>
              </div>
              <input 
                class="form-check-input ms-3 fs-4 cursor-pointer" 
                type="checkbox" 
                id="switchUnionLibre" 
                wire:model="es_union_libre"
              >
            </div>
          </div>
        </div>

        <!-- Switch Activo -->
        <div class="mb-4">
          <div class="form-check form-switch ps-0">
            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border bg-light">
              <div>
                <label class="form-check-label fw-semibold text-dark d-block mb-1 cursor-pointer" for="switchActivoEstadoCivil">
                  ¿Está activo?
                </label>
                <small class="text-muted d-block">
                  Determina si el estado civil está habilitado y visible para su selección.
                </small>
              </div>
              <input 
                class="form-check-input ms-3 fs-4 cursor-pointer" 
                type="checkbox" 
                id="switchActivoEstadoCivil" 
                wire:model="activo"
              >
            </div>
          </div>
        </div>
      </div>

      <div class="offcanvas-footer flex-shrink-0 d-flex align-items-center justify-content-end gap-3 p-4 border-top bg-white">
        <button type="button" class="btn btn-label-secondary rounded-pill px-3" data-bs-dismiss="offcanvas">
          Cancelar
        </button>
        <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="guardar">
            <i class="ti ti-device-floppy me-1"></i> {{ $isEditing ? 'Guardar' : 'Crear estado civil' }}
          </span>
          <span wire:loading wire:target="guardar">
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            Guardando...
          </span>
        </button>
      </div>
    </form>
  </div>
</div>

@script
<script>
  let offcanvasElement = document.getElementById('offcanvasEstadoCivil');
  let offcanvasInstance = null;

  if (offcanvasElement) {
    offcanvasInstance = new bootstrap.Offcanvas(offcanvasElement);
  }

  // Eventos de control del Offcanvas
  Livewire.on('abrir-offcanvas-estado-civil', () => {
    if (offcanvasInstance) {
      offcanvasInstance.show();
      setTimeout(() => {
        const input = document.getElementById('estadoCivilNombre');
        if (input) input.focus();
      }, 300);
    }
  });

  Livewire.on('cerrar-offcanvas-estado-civil', () => {
    if (offcanvasInstance) {
      offcanvasInstance.hide();
    }
  });

  // Alertas SweetAlert2 unificadas
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

  // Confirmación de eliminación con SweetAlert2
  window.confirmarEliminacion = function(id, nombre, totalUsuarios) {
    if (totalUsuarios > 0) {
      Swal.fire({
        icon: 'warning',
        title: 'No se puede eliminar',
        text: `El estado civil "${nombre}" tiene ${totalUsuarios} usuario(s) vinculado(s). No puede ser eliminado.`,
        confirmButtonText: 'Entendido',
        customClass: {
          confirmButton: 'btn btn-primary rounded-pill px-4'
        },
        buttonsStyling: false
      });
      return;
    }

    Swal.fire({
      title: '¿Estás seguro?',
      text: `Se eliminará el estado civil "${nombre}". Esta acción puede revertirse posteriormente.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-danger rounded-pill px-4 me-2',
        cancelButton: 'btn btn-label-secondary rounded-pill px-4'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        @this.call('eliminar', id);
      }
    });
  };
</script>
@endscript
