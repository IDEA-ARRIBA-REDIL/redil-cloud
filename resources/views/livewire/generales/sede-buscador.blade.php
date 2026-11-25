<div class="{{ $mostrar ? $class : '' }}" x-data="{ abierto: false }" @click.outside="abierto = false" @keydown.escape="abierto = false">
  @if($mostrar)
    @if($label)
    <label class="form-label" for="sede">
      {{ $label }}
    </label>
    @endif

    @php
      $placeholderActual = $estaDeshabilitado
        ? ($placeholderDeshabilitado ?: 'No hay sedes disponibles')
        : ($placeholder ?: 'Selecciona una sede');
    @endphp

    <div class="{{ $verInputBusqueda ? '' : 'd-none' }}">
      <div class="input-group input-group-merge">
        <span class="input-group-text" @if($estaDeshabilitado) style="background-color: #eceef1; border-color: #d9dee3; color: #a1acb8;" @endif>
          <i class="ti ti-building-church" wire:loading.remove wire:target="busqueda"></i>
          <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true" wire:loading wire:target="busqueda"></span>
        </span>
        <input
          x-ref="inputBusquedaSede"
          wire:model.live.debounce.250ms="busqueda"
          @focus="if (!{{ $estaDeshabilitado ? 'true' : 'false' }}) abierto = true"
          @click="if (!{{ $estaDeshabilitado ? 'true' : 'false' }}) abierto = true"
          @input="if (!{{ $estaDeshabilitado ? 'true' : 'false' }}) abierto = true"
          type="search"
          role="combobox"
          aria-autocomplete="list"
          class="form-control"
          @if($estaDeshabilitado) style="background-color: #eceef1; border-color: #d9dee3; padding-left: 0.75rem;" @endif
          placeholder="{{ $placeholderActual }}"
          @disabled($estaDeshabilitado)
          spellcheck="false"
          data-lpignore="true"
          data-1p-ignore="true"
          data-form-type="other"
          autocomplete="one-time-code">
      </div>
    </div>

    @if(! $estaDeshabilitado)
    <div class="divListaBusquedaUbicacion position-relative {{ $verInputBusqueda ? '' : 'd-none' }}" x-show="abierto" x-transition.opacity.duration.150ms style="display: none;">
      <div class="panel-busqueda p-2 position-absolute" style="height: auto; max-height: 260px; overflow-y: auto;">
        <div wire:loading wire:target="busqueda" class="text-center py-3">
          <p class="tx-12 text-muted m-0">
            <span class="spinner-border spinner-border-sm text-primary me-1" role="status" aria-hidden="true"></span>
            Buscando sedes...
          </p>
        </div>

        <div wire:loading.remove wire:target="busqueda">
          @if($sedes && count($sedes) > 0)
            @foreach($sedes as $sedeItem)
            <a href="javascript:;" wire:click="seleccionarSede({{ $sedeItem->id }})" @click="abierto = false" class="dropdown-item d-flex align-items-center p-2 mb-1 border rounded">
              <div class="d-flex align-items-center justify-content-center rounded me-3 bg-primary p-2">
                <i class="ti ti-building-church text-white fs-4"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p class="fs-7 text-wrap fw-bold m-0">{{ ucwords($sedeItem->nombre) }}</p>
                @if($sedeItem->direccion)
                  <p class="fs-7 text-wrap text-muted m-0">{{ $sedeItem->direccion }}</p>
                @endif
              </div>
            </a>
            @endforeach
          @else
          <div class="text-center pt-3">
            <p class="tx-12 text-muted">
              <i class="ti ti-list-search fs-4"></i>
              La búsqueda no arrojó ninguna sede.
            </p>
          </div>
          @endif
        </div>
      </div>
    </div>
    @endif

    @if($sedeSeleccionada)
      <div class="col-12">
        <div class="dropdown-item w-100 m-0 d-flex p-1 border flex-grow-1">
          <div class="flex-fill d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center rounded me-3 bg-primary p-1">
              <i class="ti ti-building-church text-white fs-5"></i>
            </div>
            <div class="flex-grow-1 me-2">
              <p class="fs-6 text-wrap m-0 mt-1 fw-bold">{{ ucwords($sedeSeleccionada->nombre) }}</p>
            </div>
          </div>

          <div class="d-flex align-items-start">
            <button type="button" wire:click="quitarSeleccion" @click="abierto = true; $nextTick(() => $refs.inputBusquedaSede?.focus())" class="align-self-start btn btn-danger btn-xs p-1">
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

    <input type="text" id="sede" name="{{ $nameId }}" value="{{ $sedeSeleccionada ? $sedeSeleccionada->id : '' }}" class="form-control d-none" placeholder="">
  @endif
</div>
