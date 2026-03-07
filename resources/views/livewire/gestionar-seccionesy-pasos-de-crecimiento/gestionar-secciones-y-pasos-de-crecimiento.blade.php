<div>
  <div class="d-flex flex-row-reverse">
    <a href="javascript:void(0);" wire:click="crearSeccion" class="btn btn-primary rounded-pill px-7 py-2"><i class="ti ti-plus me-2"></i> Nueva sección </a>
  </div>


  <div class="row g-4 mt-2" id="elementos-container">
    @if($secciones->count() > 0)
    @foreach( $secciones as $seccion )
    <div class="col-12 accordion" id="seccion{{$seccion->id}}" data-seccion-id="{{$seccion->id}}">
      <div class="card accordion-item">
        <div class="card-body">
          <div class="d-flex justify-content-between mt-3">
            <div class="my-auto">
              <h5 class="mb-1 text-primary">
                @if($seccion->logo)
                <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/secciones-formulario/'.$seccion->logo) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$seccion->logo }}?v={{ time() }}" alt="react-logo" class="me-2" width="30">
                @endif
                {{ $seccion->nombre }}
              </h5>
            </div>

            <div class="my-auto">
              @if (!$loop->first)
              <a href="javascript:void(0);" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Subir sección" wire:click="subirSeccion({{ $seccion->id }})"><i class="ti ti-circle-caret-up "></i></a>
              @else
              <a href="javascript:void(0);" class="text-muted d-none" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Subir sección"><i class="ti ti-circle-caret-up "></i></a>
              @endif

              @if (!$loop->last)
              <a href="javascript:void(0);" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Bajar sección" wire:click="bajarSeccion({{ $seccion->id }})"><i class="ti ti-circle-caret-down "></i></a>
              @else
              <a href="javascript:void(0);" class="text-muted d-none" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Bajar sección"><i class="ti ti-circle-caret-down "></i></a>
              @endif
              <a href="javascript:void(0);" wire:click="editarSeccion({{ $seccion->id }})" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar sección"><i class="ti ti-edit "></i></a>
              <a href="javascript:void(0);" wire:click="$dispatch('eliminarSeccion', { seccionId: {{ $seccion->id }}, nombreSeccion: '{{ $seccion->nombre }}' })" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Eliminar sección"><i class="ti ti-trash "></i></a>
              <a href="javascript:void(0);" class="text-muted" data-bs-toggle="collapse" data-bs-target="#accordionSeccion{{ $seccion->id }}" aria-expanded="true" aria-controls="accordionSeccion{{ $seccion->id }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Desplegar">
                <i class="ti ti-chevron-right desplegado {{ in_array($seccion->id, $seccionesActivas) ? 'd-none' : '' }} " id="chevronRigth{{ $seccion->id }}"></i>
                <i class="ti ti-chevron-down  {{ in_array($seccion->id, $seccionesActivas) ? '' : 'd-none' }}  plegado" id="chevronDown{{ $seccion->id }}"></i>
              </a>
            </div>
          </div>

          <div id="accordionSeccion{{ $seccion->id }}" class="accordion-collapse collapse {{ in_array($seccion->id, $seccionesActivas) ? 'show' : '' }} row g-2 mt-2" data-bs-parent="#seccion{{$seccion->id}}" data-seccion-id="{{ $seccion->id }}">

            @foreach ( $seccion->pasosCrecimiento as $pasoCrecimiento)
            <div data-campo-id="{{ $pasoCrecimiento->id }}">
              <div class="card border">
                <div class="card-body">
                  <div class="d-flex fw-bold">
                    <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Ordenar sección"><i class="text-muted ti ti-grip-horizontal drag-handle"></i> <span class="mb-1 text-black fw-bold"> {{ $pasoCrecimiento->nombre }}</span></a>
                  </div>

                  <ul class="list-unstyled my-1 py-1">
                    <li><i class="ti ti-number ti-sm"></i><span class="fw-medium mx-1">Orden:</span><span> {{ $pasoCrecimiento->orden }} </span></li>
                    @if($pasoCrecimiento->descripcion)
                    <li class=""><i class="ti ti-info-circle ti-sm"></i><span class="fw-medium mx-1">Descripción</span> <span>{{ $pasoCrecimiento->descripcion }}</span></li>
                    @endif
                  </ul>
                  <div class="row g-4 mt-2">

                  </div>
                  <div class="d-flex justify-content-end mt-2">
                    <div>
                      <a href="javascript:void(0);" wire:click="editarPasoCrecimiento({{ $seccion->id }}, {{ $pasoCrecimiento->id }})" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar campo"><i class="ti ti-edit "></i></a>
                      <a href="javascript:void(0);" wire:click="$dispatch('eliminarPasoCrecimiento', { pasoCrecimientoId : {{ $pasoCrecimiento->id }}, seccionId: {{ $seccion->id }}, nombreCampo: '{{ $pasoCrecimiento->nombre }}' })" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Eliminar campo"><i class="ti ti-trash "></i></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endforeach

            <div class="col-12" data-campo-id="">
              <div class="card border">
                <div class="card-body">
                  <center>
                    @if($seccion->pasosCrecimiento->count() < 1 )
                      <i class="ti ti-carousel-vertical fs-1 pb-1"></i>
                      <h6 class="text-center">No tienes campos creados.</h6>
                      @endif
                      <a href="javascript:void(0);" wire:click="crearPasoCrecimiento({{ $seccion->id }})" class="btn btn-primary btn-sm rounded-pill px-7 py-2"><i class="ti ti-plus me-2"></i> Agregar paso de crecimiento </a>
                  </center>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    @endforeach
    @else
    <div class="col-12">
      <div class="card border">
        <div class="card-body">
          <center>
            <i class="ti ti-carousel-vertical fs-1 pb-1"></i>
            <h6 class="text-center">No tienes secciones creadas.</h6>
          </center>
        </div>
      </div>
    </div>
    @endif
  </div>

  <!-- crear y editar campo  -->
  <form id="nuevoCampo" role="form" class="forms-sample" wire:submit.prevent="guardarPasoCrecimiento" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="modalNuevoPasoCrecimiento" aria-labelledby="modalNuevoPasoCrecimientoLabel">
      <div class="offcanvas-header my-1 px-8">
        <h4 class="offcanvas-title fw-bold text-primary" id="modalNuevoPasoCrecimientoLabel">
          @if($modoEdicionPasoCrecimiento) Editar paso de crecimiento @else Nuevo paso de crecimiento @endif
        </h4>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body pt-6 px-8">
        <div class="mb-4">
          @if($modoEdicionPasoCrecimiento)
          <span class="text-black ti-14px mb-4">Estas editando el paso de crecimiento <b>"{{ $pasoCrecimientoEditando->nombre }}"</b> de la sección<b>"{{ $seccionPasoCrecimiento ? $seccionPasoCrecimiento->nombre : '' }}"</b>, por favor ingresa toda la información. </span>

          @else
          <span class="text-black ti-14px mb-4">Estas ingresando un paso de crecimiento <b>"{{ $seccionPasoCrecimiento ? $seccionPasoCrecimiento->nombre : '' }}"</b>, por favor ingresa toda la información. </span>
          @endif
        </div>
        @csrf
        <div class="pt-3">
          <div class="mb-3 col-12">
            <label class="form-label" for="nombre">Nombre de paso crecimiento</label>
            <input id="nombre" name="nombrePasoCrecimiento" type="text" class="form-control" wire:model.defer="nombrePasoCrecimiento" />

            @error('nombrePasoCrecimiento')
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $message }}
            </div>
            @enderror
          </div>

          <!-- roles -->
          <div>
            <div class="mb-3 col-12" wire:ignore>
              <label for="roles" class="form-label">¿Qué roles van a usar este paso?</label>

              <select wire:model="rolesSeleccionados" id="roles" name="roles[]" multiple class="form-select w-100">
                @foreach ($roles as $rol)
                <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                @endforeach
              </select>

              @error('rolesSeleccionados')
              <div class="text-danger ti-12px mt-2">
                <i class="ti ti-circle-x"></i> {{ $message }}
              </div>
              @enderror
            </div>
          </div>
          <!-- /roles -->

          <div class="mb-3 col-12">
            <label class="form-label" for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" wire:model.defer="descripcion" type="text" class="form-control" rows="4">{{ $descripcion }}</textarea>
            @error('descripcion')
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

  <!-- crear y editar seccion  -->
  <form id="nuevaSeccion" role="form" class="forms-sample" wire:submit.prevent="guardarSeccion" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="modalNuevaSeccion" aria-labelledby="modalNuevaSeccionLabel">
      <div class="offcanvas-header my-1 px-8">
        <h4 class="offcanvas-title fw-bold text-primary" id="modalNuevaSeccionLabel">
          @if ($modoEdicion) Editar Sección @else Nueva sección @endif
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

          @if($seccionEditando && $seccionEditando->logo)
          <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/secciones-formulario/'.$seccionEditando->logo) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$seccionEditando->logo }}?v={{ time() }}" alt="react-logo" class="me-2 mt-1" width="50">
          @endif

        </div>
      </div>
      <div class="offcanvas-footer p-5 border-top border-2 px-8">
        <button class="btnGuardarLoader d-none btn btn-sm py-2 px-4 btn-primary waves-effect waves-light rounded-pill" type="button" disabled="">
          <span class="spinner-border" role="status" aria-hidden="true"></span>
          <span class="ms-1">Cargando archivo...</span>
        </button>
        <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
        <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
      </div>
    </div>
  </form>
