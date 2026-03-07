<div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2">
  <form id="formulario" role="form" class="forms-sample" wire:submit.prevent="submitFormulario" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <div class="row mt-10">
      <h4 class="fw-semibold text-black mb-0">{{ $tipoGrupo->titulo1_finalizar_reporte }}</h4>
      <p class="text-black my-3 fs-6">{{ $tipoGrupo->descripcion1_finalizar_reporte }}</p>

      <div class="row mt-3">
        <h5 class="text-black fw-semibold mb-2"> {{ $tipoGrupo->subtitulo_encargados_finalizar_reporte }} </h5>
        <div class="col-12">
          <div class="text-black small fw-medium mb-1">¿Asististe al grupo?</div>

          @if($reporte->informacion_encargado_grupo && count($reporte->informacion_encargado_grupo) > 1)
            @foreach ($reporte->informacion_encargado_grupo as $encargado)
            <div class="mt-2">
              <small class="text-black fw-semibold"> {{$encargado['nombre'] ?? 'No indicado'}} </small><br>
              <label class="switch switch-md mx-auto text-black">

                <input
                  id="encargado-asistio-{{ $encargado['id'] }}"
                  name="encargado-asistio-{{ $encargado['id'] }}"
                  placeholder=""
                  type="checkbox"
                  class="switch-input"
                  wire:model="togglesEncargadosAsistencia.{{ $encargado['id'] }}" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
                <span class="switch-label"></span>
              </label>

              @if($tipoGrupo->ingresos_individuales_lideres)
                @if($reporte->aprobado === null)
                <button type="button" wire:click="abrirModalOfrendaEspecifica({{ $encargado['id'] }})" class="btn btn-xs rounded-pill btn-outline-success waves-effect mt-1">
                  $ {{ number_format(array_sum(array_column($ofrendasPorPersona[$encargado['id']] ?? [], 'valor')), 2) }}
                  {{ $moneda->nombre_corto }}
                  <i class="ms-3 ti ti-edit"></i>
                </button>
                @else
                <small class="fw-semibold text-black "> $ {{ number_format(array_sum(array_column($ofrendasPorPersona[$encargado['id']] ?? [], 'valor')), 2) }} {{ $moneda->nombre_corto }}</small>
                @endif

              @endif
            </div>
            @endforeach
          @else
            @foreach ($reporte->informacion_encargado_grupo as $encargado)
            <div class="mt-2">
              <label class="switch switch-md mx-auto text-black">

                <input
                  id="encargado-asistio-{{ $encargado['id'] }}"
                  name="encargado-asistio-{{ $encargado['id'] }}"
                  placeholder=""
                  type="checkbox"
                  class="switch-input"
                  wire:model="togglesEncargadosAsistencia.{{ $encargado['id'] }}" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
                <span class="switch-label"></span>
              </label>

              @if($tipoGrupo->ingresos_individuales_lideres)
                @if($reporte->aprobado === null)
                <button type="button" wire:click="abrirModalOfrendaEspecifica({{ $encargado['id'] }})" class="btn btn-xs rounded-pill btn-outline-success waves-effect mt-1">
                  $ {{ number_format(array_sum(array_column($ofrendasPorPersona[$encargado['id']] ?? [], 'valor')), 2) }}
                  {{ $moneda->nombre_corto }}
                  <i class="ms-3 ti ti-edit"></i>
                </button>
                @else
                <small class="fw-semibold text-black "> $ {{ number_format(array_sum(array_column($ofrendasPorPersona[$encargado['id']] ?? [], 'valor')), 2) }} {{ $moneda->nombre_corto }}</small>
                @endif

              @endif
            </div>
            @endforeach

          @endif

        </div>
      </div>

      <div class="row">
        <hr class="mx-3 my-5 border-2">
      </div>

      @if($sumatorias)
      <div class="row">
        <h5 class="text-black fw-semibold mb-2"> {{ $tipoGrupo->subtitulo_sumatorias_adiccionales_finalizar_reporte }} </h5>
        <div class="col-6 mb-6">
          <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
            <label class="form-check-label custom-option-content p-3">
              <span class="custom-option-header m-0 pb-0">
                <span class="h6 mb-0 d-flex align-items-center">Si</span>
                <input name="tieneClasificacion" wire:model.live="tieneClasificacion" class="form-check-input" type="radio" value="si" id="ConClasificacion" checked />
              </span>
            </label>
          </div>
        </div>

        <div class="col-6">
          <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
            <label class="form-check-label custom-option-content p-3">
              <span class="custom-option-header m-0 pb-0">
                <span class="h6 mb-0 d-flex align-items-center">No</span>
                <input name="tieneClasificacion" wire:model.live="tieneClasificacion" class="form-check-input" type="radio" value="no" id="sinClasificacion" />
              </span>
            </label>
          </div>
        </div>
      </div>

      <div id="divClacificaciones" class="row mt-0 {{ $verDivClacificacion ? '' : 'd-none' }}">
        @foreach ($sumatorias as $sumatoria)
        <div class="mt-3 mb-3 d-flex flex-column col-6 col-md-4 col-lg-3">
          <label style="font-size: .8125rem!important" class="text-black mb-4 fs-6 lh-sm">{{ $sumatoria->nombre }}</label>
          <div>
            <button wire:click="restarClasificacion({{ $sumatoria->id }})" type="button" class="btn btn-xs btn-icon rounded-pill btn-outline waves-effect my-auto" style="border: solid 2px #1977E5 !important; color: #1977E5;"><i class="ti ti-minus"></i></button>
            <span class="rounded border border-2 fs-6 p-2 text-black mx-2">
              {{ $sumatoria->cantidad }}
              <input
                id="sumatoria-adiccional-{{$sumatoria->id}}"
                name="sumatoria-adiccional-{{$sumatoria->id}}"
                value="{{ $sumatoria->cantidad }}"
                class="d-none"
                wire:model="inputsSumatoriasAdiccionales.{{ $sumatoria->id }}.valor">
            </span>
            <button wire:click="sumarClasificacion({{ $sumatoria->id }})" type="button" class="btn btn-xs btn-icon rounded-pill btn-outline waves-effect my-auto" style="border: solid 2px #1977E5 !important; color: #1977E5;"><i class="ti ti-plus"></i></button>
          </div>
        </div>
        @endforeach
      </div>

      <div class="row">
        <hr class="mx-3 my-5 border-2">
      </div>
      @endif

      <div id="listaDePersonas" class="row m-0 p-0">
        <h5 class="fw-semibold">{{ $tipoGrupo->subtitulo_miebros_finalizar_reporte }}</h5>

        <div class="col-12">
          <div class="input-group input-group-merge bg-white">
            <input id="buscar" name="buscar" type="text" wire:model.live="search" value="" class="form-control" placeholder="Buscar por nombre, email o identificación" aria-describedby="btnBusqueda">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
          </div>
        </div>


        @if ($errors->any())
        <div class="col-12 mt-3" x-data x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'center' })">
          <div class="alert alert-danger">
              ¡Ups! revisa bien la información, al parecer faltan campos por llenar.
          </div>
        </div>
        @endif

        <div class="col-12">
          <div class="p-0 m-0 mx-4 mx-md-0 d-flex justify-content-between my-5">
            <span class="text-black fs-6">Personas</span>
            <span class="text-black fs-6">¿Asistio?</span>
          </div>

          <ul class="p-0 m-0 mx-4 mx-md-0" style="overflow-y: auto; max-height: 500px;">
            @if($personasFiltradas)
              @foreach ($personasFiltradas as $persona )
              <li wire:key="persona-item-{{ $persona->id }}" class=" mb-4 border-bottom pb-3">

                <div class="d-flex">

                  <div class=" flex-grow-1">
                    <div class="me-2 my-auto d-flex flex-column px-2">
                      <div class="row g-2">
                        <div class="col-12 col-md-4 d-flex flex-row">
                          <div class="avatar me-4">
                            @if($persona->foto == "default-m.png" || $persona->foto == "default-f.png")
                            <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $persona->iniciales_nombre }} </span>
                            @else
                            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) }}" alt="{{ $persona->foto }}" alt="avatar" class="rounded-circle">
                            @endif
                          </div>
                          <div class="">
                            <p class="mb-0 text-heading text-black fw-semibold">{{ $persona->nombre}}</p>
                            @if($tipoGrupo->ingresos_individuales_discipulos)
                              @if($reporte->aprobado === null)
                              <button type="button" wire:click="abrirModalOfrendaEspecifica({{ $persona->id }})" class="btn btn-xs rounded-pill btn-outline-success waves-effect mt-1">
                                $ {{ number_format(array_sum(array_column($ofrendasPorPersona[$persona->id] ?? [], 'valor')), 2) }}
                                {{ $moneda->nombre_corto }}
                                <i class="ms-3 ti ti-edit"></i>
                              </button>
                              @else
                              <small class="fw-semibold text-black "> $ {{ number_format(array_sum(array_column($ofrendasPorPersona[$persona->id] ?? [], 'valor')), 2) }} {{ $moneda->nombre_corto }}</small>
                              @endif

                            @endif
                          </div>
                        </div>

                        @if( $togglesAsistencia[$persona->id] === false && $tipoGrupo->registrar_inasistencia)
                        <div class="col-12 col-md-8">
                          <select
                            wire:model.live="selectInasistencias.{{ $persona->id }}"
                            class="form-select form-select-sm rounded-pill">
                            <option>Motivo inasistencia</option>
                            @foreach ($tipoInasistencias as $tipo)
                            <option value="{{ $tipo->id }}" @if(isset($selectInasistencias[$persona->id]) && $selectInasistencias[$persona->id] == $tipo->id) selected @endif>{{ $tipo->nombre }}</option>
                            @endforeach
                          </select>
                          {{-- Muestra el mensaje de error para este campo específico --}}
                          @error('selectInasistencias.' . $persona->id)
                          <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>
                            {{ $message }}
                          </div>
                          @enderror

                          {{-- Input de Observación Condicional --}}
                          @php
                          // Determinar si se debe mostrar el campo de observación para esta persona
                          $selectedInasistenciaId = $selectInasistencias[$persona->id] ?? null;
                          $showObservationInput = $selectedInasistenciaId && isset($tiposInasistenciaConObservacionObligatoria[$selectedInasistenciaId]);
                          @endphp

                          @if($showObservationInput)
                          <input type="text"
                            wire:model="observacionesInasistencia.{{ $persona->id }}"
                            class="rounded-pill mt-2 form-control form-control-sm @error('observacionesInasistencia.' . $persona->id) is-invalid @enderror"
                            placeholder="Ingrese observación detallada">
                          @error('observacionesInasistencia.' . $persona->id)
                          <div class="invalid-feedback d-block">{{ $message }}</div>
                          @enderror
                          @endif
                        </div>

                        @endif
                      </div>
                    </div>
                  </div>

                  <div class="">
                    <label class=" switch switch-lg mx-auto">
                      <input
                        id="asistio-{{ $persona->id }}"
                        name="asistio-{{ $persona->id }}"
                        type="checkbox"
                        class="switch-input"
                        wire:model.live.debounce.300ms="togglesAsistencia.{{ $persona->id }}"
                        wire:loading.attr="disabled" {{-- Deshabilita el input durante la carga --}}
                        wire:target="togglesAsistencia.{{ $persona->id }}" {{-- Aplica solo cuando este modelo específico cambia --}} />
                      <span class="switch-toggle-slider">
                        <span class="switch-on">SI</span>
                        <span class="switch-off">NO</span>
                      </span>
                      <span class="switch-label"></span>
                    </label>
                  </div>
                </div>
              </li>
              @endforeach
            @else
              @foreach ($personas as $persona )
              <li wire:key="persona-item-{{ $persona->id }}" class=" mb-4 border-bottom pb-3">

                <div class="d-flex">

                  <div class=" flex-grow-1">
                    <div class="me-2 my-auto d-flex flex-column px-2">
                      <div class="row g-2">
                        <div class="col-12 col-md-4 d-flex flex-row">
                          <div class="avatar me-4">
                            @if($persona->foto == "default-m.png" || $persona->foto == "default-f.png")
                            <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $persona->iniciales_nombre }} </span>
                            @else
                            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) }}" alt="{{ $persona->foto }}" alt="avatar" class="rounded-circle">
                            @endif
                          </div>
                          <div class="">
                            <p class="mb-0 text-heading text-black fw-semibold">{{ $persona->nombre}}</p>
                            @if($tipoGrupo->ingresos_individuales_discipulos)
                              @if($reporte->aprobado === null)
                              <button type="button" wire:click="abrirModalOfrendaEspecifica({{ $persona->id }})" class="btn btn-xs rounded-pill btn-outline-success waves-effect mt-1">
                                $ {{ number_format(array_sum(array_column($ofrendasPorPersona[$persona->id] ?? [], 'valor')), 2) }}
                                {{ $moneda->nombre_corto }}
                                <i class="ms-3 ti ti-edit"></i>
                              </button>
                              @else
                              <small class="fw-semibold text-black "> $ {{ number_format(array_sum(array_column($ofrendasPorPersona[$persona->id] ?? [], 'valor')), 2) }} {{ $moneda->nombre_corto }}</small>
                              @endif

                            @endif
                          </div>
                        </div>

                        @if( $togglesAsistencia[$persona->id] === false && $tipoGrupo->registrar_inasistencia)
                        <div class="col-12 col-md-8">
                          <select
                            wire:model.live="selectInasistencias.{{ $persona->id }}"
                            class="form-select form-select-sm rounded-pill">
                            <option>Motivo inasistencia</option>
                            @foreach ($tipoInasistencias as $tipo)
                            <option value="{{ $tipo->id }}" @if(isset($selectInasistencias[$persona->id]) && $selectInasistencias[$persona->id] == $tipo->id) selected @endif>{{ $tipo->nombre }}</option>
                            @endforeach
                          </select>
                          {{-- Muestra el mensaje de error para este campo específico --}}
                          @error('selectInasistencias.' . $persona->id)
                          <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>
                            {{ $message }}
                          </div>
                          @enderror

                          {{-- Input de Observación Condicional --}}
                          @php
                          // Determinar si se debe mostrar el campo de observación para esta persona
                          $selectedInasistenciaId = $selectInasistencias[$persona->id] ?? null;
                          $showObservationInput = $selectedInasistenciaId && isset($tiposInasistenciaConObservacionObligatoria[$selectedInasistenciaId]);
                          @endphp

                          @if($showObservationInput)
                          <input type="text"
                            wire:model="observacionesInasistencia.{{ $persona->id }}"
                            class="rounded-pill mt-2 form-control form-control-sm @error('observacionesInasistencia.' . $persona->id) is-invalid @enderror"
                            placeholder="Ingrese observación detallada">
                          @error('observacionesInasistencia.' . $persona->id)
                          <div class="invalid-feedback d-block">{{ $message }}</div>
                          @enderror
                          @endif
                        </div>

                        @endif
                      </div>
                    </div>
                  </div>

                  <div class="">
                    <label class=" switch switch-lg mx-auto">
                      <input
                        id="asistio-{{ $persona->id }}"
                        name="asistio-{{ $persona->id }}"
                        type="checkbox"
                        class="switch-input"
                        wire:model.live.debounce.300ms="togglesAsistencia.{{ $persona->id }}"
                        wire:loading.attr="disabled" {{-- Deshabilita el input durante la carga --}}
                        wire:target="togglesAsistencia.{{ $persona->id }}" {{-- Aplica solo cuando este modelo específico cambia --}} />
                      <span class="switch-toggle-slider">
                        <span class="switch-on">SI</span>
                        <span class="switch-off">NO</span>
                      </span>
                      <span class="switch-label"></span>
                    </label>
                  </div>
                </div>
              </li>
              @endforeach
            @endif
          </ul>
          </ul>
        </div>
      </div>

      <div class="row">
        <hr class="mx-3 my-5 border-2">
      </div>

      @if($ofrendasGenerica->count() > 0)
      <div class="row mb-10">
        <h5 class="text-black fw-semibold mb-2"> {{ $tipoGrupo->subtitulo_ofrendas_finalizar_reporte }} </h5>
        <p class="text-black mb-5 fs-6">{{ $tipoGrupo->descripcion_ofrendas_finalizar_reporte }}</p>

        <!-- ofrendas genericas -->
        @foreach ($ofrendasGenerica as $ofrendaGenerica)
        <div class="col-12 col-md-6 mb-6">
          <label class="form-label text-black" for="ofrenda1">
            {{ $ofrendaGenerica->nombre }}
          </label>

          @if($reporte->aprobado === null)
            <input
            id="ofrendaGenererica-{{ $ofrendaGenerica->tipo_ofrenda_id }}"
            type="number" min="0" step="0.01"
            class="form-control"
            name="{{ $ofrendaGenerica->nombre }}"
            value="{{ old('ofrendaGenererica-'.$ofrendaGenerica->tipo_ofrenda_id, $ofrendaGenerica->valor) }}"
            wire:model="inputsOfrendasGenerica.{{ $ofrendaGenerica->tipo_ofrenda_id }}.valor"
            {{ $ofrendaGenerica->ofrenda_obligatoria ? 'required' : '' }} />
            @if($errors->has('inputsOfrendasGenerica.'.$ofrendaGenerica->tipo_ofrenda_id.'.valor'))
            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $errors->first('inputsOfrendasGenerica.'.$ofrendaGenerica->tipo_ofrenda_id.'.valor') }}</div>
            @endif
          @else
          <br>
          <small class="fw-semibold text-black ">$ {{ $ofrendaGenerica->valor ? $ofrendaGenerica->valor : 0.00 }} {{ $moneda->nombre_corto }}</small>
          @endif


        </div>
        @endforeach
        <!-- ofrendas genericas -->
      </div>
      @endif

      <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top mt-10" style="background-color: #f8f7fa">
        <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex justify-content-sm-between">
          <a href="{{ route('grupo.lista') }}" type="button" class="btn btn-label-secondary rounded-pill btn-outline-secondary px-7 py-2 prev-step">
            <span class="align-middle">Volver</span>
          </a>
          <button type="submit" class="btn btnGuardart btn-primary rounded-pill px-7 py-2">
            <span class="align-middle me-sm-1 me-0 ">Continuar</span>
          </button>
        </div>
      </div>


    </div>
  </form>

  <!-- modal ofrendas especificas  -->
  <div wire:ignore.self class="modal fade" id="modalOfrendaEspecifica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-simple">
      <div class="modal-content p-0">
        <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
          <p class="text-black fw-semibold mb-0">Ofrendas de {{ $dador ? $dador->nombre(3) : '' }} </p>
          <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x ti-sm"></i></button>
        </div>

        <div class="modal-body px-5 py-8">

          <div class="row">
            <!-- ofrendas genericas -->
            @foreach ($ofrendasTemporalEspecificasUsuario as $ofrendaEspecifica)
            <div class="col-12 col-md-6 mb-6">
              <label class="form-label text-black" for="ofrenda1">
                {{ $ofrendaEspecifica->nombre }}
              </label>

              @if($reporte->aprobado === null)
              <input
                id="ofrendaGenererica-{{ $ofrendaEspecifica->tipo_ofrenda_id }}"
                type="number" min="0" step="0.01"
                class="form-control"
                name="{{ $ofrendaEspecifica->nombre }}"
                value="{{ old('ofrendaGenererica-'.$ofrendaEspecifica->tipo_ofrenda_id, $ofrendaEspecifica->valor) }}"
                wire:model="inputsTemporalOfrendasEspecificasUsuario.{{ $ofrendaEspecifica->tipo_ofrenda_id }}.valor" />
              @if($errors->has('inputsTemporalOfrendasEspecificasUsuario.'.$ofrendaEspecifica->tipo_ofrenda_id.'.valor'))
              <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $errors->first('inputsTemporalOfrendasEspecificasUsuario.'.$ofrendaEspecifica->tipo_ofrenda_id.'.valor') }}</div>
              @endif
              @else
              <br>
              <small class="fw-semibold text-black ">$ {{ $ofrendaEspecifica->valor ? $ofrendaEspecifica->valor : 0.00 }} {{ $moneda->nombre_corto }}</small>
              @endif


            </div>
            @endforeach
            <!-- ofrendas genericas -->
          </div>

        </div>

        <div class="modal-footer border-top p-5">
          <button type="button" data-bs-dismiss="modal" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>

          <button
            type="button"
            wire:click="guardarOfrendaTemporal"
            wire:loading.attr="disabled"
            class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">
            <span wire:loading.remove wire:target="guardarOfrendaTemporal">Confirmar</span>
            <span wire:loading wire:target="guardarOfrendaTemporal">Guardando...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>



@script
<script>
  $wire.on('abrirModal', () => {
    $('#' + event.detail.nombreModal).modal('show');
  });

  $wire.on('cerrarModal', () => {
    $('#' + event.detail.nombreModal).modal('hide');
  });

  //// para mostrar el mensaje y cerrar el modal
  $wire.on('msn', () => {
    Swal.fire({
      title: event.detail.msnTitulo,
      html: event.detail.msnTexto,
      icon: event.detail.msnIcono,
      showConfirmButton: false,
      timer: 2500,
      buttonsStyling: false
    });
  });
</script>
@endscript
