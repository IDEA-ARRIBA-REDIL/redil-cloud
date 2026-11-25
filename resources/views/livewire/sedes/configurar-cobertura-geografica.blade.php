<div>
  @include('layouts.status-msn')

  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <h5 class="mb-1 fw-semibold text-black">Regla general de visibilidad</h5>
          <p class="mb-0 text-muted small">
            Si está activado, esta sede aparecerá disponible para cualquier usuario sin importar su ubicación geográfica.
          </p>
        </div>
        <label class="switch switch-lg">
          <input type="checkbox" wire:model.live="apareceEnTodosLosCasos" class="switch-input" />
          <span class="switch-toggle-slider">
            <span class="switch-on">SÍ</span>
            <span class="switch-off">NO</span>
          </span>
          <span class="switch-label fw-semibold">¿Esta sede aparece en todos los casos?</span>
        </label>
      </div>
    </div>
  </div>

  @if (! $apareceEnTodosLosCasos)
    {{-- Nivel 1: Países --}}
    <div class="card mb-4">
      <div class="card-header border-bottom py-3">
        <h5 class="mb-0 fw-semibold text-black">
          <i class="ti ti-world me-1 text-primary"></i> 1. Condición por País
        </h5>
      </div>
      <div class="card-body pt-4">
        <div class="row g-3 mb-3">
          <div class="col-12 col-md-6">
            <div class="form-check custom-option custom-option-basic {{ $modoPaises === 'incluir' ? 'checked' : '' }}">
              <label class="form-check-label custom-option-content" for="modoPaisesIncluir">
                <input wire:model.live="modoPaises" class="form-check-input" type="radio" value="incluir" id="modoPaisesIncluir" />
                <span class="custom-option-header">
                  <span class="h6 mb-0">Solo en los países seleccionados</span>
                </span>
                <span class="custom-option-body">
                  <small class="text-muted">Permite además restringir por ciudades o localidades dentro de esos países.</small>
                </span>
              </label>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="form-check custom-option custom-option-basic {{ $modoPaises === 'excluir' ? 'checked' : '' }}">
              <label class="form-check-label custom-option-content" for="modoPaisesExcluir">
                <input wire:model.live="modoPaises" class="form-check-input" type="radio" value="excluir" id="modoPaisesExcluir" />
                <span class="custom-option-header">
                  <span class="h6 mb-0">En todos los países excepto los seleccionados</span>
                </span>
                <span class="custom-option-body">
                  <small class="text-muted">Ideal para sedes internacionales o virtuales fuera de ciertos países.</small>
                </span>
              </label>
            </div>
          </div>
        </div>

        <label class="form-label">
          {{ $modoPaises === 'incluir' ? 'Países incluidos' : 'Países excluidos' }}
        </label>
        <div class="input-group input-group-merge mb-2">
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          <input
            type="text"
            wire:model.live.debounce.250ms="busquedaPais"
            class="form-control"
            placeholder="Escribe al menos 2 caracteres para buscar y agregar un país..."
            autocomplete="off" />
        </div>

        @if ($resultadosPaises->isNotEmpty())
          <div class="border rounded p-2 mb-3 bg-white shadow-sm" style="max-height: 220px; overflow-y: auto;">
            @foreach ($resultadosPaises as $pais)
              <button
                type="button"
                wire:click="agregarPais({{ $pais->id }})"
                class="dropdown-item d-flex align-items-center justify-content-between p-2 mb-1 border rounded">
                <span>{{ ucwords($pais->nombre) }}</span>
                <span class="badge bg-label-primary"><i class="ti ti-plus ti-xs"></i> Agregar</span>
              </button>
            @endforeach
          </div>
        @endif

        <div class="d-flex flex-wrap gap-2 mt-2">
          @forelse ($this->paisesSeleccionadosItems as $paisItem)
            <span class="badge bg-label-primary d-inline-flex align-items-center gap-2 py-2 px-3 fs-6">
              {{ ucwords($paisItem->nombre) }}
              <button type="button" wire:click="quitarPais({{ $paisItem->id }})" class="btn-close btn-close-sm" aria-label="Quitar"></button>
            </span>
          @empty
            <span class="text-muted small">No hay países seleccionados aún.</span>
          @endforelse
        </div>

        @error('paisesSeleccionados')
          <div class="text-danger small mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>
    </div>

    {{-- Nivel 2: Ciudades / Municipios --}}
    @if ($modoPaises === 'incluir' && count($paisesSeleccionados) > 0)
      <div class="card mb-4">
        <div class="card-header border-bottom py-3">
          <h5 class="mb-0 fw-semibold text-black">
            <i class="ti ti-building-community me-1 text-primary"></i> 2. Condición por Ciudad / Municipio
          </h5>
        </div>
        <div class="card-body pt-4">
          @if (! $this->paisesTienenMunicipios)
            <div class="alert alert-info mb-0">
              <i class="ti ti-info-circle me-1"></i>
              Los países seleccionados no tienen ciudades registradas en la base de datos. Esta sede aplicará directamente al seleccionar el país.
            </div>
          @else
            <div class="row g-3 mb-3">
              <div class="col-12 col-md-4">
                <div class="form-check custom-option custom-option-basic {{ $modoMunicipios === 'ninguno' ? 'checked' : '' }}">
                  <label class="form-check-label custom-option-content" for="modoMunicipiosNinguno">
                    <input wire:model.live="modoMunicipios" class="form-check-input" type="radio" value="ninguno" id="modoMunicipiosNinguno" />
                    <span class="custom-option-header"><span class="h6 mb-0">No exigir ciudad</span></span>
                    <span class="custom-option-body"><small class="text-muted">Aparece cuando solo se ha seleccionado el país.</small></span>
                  </label>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="form-check custom-option custom-option-basic {{ $modoMunicipios === 'incluir' ? 'checked' : '' }}">
                  <label class="form-check-label custom-option-content" for="modoMunicipiosIncluir">
                    <input wire:model.live="modoMunicipios" class="form-check-input" type="radio" value="incluir" id="modoMunicipiosIncluir" />
                    <span class="custom-option-header"><span class="h6 mb-0">Solo en las ciudades seleccionadas</span></span>
                    <span class="custom-option-body"><small class="text-muted">Ej. Medellín, Caracas, Bogotá D.C.</small></span>
                  </label>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="form-check custom-option custom-option-basic {{ $modoMunicipios === 'excluir' ? 'checked' : '' }}">
                  <label class="form-check-label custom-option-content" for="modoMunicipiosExcluir">
                    <input wire:model.live="modoMunicipios" class="form-check-input" type="radio" value="excluir" id="modoMunicipiosExcluir" />
                    <span class="custom-option-header"><span class="h6 mb-0">Todas las ciudades excepto las seleccionadas</span></span>
                    <span class="custom-option-body"><small class="text-muted">Ideal para cobertura nacional donde no hay sede física.</small></span>
                  </label>
                </div>
              </div>
            </div>

            @if ($modoMunicipios !== 'ninguno')
              <label class="form-label">
                {{ $modoMunicipios === 'incluir' ? 'Ciudades incluidas' : 'Ciudades excluidas' }}
              </label>
              <div class="input-group input-group-merge mb-2">
                <span class="input-group-text"><i class="ti ti-search"></i></span>
                <input
                  type="text"
                  wire:model.live.debounce.250ms="busquedaMunicipio"
                  class="form-control"
                  placeholder="Escribe al menos 2 caracteres para buscar y agregar una ciudad..."
                  autocomplete="off" />
              </div>

              @if ($resultadosMunicipios->isNotEmpty())
                <div class="border rounded p-2 mb-3 bg-white shadow-sm" style="max-height: 220px; overflow-y: auto;">
                  @foreach ($resultadosMunicipios as $municipio)
                    <button
                      type="button"
                      wire:click="agregarMunicipio({{ $municipio->id }})"
                      class="dropdown-item d-flex align-items-center justify-content-between p-2 mb-1 border rounded">
                      <span>{{ $municipio->nombre }}</span>
                      <span class="badge bg-label-primary"><i class="ti ti-plus ti-xs"></i> Agregar</span>
                    </button>
                  @endforeach
                </div>
              @endif

              <div class="d-flex flex-wrap gap-2 mt-2">
                @forelse ($this->municipiosSeleccionadosItems as $munItem)
                  <span class="badge bg-label-info d-inline-flex align-items-center gap-2 py-2 px-3 fs-6">
                    {{ $munItem->nombre }}
                    <button type="button" wire:click="quitarMunicipio({{ $munItem->id }})" class="btn-close btn-close-sm" aria-label="Quitar"></button>
                  </span>
                @empty
                  <span class="text-muted small">No hay ciudades seleccionadas aún.</span>
                @endforelse
              </div>

              @error('municipiosSeleccionados')
                <div class="text-danger small mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            @endif
          @endif
        </div>
      </div>
    @endif

    {{-- Nivel 3: Localidades --}}
    @if ($modoPaises === 'incluir' && $modoMunicipios === 'incluir' && count($municipiosSeleccionados) > 0 && $this->municipiosTienenLocalidades)
      <div class="card mb-4">
        <div class="card-header border-bottom py-3">
          <h5 class="mb-0 fw-semibold text-black">
            <i class="ti ti-map-pin me-1 text-primary"></i> 3. Condición por Localidad
          </h5>
        </div>
        <div class="card-body pt-4">
          <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
              <div class="form-check custom-option custom-option-basic {{ $modoLocalidades === 'ninguno' ? 'checked' : '' }}">
                <label class="form-check-label custom-option-content" for="modoLocalidadesNinguno">
                  <input wire:model.live="modoLocalidades" class="form-check-input" type="radio" value="ninguno" id="modoLocalidadesNinguno" />
                  <span class="custom-option-header"><span class="h6 mb-0">No exigir localidad</span></span>
                  <span class="custom-option-body"><small class="text-muted">Aplica para toda la ciudad seleccionada.</small></span>
                </label>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="form-check custom-option custom-option-basic {{ $modoLocalidades === 'incluir' ? 'checked' : '' }}">
                <label class="form-check-label custom-option-content" for="modoLocalidadesIncluir">
                  <input wire:model.live="modoLocalidades" class="form-check-input" type="radio" value="incluir" id="modoLocalidadesIncluir" />
                  <span class="custom-option-header"><span class="h6 mb-0">Solo en las localidades seleccionadas</span></span>
                  <span class="custom-option-body"><small class="text-muted">Ej. Fontibón, Soacha, Suba.</small></span>
                </label>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="form-check custom-option custom-option-basic {{ $modoLocalidades === 'excluir' ? 'checked' : '' }}">
                <label class="form-check-label custom-option-content" for="modoLocalidadesExcluir">
                  <input wire:model.live="modoLocalidades" class="form-check-input" type="radio" value="excluir" id="modoLocalidadesExcluir" />
                  <span class="custom-option-header"><span class="h6 mb-0">Todas las localidades excepto las seleccionadas</span></span>
                  <span class="custom-option-body"><small class="text-muted">Cubre el resto de localidades de la ciudad.</small></span>
                </label>
              </div>
            </div>
          </div>

          @if ($modoLocalidades !== 'ninguno')
            <label class="form-label">
              {{ $modoLocalidades === 'incluir' ? 'Localidades incluidas' : 'Localidades excluidas' }}
            </label>
            <div class="input-group input-group-merge mb-2">
              <span class="input-group-text"><i class="ti ti-search"></i></span>
              <input
                type="text"
                wire:model.live.debounce.250ms="busquedaLocalidad"
                class="form-control"
                placeholder="Escribe al menos 2 caracteres para buscar y agregar una localidad..."
                autocomplete="off" />
            </div>

            @if ($resultadosLocalidades->isNotEmpty())
              <div class="border rounded p-2 mb-3 bg-white shadow-sm" style="max-height: 220px; overflow-y: auto;">
                @foreach ($resultadosLocalidades as $localidad)
                  <button
                    type="button"
                    wire:click="agregarLocalidad({{ $localidad->id }})"
                    class="dropdown-item d-flex align-items-center justify-content-between p-2 mb-1 border rounded">
                    <span>{{ ucwords($localidad->nombre) }} @if($localidad->municipio)<small class="text-muted">({{ $localidad->municipio->nombre }})</small>@endif</span>
                    <span class="badge bg-label-primary"><i class="ti ti-plus ti-xs"></i> Agregar</span>
                  </button>
                @endforeach
              </div>
            @endif

            <div class="d-flex flex-wrap gap-2 mt-2">
              @forelse ($this->localidadesSeleccionadasItems as $locItem)
                <span class="badge bg-label-success d-inline-flex align-items-center gap-2 py-2 px-3 fs-6">
                  {{ ucwords($locItem->nombre) }}
                  @if ($locItem->municipio)
                    <small>({{ $locItem->municipio->nombre }})</small>
                  @endif
                  <button type="button" wire:click="quitarLocalidad({{ $locItem->id }})" class="btn-close btn-close-sm" aria-label="Quitar"></button>
                </span>
              @empty
                <span class="text-muted small">No hay localidades seleccionadas aún.</span>
              @endforelse
            </div>

            @error('localidadesSeleccionadas')
              <div class="text-danger small mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
            @enderror
          @endif
        </div>
      </div>
    @endif
  @endif

  <div class="d-flex justify-content-end gap-2">
    <a href="{{ route('sede.lista') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
    <button type="button" wire:click="guardar" wire:loading.attr="disabled" class="btn btn-primary rounded-pill px-4">
      <span wire:loading.remove wire:target="guardar"><i class="ti ti-device-floppy me-1"></i> Guardar cobertura</span>
      <span wire:loading wire:target="guardar"><span class="spinner-border spinner-border-sm me-1"></span> Guardando...</span>
    </button>
  </div>
</div>
