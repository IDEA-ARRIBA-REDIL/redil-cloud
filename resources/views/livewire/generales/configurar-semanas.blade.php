<div>
  <h4 class="fw-semibold text-black mb-0">Configurar semanas</h4>
  <p class="text-black my-3 fs-6">Aquí podrás habilitar/deshabilitar las semanas, las cuales tendrás en cuenta a la hora de analizar los datos de los diferentes informes.</p>

    <div class="pt-2 pb-10 row">

      <div class="col-12  col-md-4 col-lg-3">
        <label for="añoSeleccionado" class="form-label d-none"><b>Selecciona un año</b></label>
        {{-- .live hace que se actualice en tiempo real mientras escribes --}}

        <div class="d-flex flex-row">
          <button wire:click="restarAño" type="button" class="btn btn-sm btn-icon rounded-pill btn-outline waves-effect my-auto me-1" style="border: solid 2px #1977E5 !important; color: #1977E5;">
            <i class="ti ti-minus"></i>
          </button>
          <input wire:model.live.debounce.500ms="añoSeleccionado" id="añoSeleccionado" type="number" class="form-control" placeholder="Ej: {{ date('Y') }}">
          <button wire:click="sumarAño" type="button" class="btn btn-sm btn-icon rounded-pill btn-outline waves-effect my-auto ms-1" style="border: solid 2px #1977E5 !important; color: #1977E5;">
            <i class="ti ti-plus"></i>
          </button>
        </div>
      </div>

      <div class="col-12  col-md-3 col-lg-2">
          <div class="form-check mt-1">
              <input wire:model.live="filtroHabilitadas" class="form-check-input" type="checkbox" id="filtroHabilitadasCheck">
              <label class="form-check-label" for="filtroHabilitadasCheck"> Habilitadas </label>
          </div>
      </div>
      <div class="col-12  col-md-3">
          <div class="form-check mt-1">
              <input wire:model.live="filtroDeshabilitadas" class="form-check-input" type="checkbox" id="filtroDeshabilitadasCheck">
              <label class="form-check-label" for="filtroDeshabilitadasCheck"> Deshabilitadas </label>
          </div>
      </div>

    </div>

    {{-- Mostramos un indicador de carga mientras se cambia de año --}}
    <div wire:loading wire:target="añoSeleccionado" class="col-12 text-center my-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p>Cargando semanas...</p>
    </div>

      <div class=" m-0 mx-4 mx-md-0 d-flex justify-content-between my-5">
        <span class="text-black fs-6">Semanas</span>
        <span class="text-black fs-6">¿Habilitada?</span>
      </div>

      @forelse($semanasFiltradas as $index => $semana)
      <div class="d-flex border-bottom py-2" wire:key="semana-{{ $index }}">

        <div class="flex-fill row g-2 g-md-3">

          <div class="col-12 col-md-3">
            <small class="text-black fw-semibold fs-6">Semana Nº {{ $semana['numeroSemana'] }}</small><br>

            <span class="badge rounded-pill fw-light {{ $semana['habilitada'] ? 'text-bg-success': 'text-bg-danger'  }}">
              <span class="text-white"> {{ $semana['habilitada'] ? 'Habilitada' : 'Deshabilitada' }}</span>
            </span>
          </div>

          <div class="col-12 col-md-6">
            <div class="d-flex flex-row">
              <i class="ti ti-calendar text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Fecha:</small>
                <small class="fw-semibold ms-1 text-black ">{{ $semana['fechaInicio'] }} al {{ $semana['fechaFin'] }}</small>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-3 text-end">
              <label class="switch switch-lg mx-auto">
                  <input
                      type="checkbox"
                      class="switch-input"
                      wire:model.live="semanas.{{ $index }}.habilitada"
                      wire:loading.attr="disabled"
                      wire:target="semanas.{{ $index }}.habilitada" />
                    <span class="switch-toggle-slider">
                      <span class="switch-off">NO</span>
                      <span class="switch-on">SI</span>
                    </span>
                  <span class="switch-label"></span>
              </label>
          </div>
        </div>

      </div>
      @empty
      <div class="col-12 py-10">
          <p class="text-center">No se encontraron semanas.</p>
      </div>
      @endforelse
</div>
