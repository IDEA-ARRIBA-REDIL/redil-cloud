<div class="{{ $class }}">
    <div class="card shadow text-white" style="background-color: #1C1C1E !important; border-radius: 25px">
        <div class="card-body">
            <div class="row mx-3 mt-4">
                <div class="col-4 col-md-3 col-xl-4 d-flex align-items-center">
                  <img class="card-img img-fluid" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$imgAlbumActual) : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$imgAlbumActual) }}"  alt="album">
                </div>
                <div class="col-8 col-md-9 col-xl-8 my-2 text-md-start">
                  <h4 class="text-white text-truncate mb-0">{{ $cancionActual->nombre }} </h4>
                  <p class="text-muted  mb-0">{{ $cancionActual->album ? $cancionActual->album->nombre : 'Álbum
                      desconocido'}}</p>
                  <p class="mb-0">{{ $cancionActual->artista ? $cancionActual->artista : 'Artista desconocido'}}</p>
                </div>
            </div>
            <div class="row mt-5">

              <div  class="col-8 col-lg-9 d-flex justify-content-center">
                  <audio class="d-none" id="cancion">
                      <source src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/audios/'.$cancionActual->archivo) : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/audios/'.$cancionActual->archivo) }}" type="audio/mp3">
                  </audio>
                  <div class="d-flex flex-grow-1">
                      <span id="duracion" class="my-auto mx-3 fs-6">{{ $duracionCancionActual }}</span>
                      <input id="progreso" type="range" value="0" class="flex-grow-1 my-auto " style="accent-color: #f5365c;">
                      <span id="tiempoRestante" class="my-auto mx-3">0:00</span>
                  </div>
              </div>
              <div class="col-4 col-lg-3 d-flex justify-content-center">
                <i class="ti ti-volume ti-lg my-auto me-1"></i>
                <input id="volumen-control" type="range" min="0" max="1" step="0.01" value="1" class="my-auto" style="width: 100%; accent-color: #f5365c;">
              </div>

            </div>
            <div class="row mt-2">
              <div class="col-2 col-lg-2 d-flex justify-content-center">
                <button id="aleatorio" type="button" class="btn rounded-pill text-white btn-md my-auto" wire:click="modoAleatorio" wire:loading.attr="disabled">
                  @if($aleatorio)
                  <i class="ti ti-arrows-shuffle ti-md text-primary"></i>
                  @else
                  <i class="ti ti-arrows-shuffle ti-md"></i>
                  @endif
                </button>
              </div>
              <div class="col-8 col-lg-8 d-flex justify-content-center">
                <button id="anteriorCancion" type="button" class="btn rounded-pill text-white btn-md my-auto" wire:click="anteriorCancion" wire:loading.attr="disabled">
                  <i class="ti ti-player-track-prev ti-md"></i>
                </button>
                <button id="btnReproducirPausar" type="button" class="btn rounded-pill text-white btn-lg mx-3" >
                    <i id="iconoControl" class="ti ti-player-{{ $iconoPlayPause ? $iconoPlayPause : 'play'}}" style="font-size: 50px !important;"></i>
                </button>
                <button id="anteriorSiguiente" type="button" class="btn rounded-pill text-white btn-md  my-auto" wire:click="siguienteCancion" wire:loading.attr="disabled">
                  <i class="ti ti-player-track-next ti-md"></i>
                </button>
              </div>
                <div class="col-2 col-lg-2 d-flex justify-content-center">
                <button id="playlist" type="button" class="btn rounded-pill text-white btn-md my-auto" wire:click="abrirPlaylist" wire:loading.attr="disabled">
                  <i class="ti ti-playlist ti-md"></i>
                </button>
              </div>

            </div>
        </div>
    </div>



  <!-- Playlist -->
  <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar" style="background-color: #1C1C1E !important"  tabindex="-1" id="modalPlayList" aria-labelledby="modalPlayListLabel">
    <div class="offcanvas-header my-1 px-8">
      <button type="button" class="btn rounded-pill btn-icon waves-effect waves-light me-5" style="background-color: #000" data-bs-dismiss="offcanvas" aria-label="Close">
        <i class="ti ti-x ti-lg"></i>
      </button>
      <h4 class="offcanvas-title text-white" id="modalPlayListLabel">
        Play list
      </h4>
    </div>
    <div class="offcanvas-body pt-6 px-8">
      <div class="row g-2">
        @foreach ($listaCanciones as $cancion)
        <div class="col-12 m-0">
          <div class="card" style="background-color: #1C1C1E !important">
            <div class="card-body p-1">
              <div class="row">
                <div class="col-2 d-flex align-items-center">
                  <button wire:click="playEspecifico({{ $cancion->id }})" type="button" class="btn rounded-pill btn-icon rounded border text-white btn-md" >
                    <i id="iconoControl" class="ti ti-player-play"></i>
                  </button>
                </div>
                <div class="col-10 my-2 text-start">
                  <h6 class="text-white text-truncate mb-0">{{ $cancion->nombre }}</h6>
                  <p class="text-muted mb-0"> {{ $cancion->artista }} </p>
                </div>
              </div>
            </div>
          </div>
          <hr class="m-0" style="color: #000" >
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

