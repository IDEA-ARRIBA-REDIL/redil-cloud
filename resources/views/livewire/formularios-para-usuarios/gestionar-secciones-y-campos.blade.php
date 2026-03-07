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
              <p class="m-0"><b>Título:</b> {{ $seccion->titulo }}</p>
              @if($seccion->icono)
              <p class="m-0"><b>Icono:</b> <i class="{{$seccion->icono}}"></i></p>
              @endif
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

            @foreach ( $seccion->campos()->orderBy('orden','asc')->get() as $campo)
            <div class="{{ $campo->pivot->class }}" data-campo-id="{{ $campo->id }}">
              <div class="card border">
                <div class="card-body">
                  <div class="d-flex fw-bold">
                    <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Ordenar sección"><i class="text-muted ti ti-grip-horizontal drag-handle"></i> <span class="mb-1 text-black fw-bold"> {{ $campo->nombre }}</span></a>
                  </div>

                  <ul class="list-unstyled my-1 py-1">
                    <li><i class="ti ti-number ti-sm"></i><span class="fw-medium mx-1">Orden:</span><span>{{ $campo->pivot->orden }}</span></li>
                    <li class=""><i class="ti ti-square-check ti-sm"></i><span class="fw-medium mx-1">¿Requerido?:</span><span>{{ $campo->pivot->requerido? 'Si': 'No' }}</span></li>
                    <li class=""><i class="ti ti-paint ti-sm"></i><span class="fw-medium mx-1">Class:</span> <span>{{ $campo->pivot->class }}</span></li>
                    @if($campo->pivot->informacion_de_apoyo)
                    <li class=""><i class="ti ti-info-circle ti-sm"></i><span class="fw-medium mx-1">Información de apoyo:</span> <span>{{ Str::limit($campo->pivot->informacion_de_apoyo, 20) }}</span></li>
                    @endif
                  </ul>
                  <div class="row g-4 mt-2">

                  </div>
                  <div class="d-flex justify-content-end mt-2">
                    <div>
                      <a href="javascript:void(0);" wire:click="editarCampo({{ $seccion->id }}, {{ $campo->id }})" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar campo"><i class="ti ti-edit "></i></a>
                      <a href="javascript:void(0);" wire:click="$dispatch('eliminarCampo', { campoId : {{ $campo->id }}, seccionId: {{ $seccion->id }}, nombreCampo: '{{ $campo->nombre }}' })" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Eliminar campo"><i class="ti ti-trash "></i></a>
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
                    @if($seccion->campos()->orderBy('orden','asc')->count() < 1 )
                      <i class="ti ti-carousel-vertical fs-1 pb-1"></i>
                      <h6 class="text-center">No tienes campos creados.</h6>
                      @endif
                      <a href="javascript:void(0);" wire:click="crearCampo({{ $seccion->id }})" class="btn btn-primary btn-sm rounded-pill px-7 py-2"><i class="ti ti-plus me-2"></i> Agregar campo </a>
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
  <form id="nuevoCampo" role="form" class="forms-sample" wire:submit.prevent="guardarCampo" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="modalNuevoCampo" aria-labelledby="modalNuevoCampoLabel">
      <div class="offcanvas-header my-1 px-8">
        <h4 class="offcanvas-title fw-bold text-primary" id="modalNuevoCampoLabel">
          @if($modoEdicionCampo) Editar campo @else Nuevo campo @endif
        </h4>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body pt-6 px-8">
        <div class="mb-4">
          @if($modoEdicionCampo)
          <span class="text-black ti-14px mb-4">Estas editando el campo <b>"{{ $campoEditando->nombre }}"</b> de la sección<b>"{{ $seccionCampo ? $seccionCampo->nombre : '' }}"</b>, por favor ingresa toda la información. </span>

          @else
          <span class="text-black ti-14px mb-4">Estas ingresando un campo nuevo a <b>"{{ $seccionCampo ? $seccionCampo->nombre : '' }}"</b>, por favor ingresa toda la información. </span>
          @endif
        </div>
        @csrf
        <div class="pt-3">

          @if(!$modoEdicionCampo)
          @livewire('FormulariosParaUsuarios.selector-de-campos', [
          'formulario' => $formulario
          ])
          @endif

          <div class="mb-3 col-12">
            <label class="form-label" for="class">Class</label>
            <input id="class" name="class" wire:model.defer="class" type="text" class="form-control" />
            @error('class')
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $message }}
            </div>
            @enderror
          </div>

          <!-- requerido-->
          <div class="mb-3 col-12">
            <div class=" small fw-medium mb-1">¿El campo es requerido?</div>
            <label class="switch switch-lg">
              <input id="requerido" name="requerido" wire:model.defer="campoRequerido" type="checkbox" checked class="switch-input" />
              <span class="switch-toggle-slider">
                <span class="switch-on">SI</span>
                <span class="switch-off">NO</span>
              </span>
              <span class="switch-label"></span>
            </label>
          </div>
          <!-- / requerido-->

          <div class="mb-3 col-12">
            <label class="form-label" for="informacionDeApoyo">Información de apoyo</label>
            <textarea id="informacionDeApoyo" name="informacionDeApoyo" wire:model.defer="informacionDeApoyo" type="text" class="form-control" rows="4">{{ $informacionDeApoyo }}</textarea>
            @error('informacionDeApoyo')
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

          <div class="mb-3 col-12">
            <label class="form-label" for="titulo">Título</label>
            <input id="titulo" name="título" wire:model.defer="título" type="text" class="form-control" />
            @error('título')
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $message }}
            </div>
            @enderror
          </div>

          <div class="mb-3 col-12">
            <label class="form-label" for="icono">Icono </label>
            <input id="icono" name="icono" wire:model.defer="icono" type="text" class="form-control" placeholder="ejemplo 'ti ti-home'" />
            @error('icono')
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $message }}
            </div>
            @enderror
            <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>Librería de iconos <a href="https://tabler.io/icons" target="_blank">(Ver librería)</a></div>
          </div>

          @if($seccionEditando && $seccionEditando->logo)
          <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/secciones-formulario/'.$seccionEditando->logo) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$seccionEditando->logo }}?v={{ time() }}" alt="react-logo" class="me-2 mt-1" width="50">
          @endif

          <!-- imagen -->
          <div class="mb-3 col-12">
            <label id="label_imagen" class="form-label" for="imagen">
              {{ $seccionEditando && $seccionEditando->logo ? 'Reemplazar imagen'  : 'Subir imagen' }}
            </label>
            <input type="file" id="imagen" name="imagen" wire:model.defer="imagen" data-input="imagen" class="form-control inputFile " accept=".jpg, .png, .jpeg">
            @if($errors->has('imagen'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('imagen') }}
            </div>
            @endif
            <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>La imagen debe ser de 100px alto y 100px ancho</div>
          </div>
          <!-- /imagen -->
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
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
]);


