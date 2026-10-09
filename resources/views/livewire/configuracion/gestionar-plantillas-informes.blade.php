<div>
  <style>
    .plantillas-tabs-scroll-wrapper {
      width: 100%;
      overflow-x: auto;
      scrollbar-width: none;
      -ms-overflow-style: none;
      -webkit-overflow-scrolling: touch;
      padding-bottom: 6px;
      padding-top: 2px;
    }

    .plantillas-tabs-scroll-wrapper::-webkit-scrollbar {
      display: none;
    }

    .plantillas-pill-container {
      border: 1px solid #e2e8f0 !important;
      border-radius: 50rem !important;
      padding: 5px 6px !important;
      background-color: #ffffff !important;
      display: inline-flex !important;
      flex-wrap: nowrap !important;
      align-items: center !important;
      gap: 4px !important;
      margin: 0 !important;
      white-space: nowrap !important;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04) !important;
    }

    .plantillas-pill-container .nav-item {
      margin: 0 !important;
      padding: 0 !important;
      flex-shrink: 0 !important;
    }

    .plantillas-pill-container .nav-link {
      color: #2f2b3d !important;
      font-weight: 600 !important;
      font-size: 0.9rem !important;
      padding: 0.45rem 1.25rem !important;
      border: none !important;
      margin: 0 !important;
      background: transparent !important;
      box-shadow: none !important;
      white-space: nowrap !important;
      border-radius: 50rem !important;
      transition: all 0.2s ease-in-out !important;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }


    .plantillas-pill-container .nav-link .tab-count-badge {
      font-size: 0.75rem;
      padding: 0.15rem 0.55rem;
      border-radius: 50rem;
      font-weight: 700;
      transition: all 0.2s ease-in-out;
      line-height: 1.2;
    }

    .plantillas-pill-container .nav-link.active .tab-count-badge {
      background-color: rgba(255, 255, 255, 0.22) !important;
      color: #ffffff !important;
      border: 1px solid rgba(255, 255, 255, 0.35);
    }

    .plantillas-pill-container .nav-link:not(.active) .tab-count-badge {
      background-color: #166534 !important;
      color: #ffffff !important;
    }

    .cursor-move {
      cursor: grab !important;
    }
    .cursor-move:active {
      cursor: grabbing !important;
    }
    .sortable-ghost {
      opacity: 0.45;
      background-color: #f4f3ff !important;
      border: 2px dashed #7367f0 !important;
    }
    .sortable-chosen {
      box-shadow: 0 4px 14px rgba(115, 103, 240, 0.2) !important;
    }
  </style>

  <!-- HEADER PRINCIPAL -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Plantillas de informes</h4>
      </div>
      <p class="text-black mb-0 small">
        Configura y personaliza la estructura de columnas (Secciones, Subsecciones y Métricas) y bloques de sedes para la generación dinámica de informes.
      </p>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button" wire:click="crearInforme" class="btn btn-primary rounded-pill px-3 waves-effect waves-light shadow-sm">
        <i class="ti ti-plus me-1"></i> Nuevo informe
      </button>
    </div>
  </div>

  @include('layouts.status-msn')

  <!-- SELECTOR DE INFORME Y ACCIONES -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3 p-md-4">
      <div class="row align-items-center g-3">
        <div class="col-12 col-md-5 col-lg-4">
          <label class="form-label fw-bold text-dark mb-1">
            <i class="ti ti-file-analytics text-primary me-1"></i> Seleccionar informe:
          </label>
          <select wire:model.live="informeId" class="form-select border-primary shadow-none">
            <option value="">-- Seleccione un informe --</option>
            @foreach($informes as $inf)
              <option value="{{ $inf->id }}">{{ $inf->nombre }}</option>
            @endforeach
          </select>
        </div>

        @if($informeSeleccionado)
          <div class="col-12 col-md-7 col-lg-8 d-flex flex-wrap justify-content-md-end align-items-center gap-2 pt-2 pt-md-0">
            <button type="button" wire:click="editarInforme({{ $informeSeleccionado->id }})" class="btn btn-outline-secondary rounded-pill btn-sm px-3" title="Editar propiedades de la plantilla">
              <i class="ti ti-edit me-1"></i> Editar
            </button>

            <button type="button" wire:click="duplicarInforme({{ $informeSeleccionado->id }})" class="btn btn-outline-secondary rounded-pill btn-sm px-3" title="Duplicar toda la estructura del informe">
              <i class="ti ti-copy me-1"></i> Duplicar
            </button>

            <button type="button" wire:click="confirmarEliminarInforme({{ $informeSeleccionado->id }})" class="btn btn-outline-danger rounded-pill btn-sm px-3" title="Eliminar plantilla">
              <i class="ti ti-trash me-1"></i> Eliminar
            </button>
          </div>

          @if($informeSeleccionado->descripcion)

            <div class="p-4 d-flex mb-4" style="color:black; font-size:12px; border: solid 1.5px #95CDDF; border-radius: 14px; background-color: #f0faff;">
              <i class="ti ti-info-circle text-secondary me-2 fs-4"></i>
              <p class="m-0 align-self-center"> {{ $informeSeleccionado->descripcion }}</p>
            </div>
          @endif
        @endif
      </div>
    </div>
  </div>

  @if($informeSeleccionado)
    <!-- TABS DE CONFIGURACIÓN ESTILO GAMIFICACIÓN -->
    <div class="plantillas-tabs-scroll-wrapper mb-4">
      <ul class="nav nav-pills plantillas-pill-container" role="tablist">
        <li class="nav-item">
          <button type="button" class="nav-link {{ $tabActiva === 'columnas' ? 'active' : '' }}" wire:click="$set('tabActiva', 'columnas')">
            <i class="ti ti-layout-columns"></i>
            <span>Estructura de Columnas (Secciones, Subsecciones e Ítems)</span>
            <span class="tab-count-badge">{{ $informeSeleccionado->secciones->count() }}</span>
          </button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link {{ $tabActiva === 'bloques' ? 'active' : '' }}" wire:click="$set('tabActiva', 'bloques')">
            <i class="ti ti-building-community"></i>
            <span>Bloques de Sedes / Subtotales</span>
            <span class="tab-count-badge">{{ $informeSeleccionado->bloques->count() }}</span>
          </button>
        </li>
      </ul>

      <!-- ========================================================================= -->
      <!-- CONTENIDO TAB 1: ESTRUCTURA DE COLUMNAS -->
      <!-- ========================================================================= -->
      @if($tabActiva === 'columnas')
        <div class="card shadow-sm border-0 mt-5">
          <div class="card-header d-flex justify-content-between align-items-center bg-light-subtle py-3 border-bottom">
            <div>
              <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="ti ti-hierarchy-2 text-primary me-2"></i> Árbol Jerárquico de Columnas
              </h5>
              <small class="text-black">Nivel 1 (Secciones) &rarr; Nivel 2 (Subsecciones) &rarr; Nivel 3 (Métricas con reglas y fórmulas)</small>
            </div>
            <button type="button" wire:click="crearSeccion" class="btn btn-primary rounded-pill btn-sm px-3 shadow-sm">
              <i class="ti ti-plus me-1"></i> Añadir Sección (Nivel 1)
            </button>
          </div>

          <div class="card-body p-3 p-md-4">
            @if($informeSeleccionado->secciones->isEmpty())
              <div class="text-center py-5">
                <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3">
                  <i class="ti ti-folder-plus fs-1"></i>
                </div>
                <h5 class="fw-semibold text-dark">Aún no hay secciones en esta plantilla</h5>
                <p class="text-muted small max-w-500 mx-auto">
                  Comienza agregando la primera sección (ejemplo: "Crecimiento Espiritual", "Consolidación", "Matrículas").
                </p>
                <button type="button" wire:click="crearSeccion" class="btn btn-primary rounded-pill btn-sm px-4">
                  <i class="ti ti-plus me-1"></i> Crear Primera Sección
                </button>
              </div>
            @else
              <div class="listadoSecciones accordion accordion-bordered d-flex flex-column gap-3" id="accordionSecciones">
                @foreach($informeSeleccionado->secciones->sortBy('orden') as $seccion)
                  <div class="accordion-item card shadow-none border mb-0 seccion-item" wire:key="seccion-card-{{ $seccion->id }}" data-seccion-id="{{ $seccion->id }}">
                    <!-- HEADER SECCIÓN (NIVEL 1) -->
                    <h2 class="accordion-header d-flex align-items-center rounded-top" id="headingSeccion{{ $seccion->id }}">
                      <i class="ti ti-grip-vertical drag-handle-seccion ms-3 cursor-move text-primary" style="font-size: 1.25rem;" title="Arrastrar para reordenar sección"></i>
                     <button class="accordion-button collapsed border-0 shadow-none bg-transparent py-3"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseSeccion{{ $seccion->id }}"
                                aria-expanded="false"
                                aria-controls="collapseSeccion{{ $seccion->id }}"
                                wire:ignore.self
                                style="padding-right: 0;">
                            <style>
                                #headingSeccion{{ $seccion->id }} .accordion-button::after { display: none !important; }
                            </style>
                        <div class="d-flex align-items-start flex-column flex-wrap">
                          <span class="fw-bold text-primary fs-6">{{ $seccion->nombre }}</span>
                          <span class="small">
                            {{ $seccion->subsecciones->count() }} {{ $seccion->subsecciones->count() === 1 ? 'subsección' : 'subsecciones' }}
                          </span>
                        </div>
                      </button>

                      <div class="ms-auto pe-3 d-flex align-items-center gap-2">
                        <button type="button" wire:click="crearSubseccion({{ $seccion->id }})" class="btn btn-xs btn-primary rounded-pill px-3 py-1 shadow-sm" title="Añadir Subsección">
                          <i class="ti ti-plus me-1"></i> Subsección
                        </button>
                        <div class="dropdown">
                          <button type="button" class="btn btn-xs rounded-pill btn-icon btn-outline-primary waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0);" wire:click="editarSeccion({{ $seccion->id }})">
                                <i class="ti ti-edit me-2"></i> Editar Sección
                              </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <a class="dropdown-item text-danger" href="javascript:void(0);" wire:click="confirmarEliminarSeccion({{ $seccion->id }})">
                                <i class="ti ti-trash me-2"></i> Eliminar Sección
                              </a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </h2>

                    <!-- CUERPO SECCIÓN (SUBSECCIONES - NIVEL 2) -->
                    <div id="collapseSeccion{{ $seccion->id }}"
                         class="accordion-collapse collapse show"
                         aria-labelledby="headingSeccion{{ $seccion->id }}"
                         wire:ignore.self>
                      <div class="accordion-body bg-light-subtle p-3">
                        @if($seccion->subsecciones->isEmpty())
                          <div class="text-center py-4 border border-dashed rounded-3 bg-white">
                            <p class="text-muted small mb-2">No hay subsecciones dentro de esta sección.</p>
                            <button type="button" wire:click="crearSubseccion({{ $seccion->id }})" class="btn btn-xs btn-outline-primary rounded-pill">
                              <i class="ti ti-plus me-1"></i> Agregar Subsección
                            </button>
                          </div>
                        @else
                          <div class="listadoSubsecciones d-flex flex-column gap-3" data-seccion-id="{{ $seccion->id }}" id="contenedorSubsecciones{{ $seccion->id }}">
                            @foreach($seccion->subsecciones->sortBy('orden') as $subseccion)
                              <div class="card shadow-none border bg-white subseccion-item" wire:key="subseccion-card-{{ $subseccion->id }}" data-subseccion-id="{{ $subseccion->id }}">
                                <!-- HEADER SUBSECCIÓN -->
                                <div class="card-header bg-white d-flex align-items-center py-2 px-3 border-bottom">
                                  <i class="ti ti-grip-vertical drag-handle-subseccion me-2 cursor-move text-secondary" style="font-size: 1.15rem;" title="Arrastrar para reordenar subsección"></i>
                                  <div class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap">
                                    <span class="fw-bold text-dark fs-6">
                                      <i class="ti ti-subtask text-secondary me-1"></i> {{ $subseccion->nombre }}
                                    </span>
                                    <span class="badge bg-label-secondary rounded-pill small">
                                      {{ $subseccion->items->count() }} {{ $subseccion->items->count() === 1 ? 'métrica' : 'métricas' }}
                                    </span>
                                  </div>

                                  <div class="d-flex align-items-center gap-2 ms-auto">
                                    <button type="button" wire:click="crearItem({{ $subseccion->id }})" class="btn btn-xs btn-success rounded-pill px-2 py-1 shadow-none" title="Añadir Métrica / Ítem">
                                      <i class="ti ti-plus me-1"></i> Métrica
                                    </button>
                                    <div class="dropdown">
                                      <button type="button" class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                      </button>
                                      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                          <a class="dropdown-item" href="javascript:void(0);" wire:click="editarSubseccion({{ $subseccion->id }})">
                                            <i class="ti ti-edit me-2"></i> Editar Subsección
                                          </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                          <a class="dropdown-item text-danger" href="javascript:void(0);" wire:click="confirmarEliminarSubseccion({{ $subseccion->id }})">
                                            <i class="ti ti-trash me-2"></i> Eliminar Subsección
                                          </a>
                                        </li>
                                      </ul>
                                    </div>
                                  </div>
                                </div>

                                <!-- LISTADO DE ITEMS / MÉTRICAS (NIVEL 3) -->
                                <div class="card-body p-3 bg-light-subtle">
                                  @if($subseccion->items->isEmpty())
                                    <div class="text-center py-3 border border-dashed rounded-3 bg-white">
                                      <p class="text-muted small mb-1">Sin métricas configuradas.</p>
                                      <button type="button" wire:click="crearItem({{ $subseccion->id }})" class="btn btn-xs btn-outline-success rounded-pill">
                                        <i class="ti ti-plus me-1"></i> Crear Métrica
                                      </button>
                                    </div>
                                  @else
                                    <div class="listadoItems d-flex flex-column gap-2" data-subseccion-id="{{ $subseccion->id }}" id="contenedorItems{{ $subseccion->id }}">
                                      @foreach($subseccion->items->sortBy('orden') as $item)
                                        <div class="card shadow-none border bg-white item-card" wire:key="item-card-{{ $item->id }}" data-item-id="{{ $item->id }}">
                                          <div class="card-body p-2 d-flex align-items-center justify-content-between gap-2 flex-wrap flex-md-nowrap">
                                            <!-- Handle, Nombre & Reglas -->
                                            <div class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap">
                                              <i class="ti ti-grip-vertical drag-handle-item cursor-move text-muted" style="font-size: 1.15rem;" title="Arrastrar para reordenar métrica"></i>
                                              <div>
                                                <span class="fw-semibold text-dark fs-6">{{ $item->nombre }}</span>
                                                <span class="text-muted small ms-1">#{{ $item->id }}</span>
                                              </div>

                                              <!-- Badges descriptivos de la regla -->
                                              <div class="d-flex flex-wrap align-items-center gap-1 ms-1">
                                                @if($item->con_operacion)
                                                  <span class="badge bg-label-warning text-dark border border-warning-subtle py-1">
                                                    <i class="ti ti-calculator me-1"></i>
                                                    @php
                                                      $ops = [1 => '+', 2 => '-', 3 => '*', 4 => '/', 5 => 'Promedio'];
                                                      $simbolo = $ops[$item->operacion] ?? '?';
                                                      $itemAObj = $itemsDelInforme->firstWhere('id', $item->item_a);
                                                      $itemBObj = $itemsDelInforme->firstWhere('id', $item->item_b);
                                                    @endphp
                                                    [{{ $itemAObj ? $itemAObj->nombre : 'Ítem #'.$item->item_a }}]
                                                    <strong>{{ $simbolo }}</strong>
                                                    [{{ $itemBObj ? $itemBObj->nombre : 'Ítem #'.$item->item_b }}]
                                                  </span>
                                                @elseif(!empty($item->totalizar_items))
                                                  <span class="badge bg-label-info border border-info-subtle py-1">
                                                    <i class="ti ti-sum me-1"></i> Totalizador ({{ count(explode(',', $item->totalizar_items)) }} ítems)
                                                  </span>
                                                @elseif($item->pasoCrecimiento)
                                                  <span class="badge bg-label-primary py-1">
                                                    <i class="ti ti-stairs me-1"></i> {{ $item->pasoCrecimiento->nombre }}
                                                  </span>
                                                  @if($item->estadoPasoCrecimiento)
                                                    <span class="badge bg-label-secondary fs-8 py-1">
                                                      {{ $item->estadoPasoCrecimiento->nombre }}
                                                    </span>
                                                  @endif
                                                  @if($item->parametro_comparacion === 'paso-crecimiento' && $item->pasoCrecimiento2)
                                                    <span class="badge bg-label-danger fs-8 py-1">
                                                      <i class="ti ti-git-compare me-1"></i>
                                                      {{ $item->no_existe_paso_crecimiento_id_2 ? 'NO en' : 'VS' }} {{ $item->pasoCrecimiento2->nombre }}
                                                      @if($item->cantidad_dias_dilacion) ({{ $item->cantidad_dias_dilacion }}d dilación) @endif
                                                    </span>
                                                  @elseif($item->parametro_comparacion === 'fecha-creacion')
                                                    <span class="badge bg-label-info fs-8 py-1">
                                                      <i class="ti ti-calendar-event me-1"></i> VS Creación @if($item->cantidad_dias_dilacion) ({{ $item->cantidad_dias_dilacion }}d) @endif
                                                    </span>
                                                  @endif
                                                @elseif($item->tipoBajaAlta)
                                                  <span class="badge bg-label-dark py-1">
                                                    <i class="ti ti-user-x me-1"></i> Baja/Alta: {{ $item->tipoBajaAlta->nombre }}
                                                    @if($item->estado_reporte_dado_baja !== null) ({{ $item->estado_reporte_dado_baja ? 'Baja' : 'Alta' }}) @endif
                                                  </span>
                                                @elseif($item->estado_matricula)
                                                  <span class="badge bg-label-success py-1">
                                                    <i class="ti ti-school me-1"></i> Matrícula: {{ $item->estado_matricula }}
                                                  </span>
                                                @elseif($item->fecha_creacion)
                                                  <span class="badge bg-label-info py-1">
                                                    <i class="ti ti-user-plus me-1"></i> Usuarios Nuevos
                                                  </span>
                                                @else
                                                  <span class="badge bg-label-secondary py-1">Métrica Estándar</span>
                                                @endif

                                                <!-- Desglose Temporal -->
                                                @if($item->visualizar_por_mes)
                                                  <span class="badge bg-label-primary fs-9 py-1" title="Desglose mensual"><i class="ti ti-calendar me-1"></i> Por mes</span>
                                                @elseif($item->visualizar_por_semanas)
                                                  <span class="badge bg-label-info fs-9 py-1" title="Desglose semanal"><i class="ti ti-calendar-week me-1"></i> Por semana</span>
                                                @else
                                                  <span class="badge bg-label-secondary fs-9 py-1" title="Total acumulado en una sola columna">Valor único</span>
                                                @endif

                                                <!-- Filtros Demográficos -->
                                                @if($item->grupo_personas == 2)
                                                  <span class="badge bg-label-success fs-9 py-1">Solo Activos</span>
                                                @elseif($item->grupo_personas == 3)
                                                  <span class="badge bg-label-danger fs-9 py-1">Solo Bajas</span>
                                                @endif
                                                @if($item->filtrar_tipo_vinculacion)
                                                  <span class="badge bg-label-secondary fs-9 py-1" title="Tipos de vinculación">Vinc: {{ $item->filtrar_tipo_vinculacion }}</span>
                                                @endif
                                                @if($item->filtrar_estado_civil)
                                                  <span class="badge bg-label-secondary fs-9 py-1" title="Estados civiles">Civ: {{ $item->filtrar_estado_civil }}</span>
                                                @endif
                                              </div>
                                            </div>

                                            <!-- Acciones Métrica -->
                                            <div class="d-flex align-items-center gap-1 ms-auto">
                                              <button type="button" wire:click="editarItem({{ $item->id }})" class="btn btn-xs btn-icon btn-outline-secondary rounded-pill waves-effect" title="Editar Métrica">
                                                <i class="ti ti-edit"></i>
                                              </button>
                                              <button type="button" wire:click="confirmarEliminarItem({{ $item->id }})" class="btn btn-xs btn-icon btn-outline-danger rounded-pill waves-effect" title="Eliminar Métrica">
                                                <i class="ti ti-trash"></i>
                                              </button>
                                            </div>
                                          </div>
                                        </div>
                                      @endforeach
                                    </div>
                                  @endif
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      @endif

      <!-- ========================================================================= -->
      <!-- CONTENIDO TAB 2: BLOQUES DE SEDES -->
      <!-- ========================================================================= -->
      @if($tabActiva === 'bloques')
        <div class="card shadow-sm border-0">
          <div class="card-header d-flex justify-content-between align-items-center bg-light-subtle py-3 border-bottom">
            <div>
              <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="ti ti-building-community text-primary me-2"></i> Bloques de Sedes / Subtotales
              </h5>
              <small class="text-muted">Define los bloques y agrupaciones de sedes para consolidar datos en las pestañas del informe generado.</small>
            </div>
            <button type="button" wire:click="crearBloque" class="btn btn-primary rounded-pill btn-sm px-3 shadow-sm">
              <i class="ti ti-plus me-1"></i> Añadir Bloque
            </button>
          </div>

          <div class="card-body p-3 p-md-4">
            @if($informeSeleccionado->bloques->isEmpty())
              <div class="text-center py-5">
                <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3">
                  <i class="ti ti-building-plus fs-1"></i>
                </div>
                <h5 class="fw-semibold text-dark">No hay bloques de sedes configurados</h5>
                <p class="text-muted small max-w-500 mx-auto">
                  Los bloques permiten agrupar sedes específicas (o todas) para emitir subtotales consolidados por región, ciudad o campus.
                </p>
                <button type="button" wire:click="crearBloque" class="btn btn-primary rounded-pill btn-sm px-4">
                  <i class="ti ti-plus me-1"></i> Crear Primer Bloque
                </button>
              </div>
            @else
              <div class="row g-3">
                @foreach($informeSeleccionado->bloques as $bloque)
                  <div class="col-12 col-md-6 col-lg-4" wire:key="bloque-card-{{ $bloque->id }}">
                    <div class="card border rounded-3 h-100 shadow-none hover-shadow-sm transition-all">
                      <div class="card-header bg-light p-3 d-flex justify-content-between align-items-center border-bottom">
                        <h6 class="mb-0 fw-bold text-dark">
                          <i class="ti ti-building me-1 text-primary"></i> {{ $bloque->nombre }}
                        </h6>
                        <div class="dropdown">
                          <button class="btn btn-sm btn-icon btn-outline-secondary rounded-pill" type="button" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0);" wire:click="editarBloque({{ $bloque->id }})">
                                <i class="ti ti-edit me-2"></i> Editar
                              </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <a class="dropdown-item text-danger" href="javascript:void(0);" wire:click="confirmarEliminarBloque({{ $bloque->id }})">
                                <i class="ti ti-trash me-2"></i> Eliminar
                              </a>
                            </li>
                          </ul>
                        </div>
                      </div>

                      <div class="card-body p-3">
                        <small class="fw-semibold text-muted d-block mb-2">Sedes incluidas en este bloque:</small>
                        <div class="d-flex flex-wrap gap-1">
                          @php
                            $sedesIds = !empty($bloque->sedes) ? explode(',', $bloque->sedes) : [];
                            $sedesDelBloque = $sedes->whereIn('id', $sedesIds);
                          @endphp
                          @forelse($sedesDelBloque as $s)
                            <span class="badge bg-label-primary rounded-pill">
                              <i class="ti ti-map-pin me-1"></i> {{ $s->nombre }}
                            </span>
                          @empty
                            <span class="text-muted small">Sin sedes asignadas.</span>
                          @endforelse
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      @endif
    </div>
  @else
    <div class="card border-0 shadow-sm text-center py-5">
      <div class="card-body">
        <div class="avatar avatar-xl bg-label-primary mx-auto mb-3">
          <i class="ti ti-file-settings fs-1"></i>
        </div>
        <h5 class="fw-semibold text-dark">Ninguna plantilla de informe seleccionada</h5>
        <p class="text-muted small max-w-500 mx-auto">
          Selecciona una plantilla del listado superior o crea una nueva para comenzar a configurar sus columnas, fórmulas y sedes.
        </p>
        <button type="button" wire:click="crearInforme" class="btn btn-primary rounded-pill px-4">
          <i class="ti ti-plus me-1"></i> Crear nueva plantilla
        </button>
      </div>
    </div>
  @endif

  <!-- ========================================================================= -->
  <!-- OFFCANVAS: INFORME PERSONALIZADO (CREAR / EDITAR) -->
  <!-- ========================================================================= -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasInforme" wire:ignore.self style="width: 500px;">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title fw-bold text-primary">
        <i class="ti ti-file-settings me-1"></i> {{ $editInformeId ? 'Editar plantilla de informe' : 'Nueva plantilla de informe' }}
      </h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
      <form wire:submit.prevent="guardarInforme">
        <div class="mb-3">
          <label class="form-label fw-bold">Nombre del informe <span class="text-danger">*</span></label>
          <input type="text" wire:model="informeNombre" class="form-control" placeholder="Nombre del informe" required>
          @error('informeNombre') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea wire:model="informeDescripcion" rows="3" class="form-control" placeholder="Explica el propósito de este informe..."></textarea>
          @error('informeDescripcion') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Texto del botón</label>
          <input type="text" wire:model="informeNombreBoton" class="form-control" placeholder="Ej. Generar">
          @error('informeNombreBoton') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Categoría del informe <span class="text-danger">*</span></label>
          <select wire:model="informeTipoInformeId" class="form-select" required>
            <option value="">-- Seleccione categoría --</option>
            @foreach($tiposInforme as $tipo)
              <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
            @endforeach
          </select>
          @error('informeTipoInformeId') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>



        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
          <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="offcanvas">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <span wire:loading.remove wire:target="guardarInforme"><i class="ti ti-device-floppy me-1"></i> Guardar</span>
            <span wire:loading wire:target="guardarInforme"><span class="spinner-border spinner-border-sm me-1"></span> Guardando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================================================= -->
  <!-- OFFCANVAS: SECCIÓN (NIVEL 1) -->
  <!-- ========================================================================= -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSeccion" wire:ignore.self style="width: 450px;">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title fw-bold text-primary">
        <i class="ti ti-folder me-1"></i> {{ $seccionId ? 'Editar Sección (Nivel 1)' : 'Nueva Sección (Nivel 1)' }}
      </h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
      <form wire:submit.prevent="guardarSeccion">
        <div class="mb-3">
          <label class="form-label fw-bold">Nombre de la Sección <span class="text-danger">*</span></label>
          <input type="text" wire:model="seccionNombre" class="form-control" placeholder="Ej: Crecimiento Espiritual" required>
          @error('seccionNombre') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">Posición / Orden</label>
          <input type="number" wire:model="seccionOrden" class="form-control" min="0" placeholder="0">
          <small class="text-muted">Define el orden de aparición de izquierda a derecha en el informe.</small>
          @error('seccionOrden') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
          <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="offcanvas">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <span wire:loading.remove wire:target="guardarSeccion"><i class="ti ti-device-floppy me-1"></i> Guardar</span>
            <span wire:loading wire:target="guardarSeccion"><span class="spinner-border spinner-border-sm me-1"></span> Guardando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================================================= -->
  <!-- OFFCANVAS: SUBSECCIÓN (NIVEL 2) -->
  <!-- ========================================================================= -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSubseccion" wire:ignore.self style="width: 450px;">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title fw-bold text-primary">
        <i class="ti ti-subtask me-1"></i> {{ $subseccionId ? 'Editar Subsección (Nivel 2)' : 'Nueva Subsección (Nivel 2)' }}
      </h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
      <form wire:submit.prevent="guardarSubseccion">
        <div class="mb-3">
          <label class="form-label fw-bold">Sección Padre <span class="text-danger">*</span></label>
          <select wire:model="subseccionSeccionId" class="form-select" required>
            <option value="">-- Seleccionar Sección --</option>
            @if($informeSeleccionado)
              @foreach($informeSeleccionado->secciones->sortBy('orden') as $sec)
                <option value="{{ $sec->id }}">{{ $sec->nombre }} (Orden #{{ $sec->orden }})</option>
              @endforeach
            @endif
          </select>
          @error('subseccionSeccionId') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Nombre de la Subsección <span class="text-danger">*</span></label>
          <input type="text" wire:model="subseccionNombre" class="form-control" placeholder="Ej: Bautismos" required>
          @error('subseccionNombre') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">Posición / Orden</label>
          <input type="number" wire:model="subseccionOrden" class="form-control" min="0" placeholder="0">
          <small class="text-muted">Orden de aparición dentro de la sección.</small>
          @error('subseccionOrden') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
          <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="offcanvas">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <span wire:loading.remove wire:target="guardarSubseccion"><i class="ti ti-device-floppy me-1"></i> Guardar</span>
            <span wire:loading wire:target="guardarSubseccion"><span class="spinner-border spinner-border-sm me-1"></span> Guardando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================================================= -->
  <!-- OFFCANVAS: BLOQUE DE SEDES -->
  <!-- ========================================================================= -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasBloque" wire:ignore.self style="width: 500px;">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title fw-bold text-primary">
        <i class="ti ti-building me-1"></i> {{ $bloqueId ? 'Editar Bloque de Sedes' : 'Nuevo Bloque de Sedes' }}
      </h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
      <form wire:submit.prevent="guardarBloque">
        <div class="mb-3">
          <label class="form-label fw-bold">Nombre del Bloque <span class="text-danger">*</span></label>
          <input type="text" wire:model="bloqueNombre" class="form-control" placeholder="Ej: Sedes Principales / Antioquia" required>
          @error('bloqueNombre') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">Seleccionar Sedes Pertenecientes</label>
          <div class="border rounded p-3 bg-light" style="max-height: 280px; overflow-y: auto;">
            @foreach($sedes as $s)
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" wire:model="bloqueSedesIds" value="{{ $s->id }}" id="sede_chk_{{ $s->id }}">
                <label class="form-check-label" for="sede_chk_{{ $s->id }}">
                  {{ $s->nombre }}
                </label>
              </div>
            @endforeach
          </div>
          <small class="text-muted">Las sedes seleccionadas sumarán sus datos en este bloque del informe.</small>
          @error('bloqueSedesIds') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
          <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="offcanvas">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <span wire:loading.remove wire:target="guardarBloque"><i class="ti ti-device-floppy me-1"></i> Guardar</span>
            <span wire:loading wire:target="guardarBloque"><span class="spinner-border spinner-border-sm me-1"></span> Guardando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================================================= -->
  <!-- OFFCANVAS: ITEM / MÉTRICA (NIVEL 3 - CONFIGURACIÓN AVANZADA) -->
  <!-- ========================================================================= -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasItem" wire:ignore.self style="width: 620px;">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title fw-bold text-primary">
        <i class="ti ti-chart-dots me-1"></i> {{ $itemId ? 'Editar Métrica / Ítem' : 'Nueva Métrica / Ítem (Nivel 3)' }}
      </h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
      <form wire:submit.prevent="guardarItem">
        <!-- DATOS BÁSICOS -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-md-8">
            <label class="form-label fw-bold">Nombre de la Columna / Métrica <span class="text-danger">*</span></label>
            <input type="text" wire:model="itemNombre" class="form-control" placeholder="Ej: Bautizados en el periodo" required>
            @error('itemNombre') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label fw-bold">Orden</label>
            <input type="number" wire:model="itemOrden" class="form-control" min="0" placeholder="0">
            @error('itemOrden') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Subsección Contenedora <span class="text-danger">*</span></label>
            <select wire:model="itemSubseccionId" class="form-select" required>
              <option value="">-- Seleccionar Subsección --</option>
              @if($informeSeleccionado)
                @foreach($informeSeleccionado->secciones->sortBy('orden') as $sec)
                  <optgroup label="Sección: {{ $sec->nombre }}">
                    @foreach($sec->subsecciones->sortBy('orden') as $sub)
                      <option value="{{ $sub->id }}">{{ $sec->nombre }} &rarr; {{ $sub->nombre }}</option>
                    @endforeach
                  </optgroup>
                @endforeach
              @endif
            </select>
            @error('itemSubseccionId') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>
        </div>

        <!-- TIPO DE ÍTEM (MÉTRICA DIRECTA, OPERACIÓN ARITMÉTICA O TOTALIZADOR) -->
        <div class="mb-3">
          <label class="form-label fw-bold text-dark d-block">Tipo de Cálculo / Fuente de Datos:</label>
          <div class="btn-group w-100" role="group">
            <input type="radio" class="btn-check" name="itemTipoRadio" id="tipoMetrica" value="metrica" wire:model.live="itemTipo">
            <label class="btn btn-outline-primary" for="tipoMetrica">
              <i class="ti ti-stairs me-1"></i> Métrica / Filtro
            </label>

            <input type="radio" class="btn-check" name="itemTipoRadio" id="tipoOperacion" value="operacion" wire:model.live="itemTipo">
            <label class="btn btn-outline-primary" for="tipoOperacion">
              <i class="ti ti-calculator me-1"></i> Operación (A op B)
            </label>

            <input type="radio" class="btn-check" name="itemTipoRadio" id="tipoTotalizador" value="totalizador" wire:model.live="itemTipo">
            <label class="btn btn-outline-primary" for="tipoTotalizador">
              <i class="ti ti-sum me-1"></i> Totalizador
            </label>
          </div>
        </div>

        <!-- DESGLOSE TEMPORAL -->
        <div class="card bg-light border-0 p-3 mb-3">
          <label class="form-label fw-bold text-dark mb-2"><i class="ti ti-calendar-time me-1"></i> Desglose Temporal en el Informe:</label>
          <div class="row g-2">
            <div class="col-12 col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="itemDesgloseRadio" id="desgloseNinguno" value="ninguno" wire:model="itemDesgloseTemporal">
                <label class="form-check-label small fw-semibold" for="desgloseNinguno">
                  <i class="ti ti-chart-bar me-1 text-secondary"></i> Valor único
                </label>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="itemDesgloseRadio" id="desgloseMes" value="mes" wire:model="itemDesgloseTemporal">
                <label class="form-check-label small fw-semibold" for="desgloseMes">
                  <i class="ti ti-calendar me-1 text-primary"></i> Por mes
                </label>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="itemDesgloseRadio" id="desgloseSemana" value="semanas" wire:model="itemDesgloseTemporal">
                <label class="form-check-label small fw-semibold" for="desgloseSemana">
                  <i class="ti ti-calendar-week me-1 text-info"></i> Por semana
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- =================================================================== -->
        <!-- CAMPOS CONDICIONALES SEGÚN TIPO -->
        <!-- =================================================================== -->

        <!-- CASO 1: OPERACIÓN ARITMÉTICA -->
        @if($itemTipo === 'operacion')
          <div class="card border border-warning-subtle bg-warning-subtle p-3 mb-3">
            <h6 class="fw-bold text-dark mb-1"><i class="ti ti-calculator me-1"></i> Configuración de Fórmula Aritmética</h6>
            <p class="text-muted small mb-2 fs-8">Calcula un resultado matemático entre dos métricas existentes (ej. Tasa de conversión: Paso B / Paso A).</p>
            <div class="row g-2">
              <div class="col-12">
                <label class="form-label small fw-bold">Primer Ítem (A):</label>
                <select wire:model="itemItemA" class="form-select form-select-sm">
                  <option value="">-- Seleccione Ítem A --</option>
                  @foreach($itemsDelInforme as $it)
                    <option value="{{ $it->id }}">{{ $it->nombre }} (ID #{{ $it->id }})</option>
                  @endforeach
                </select>
                @error('itemItemA') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>

              <div class="col-12">
                <label class="form-label small fw-bold">Operador:</label>
                <select wire:model="itemOperacion" class="form-select form-select-sm">
                  <option value="">-- Seleccione Operación --</option>
                  <option value="1">Suma (+)</option>
                  <option value="2">Resta (-)</option>
                  <option value="3">Multiplicación (*)</option>
                  <option value="4">División (/)</option>
                  <option value="5">Promedio</option>
                </select>
                @error('itemOperacion') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>

              <div class="col-12">
                <label class="form-label small fw-bold">Segundo Ítem (B):</label>
                <select wire:model="itemItemB" class="form-select form-select-sm">
                  <option value="">-- Seleccione Ítem B --</option>
                  @foreach($itemsDelInforme as $it)
                    <option value="{{ $it->id }}">{{ $it->nombre }} (ID #{{ $it->id }})</option>
                  @endforeach
                </select>
                @error('itemItemB') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
            </div>
          </div>
        @endif

        <!-- CASO 2: TOTALIZADOR -->
        @if($itemTipo === 'totalizador')
          <div class="card border border-info-subtle bg-info-subtle p-3 mb-3">
            <h6 class="fw-bold text-dark mb-1"><i class="ti ti-sum me-1"></i> Selección de Ítems a Sumar en Totalizador</h6>
            <p class="text-muted small mb-2 fs-8">Consolida automáticamente la suma acumulada de varias columnas seleccionadas.</p>
            <div class="border rounded p-2 bg-white" style="max-height: 200px; overflow-y: auto;">
              @foreach($itemsDelInforme as $it)
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" wire:model="itemTotalizarItemsIds" value="{{ $it->id }}" id="tot_it_{{ $it->id }}">
                  <label class="form-check-label small" for="tot_it_{{ $it->id }}">
                    {{ $it->nombre }} <span class="text-muted">(ID #{{ $it->id }})</span>
                  </label>
                </div>
              @endforeach
            </div>
            @error('itemTotalizarItemsIds') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>
        @endif

        <!-- CASO 3: MÉTRICA DIRECTA CON REGLAS Y FILTROS -->
        @if($itemTipo === 'metrica')
          <!-- REGLA: PASO DE CRECIMIENTO 1 -->
          <div class="card border p-3 mb-3">
            <h6 class="fw-bold text-primary mb-1"><i class="ti ti-stairs me-1"></i> Paso de Crecimiento Principal</h6>
            <p class="text-muted small mb-2 fs-8">Filtra personas que alcanzaron o están cursando un paso específico de la ruta de crecimiento.</p>
            <div class="row g-2">
              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Paso de Crecimiento:</label>
                <select wire:model="itemPasoCrecimientoId" class="form-select form-select-sm">
                  <option value="">-- Ninguno / General --</option>
                  @foreach($pasosCrecimiento as $pc)
                    <option value="{{ $pc->id }}">{{ $pc->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Estado del Paso:</label>
                <select wire:model="itemEstadoPasoCrecimiento" class="form-select form-select-sm">
                  <option value="">-- Cualquier Estado --</option>
                  @foreach($estadosPasos as $ep)
                    <option value="{{ $ep->id }}">{{ $ep->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12">
                <div class="form-check mt-1">
                  <input class="form-check-input" type="checkbox" wire:model="itemFiltrarFechaPasoCrecimiento" id="chkFechaPaso1">
                  <label class="form-check-label small" for="chkFechaPaso1">Filtrar por la fecha del paso en el rango seleccionado</label>
                </div>
              </div>
            </div>
          </div>

          <!-- REGLA: COMPARACIÓN / DILACIÓN -->
          <div class="card border p-3 mb-3">
            <h6 class="fw-bold text-dark mb-1"><i class="ti ti-git-compare me-1"></i> Comparación / Dilación de Pasos</h6>
            <p class="text-muted small mb-2 fs-8">Permite contrastar dos eventos (ej. usuarios que hicieron el Paso 1 pero NO el Paso 2) o medir días transcurridos entre ellos.</p>
            <div class="row g-2">
              <div class="col-12">
                <label class="form-label small fw-semibold">Comparar con:</label>
                <select wire:model.live="itemParametroComparacion" class="form-select form-select-sm">
                  <option value="">Sin comparación adicional</option>
                  <option value="paso-crecimiento">Otro Paso de Crecimiento (Paso 2)</option>
                  <option value="fecha-creacion">Fecha de Creación del Usuario</option>
                </select>
              </div>

              @if($itemParametroComparacion === 'paso-crecimiento')
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold">Paso 2 a Comparar:</label>
                  <select wire:model="itemPasoCrecimientoId2" class="form-select form-select-sm">
                    <option value="">-- Seleccione Paso 2 --</option>
                    @foreach($pasosCrecimiento as $pc)
                      <option value="{{ $pc->id }}">{{ $pc->nombre }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold">Estado del Paso 2:</label>
                  <select wire:model="itemEstadoPasoCrecimiento2" class="form-select form-select-sm">
                    <option value="">-- Cualquier Estado --</option>
                    @foreach($estadosPasos as $ep)
                      <option value="{{ $ep->id }}">{{ $ep->nombre }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" wire:model="itemNoExistePasoCrecimientoId2" id="chkNoExistePaso2">
                    <label class="form-check-label small text-danger" for="chkNoExistePaso2">
                      Filtrar usuarios que <strong>NO</strong> tengan el Paso 2
                    </label>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" wire:model="itemFiltrarFechaPasoCrecimiento2" id="chkFechaPaso2">
                    <label class="form-check-label small" for="chkFechaPaso2">Filtrar por la fecha del paso 2</label>
                  </div>
                </div>
              @endif

              @if($itemParametroComparacion)
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold">Días de Dilación (Opcional):</label>
                  <input type="number" wire:model="itemCantidadDiasDilacion" class="form-control form-select-sm" placeholder="Ej: 30">
                  <small class="text-muted fs-9">Diferencia en días entre los dos eventos.</small>
                </div>
              @endif
            </div>
          </div>

          <!-- REGLA: PERSONAS Y DEMOGRAFÍA -->
          <div class="card border p-3 mb-3">
            <h6 class="fw-bold text-dark mb-1"><i class="ti ti-users me-1"></i> Filtro de Personas & Demografía</h6>
            <p class="text-muted small mb-2 fs-8">Segmenta por estado activo/inactivo, fecha de creación del perfil, canales de vinculación y estado civil.</p>
            <div class="row g-2">
              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Estado de Personas:</label>
                <select wire:model="itemGrupoPersonas" class="form-select form-select-sm">
                  <option value="1">Todos los usuarios</option>
                  <option value="2">Solo usuarios dados de Alta (Activos)</option>
                  <option value="3">Solo usuarios dados de Baja (Inactivos)</option>
                </select>
              </div>

              <div class="col-12 col-md-6">
                <div class="form-check mt-3">
                  <input class="form-check-input" type="checkbox" wire:model="itemFechaCreacion" id="chkFechaCreacionUser">
                  <label class="form-check-label small" for="chkFechaCreacionUser">Filtrar por fecha de creación del usuario en el rango</label>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Tipos de Vinculación:</label>
                <div class="border rounded p-2 bg-light" style="max-height: 120px; overflow-y: auto;">
                  @foreach($tiposVinculacion as $tv)
                    <div class="form-check form-check-sm mb-1">
                      <input class="form-check-input" type="checkbox" wire:model="itemTiposVinculacionIds" value="{{ $tv->id }}" id="tv_{{ $tv->id }}">
                      <label class="form-check-label small" for="tv_{{ $tv->id }}">{{ $tv->nombre }}</label>
                    </div>
                  @endforeach
                </div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Estados Civiles:</label>
                <div class="border rounded p-2 bg-light" style="max-height: 120px; overflow-y: auto;">
                  @foreach($estadosCiviles as $ec)
                    <div class="form-check form-check-sm mb-1">
                      <input class="form-check-input" type="checkbox" wire:model="itemEstadosCivilesIds" value="{{ $ec->id }}" id="ec_{{ $ec->id }}">
                      <label class="form-check-label small" for="ec_{{ $ec->id }}">{{ $ec->nombre }}</label>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>

          <!-- REGLA: BAJAS / ALTAS & MATRÍCULAS -->
          <div class="card border p-3 mb-3">
            <h6 class="fw-bold text-dark mb-1"><i class="ti ti-activity me-1"></i> Bajas, Altas & Matrículas</h6>
            <p class="text-muted small mb-2 fs-8">Filtra por novedades pastorales (motivos de retiro o alta) y estado de inscripciones en la escuela.</p>
            <div class="row g-2">
              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Motivo Baja / Alta:</label>
                <select wire:model="itemTipoBajaAltaId" class="form-select form-select-sm">
                  <option value="">-- No aplicar filtro --</option>
                  @foreach($tiposBajaAlta as $tba)
                    <option value="{{ $tba->id }}">{{ $tba->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Tipo de Reporte:</label>
                <select wire:model="itemEstadoReporteDadoBaja" class="form-select form-select-sm">
                  <option value="">-- Ambos --</option>
                  <option value="0">Dado de Alta</option>
                  <option value="1">Dado de Baja</option>
                </select>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" wire:model="itemFiltrarFechaReporteBajaAlta" id="chkFechaBajaAlta">
                  <label class="form-check-label small" for="chkFechaBajaAlta">Filtrar por fecha del reporte de baja/alta en el rango</label>
                </div>
              </div>

              <div class="col-12 col-md-6 mt-2">
                <label class="form-label small fw-semibold">Estado de Matrícula (Escuela):</label>
                <select wire:model="itemEstadoMatricula" class="form-select form-select-sm">
                  <option value="">-- No aplicar filtro --</option>
                  @foreach($estadosMatricula as $em)
                    <option value="{{ $em->nombre }}">{{ $em->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 col-md-6 mt-2">
                <div class="form-check mt-3">
                  <input class="form-check-input" type="checkbox" wire:model="itemFiltroFechaMatricula" id="chkFechaMatricula">
                  <label class="form-check-label small" for="chkFechaMatricula">Filtrar por fecha de matrícula en el rango</label>
                </div>
              </div>
            </div>
          </div>
        @endif

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
          <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="offcanvas">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <span wire:loading.remove wire:target="guardarItem"><i class="ti ti-device-floppy me-1"></i> Guardar</span>
            <span wire:loading wire:target="guardarItem"><span class="spinner-border spinner-border-sm me-1"></span> Guardando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>

@script
<script>
  // Listeners de apertura y cierre de Offcanvas
  $wire.on('abrir-offcanvas-informe', () => {
    const el = document.getElementById('offcanvasInforme');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getOrCreateInstance(el).show();
  });
  $wire.on('cerrar-offcanvas-informe', () => {
    const el = document.getElementById('offcanvasInforme');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getInstance(el)?.hide();
  });

  $wire.on('abrir-offcanvas-seccion', () => {
    const el = document.getElementById('offcanvasSeccion');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getOrCreateInstance(el).show();
  });
  $wire.on('cerrar-offcanvas-seccion', () => {
    const el = document.getElementById('offcanvasSeccion');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getInstance(el)?.hide();
  });

  $wire.on('abrir-offcanvas-subseccion', () => {
    const el = document.getElementById('offcanvasSubseccion');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getOrCreateInstance(el).show();
  });
  $wire.on('cerrar-offcanvas-subseccion', () => {
    const el = document.getElementById('offcanvasSubseccion');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getInstance(el)?.hide();
  });

  $wire.on('abrir-offcanvas-bloque', () => {
    const el = document.getElementById('offcanvasBloque');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getOrCreateInstance(el).show();
  });
  $wire.on('cerrar-offcanvas-bloque', () => {
    const el = document.getElementById('offcanvasBloque');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getInstance(el)?.hide();
  });

  $wire.on('abrir-offcanvas-item', () => {
    const el = document.getElementById('offcanvasItem');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getOrCreateInstance(el).show();
  });
  $wire.on('cerrar-offcanvas-item', () => {
    const el = document.getElementById('offcanvasItem');
    if (el) (window.bootstrap || bootstrap).Offcanvas.getInstance(el)?.hide();
  });

  // SweetAlert: Mensajes informativos / éxito / error
  $wire.on('msn', (data) => {
    const payload = Array.isArray(data) ? data[0] : (data?.detail || data);
    Swal.fire({
      icon: payload.icono || 'info',
      title: payload.titulo || 'Atención',
      text: payload.texto || '',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
  });

  // SweetAlert: Confirmación centralizada de eliminación
  $wire.on('mostrar-confirmacion-eliminar', (data) => {
    const payload = Array.isArray(data) ? data[0] : (data?.detail || data);
    Swal.fire({
      title: payload.titulo || '¿Estás seguro?',
      text: payload.texto || 'Esta acción no se puede deshacer.',
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
        if (payload.tipo === 'informe') {
          $wire.eliminarInforme(payload.id);
        } else if (payload.tipo === 'seccion') {
          $wire.eliminarSeccion(payload.id);
        } else if (payload.tipo === 'subseccion') {
          $wire.eliminarSubseccion(payload.id);
        } else if (payload.tipo === 'bloque') {
          $wire.eliminarBloque(payload.id);
        } else if (payload.tipo === 'item') {
          $wire.eliminarItem(payload.id);
        }
      }
    });
  });
  // SortableJS: Inicializadores de Drag & Drop para Secciones, Subsecciones y Métricas
  window.initSortableSecciones = function() {
    const contenedor = document.getElementById('accordionSecciones');
    if (!contenedor || !window.Sortable) return;
    if (contenedor.sortableInstance) {
      contenedor.sortableInstance.destroy();
    }
    contenedor.sortableInstance = Sortable.create(contenedor, {
      animation: 150,
      handle: '.drag-handle-seccion',
      draggable: '.seccion-item',
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen',
      onEnd: function () {
        const items = contenedor.querySelectorAll(':scope > .seccion-item');
        const nuevoOrden = [];
        items.forEach((el, index) => {
          nuevoOrden.push({
            id: el.dataset.seccionId,
            orden: index + 1
          });
        });
        $wire.actualizarOrdenSecciones(nuevoOrden);
      }
    });
  };

  window.initSortableSubsecciones = function() {
    const contenedores = document.querySelectorAll('.listadoSubsecciones');
    if (!window.Sortable) return;
    contenedores.forEach(contenedor => {
      if (contenedor.sortableInstance) {
        contenedor.sortableInstance.destroy();
      }
      contenedor.sortableInstance = Sortable.create(contenedor, {
        animation: 150,
        handle: '.drag-handle-subseccion',
        draggable: '.subseccion-item',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function () {
          const seccionId = contenedor.dataset.seccionId;
          const items = contenedor.querySelectorAll(':scope > .subseccion-item');
          const nuevoOrden = [];
          items.forEach((el, index) => {
            nuevoOrden.push({
              id: el.dataset.subseccionId,
              orden: index + 1
            });
          });
          $wire.actualizarOrdenSubsecciones(seccionId, nuevoOrden);
        }
      });
    });
  };

  window.initSortableItems = function() {
    const contenedores = document.querySelectorAll('.listadoItems');
    if (!window.Sortable) return;
    contenedores.forEach(contenedor => {
      if (contenedor.sortableInstance) {
        contenedor.sortableInstance.destroy();
      }
      contenedor.sortableInstance = Sortable.create(contenedor, {
        animation: 150,
        handle: '.drag-handle-item',
        draggable: '.item-card',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function () {
          const subseccionId = contenedor.dataset.subseccionId;
          const items = contenedor.querySelectorAll(':scope > .item-card');
          const nuevoOrden = [];
          items.forEach((el, index) => {
            nuevoOrden.push({
              id: el.dataset.itemId,
              orden: index + 1
            });
          });
          $wire.actualizarOrdenItems(subseccionId, nuevoOrden);
        }
      });
    });
  };

  window.initAllSortables = function() {
    window.initSortableSecciones();
    window.initSortableSubsecciones();
    window.initSortableItems();
  };

  document.addEventListener('livewire:initialized', () => {
    setTimeout(() => {
      window.initAllSortables();
    }, 150);
  });

  Livewire.hook('morph.updated', () => {
    setTimeout(() => {
      window.initAllSortables();
    }, 150);
  });

  $wire.on('refreshSortable', () => {
    setTimeout(() => {
      window.initAllSortables();
    }, 150);
  });
</script>
@endscript
</div>
