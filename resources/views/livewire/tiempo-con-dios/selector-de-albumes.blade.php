<div class="mb-3 col-12" wire:mouseover="desplegarListaBusqueda" wire:mouseout="ocultarListaBusqueda">
    <label class="form-label">
      Seleccionar álbum
    </label>
    <div class="{{ $verInputBusqueda ? '' : 'd-none'}}">
      <div class="input-group input-group-merge">
        <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
        <input wire:model.live.debounce.100ms="busqueda" type="text" class="form-control"  placeholder="Buscar álbum" aria-label="Buscar " aria-describedby="basic-addon-search31" spellcheck="false" data-ms-editor="true">
      </div>
    </div>

    <div class="divListaBusquedaAlbum position-relative {{ $verListaBusqueda ? ''  : 'd-none' }}">
      <div id="listaItemsBusquedaAlbum" class="panel-busqueda p-2 position-absolute">
        @if($albumes && count($albumes) >0)
          @foreach($albumes as $album)
          <a href="javascript:;" wire:click="seleccionarAlbum({{$album->id}})" class="dropdown-item d-flex align-items-center p-2 mb-1 border">
            <div class="avatar me-2" >
              @if($album->imagen)
              <img class="img-fluid rounded"  src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$album->imagen) : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$album->imagen) }}"  alt="album">
              @else
                <img class="img-fluid" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/album-default.png') : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/album-default.png') }}"  alt="album">
              @endif
            </div>
            <div class="flex-grow-1 me-2">
              <p class="fs-7 text-wrap m-0">{{ $album->nombre }}</p>
            </div>
          </a>
          @endforeach
        @else
        <div class="text-center pt-3">
          <center>
            <p class="tx-12 text-muted"> <i class="ti ti-list-search fs-4"> </i>
            {{ strlen($busqueda) < 3 ? 'Busca el campo': 'La busqueda no arrojo ningun resultado.' }}
            </p>
          </center>
        </div>
        @endif
      </div>
    </div>

    @if($albumSeleccionado)
      <div class="col-12">
        <div class="dropdown-item w-100 m-0 d-flex p-1 border flex-grow-1">
          <div class="flex-fill d-flex align-items-center">
            <div class="avatar me-2" >
              @if($albumSeleccionado->imagen)
              <img class="img-fluid rounded"  src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$albumSeleccionado->imagen) : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/'.$albumSeleccionado->imagen) }}"  alt="album">
              @else
              <img class="img-fluid" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/album-default.png') : Storage::url($configuracion->ruta_almacenamiento.'/reproductor-audio/imagenes/album-default.png') }}"  alt="album">
              @endif
            </div>
            <div class="flex-grow-1 me-2">
              <p class="fs-6 text-wrap m-0 mt-1 fw-bold">{{ $albumSeleccionado->nombre }}</p>
            </div>
          </div>

          <div class="d-flex align-items-start my-auto">
            <button type="button" wire:click="quitarSeleccion" class="align-self-start btn btn-danger btn-xs p-1"><i class="ti ti-x fs-6"></i></button>
          </div>
        </div>
      </div>
    @endif

    @if($errors->has('album') || $mostrarError == true)
    <div wire:ignore class="text-danger ti-12px mt-2">
      <i class="ti ti-circle-x"></i> {{ $errors->first('album') ? $errors->first('album') : $msnError }}
    </div>
    @endif

    <input type="text" id="album" name="álbum" value="{{$albumSeleccionado ? $albumSeleccionado->id : '' }}" class="form-control d-none" placeholder="">
</div>
