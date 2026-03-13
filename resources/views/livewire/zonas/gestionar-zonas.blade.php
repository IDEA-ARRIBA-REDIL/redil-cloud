<div>

  <div class="d-flex flex-row-reverse">
    <a href="javascript:void(0);" wire:click="crearZona" class="btn btn-primary rounded-pill px-7 py-2"><i class="ti ti-plus me-2"></i> Nueva zona</a>
  </div>


  <div class="row g-4 pt-10">
    <div class="col-12 col-md-6 offset-md-3 mt-3">
      <div class="input-group">
        <input wire:model.live.debounce.500ms="busqueda" type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Buscar">
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
                            <li><a class="dropdown-item" href="javascript:void(0);"  wire:click="duplicarZona({{ $zona->id }})"> Duplicar</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" wire:click="editarZona({{ $zona->id }})"> Editar</a></li>
                            <hr class="dropdown-divider">
                            <li><a class="dropdown-item text-danger" href="javascript:void(0);"  wire:click="$dispatch('eliminar',  { id: {{ $zona->id }} })" >Eliminar </a></li>
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
    <div class="mt-4">
        {{ $zonas->links() }}
    </div>
    @else
    <div class="py-4">
      <center>
        <i class="ti ti-browser fs-1 pb-1"></i>
        <h6 class="text-center">¡Ups! no hay zonas creadas. </h6>
      </center>
    </div>
    @endif
  </div>

  <!-- crear y editar campo  -->
  <form id="crearEditarZona" role="form" class="forms-sample" wire:submit.prevent="guardarZona" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar"  tabindex="-1" id="modalcrearEditarZona" aria-labelledby="modalcrearEditarZonaLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalcrearEditarZonaLabel">
              @if ($modoEdicion) Editar zona @else Nueva zona @endif
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
            <div class="mb-4">
                <span class="text-black ti-14px mb-4">Por favor ingresa toda la información.</span>
            </div>
            @csrf
            <div class="pt-3">
              <div class="mb-3 col-12">
                <label class="form-label" for="nombre">Nombre</label>
                <input id="nombre" name="nombre" wire:model.defer="nombre" type="text" class="form-control" />
                @error('nombre')
                <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                </div>
                @enderror
              </div>

              <div class="mb-3 col-12">
                <label class="form-label" for="placeholder">Descripción</label>
                <textarea id="descripcion" name="descripcion" wire:model.defer="descripcion" type="text" class="form-control">
                </textarea>
                @error('descripcion')
                <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                </div>
                @enderror
              </div>




              <div class="mb-3 col-12">
                <label for="sedes" class="form-label">¿Qué sedes componen la zona?</label>
                <div wire:ignore>
                  <select id="sedes" name="sedes[]" multiple class="form-select" >
                    @foreach ($sedes as $sede )
                    <option value="{{ $sede->id }}" >{{ $sede->nombre }} </option>
                    @endforeach
                  </select>
                </div>
                @error('sedesSeleccionadas')
                  <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                  </div>
                @enderror
              </div>

              <div class="mb-3 col-12">
                <label for="localidades" class="form-label">¿Qué localidades componen la zona?</label>
                <div wire:ignore>
                  <select id="localidades" name="localidades[]" multiple class="form-select" >
                    @foreach ($localidades as $localidad )
                    <option value="{{ $localidad->id }}" >{{ $localidad->nombre }} ({{ $localidad->municipio->nombre }}) </option>
                    @endforeach
                  </select>
                </div>
                @error('localidadesSeleccionadas')
                  <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                  </div>
                @enderror
              </div>



            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>


</div>



@script
<script>
  // Definimos las funciones en window para asegurar su visibilidad global
  window.initSelect2Zonas = function() {
    if (typeof $.fn.select2 !== 'undefined') {
      $('#sedes').select2({
        placeholder: 'Seleccione una o más sedes',
        dropdownParent: $('#modalcrearEditarZona'),
        allowClear: true
      });
      $('#localidades').select2({
        placeholder: 'Seleccione una o más localidades',
        dropdownParent: $('#modalcrearEditarZona'),
        allowClear: true
      });

      // Sincronizar valores iniciales de Livewire a Select2
      $('#sedes').val($wire.get('sedesSeleccionadas')).trigger('change');
      $('#localidades').val($wire.get('localidadesSeleccionadas')).trigger('change');

      // Listeners para sincronizar cambios de Select2 a Livewire
      $('#sedes').on('change', function () {
        $wire.set('sedesSeleccionadas', $(this).val());
      });
      $('#localidades').on('change', function () {
        $wire.set('localidadesSeleccionadas', $(this).val());
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
  };

  $wire.on('msn', data => {
    Swal.fire({
      title: data.msnTitulo,
      html: data.msnTexto,
      icon: data.msnIcono,
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false
    });
  });

  $wire.on('eliminar', data => {
    Swal.fire({
      title: '¿Deseas eliminar esta zona?',
      text: "Esta acción no es reversible.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $wire.dispatch('eliminarZona', { id: data.id });
      }
    });
  });

  $wire.on('cerrarModal', data => {
    var offcanvasElement = document.getElementById(data.nombreModal);
    if (offcanvasElement) {
      var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
      if (offcanvas) offcanvas.hide();
    }
    window.destroySelect2Zonas();
  });

  $wire.on('abrirModal', data => {
    var offcanvasElement = document.getElementById(data.nombreModal);
    if (!offcanvasElement) return;

    var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement) || new bootstrap.Offcanvas(offcanvasElement);
    offcanvas.show();

    offcanvasElement.addEventListener('shown.bs.offcanvas', () => {
      window.initSelect2Zonas();
    }, { once: true });

    offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
      window.destroySelect2Zonas();
    }, { once: true });
  });
</script>
@endscript
