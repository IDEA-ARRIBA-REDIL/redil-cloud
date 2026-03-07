<div class="{{ $class }}">
    <button type="button" class="btn btn-label-secondary mb-6 my-3 rounded-pill btn-outline-secondary px-7 py-3"  class="btn btn-primary" wire:click="abrirBiblia" wire:loading.attr="disabled">
      <span class="align-middle">Seleccionar</span>
    </button>


    <textarea id="{{$name_id}}"  name="{{$name_id}}" class="form-control d-none">@json($versiculosResaltados)</textarea>

    <div class="col-12">
      <!-- Versiculos favoritos -->
      @if(!empty($versiculosResaltados))

        <h5 class="text-black fw-semibold mb-2"> Mis versiculos favoritos </h5>
        <div class="g-2">
          @foreach ($versiculosResaltados as $index => $resaltado)

            <div class="btn-group">
              <button type="button" wire:loading.attr="disabled" class="btn btn-xs btn-outline-primary my-1 py-1" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="align-middle"><i class="ti ti-bookmark"></i> {{ $resaltado['cita'] }}</span>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:void(0);" wire:click="verVersiculoResaltado( {{ $index }} )"><i class="ti ti-book"></i> Ver</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);" wire:click="desmarcar( {{ $index }} )"><i class="ti ti-bookmark-off"></i> Desmarcar</a></li>

              </ul>
            </div>
          @endforeach
        </div>
      @endif
      <!-- /Versiculos favoritos -->
    </div>

    <!-- modalBiblia -->
    <div class="modal fade" id="modalBiblia" tabindex="-1" wire:ignore.self aria-hidden="true">
      <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
          <div class="modal-header border-bottom pb-4 row">

              <div class="col-3 text-start">
                <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
                  <span class="ti-xs ti ti-arrow-left me-2"></span>
                  <span class="d-none d-md-block fw-normal">Volver</span>
                </button>
              </div>
              <div class="col-6 text-center">
                <h5 id="tituloPrincipal" class="text-black my-auto fw-normal">{{ $libroSeleccionado && $libroSeleccionado->libro ? $libroSeleccionado->libro : '¿Qué versículo que deseas leer hoy?' }} {{ $capituloSeleccionado && $capituloSeleccionado['chapter'] ? $capituloSeleccionado['chapter'] : '' }}</h5>
              </div>
              <div class="col-3 text-end">
                <button type="button" class="btn text-black" data-bs-dismiss="modal" aria-label="Close">
                  <span class="d-none d-md-block fw-normal">Salir</span>
                  <span class="ti-md ti ti-x mx-2"></span>
                </button>
              </div>
          </div>
          <div class="modal-body pt-0">
            <div class="row g-2">
              <div class="col-12 col-md-8 offset-md-2 col-lg-6 offset-md-3">
                <div class="sticky-top z-index-10 pt-10 pb-5" style="background: #fff !important;">
                  <div class="d-flex d-column g-2">
                    <div class="mt-2 mb-4 flex-fill">
                      <label>Libro</label>
                      <select id="selectLibro" class="form-select form-select-md" wire:model.live="selectLibro">
                          <option value="" selected>Seleccione el libro</option>
                          <optgroup label="Antiguo testamento">
                              @foreach ($librosAt as $libro)
                                  <option value="{{ $libro->libro }}"  {{ $libroSeleccionado && $libroSeleccionado->libro == $libro->libro ? 'selected' : '' }} >{{ $libro->libro }}</option>
                              @endforeach
                          </optgroup>

                          <optgroup label="Nuevo testamento">
                              @foreach ($librosNt as $libro)
                                  <option value="{{ $libro->libro }}" {{ $libroSeleccionado && $libroSeleccionado->libro == $libro->libro ? 'selected' : '' }}>{{ $libro->libro }}</option>
                              @endforeach
                          </optgroup>
                      </select>
                    </div>

                    <div class="mt-2 mb-4 flex-fill ms-3 ">
                      <label>Capítulo</label>
                      <select id="selectCapitulo" class="form-select form-select-md" wire:model.live="selectCapitulo" {{ $libroSeleccionado ? '' : 'disabled' }}>
                        @if($libroSeleccionado)
                          @foreach ($capitulos as $capitulo)
                            <option value="{{ $capitulo }}"  {{ $capituloSeleccionado && $capituloSeleccionado['chapter'] == $capitulo ? 'selected' : '' }} >{{ $capitulo }}</option>
                          @endforeach
                        @else
                          <option value="" selected>Seleccione la capítulo</option>
                        @endif
                      </select>
                    </div>

                    <div class="mt-2 mb-4 flex-fill ms-3">
                      <label>Versión</label>
                      <select id="selectVersion" class="form-select form-select-md" wire:model.live="selectVersion" {{ $libroSeleccionado ? '' : 'disabled' }}>
                        @if($libroSeleccionado)
                          @foreach ($versiones as $version)
                              <option value="{{ $version['name'] }}" {{ $versionSeleccionada &&  $versionSeleccionada['name'] == $version['name'] ? 'selected' : '' }} >{{ strtoupper($version['version']) }}</option>
                          @endforeach
                        @else
                        <option value="" selected>Seleccione la versión</option>
                        @endif
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <!-- Versiculos favoritos -->
                      @if(!empty($versiculosResaltados))

                        <h6 class="text-black fw-semibold mb-2"> Mis versículos favoritos </h6>
                        <div class="g-2">
                          @foreach ($versiculosResaltados as $index => $resaltado)

                            <div class="btn-group">
                              <button type="button" wire:loading.attr="disabled" class="btn btn-xs btn-outline-primary my-1 py-1" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="align-middle"><i class="ti ti-bookmark"></i> {{ $resaltado['cita'] }}</span>
                              </button>
                              <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0);" wire:click="verVersiculoResaltado( {{ $index }} )"><i class="ti ti-book"></i> Ver</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" wire:click="desmarcar( {{ $index }} )"><i class="ti ti-bookmark-off"></i> Desmarcar</a></li>

                              </ul>
                            </div>
                          @endforeach
                        </div>
                      @endif
                      <!-- /Versiculos favoritos -->
                    </div>

                    <div class="col-12 d-flex align-items-end pt-3">
                      @if(count($subrayados) > 0)
                      <button type="button" class="btn btn-sm  rounded-pill btn-outline-secondary" wire:click="resaltar()" wire:loading.attr="disabled" >
                        <span class="align-middle"><i class="ti ti-bookmark"></i>Resaltar</span>
                      </button>
                      @endif
                    </div>
                  </div>
                </div>

                <div class="pt-5">
                  @if($capituloSeleccionado && $capituloSeleccionado->isNotEmpty())

                    @foreach ( $capituloSeleccionado['vers'] as $versiculo )
                      @if (isset($versiculo['study']) && !empty($versiculo['study']))
                        <h5 class="text-black fw-bold my-2">{{$versiculo['study']}}</h5>
                      @endif

                      <span class="text-black cursor-pointer fs-5 lh-lg {{ in_array($versiculo['number'],$subrayados) ? 'bg-warning' : '' }} {{ in_array($versiculo['number'],$subrayadosTemp) ? 'bg-info' : '' }}" wire:click="toggleSubrayado({{ $versiculo['number'] }})" >
                        <sup><b>{{ $versiculo['number'] }}</b></sup> {{ $versiculo['verse'] }}
                      </span>

                    @endforeach
                  @endif
                </div>
              </div>


            </div>
          </div>
          <div class=" row border-top p-4">
              <div class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3 d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="button" class="btn btn-primary rounded-pill px-10 py-2" data-bs-dismiss="modal" aria-label="Close">
                  <span class="align-middle">Finalizar</span>
                </button>
              </div>
          </div>
        </div>
      </div>
    </div>
    <!-- modalBiblia -->


</div>

@assets
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endassets

@script
  <script>
    $wire.on('abrirModal', () => {
      $('#' + event.detail.nombreModal).modal('show');
    });

    window.addEventListener('msn', event => {
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
  </script>
@endscript
