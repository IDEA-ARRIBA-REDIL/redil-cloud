<div>

  <div class="d-flex flex-row-reverse">
    <a href="javascript:void(0);" wire:click="crearZona" class="btn btn-primary rounded-pill px-7 py-2"><i class="ti ti-plus me-2"></i> Nueva zona</a>
  </div>


  <div class="row g-4 pt-10">
    <div class="col-10 col-md-5 offset-md-2 mt-3">
      <div class="input-group">
        <input wire:model.live.debounce.500ms="busqueda" type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Buscar">
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="form-check my-auto">
        <input class="form-check-input" type="checkbox" wire:model.live="conEliminados">
        <label class="form-check-label">
          ¿Mostrar ocultos?
        </label>
      </div>
    </div>
  </div>


  <div class="row g-4 mt-5">

    @if($zonas->count()>0)
      @foreach( $zonas as $zona )
          <div class=" col equal-height-col col-12 col-md-6">
              <div class="card rounded-3 shadow">
                  <div class="card-header border-bottom d-flex p-4" style="background-color:#F9F9F9!important">

                    <div class="flex-fill row">

                      <div class="col-12 col-md-12">
                        <div class="d-flex flex-row">
                          <div class="d-flex flex-column">
                            <small class="fw-semibold ms-1 text-black">Zona:</small>
                            <small class="fw-semibold ms-1 text-black fs-5">{{ $zona->nombre }}</small>
                          </div>
                        </div>
                      </div>

                    </div>

                    <div class="flex-fill">
                      <div class="ms-auto">
                        <div class="dropdown zindex-2 p-1 float-end">
                          <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect"  data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
                          <ul class="dropdown-menu dropdown-menu-end">
                            @if(!$conEliminados)
                                <li><a class="dropdown-item" href="javascript:void(0);"  wire:click="duplicarZona({{ $zona->id }})"> Duplicar</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" wire:click="editarZona({{ $zona->id }})"> Editar</a></li>
                                <hr class="dropdown-divider">
                                <li><a class="dropdown-item text-danger" href="javascript:void(0);"  wire:click="$dispatch('confirmarBaja',  { id: {{ $zona->id }} })" >Eliminar </a></li>
                            @else
                                <li><a class="dropdown-item" href="javascript:void(0);"  wire:click="$dispatch('confirmarRestauracion',  { id: {{ $zona->id }} })"> Restaurar</a></li>
                                <hr class="dropdown-divider">
                                <li><a class="dropdown-item text-danger" href="javascript:void(0);"  wire:click="$dispatch('confirmarEliminacionDefinitiva',  { id: {{ $zona->id }} })" >Eliminar Permanente</a></li>
                            @endif
                          </ul>
                        </div>
                      </div>
                    </div>

                  </div>
                  <div class="card-body  p-4 collapse @if(!($collapsedStates[$zona->id] ?? true)) show @endif" id="cardBody{{ $zona->id }}">

                      <div class="row">
                          <div class="col-12">
                              <small class="fw-semibold text-black">Descripción:</small>
                              <p class="text-black">{{ $zona->descripcion }}</p>
                          </div>

                          <div class="col-12">
                              <small class="fw-semibold text-black ms-1">Sedes:</small>
                              <div>
                                  @forelse ($zona->sedes as $sede)
                                  <span type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1">
                                    {{ $sede->nombre }}
                                  </span>
                                  @empty
                                  <p class="text-black">No indicada</p>
                                  @endforelse
                              </div>
                          </div>

                          <div class="col-12 mt-3">
                              <small class="fw-semibold text-black ms-1">Localidades:</small>
                              <div>
                                  @forelse ($zona->localidades as $localidad)
                                  <span type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1">
                                    {{ $localidad->nombre }}
                                  </span>
                                  @empty
                                  <p class="text-black">No indicada</p>
                                  @endforelse
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="card-footer border-top p-1">
                    <div class="d-flex justify-content-center">
                        <button type="button"
                                class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
                                wire:click="toggleCollapse({{ $zona->id }})">
                            <span class="ti {{ ($collapsedStates[$zona->id] ?? true) ? 'ti-plus' : 'ti-minus' }}"></span>
                        </button>
                    </div>
                  </div>
              </div>
          </div>
      @endforeach

      {{-- PAGINACIÓN --}}
      @if($zonas->hasPages())
        <div class="col-12 mt-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
          <span class="text-black small">
            Mostrando {{ $zonas->firstItem() }} a {{ $zonas->lastItem() }} de {{ $zonas->total() }} zonas
          </span>
          <div class="pagination-clean">
            {{ $zonas->links() }}
          </div>
        </div>
      @endif
    @else
    <div class="py-4 col-12">
      <div class="text-center">
        <i class="ti ti-browser fs-1 pb-1"></i>
        <h6 class="text-center">@if(!$conEliminados)¡Ups! no hay zonas creadas. @else ¡Ups! no hay zonas eliminadas. @endif </h6>
      </div>
    </div>
    @endif
  </div>

  <!-- Offcanvas para Crear / Editar Zona -->
  <div 
    class="offcanvas offcanvas-end" 
    tabindex="-1" 
    id="modalcrearEditarZona" 
    data-bs-backdrop="true"
    data-bs-scroll="false"
    wire:ignore.self 
    aria-labelledby="modalcrearEditarZonaLabel"
  >
    <form id="crearEditarZona" role="form" wire:submit.prevent="guardarZona" class="d-flex flex-column h-100 m-0 overflow-hidden">
      <div class="offcanvas-header border-bottom flex-shrink-0">
        <h5 class="offcanvas-title fw-semibold text-primary" id="modalcrearEditarZonaLabel">
          <i class="ti {{ $modoEdicion ? 'ti-pencil' : 'ti-plus' }} me-2"></i>
          {{ $modoEdicion ? 'Editar zona' : 'Nueva zona' }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body flex-grow-1 overflow-y-auto p-4">
        <p class="text-black small mb-3">Por favor ingresa la información correspondiente a la zona.</p>

        <div class="mb-3">
          <label class="form-label fw-semibold" for="nombre">Nombre <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-map-pin"></i></span>
            <input id="nombre" name="nombre" wire:model="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Zona Norte, Sector Centro..." autocomplete="off" />
          </div>
          @error('nombre')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold" for="descripcion">Descripción</label>
          <textarea id="descripcion" name="descripcion" wire:model="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Descripción breve de la zona..."></textarea>
          @error('descripcion')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="sedes" class="form-label fw-semibold mb-0">¿Qué sedes componen la zona? <span class="text-danger">*</span></label>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" id="btnSeleccionarTodasSedesZona">
                <i class="ti ti-checks me-1"></i>Todas
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnBorrarTodasSedesZona">
                <i class="ti ti-x me-1"></i>Borrar
              </button>
            </div>
          </div>
          <div wire:ignore>
            <select id="sedes" name="sedes[]" multiple class="form-select">
              @foreach ($sedes as $sede)
                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
              @endforeach
            </select>
          </div>
          @error('sedesSeleccionadas')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="localidades" class="form-label fw-semibold mb-0">¿Qué localidades componen la zona?</label>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" id="btnSeleccionarTodasLocalidadesZona">
                <i class="ti ti-checks me-1"></i>Todas
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnBorrarTodasLocalidadesZona">
                <i class="ti ti-x me-1"></i>Borrar
              </button>
            </div>
          </div>
          <div wire:ignore>
            <select id="localidades" name="localidades[]" multiple class="form-select">
              @foreach ($localidades as $localidad)
                <option value="{{ $localidad->id }}">{{ $localidad->nombre }} ({{ $localidad->municipio->nombre ?? 'N/A' }})</option>
              @endforeach
            </select>
          </div>
          @error('localidadesSeleccionadas')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="offcanvas-footer p-4 border-top flex-shrink-0 bg-white">
        <div class="d-flex justify-content-end gap-2">
          <button type="button" data-bs-dismiss="offcanvas" class="btn btn-label-secondary rounded-pill px-3">
            Cancelar
          </button>
          <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="guardarZona">
              <i class="ti ti-device-floppy me-1"></i> Guardar
            </span>
            <span wire:loading wire:target="guardarZona">
              <span class="spinner-border spinner-border-sm me-1" role="status"></span> Guardando...
            </span>
          </button>
        </div>
      </div>
    </form>
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
</div>



@script
<script>
  window.initSelect2Zonas = function() {
    if (typeof $.fn.select2 !== 'undefined') {
      const $sedes = $('#sedes');
      const $localidades = $('#localidades');

      // Desactivar listeners previos antes de re-inicializar
      $sedes.off('change');
      $localidades.off('change');

      if ($sedes.data('select2')) $sedes.select2('destroy');
      if ($localidades.data('select2')) $localidades.select2('destroy');

      $sedes.select2({
        placeholder: 'Seleccione una o más sedes',
        dropdownParent: $('#modalcrearEditarZona'),
        allowClear: true
      });
      $localidades.select2({
        placeholder: 'Seleccione una o más localidades',
        dropdownParent: $('#modalcrearEditarZona'),
        allowClear: true
      });

      // Sincronizar valores iniciales sin disparar eventos de change
      const sedesVals = $wire.get('sedesSeleccionadas') || [];
      const locVals = $wire.get('localidadesSeleccionadas') || [];
      $sedes.val(sedesVals).trigger('change.select2');
      $localidades.val(locVals).trigger('change.select2');

      // Listeners para sincronizar cambios a Livewire SIN peticiones innecesarias al servidor (live: false)
      $sedes.on('change', function () {
        const val = $(this).val() || [];
        $wire.set('sedesSeleccionadas', val, false);
      });
      $localidades.on('change', function () {
        const val = $(this).val() || [];
        $wire.set('localidadesSeleccionadas', val, false);
      });

      $('#btnSeleccionarTodasSedesZona').off('click').on('click', function(e) {
        e.preventDefault();
        $sedes.find('option').prop('selected', true);
        $sedes.trigger('change');
      });
      $('#btnBorrarTodasSedesZona').off('click').on('click', function(e) {
        e.preventDefault();
        $sedes.val(null).trigger('change');
      });

      $('#btnSeleccionarTodasLocalidadesZona').off('click').on('click', function(e) {
        e.preventDefault();
        $localidades.find('option').prop('selected', true);
        $localidades.trigger('change');
      });
      $('#btnBorrarTodasLocalidadesZona').off('click').on('click', function(e) {
        e.preventDefault();
        $localidades.val(null).trigger('change');
      });
    }
  };

  window.destroySelect2Zonas = function() {
    if (typeof $.fn.select2 !== 'undefined') {
      if ($('#sedes').data('select2')) $('#sedes').select2('destroy');
      if ($('#localidades').data('select2')) $('#localidades').select2('destroy');
    }
    $('#sedes').off('change');
    $('#localidades').off('change');
    $('#btnSeleccionarTodasSedesZona').off('click');
    $('#btnBorrarTodasSedesZona').off('click');
    $('#btnSeleccionarTodasLocalidadesZona').off('click');
    $('#btnBorrarTodasLocalidadesZona').off('click');
  };

  const getOffcanvasZona = () => {
    const el = document.getElementById('modalcrearEditarZona');
    if (!el) return null;
    return (window.bootstrap || bootstrap).Offcanvas.getOrCreateInstance(el, {
      backdrop: true,
      keyboard: true,
      scroll: false
    });
  };

  $wire.on('abrirModal', data => {
    const inst = getOffcanvasZona();
    if (inst) {
      inst.show();
      setTimeout(() => {
        window.initSelect2Zonas();
        const input = document.getElementById('nombre');
        if (input) input.focus();
      }, 150);
    }
  });

  $wire.on('cerrarModal', data => {
    const el = document.getElementById('modalcrearEditarZona');
    if (el) {
      const inst = (window.bootstrap || bootstrap).Offcanvas.getInstance(el);
      if (inst) inst.hide();
    }
    window.destroySelect2Zonas();
  });

  const modalEl = document.getElementById('modalcrearEditarZona');
  if (modalEl) {
    modalEl.addEventListener('hidden.bs.offcanvas', () => {
      window.destroySelect2Zonas();
    });
  }

  $wire.on('msn', data => {
    Swal.fire({
      title: data.msnTitulo,
      html: data.msnTexto,
      icon: data.msnIcono,
      customClass: { confirmButton: 'btn btn-primary rounded-pill px-4' },
      buttonsStyling: false
    });
  });

  $wire.on('confirmarBaja', data => {
    Swal.fire({
      title: '¿Deseas dar de baja esta zona?',
      text: "La zona se moverá a la papelera.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, dar de baja',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-primary rounded-pill px-4 me-3',
        cancelButton: 'btn btn-label-secondary rounded-pill px-4'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $wire.dispatch('eliminarZona', { id: data.id });
      }
    });
  });

  $wire.on('confirmarRestauracion', data => {
    Swal.fire({
      title: '¿Deseas restaurar esta zona?',
      text: "La zona volverá a estar activa.",
      icon: 'info',
      showCancelButton: true,
      confirmButtonText: 'Sí, restaurar',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-primary rounded-pill px-4 me-3',
        cancelButton: 'btn btn-label-secondary rounded-pill px-4'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $wire.dispatch('restaurarZona', { id: data.id });
      }
    });
  });

  $wire.on('confirmarEliminacionDefinitiva', data => {
    Swal.fire({
      title: '¿ELIMINAR PERMANENTEMENTE?',
      text: "Esta acción NO se puede deshacer.",
      icon: 'error',
      showCancelButton: true,
      confirmButtonText: 'Sí, ELIMINAR',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-danger rounded-pill px-4 me-3',
        cancelButton: 'btn btn-label-secondary rounded-pill px-4'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $wire.dispatch('eliminarPermanenteZona', { id: data.id });
      }
    });
  });
</script>
@endscript
