<div>
  <div class="d-flex flex-row-reverse">
    <a href="javascript:;" wire:click="abrirGestionarAlbum" class="btn btn-primary rounded-pill px-7 py-2 mx-1"><i class="ti ti-disc me-2"></i> Gestionar albumes </a>
    <a href="javascript:;" wire:click="crearCancion" class="btn btn-primary rounded-pill px-7 py-2 mx-1"><i class="ti ti-music-plus me-2"></i> Nueva canción </a>
  </div>

  <div class="row g-4 pt-10">
    <div class="col-12 col-md-6 offset-md-3 mt-3">
      <div class="input-group">
        <input wire:model.live.debounce.500ms="busqueda" type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Buscar">
      </div>
    </div>
  </div>

  <div class="row g-2 listadoDeCanciones mt-5">
    @foreach ($canciones as $cancion)
    <div class="col-12 col-md-4" data-cancion-id="{{ $cancion->id }}">
      <div class="card border">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="flex-shrink-1">
              @if(!$busqueda)
              <a href="javascript:;" class="" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Ordenar sección"><i class="text-muted ti ti-grip-horizontal drag-handle"></i></a>
              @endif
            </div>
            <div class="d-flex justify-content-end">
              <div>
                <a href="javascript:;" wire:click="editarCancion({{ $cancion->id }})"  class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar cancion"><i class="ti ti-edit "></i></a>
                <a href="javascript:;" wire:click="$dispatch('eliminarCancion', { cancionId: {{ $cancion->id }}, nombreCancion: '{{ $cancion->nombre }}' })" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="right" title="Eliminar cancion"><i class="ti ti-trash "></i></a>
              </div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-6 offset-3 offset-md-0 col-md-3 col-xl-4 d-flex align-items-center">
              @if($cancion->album && $cancion->album->imagen)
                <img class="card-img img-fluid" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$cancion->album->imagen) : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$cancion->album->imagen) }}"  alt="album">
              @else
                <img class="card-img img-fluid" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/album-default.png') : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/album-default.png') }}"  alt="album">
              @endif
            </div>
            <div class="col-12 col-md-9 col-xl-8 my-2 text-center text-md-start">
              <h4 class="text-truncate mb-0">{{ $cancion->nombre }}</h4>
              <p class="text-muted  mb-0">{{ $cancion->album ? $cancion->album->nombre : 'Álbum desconocido'}}</p>
              <p class="mb-0">{{ $cancion->artista ? $cancion->artista : 'Artista desconocido'}}</p>
              <p class="mb-0"><i class="ti ti-number ti-sm"></i><span class="fw-medium mx-1">Orden:</span><span>{{ $cancion->orden }}</span></p>
            </div>
            <div class="col-12 mt-3" >
              <audio controls id="cancion" class="w-100" preload="none">
                <source src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/audios/'.$cancion->archivo) : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/audios/'.$cancion->archivo) }}?v={{ time() }}" type="audio/mp3">
              </audio>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <!-- crear y editar canción  -->
  <form id="nuevaEditarCancion" role="form" class="forms-sample" wire:submit.prevent="guardarCancion" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar"  tabindex="-1" id="modalNuevaEditarCancion" aria-labelledby="modalNuevaEditarCancionLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalNuevaEditarCancionLabel">
              @if($modoEdicionCancion) Editar canción @else Nueva canción @endif
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
            <div class="mb-4">
              @if($modoEdicionCancion)
              <span class="text-black ti-14px mb-4">Estas editando la canción <b>"{{ $cancionEditando->nombre }}"</b>, por favor ingresa toda la información. </span>
              @else
              <span class="text-black ti-14px mb-4">Estas ingresando una canción nueva, por favor ingresa toda la información. </span>
              @endif
            </div>
            @csrf
            <div class="pt-3">

              <!-- archivo -->
              <div class="mb-3 col-12">
                <label id="label_archivo" class="form-label" for="archivo">
                {{ $cancionEditando && $cancionEditando->archivo ? 'Reemplazar canción'  : 'Subir canción' }}
                </label>
                <input type="file" id="archivo" name="archivo" wire:model="archivo"  data-input="archivo" class="form-control inputFile " accept=".mp3">
                @if($errors->has('archivo'))
                <div class="text-danger ti-12px mt-2">
                  <i class="ti ti-circle-x"></i> {{ $errors->first('archivo') }}
                </div>
                @endif
              </div>
              <!-- /archivo -->

              @livewire('TiempoConDios.selector-de-albumes', [])

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
                <label class="form-label" for="artista">Artista</label>
                <input id="artista" name="artista" wire:model.defer="artista" type="text" class="form-control" />
                @error('artista')
                <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                </div>
                @enderror
              </div>

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

  <!-- crear y editar álbum  -->
  <form id="nuevaEditarAlbum" role="form" class="forms-sample" wire:submit.prevent="guardarAlbum" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar"  tabindex="-1" id="modalNuevaEditarAlbum" aria-labelledby="modalNuevaEditarAlbumLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalNuevaEditarAlbumLabel">
              @if($modoEdicionAlbum) Editar álbum @else Nuevo álbum @endif
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
            <div class="mb-4">
              @if($modoEdicionAlbum)
              <span class="text-black ti-14px mb-4">Estas editando el álbum <b>"{{ $albumEditando->nombre }}"</b>, por favor ingresa toda la información. </span>
              @else
              <span class="text-black ti-14px mb-4">Estas ingresando un álbum nuevo, por favor ingresa toda la información. </span>
              @endif
            </div>
            @csrf
            <div class="pt-3">

              @if($albumEditando && $albumEditando->imagen)
               <img class="card-img img-fluid mb-3 rounded " src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$albumEditando->imagen) : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$albumEditando->imagen) }}"  alt="album">
              @endif

              <div class="mb-3 col-12">
                <label class="form-label" for="nombreÁlbum">Nombre</label>
                <input id="nombreÁlbum" name="nombreÁlbum" wire:model.defer="nombreÁlbum" type="text" class="form-control" />
                @error('nombreÁlbum')
                <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                </div>
                @enderror
              </div>

              <!-- imagen -->
              <div class="mb-3 col-12">
                <label id="label_imagen" class="form-label" for="imagen">
                {{ $albumEditando && $albumEditando->imagen ? 'Reemplazar imagen'  : 'Subir imagen' }}
                </label>
                <input type="file" id="imagen" name="imagen" wire:model="imagen"  data-input="imagen" class="form-control inputFile " accept=".jpg, .png, .jpeg">
                @if($errors->has('imagen'))
                <div class="text-danger ti-12px mt-2">
                  <i class="ti ti-circle-x"></i> {{ $errors->first('imagen') }}
                </div>
                @endif
                <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>La imagen debe ser de 300px alto y 300px ancho</div>
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

  <!-- Gestionar album -->
  <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar"  tabindex="-1" id="modalGestionarAlbum" aria-labelledby="modalGestionarAlbumLabel">
    <div class="offcanvas-header my-1 px-8">
        <h4 class="offcanvas-title fw-bold text-primary" id="modalGestionarAlbumLabel">
          Gestionar álbum
        </h4>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body pt-6 px-8">
      <div class="row g-2 listadoDeAlbumes mt-5">

        <div class="d-flex flex-row-reverse">
          <a href="javascript:;" wire:click="crearAlbum" class="btn btn-primary rounded-pill px-7 py-2 mx-1"><i class="ti ti-plus me-2"></i> Nuevo álbum </a>
        </div>

        <div class="col-12 my-3">
          <div class="input-group">
            <input wire:model.live.debounce.500ms="busquedaAlbumes" type="text" class="form-control" id="busquedaAlbumes" name="busquedaAlbumes" placeholder="Buscar">
          </div>
        </div>

        @foreach ($albumes as $album)
        <div class="col-12">
          <div class="card border">
            <div class="card-body p-1">
              <div class="row">
                <div class="col-3 d-flex align-items-center">
                  @if($album->imagen)
                    <img class="card-img img-fluid" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$album->imagen) : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$album->imagen) }}?v={{ time() }}"  alt="album">
                  @else
                    <img class="card-img img-fluid" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/album-default.png') : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/album-default.png') }}?v={{ time() }}"  alt="album">
                  @endif
                </div>
                <div class="col-7 my-2 text-start">
                  <h6 class="text-truncate mb-0">{{ $album->nombre }}</h6>
                  <p class="text-muted  mb-0"> <i class="ti ti-playlist"></i> {{ $album->canciones()->count() }} {{ $album->canciones()->count() == 1 ? 'Canción': 'Canciones' }}</p>
                </div>

                <div class="col-2 d-flex justify-content-end d-flex align-items-center">
                  <a href="javascript:;" wire:click="editarAlbum({{ $album->id }})"  class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar álbum"><i class="ti ti-edit "></i></a>
                  <a href="javascript:;" wire:click="$dispatch('eliminarAlbum', { albumId: {{ $album->id }}, nombreAlbum: '{{ $album->nombre }}' })" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="right" title="Eliminar álbum"><i class="ti ti-trash "></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

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
    const cancionesList = document.querySelector('.listadoDeCanciones');

    Sortable.create(cancionesList, {
        animation: 150,
        handle: '.drag-handle',
        onEnd: function (evt) {
          let nuevoOrden = [];
          const canciones = cancionesList.children;
          //alert( JSON.stringify(canciones));
          // 1. Recorrer los elementos y crear el array de nuevo orden
          for (let i = 0; i < canciones.length; i++) {
              nuevoOrden.push({
                  id: canciones[i].dataset.cancionId,
                  orden: i + 1 // El orden comienza en 1
              });
          }

          //alert( JSON.stringify(nuevoOrden));

          // 2. Emitir el evento a Livewire
          $wire.actualizarOrden( JSON.stringify(nuevoOrden) );
        }
    });
  });

  document.addEventListener('livewire:initialized', () => {
      const inputImagen = document.getElementById('imagen');
      const btnGuardar = document.querySelector('#nuevaEditarAlbum .btnGuardar');
      const btnLoader = document.querySelector('#nuevaEditarAlbum .btnGuardarLoader');

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

  document.addEventListener('livewire:initialized', () => {
    const inputImagen = document.getElementById('archivo');
    const btnGuardar = document.querySelector('#nuevaEditarCancion .btnGuardar');
    const btnLoader = document.querySelector('#nuevaEditarCancion .btnGuardarLoader');

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

  $wire.on('eliminarCancion', (params) => {
      const cancionId = params.cancionId;
      const nombreCancion = params.nombreCancion;
      Swal.fire({
        title: '¿Deseas eliminar la canción "'+nombreCancion+'"?',
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
        $wire.eliminarCancion(cancionId);

        Swal.fire({
          title: '¡Eliminado!',
          text: 'La canción '+nombreSeccion+' fue eliminada correctamente.',
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

  $wire.on('eliminarAlbum', (params) => {
      const albumId = params.albumId;
      const nombreAlbum = params.nombreAlbum;
      Swal.fire({
        title: '¿Deseas eliminar el álbum '+nombreAlbum+'?',
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
        $wire.eliminarAlbum(albumId);

        Swal.fire({
          title: '¡Eliminado!',
          text: 'El álbum "'+nombreSeccion+'" fue eliminado correctamente.',
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
