<div>
  <div class="d-flex flex-row-reverse">
    <a href="javascript:;" wire:click="abrirGestionarAlbum" class="btn btn-primary rounded-pill px-7 py-2 mx-1"><i class="ti ti-disc me-2"></i> Gestionar albumes </a>
    <a href="javascript:;" wire:click="crearCancion" class="btn btn-primary rounded-pill px-7 py-2 mx-1"><i class="ti ti-music-plus me-2"></i> Nueva canción </a>
  </div>

  <div class="row g-3 pt-6 justify-content-center">
    <div class="col-12 col-md-5">
      <div class="input-group">
        <span class="input-group-text"><i class="ti ti-search"></i></span>
        <input wire:model.live.debounce.400ms="busqueda" type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Buscar por canción, artista o álbum...">
        @if($busqueda)
        <button class="btn btn-outline-secondary" type="button" wire:click="$set('busqueda', '')">
          <i class="ti ti-x"></i>
        </button>
        @endif
      </div>
    </div>
    <div class="col-12 col-md-4">
      <select wire:model.live="filtroAlbum" class="form-select" id="filtroAlbum">
        <option value="">Todos los álbumes</option>
        <option value="sin-album">Sin álbum asignado</option>
        @foreach ($todosLosAlbumes as $itemAlbum)
          <option value="{{ $itemAlbum->id }}">{{ $itemAlbum->nombre }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="row g-2 listadoDeCanciones mt-5">
    @forelse ($canciones as $cancion)
    <div class="col-12 col-md-4" data-cancion-id="{{ $cancion->id }}">
      <div class="card border">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="flex-shrink-1">
              @if(!$busqueda && !$filtroAlbum)
              <a href="javascript:;" class="" data-bs-toggle="tooltip" data-bs-placement="right" title="Ordenar sección"><i class="text-black ti ti-grip-horizontal drag-handle"></i></a>
              @endif
            </div>
            <div class="d-flex justify-content-end">
              <div>
                <a href="javascript:;" wire:click="editarCancion({{ $cancion->id }})"  class="text-black" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar cancion"><i class="ti ti-edit "></i></a>
                <a href="javascript:;" wire:click="$dispatch('eliminarCancion', { cancionId: {{ $cancion->id }}, nombreCancion: '{{ $cancion->nombre }}' })" class="text-black" data-bs-toggle="tooltip" data-bs-placement="right" title="Eliminar cancion"><i class="ti ti-trash "></i></a>
              </div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-6 offset-3 offset-md-0 col-md-3 col-xl-4 d-flex align-items-center">
              <img class="card-img img-fluid" src="{{ $cancion->album ? $cancion->album->portada_url : Storage::disk('global_media')->url('reproductor/album-default.png') }}" alt="album">
            </div>
            <div class="col-12 col-md-9 col-xl-8 my-2 text-center text-md-start">
              <h4 class="text-truncate mb-0 text-black">{{ $cancion->nombre }}</h4>
              <p class="text-black  mb-0">{{ $cancion->album ? $cancion->album->nombre : 'Álbum desconocido'}}</p>
              <p class="text-black mb-0">{{ $cancion->artista ? $cancion->artista : 'Artista desconocido'}}</p>
              <p class="mb-0 text-black"><i class="ti ti-number ti-sm"></i><span class="fw-medium mx-1">Orden:</span><span>{{ $cancion->orden }}</span></p>
            </div>
            <div class="col-12 mt-3" >
              <audio controls id="cancion" class="w-100" preload="none">
                <source src="{{ $cancion->ruta_audio }}?v={{ time() }}" type="audio/mp3">
              </audio>
            </div>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
      <i class="ti ti-music-off fs-1 text-black d-block mb-2"></i>
      <h5 class="text-black mb-1">No se encontraron canciones</h5>
      <p class="text-black small">Intenta ajustar tu búsqueda o el filtro de álbumes.</p>
    </div>
    @endforelse
  </div>

  <!-- crear y editar canción  -->
  <form id="nuevaEditarCancion" role="form" class="forms-sample" wire:submit.prevent="guardarCancion" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="modalNuevaEditarCancion" aria-labelledby="modalNuevaEditarCancionLabel" data-bs-backdrop="false" data-bs-scroll="false">
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
                <input type="file" id="archivo" name="archivo" wire:model="archivo" class="form-control inputFile" accept=".mp3, .wav, .mp4">
                <div wire:loading wire:target="archivo" class="text-primary ti-12px mt-2">
                  <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                  <span>Cargando archivo de audio...</span>
                </div>
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
                <input id="nombre" name="nombre" wire:model="nombre" type="text" class="form-control" />
                @error('nombre')
                <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                </div>
                @enderror
              </div>

              <div class="mb-3 col-12">
                <label class="form-label" for="artista">Artista</label>
                <input id="artista" name="artista" wire:model="artista" type="text" class="form-control" />
                @error('artista')
                <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $message }}
                </div>
                @enderror
              </div>

            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <!-- Spinner al procesar en servidor -->
            <button class="d-none btn btn-sm py-2 px-4 btn-primary waves-effect waves-light rounded-pill" 
                    wire:loading.class.remove="d-none"
                    wire:target="guardarCancion,archivo"
                    type="button" 
                    disabled="">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <span class="ms-1">Guardando...</span>
            </button>

            <!-- Botones normales (se ocultan si está guardando) -->
            <button wire:loading.class="d-none" wire:target="guardarCancion,archivo" type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
            <button wire:loading.attr="disabled" wire:target="guardarCancion,archivo" type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

  <!-- crear y editar álbum  -->
  <form id="nuevaEditarAlbum" role="form" class="forms-sample" wire:submit.prevent="guardarAlbum" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="modalNuevaEditarAlbum" aria-labelledby="modalNuevaEditarAlbumLabel" data-bs-backdrop="false" data-bs-scroll="false">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalNuevaEditarAlbumLabel">
              @if($modoEdicionAlbum) Editar álbum @else Nuevo álbum @endif
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
            <div class="mb-4 pb-5">
              @if($modoEdicionAlbum)
              <span class="text-black ti-14px mb-4">Estas editando el álbum <b>"{{ $albumEditando->nombre }}"</b>, por favor ingresa toda la información. </span>
              @else
              <span class="text-black ti-14px mb-4">Estas ingresando un álbum nuevo, por favor ingresa toda la información. </span>
              @endif
            </div>
            @csrf
            <div class="pt-3">

              <div class="mb-3 col-12 text-center" id="contenedorPreviewAlbum">
                @if($albumEditando && $albumEditando->imagen)
                  <img id="previewAlbumPortada" class="card-img img-fluid mb-2 rounded shadow-sm" style="max-height: 180px; width: 180px; object-fit: cover;" src="{{ $albumEditando->portada_url }}" alt="album">
                @else
                  <img id="previewAlbumPortada" class="card-img img-fluid mb-2 rounded shadow-sm d-none" style="max-height: 180px; width: 180px; object-fit: cover;" src="" alt="album">
                @endif
              </div>

              <div class="mb-3 col-12">
                <label class="form-label" for="nombreAlbum">Nombre</label>
                <input id="nombreAlbum" name="nombreAlbum" wire:model="nombreAlbum" type="text" class="form-control" />
                @error('nombreAlbum')
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
                <input type="file" id="imagen" class="form-control inputFile" accept=".jpg, .png, .jpeg, .webp">
                <div wire:loading wire:target="imagen" class="text-primary ti-12px mt-2">
                  <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                  <span>Cargando imagen recortada...</span>
                </div>
                @if($errors->has('imagen'))
                <div class="text-danger ti-12px mt-2">
                  <i class="ti ti-circle-x"></i> {{ $errors->first('imagen') }}
                </div>
                @endif
                <div class="ti-12px mt-2 text-muted"> <i class="text-info ti ti-crop me-1"></i>Al elegir una imagen se abrirá el recortador cuadrado (300x300px).</div>
              </div>
              <!-- /imagen -->

            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <!-- Spinner al procesar en servidor -->
            <button class="d-none btn btn-sm py-2 px-4 btn-primary waves-effect waves-light rounded-pill" 
                    wire:loading.class.remove="d-none"
                    wire:target="guardarAlbum,imagen"
                    type="button" 
                    disabled="">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <span class="ms-1">Guardando...</span>
            </button>

            <!-- Botones normales (se ocultan si está guardando) -->
            <button wire:loading.class="d-none" wire:target="guardarAlbum,imagen" type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
            <button wire:loading.attr="disabled" wire:target="guardarAlbum,imagen" type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

  <!-- Gestionar album -->
  <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="modalGestionarAlbum" aria-labelledby="modalGestionarAlbumLabel" data-bs-backdrop="false" data-bs-scroll="false">
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
                  <img class="card-img img-fluid" src="{{ $album->portada_url }}" alt="album">
                </div>
                <div class="col-7 my-2 text-start">
                  <h6 class="text-truncate mb-0">{{ $album->nombre }}</h6>
                  <p class="text-black  mb-0"> <i class="ti ti-playlist"></i> {{ $album->canciones_count }} {{ $album->canciones_count == 1 ? 'Canción': 'Canciones' }}</p>
                </div>

                <div class="col-2 d-flex justify-content-end d-flex align-items-center">
                  <a href="javascript:;" wire:click="editarAlbum({{ $album->id }})"  class="text-black" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar álbum"><i class="ti ti-edit "></i></a>
                  <a href="javascript:;" wire:click="$dispatch('eliminarAlbum', { albumId: {{ $album->id }}, nombreAlbum: '{{ $album->nombre }}' })" class="text-black" data-bs-toggle="tooltip" data-bs-placement="right" title="Eliminar álbum"><i class="ti ti-trash "></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- Modal Recorte Álbum (Cropper) -->
  <div wire:ignore.self class="modal fade" id="modalRecorteAlbum" tabindex="-1" aria-labelledby="modalRecorteAlbumLabel" aria-hidden="true" style="z-index: 1090;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-primary" id="modalRecorteAlbumLabel"><i class="ti ti-crop me-2"></i>Recortar portada de álbum</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
          <div class="img-container d-flex justify-content-center align-items-center bg-dark" style="min-height: 350px; max-height: 65vh; overflow: hidden;">
            <img id="croppingImageAlbum" src="" alt="Recorte" style="max-width: 100%; display: block;">
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between pt-5">
          <span class="text-black small"><i class="ti ti-aspect-ratio me-1"></i>Formato cuadrado 1:1 (300x300px)</span>
          <div>
            <button type="button" class="btn btn-label-secondary rounded-pill me-2" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" id="btnAplicarRecorteAlbum" class="btn btn-primary rounded-pill">
              <span id="btnRecorteSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
              <i class="ti ti-check me-1"></i> Aplicar recorte
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

