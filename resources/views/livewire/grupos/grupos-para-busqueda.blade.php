<div class="{{$class}}">
  @if($label)
  <label class="form-label">
    {{ $label }}

  </label>
  @endif
  <div wire:mouseenter="ocultarListaBusqueda()" class="{{ $verInputBusqueda ? '' : 'd-none'}}">
    <div class="input-group input-group-merge">
      <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
      <input wire:model.live.debounce.30ms="busqueda" wire:click="desplegarListaBusqueda" wire:keydown="resetCantidadGruposCargados" type="text" class="form-control"  placeholder="{{ $placeholder ? $placeholder : 'Buscar' }}" aria-label="Buscar grupo" aria-describedby="basic-addon-search31" spellcheck="false" data-ms-editor="true">

    </div>
  </div>

  @if($errors->has($id) && $obligatorio) <div class="text-danger form-label">{{ $errors->first($id) }}</div> @endif
  <div class="divListaBusquedaGrupos position-relative {{ $verListaBusqueda ? ''  : 'd-none' }}">
    <div id="listaItemsBusquedaGrupos" class="panel-busqueda p-2">
      @if($grupos && count($grupos) >0)
      @foreach($grupos as $grupo)
      <a href="{{ $redirect ? route($redirect, $grupo) : 'javascript:;'}}" @if($multiple) wire:click="seleccionarGrupos({{$grupo->id}})" @else wire:click="seleccionarGrupo({{$grupo->id}})" @endif class="dropdown-item d-flex align-items-center p-2 mb-1 border">
        <div class="d-flex align-items-center justify-content-center rounded me-3" style="background-color: {{$grupo->tipoGrupo->color ?? '#000000' }}">
          <i class="ti ti-users-group text-white" style="font-size: 3.0rem !important"></i>
        </div>
        <div class="flex-grow-1 me-2">
          <p class="fs-7 text-wrap m-0">{{ $grupo->nombre }}</p>
          <p class="fs-7 text-wrap fw-bold m-0">ID: {{ $grupo->id }} | {{ $grupo->tipoGrupo->nombre }}</p>
          @foreach ($grupo->encargados as $encargado)
          <p class="fs-7 text-wrap m-0 mb-1"> <span class="badge px-1" style="background-color: {{$encargado->tipoUsuario->color}}"><i class="fs-6 {{ $encargado->tipoUsuario->icono }}"></i></span> {{ $encargado->nombre(3) }}</p>
          @endforeach
        </div>
      </a>
      @endforeach

      @if($grupos->count() < $grupos->total())
        <div wire:loading class="text-center">
          <div class="spinner-border spinner-border-lg text-primary my-2" role="status">
            <span class="visually-hidden">Cargado...</span>
          </div>
        </div>
        @else
        <div class="text-center pt-3">
          <center>
            <p class="tx-12 text-muted"> <i class="ti ti-list-search fs-4"> </i> No hay más grupos.</p>
          </center>
        </div>
        @endif

        @else
        <div class="text-center pt-3">
          <center>
            <p class="tx-12 text-muted"> <i class="ti ti-list-search fs-4"> </i> La busqueda no arrojo ningun resultado.</p>
          </center>
        </div>
        @endif
    </div>
  </div>

  @if($tieneInformeDeVinculacion || $tieneInformeDeDesvinculacion)
  <input type="text" id="bitacora" name="bitacora" value="{{ json_encode($bitacoras) }}" class="form-control d-none" placeholder="Enter Name">
  @endif

  @if($multiple)
    <input type="text" id="{{$id}}" name="{{$id}}" value="{{ json_encode($gruposSeleccionadosIds) }}" class="form-control d-none" placeholder="Enter Name">

      @foreach($gruposSeleccionados as $grupoSel)
      <div wire:key="{{ $grupoSel->id }}-grupo" class="dropdown-item w-100 mx-0 d-flex p-2 mt-1 border flex-grow-1">
        <div class="flex-fill d-flex align-items-center">
          <div class="d-flex align-items-center justify-content-center rounded me-3" style="background-color: {{$grupoSel->tipoGrupo->color }}">
            <i class="ti ti-users-group text-white" style="font-size: 4.0rem !important"></i>
          </div>
          <div class="flex-grow-1 me-2">
            <p class="fs-5 text-wrap m-0">{{ $grupoSel->nombre }}</p>
            <p class="fs-6 text-wrap fw-bold m-0">ID: {{ $grupoSel->id }} | {{ $grupoSel->tipoGrupo->nombre }}</p>
            @foreach ($grupoSel->encargados as $encargado)
            <p class="fs-6 text-wrap m-0 mt-1"> <span class="badge px-1" style="background-color: {{$encargado->tipoUsuario->color}}"><i class="fs-6 {{ $encargado->tipoUsuario->icono }}"></i></span> {{ $encargado->nombre(3) }}</p>
            @endforeach
          </div>
        </div>

        <div class="d-flex align-items-start">
          @if($rolActivo->hasPermissionTo('grupos.opcion_desvincular_asistentes_grupos'))
          <button type="button" wire:click="quitarSeleccion({{ $grupoSel->id }})" class="align-self-start btn btn-danger btn-xs p-1"><i class="ti ti-x fs-6"></i></button>
          @endif
        </div>
      </div>
      @endforeach
  @else
    @if($grupoSeleccionado)
      <input type="text" id="{{$id}}" name="{{$id}}" value="{{$grupoSeleccionado->id}}" class="form-control d-none" placeholder="Enter Name">
      <div class="col-12">
        @if($estiloSeleccion)
          @if($estiloSeleccion == 'pequeno')
          <div class="dropdown-item w-100 m-0 d-flex p-1 mt-1 border flex-grow-1">
            <div class="flex-fill d-flex align-items-center">
              <div class="d-flex align-items-center justify-content-center rounded me-3" style="background-color: {{$grupoSeleccionado->tipoGrupo->color }}">
                <i class="ti ti-users-group text-white" style="font-size: 1.6rem !important"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p class="fs-6 text-wrap m-0 mt-1 fw-bold">{{ $grupoSeleccionado->nombre }}</p>
              </div>
            </div>

            <div class="d-flex align-items-start">
              @if($rolActivo->hasPermissionTo('grupos.opcion_desvincular_asistentes_grupos'))
              <button type="button" wire:click="quitarSeleccion({{ $grupoSeleccionado->id }})" class="align-self-start btn btn-danger btn-xs p-1"><i class="ti ti-x fs-6"></i></button>
              @endif
            </div>
          </div>
          @endif
        @else
        <div class="dropdown-item w-100 mx-0 d-flex p-2 mt-1 border  flex-grow-1">
          <div class="flex-fill d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center rounded me-3" style="background-color: {{$grupoSeleccionado->tipoGrupo->color }}">
              <i class="ti ti-users-group text-white" style="font-size: 4.0rem !important"></i>
            </div>
            <div class="flex-grow-1 me-2">
              <p class="fs-5 text-wrap m-0">{{ $grupoSeleccionado->nombre }}</p>
              <p class="fs-6 text-wrap fw-bold m-0">ID: {{ $grupoSeleccionado->id }} | {{ $grupoSeleccionado->tipoGrupo->nombre }}</p>
              @foreach ($grupoSeleccionado->encargados as $encargado)
              <p class="fs-6 text-wrap m-0 mt-1"> <span class="badge px-1" style="background-color: {{$encargado->tipoUsuario->color}}"><i class="fs-6 {{ $encargado->tipoUsuario->icono }}"></i></span> {{ $encargado->nombre(3) }}</p>
              @endforeach
            </div>
          </div>

          <div class="d-flex align-items-start">
            @if($rolActivo->hasPermissionTo('grupos.opcion_desvincular_asistentes_grupos'))
            <button type="button" wire:click="quitarSeleccion({{ $grupoSeleccionado->id }})" class="align-self-start btn btn-danger btn-xs p-1"><i class="ti ti-x fs-6"></i></button>
            @endif
          </div>
        </div>
        @endif
      </div>
    @endif
  @endif

</div>


@script
<script>
  // scroll infinito para listado de grupos
  $("#listaItemsBusquedaGrupos").on('scroll', function() {
    if (this.scrollHeight - this.scrollTop === this.clientHeight) {
      //$dispatch('cargarMas');
      Livewire.dispatch('cargarMas');
    }
  });

  Livewire.on('msn', () => {
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

  Livewire.on('cerrarModal', () => {
    $('#' + event.detail.nombreModal).modal('hide');
  });

  Livewire.on('abrirModal', () => {
    $('#' + event.detail.nombreModal).modal('show');
  });
</script>
@endscript
