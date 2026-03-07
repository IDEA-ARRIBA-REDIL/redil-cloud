<div>
  <div class="d-flex flex-row-reverse">
    <a href="javascript:void(0);" wire:click="crearCampo" class="btn btn-primary rounded-pill px-7 py-2"><i class="ti ti-plus me-2"></i> Nuevo campo extra</a>
  </div>

  <div class="row g-4 pt-10">
    <div class="col-12 col-md-6 col-lg-6 offset-lg-1 mt-3">
      <div class="input-group">
        <input wire:model.live.debounce.500ms="busqueda" type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Buscar">
      </div>
    </div>

    <div class="col-12 col-md-3 col-lg-2">
      <div class="form-check my-auto">
        <label class="form-check-label">
          <input class="form-check-input" type="checkbox" wire:model.live="conEliminados">
          ¿Mostrar ocultos?
        </label>
      </div>
    </div>

    <div class="col-12 col-md-3 col-lg-2">
      <div class="form-check my-auto">
        <label class="form-check-label">
        <input class="form-check-input" type="checkbox" wire:model.live="soloCamposExtra">
          ¿Solo campos extra?
        </label>
      </div>
    </div>
  </div>

  <div class="row g-4 mt-5">
    @if($campos)
      @foreach( $campos as $campo )
      <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card shadow-1">
          <div class="card-body">
            <h5 class="mb-1 text-primary"> {{ $campo->nombre }}</h5>

            <div class="d-flex flex-column mt-1">
              <div class="role-heading ">
                <span class="text-white badge rounded-pill bg-primary mb-2">{{ $campo->es_campo_extra ? 'Campo extra' : 'Campo esencial' }}</span>
                <p class="py-0 m-0 text-black"><b>name:</b> {{ $campo->name_id }}</p>
                <p class="py-0 m-0 text-black"><b>Placeholder:</b> {{ $campo->placeholder }}</p>
                <p class="py-0 m-0 text-black"><b>¿Visible en el resumen?</b> {{ $campo->visible_resumen ? 'Si' : 'No' }}</p>
                @if($campo->es_campo_extra)
                <p class="py-0 m-0 text-black"><b>Tipo de campo: </b>  {{ $tipoCampos[$campo->tipo_de_campo-1] }}</p>
                @endif
              </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
              <div>
                @if(!$campo->trashed())
                <a href="javascript:void(0);" wire:click="editarCampo({{$campo->id}})" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar campo"><i class="ti ti-edit "></i></a>
                @endif
                <a href="javascript:void(0);" wire:click="ocultarMostrar({{$campo->id}})"  class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $campo->deleted_at ? 'Mostrar campo' : 'Ocultar campo' }}"><i class="ti {{ $campo->deleted_at ? 'ti-eye' : 'ti-eye-off' }} "></i></a>
                @if($campo->es_campo_extra)
                <a href="javascript:void(0);" wire:click="$dispatch('eliminar', {{ $campo->id }})" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Eliminar"><i class="ti ti-trash "></i></a>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    @else
    <div class="py-4">
      <center>
        <i class="ti ti-browser fs-1 pb-1"></i>
        <h6 class="text-center">¡Ups! no hay campos creados. </h6>
      </center>
    </div>
    @endif
  </div>

  <!-- crear y editar campo  -->
  <form id="crearEditarCampo" role="form" class="forms-sample" wire:submit.prevent="guardarCampo" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar"  tabindex="-1" id="modalcrearEditarCampo" aria-labelledby="modalcrearEditarCampoLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalcrearEditarCampoLabel">
              @if ($modoEdicionCampo) Editar Sección @else Nueva campo extra @endif
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
                <label class="form-label" for="placeholder">Placeholder</label>
                <input id="placeholder" name="placeholder" wire:model.defer="placeholder" type="text" class="form-control" />
                @error('placeholder')
                <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                </div>
                @enderror
              </div>

              <!-- visibleResumen-->
              <div class="mb-3 col-12">
                <div class=" small fw-medium mb-1">¿Visible en el resumen?</div>
                <label class="switch switch-lg">
                  <input id="visibleResumen" name="visibleResumen" wire:model.defer="visibleResumen" type="checkbox" checked class="switch-input" />
                  <span class="switch-toggle-slider">
                    <span class="switch-on">SI</span>
                    <span class="switch-off">NO</span>
                  </span>
                  <span class="switch-label"></span>
                </label>
              </div>
              <!-- / visibleResumen-->

              <!-- roles -->
              <div class="mb-3 col-12">
                <label for="roles" class="form-label">¿Qué roles van a usar este campo?</label>
                <select id="roles" name="roles[]" wire:model.defer="roles" multiple class="select2Iconos form-select" >
                  @foreach ($listaRoles as $rol )
                  <option value="{{ $rol->id }}" data-icon="{{ $rol->icono }}" >{{ $rol->name }} </option>
                  @endforeach
                </select>
                @if($errors->has('roles'))
                  <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $errors->first('roles') }}
                  </div>
                @endif
              </div>
              <!-- /roles -->

              @if (($campoEditar && $campoEditar->es_campo_extra==true) || $modoEdicionCampo==false)
              <div class="mb-3 col-12">
                <label class="form-label" for="nameId">Name Id</label>
                <input id="nameId" name="nameId" wire:model.defer="nameId" type="text" class="form-control" />
                @error('nameId')
                <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                </div>
                @enderror
                <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>Debes de escribir este campo sin espacio o con raya baja. Ejemplo name_campo
                </div>
              </div>

              <div class="mb-3 col-12">
                <label class="form-label" for="tipoDeCampo">Tipo de campo</label>
                <select id="tipoDeCampo" name="tipoDeCampo" wire:model.defer="tipoDeCampo" class="form-select">
                    <option value="">Selecciona el tipo de campo</option>
                    @foreach($tipoCampos as $tipo)
                        <option value="{{ $loop->index+1 }}">{{ $tipo }}</option>
                    @endforeach
                </select>
                @error('tipoDeCampo')
                    <div class="text-danger ti-12px mt-2">
                        <i class="ti ti-circle-x"></i> {{ $message }}
                    </div>
                @enderror
              </div>

              <div class="mb-3 col-12">
                <label class="form-label" for="opcionesSelect">Opciones tipo select</label>
                <textarea id="opcionesSelect" name="opcionesSelect" wire:model.defer="opcionesSelect" type="text" class="form-control" @if(!$tipoDeCampo || ($tipoDeCampo && $tipoDeCampo < 3) ) disabled  @endif style="font-size: .8rem !important;"></textarea>
                <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>Debe ser en formato json, ejemplo:
                  <br> [{ "id": "1", "nombre":"opc1", "visible":"1" ,"value":"1" }, <br> { "id": "2", "nombre":"opc2", "visible":"1", "value":"2" }]
                </div>
                @error('opcionesSelect')
                <div class="text-danger ti-12px mt-2">
                  <i class="ti ti-circle-x"></i> {{ $message }}
                </div>
                @enderror
              </div>
              @endif
            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>
