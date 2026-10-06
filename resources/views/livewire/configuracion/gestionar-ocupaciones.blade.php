<div>
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Ocupaciones</h4>
      </div>
      <p class="text-black mb-0 small">
        Administra las ocupaciones y oficios registrados para los usuarios del sistema.
      </p>
    </div>

    <div>
      <button type="button" class="btn btn-primary rounded-pill shadow-sm" wire:click="crear">
        <i class="ti ti-plus me-1"></i> Nueva ocupación
      </button>
    </div>
  </div>

  <!-- Card Principal con Buscador y Tabla -->
  <div class="card border-0 shadow-sm">
    <div class="card-body pb-2">
      <!-- Buscador en tiempo real -->
      <div class="row align-items-center justify-content-between g-3">
        <div class="col-12 col-md-5 col-lg-4">
          <div class="input-group input-group-merge border border-primary rounded-3">
            <span class="input-group-text bg-white text-muted">
              <i class="ti ti-search"></i>
            </span>
            <input 
              type="text" 
              class="form-control ps-1" 
              placeholder="Buscar ocupación..." 
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

        <div class="col-12 col-md-auto text-md-end text-black small">
          Total de registros: <strong class="text-dark">{{ $ocupaciones->total() }}</strong>
        </div>
      </div>
    </div>

    <!-- Tabla de Ocupaciones -->
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4" style="width: 70px;">#</th>
            
            <th class="cursor-pointer user-select-none" wire:click="sortBy('nombre')" title="Ordenar por Ocupación">
              <div class="d-inline-flex align-items-center gap-1">
                <span>Ocupación</span>
                @if($sortField === 'nombre')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="text-center cursor-pointer user-select-none" style="width: 200px;" wire:click="sortBy('usuarios_count')" title="Ordenar por Usuarios vinculados">
              <div class="d-inline-flex align-items-center justify-content-center gap-1">
                <span>Usuarios Vinculados</span>
                @if($sortField === 'usuarios_count')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="cursor-pointer user-select-none" style="width: 200px;" wire:click="sortBy('created_at')" title="Ordenar por Fecha de creación">
              <div class="d-inline-flex align-items-center gap-1">
                <span>Fecha de creación</span>
                @if($sortField === 'created_at')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="text-center pe-4" style="width: 130px;">Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse($ocupaciones as $ocupacion)
            <tr>
              <td class="ps-4 text-muted small">
                {{ $loop->iteration + ($ocupaciones->currentPage() - 1) * $ocupaciones->perPage() }}
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-semibold text-dark">{{ $ocupacion->nombre }}</span>
                </div>
              </td>
              <td class="text-center text-black">
                @if($ocupacion->usuarios_count > 0)
                  {{ $ocupacion->usuarios_count }}
                @else
                  0 
                @endif
              </td>
              <td class="text-black small">
                {{ $ocupacion->created_at ? $ocupacion->created_at->format('Y-m-d h:i A') : 'N/A' }}
              </td>
              <td class="text-center pe-4">
                <div class="d-inline-flex gap-1">
                  <button 
                    type="button" 
                    class="btn btn-icon btn-sm btn-primary rounded-pill" 
                    wire:click="editar({{ $ocupacion->id }})"
                    title="Editar ocupación"
                  >
                    <i class="ti ti-pencil ti-xs"></i>
                  </button>

                  <button 
                    type="button" 
                    class="btn btn-icon btn-sm btn-danger rounded-pill" 
                    onclick="confirmarEliminacion({{ $ocupacion->id }}, '{{ addslashes($ocupacion->nombre) }}', {{ $ocupacion->usuarios_count }})"
                    title="Eliminar ocupación"
                  >
                    <i class="ti ti-trash ti-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-5">
                <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                  <i class="ti ti-tool-off ti-lg text-white"></i>
                </div>
                <h6 class="fw-semibold mb-1 text-dark">No se encontraron ocupaciones</h6>
                <p class="text-black small mb-3">
                  @if(!empty($search))
                    No hay resultados para la búsqueda "<strong class="text-primary">{{ $search }}</strong>".
                  @else
                    Aún no has registrado ninguna ocupación en el sistema.
                  @endif
                </p>
                @if(!empty($search))
                  <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" wire:click="limpiarBusqueda">
                    <i class="ti ti-arrow-back-up me-1"></i> Restablecer búsqueda
                  </button>
                @else
                  <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" wire:click="crear">
                    <i class="ti ti-plus me-1"></i> Registrar la primera
                  </button>
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    @if($ocupaciones->hasPages())
      <div class="card-footer border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 py-3">
        <span class="text-black small">
          Mostrando {{ $ocupaciones->firstItem() }} a {{ $ocupaciones->lastItem() }} de {{ $ocupaciones->total() }} registros
        </span>
        <div class="pagination-clean">
          {{ $ocupaciones->links() }}
        </div>
      </div>
    @endif
  </div>

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

  <!-- Offcanvas para Crear / Editar Ocupación -->
  <div 
    class="offcanvas offcanvas-end" 
    tabindex="-1" 
    id="offcanvasOcupacion" 
    wire:ignore.self 
    aria-labelledby="offcanvasOcupacionLabel"
  >
    <form wire:submit.prevent="guardar">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasOcupacionLabel" class="offcanvas-title fw-semibold text-primary">
          <i class="ti {{ $isEditing ? 'ti-pencil' : 'ti-plus' }} me-2"></i>
          {{ $isEditing ? 'Editar ocupación' : 'Nueva ocupación' }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body my-auto mx-0 flex-grow-0 p-4">
        <div class="mb-4">
          <label class="form-label fw-semibold" for="ocupacionNombre">
            Nombre de la ocupación 
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-briefcase"></i></span>
            <input 
              type="text" 
              id="ocupacionNombre" 
              class="form-control @error('nombre') is-invalid @enderror" 
              placeholder="Ej. Empleado, Comerciante, Estudiante..." 
              wire:model="nombre"
              autocomplete="off"
            >
          </div>
          @error('nombre')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <small class="text-muted d-block mt-1">
            Indica el nombre descriptivo de la ocupación u oficio.
          </small>
        </div>
      </div>

      <div class="offcanvas-footer d-flex align-items-center justify-content-end gap-3 p-4 border-top">
        <button type="button" class="btn btn-label-secondary rounded-pill px-3" data-bs-dismiss="offcanvas">
          Cancelar
        </button>
        <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="guardar">
            <i class="ti ti-device-floppy me-1"></i> {{ $isEditing ? 'Guardar' : 'Crear ocupación' }}
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
  let offcanvasElement = document.getElementById('offcanvasOcupacion');
  let offcanvasInstance = null;

  if (offcanvasElement) {
    offcanvasInstance = new bootstrap.Offcanvas(offcanvasElement);
  }

  // Eventos de control del Offcanvas
  Livewire.on('abrir-offcanvas-ocupacion', () => {
    if (offcanvasInstance) {
      offcanvasInstance.show();
      setTimeout(() => {
        const input = document.getElementById('ocupacionNombre');
        if (input) input.focus();
      }, 300);
    }
  });

  Livewire.on('cerrar-offcanvas-ocupacion', () => {
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
        text: `La ocupación "${nombre}" tiene ${totalUsuarios} usuario(s) vinculado(s). No puede ser eliminada.`,
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
      text: `Se eliminará la ocupación "${nombre}". Esta acción puede revertirse posteriormente.`,
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
