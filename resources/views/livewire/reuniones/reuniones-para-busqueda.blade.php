<div class="{{ $mostrar ? $class : '' }}" wire:mouseover="desplegarListaBusqueda" wire:mouseout="ocultarListaBusqueda">
  @if ($mostrar)
  @if ($label)
  <span class="badge badge-dot bg-info me-1"></span>
  <label class="form-label">
    {{ $label }}
  </label>
  @endif
  <div class="{{ $verInputBusqueda ? '' : 'd-none' }}">
    <div class="input-group input-group-merge">
      <span class="input-group-text" id="basic-addon-search31">
        <i class="ti ti-search"></i>
      </span>
      <input wire:model.live.debounce.100ms="busqueda" type="text" class="form-control"
        placeholder="{{ $placeholder ? $placeholder : 'Buscar' }}" aria-label="Buscar "
        aria-describedby="basic-addon-search31" spellcheck="false" data-ms-editor="true">
    </div>
  </div>
  <div class="divListaBusquedaReunion position-relative {{ $verListaBusqueda ? '' : 'd-none' }}">
    <div id="listaItemsBusquedaReunion" class="panel-busqueda position-absolute p-2">
      @if ($reuniones && $reuniones->count() > 0)
      @foreach ($reuniones as $reunion)
      <a href="javascript:;" wire:click="seleccionarReunion({{ $reunion->id }}, true)"
        class="dropdown-item d-flex align-items-center mb-1 border p-2">
        <div class="d-flex align-items-center justify-content-center bg-primary me-3 rounded">
          <i class="ti ti-building-church text-white" style="font-size: 3.0rem !important"></i>
        </div>
        <div class="flex-grow-1 me-2">
          <p class="fs-7 m-0 text-wrap">{{ $reunion->nombre }}</p>
          <p class="fs-7 fw-bold m-0 text-wrap">Sede {{ $reunion->sede }}</p>
          <p class="fs-7 fw-bold m-0 text-wrap">Hora {{ Carbon\Carbon::parse($reunion->hora)->format('g:i a') }}</p>
          <p class="fs-7 fw-bold m-0 text-wrap">Día
            {{ Helper::obtenerDiaDeLaSemana($reunion->dia) ? Helper::obtenerDiaDeLaSemana($reunion->dia) : 'Día no indicado' }}
          </p>
        </div>
      </a>
      @endforeach
      @else
      <div class="pt-3 text-center">
        <center>
          <p class="tx-12 text-muted"> <i class="ti ti-list-search fs-4"> </i>
            {{ strlen($busqueda) < 3 ? $placeholder : 'La busqueda no arrojo ningun resultado.' }}
          </p>
        </center>
      </div>
      @endif
    </div>
  </div>

  @if ($reunionSeleccionada)
  <div class="col-12">
    <div class="dropdown-item w-100 d-flex flex-grow-1 m-0 border p-1">
      <div class="flex-fill d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-center bg-primary me-3 rounded">
          <i class="ti ti-building-church text-white" style="font-size: 1.6rem !important"></i>
        </div>
        <div class="flex-grow-1 me-2">
          <p class="fs-6 fw-bold m-0 mt-1 text-wrap">{{ $reunionSeleccionada->nombre }}</p>
        </div>
      </div>

      <div class="d-flex align-items-start">
        <button type="button" wire:click="quitarSeleccion"
          class="align-self-start btn btn-danger btn-xs p-1"><i class="ti ti-x fs-6"></i></button>
      </div>
    </div>
  </div>
  @endif

  @if ($errors->has($nameId))
  <div class="text-danger ti-12px mt-2">
    {{ $errors->first($nameId) }}
  </div>
  @endif

  <input type="text" id="{{ $nameId }}" name="{{ $nameId }}"
    value="{{ $reunionSeleccionada ? $reunionSeleccionada->id : '' }}" class="form-control d-none" placeholder="">

  @endif
</div>