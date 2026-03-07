<div class="{{ $mostrar ? $class : '' }} " wire:mouseover="desplegarListaBusqueda" wire:mouseout="ocultarListaBusqueda">
  @if($mostrar)
    @if($label)
    <label class="form-label">
      {{ $label }}
    </label>
    @endif
    <div class="{{ $verInputBusqueda ? '' : 'd-none'}}">
      <div class="input-group input-group-merge">
        <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
        <input wire:model.live.debounce.100ms="busqueda" type="text" class="form-control"  placeholder="{{ $placeholder ? $placeholder : 'Buscar' }}" aria-label="Buscar " aria-describedby="basic-addon-search31" spellcheck="false" data-ms-editor="true">
      </div>
    </div>



    <div class="divListaBusquedaUbicacion position-relative {{ $verListaBusqueda ? ''  : 'd-none' }}">
      <div id="listaItemsBusquedaUbicacion" class="panel-busqueda p-2 position-absolute">
        @if($ubicaciones && count($ubicaciones) >0)
          @foreach($ubicaciones as $ubicacion)
          <a href="javascript:;" wire:click="seleccionarUbicacion({{$ubicacion->id}}, '{{$ubicacion->tipo}}')" class="dropdown-item d-flex align-items-center p-2 mb-1 border">
            <div class="d-flex align-items-center justify-content-center rounded me-3 bg-primary" >
              <i class="ti ti-map-pin-2 text-white" style="font-size: 3.0rem !important"></i>
            </div>
            <div class="flex-grow-1 me-2">
              <p class="fs-7 text-wrap m-0">{{ $ubicacion->nombre }}</p>
              <p class="fs-7 text-wrap fw-bold m-0">{{ $ubicacion->nombreMunicipio }}</p>

            </div>
          </a>
          @endforeach
        @else
        <div class="text-center pt-3">
          <center>
            <p class="tx-12 text-muted"> <i class="ti ti-list-search fs-4"> </i>
            {{ strlen($busqueda) < 3 ? $placeholder : 'La busqueda no arrojo ningun resultado.' }}
            </p>
          </center>
        </div>
        @endif
      </div>
    </div>

    @if($ubicacionSeleccionada)
        <div class="col-12">
          <div class="dropdown-item w-100 m-0 d-flex p-1 border flex-grow-1">
            <div class="flex-fill d-flex align-items-center">
              <div class="d-flex align-items-center justify-content-center rounded me-3 bg-primary">
                <i class="ti ti-map-pin-2 text-white" style="font-size: 1.6rem !important"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p class="fs-6 text-wrap m-0 mt-1 fw-bold">{{ $ubicacionSeleccionada->nombre }}</p>
              </div>
            </div>

            <div class="d-flex align-items-start">
              <button type="button" wire:click="quitarSeleccion('{{ $ubicacionSeleccionada->tipo }}')" class="align-self-start btn btn-danger btn-xs p-1"><i class="ti ti-x fs-6"></i></button>
            </div>
          </div>
      </div>
    @endif

    @if($errors->has($nameId) || $mostrarError == true)
    <div wire:ignore class="text-danger ti-12px mt-2">
      <i class="ti ti-circle-x"></i> {{ $errors->first($nameId) ? $errors->first($nameId) : $msnError }}
    </div>
    @endif

    <input type="text" id="ubicacionId" name="{{$nameId}}" value="{{$ubicacionSeleccionada ? $ubicacionSeleccionada->id : '' }}" class="form-control d-none" placeholder="">
    <input type="text" id="tipoUbicacion" name="tipoUbicacion" value="{{$tipoUbicacionSeleccionada}}" class="form-control d-none" placeholder="tipo ubicación">

  @endif
</div>
