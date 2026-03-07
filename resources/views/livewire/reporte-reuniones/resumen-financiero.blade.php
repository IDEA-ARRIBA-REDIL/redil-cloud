<div>

  <!-- contenido principal -->
  <div class="row mt-10">
    <div class="col-12 offset-md-1 col-md-10">
      <div id="contadores" class="row mt-10">
        <div class="d-flex flex-column col-6 col-md-3 mb-3">
          <label class="text-black mb-2 fw-semibold">Total</label>
          <div>
            <span class="text-black mx-2 fw-semibold">
              $ {{ number_format($total, 2) }} {{ $moneda->nombre_corto }}
            </span>
          </div>
        </div>

        @foreach ($data as $d)
        <div class="d-flex flex-column col-6 col-md-3 mb-3">
          <label class="text-black mb-2">{{ $d->nombre ?? 'N/A' }}</label>
          <div>
            <span class="text-black mx-2">
              $ {{ number_format($d->sumatoria, 2) }} {{ $moneda->nombre_corto }}
            </span>

            @if ($d->generica)
            <button wire:click="abrirModalEdicion({{ $d->id }})" type="button"
              class="btn btn-sm btn-icon rounded-pill btn-outline waves-effect my-auto"
              style="border: solid 2px #1977E5 !important; color: #1977E5;">
              <i class="ti ti-currency-dollar"></i>
            </button>
            @endif

          </div>
        </div>
        @endforeach
      </div>

      <div id="listaDePersonas" class="row mt-10">
        <h5 class="fw-semibold">Buscar personas para asignarles ingresos financieros</h5>
        <div class="col-12">
          <div class="input-group input-group-merge bg-white">
            <input type="text" wire:model.live.debounce.500ms="buscar" class="form-control"
              placeholder="Buscar por nombre, email o identificación" />
            <hr>
            <span class="input-group-text"><i class="ti ti-search"></i></span>
          </div>
        </div>


        <div class="col-12">
          <div class="d-flex justify-content-between my-5">

          </div>
          <div id="scrollable-personas" class="col-12" style="max-height: 200px; overflow-y: scroll;"
            x-data="{
                            noMasPersonas: @entangle('noMasPersonas'),
                            handleScroll($el) {
                                if (!this.noMasPersonas && $el.scrollHeight - $el.scrollTop === $el.clientHeight) {
                                    // Tu función aquí
                                    console.log('Fin del scroll alcanzado');

                                    $wire.loadMore();
                                }
                            }
                        }" x-on:scroll="handleScroll($el)">
            <ul class="p-0 m-0 mx-4 mx-md-0">

              @foreach ($personas as $persona)
              <li wire:key="persona-{{ $persona->id }}"
                class="d-flex flex-wrap mb-4 border-bottom pb-3">
                <div class="avatar me-4">
                  @if ($persona->foto == 'default-m.png' || $persona->foto == 'default-f.png')
                  <span
                    class="avatar-initial rounded-circle border border-3 border-white bg-info">
                    {{ $persona->inicialesNombre() }} </span>
                  @else
                  <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $persona->foto) : Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $persona->foto) }}"
                    alt="{{ $persona->foto }}" alt="avatar" class="rounded-circle">
                  @endif
                </div>
                <div class="d-flex justify-content-between flex-grow-1">
                  <div class="me-2">
                    <p class="mb-0 text-heading text-black fw-semibold">
                      {{ $persona->nombre(4) }}
                    </p>
                    <p class="small mb-0"><i
                        class="{{ $persona->tipoUsuario->icono }} fs-6"></i>
                      {{ $persona->tipoUsuario->nombre }}
                    </p>
                  </div>
                  <div class="">
                    <button wire:click="abrirModalOfrendaEspecifica({{ $persona->id }})"
                      class="btn btn-xs rounded-pill btn-outline-success waves-effect mt-1 m-2">
                      $
                      {{ number_format($reporteReunion->totalOfrendasPorUsuario($persona->id), 2) }}
                      {{ $moneda->nombre_corto }}
                      <i class="ms-3 ti ti-edit"></i>
                    </button>
                  </div>
                </div>
              </li>
              @endforeach
            </ul>

            <!-- Aquí insertas el mensaje de carga o fin -->
            <div wire:loading.class="opacity-50" class="text-center my-3">
              @if ($noMasPersonas)
              <p class="tx-12 text-muted text-center pt-3">
                <i class="ti ti-list-search fs-4"></i> No hay más usuarios.
              </p>
              @else
              <div wire:loading class="text-center">
                <div class="spinner-border spinner-border-lg text-primary my-2" role="status">
                  <span class="visually-hidden">Cargando...</span>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- /Modal Especifica -->
  <div wire:ignore.self class="modal fade" id="modalOfrendaEspecifica" tabindex="-1"
    aria-labelledby="modalEdicionLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ofrendas de
            {{ $usuarioSeleccionado ? $usuarioSeleccionado->nombre(3) : '' }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            @foreach ($ofrendasTemporalEspecificasUsuario as $ofrenda)
            <div class="col-12 col-md-6 mb-3">
              <label for="valorModal" class="form-label">{{ $ofrenda->nombre }}</label>
              <input type="number" class="form-control" id="{{ $ofrenda->tipo_ofrenda_id }}"
                name="{{ $ofrenda->nombre }}"
                value="{{ old('' . $ofrenda->tipo_ofrenda_id, $ofrenda->valor) }}"
                wire:model="inputsTemporalOfrendasEspecificasUsuario.{{ $ofrenda->tipo_ofrenda_id }}.valor"
                min="0" step="0.01">
              @if ($errors->has('inputsTemporalOfrendasEspecificasUsuario.' . $ofrenda->tipo_ofrenda_id . '.valor'))
              <small class="text-danger">
                {{ $errors->first('inputsTemporalOfrendasEspecificasUsuario.' . $ofrenda->tipo_ofrenda_id . '.valor') }}
              </small>
              @endif
            </div>
            @endforeach
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" wire:click="guardarCambiosEspecificos">Añadir
            ofrenda</button>
        </div>
      </div>
    </div>
  </div>

  <!-- /Modal Generica -->
  <div wire:ignore.self class="modal fade" id="modalEdicionOfrenda" tabindex="-1" aria-labelledby="modalEdicionLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Ofrenda</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="valorModal" class="form-label">Valor</label>
            <input type="number" class="form-control" wire:model="valorModal" min="0"
              step="0.01">
            @error('valorModal')
            <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" wire:click="guardarCambios">Añadir
            ofrenda</button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
  <script>
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

    window.addEventListener('abrirModal', event => {
      const modalId = event.detail.nombreModal;
      $('#' + modalId).modal('show');
    });

    window.addEventListener('cerrarModal', event => {
      const modalId = event.detail.nombreModal;
      $('#' + modalId).modal('hide');
    });
  </script>
  @endpush
</div>