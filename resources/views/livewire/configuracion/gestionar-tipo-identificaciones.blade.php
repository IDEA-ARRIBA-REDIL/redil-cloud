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
              class="form-control border-start-0 ps-1" 
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
              class="btn btn-xs rounded-pill {{ $sortField === 'abreviatura' ? 'btn-primary' : 'btn-label-secondary' }}"
              wire:click="sortBy('abreviatura')"
            >
              Abreviatura
              @if($sortField === 'abreviatura')
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
            Total de registros: <strong class="text-black">{{ $tipoIdentificaciones->total() }}</strong>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Listado de Cards de Tipos de Identificación -->
  <div class="row g-4 mb-4">
    @forelse($tipoIdentificaciones as $tipo)
      <div class="col-12 col-md-6 col-lg-4" wire:key="tipo-identificacion-card-{{ $tipo->id }}">
        <div class="card h-100 border-0 shadow-sm {{ !($tipo->activo ?? true) ? 'opacity-75' : '' }}">
          <div class="card-body d-flex flex-column justify-content-between p-4">
            <div>
              <!-- Encabezado de la Card -->
              <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <div>
                  <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <span class="badge bg-label-primary rounded-pill px-2 py-1 fw-semibold" title="Orden de visualización">
                      #{{ $tipo->orden ?? 0 }}
                    </span>
                    <span class="badge bg-label-secondary rounded-pill px-2 font-monospace" title="Abreviatura">
                      {{ $tipo->abreviatura }}
                    </span>
                  </div>
                  <h5 class="card-title text-black mb-0 fw-bold">{{ $tipo->nombre }}</h5>
                </div>

                <!-- Acciones -->
                <div class="d-inline-flex gap-1 flex-shrink-0">
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
              </div>

              <!-- Formato / Restricción -->
              <div class="d-flex align-items-center gap-1 flex-wrap mb-3">
                @if(($tipo->tipo_caracteres ?? 'solo_numeros') === 'solo_numeros')
                  <span class="badge bg-label-info rounded-pill px-2 py-1" title="Solo acepta dígitos numéricos (0-9)">
                    <i class="ti ti-numbers ti-xs me-1"></i> Solo números
                  </span>
                @elseif($tipo->tipo_caracteres === 'alfanumerico')
                  <span class="badge bg-label-primary rounded-pill px-2 py-1" title="Acepta letras y números (A-Z, 0-9)">
                    <i class="ti ti-letter-case ti-xs me-1"></i> Alfanumérico
                  </span>
                @else
                  <span class="badge bg-label-warning rounded-pill px-2 py-1" title="Acepta letras, números y símbolos">
                    <i class="ti ti-regex ti-xs me-1"></i> Letras, números y símbolos
                  </span>
                @endif

                @if($tipo->forzar_mayusculas ?? true)
                  <span class="badge bg-label-dark rounded-pill px-2 py-1" title="Convierte automáticamente a mayúsculas">
                    <i class="ti ti-arrow-up-circle ti-xs me-1"></i>ABC
                  </span>
                @endif
              </div>

              <!-- Métricas -->
              <div class="p-3 bg-light rounded-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-black small">
                    <i class="ti ti-users ti-xs text-primary me-1"></i> Usuarios vinculados:
                  </span>
                  <span class="fw-bold text-black">{{ $tipo->usuarios_count > 0 ? $tipo->usuarios_count : 0 }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-black small">
                    <i class="ti ti-calendar ti-xs text-muted me-1"></i> Creado:
                  </span>
                  <span class="text-muted small">{{ $tipo->created_at ? $tipo->created_at->format('d/m/Y') : 'N/A' }}</span>
                </div>
              </div>
            </div>

            <!-- Badges de estado e interruptores rápidos -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
              <!-- Estado Activo / Inactivo -->
              <button 
                type="button" 
                class="btn btn-xs py-1 px-3 rounded-pill {{ ($tipo->activo ?? true) ? 'btn-label-success' : 'btn-label-secondary' }}"
                wire:click="toggleActivo({{ $tipo->id }})"
                title="Clic para cambiar estado activo/inactivo"
              >
                <i class="ti {{ ($tipo->activo ?? true) ? 'ti-circle-check' : 'ti-circle-x' }} ti-xs me-1"></i>
                {{ ($tipo->activo ?? true) ? 'Activo' : 'Inactivo' }}
              </button>

              <!-- Estado Donaciones -->
              <button 
                type="button" 
                class="btn btn-xs py-1 px-3 rounded-pill {{ $tipo->formulario_donacion ? 'btn-label-info' : 'btn-label-secondary' }}"
                wire:click="toggleDonacion({{ $tipo->id }})"
                title="Clic para cambiar estado de donación"
              >
                <i class="ti {{ $tipo->formulario_donacion ? 'ti-check' : 'ti-x' }} ti-xs me-1"></i>
                Donaciones: {{ $tipo->formulario_donacion ? 'Sí' : 'No' }}
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
          </div>
        </div>
      </div>
    @endforelse
  </div>

  <!-- Paginación -->
  @if($tipoIdentificaciones->hasPages())
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 py-3">
        <span class="text-black small">
          Mostrando {{ $tipoIdentificaciones->firstItem() }} a {{ $tipoIdentificaciones->lastItem() }} de {{ $tipoIdentificaciones->total() }} registros
        </span>
        <div class="pagination-clean">
          {{ $tipoIdentificaciones->links() }}
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
    data-bs-backdrop="true"
    data-bs-scroll="false"
    wire:ignore.self 
    aria-labelledby="offcanvasTipoIdentificacionLabel"
  >
    <form wire:submit.prevent="guardar" class="d-flex flex-column h-100 m-0 overflow-hidden">
      <div class="offcanvas-header border-bottom flex-shrink-0">
        <h5 id="offcanvasTipoIdentificacionLabel" class="offcanvas-title fw-semibold text-primary">
          <i class="ti {{ $isEditing ? 'ti-pencil' : 'ti-plus' }} me-2"></i>
          {{ $isEditing ? 'Editar tipo de identificación' : 'Nuevo tipo de identificación' }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body flex-grow-1 overflow-y-auto p-4">
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

        <!-- Abreviatura y Orden -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6">
            <label class="form-label fw-semibold" for="tipoAbreviatura">
              Abreviatura <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="ti ti-letter-case"></i></span>
              <input 
                type="text" 
                id="tipoAbreviatura" 
                class="form-control font-monospace @error('abreviatura') is-invalid @enderror" 
                placeholder="Ej. CC, TI, CE" 
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
              Sigla corta (máx. 10 letras).
            </small>
          </div>

          <div class="col-12 col-sm-6">
            <label class="form-label fw-semibold" for="tipoOrden">
              Orden <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="ti ti-sort-ascending-numbers"></i></span>
              <input 
                type="number" 
                id="tipoOrden" 
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
              Posición en listados.
            </small>
          </div>
        </div>

        <!-- Restricción de Caracteres -->
        <div class="mb-3">
          <label class="form-label fw-semibold" for="tipoCaracteres">
            Caracteres permitidos en identificación <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-dialpad"></i></span>
            <select 
              id="tipoCaracteres" 
              class="form-select @error('tipo_caracteres') is-invalid @enderror" 
              wire:model="tipo_caracteres"
            >
              <option value="solo_numeros">Solo números (0-9) - Ej. Cédula, Tarjeta de identidad</option>
              <option value="alfanumerico">Letras y números (Alfanumérico) - Ej. Pasaporte</option>
              <option value="alfanumerico_simbolos">Letras, números y símbolos - Ej. Cédula extranjería</option>
            </select>
          </div>
          @error('tipo_caracteres')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <small class="text-muted d-block mt-1">
            Filtra en tiempo real los caracteres que el usuario puede escribir o pegar en el campo de identificación.
          </small>
        </div>

        <!-- Forzar Mayúsculas -->
        <div class="mb-3">
          <label class="form-label fw-semibold d-block mb-2">Formato de texto</label>
          <div class="form-check form-switch card p-3 border shadow-none mb-0">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <label class="form-check-label fw-medium text-black cursor-pointer" for="switchMayusculas">
                  Convertir automáticamente a MAYÚSCULAS
                </label>
                <small class="text-muted d-block">
                  Convierte las letras ingresadas a mayúsculas de forma instantánea al escribir o pegar.
                </small>
              </div>
              <input 
                class="form-check-input ms-0 cursor-pointer" 
                type="checkbox" 
                id="switchMayusculas" 
                wire:model="forzar_mayusculas"
              >
            </div>
          </div>
          @error('forzar_mayusculas')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <!-- Formulario Donación -->
        <div class="mb-3">
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

        <!-- Switch Activo -->
        <div class="mb-4">
          <label class="form-label fw-semibold d-block mb-2">Estado del documento</label>
          <div class="form-check form-switch card p-3 border shadow-none mb-0">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <label class="form-check-label fw-medium text-black cursor-pointer" for="switchActivoIdentificacion">
                  ¿Está activo?
                </label>
                <small class="text-muted d-block">
                  Determina si este tipo de identificación está habilitado y visible en el sistema.
                </small>
              </div>
              <input 
                class="form-check-input ms-0 cursor-pointer" 
                type="checkbox" 
                id="switchActivoIdentificacion" 
                wire:model="activo"
              >
            </div>
          </div>
          @error('activo')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="offcanvas-footer p-4 border-top flex-shrink-0 bg-white">
        <div class="d-flex justify-content-end gap-2">
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
  const getOffcanvasInstance = () => {
    const el = document.getElementById('offcanvasTipoIdentificacion');
    if (!el) return null;
    return (window.bootstrap || bootstrap).Offcanvas.getOrCreateInstance(el, {
      backdrop: true,
      keyboard: true,
      scroll: false
    });
  };

  // Eventos de control del Offcanvas
  Livewire.on('abrir-offcanvas-tipo-identificacion', () => {
    const inst = getOffcanvasInstance();
    if (inst) {
      inst.show();
      setTimeout(() => {
        const input = document.getElementById('tipoNombre');
        if (input) input.focus();
      }, 300);
    }
  });

  Livewire.on('cerrar-offcanvas-tipo-identificacion', () => {
    const el = document.getElementById('offcanvasTipoIdentificacion');
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
