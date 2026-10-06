<div>
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Tipos de identificación</h4>
      </div>
      <p class="text-black mb-0 small">
        Administra los tipos de documentos de identidad y su disponibilidad en formularios de donación.
      </p>
    </div>

    <div>
      <button type="button" class="btn btn-primary rounded-pill shadow-sm" wire:click="crear">
        <i class="ti ti-plus me-1"></i> Nuevo tipo
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
            <span class="input-group-text bg-white  text-muted">
              <i class="ti ti-search"></i>
            </span>
            <input 
              type="text" 
              class="form-control border-start-0  ps-1" 
              placeholder="Buscar por nombre o abreviatura..." 
              wire:model.live.debounce.300ms="search"
            >
            @if(!empty($search))
              <button 
                class="input-group-text bg-white border-start-0 text-muted cursor-pointer" 
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
          Total de registros: <strong class="text-black">{{ $tipoIdentificaciones->total() }}</strong>
        </div>
      </div>
    </div>

    <!-- Tabla de Tipos de Identificación -->
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4" style="width: 70px;">#</th>
            
            <th class="cursor-pointer user-select-none" wire:click="sortBy('nombre')" title="Ordenar por tipo de identificación">
              <div class="d-inline-flex align-items-center gap-1">
                <span>Tipo de identificación</span>
                @if($sortField === 'nombre')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="text-center cursor-pointer user-select-none" style="width: 140px;" wire:click="sortBy('abreviatura')" title="Ordenar por Abreviatura">
              <div class="d-inline-flex align-items-center justify-content-center gap-1">
                <span>Abreviatura</span>
                @if($sortField === 'abreviatura')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="text-center cursor-pointer user-select-none" style="width: 170px;" wire:click="sortBy('formulario_donacion')" title="Ordenar por Donaciones">
              <div class="d-inline-flex align-items-center justify-content-center gap-1">
                <span>Donaciones</span>
                @if($sortField === 'formulario_donacion')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="text-center cursor-pointer user-select-none" style="width: 180px;" wire:click="sortBy('usuarios_count')" title="Ordenar por Usuarios vinculados">
              <div class="d-inline-flex align-items-center justify-content-center gap-1">
                <span>Usuarios Vinculados</span>
                @if($sortField === 'usuarios_count')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="cursor-pointer user-select-none" style="width: 180px;" wire:click="sortBy('created_at')" title="Ordenar por Fecha de creación">
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
          @forelse($tipoIdentificaciones as $tipo)
            <tr>
              <td class="ps-4 text-black small">
                {{ $loop->iteration + ($tipoIdentificaciones->currentPage() - 1) * $tipoIdentificaciones->perPage() }}
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">             
                  <span class="fw-semibold text-black">{{ $tipo->nombre }}</span>
                </div>
              </td>
              <td class="text-center">
                  {{ $tipo->abreviatura }}
              </td>
              <td class="text-center">
                <button 
                  type="button" 
                  class="btn btn-sm p-0 border-0 bg-transparent" 
                  wire:click="toggleDonacion({{ $tipo->id }})"
                  title="Clic para cambiar estado de donación"
                >
                  @if($tipo->formulario_donacion)
                    <span class="badge bg-label-success rounded-pill px-3 cursor-pointer">
                      <i class="ti ti-check ti-xs me-1"></i> Habilitado
                    </span>
                  @else
                    <span class="badge bg-label-secondary rounded-pill px-3 cursor-pointer">
                      <i class="ti ti-x ti-xs me-1"></i> Inactivo
                    </span>
                  @endif
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
                    title="Editar tipo de identificación"
                  >
                    <i class="ti ti-pencil ti-xs"></i>
                  </button>

                  <button 
                    type="button" 
                    class="btn btn-icon btn-sm btn-danger rounded-pill" 
                    onclick="confirmarEliminacionTipo({{ $tipo->id }}, '{{ addslashes($tipo->nombre) }}', {{ $tipo->usuarios_count }})"
                    title="Eliminar tipo de identificación"
                  >
                    <i class="ti ti-trash ti-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                  <i class="ti ti-id-off ti-lg text-white"></i>
                </div>
                <h6 class="fw-semibold mb-1 text-black">No se encontraron tipos de identificación</h6>
                <p class="text-black small mb-3">
                  @if(!empty($search))
                    No hay resultados para la búsqueda "<strong class="text-primary">{{ $search }}</strong>".
                  @else
                    Aún no has registrado ningún tipo de identificación.
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
    @if($tipoIdentificaciones->hasPages())
      <div class="card-footer border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 py-3">
        <span class="text-black small">
          Mostrando {{ $tipoIdentificaciones->firstItem() }} a {{ $tipoIdentificaciones->lastItem() }} de {{ $tipoIdentificaciones->total() }} registros
        </span>
        <div class="pagination-clean">
          {{ $tipoIdentificaciones->links() }}
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
    /* Oculta el texto interno "Mostrando 1 al 12 de ... resultados" */
    .pagination-clean nav > div:last-child > div:first-child {
      display: none !important;
    }
    .pagination-clean .pagination {
      margin-bottom: 0 !important;
    }
  </style>

  <!-- Offcanvas para Crear / Editar Tipo de Identificación -->
  <div 
    class="offcanvas offcanvas-end" 
    tabindex="-1" 
    id="offcanvasTipoIdentificacion" 
    wire:ignore.self 
    aria-labelledby="offcanvasTipoIdentificacionLabel"
  >
    <form wire:submit.prevent="guardar">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasTipoIdentificacionLabel" class="offcanvas-title fw-semibold text-primary">
          <i class="ti {{ $isEditing ? 'ti-pencil' : 'ti-plus' }} me-2"></i>
          {{ $isEditing ? 'Editar tipo de identificación' : 'Nuevo tipo de identificación' }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body my-auto mx-0 flex-grow-0 p-4">
          <!-- Nombre -->
          <div class="mb-3">
            <label class="form-label fw-semibold" for="tipoNombre">
              Nombre del documento <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="ti ti-id"></i></span>
              <input 
                type="text" 
                id="tipoNombre" 
                class="form-control @error('nombre') is-invalid @enderror" 
                placeholder="Ej. Cédula de Ciudadanía, Pasaporte..." 
                wire:model="nombre"
                autocomplete="off"
              >
            </div>
            @error('nombre')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted d-block mt-1">
              Nombre descriptivo y completo del tipo de documento.
            </small>
          </div>

          <!-- Abreviatura -->
          <div class="mb-3">
            <label class="form-label fw-semibold" for="tipoAbreviatura">
              Abreviatura <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="ti ti-letter-case"></i></span>
              <input 
                type="text" 
                id="tipoAbreviatura" 
                class="form-control font-monospace @error('abreviatura') is-invalid @enderror" 
                placeholder="Ej. CC, TI, CE, PAS, NIT" 
                wire:model="abreviatura"
                style="text-transform: uppercase;"
                maxlength="10"
                autocomplete="off"
              >
            </div>
            @error('abreviatura')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted d-block mt-1">
              Sigla o código corto utilizado en tablas y reportes (máx. 10 letras).
            </small>
          </div>

          <!-- Formulario Donación -->
          <div class="mb-4">
            <label class="form-label fw-semibold d-block mb-2">Formularios de donación</label>
            <div class="form-check form-switch card p-3 border shadow-none mb-0">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <label class="form-check-label fw-medium text-black cursor-pointer" for="switchDonacion">
                    Habilitar para Donaciones
                  </label>
                  <small class="text-muted d-block">
                    Permite que los donantes seleccionen este documento al realizar aportes.
                  </small>
                </div>
                <input 
                  class="form-check-input ms-0 cursor-pointer" 
                  type="checkbox" 
                  id="switchDonacion" 
                  wire:model="formulario_donacion"
                >
              </div>
            </div>
            @error('formulario_donacion')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          
        
      </div>
      <div class="offcanvas-footer p-4 border-top">
        <!-- Botones de Acción -->
        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
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
      </div>
    </form>
  </div>
</div>

@script
<script>
  let offcanvasTipoElement = document.getElementById('offcanvasTipoIdentificacion');
  let offcanvasTipoInstance = null;

  if (offcanvasTipoElement) {
    offcanvasTipoInstance = new bootstrap.Offcanvas(offcanvasTipoElement);
  }

  // Eventos de control del Offcanvas
  Livewire.on('abrir-offcanvas-tipo-identificacion', () => {
    if (offcanvasTipoInstance) {
      offcanvasTipoInstance.show();
      setTimeout(() => {
        const input = document.getElementById('tipoNombre');
        if (input) input.focus();
      }, 300);
    }
  });

  Livewire.on('cerrar-offcanvas-tipo-identificacion', () => {
    if (offcanvasTipoInstance) {
      offcanvasTipoInstance.hide();
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
  window.confirmarEliminacionTipo = function(id, nombre, totalUsuarios) {
    if (totalUsuarios > 0) {
      Swal.fire({
        icon: 'warning',
        title: 'No se puede eliminar',
        text: `El tipo de identificación "${nombre}" tiene ${totalUsuarios} usuario(s) vinculado(s). No puede ser eliminado.`,
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
      text: `Se eliminará el tipo de identificación "${nombre}". Esta acción puede revertirse posteriormente.`,
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
