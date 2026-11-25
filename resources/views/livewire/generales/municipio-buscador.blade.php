<div class="{{ $mostrar ? $class : '' }}" x-data="{ abierto: false }" @click.outside="abierto = false" @keydown.escape="abierto = false">
  @if($mostrar)
    @if($label)
    <label class="form-label" for="municipio_id">
      {{ $label }}
    </label>
    @endif

    <div class="{{ $verInputBusqueda ? '' : 'd-none' }}">
      <div class="input-group input-group-merge">
        <span class="input-group-text" @if($this->estaDeshabilitado) style="background-color: #eceef1; border-color: #d9dee3; color: #a1acb8;" @endif>
          <i class="ti ti-building-community" wire:loading.remove wire:target="busqueda"></i>
          <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true" wire:loading wire:target="busqueda"></span>
        </span>
        <input
          x-ref="inputBusquedaMunicipio"
          wire:model.live.debounce.250ms="busqueda"
          @focus="if (!{{ $this->estaDeshabilitado ? 'true' : 'false' }}) abierto = true"
          @click="if (!{{ $this->estaDeshabilitado ? 'true' : 'false' }}) abierto = true"
          @input="if (!{{ $this->estaDeshabilitado ? 'true' : 'false' }}) abierto = true"
          type="search"
          role="combobox"
          aria-autocomplete="list"
          class="form-control"
          @if($this->estaDeshabilitado) style="background-color: #eceef1; border-color: #d9dee3; padding-left: 0.75rem;" @endif
          placeholder="{{ $this->estaDeshabilitado ? 'Primero selecciona un país' : ($placeholder ?: 'Busca aquí la ciudad') }}"
          @disabled($this->estaDeshabilitado)
          spellcheck="false"
          data-lpignore="true"
          data-1p-ignore="true"
          data-form-type="other"
          autocomplete="one-time-code">
      </div>
    </div>

    @if(! $this->estaDeshabilitado)
    <div class="divListaBusquedaUbicacion position-relative {{ $verInputBusqueda ? '' : 'd-none' }}" x-show="abierto" x-transition.opacity.duration.150ms style="display: none;">
      <div class="panel-busqueda p-2 position-absolute" style="height: auto; max-height: 240px; overflow-y: auto;">
        <div wire:loading wire:target="busqueda" class="text-center py-3">
          <p class="tx-12 text-muted m-0">
            <span class="spinner-border spinner-border-sm text-primary me-1" role="status" aria-hidden="true"></span>
            Buscando ciudades...
          </p>
        </div>

        <div wire:loading.remove wire:target="busqueda">
          @if($municipios && count($municipios) > 0)
            @foreach($municipios as $municipio)
            <a href="javascript:;" wire:click="seleccionarMunicipio({{ $municipio->id }})" @click="abierto = false" class="dropdown-item d-flex align-items-center p-2 mb-1 border rounded">
              <div class="d-flex align-items-center justify-content-center rounded me-3 bg-primary p-2">
                <i class="ti ti-building-community text-white fs-4"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p class="fs-6 text-wrap m-0">{{ $municipio->nombre }}</p>
              </div>
            </a>
            @endforeach
          @else
          <div class="text-center pt-3">
            <p class="tx-12 text-muted">
              <i class="ti ti-list-search fs-4"></i>
              {{ mb_strlen(trim($busqueda)) < 2 ? ($placeholder ?: 'Escribe al menos 2 caracteres para buscar.') : 'La búsqueda no arrojó ningún resultado.' }}
            </p>
          </div>
          @endif
        </div>
      </div>
    </div>
    @endif

    @if($municipioSeleccionado)
      <div class="col-12">
        <div class="dropdown-item w-100 m-0 d-flex p-1 border flex-grow-1">
          <div class="flex-fill d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center rounded me-3 bg-primary p-1">
              <i class="ti ti-building-community text-white fs-5"></i>
            </div>
            <div class="flex-grow-1 me-2">
              <p class="fs-6 text-wrap m-0 mt-1 fw-bold">{{ $municipioSeleccionado->nombre }}</p>
            </div>
          </div>

          <div class="d-flex align-items-start">
            <button type="button" wire:click="quitarSeleccion" @click="abierto = true; $nextTick(() => $refs.inputBusquedaMunicipio?.focus())" class="align-self-start btn btn-danger btn-xs p-1">
              <i class="ti ti-x fs-6"></i>
            </button>
          </div>
        </div>
      </div>
    @endif

    @if($informacionDeApoyo)
      <div class="ti-12px mt-2"><i class="text-info ti ti-info-circle me-1"></i>{{ $informacionDeApoyo }}</div>
    @endif

    @if($errors->has($nameId) || $mostrarError == true)
    <div wire:ignore class="text-danger ti-12px mt-2">
      <i class="ti ti-circle-x"></i> {{ $errors->first($nameId) ? $errors->first($nameId) : $msnError }}
    </div>
    @endif

    <input type="text" id="municipio_id" name="{{ $nameId }}" value="{{ $municipioSeleccionado ? $municipioSeleccionado->id : '' }}" class="form-control d-none" placeholder="">
  @endif
</div>
