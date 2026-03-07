<div class="row">
  <div class="col-12 mb-3 offset-md-3 col-md-6" wire:click.outside="ocultarListaBusqueda()">
    <div class="input-group">
      <input wire:model.live.throttle.150ms="busqueda" wire:click="desplegarListaBusqueda" type="text" value="" class="form-control" placeholder="Buscar" aria-label="Recipient's username" aria-describedby="button-addon2">
      <button class="btn btn-outline-primary px-2 px-md-3" type="submit" id="button-addon2"><i class="ti ti-search"></i></button>
    </div>
    <div class="divListaBusquedaGrupos position-relative {{ $verListaBusqueda ? ''  : 'd-none' }}">
      <div id="listaItemsBusquedaGrupos" class="panel-busqueda">
        {{$this->busqueda}}

        @json($resultadosBusqueda)
        @if($resultadosBusqueda && count($resultadosBusqueda) > 0)
        @foreach($resultadosBusqueda as $item)
        <a href="javascript:;" wire:click="verEnMapa({{ $item->lat }}, {{ $item->lon }}, '{{ $item->addresstype }}')" class="dropdown-item d-flex align-items-center p-2 border-bottom">
          <div class="d-flex align-items-center justify-content-center rounded me-3">
            <i class="ti ti-map-pin" style="font-size: 2.0rem !important"></i>
          </div>
          <div class="flex-grow-1 me-2">
            <p class="fs-7 text-wrap m-0">{{ $item->display_name }}</p>
          </div>
        </a>
        @endforeach
        @else
        <a href="javascript:;" class="dropdown-item d-flex align-items-center p-2 border-bottom">
          <div class="d-flex align-items-center justify-content-center rounded me-3">
            <i class="ti ti-search" style="font-size: 2.0rem !important"></i>
          </div>
          <div class="flex-grow-1 me-2">
            <p class="fs-7 text-wrap m-0">No hay resultados</p>
          </div>
        </a>
        @endif
      </div>
    </div>
  </div>


</div>