@assets
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/cropperjs/cropper.css',
  ]);

  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/sortablejs/sortable.js',
    'resources/assets/vendor/libs/cropperjs/cropper.js',
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
          for (let i = 0; i < canciones.length; i++) {
              nuevoOrden.push({
                  id: canciones[i].dataset.cancionId,
                  orden: i + 1
              });
          }

          $wire.actualizarOrden( JSON.stringify(nuevoOrden) );
        }
    });
  });

  // Lógica de Cropper para Portada de Álbum
  document.addEventListener('livewire:initialized', () => {
      const inputImagen = document.getElementById('imagen');
      const croppingImage = document.getElementById('croppingImageAlbum');
      const modalRecorteEl = document.getElementById('modalRecorteAlbum');
      const btnAplicarRecorte = document.getElementById('btnAplicarRecorteAlbum');
      const btnRecorteSpinner = document.getElementById('btnRecorteSpinner');
      const previewAlbumPortada = document.getElementById('previewAlbumPortada');
      let cropper = null;

      if (inputImagen && modalRecorteEl) {
        inputImagen.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) {
                return;
            }

            if (!file.type.match(/^image\/(jpeg|png|jpg|webp|gif)$/i)) {
              Swal.fire({
                title: 'Formato no soportado',
                text: 'Por favor selecciona una imagen válida (JPG, PNG o WEBP).',
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
              });
              inputImagen.value = '';
              return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                croppingImage.src = event.target.result;
                if (cropper) {
                  cropper.destroy();
                  cropper = null;
                }
                const modalRecorte = bootstrap.Modal.getOrCreateInstance(modalRecorteEl);
                modalRecorte.show();
            };
            reader.readAsDataURL(file);
        });

        modalRecorteEl.addEventListener('shown.bs.modal', () => {
          if (cropper) {
            cropper.destroy();
          }
          cropper = new Cropper(croppingImage, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            responsive: true,
            restore: false,
            checkCrossOrigin: false,
            zoomable: true
          });
        });

        modalRecorteEl.addEventListener('hidden.bs.modal', () => {
          if (cropper) {
            cropper.destroy();
            cropper = null;
          }
        });

        if (btnAplicarRecorte) {
          btnAplicarRecorte.addEventListener('click', () => {
            if (!cropper) return;

            btnAplicarRecorte.disabled = true;
            if (btnRecorteSpinner) btnRecorteSpinner.classList.remove('d-none');

            const canvas = cropper.getCroppedCanvas({
              width: 300,
              height: 300,
              imageSmoothingEnabled: true,
              imageSmoothingQuality: 'high'
            });

            canvas.toBlob((blob) => {
              if (!blob) {
                btnAplicarRecorte.disabled = false;
                if (btnRecorteSpinner) btnRecorteSpinner.classList.add('d-none');
                return;
              }

              const fileRecortado = new File([blob], 'album-portada-' + Date.now() + '.png', { type: 'image/png' });

              $wire.upload('imagen', fileRecortado, () => {
                // Vista previa local inmediata
                if (previewAlbumPortada) {
                  previewAlbumPortada.src = canvas.toDataURL();
                  previewAlbumPortada.classList.remove('d-none');
                }
                btnAplicarRecorte.disabled = false;
                if (btnRecorteSpinner) btnRecorteSpinner.classList.add('d-none');

                const modalRecorte = bootstrap.Modal.getInstance(modalRecorteEl);
                if (modalRecorte) {
                  modalRecorte.hide();
                }
              }, () => {
                btnAplicarRecorte.disabled = false;
                if (btnRecorteSpinner) btnRecorteSpinner.classList.add('d-none');
                Swal.fire('Error', 'No se pudo procesar la imagen recortada.', 'error');
              });
            }, 'image/png');
          });
        }
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
          text: 'La canción "'+nombreCancion+'" fue eliminada correctamente.',
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
          text: 'El álbum "'+nombreAlbum+'" fue eliminado correctamente.',
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

  $wire.on('msn', (params) => {
    const data = Array.isArray(params) ? params[0] : params;
    Swal.fire({
      title: data?.msnTitulo || '',
      html: data?.msnTexto || '',
      icon: data?.msnIcono || 'info',
      customClass: {
          confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
  });

  const MODALES_GESTION = ['modalNuevaEditarCancion', 'modalNuevaEditarAlbum', 'modalGestionarAlbum'];

  $wire.on('cerrarModal', (params) => {
    const data = Array.isArray(params) ? params[0] : params;
    const nombreModal = data?.nombreModal;
    if (!nombreModal || !MODALES_GESTION.includes(nombreModal)) return;

    const offcanvasElement = document.getElementById(nombreModal);
    if (offcanvasElement) {
      const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
      if (offcanvas) {
        offcanvas.hide();
      }
    }

    setTimeout(() => {
      if (!document.querySelector('.offcanvas.show')) {
        document.querySelectorAll('.offcanvas-backdrop').forEach(el => el.remove());
      }
    }, 350);
  });

  $wire.on('abrirModal', (params) => {
    const data = Array.isArray(params) ? params[0] : params;
    const nombreModal = data?.nombreModal;
    if (!nombreModal || !MODALES_GESTION.includes(nombreModal)) return;

    // Limpiar inputs de archivo locales al abrir cualquier modal
    const archivoInput = document.getElementById('archivo');
    if (archivoInput) {
        archivoInput.value = '';
    }
    const imagenInput = document.getElementById('imagen');
    if (imagenInput) {
        imagenInput.value = '';
    }

    // Si es nuevo álbum, resetear preview si no tiene imagen previa
    if (nombreModal === 'modalNuevaEditarAlbum') {
      const previewImg = document.getElementById('previewAlbumPortada');
      if (previewImg && !previewImg.getAttribute('src')) {
        previewImg.classList.add('d-none');
      }
    }

    const offcanvasElement = document.getElementById(nombreModal);
    if (!offcanvasElement) return;

    // Si hay otro offcanvas abierto actualmente, ocultarlo
    document.querySelectorAll('.offcanvas.show').forEach((el) => {
      if (el.id !== nombreModal) {
        const inst = bootstrap.Offcanvas.getInstance(el);
        if (inst) {
          inst.hide();
        }
      }
    });

    // Limpiar backdrops anteriores
    document.querySelectorAll('.offcanvas-backdrop').forEach(el => el.remove());

    // Crear y añadir backdrop explícito al body
    const backdrop = document.createElement('div');
    backdrop.className = 'offcanvas-backdrop fade show';
    document.body.appendChild(backdrop);

    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement, {
      backdrop: false,
      scroll: false
    });
    offcanvas.show();

    // Limpiar backdrop al cerrar este offcanvas
    const removerBackdrop = () => {
      backdrop.remove();
      offcanvasElement.removeEventListener('hidden.bs.offcanvas', removerBackdrop);
    };
    offcanvasElement.addEventListener('hidden.bs.offcanvas', removerBackdrop);

    // Cerrar offcanvas al pulsar fuera sobre el backdrop
    backdrop.addEventListener('click', () => {
      offcanvas.hide();
    });
  });
</script>
@endscript