</div>

@assets
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',

  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/select2/select2.js',

  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
  ]);
@endassets


@script
<script>
  const tipoDeCampoSelect = document.getElementById('tipoDeCampo');
  const opcionesSelectInput = document.getElementById('opcionesSelect');

  tipoDeCampoSelect.addEventListener('change', function() {
    if (this.value > 2) {
      opcionesSelectInput.disabled = false;
    } else {
      opcionesSelectInput.disabled = true;
    }
  });

  const nameIdInput = document.getElementById('nameId');

  nameIdInput.addEventListener('input', function() {
    let valor = this.value;
    // Reemplazar espacios por guiones bajos
    valor = valor.replace(/\s+/g, '_');
    // Eliminar espacios adicionales
    valor = valor.replace(/_+/g, '_');
    this.value = valor;
  });

  $wire.on('msn', data => {
    Swal.fire({
      title: event.detail.msnTitulo,
      html: event.detail.msnTexto,
      icon: event.detail.msnIcono,
      customClass: {
          confirmButton: 'btn btn-primary'
      },
        buttonsStyling: false
    });
  });

  $wire.on('cerrarModal', data => {
    var offcanvasElement = document.getElementById(event.detail.nombreModal);
    var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
    offcanvas.hide();
  });

  $wire.on('abrirModal', data => {

    // Agregar backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'offcanvas-backdrop fade show';
    document.body.appendChild(backdrop);

    var offcanvasElement = document.getElementById(event.detail.nombreModal);
    var offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
      backdrop: true
    });
    offcanvas.show();

    // Remover backdrop al cerrar
    offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
      backdrop.remove();
    });
  });

  $wire.on('eliminar', campoId => {

  Swal.fire({
    title: '¿Deseas eliminar este campo extra?',
    text: "Tenga en cuenta que esta acción no es reversible, se eliminaran también la información que tenga el campo con el usuario y los formularios relacionados.",
    icon: 'warning',
    showCancelButton: true,
    focusConfirm: false,
    confirmButtonText: 'Si, eliminar',
    cancelButtonText: 'No',
    customClass: {
      confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
      cancelButton: 'btn btn-label-secondary waves-effect waves-light'
    },
    buttonsStyling: false
  }).then((result) => {
    if (result.isConfirmed) {
      $wire.eliminarCampo(campoId);

      Swal.fire({
        title: '¡Eliminado!',
        text: 'El campo fue eliminado correctamente.',
        icon:'success',
        showCancelButton: false,
        focusConfirm: false,
        confirmButtonText: 'Aceptar',
        customClass: {
          confirmButton: 'btn btn-primary me-3 waves-effect waves-light'
        },
      })
    }
  })
  });
</script>
@endscript
