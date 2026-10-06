<div>
  @include('layouts.status-msn')

  <div class="row">
    <div class="col-12 ">
      <div class="row mb-10 equal-height-row  g-2">
        <div class="col equal-height-col col-lg-3 col-12">
          <a href="javascript:;">
            <div class="h-100 card border rounded-3 shadow-sm p-2">
              <div class="card-body d-flex flex-row p-3">

                <div class="card-icon me-2 my-auto">
                <h5 class="mb-0 me-2 fw-bold">{{ $aforo }}</h5>
                </div>

                <div class="card-title mb-0 lh-md flex-shrink-1">
                  <p class="text-black mb-0">Aforo <br> Total</p>

                </div>

              </div>
            </div>
          </a>
        </div>

        <div class="col equal-height-col col-lg-3 col-12">
          <a href="javascript:;">
            <div class="h-100 card border rounded-3 shadow-sm p-2">
              <div class="card-body d-flex flex-row p-3">

                <div class="card-icon me-2 my-auto">
                <h5 class="mb-0 me-2 fw-bold text-danger">{{ $aforoOcupado }}</h5>
                </div>

                <div class="card-title mb-0 lh-md flex-shrink-1">
                  <p class="text-black mb-0">Aforo <br> reservado</p>

                </div>

              </div>
            </div>
          </a>
        </div>

        <div class="col equal-height-col col-lg-3 col-12">
          <a href="javascript:;">
            <div class="h-100 card border rounded-3 shadow-sm p-2">
              <div class="card-body d-flex flex-row p-3">

                <div class="card-icon me-2 my-auto">
                <h5 class="mb-0 me-2 fw-bold text-success">{{ $aforoDisponible }}</h5>
                </div>

                <div class="card-title mb-0 lh-md flex-shrink-1">
                  <p class="text-black mb-0">Aforo <br> disponible</p>
                </div>

              </div>
            </div>
          </a>
        </div>

      </div>

      <hr>

      <div id="listaDePersonas" class="row mt-10">

      {{-- ✅ NUEVO BOTÓN PARA AGREGAR INVITADO --}}
        <div class="col-12 col-md-12  mb-3 d-flex justify-content-end">
            <button type="button" wire:click="añadirInvitado" class="btn btn-outline-primary waves-effect">
                <i class="ti ti-plus me-1"></i>
                Agregar Invitado
            </button>
        </div>


        <div class="col-12 col-md-12 mb-3 mb-md-0">
          <div class="input-group input-group-merge bg-white">
              <input type="text" wire:model.live.debounce.500ms="buscar" class="form-control" placeholder="Buscar por nombre o email" />
              <span class="input-group-text"><i class="ti ti-search"></i></span>
          </div>
        </div>

        <div class="col-12">
          <div class="d-flex justify-content-between my-5">
            <span class="text-black fs-6"></span>
            <span class="text-black fs-6"></span>
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
                    {{-- CAMBIO 1: Usar la clave única para evitar conflictos de ID --}}
                    <li wire:key="{{ $persona->unique_key }}"
                        class="d-flex flex-wrap mb-4 border-bottom pb-3">
                        <div class="avatar me-4">
                            {{-- Lógica para mostrar avatar o iniciales --}}
                            @if(!$persona->es_invitado && ($persona->foto == 'default-m.png' || $persona->foto == 'default-f.png'))
                                <span class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                    {{-- Asumimos que el modelo User tiene este método --}}
                                    {{ substr($persona->primer_nombre, 0, 1) . substr($persona->primer_apellido, 0, 1) }}
                                </span>
                            @elseif(!$persona->es_invitado && $persona->foto)
                                <img src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $persona->foto) }}"
                                     alt="avatar" class="rounded-circle">
                            @else
                                {{-- Avatar por defecto para invitados --}}
                                <span class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                    <i class="ti ti-ticket ti-sm"></i>
                                </span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between flex-grow-1">
                            <div class="me-2">
                              <p class="mb-0 text-heading text-black fw-bold">
                                {{ $persona->primer_nombre }} {{ $persona->primer_apellido }}
                              </p>
                              <p class="small mb-0">
                                {{ $persona->email ?? 'No indicado' }}
                              </p>
                            </div>
                            <div class="">
                              @if ($persona->tiene_reserva)
                                @if ($persona->tiene_asistencia)
                                  <a href="javascript;:" class="my-auto btn btn-sm mx-1 rounded-pill waves-effect btn-outline-primary" disabled> Gestionar reservas</a>
                                @else
                                  @if(!$persona->es_invitado)
                                  <a href="{{ route('reporteReunion.resumenReserva', ['reporteReunion' => $reporteReunion->id,'user' => $persona->id]) }}?origen=añadirReservas" class="my-auto btn btn-sm mx-1 rounded-pill waves-effect btn-outline-primary"> Gestionar reservas</a>
                                  @else
                                  <a href="{{ route('reporteReunion.resumenReservaInvitado', ['reserva' => $persona->reserva_id]) }}?origen=añadirReservas" class="my-auto btn btn-sm mx-1 rounded-pill waves-effect btn-outline-primary"> Gestionar reservas</a>
                                  @endif
                                @endif
                              @else
                                 <a href="{{ route('reporteReunion.miReserva', ['reporte' => $reporteReunion->id, 'user' => $persona->id]) }}?origen=añadirReservas" class="my-auto btn btn-sm mx-1 rounded-pill waves-effect btn-outline-secondary">Reservar</a>
                              @endif
                            </div>
                        </div>
                    </li>
                @endforeach

            </ul>

            <!-- Aquí insertas el mensaje de carga o fin -->
            <div wire:loading.class="opacity-50" class="text-center my-3">
              @if ($noMasPersonas)
              <p class="tx-12 text-muted text-center pt-3">
                <i class="ti ti-list-search fs-4"></i> No hay más personas.
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



    <!-- nuevo reporte -->

    <div wire:ignore.self class="modal fade" id="modalNuevoInvitado" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered modal-simple">
        <div class="modal-content p-0">
          <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
            <p class="text-black fw-semibold mb-0">Crear invitado</p>
            <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x ti-sm"></i></button>
          </div>

          <div class="modal-body px-5 py-8">
                <form wire:submit.prevent="crearInvitado">
                    <div class="mb-6">
                        <label for="guestName" class="form-label">Nombre del Invitado</label>
                        <input type="text" id="guestName" onkeypress="return sinComillas(event)" class="form-control @error('guestName') is-invalid @enderror"
                               wire:model="guestName" placeholder="Ingrese el nombre completo">
                        @error('guestName') <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-6">
                        <label for="guestEmail" class="form-label">Email</label>
                        <input type="email" id="guestEmail" class="form-control @error('guestEmail') is-invalid @enderror"
                               wire:model="guestEmail" placeholder="Ingrese el correo electrónico">
                        @error('guestEmail') <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div> @enderror
                    </div>
                </form>

          </div>

          <div class="modal-footer border-top p-5">
            <button type="button" data-bs-dismiss="modal" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>

            <button type="button" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light" wire:click="crearInvitado">
              <span wire:loading wire:target="crearInvitado" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </div>



</div>


@assets
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endassets

@script
  <script>
    $wire.on('abrirModal', () => {
      const modalSelector = '#' + event.detail.nombreModal;
      $('#' + event.detail.nombreModal).modal('show');
    });

    $wire.on('cerrarModal', data => {
      const modalSelector = '#' + event.detail.nombreModal;
      $('#' + event.detail.nombreModal).modal('hide');
    });
  </script>

@endscript

</div>
