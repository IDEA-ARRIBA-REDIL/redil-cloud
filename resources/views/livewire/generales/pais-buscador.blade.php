<div class="{{ $mostrar ? $class : '' }}" x-data="{ abierto: false }" @click.outside="abierto = false" @keydown.escape="abierto = false">
  @if($mostrar)
    @if($label)
    <label class="form-label" for="pais_id">
      {{ $label }}
    </label>
    @endif

    <div class="{{ $verInputBusqueda ? '' : 'd-none' }}">
      <div class="input-group input-group-merge">
        <span class="input-group-text">
          <i class="ti ti-world" wire:loading.remove wire:target="busqueda"></i>
          <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true" wire:loading wire:target="busqueda"></span>
        </span>
        <input
          x-ref="inputBusquedaPais"
          wire:model.live.debounce.250ms="busqueda"
          @focus="abierto = true"
          @click="abierto = true"
          @input="abierto = true"
          type="search"
          role="combobox"
          aria-autocomplete="list"
          class="form-control"
          placeholder="{{ $placeholder ?: 'Buscar país' }}"
          spellcheck="false"
          data-lpignore="true"
          data-1p-ignore="true"
          data-form-type="other"
          autocomplete="one-time-code">
      </div>
    </div>

    <div class="divListaBusquedaUbicacion position-relative {{ $verInputBusqueda ? '' : 'd-none' }}" x-show="abierto" x-transition.opacity.duration.150ms style="display: none;">
      <div class="panel-busqueda p-2 position-absolute" style="height: auto; max-height: 240px; overflow-y: auto;">
        <div wire:loading wire:target="busqueda" class="text-center py-3">
          <p class="tx-12 text-muted m-0">
            <span class="spinner-border spinner-border-sm text-primary me-1" role="status" aria-hidden="true"></span>
            Buscando países...
          </p>
        </div>

        <div wire:loading.remove wire:target="busqueda">
          @if($paises && count($paises) > 0)
            @foreach($paises as $pais)
            <a href="javascript:;" wire:click="seleccionarPais({{ $pais->id }})" @click="abierto = false" class="dropdown-item d-flex align-items-center p-2 mb-1 border rounded">
              <div class="d-flex align-items-center justify-content-center rounded me-3 bg-primary p-2">
                <i class="ti ti-world text-white fs-4"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p class="fs-6 text-wrap m-0">{{ ucwords($pais->nombre) }}</p>
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

    @if($paisSeleccionado)
      <div class="col-12">
        <div class="dropdown-item w-100 m-0 d-flex p-1 border flex-grow-1">
          <div class="flex-fill d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center rounded me-3 bg-primary p-1">
              <i class="ti ti-world text-white fs-5"></i>
            </div>
            <div class="flex-grow-1 me-2">
              <p class="fs-6 text-wrap m-0 mt-1 fw-bold">{{ ucwords($paisSeleccionado->nombre) }}</p>
            </div>
          </div>

          <div class="d-flex align-items-start">
            <button type="button" wire:click="quitarSeleccion" @click="abierto = true; $nextTick(() => $refs.inputBusquedaPais?.focus())" class="align-self-start btn btn-danger btn-xs p-1">
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

    <input type="text" id="pais_id" name="{{ $nameId }}" value="{{ $paisSeleccionado ? $paisSeleccionado->id : '' }}" class="form-control d-none" placeholder="">
  @endif
</div>
