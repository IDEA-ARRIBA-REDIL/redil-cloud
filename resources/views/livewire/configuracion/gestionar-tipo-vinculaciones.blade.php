<div>
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Tipos de vinculación</h4>
      </div>
      <p class="text-black mb-0 small">
        Administra los tipos de vinculación o membresía para clasificar la relación de los usuarios con la iglesia.
      </p>
    </div>

    <div>
      <button type="button" class="btn btn-primary rounded-pill shadow-sm" wire:click="crear">
        <i class="ti ti-plus me-1"></i> Nuevo tipo de vinculación
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
              placeholder="Buscar tipo de vinculación..." 
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
          Total de registros: <strong class="text-dark">{{ $tipoVinculaciones->total() }}</strong>
        </div>
      </div>
    </div>

    <!-- Tabla de Tipos de Vinculación -->
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4" style="width: 70px;">#</th>
            
            <th class="cursor-pointer user-select-none" wire:click="sortBy('nombre')" title="Ordenar por Tipo de vinculación">
              <div class="d-inline-flex align-items-center gap-1">
                <span>Tipo de vinculación</span>
                @if($sortField === 'nombre')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="text-center cursor-pointer user-select-none" style="width: 190px;" wire:click="sortBy('por_grupo')" title="Ordenar por Asignación en grupo">
              <div class="d-inline-flex align-items-center justify-content-center gap-1">
                <span>¿Es por grupo?</span>
                @if($sortField === 'por_grupo')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="text-center cursor-pointer user-select-none" style="width: 190px;" wire:click="sortBy('usuarios_count')" title="Ordenar por Usuarios vinculados">
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
          @forelse($tipoVinculaciones as $tipo)
            <tr>
              <td class="ps-4 text-muted small">
                {{ $loop->iteration + ($tipoVinculaciones->currentPage() - 1) * $tipoVinculaciones->perPage() }}
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-semibold text-dark">{{ $tipo->nombre }}</span>
                </div>
              </td>
              <td class="text-center">
                <button 
                  type="button" 
                  class="btn btn-sm py-1 px-3 rounded-pill {{ $tipo->por_grupo ? 'btn-label-primary' : 'btn-label-secondary' }}"
                  wire:click="togglePorGrupo({{ $tipo->id }})"
                  title="Clic para cambiar estado"
                >
                  <i class="ti {{ $tipo->por_grupo ? 'ti-check' : 'ti-x' }} ti-xs me-1"></i>
                  {{ $tipo->por_grupo ? 'Sí' : 'No' }}
                </button>
              </td>
              <td class="text-center text-black">
                @if($tipo->usuarios_count > 0)
                  {{ $tipo->usuarios_count }}
                @else
                  0 
                @endif
              </td>
              <td class="text-black small">
                {{ $tipo->created_at ? $tipo->created_at->format('Y-m-d h:i A') : 'N/A' }}
              </td>
              <td class="text-center pe-4">
                <div class="d-inline-flex gap-1">
                  <button 
                    type="button" 
                    class="btn btn-icon btn-sm btn-primary rounded-pill" 
                    wire:click="editar({{ $tipo->id }})"
                    title="Editar tipo de vinculación"
                  >
                    <i class="ti ti-pencil ti-xs"></i>
                  </button>

                  <button 
                    type="button" 
                    class="btn btn-icon btn-sm btn-danger rounded-pill" 
                    onclick="confirmarEliminacion({{ $tipo->id }}, '{{ addslashes($tipo->nombre) }}', {{ $tipo->usuarios_count }})"
                    title="Eliminar tipo de vinculación"
                  >
                    <i class="ti ti-trash ti-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5">
                <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                  <i class="ti ti-link-off ti-lg text-white"></i>
                </div>
                <h6 class="fw-semibold mb-1 text-dark">No se encontraron tipos de vinculación</h6>
                <p class="text-black small mb-3">
                  @if(!empty($search))
                    No hay resultados para la búsqueda "<strong class="text-primary">{{ $search }}</strong>".
                  @else
                    Aún no has registrado ningún tipo de vinculación en el sistema.
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
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    @if($tipoVinculaciones->hasPages())
      <div class="card-footer border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 py-3">
        <span class="text-black small">
          Mostrando {{ $tipoVinculaciones->firstItem() }} a {{ $tipoVinculaciones->lastItem() }} de {{ $tipoVinculaciones->total() }} registros
        </span>
        <div class="pagination-clean">
          {{ $tipoVinculaciones->links() }}
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

  <!-- Offcanvas para Crear / Editar Tipo de Vinculación -->
  <div 
    class="offcanvas offcanvas-end" 
    tabindex="-1" 
    id="offcanvasTipoVinculacion" 
    wire:ignore.self 
    aria-labelledby="offcanvasTipoVinculacionLabel"
  >
    <form wire:submit.prevent="guardar">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasTipoVinculacionLabel" class="offcanvas-title fw-semibold text-primary">
          <i class="ti {{ $isEditing ? 'ti-pencil' : 'ti-plus' }} me-2"></i>
          {{ $isEditing ? 'Editar tipo de vinculación' : 'Nuevo tipo de vinculación' }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body my-auto mx-0 flex-grow-0 p-4">
        <!-- Nombre -->
        <div class="mb-4">
          <label class="form-label fw-semibold" for="tipoVinculacionNombre">
            Nombre del tipo de vinculación 
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-link"></i></span>
            <input 
              type="text" 
              id="tipoVinculacionNombre" 
              class="form-control @error('nombre') is-invalid @enderror" 
              placeholder="Ej. Miembro, Asistente, Visitante..." 
              wire:model="nombre"
              maxlength="30"
              autocomplete="off"
            >
          </div>
          @error('nombre')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <small class="text-muted d-block mt-1">
            Máximo 30 caracteres.
          </small>
        </div>

        <!-- Switch Por Grupo -->
        <div class="mb-4">
          <div class="form-check form-switch ps-0">
            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border bg-light">
              <div>
                <label class="form-check-label fw-semibold text-dark d-block mb-1" for="switchPorGrupo">
                  ¿Es por grupo?
                </label>
                <small class="text-muted d-block">
                  Indica si este tipo de vinculación es por grupo.
                </small>
              </div>
              <input 
                class="form-check-input ms-3 fs-4" 
                type="checkbox" 
                id="switchPorGrupo" 
                wire:model="por_grupo"
              >
            </div>
          </div>
        </div>
      </div>

      <div class="offcanvas-footer d-flex align-items-center justify-content-end gap-3 p-4 border-top">
        <button type="button" class="btn btn-label-secondary rounded-pill px-3" data-bs-dismiss="offcanvas">
          Cancelar
        </button>
        <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="guardar">
            <i class="ti ti-device-floppy me-1"></i> {{ $isEditing ? 'Guardar' : 'Crear' }}
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
  let offcanvasElement = document.getElementById('offcanvasTipoVinculacion');
  let offcanvasInstance = null;

  if (offcanvasElement) {
    offcanvasInstance = new bootstrap.Offcanvas(offcanvasElement);
  }

  // Eventos de control del Offcanvas
  Livewire.on('abrir-offcanvas-tipo-vinculacion', () => {
    if (offcanvasInstance) {
      offcanvasInstance.show();
      setTimeout(() => {
        const input = document.getElementById('tipoVinculacionNombre');
        if (input) input.focus();
      }, 300);
    }
  });

  Livewire.on('cerrar-offcanvas-tipo-vinculacion', () => {
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
        text: `El tipo de vinculación "${nombre}" tiene ${totalUsuarios} usuario(s) vinculado(s). No puede ser eliminado.`,
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
      text: `Se eliminará el tipo de vinculación "${nombre}". Esta acción puede revertirse posteriormente.`,
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
