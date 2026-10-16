<div>
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Tareas de consolidación</h4>
      </div>
      <p class="text-black mb-0 small">
        Administra las tareas del flujo de seguimiento, consolidación y atención para nuevos creyentes y miembros.
      </p>
    </div>

    <div>
      <button type="button" class="btn btn-primary rounded-pill shadow-sm" wire:click="crear">
        <i class="ti ti-plus me-1"></i> Nueva tarea
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
              placeholder="Buscar por nombre o descripción..." 
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
          Total de registros: <strong class="text-dark">{{ $tareas->total() }}</strong>
        </div>
      </div>
    </div>

    <!-- Tabla de Tareas de Consolidación -->
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4" style="width: 70px;">#</th>
            
            <th class="text-center cursor-pointer user-select-none" style="width: 110px;" wire:click="sortBy('orden')" title="Ordenar por Orden">
              <div class="d-inline-flex align-items-center justify-content-center gap-1">
                <span>Orden</span>
                @if($sortField === 'orden')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="cursor-pointer user-select-none" wire:click="sortBy('nombre')" title="Ordenar por Nombre de Tarea">
              <div class="d-inline-flex align-items-center gap-1">
                <span>Tarea</span>
                @if($sortField === 'nombre')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="text-center cursor-pointer user-select-none" style="width: 170px;" wire:click="sortBy('default')" title="Ordenar por Tarea por defecto">
              <div class="d-inline-flex align-items-center justify-content-center gap-1">
                <span>¿Por defecto?</span>
                @if($sortField === 'default')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="text-center user-select-none" style="width: 160px;">
              <span>Roles autorizados</span>
            </th>

            <th class="text-center cursor-pointer user-select-none" style="width: 180px;" wire:click="sortBy('usuarios_count')" title="Ordenar por Asignaciones">
              <div class="d-inline-flex align-items-center justify-content-center gap-1">
                <span>Asignaciones</span>
                @if($sortField === 'usuarios_count')
                  <i class="ti {{ $sortDirection === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down' }} text-primary ti-xs"></i>
                @else
                  <i class="ti ti-arrows-sort text-muted ti-xs opacity-50"></i>
                @endif
              </div>
            </th>

            <th class="cursor-pointer user-select-none" style="width: 190px;" wire:click="sortBy('created_at')" title="Ordenar por Fecha de creación">
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
          @forelse($tareas as $tarea)
            <tr>
              <td class="ps-4 text-muted small">
                {{ $loop->iteration + ($tareas->currentPage() - 1) * $tareas->perPage() }}
              </td>
              <td class="text-center">
                <span class="badge bg-label-secondary px-2 py-1 fs-tiny rounded-pill fw-semibold">
                  #{{ $tarea->orden ?? '0' }}
                </span>
              </td>
              <td>
                <div class="d-flex flex-column">
                  <span class="fw-semibold text-dark">{{ $tarea->nombre }}</span>
                  @if(!empty($tarea->descripcion))
                    <small class="text-muted text-truncate" style="max-width: 320px;" title="{{ $tarea->descripcion }}">
                      {{ $tarea->descripcion }}
                    </small>
                  @endif
                </div>
              </td>
              <td class="text-center">
                <button 
                  type="button" 
                  class="btn btn-sm py-1 px-3 rounded-pill {{ $tarea->default ? 'btn-label-primary' : 'btn-label-secondary' }}"
                  wire:click="toggleDefault({{ $tarea->id }})"
                  title="Clic para cambiar estado por defecto"
                >
                  <i class="ti {{ $tarea->default ? 'ti-check' : 'ti-x' }} ti-xs me-1"></i>
                  {{ $tarea->default ? 'Sí' : 'No' }}
                </button>
              </td>
              <td class="text-center">
                @if($tarea->todos_los_roles)
                  <span class="badge bg-label-success rounded-pill px-2 py-1">
                    <i class="ti ti-world ti-xs me-1"></i> Todos
                  </span>
                @else
                  <span
                    class="badge bg-label-warning rounded-pill px-2 py-1"
                    title="{{ $tarea->roles->pluck('name')->implode(', ') }}"
                  >
                    <i class="ti ti-lock ti-xs me-1"></i> {{ $tarea->roles->count() }} rol(es)
                  </span>
                @endif
              </td>
              <td class="text-center text-black">
                @if($tarea->usuarios_count > 0)
                  {{ $tarea->usuarios_count }}
                @else
                  0 
                @endif
              </td>
              <td class="text-black small">
                {{ $tarea->created_at ? $tarea->created_at->format('Y-m-d h:i A') : 'N/A' }}
              </td>
              <td class="text-center pe-4">
                <div class="d-inline-flex gap-1">
                  <button 
                    type="button" 
                    class="btn btn-icon btn-sm btn-primary rounded-pill" 
                    wire:click="editar({{ $tarea->id }})"
                    title="Editar tarea de consolidación"
                  >
                    <i class="ti ti-pencil ti-xs"></i>
                  </button>

                  <button 
                    type="button" 
                    class="btn btn-icon btn-sm btn-danger rounded-pill" 
                    onclick="confirmarEliminacion({{ $tarea->id }}, '{{ addslashes($tarea->nombre) }}', {{ $tarea->usuarios_count }})"
                    title="Eliminar tarea de consolidación"
                  >
                    <i class="ti ti-trash ti-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                  <i class="ti ti-list-check ti-lg text-white"></i>
                </div>
                <h6 class="fw-semibold mb-1 text-dark">No se encontraron tareas de consolidación</h6>
                <p class="text-black small mb-3">
                  @if(!empty($search))
                    No hay resultados para la búsqueda "<strong class="text-primary">{{ $search }}</strong>".
                  @else
                    Aún no has registrado ninguna tarea de consolidación en el sistema.
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
    @if($tareas->hasPages())
      <div class="card-footer border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 py-3">
        <span class="text-black small">
          Mostrando {{ $tareas->firstItem() }} a {{ $tareas->lastItem() }} de {{ $tareas->total() }} registros
        </span>
        <div class="pagination-clean">
          {{ $tareas->links() }}
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

  <!-- Offcanvas para Crear / Editar Tarea de Consolidación -->
  <div 
    class="offcanvas offcanvas-end" 
    tabindex="-1" 
    id="offcanvasTareaConsolidacion" 
    data-bs-backdrop="true"
    data-bs-scroll="false"
    wire:ignore.self 
    aria-labelledby="offcanvasTareaConsolidacionLabel"
    x-data="{
      todosLosRoles: @entangle('todosLosRoles'),
      busquedaRol: ''
    }"
  >
    <form wire:submit.prevent="guardar" class="d-flex flex-column h-100 m-0 overflow-hidden">
      <div class="offcanvas-header border-bottom flex-shrink-0">
        <h5 id="offcanvasTareaConsolidacionLabel" class="offcanvas-title fw-semibold text-primary">
          <i class="ti {{ $isEditing ? 'ti-pencil' : 'ti-plus' }} me-2"></i>
          {{ $isEditing ? 'Editar tarea de consolidación' : 'Nueva tarea de consolidación' }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body flex-grow-1 overflow-y-auto p-4">
        <!-- Nombre -->
        <div class="mb-3">
          <label class="form-label fw-semibold" for="tareaNombre">
            Nombre de la tarea <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-list-check"></i></span>
            <input 
              type="text" 
              id="tareaNombre" 
              class="form-control @error('nombre') is-invalid @enderror" 
              placeholder="Ej. Llamada de bienvenida, Visita pastoral..." 
              wire:model="nombre"
              maxlength="50"
              autocomplete="off"
            >
          </div>
          @error('nombre')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <small class="text-muted d-block mt-1">
            Máximo 50 caracteres.
          </small>
        </div>

        <!-- Descripción -->
        <div class="mb-3">
          <label class="form-label fw-semibold" for="tareaDescripcion">
            Descripción
          </label>
          <textarea 
            id="tareaDescripcion" 
            class="form-control @error('descripcion') is-invalid @enderror" 
            rows="3" 
            placeholder="Detalles sobre el propósito o pasos de esta tarea..."
            wire:model="descripcion"
            maxlength="200"
          ></textarea>
          @error('descripcion')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <small class="text-muted d-block mt-1">
            Opcional (máximo 200 caracteres).
          </small>
        </div>

        <!-- Orden -->
        <div class="mb-4">
          <label class="form-label fw-semibold" for="tareaOrden">
            Orden de ejecución / secuencia
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-sort-ascending-numbers"></i></span>
            <input 
              type="number" 
              id="tareaOrden" 
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
            Define la posición en que se sugerirá o listará esta tarea.
          </small>
        </div>

        <!-- Switch Default -->
        <div class="mb-4">
          <div class="form-check form-switch ps-0">
            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border bg-light">
              <div>
                <label class="form-check-label fw-semibold text-dark d-block mb-1 cursor-pointer" for="switchDefault">
                  ¿Es tarea por defecto?
                </label>
                <small class="text-muted d-block">
                  Las tareas por defecto se asignan automáticamente a los nuevos procesos de consolidación.
                </small>
              </div>
              <input 
                class="form-check-input ms-3 fs-4 cursor-pointer"
                type="checkbox" 
                id="switchDefault" 
                wire:model="default"
              >
            </div>
          </div>
        </div>

        <!-- Switch Todos los Roles -->
        <div class="mb-4">
          <div class="form-check form-switch ps-0">
            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border bg-light">
              <div>
                <label class="form-check-label fw-semibold text-dark d-block mb-1 cursor-pointer" for="switchTodosLosRoles">
                  ¿Disponible para todos los roles?
                </label>
                <small class="text-muted d-block">
                  Si está marcado, cualquier consolidador podrá ver y gestionar esta tarea. Si se desmarca, solo los roles seleccionados tendrán acceso.
                </small>
              </div>
              <input
                class="form-check-input ms-3 fs-4 cursor-pointer"
                type="checkbox"
                id="switchTodosLosRoles"
                x-model="todosLosRoles"
              >
            </div>
          </div>
        </div>

        <!-- Bloque de Selección de Roles Específicos -->
        <div class="mb-4 border rounded-3 p-3 bg-white shadow-sm" x-show="!todosLosRoles" x-cloak x-transition>
          <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2 pb-2 border-bottom">
            <div>
              <label class="form-label fw-semibold text-dark mb-0">
                Roles autorizados <span class="text-danger">*</span>
              </label>
              <small class="text-muted d-block" style="font-size: 0.75rem;">
                Selecciona qué roles pueden ver y gestionar esta tarea.
              </small>
            </div>
            <div class="d-flex gap-1">
              <button
                type="button"
                class="btn btn-xs btn-label-primary rounded-pill px-2"
                @click="$wire.set('rolesSeleccionados', {{ json_encode($todosLosRolesLista->pluck('id')->map(fn($id)=>(int)$id)->toArray()) }})"
              >
                Todos
              </button>
              <button
                type="button"
                class="btn btn-xs btn-label-secondary rounded-pill px-2"
                @click="$wire.set('rolesSeleccionados', [])"
              >
                Limpiar
              </button>
            </div>
          </div>

          <!-- Buscador rápido de roles en cliente -->
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text bg-light"><i class="ti ti-search ti-xs"></i></span>
            <input
              type="text"
              class="form-control form-control-sm"
              placeholder="Filtrar rol por nombre..."
              x-model="busquedaRol"
            >
          </div>

          <!-- Listado de roles con checkboxes -->
          <div class="roles-checkbox-container p-2 border rounded-2 bg-light" style="max-height: 240px; overflow-y: auto;">
            <div class="row g-2">
              @forelse($todosLosRolesLista as $rol)
                <div
                  class="col-12 col-sm-6"
                  x-show="!busquedaRol || '{{ strtolower(addslashes($rol->name)) }}'.includes(busquedaRol.toLowerCase())"
                >
                  <div class="form-check">
                    <input
                      class="form-check-input rol-check-input cursor-pointer"
                      type="checkbox"
                      value="{{ $rol->id }}"
                      id="rol_chk_{{ $rol->id }}"
                      wire:model="rolesSeleccionados"
                    >
                    <label class="form-check-label text-dark small text-truncate d-block cursor-pointer" for="rol_chk_{{ $rol->id }}" title="{{ $rol->name }}">
                      {{ $rol->name }}
                    </label>
                  </div>
                </div>
              @empty
                <div class="col-12 text-center py-2 text-muted small">
                  No hay roles registrados en el sistema.
                </div>
              @endforelse
            </div>
          </div>
          @error('rolesSeleccionados')
            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="offcanvas-footer flex-shrink-0 d-flex align-items-center justify-content-end gap-3 p-4 border-top bg-white">
        <button type="button" class="btn btn-label-secondary rounded-pill px-3" data-bs-dismiss="offcanvas">
          Cancelar
        </button>
        <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="guardar">
            <i class="ti ti-device-floppy me-1"></i> {{ $isEditing ? 'Guardar' : 'Crear tarea' }}
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
  const getOffcanvasInstance = () => {
    const el = document.getElementById('offcanvasTareaConsolidacion');
    if (!el) return null;
    return (window.bootstrap || bootstrap).Offcanvas.getOrCreateInstance(el, {
      backdrop: true,
      keyboard: true,
      scroll: false
    });
  };

  Livewire.on('abrir-offcanvas-tarea', () => {
    const inst = getOffcanvasInstance();
    if (inst) {
      inst.show();
      setTimeout(() => {
        const input = document.getElementById('tareaNombre');
        if (input) input.focus();
      }, 300);
    }
  });

  Livewire.on('cerrar-offcanvas-tarea', () => {
    const el = document.getElementById('offcanvasTareaConsolidacion');
    if (el) {
      const inst = (window.bootstrap || bootstrap).Offcanvas.getInstance(el);
      if (inst) {
        inst.hide();
      }
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
        text: `La tarea "${nombre}" tiene ${totalUsuarios} asignación(es) o usuario(s) vinculado(s). No puede ser eliminada.`,
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
      text: `Se eliminará la tarea de consolidación "${nombre}". Esta acción no se podrá deshacer si no cuenta con papelera.`,
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