</div>

@assets
@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',
]);


@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/sortablejs/sortable.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/select2/select2.js',
]);
@endassets

@script
<script type="module">
  document.addEventListener('livewire:initialized', () => {
    const secciones = document.querySelectorAll('.accordion-collapse');

    secciones.forEach(seccion => {
      Sortable.create(seccion, {
        animation: 150,
        handle: '.drag-handle',
        group: 'campos', // Grupo global para todas las secciones
        onEnd: function(evt) {
          const pasoCrecimientoId = evt.item.dataset.pasoCrecimientoId;
          const seccionOrigenId = evt.from.dataset.seccionId;
          const seccionDestinoId = evt.to.dataset.seccionId;
          const ordenDestino = Array.from(evt.to.querySelectorAll('[data-campo-id]')).map(campo => campo.dataset.pasoCrecimientoId);
          const ordenOrigen = Array.from(evt.from.querySelectorAll('[data-campo-id]')).map(campo => campo.dataset.pasoCrecimientoId);
          $wire.actualizarOrdenPasoCrecimiento(
            pasoCrecimientoId,
            seccionOrigenId,
            seccionDestinoId,
            ordenOrigen,
            ordenDestino
          );
        }
      });
    });
  });

  document.addEventListener('livewire:initialized', () => {
    const btnGuardar = document.querySelector('#nuevaSeccion .btnGuardar');
    const btnLoader = document.querySelector('#nuevaSeccion .btnGuardarLoader');
  });

  $('.accordion-collapse').on('shown.bs.collapse', function() {
    let id = $(this).attr('id').replace('accordionSeccion', '');
    $('#chevronRigth' + id).addClass('d-none');
    $('#chevronDown' + id).removeClass('d-none');
  });

  $('.accordion-collapse').on('hidden.bs.collapse', function() {
    let id = $(this).attr('id').replace('accordionSeccion', '');
    $('#chevronDown' + id).addClass('d-none');
    $('#chevronRigth' + id).removeClass('d-none');
  });

  $wire.on('eliminarPasoCrecimiento', (params) => {
    const pasoCrecimientoId = params.pasoCrecimientoId;
    const seccionId = params.seccionId;
    const nombreCampo = params.nombreCampo;
    Swal.fire({
      title: '¿Deseas eliminar el campo ' + nombreCampo + '?',
      text: "Esta acción no es reversible.",
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
        $wire.eliminarPasoCrecimiento(pasoCrecimientoId, seccionId);

        Swal.fire({
          title: '¡Eliminado!',
          text: 'El campo ' + nombreCampo + ' fue eliminado correctamente.',
          icon: 'success',
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

  $wire.on('eliminarSeccion', (params) => {
    const seccionId = params.seccionId;
    const nombreSeccion = params.nombreSeccion;
    Swal.fire({
      title: '¿Deseas eliminar la sección ' + nombreSeccion + '?',
      text: "Esta acción no es reversible.",
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
        $wire.eliminarSeccion(seccionId);

        Swal.fire({
          title: '¡Eliminado!',
          text: 'La sección ' + nombreSeccion + ' fue eliminada correctamente.',
          icon: 'success',
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



  // Referencia al Offcanvas
  const offcanvasElement = document.getElementById('modalNuevoPasoCrecimiento');

  // Función para inicializar Select2
  function inicializarSelect2() {
    // Usamos el ID correcto 'roles'
    const select = $('#roles');

    // Si ya está inicializado, no hacer nada
    if (select.hasClass("select2-hidden-accessible")) {
      return;
    }

    select.select2({
      dropdownParent: $('#modalNuevoPasoCrecimiento') // Importante para que el dropdown aparezca sobre el offcanvas
    });

    select.on('change', function() {
      // Usamos la propiedad correcta 'rolesSeleccionados'
      @this.set('rolesSeleccionados', $(this).val());
    });
  }

  // Función para destruir Select2
  function destruirSelect2() {
    const select = $('#roles');
    if (select.data('select2')) {
      select.select2('destroy');
    }
  }

  // Evento de Bootstrap: se dispara cuando el offcanvas se ha mostrado completamente
  offcanvasElement.addEventListener('shown.bs.offcanvas', () => {
    inicializarSelect2();
  });

  // Evento de Bootstrap: se dispara cuando el offcanvas se ha ocultado completamente
  offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
    destruirSelect2();
  });

  // Escucha un evento de Livewire para cargar los datos al editar
  $wire.on('cargarRolesSeleccionados', (event) => {
    // Asegúrate de que Select2 esté inicializado antes de ponerle valores
    inicializarSelect2();

    // Asigna los valores y dispara el evento 'change' para que Select2 se actualice
    $('#roles').val(event.roles).trigger('change');
  });


  const offcanvasPasoCrecimiento = document.getElementById('modalNuevoPasoCrecimiento');

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

    // 2. Verificamos si es el modal correcto para inicializar Select2
    if (event.nombreModal === 'modalNuevoPasoCrecimiento') {
      const select = $('#roles');

      // 3. Inicializamos Select2
      // Evita reinicializar si ya existe
      if (!select.hasClass("select2-hidden-accessible")) {
        select.select2({
          // Importante: Asigna el dropdown al contenedor del offcanvas
          dropdownParent: $('#modalNuevoPasoCrecimiento')
        });

        select.on('change', function() {
          // Envía el valor a la propiedad 'rolesSeleccionados' de Livewire
          @this.set('rolesSeleccionados', $(this).val());
        });
      }
    }

    // Remover backdrop al cerrar
    offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
      backdrop.remove();
    });

    // Escucha un evento de Livewire para cargar los datos al editar
    $wire.on('cargarRolesSeleccionados', (event) => {
      // Asigna los valores y dispara el evento 'change' para que Select2 se actualice visualmente
      $('#roles').val(event.roles).trigger('change');
    });

    // EVENTO PARA DESTRUIR SELECT2 AL CERRAR
    // Es buena práctica mantener esto para limpiar el DOM.
    offcanvasPasoCrecimiento.addEventListener('hidden.bs.offcanvas', () => {
      const select = $('#roles');
      if (select.data('select2')) {
        select.select2('destroy');
      }
    });
  });
</script>
@endscript
