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

    .badge-puntos-gold {
      background-color: #fff9db;
      color: #b7791f;
      border: 1px solid #fef08a;
      font-weight: 700;
      border-radius: 50rem;
      padding: 0.35rem 0.85rem;
      font-size: 0.85rem;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
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

    .btn-action-icon:hover {
      background-color: #f1f5f9;
    }

    .btn-action-icon.btn-action-delete:hover {
      background-color: #fee2e2;
      color: #dc2626;
    }

    .offcanvas-gamificacion {
      width: 500px !important;
      border-left: 1px solid #eef2f6;
    }

    .card-offcanvas-section {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 1.15rem;
    }

    .tipo-toggle-btn {
      border: 1px solid #e2e8f0;
      background-color: #ffffff;
      color: #475569;
      font-weight: 600;
      border-radius: 10px;
      padding: 0.65rem 1rem;
      transition: all 0.2s ease;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      width: 100%;
    }

    .tipo-toggle-btn.active {
      background-color: var(--bs-primary);
      border-color: var(--bs-primary);
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.25);
    }

    .img-product-thumb {
      width: 52px;
      height: 52px;
      border-radius: 10px;
      object-fit: cover;
      border: 1px solid #e2e8f0;
    }

    .img-product-placeholder {
      width: 52px;
      height: 52px;
      border-radius: 10px;
      background-color: #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #64748b;
      border: 1px solid #e2e8f0;
    }

    /* Estilos mejorados para Select2 dentro del Offcanvas */
    .offcanvas-gamificacion .select2-container .select2-selection--multiple {
      min-height: 38px;
      cursor: pointer;
      border-color: #dbdade;
      border-radius: 0.375rem;
    }

    .offcanvas-gamificacion .select2-container--default.select2-container--focus .select2-selection--multiple {
      border-color: var(--bs-primary) !important;
      box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.15);
    }

    .offcanvas-gamificacion .select2-container .select2-search--inline .select2-search__field {
      cursor: text;
      margin-top: 5px;
    }

    /* Estilos para Vista Móvil y Tablet de Solicitudes y Catálogo */
    .mobile-item-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      padding: 1.15rem;
      margin-bottom: 0.85rem;
      transition: all 0.2s ease;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .mobile-item-card:hover {
      border-color: #cbd5e1;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
  </style>

  <!-- ======================================================================= -->
  <!-- TABS DE NAVEGACIÓN ESTILO DASHBOARD GRUPOS -->
  <!-- ======================================================================= -->
  <div class="card mb-4 p-1 border-1">
    <ul class="nav nav-pills justify-content-start flex-column flex-md-row gap-2" role="tablist">
      <li class="nav-item flex-fill">
        <button type="button" class="nav-link p-3 waves-effect waves-light w-100 fw-semibold {{ $tabActivo === 'solicitudes' ? 'active' : '' }}" wire:click="cambiarTab('solicitudes')">
          <span>Solicitudes de canje</span>
          @if($pendientesCount > 0)
            <span class="badge bg-danger text-white rounded-pill ms-2 px-2">{{ $pendientesCount }}</span>
          @endif
        </button>
      </li>
      <li class="nav-item flex-fill">
        <button type="button" class="nav-link p-3 waves-effect waves-light w-100 fw-semibold {{ $tabActivo === 'catalogo' ? 'active' : '' }}" wire:click="cambiarTab('catalogo')">
          <span>Catálogo de productos</span>
        </button>
      </li>
    </ul>
  </div>

  <!-- ======================================================================= -->
  <!-- TARJETA PRINCIPAL DE CONTENIDO -->
  <!-- ======================================================================= -->
  <div class="card gamificacion-admin-card p-4">

    <!-- ===================================================================== -->
    <!-- TAB 1: SOLICITUDES DE CANJE -->
    <!-- ===================================================================== -->
    @if($tabActivo === 'solicitudes')
      <div>
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <h5 class="fw-semibold text-black mb-1">Solicitudes de canje</h5>
            <p class="text-black small mb-0">Revisa y gestiona las peticiones de canje de puntos realizadas por los usuarios.</p>
          </div>
        </div>

        <!-- Barra de Filtros y Búsqueda para Solicitudes -->
        <div class="row g-2 mb-4 align-items-center">
          <div class="col-12 col-md-12 col-lg-4">
            <div class="input-group">
              <span class="input-group-text border-end-0 text-muted"><i class="ti ti-search"></i></span>
              <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar por código, usuario o producto..." wire:model.live.debounce.300ms="busquedaSolicitudes">
              @if(!empty($busquedaSolicitudes))
                <button class="btn btn-outline-secondary border-start-0" type="button" wire:click="$set('busquedaSolicitudes', '')">
                  <i class="ti ti-x"></i>
                </button>
              @endif
            </div>
          </div>

          <div class="col-12 col-sm-6 col-lg-3">
            <select class="form-select" wire:model.live="filtroEstadoSolicitud">
              <option value="">Todos los estados</option>
              <option value="pendiente">Pendientes</option>
              <option value="aprobado">Aprobados / Entregados</option>
              <option value="rechazado">Rechazados</option>
            </select>
          </div>

          <div class="col-12 col-sm-6 col-lg-3">
            <div class="input-group" wire:ignore>
              <span class="input-group-text border-end-0 text-muted"><i class="ti ti-calendar"></i></span>
              <input type="text" id="filtro_fecha_rango" class="form-control border-start-0 ps-0" placeholder="Últimos 30 días" readonly
                x-data
                x-init="
                  if (typeof flatpickr !== 'undefined') {
                    const fp = flatpickr($el, {
                      mode: 'range',
                      dateFormat: 'Y-m-d',
                      defaultDate: [$wire.fechaInicio, $wire.fechaFin],
                      locale: { rangeSeparator: ' a ' },
                      onClose: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length === 2) {
                          $wire.set('fechaInicio', instance.formatDate(selectedDates[0], 'Y-m-d'));
                          $wire.set('fechaFin', instance.formatDate(selectedDates[1], 'Y-m-d'));
                        } else if (selectedDates.length === 0) {
                          $wire.set('fechaInicio', null);
                          $wire.set('fechaFin', null);
                        }
                      }
                    });
                    $wire.on('resetFlatpickr', (data) => {
                      const payload = Array.isArray(data) ? data[0] : data;
                      if (payload && fp) {
                        fp.setDate([payload.inicio, payload.fin]);
                      }
                    });
                  }
                ">
            </div>
          </div>

          <div class="col-12 col-lg-2 text-lg-end">
            <button type="button" class="btn btn-outline-secondary rounded-pill w-100" wire:click="limpiarFiltrosSolicitudes" title="Restablecer filtros a valores por defecto">
              <i class="ti ti-filter-off me-1"></i> Limpiar
            </button>
          </div>
        </div>

        <!-- VISTA ESCRITORIO (TABLA) -->
        <div class="table-responsive d-none d-lg-block">
          <table class="table table-gamificacion align-middle">
            <thead>
              <tr>
                <th style="white-space: nowrap; width: 140px;">FECHA</th>
                <th>USUARIO</th>
                <th>PRODUCTO SOLICITADO</th>
                <th style="white-space: nowrap;">COSTO (PUNTOS)</th>
                <th style="white-space: nowrap;">ESTADO</th>
                <th class="text-end" style="white-space: nowrap; width: 120px;">GESTIÓN</th>
              </tr>
            </thead>
            <tbody>
              @forelse($solicitudes as $solicitud)
                @php
                  $fechaFormateada = '-';
                  if ($solicitud->created_at) {
                      if ($solicitud->created_at->isToday()) {
                          $fechaFormateada = 'Hoy, ' . $solicitud->created_at->format('g:i A');
                      } elseif ($solicitud->created_at->isYesterday()) {
                          $fechaFormateada = 'Ayer, ' . $solicitud->created_at->format('g:i A');
                      } else {
                          $fechaFormateada = $solicitud->created_at->format('d M, Y g:i A');
                      }
                  }
                  $esDigital = ($solicitud->producto->tipo ?? 'fisico') === 'digital';
                @endphp
                <tr wire:key="solicitud-desk-{{ $solicitud->id }}">
                  <!-- 1. Fecha -->
                  <td style="white-space: nowrap;">
                    <span class="text-black small fw-medium">{{ $fechaFormateada }}</span>
                  </td>

                  <!-- 2. Usuario -->
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm me-2">
                        @if($solicitud->user && $solicitud->user->foto && $solicitud->user->foto !== 'default-m.png' && $solicitud->user->foto !== 'default-f.png')
                          <img src="{{ $solicitud->user->foto_url }}" alt="Avatar" class="rounded-circle">
                        @else
                          <span class="avatar-initial rounded-circle bg-label-secondary text-white fw-semibold" style="background-color: #e2e8f0; color: #475569;">
                            {{ $solicitud->user ? $solicitud->user->inicialesNombre() : 'U' }}
                          </span>
                        @endif
                      </div>
                      <span class="fw-semibold text-black">{{ $solicitud->user ? $solicitud->user->nombre(3) : 'Usuario' }}</span>
                    </div>
                  </td>

                  <!-- 3. Producto Solicitado -->
                  <td>
                    <div class="d-flex flex-column">
                      <span class="fw-semibold text-black fs-6">{{ $solicitud->producto->nombre ?? 'Producto eliminado' }}</span>
                      <div class="d-flex align-items-center gap-1 mt-1">
                        @if($esDigital)
                          <small class="text-info d-inline-flex align-items-center gap-1 fw-medium" style="font-size: 0.78rem;">
                            <i class="ti ti-cloud fs-6"></i> Digital
                          </small>
                        @else
                          <small class="text-success d-inline-flex align-items-center gap-1 fw-medium" style="font-size: 0.78rem;">
                            <i class="ti ti-gift fs-6"></i> Físico
                          </small>
                        @endif
                      </div>
                    </div>
                  </td>

                  <!-- 4. Costo (Puntos) -->
                  <td style="white-space: nowrap;">
                    <span class="text-danger fw-bold fs-6">
                      - {{ number_format($solicitud->puntos_gastados) }} pts
                    </span>
                  </td>

                  <!-- 5. Estado -->
                  <td style="white-space: nowrap;">
                    @if($solicitud->estado === 'pendiente')
                      <span class="badge rounded-pill bg-warning text-dark px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="ti ti-clock-hour-4 fs-6"></i> Pendiente
                      </span>
                    @elseif($solicitud->estado === 'aprobado' || $solicitud->estado === 'entregado')
                      <span class="badge rounded-pill bg-success text-white px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="ti ti-check fs-6"></i> Entregado
                      </span>
                    @elseif($solicitud->estado === 'rechazado')
                      <span class="badge rounded-pill bg-danger text-white px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="ti ti-x fs-6"></i> Rechazado
                      </span>
                    @else
                      <span class="badge rounded-pill bg-secondary text-white px-3 py-2 fw-semibold">{{ ucfirst($solicitud->estado) }}</span>
                    @endif
                  </td>

                  <!-- 6. Gestión -->
                  <td class="text-end" style="white-space: nowrap;">
                    @if($solicitud->estado === 'pendiente')
                      <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Gestionar solicitud">
                          <i class="ti ti-dots fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                          <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 text-success py-2" href="javascript:void(0);" onclick="confirmarAprobarSolicitud({{ $solicitud->id }}, '{{ $solicitud->codigo_canje }}', '{{ addslashes($solicitud->user ? $solicitud->user->nombre(3) : 'Usuario') }}')">
                              <i class="ti ti-check fs-5"></i>
                              <span>Aprobar y Entregar</span>
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger py-2" href="javascript:void(0);" onclick="confirmarRechazarSolicitud({{ $solicitud->id }}, '{{ $solicitud->codigo_canje }}', '{{ addslashes($solicitud->user ? $solicitud->user->nombre(3) : 'Usuario') }}')">
                              <i class="ti ti-x fs-5"></i>
                              <span>Rechazar y Reembolsar</span>
                            </a>
                          </li>
                        </ul>
                      </div>
                    @else
                       <div class="dropdown d-inline-block">
                        <button class="btn disabled btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Gestionar solicitud">
                          <i class="ti ti-dots fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        </ul>
                      </div>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-black">
                    <i class="ti ti-inbox fs-2 mb-2 d-block"></i>
                    No se encontraron solicitudes de canje registradas con los filtros actuales.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- VISTA MÓVIL Y TABLET (TARJETAS COMPACTAS) -->
        <div class="d-block d-lg-none">
          @forelse($solicitudes as $solicitud)
            @php
              $fechaFormateadaMob = '-';
              if ($solicitud->created_at) {
                  if ($solicitud->created_at->isToday()) {
                      $fechaFormateadaMob = 'Hoy, ' . $solicitud->created_at->format('g:i A');
                  } elseif ($solicitud->created_at->isYesterday()) {
                      $fechaFormateadaMob = 'Ayer, ' . $solicitud->created_at->format('g:i A');
                  } else {
                      $fechaFormateadaMob = $solicitud->created_at->format('d M, Y g:i A');
                  }
              }
              $esDigitalMob = ($solicitud->producto->tipo ?? 'fisico') === 'digital';
            @endphp
            <div class="mobile-item-card" wire:key="solicitud-mob-{{ $solicitud->id }}">
              <!-- Cabecera de la tarjeta: Fecha y Estado -->
              <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                <span class="text-black small fw-medium">{{ $fechaFormateadaMob }}</span>
                @if($solicitud->estado === 'pendiente')
                  <span class="badge rounded-pill bg-warning text-dark px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                    <i class="ti ti-clock-hour-4 fs-6"></i> Pendiente
                  </span>
                @elseif($solicitud->estado === 'aprobado' || $solicitud->estado === 'entregado')
                  <span class="badge rounded-pill bg-success text-white px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                    <i class="ti ti-check fs-6"></i> Entregado
                  </span>
                @elseif($solicitud->estado === 'rechazado')
                  <span class="badge rounded-pill bg-danger text-white px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                    <i class="ti ti-x fs-6"></i> Rechazado
                  </span>
                @else
                  <span class="badge rounded-pill bg-secondary text-white px-2 py-1 fw-semibold" style="font-size: 0.75rem;">{{ ucfirst($solicitud->estado) }}</span>
                @endif
              </div>

              <!-- Fila Usuario -->
              <div class="d-flex align-items-center mb-2">
                <div class="avatar avatar-sm me-2">
                  @if($solicitud->user && $solicitud->user->foto && $solicitud->user->foto !== 'default-m.png' && $solicitud->user->foto !== 'default-f.png')
                    <img src="{{ $solicitud->user->foto_url }}" alt="Avatar" class="rounded-circle">
                  @else
                    <span class="avatar-initial rounded-circle bg-label-secondary text-white fw-semibold" style="background-color: #e2e8f0; color: #475569;">
                      {{ $solicitud->user ? $solicitud->user->inicialesNombre() : 'U' }}
                    </span>
                  @endif
                </div>
                <span class="fw-semibold text-dark">{{ $solicitud->user ? $solicitud->user->nombre(3) : 'Usuario' }}</span>
              </div>

              <!-- Fila Producto y Puntos -->
              <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <div>
                  <span class="fw-semibold text-dark d-block fs-6">{{ $solicitud->producto->nombre ?? 'Producto eliminado' }}</span>
                  <div class="d-flex align-items-center gap-1 mt-1">
                    @if($esDigitalMob)
                      <small class="text-info d-inline-flex align-items-center gap-1 fw-medium" style="font-size: 0.78rem;">
                        <i class="ti ti-cloud fs-6"></i> Digital
                      </small>
                    @else
                      <small class="text-success d-inline-flex align-items-center gap-1 fw-medium" style="font-size: 0.78rem;">
                        <i class="ti ti-gift fs-6"></i> Físico
                      </small>
                    @endif
                  </div>
                </div>

                <div class="text-end">
                  <span class="text-danger fw-bold fs-6">
                    - {{ number_format($solicitud->puntos_gastados) }} pts
                  </span>
                </div>
              </div>

              <!-- Opciones / Acciones -->
              <div class="pt-2 border-top">
                @if($solicitud->estado === 'pendiente')
                  <div class="row g-2">
                    <div class="col-6">
                      <button type="button" class="btn btn-sm btn-success w-100 py-1" onclick="confirmarAprobarSolicitud({{ $solicitud->id }}, '{{ $solicitud->codigo_canje }}', '{{ addslashes($solicitud->user ? $solicitud->user->nombre(3) : 'Usuario') }}')">
                        <i class="ti ti-check me-1"></i> Aprobar
                      </button>
                    </div>
                    <div class="col-6">
                      <button type="button" class="btn btn-sm btn-outline-danger w-100 py-1" onclick="confirmarRechazarSolicitud({{ $solicitud->id }}, '{{ $solicitud->codigo_canje }}', '{{ addslashes($solicitud->user ? $solicitud->user->nombre(3) : 'Usuario') }}')">
                        <i class="ti ti-x me-1"></i> Rechazar
                      </button>
                    </div>
                  </div>
                @else
                  <div class="text-center text-muted small py-1">
                    <i class="ti ti-ban me-1"></i> Cerrado
                  </div>
                @endif
              </div>
            </div>
          @empty
            <div class="text-center py-5 text-black border rounded-3 bg-light">
              <i class="ti ti-inbox fs-2 mb-2 d-block text-muted"></i>
              No se encontraron solicitudes de canje registradas con los filtros actuales.
            </div>
          @endforelse
        </div>
      </div>
    @endif

    <!-- ===================================================================== -->
    <!-- TAB 2: CATÁLOGO DE PRODUCTOS -->
    <!-- ===================================================================== -->
    @if($tabActivo === 'catalogo')
      <div>
        <!-- Encabezado de Catálogo y Botón Nuevo -->
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4">
          <div>
            <h5 class="fw-semibold text-black mb-1">Catálogo de productos</h5>
            <p class="text-black small mb-0">Premios físicos y digitales canjeables por puntos.</p>
          </div>
          <div>
            <button type="button" class="btn btn-primary rounded-pill d-inline-flex align-items-center gap-1 shadow-sm w-100 w-sm-auto justify-content-center" wire:click="abrirModalProducto()">
              <i class="ti ti-plus"></i>
              <span>Nuevo producto</span>
            </button>
          </div>
        </div>

        <!-- Buscador y Filtros de Productos -->
        <div class="row g-2 mb-4 align-items-center">
          <div class="col-12 col-md-6 col-lg-6">
            <div class="input-group">
              <span class="input-group-text border-end-0 text-muted"><i class="ti ti-search"></i></span>
              <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar por nombre o descripción de producto..." wire:model.live.debounce.300ms="busquedaProductos">
              @if(!empty($busquedaProductos))
                <button class="btn btn-outline-secondary border-start-0" type="button" wire:click="$set('busquedaProductos', '')">
                  <i class="ti ti-x"></i>
                </button>
              @endif
            </div>
          </div>

          <div class="col-12 col-sm-6 col-lg-3">
            <select class="form-select" wire:model.live="filtroTipoProducto">
              <option value="">Todos los tipos</option>
              <option value="fisico">📦 Físicos</option>
              <option value="digital">☁️ Digitales</option>
            </select>
          </div>

          <div class="col-12 col-sm-6 col-lg-3 text-lg-end">
            @if(!empty($busquedaProductos) || !empty($filtroTipoProducto))
              <button type="button" class="btn btn-outline-secondary rounded-pill w-100" wire:click="limpiarFiltrosProductos">
                <i class="ti ti-filter-off me-1"></i> Limpiar filtros
              </button>
            @endif
          </div>
        </div>

        <!-- VISTA ESCRITORIO (TABLA DE PRODUCTOS) -->
        <div class="table-responsive d-none d-lg-block">
          <table class="table table-gamificacion">
            <thead>
              <tr>
                <th style="width: 70px;">IMAGEN</th>
                <th>DETALLES DEL PRODUCTO</th>
                <th style="white-space: nowrap;">COSTO</th>
                <th style="white-space: nowrap;">STOCK</th>
                <th class="text-end" style="white-space: nowrap;">OPCIONES</th>
              </tr>
            </thead>
            <tbody>
              @forelse($productos as $producto)
                <tr wire:key="producto-desk-{{ $producto->id }}">
                  <!-- Imagen -->
                  <td style="width: 70px;">
                    @if($producto->imagen_ruta)
                      <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="img-product-thumb">
                    @else
                      <div class="img-product-placeholder">
                        <i class="ti ti-gift fs-4"></i>
                      </div>
                    @endif
                  </td>

                  <!-- Detalles del Producto -->
                  <td>
                    <div class="d-flex flex-column">
                      <div class="d-flex flex-column  mb-1">
                        <span class="fw-bold text-black fs-6">{{ $producto->nombre }}</span>
                        <div>
                          @if($producto->tipo === 'digital')
                            <span class="badge bg-label-info d-inline-flex align-items-center gap-1 py-1 px-2">
                              <i class="ti ti-cloud fs-6"></i> Digital
                            </span>
                          @else
                            <span class="badge bg-label-success d-inline-flex align-items-center gap-1 py-1 px-2">
                              <i class="ti ti-box fs-6"></i> Físico
                            </span>
                          @endif
                        </div>
                      </div>

                      @if($producto->descripcion)
                        <span class="text-black small mb-1">{{ Str::limit($producto->descripcion, 90) }}</span>
                      @endif

                      @if($producto->tipo === 'digital' && $producto->enlace_digital)
                        <span class="text-primary small d-flex align-items-center gap-1">
                          <i class="ti ti-link"></i> Enlace configurado
                        </span>
                      @endif
                    </div>
                  </td>

                  <!-- Costo -->
                  <td style="white-space: nowrap;">
                    <span class="badge-puntos-gold">
                      <span>🪙</span> {{ number_format($producto->costo_puntos) }} pts
                    </span>
                  </td>

                  <!-- Stock -->
                  <td style="white-space: nowrap;">
                    @if($producto->tipo === 'digital')
                      <span class="text-info">∞ Ilimitado</span>
                    @else
                      @if($producto->stock !== null)
                        <span class="fw-semibold text-black">{{ $producto->stock }} unidades</span>
                        @if($producto->stock <= 3)
                          <span class="text-danger ms-1">Bajo stock</span>
                        @endif
                      @else
                        <span class="text-info">∞ Ilimitado</span>
                      @endif
                    @endif
                    @if($producto->limite_por_usuario)
                      <small class="text-black d-block mt-1 small" >
                        <i class="ti ti-user me-1"></i>Máx. {{ $producto->limite_por_usuario }} por persona
                      </small>
                    @endif
                  </td>

                  <!-- Opciones -->
                  <td class="text-end" style="white-space: nowrap;">
                    <div class="d-inline-flex align-items-center gap-1">
                      <!-- Botón Editar -->
                      <button type="button" class="btn-action-icon text-primary" title="Editar Producto" wire:click="abrirModalProducto({{ $producto->id }})">
                        <i class="ti ti-pencil fs-5"></i>
                      </button>

                      <!-- Botón Eliminar -->
                      <button type="button" class="btn-action-icon text-danger btn-action-delete" title="Eliminar Producto" onclick="confirmarEliminarProducto({{ $producto->id }}, '{{ addslashes($producto->nombre) }}')">
                        <i class="ti ti-trash fs-5"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-5 text-black">
                    <i class="ti ti-package-off fs-2 mb-2 d-block"></i>
                    No se encontraron productos en el catálogo. ¡Crea el primero con el botón "Nuevo producto"!
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- VISTA MÓVIL Y TABLET (TARJETAS DE PRODUCTOS) -->
        <div class="d-block d-lg-none">
          @forelse($productos as $producto)
            <div class="mobile-item-card" wire:key="producto-mob-{{ $producto->id }}">
              <div class="d-flex align-items-start gap-3">
                <!-- Imagen / Placeholder -->
                @if($producto->imagen_ruta)
                  <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="img-product-thumb flex-shrink-0" style="width: 48px; height: 48px;">
                @else
                  <div class="img-product-placeholder flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="ti ti-gift fs-4"></i>
                  </div>
                @endif

                <!-- Contenido Principal -->
                <div class="flex-grow-1">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-1">
                    <span class="fw-bold text-dark fs-6">{{ $producto->nombre }}</span>
                    <div class="d-inline-flex align-items-center gap-1">
                      <!-- Botón Editar -->
                      <button type="button" class="btn-action-icon text-primary" title="Editar Producto" wire:click="abrirModalProducto({{ $producto->id }})">
                        <i class="ti ti-pencil fs-5"></i>
                      </button>

                      <!-- Botón Eliminar -->
                      <button type="button" class="btn-action-icon text-danger btn-action-delete" title="Eliminar Producto" onclick="confirmarEliminarProducto({{ $producto->id }}, '{{ addslashes($producto->nombre) }}')">
                        <i class="ti ti-trash fs-5"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Tipo Badge -->
                  <div class="mb-1">
                    @if($producto->tipo === 'digital')
                      <span class="badge bg-label-info d-inline-flex align-items-center gap-1 py-1 px-2">
                        <i class="ti ti-cloud fs-6"></i> Digital
                      </span>
                    @else
                      <span class="badge bg-label-success d-inline-flex align-items-center gap-1 py-1 px-2">
                        <i class="ti ti-box fs-6"></i> Físico
                      </span>
                    @endif
                  </div>

                  <!-- Descripción -->
                  @if($producto->descripcion)
                    <p class="text-black small mb-1">{{ Str::limit($producto->descripcion, 90) }}</p>
                  @endif

                  <!-- Enlace Digital -->
                  @if($producto->tipo === 'digital' && $producto->enlace_digital)
                    <div class="text-primary small d-flex align-items-center gap-1 mb-2">
                      <i class="ti ti-link"></i> Enlace configurado
                    </div>
                  @endif

                  <!-- Badges: Costo y Stock -->
                  <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top mt-2">
                    <span class="badge-puntos-gold">
                      <span>🪙</span> {{ number_format($producto->costo_puntos) }} pts
                    </span>

                    @if($producto->tipo === 'digital')
                      <span class="badge bg-label-secondary">∞ Ilimitado</span>
                    @else
                      @if($producto->stock !== null)
                        <span class="fw-semibold text-dark small">{{ $producto->stock }} unidades</span>
                        @if($producto->stock <= 3)
                          <span class="badge bg-label-danger ms-1">Bajo stock</span>
                        @endif
                      @else
                        <span class="badge bg-label-secondary">∞ Ilimitado</span>
                      @endif
                    @endif

                    @if($producto->limite_por_usuario)
                      <span class="badge bg-label-light-primary text-primary" style="font-size: 0.72rem;">
                        <i class="ti ti-user me-1"></i>Máx. {{ $producto->limite_por_usuario }} / persona
                      </span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="text-center py-5 text-black border rounded-3 bg-light">
              <i class="ti ti-package-off fs-2 mb-2 d-block text-muted"></i>
              No se encontraron productos en el catálogo. ¡Crea el primero con el botón "Nuevo producto"!
            </div>
          @endforelse
        </div>
      </div>
    @endif

  </div>

  <!-- ======================================================================= -->
  <!-- OFFCANVAS CREAR / EDITAR PRODUCTO -->
  <!-- ======================================================================= -->
  <div class="offcanvas offcanvas-end offcanvas-gamificacion" tabindex="-1" id="offcanvasProducto" wire:ignore.self aria-labelledby="offcanvasProductoLabel">
    <div class="offcanvas-header border-bottom py-3">
      <div>
        <h5 class="offcanvas-title text-primary fw-bold text-black" id="offcanvasProductoLabel">
          {{ $productoId ? 'Editar producto' : 'Configuración de producto' }}
        </h5>
      </div>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-4">
      <form wire:submit.prevent="guardarProducto" id="formProducto">

        <!-- Selector de Tipo de Producto -->
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Tipo de producto</label>
          <div class="row g-2">
            <div class="col-6">
              <button type="button" class="tipo-toggle-btn {{ $tipo === 'fisico' ? 'active  btn-secondary' : '' }}" wire:click="setTipo('fisico')">
                <i class="ti ti-box fs-5"></i>
                <span>Producto físico</span>
              </button>
            </div>
            <div class="col-6">
              <button type="button" class="tipo-toggle-btn {{ $tipo === 'digital' ? 'active btn-secondary' : '' }}" wire:click="setTipo('digital')">
                <i class="ti ti-cloud fs-5"></i>
                <span>Producto digital</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Nombre del Artículo -->
        <div class="mb-3">
          <label class="form-label fw-semibold text-dark" for="inputNombre">Nombre del artículo <span class="text-danger">*</span></label>
          <input type="text" id="inputNombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej: Libro, Camiseta, PDF..." wire:model="nombre">
          @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- Costo Puntos -->
        <div class="mb-3">
          <label class="form-label fw-semibold text-dark" for="inputCostoPuntos">Costo en puntos <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text bg-light">🪙</span>
            <input type="number" id="inputCostoPuntos" class="form-control @error('costo_puntos') is-invalid @enderror" placeholder="Ej: 1500" min="0" wire:model="costo_puntos">
            @error('costo_puntos')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <!-- Descripción -->
        <div class="mb-3">
          <label class="form-label fw-semibold text-dark" for="inputDescripcion">Descripción</label>
          <textarea id="inputDescripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3" placeholder="Describe el artículo..." wire:model="descripcion"></textarea>
          @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- Instrucciones de Canje -->
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark" for="inputInstrucciones">Instrucciones de canje</label>
          <textarea id="inputInstrucciones" class="form-control @error('instrucciones_canje') is-invalid @enderror" rows="2" placeholder="Ej: Acércate al punto de información los domingos para reclamar tu premio..." wire:model="instrucciones_canje"></textarea>
          <small class="text-muted d-block mt-1">Este mensaje lo verá el usuario antes de confirmar el canje.</small>
          @error('instrucciones_canje')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- =============================================================== -->
        <!-- SECCIÓN CONDICIONAL: PRODUCTO FÍSICO -->
        <!-- =============================================================== -->
        @if($tipo === 'fisico')
          <div class="card-offcanvas-section mb-4">
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
              <i class="ti ti-packages text-primary"></i> Detalles de inventario
            </h6>

            <!-- Unidades Disponibles -->
            <div class="mb-3">
              <label class="form-label text-dark small fw-semibold" for="inputStock">Unidades disponibles</label>
              <input type="number" id="inputStock" class="form-control bg-white @error('stock') is-invalid @enderror" placeholder="Dejar vacío para stock ilimitado" min="0" wire:model="stock">
              @error('stock')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Límite por usuario -->
            <div class="mb-3">
              <label class="form-label text-dark small fw-semibold" for="inputLimite">Límite por usuario</label>
              <input type="number" id="inputLimite" class="form-control bg-white @error('limite_por_usuario') is-invalid @enderror" placeholder="Máx. canjes por persona" min="1" wire:model="limite_por_usuario">
              @error('limite_por_usuario')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Subir Foto del Producto -->
            <div class="mb-2" wire:ignore>
              <label class="form-label text-dark small fw-semibold">Foto del producto</label>

              <!-- Input oculto para selección de foto -->
              <input type="file" id="inlineCropperUploadProducto" class="d-none" accept="image/png,image/jpeg,image/webp" onchange="window.handleCropperProductoChange(this, 'inlineCropperContainerProducto', 'inlineCroppingImageProducto')">

              <div id="inlinePreviewSectionProducto" class="d-flex align-items-center gap-3">
                <div class="position-relative d-inline-block">
                  <img id="inlineThumbProducto" src="{{ $imagen_recortada ?: ($imagen_existente ? tenant_asset('img/tienda/' . $imagen_existente) : Storage::disk('global_media')->url('placeholder.jpg')) }}" alt="Previsualización" class="img-product-thumb rounded border" style="width: 75px; height: 75px; object-fit: cover; {{ (!$imagen_recortada && !$imagen_existente) ? 'opacity: 0.6;' : '' }}">
                </div>

                <div>
                  <button type="button" class="btn btn-outline-primary btn-sm rounded-pill d-flex align-items-center gap-1 mb-1" onclick="document.getElementById('inlineCropperUploadProducto').click()">
                    <i class="ti ti-photo fs-5"></i>
                    <span>{{ ($imagen_recortada || $imagen_existente) ? 'Cambiar foto' : 'Seleccionar foto' }}</span>
                  </button>
                  <small class="text-muted d-block" style="font-size: 0.78rem;">Recorte cuadrado (1:1), PNG/JPG recomendado.</small>
                </div>
              </div>

              <!-- Contenedor del Cropper en línea Producto Físico -->
              <div id="inlineCropperContainerProducto" class="mt-3 p-3 border rounded-3 bg-light" style="display: none;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="fw-bold small text-dark d-flex align-items-center gap-1">
                    <i class="ti ti-crop text-primary"></i> Encuadra la foto del producto (1:1)
                  </span>
                  <button type="button" class="btn btn-xs btn-outline-secondary rounded-circle" onclick="window.limpiarCropperProducto()" title="Cancelar">
                    <i class="ti ti-x"></i>
                  </button>
                </div>

                <div class="border rounded bg-white d-flex align-items-center justify-content-center p-1 mb-3" style="min-height: 200px; max-height: 280px; overflow: hidden;">
                  <img id="inlineCroppingImageProducto" class="img-fluid" alt="Cropper Producto" style="max-height: 260px; display: block; max-width: 100%;">
                </div>

                <div class="d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.limpiarCropperProducto()">Cancelar</button>
                  <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-1" onclick="window.confirmarRecorteProducto()">
                    <i class="ti ti-check"></i>
                    <span>Aplicar recorte</span>
                  </button>
                </div>
              </div>

              @error('imagen')
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
              @enderror
            </div>
          </div>
        @endif

        <!-- =============================================================== -->
        <!-- SECCIÓN CONDICIONAL: PRODUCTO DIGITAL -->
        <!-- =============================================================== -->
        @if($tipo === 'digital')
          <div class="card-offcanvas-section mb-4">
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
              <i class="ti ti-cloud-download text-info"></i> Entrega digital automática
            </h6>

            <!-- Enlace de descarga -->
            <div class="mb-3">
              <label class="form-label text-dark small fw-semibold" for="inputEnlace">Enlace de descarga o URL externa</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-link"></i></span>
                <input type="text" id="inputEnlace" class="form-control bg-white @error('enlace_digital') is-invalid @enderror" placeholder="https://drive.google.com/archivo..." wire:model="enlace_digital">
                @error('enlace_digital')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Límite por usuario -->
            <div class="mb-3">
              <label class="form-label text-dark small fw-semibold" for="inputLimiteDigital">Límite de canjes por usuario</label>
              <input type="number" id="inputLimiteDigital" class="form-control bg-white @error('limite_por_usuario') is-invalid @enderror" placeholder="Dejar vacío para canjes ilimitados (ej: 1)" min="1" wire:model="limite_por_usuario">
              <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">Define el número máximo de veces que cada usuario puede canjear este producto digital.</small>
              @error('limite_por_usuario')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="alert alert-info py-2 px-3 d-flex align-items-center mb-3" style="font-size: 0.82rem;">
              <i class="ti ti-info-circle fs-5 me-2 flex-shrink-0"></i>
              <span>Al aprobarse el canje, el sistema revelará este enlace al usuario automáticamente.</span>
            </div>

            <!-- Subir Foto / Portada del Producto Digital -->
            <div class="mb-2" wire:ignore>
              <label class="form-label text-dark small fw-semibold">Foto o Portada del Producto</label>

              <!-- Input oculto para selección de foto digital -->
              <input type="file" id="inlineCropperUploadProductoDigital" class="d-none" accept="image/png,image/jpeg,image/webp" onchange="window.handleCropperProductoChange(this, 'inlineCropperContainerProductoDigital', 'inlineCroppingImageProductoDigital')">

              <div id="inlinePreviewSectionProductoDigital" class="d-flex align-items-center gap-3">
                <div class="position-relative d-inline-block">
                  <img id="inlineThumbProductoDigital" src="{{ $imagen_recortada ?: ($imagen_existente ? tenant_asset('img/tienda/' . $imagen_existente) : Storage::disk('global_media')->url('placeholder.jpg')) }}" alt="Previsualización" class="img-product-thumb rounded border" style="width: 75px; height: 75px; object-fit: cover; {{ (!$imagen_recortada && !$imagen_existente) ? 'opacity: 0.6;' : '' }}">
                </div>

                <div>
                  <button type="button" class="btn btn-outline-primary btn-sm rounded-pill d-flex align-items-center gap-1 mb-1" onclick="document.getElementById('inlineCropperUploadProductoDigital').click()">
                    <i class="ti ti-photo fs-5"></i>
                    <span>{{ ($imagen_recortada || $imagen_existente) ? 'Cambiar foto' : 'Seleccionar foto' }}</span>
                  </button>
                  <small class="text-muted d-block" style="font-size: 0.78rem;">Recorte cuadrado (1:1), PNG/JPG recomendado.</small>
                </div>
              </div>

              <!-- Contenedor del Cropper en línea Producto Digital -->
              <div id="inlineCropperContainerProductoDigital" class="mt-3 p-3 border rounded-3 bg-light" style="display: none;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="fw-bold small text-dark d-flex align-items-center gap-1">
                    <i class="ti ti-crop text-primary"></i> Encuadra la foto del producto (1:1)
                  </span>
                  <button type="button" class="btn btn-xs btn-outline-secondary rounded-circle" onclick="window.limpiarCropperProducto()" title="Cancelar">
                    <i class="ti ti-x"></i>
                  </button>
                </div>

                <div class="border rounded bg-white d-flex align-items-center justify-content-center p-1 mb-3" style="min-height: 200px; max-height: 280px; overflow: hidden;">
                  <img id="inlineCroppingImageProductoDigital" class="img-fluid" alt="Cropper Producto" style="max-height: 260px; display: block; max-width: 100%;">
                </div>

                <div class="d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.limpiarCropperProducto()">Cancelar</button>
                  <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-1" onclick="window.confirmarRecorteProducto()">
                    <i class="ti ti-check"></i>
                    <span>Aplicar recorte</span>
                  </button>
                </div>
              </div>

              @error('imagen')
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
              @enderror
            </div>
          </div>
        @endif

        <!-- =============================================================== -->
        <!-- SECCIÓN: RESTRICCIONES DE VISIBILIDAD -->
        <!-- =============================================================== -->
        <div class="card card-offcanvas-section mb-4 p-0 overflow-hidden border">
          <div class="card-header bg-light py-3 px-3 cursor-pointer d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseRestricciones" aria-expanded="true" aria-controls="collapseRestricciones">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-filter text-primary"></i> Restricciones de visibilidad
            </h6>
            <i class="ti ti-chevron-down"></i>
          </div>

          <div id="collapseRestricciones" class="collapse show">
            <div class="card-body p-3">
              <!-- Switch Visible para todos -->
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="visible_todos" wire:model.live="visible_todos">
                <label class="form-check-label w-100 cursor-pointer" for="visible_todos">
                  <strong class="text-dark">Publicación visible para todos los usuarios</strong>
                  <small class="d-block text-muted">Si se desactiva, solo los usuarios que cumplan los requisitos podrán verla.</small>
                </label>
              </div>

              <div x-show="!$wire.visible_todos" x-cloak>
                <hr class="my-3">

                <div class="row g-3">
                  <!-- Género -->
                  <div class="col-12">
                    <label class="form-label text-dark fw-bold mb-2">Género</label>
                    <div class="row g-2">
                      <div class="col-4">
                        <div class="form-check custom-option custom-option-basic {{ $genero === 1 ? 'checked' : '' }}">
                          <label class="form-check-label custom-option-content p-2 d-flex justify-content-between align-items-center cursor-pointer" for="genero_masc">
                            <span class="custom-option-header p-0 border-0">
                              <span class="fw-medium small">Masculino</span>
                            </span>
                            <input name="genero" class="form-check-input" type="radio" value="1" id="genero_masc" wire:model.live="genero" />
                          </label>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-check custom-option custom-option-basic {{ $genero === 2 ? 'checked' : '' }}">
                          <label class="form-check-label custom-option-content p-2 d-flex justify-content-between align-items-center cursor-pointer" for="genero_fem">
                            <span class="custom-option-header p-0 border-0">
                              <span class="fw-medium small">Femenino</span>
                            </span>
                            <input name="genero" class="form-check-input" type="radio" value="2" id="genero_fem" wire:model.live="genero" />
                          </label>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-check custom-option custom-option-basic {{ $genero === 3 ? 'checked' : '' }}">
                          <label class="form-check-label custom-option-content p-2 d-flex justify-content-between align-items-center cursor-pointer" for="genero_ambos">
                            <span class="custom-option-header p-0 border-0">
                              <span class="fw-medium small">Ambos</span>
                            </span>
                            <input name="genero" class="form-check-input" type="radio" value="3" id="genero_ambos" wire:model.live="genero" />
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Sedes Permitidas -->
                  <div class="col-12">
                    <label class="form-label text-dark small fw-semibold" for="select-restriccion-sedes">Sedes permitidas</label>
                    <div wire:ignore>
                      <select id="select-restriccion-sedes" class="form-select select2-restricciones" multiple data-placeholder="Seleccionar sedes...">
                        @foreach ($sedes as $sede)
                          <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                      </select>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Deja vacío para permitir todas las sedes.</small>
                  </div>

                  <!-- Estados Civiles -->
                  <div class="col-12">
                    <label class="form-label text-dark small fw-semibold" for="select-restriccion-estados-civiles">Estados civiles</label>
                    <div wire:ignore>
                      <select id="select-restriccion-estados-civiles" class="form-select select2-restricciones" multiple data-placeholder="Seleccionar estados civiles...">
                        @foreach ($estadosCiviles as $estado)
                          <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                        @endforeach
                      </select>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Deja vacío para permitir todos los estados civiles.</small>
                  </div>

                  <!-- Rangos de Edad -->
                  <div class="col-12">
                    <label class="form-label text-dark small fw-semibold" for="select-restriccion-rangos-edad">Rangos de edad</label>
                    <div wire:ignore>
                      <select id="select-restriccion-rangos-edad" class="form-select select2-restricciones" multiple data-placeholder="Seleccionar rangos de edad...">
                        @foreach ($rangosEdad as $rango)
                          <option value="{{ $rango->id }}">{{ $rango->nombre }} ({{ $rango->edad_minima }}-{{ $rango->edad_maxima }})</option>
                        @endforeach
                      </select>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Deja vacío para permitir todas las edades.</small>
                  </div>

                  <!-- Tipos de Usuario -->
                  <div class="col-12">
                    <label class="form-label text-dark small fw-semibold" for="select-restriccion-tipos-usuario">Tipos de usuario</label>
                    <div wire:ignore>
                      <select id="select-restriccion-tipos-usuario" class="form-select select2-restricciones" multiple data-placeholder="Seleccionar tipos de usuario...">
                        @foreach ($tiposUsuario as $tipoU)
                          <option value="{{ $tipoU->id }}">{{ $tipoU->nombre }}</option>
                        @endforeach
                      </select>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Deja vacío para permitir todos los tipos de usuario.</small>
                  </div>

                  <!-- Pasos de Crecimiento -->
                  <div class="col-12 mt-3">
                    <label class="form-label text-dark small fw-bold mb-2">Pasos de crecimiento</label>
                    @if(count($procesosRequisito) > 0)
                      <div class="d-flex flex-column gap-2 mb-2">
                        @foreach ($procesosRequisito as $idx => $proceso)
                          <div class="row g-1 align-items-center" wire:key="paso-req-{{ $idx }}">
                            <div class="col-6">
                              <select class="form-select form-select-sm bg-white" wire:model="procesosRequisito.{{ $idx }}.paso_crecimiento_id">
                                <option value="">Seleccionar paso...</option>
                                @foreach ($pasosCrecimiento as $paso)
                                  <option value="{{ $paso->id }}">{{ $paso->nombre }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="col-5">
                              <select class="form-select form-select-sm bg-white" wire:model="procesosRequisito.{{ $idx }}.estado_paso_crecimiento_usuario_id">
                                <option value="">Estado requerido...</option>
                                @foreach ($estadosPasos as $ep)
                                  <option value="{{ $ep->id }}">{{ $ep->nombre }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="col-1 text-end">
                              <button type="button" class="btn btn-sm btn-action-icon text-danger p-0" wire:click="eliminarPasoRequisito({{ $idx }})" title="Eliminar requisito">
                                <i class="ti ti-trash fs-5"></i>
                              </button>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" wire:click="agregarPasoRequisito">
                      <i class="ti ti-plus me-1"></i> Agregar paso
                    </button>
                  </div>

                  <!-- Tareas de Consolidación -->
                  <div class="col-12 mt-3">
                    <label class="form-label text-dark small fw-bold mb-2">Tareas de consolidación</label>
                    @if(count($tareasRequisito) > 0)
                      <div class="d-flex flex-column gap-2 mb-2">
                        @foreach ($tareasRequisito as $idx => $tarea)
                          <div class="row g-1 align-items-center" wire:key="tarea-req-{{ $idx }}">
                            <div class="col-6">
                              <select class="form-select form-select-sm bg-white" wire:model="tareasRequisito.{{ $idx }}.tarea_consolidacion_id">
                                <option value="">Seleccionar tarea...</option>
                                @foreach ($tareasConsolidacion as $tc)
                                  <option value="{{ $tc->id }}">{{ $tc->nombre }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="col-5">
                              <select class="form-select form-select-sm bg-white" wire:model="tareasRequisito.{{ $idx }}.estado_tarea_consolidacion_id">
                                <option value="">Estado requerido...</option>
                                @foreach ($estadosTareas as $et)
                                  <option value="{{ $et->id }}">{{ $et->nombre }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="col-1 text-end">
                              <button type="button" class="btn btn-sm btn-action-icon text-danger p-0" wire:click="eliminarTareaRequisito({{ $idx }})" title="Eliminar requisito">
                                <i class="ti ti-trash fs-5"></i>
                              </button>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" wire:click="agregarTareaRequisito">
                      <i class="ti ti-plus me-1"></i> Agregar tarea
                    </button>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Offcanvas -->
        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
          <button type="button" class="btn btn-label-secondary rounded-pill" data-bs-dismiss="offcanvas">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill" wire:loading.attr="disabled">
            <span wire:loading wire:target="guardarProducto" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            <span>Guardar</span>
          </button>
        </div>

      </form>
    </div>
  </div>

</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: MANEJO PERSISTENTE DE BACKDROP Y SWEETALERT2 -->
<!-- ========================================================================= -->
@script
<script>
  let offcanvasProductoInstance = null;
  let currentBackdrop = null;

  function crearBackdropPersistente(onCloseCallback) {
    quitarBackdropPersistente();
    const backdrop = document.createElement('div');
    backdrop.className = 'offcanvas-backdrop fade show';
    backdrop.style.zIndex = '1040';
    backdrop.addEventListener('click', () => {
      if (onCloseCallback) onCloseCallback();
    });
    document.body.appendChild(backdrop);
    currentBackdrop = backdrop;
  }

  function quitarBackdropPersistente() {
    if (currentBackdrop) {
      currentBackdrop.remove();
      currentBackdrop = null;
    }
    const backdrops = document.querySelectorAll('.offcanvas-backdrop');
    backdrops.forEach(b => b.remove());
  }

  function getOrCreateOffcanvas(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return null;
    let instance = bootstrap.Offcanvas.getInstance(el);
    if (!instance) {
      instance = new bootstrap.Offcanvas(el, {
        backdrop: false,
        keyboard: true
      });
    }
    return instance;
  }

  // Inicialización de Select2 para las Restricciones de Visibilidad
  window.initSelect2Restricciones = function() {
    if (typeof $.fn.select2 !== 'undefined') {
      const selectores = [
        '#select-restriccion-sedes',
        '#select-restriccion-estados-civiles',
        '#select-restriccion-rangos-edad',
        '#select-restriccion-tipos-usuario'
      ];

      const selectConfig = {
        dropdownParent: $('#offcanvasProducto'),
        width: '100%',
        closeOnSelect: false,
        allowClear: true
      };

      selectores.forEach(function (selector) {
        const $select = $(selector);
        if ($select.length) {
          if (!$select.hasClass('select2-hidden-accessible')) {
            $select.select2({ ...selectConfig, placeholder: $select.data('placeholder') || 'Seleccionar...' });
          }

          $select.off('select2:select select2:unselect change.livewire');

          $select.on('select2:select select2:unselect', function(e) {
            const selectEl = $(this);
            const select2Data = selectEl.data('select2');

            if (select2Data && select2Data.$container) {
              const $inlineInput = select2Data.$container.find('.select2-search__field');
              if ($inlineInput.length) {
                $inlineInput.val('');
              }
            }
            if (select2Data && select2Data.$dropdown) {
              const $dropInput = select2Data.$dropdown.find('.select2-search__field');
              if ($dropInput.length) {
                $dropInput.val('');
              }
            }

            setTimeout(() => {
              if (select2Data && select2Data.isOpen()) {
                select2Data.trigger('query', { term: '' });
              }
            }, 10);
          });

          $select.on('change.livewire', function () {
            const val = $(this).val() || [];
            if (selector === '#select-restriccion-sedes') $wire.sedesSeleccionadas = val;
            if (selector === '#select-restriccion-estados-civiles') $wire.estadosCivilesSeleccionados = val;
            if (selector === '#select-restriccion-rangos-edad') $wire.rangosEdadSeleccionados = val;
            if (selector === '#select-restriccion-tipos-usuario') $wire.tiposUsuarioSeleccionados = val;
          });
        }
      });
    }
  };

  $(document).off('click.select2single', '.select2-container--default .select2-selection--multiple')
    .on('click.select2single', '.select2-container--default .select2-selection--multiple', function (e) {
      if (!$(e.target).hasClass('select2-selection__choice__remove') && !$(e.target).closest('.select2-selection__choice__remove').length) {
        const $select = $(this).closest('.select2-container').prev('select.select2-restricciones');
        if ($select.length && $select.data('select2') && !$select.data('select2').isOpen()) {
          $select.select2('open');
        }
      }
    });

  $('#formProducto').off('submit.syncSelect2').on('submit.syncSelect2', function() {
    $wire.sedesSeleccionadas = $('#select-restriccion-sedes').val() || [];
    $wire.estadosCivilesSeleccionados = $('#select-restriccion-estados-civiles').val() || [];
    $wire.rangosEdadSeleccionados = $('#select-restriccion-rangos-edad').val() || [];
    $wire.tiposUsuarioSeleccionados = $('#select-restriccion-tipos-usuario').val() || [];
  });

  window.sincronizarValoresSelect2Restricciones = function(payload) {
    if (typeof $.fn.select2 !== 'undefined') {
      $('#select-restriccion-sedes').val(payload.sedes || []).trigger('change.select2');
      $('#select-restriccion-estados-civiles').val(payload.estados || []).trigger('change.select2');
      $('#select-restriccion-rangos-edad').val(payload.rangos || []).trigger('change.select2');
      $('#select-restriccion-tipos-usuario').val(payload.tipos || []).trigger('change.select2');
    }
  };

  // Evento para sincronizar valores desde Livewire
  $wire.on('sincronizarSelect2Restricciones', (data) => {
    const payload = Array.isArray(data) ? data[0] : data;
    setTimeout(() => {
      window.initSelect2Restricciones();
      if (payload) {
        window.sincronizarValoresSelect2Restricciones(payload);
      }
    }, 120);
  });

  // Eventos de Apertura y Cierre de Offcanvas Producto
  $wire.on('abrirOffcanvasProducto', (data) => {
    const payload = Array.isArray(data) ? data[0] : (data || {});
    limpiarCropperProducto();

    const placeholder = payload.placeholderUrl || '';
    const currentImg = payload.imagenUrl || placeholder;
    const isPlaceholder = !payload.imagenUrl;

    const thumb = document.getElementById('inlineThumbProducto');
    if (thumb) {
      thumb.src = currentImg;
      thumb.style.opacity = isPlaceholder ? '0.6' : '1';
    }
    const thumbDigital = document.getElementById('inlineThumbProductoDigital');
    if (thumbDigital) {
      thumbDigital.src = currentImg;
      thumbDigital.style.opacity = isPlaceholder ? '0.6' : '1';
    }

    const el = document.getElementById('offcanvasProducto');
    if (el) {
      offcanvasProductoInstance = getOrCreateOffcanvas('offcanvasProducto');
      crearBackdropPersistente(() => {
        offcanvasProductoInstance.hide();
        quitarBackdropPersistente();
        limpiarCropperProducto();
      });
      offcanvasProductoInstance.show();

      setTimeout(() => {
        window.initSelect2Restricciones();
      }, 150);
    }
  });

  $wire.on('cerrarOffcanvasProducto', () => {
    const el = document.getElementById('offcanvasProducto');
    if (el) {
      offcanvasProductoInstance = getOrCreateOffcanvas('offcanvasProducto');
      offcanvasProductoInstance.hide();
    }
    quitarBackdropPersistente();
    limpiarCropperProducto();
  });

  // Limpieza al cerrar manualmente vía botón o tecla Escape
  const offcanvasEl = document.getElementById('offcanvasProducto');
  if (offcanvasEl) {
    offcanvasEl.addEventListener('hidden.bs.offcanvas', () => {
      quitarBackdropPersistente();
      limpiarCropperProducto();
    });
  }

  // SweetAlert2: Mensajes globales
  $wire.on('msn', (data) => {
    const payload = Array.isArray(data) ? data[0] : data;
    Swal.fire({
      icon: payload.icono || 'info',
      title: payload.titulo || 'Notificación',
      text: payload.texto || '',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
  });

  // SweetAlert2: Confirmación para Eliminar Producto
  window.confirmarEliminarProducto = function(id, nombre) {
    Swal.fire({
      title: '¿Eliminar producto?',
      text: `¿Estás seguro de que deseas eliminar el producto "${nombre}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-danger me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $wire.eliminarProducto(id);
      }
    });
  };

  // SweetAlert2: Confirmación para Aprobar Solicitud
  window.confirmarAprobarSolicitud = function(id, codigo, usuario) {
    Swal.fire({
      title: '¿Aprobar canje?',
      text: `¿Deseas marcar como aprobada la solicitud #${codigo} de ${usuario}?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, aprobar',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-success me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $wire.aprobarSolicitud(id);
      }
    });
  };

  // SweetAlert2: Confirmación para Rechazar Solicitud
  window.confirmarRechazarSolicitud = function(id, codigo, usuario) {
    Swal.fire({
      title: '¿Rechazar solicitud?',
      text: `Se reembolsarán los puntos a ${usuario}. Puedes ingresar un motivo:`,
      input: 'textarea',
      inputPlaceholder: 'Ej: Artículo fuera de inventario, información incompleta...',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, rechazar y reembolsar',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-danger me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $wire.rechazarSolicitud(id, result.value);
      }
    });
  };

  // =========================================================================
  // CROPPER JS EN LÍNEA - PRODUCTO (FUNCIONES GLOBALES)
  // =========================================================================
  let cropperProducto = null;

  window.limpiarCropperProducto = function() {
    if (cropperProducto) {
      cropperProducto.destroy();
      cropperProducto = null;
    }
    const c1 = document.getElementById('inlineCropperContainerProducto');
    if (c1) c1.style.display = 'none';
    const c2 = document.getElementById('inlineCropperContainerProductoDigital');
    if (c2) c2.style.display = 'none';

    const u1 = document.getElementById('inlineCropperUploadProducto');
    if (u1) u1.value = '';
    const u2 = document.getElementById('inlineCropperUploadProductoDigital');
    if (u2) u2.value = '';
  };

  window.handleCropperProductoChange = function(input, containerId, imageId) {
    if (input.files && input.files.length) {
      const file = input.files[0];
      if (['image/gif', 'image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        if (cropperProducto) {
          cropperProducto.destroy();
          cropperProducto = null;
        }
        const reader = new FileReader();
        reader.onload = function(evt) {
          const croppingImg = document.getElementById(imageId);
          const container = document.getElementById(containerId);
          if (croppingImg && container) {
            croppingImg.src = evt.target.result;
            container.style.display = 'block';

            setTimeout(() => {
              if (cropperProducto) {
                cropperProducto.destroy();
              }
              cropperProducto = new Cropper(croppingImg, {
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

  window.confirmarRecorteProducto = function() {
    if (!cropperProducto) return;

    const canvas = cropperProducto.getCroppedCanvas({
      width: 600,
      height: 600,
      imageSmoothingQuality: 'high'
    });

    if (canvas) {
      const imgSrc = canvas.toDataURL('image/png');
      $wire.set('imagen_recortada', imgSrc);

      const thumb = document.getElementById('inlineThumbProducto');
      if (thumb) {
        thumb.src = imgSrc;
        thumb.style.opacity = '1';
      }
      const thumbDigital = document.getElementById('inlineThumbProductoDigital');
      if (thumbDigital) {
        thumbDigital.src = imgSrc;
        thumbDigital.style.opacity = '1';
      }

      window.limpiarCropperProducto();
    }
  };
</script>
@endscript