@script
<script>
    const cancion = document.getElementById('cancion');
    const duracionElement = document.getElementById('duracion');
    const tiempoRestanteElement = document.getElementById('tiempoRestante');
    const progreso = document.getElementById('progreso');

    const inconoControl = document.getElementById('iconoControl');
    const botonReproducirPausar = document.getElementById('btnReproducirPausar');

    const volumenControl = document.getElementById('volumen-control');

    volumenControl.addEventListener('input', () => {
        cancion.volume = volumenControl.value;
    });

    // Función para formatear el tiempo (segundos a minutos:segundos)
    function formatearTiempo(tiempo) {
        const minutos = Math.floor(tiempo / 60);
        const segundos = Math.floor(tiempo % 60);
        return `${minutos}:${segundos.toString().padStart(2, '0')}`;
    }

     // Función para actualizar el tiempo restante
    function actualizarTiempoRestante() {
        const tiempoRestante = cancion.duration - cancion.currentTime;
        if (isNaN(tiempoRestante)) {
              tiempoRestanteElement.textContent = '0:00';
        } else {
              tiempoRestanteElement.textContent = '-' + formatearTiempo(tiempoRestante);
        }

    }

    function actualizarInfoCancion(){
      cancion.addEventListener('loadeddata',function(){});
    };

    cancion.addEventListener('loadedmetadata', function(){
      progreso.max = cancion.duration;
      progreso.value = cancion.currentTime;

      duracionElement.textContent = formatearTiempo(cancion.duration);
      actualizarTiempoRestante();

    });

    botonReproducirPausar.addEventListener('click', reproducirPausar);

    function reproducirPausar(){

      if(cancion.paused){
          reproducirCancion();
      } else {
          pausarCancion();
      }
    };

    function reproducirCancion(){
        cancion.play();
        inconoControl.classList.add('ti-player-pause');
        inconoControl.classList.remove('ti-player-play');
    }

    function pausarCancion(){
        cancion.pause();
        inconoControl.classList.remove('ti-player-pause');
        inconoControl.classList.add('ti-player-play');
    }

    cancion.addEventListener('timeupdate', function(){
        if(!cancion.paused){
            progreso.value = cancion.currentTime;
            actualizarTiempoRestante();
        }
    });

    progreso.addEventListener('input', function(){
        cancion.currentTime = progreso.value;
        actualizarTiempoRestante();
    });

    actualizarInfoCancion();

    cancion.addEventListener('ended', () => {
      // Lógica para reproducir la siguiente canción
        $wire.$call('siguienteCancion');
    });

    // Escuchar el evento 'cambiar-cancion-play' desde Livewire
    Livewire.on('cambiar-cancion-play', ({ src }) => {
      // Emitir evento a Livewire
      cancion.src = src;
      cancion.load(); // Importante para cargar la nueva fuente
      reproducirPausar();

    });

    // Escuchar el evento 'cambiar-cancion' desde Livewire
    Livewire.on('cambiar-cancion', ({ src }) => {
      // Emitir evento a Livewire
      cancion.src = src;
      cancion.load();

    });

     Livewire.on('pausar', ({ src }) => {
      pausarCancion();

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