@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/sortablejs/sortable.js',
]);
@endassets

@script
<script>
  document.addEventListener('livewire:initialized', () => {
    const secciones = document.querySelectorAll('.accordion-collapse');

    secciones.forEach(seccion => {
      Sortable.create(seccion, {
        animation: 150,
        handle: '.drag-handle',
        group: 'campos', // Grupo global para todas las secciones
        onEnd: function(evt) {
          const campoId = evt.item.dataset.campoId;
          const seccionOrigenId = evt.from.dataset.seccionId;
          const seccionDestinoId = evt.to.dataset.seccionId;
          const ordenDestino = Array.from(evt.to.querySelectorAll('[data-campo-id]')).map(campo => campo.dataset.campoId);
          const ordenOrigen = Array.from(evt.from.querySelectorAll('[data-campo-id]')).map(campo => campo.dataset.campoId);
          $wire.actualizarOrdenCampos(
            campoId,
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
    const inputImagen = document.getElementById('imagen');
    const btnGuardar = document.querySelector('#nuevaSeccion .btnGuardar');
    const btnLoader = document.querySelector('#nuevaSeccion .btnGuardarLoader');

    if (inputImagen && btnGuardar) {
      inputImagen.addEventListener('change', () => {
        btnGuardar.classList.add('d-none');
        btnLoader.classList.remove('d-none');

        setTimeout(() => {
          btnGuardar.classList.remove('d-none');
          btnLoader.classList.add('d-none');
        }, 1000);
      });
    }
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

  $wire.on('eliminarCampo', (params) => {
    const campoId = params.campoId;
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
        $wire.eliminarCampo(campoId, seccionId);

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
</script>
@endscript
