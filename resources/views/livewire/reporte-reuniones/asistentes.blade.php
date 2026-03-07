<div>
  @include('layouts.status-msn')
  <div class="row">
    <div class="col-12 offset-md-1 col-md-10">
      <div id="contadores" class="row mt-10">
        <div class="d-flex flex-column col-6 col-md-3 mb-3">
          <label class="text-black mb-3 fw-semibold">Todas</label>
          <div>
            <span class="rounded fs-5 p-2 text-black mx-2 fw-semibold">
              {{ $reporteReunion->cantidad_asistencias }}
            </span>
          </div>
        </div>

        <div class="d-flex flex-column col-6 col-md-3 mb-3">
          <label class="text-black mb-3">Invitados</label>
          <div>
            <button wire:click="restarInvitados" type="button"
              class="btn btn-sm btn-icon rounded-pill btn-outline waves-effect my-auto"
              style="border: solid 2px #1977E5 !important; color: #1977E5;"><i
                class="ti ti-minus"></i></button>
            <span class="rounded border border-2 fs-5 p-2 text-black mx-2">
              {{ $reporteReunion->invitados }}
            </span>
            <button wire:click="sumarInvitados" type="button"
              class="btn btn-sm btn-icon rounded-pill btn-outline waves-effect my-auto"
              style="border: solid 2px #1977E5 !important; color: #1977E5;"><i
                class="ti ti-plus"></i></button>
          </div>
        </div>

        @foreach ($sumatoriasAdicionales as $sa)
        <div class="d-flex flex-column col-6 col-md-3 mb-3">
          {{-- Es bueno añadir un fallback por si $sa->nombre también puede ser null --}}
          <label class="text-black mb-3">{{ $sa->nombre ?? 'N/A' }}</label>
          <div>
            {{-- Si $clasificacionId existe, muestra los botones funcionales --}}
            <button wire:click="restarClasificacion({{ $sa->id }})" type="button"
              class="btn btn-sm btn-icon rounded-pill btn-outline waves-effect my-auto"
              style="border: solid 2px #1977E5 !important; color: #1977E5;"><i
                class="ti ti-minus"></i></button>
            <span class="rounded border border-2 fs-5 p-2 text-black mx-2">
              {{-- Añadir un fallback para cantidad también es buena práctica --}}
              {{ $sa->pivot->cantidad }}
            </span>
            <button wire:click="sumarClasificacion({{ $sa->id }})" type="button"
              class="btn btn-sm btn-icon rounded-pill btn-outline waves-effect my-auto"
              style="border: solid 2px #1977E5 !important; color: #1977E5;"><i
                class="ti ti-plus"></i></button>
          </div>
        </div>
        @endforeach

      </div>

      <hr>

      <div id="listaDePersonas" class="row mt-10">
        <h5 class="fw-semibold">¿Quienes asistieron a la reunión?</h5>
        <div class="col-12">
          <div class="input-group input-group-merge bg-white">
            <input type="text" wire:model.live.debounce.500ms="buscar" class="form-control"
              placeholder="Buscar por nombre o email" />
            <span class="input-group-text"><i class="ti ti-search"></i></span>
          </div>
        </div>

        <div class="col-12">
          <div class="d-flex justify-content-between my-5">
            <span class="text-black fs-6">Personas</span>
            <span class="text-black fs-6">¿Asistio?</span>
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
                                <p class="mb-0 text-heading text-black fw-semibold">
                                    {{-- El nombre viene estandarizado desde la consulta --}}
                                    {{ $persona->primer_nombre }} {{ $persona->primer_apellido }}
                                </p>

                                {{-- CAMBIO 2: Mostrar "Invitado" o el tipo de usuario real --}}
                                @if($persona->es_invitado)
                                    <p class="small mb-0"><i class="ti ti-ticket fs-6"></i> Invitado</p>
                                @else
                                    <p class="small mb-0">
                                        {{ $persona->email ?? 'No indicado' }}
                                    </p>
                                @endif
                            </div>
                            <div class="">
                                {{-- CAMBIO 3: Solo mostrar el switch para usuarios reales --}}
                                @if(!$persona->es_invitado)
                                    <label class="switch switch-lg mx-auto">
                                        <input id="asistencias.{{ $persona->id }}" type="checkbox"
                                               class="switch-input"
                                               wire:model.live="asistencias.{{ $persona->id }}" />
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">SI</span>
                                            <span class="switch-off">NO</span>
                                        </span>
                                        <span class="switch-label"></span>
                                    </label>
                                @else
                                     <label class="switch switch-lg mx-auto">
                                        <input id="asistenciasInvitados.{{ $persona->id }}" type="checkbox"
                                               class="switch-input"
                                               wire:model.live="asistenciasInvitados.{{ $persona->id  }}" />
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">SI</span>
                                            <span class="switch-off">NO</span>
                                        </span>
                                        <span class="switch-label"></span>
                                    </label>
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
</div>


@assets
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endassets

@push('scripts')

@endpush
</div>
