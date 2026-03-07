<div class="{{ $class }}">

  @if ($label)
  <label class="form-label">
    {{ $label }}
  </label>
  @endif

  <div wire:click.outside="ocultarListaBusqueda()" class="{{ $verInputBusqueda ? '' : 'd-none' }}">
    <div class="input-group input-group-merge">
      <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
      <input wire:model.live.debounce.30ms="busqueda" wire:click="desplegarListaBusqueda"
        wire:keydown="resetCantidadUsuariosCargados" type="text" class="form-control"
        placeholder="{{ $placeholder ? $placeholder : 'Buscar' }}" aria-label="Buscar"
        aria-describedby="basic-addon-search31" spellcheck="false" data-ms-editor="true">
    </div>
  </div>

  @if ($errors->has($id) && $obligatorio)
  <div class="text-danger form-label">{{ $errors->first($id) }}</div>
  @endif

  @if ($contenidoExtraListaPersonas == true)
  @if ($usuarios && count($usuarios) > 0)
  <div class="card mb-6 mt-3 list-group" style="height: 400px;border: solid 2px #e6e6e8;">
    <div wire:ignore.self class="mt-5 card-body scroll" id="vertical-example" x-data="{
                    handleScroll($el) {
                            if ($el.scrollHeight - $el.scrollTop === $el.clientHeight) {
                                // Tu función aquí
                                console.log('Fin del scroll alcanzado');
                                this.cargarMas();
                                $wire.loadMore();
                            }
                        },
                        cargarMas() {
                            // Ejemplo de función a ejecutar
                            console.log('Cargando más contenido...');
                        }
                }"
      x-on:scroll="handleScroll($el)">
      @foreach ($usuarios as $usuario)
      <div class="row mb-3 p-3 border-bottom border-2 align-items-center cursor-pointer">
        <div class="col-lg-2 col-sm-12 align-items-start justify-content-start mx-auto me-3">
          <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario->foto }}"
            alt="foto {{ $usuario->primer_nombre }}" class="rounded-circle w-px-75" />
        </div>
        <div class="col-sm-12 col-lg-6  me-2">
          <p class="fs-6 text-wrap m-0 mt-1 fw-bold"> {{ $usuario->nombre(3) }}
            {{ $usuario->deleted_at }}
          </p>
          <p class="fs-6 text-wrap m-0 mt-1"> <span class="badge px-1"
              style="background-color: {{ $usuario->tipoUsuario->color }}"><i
                class="fs-6 {{ $usuario->tipoUsuario->icono }}"></i></span>
            {{ $usuario->tipoUsuario->nombre }}
          </p>
        </div>
        <!-- aqui se se agrego esta seccion para el boton de accion de gestion de cargos actividad-->
        @if ($modulo == 'anadir-encargados-actividad')
        <div class=" col-sm-12 col-lg-3  me-2 justify-content-end ">
          <button type="button"
            wire:click="$dispatch('abrirModalNuevoCargo', { usuarioId: '{{ $usuario->id }}' })"
            class="btn btn-primary rounded-pill waves-effect  float-end">
            <i class="ti ti-user-pentagon"></i> <span style="font-size:small"> Gestionar
              Cargos </span>
          </button>
        </div>
        @endif
        <!-- aqui se se agrego esta seccion para el boton de accion de gestion de asingar asistencias a una actividad-->
        @if ($modulo == 'anadir-asistencias-actividad')
        <!-- aqui se se agrego esto es un metodo en el controlador de livewire
                    de usuarios busqueda que consulta si tiene asistencia, y segun la respuesta pone el boton -->
        @if (!$this->tieneAsistencia($usuario->id))
        <div class="col-sm-12 col-lg-3 me-2 justify-content-end">
          <!--  el metodo que se llama aqui esta en el controlador del livewire de activiadd asistencias, el devuelta ejecuta un metodo dentro
                              del livewire de usuarios para busqueda que actualiza el contenido del listado de las asistencias en un array para que cambie automaticamente
                            cual boton mostrar y que si se recarga la pagina consulte si el usuario que recorre tiene una asistencia -->
          <button wire:click="$parent.registrarAsistencia('{{ $usuario->id }}')"
            class="btn btn-primary float-end">
            <i class="ti ti-user-plus"></i>
            <span style="font-size:small">Registrar Asistencia</span>
          </button>
        </div>
        @else
        <div class=" col-sm-12 col-lg-3 me-2 justify-content-end">
          <button wire:click="$parent.eliminarAsistencia('{{ $usuario->id }}')"
            class="btn btn-success float-end">
            <i class="ti ti-user-check"></i>
            <span style="font-size:small">Eliminar Asistencia</span>
          </button>
        </div>
        @endif
        @endif
      </div>
      @endforeach

      @if ($usuarios->hasMorePages())
      <p class="p-2 text-center">
        Cargando más usuarios...
      </p>
      @endif
    </div>
  </div>
  @endif
  @else
  <!-- aqui se se agrego esta restriccion para poder cargar los usuarios de tipo lista con buscador sin muestra en el div del buscador -->
  <div class="divListaBusquedaGrupos position-relative {{ $verListaBusqueda ? '' : 'd-none' }}">
    <div id="listaItemsBusqueda{{ $id }}" wire:ignore.self
      class="panel-busqueda p-2 card-body scroll" id="vertical-example" x-data="{
                    handleScroll($el) {
                            if ($el.scrollHeight - $el.scrollTop === $el.clientHeight) {
                                $wire.loadMore();
                            }
                        },
                        cargarMas() {
                            console.log('Cargando más contenido...');
                        }
                }"
      x-on:scroll="handleScroll($el)">
      @if ($usuarios && count($usuarios) > 0)
      @if ($contenidoExtraListaPersonas == false)
      @foreach ($usuarios as $usuario)
      <a href="{{ $redirect ? route($redirect, $usuario->id) : 'javascript:;' }}"
        @if ($tipoBuscador=='multiple' ) wire:click="seleccionarUsuarios({{ $usuario->id }})" @elseif($tipoBuscador=='unico' ) wire:click="seleccionarUsuario({{ $usuario->id }})" @endif
        class="dropdown-item d-flex align-items-center p-2 m-1 border">
        <div class="d-flex align-items-center justify-content-center mx-auto me-3">
          <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario->foto }}"
            alt="foto {{ $usuario->primer_nombre }}" class="rounded-circle w-px-75" />
        </div>
        <div class="flex-grow-1 me-2">
          <p class="fs-6 text-wrap m-0 mt-1 fw-bold"> {{ $usuario->nombre(3) }}
            {{ $usuario->deleted_at }}
          </p>
          <p class="fs-6 text-wrap m-0 mt-1"> <span class="badge px-1"
              style="background-color: {{ $usuario->tipoUsuario->color }}"><i
                class="fs-6 {{ $usuario->tipoUsuario->icono }}"></i></span>
            {{ $usuario->tipoUsuario->nombre }}
          </p>
        </div>
      </a>
      @endforeach

      @if ($usuarios->count() < $usuarios->total())
        <div wire:loading class="text-center">
          <div class="spinner-border spinner-border-lg text-primary my-2" role="status">
            <span class="visually-hidden">Cargado...</span>
          </div>
        </div>
        @else
        <p class="tx-12 text-muted text-center pt-3"> <i class="ti ti-list-search fs-4 "> </i> No
          hay más usuarios.</p>
        @endif
        @endif
        @else
        <div class="text-center pt-3">
          <p class="tx-12 text-muted"> <i class="ti ti-list-search fs-4"> </i> La busqueda no arrojo
            ningun resultado.</p>
        </div>
        @endif
    </div>
  </div>
  @endif

  @if ($tipoBuscador == 'multiple')
  <input type="text" id="{{ $id }}" name="{{ $id }}"
    value="{{ json_encode($usuariosSeleccionadosIds) }}" class="form-control d-none" placeholder="Enter Name">
  <div class="row mt-2">
    @foreach ($usuariosSeleccionados as $usuarioSel)
    <div class=" {{ in_array($modulo, ['integrantes-grupo']) ? 'col-12 col-md-6 col-lg-4' : 'col-12' }}">
      <div wire:key="usuario-{{ $usuarioSel->id }}"
        class="dropdown-item w-100 mx-0 d-flex p-2 border flex-grow-1  ">
        <div class="flex-fill d-flex align-items-center">
          <div class="d-flex align-items-center justify-content-center mx-auto me-3">
            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuarioSel->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuarioSel->foto }}"
              alt="foto {{ $usuarioSel->primer_nombre }}" class="rounded-circle w-px-75" />
          </div>
          <div class="flex-grow-1 me-2">
            <p class="fs-6 text-wrap m-0 mt-1 fw-bold"> {{ $usuarioSel->nombre(3) }}
              {{ $usuarioSel->deleted_at }}
            </p>
            <p class="fs-6 text-wrap m-0 mt-1"> <span class="badge px-1"
                style="background-color: {{ $usuarioSel->tipoUsuario->color }}"><i
                  class="fs-6 {{ $usuarioSel->tipoUsuario->icono }}"></i></span>
              {{ $usuarioSel->tipoUsuario->nombre }}
            </p>
            @if ($modulo == 'servidores-grupo')
            <div class="m-0 mt-1">
              <div class="text-wrap border rounded p-1 mt-1">
                @if ($usuarioSel->serviciosPrestadosEnGrupos($grupo->id)->count() > 0)
                @foreach ($usuarioSel->serviciosPrestadosEnGrupos($grupo->id) as $tipoServicio)
                <span wire:key="servicio-{{ $tipoServicio->id }}"
                  class="mt-1 badge rounded-pill bg-label-primary">{{ $tipoServicio->nombre }}</span>
                @endforeach
                @else
                <span class="mt-1 badge badge rounded-pill bg-label-secondary">No tiene
                  asignado ningun servicio </span>
                @endif
              </div>
              <button type="button" wire:click='gestionarServicios({{ $usuarioSel->id }})'
                class="mt-1 btn btn-xs rounded-pill btn-outline-primary waves-effect">
                <span class="ti-xs ti ti-edit me-1"></span>Gestionar servicios
              </button>
            </div>
            @endif
          </div>
        </div>

        <div class="d-flex align-items-start">
          @if ($rolActivo->hasPermissionTo('grupos.opcion_desvincular_asistentes_grupos'))
          <button type="button" wire:click="quitarSeleccion({{ $usuarioSel->id }})"
            class="align-self-start btn btn-danger btn-xs p-1"><i
              class="ti ti-x fs-6"></i></button>
          @endif
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @elseif($tipoBuscador == 'unico')
  @if ($usuarioSeleccionado)
  <input type="text" id="{{ $id }}" name="{{ $id }}"
    value="{{ $usuarioSeleccionado->id }}" class="form-control d-none" placeholder="Enter Name">

  <div class="col-12">
    @if ($estiloSeleccion)
    @if ($estiloSeleccion == 'pequeno')
    <div wire:key="usuario-{{ $usuarioSeleccionado->id }}"
      class="dropdown-item w-100 m-0 d-flex p-1 border flex-grow-1">
      <div class="flex-fill d-flex align-items-center">
        <div class="avatar avatar-xs me-2">
          <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuarioSeleccionado->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuarioSeleccionado->foto }}"
            alt="foto {{ $usuarioSeleccionado->primer_nombre }}"
            class="rounded-circle" />
        </div>
        <div class="flex-grow-1 me-2">
          <p class="fs-6 text-wrap m-0 mt-1 fw-bold"> {{ $usuarioSeleccionado->nombre(3) }}
            {{ $usuarioSeleccionado->deleted_at }}
          </p>
        </div>
      </div>

      <div class="d-flex align-items-start">
        @if ($rolActivo->hasPermissionTo('grupos.opcion_desvincular_asistentes_grupos'))
        <button type="button"
          wire:click="quitarSeleccion({{ $usuarioSeleccionado->id }})"
          class="align-self-start btn btn-danger btn-xs p-1"><i
            class="ti ti-x fs-6"></i></button>
        @endif
      </div>
    </div>
    @endif
    @else
    <div wire:key="usuario-{{ $usuarioSeleccionado->id }}"
      class="dropdown-item w-100 mx-0 d-flex p-2 border flex-grow-1 mt-2 ">
      <div class="flex-fill d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-center mx-auto me-3">
          <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuarioSeleccionado->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuarioSeleccionado->foto }}"
            alt="foto {{ $usuarioSeleccionado->primer_nombre }}"
            class="rounded-circle w-px-75" />
        </div>
        <div class="flex-grow-1 me-2">
          <p class="fs-6 text-wrap m-0 mt-1 fw-bold"> {{ $usuarioSeleccionado->nombre(3) }}
            {{ $usuarioSeleccionado->deleted_at }}
          </p>
          <p class="fs-6 text-wrap m-0 mt-1"> <span class="badge px-1"
              style="background-color: {{ $usuarioSeleccionado->tipoUsuario->color }}"><i
                class="fs-6 {{ $usuarioSeleccionado->tipoUsuario->icono }}"></i></span>
            {{ $usuarioSeleccionado->tipoUsuario->nombre }}
          </p>
        </div>
      </div>

      <div class="d-flex align-items-start align-middle">
        @if ($rolActivo->hasPermissionTo('grupos.opcion_desvincular_asistentes_grupos'))
        <button type="button" wire:click="quitarSeleccion({{ $usuarioSeleccionado->id }})"
          class="align-self-start btn btn-danger btn-sm p-1"><i
            class="ti ti-x"></i></button>
        @endif
      </div>
    </div>
    @endif
  </div>
  @endif
  @endif

  @if (
  $tieneInformeDeVinculacion &&
  ($rolActivo->hasPermissionTo('grupos.mostar_modal_informe_asignacion_de_lideres') ||
  $rolActivo->hasPermissionTo('grupos.mostar_modal_informe_asignacion_de_asistentes')))
  <!-- modalInformeAsignacion-->
  <div wire:ignore.self class="modal fade" id="modalInformeAsignacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
      <div class="modal-content p-3 p-md-5">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
          <div class="text-center mb-4">
            <h3 class="mb-2"><i class="ti ti-file-info ti-lg"></i> Informe de asignación al grupo
            </h3>
            <p class="text-muted">Los campos con <span class="badge badge-dot bg-info me-1"></span>
              son obligatorios</p>
          </div>
          <form wire:submit="informeAsignacion" class="row g-3">
            <!-- Motivo de asignación -->
            <div id="divSelectTipoPeticion" class="mb-2 col-12 col-md-12">
              <label class="form-label" for="tipo_peticion">
                <span class="badge badge-dot bg-info me-1"></span>
                Motivo de asignación @error('motivoModalAsignacion')
                <span class="error"> Este campo es obligatorio </span>
                @enderror
              </label>
              <select class="form-select" wire:model="motivoModalAsignacion"
                data-allow-clear="true">
                <option value="">Ninguno</option>
                @foreach ($tiposAsignaciones as $tipoAsignacion)
                <option value="{{ $tipoAsignacion->id }}">{{ $tipoAsignacion->nombre }}
                </option>
                @endforeach
              </select>
            </div>
            <!-- /Motivo de asignación -->

            <!-- Observacion -->
            <div class="mb-2 col-12 col-md-12">
              <label class="form-label" for="descripcion_peticion">
                Observaciones
              </label>
              <textarea wire:model="observacionModalAsigancion" class="form-control" rows="2" maxlength="500"
                spellcheck="false" data-ms-editor="true" placeholder="Detalla aquí las observaciones adicionales .">{{ old('descripcion_peticion') }}</textarea>
            </div>
            <!--/Observacion-->

            <!-- Desvinculacion de servicios-->
            <div class="mb-2 col-12 col-md-12">
              <div class=" small fw-medium mb-1">¿Desvincular de los servicios que presta en los
                grupos?</div>
              <label class="switch switch-lg">
                <input wire:model="desvinculacionDeServiciosModalAsignacion" type="checkbox"
                  class="switch-input" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">SI</span>
                  <span class="switch-off">NO</span>
                </span>
                <span class="switch-label"></span>
              </label>
            </div>
            <!-- / Desvinculacion de servicios-->

            <!-- $desvinculacionDeGruposDondeAsiste -->

            <!-- Desvinculacion de grupos donde asiste -->
            @if ($gruposDondeAsisteActualmente && count($gruposDondeAsisteActualmente) > 0)
            <div class="mb-2 col-12 col-md-12">
              <div class=" small fw-medium mb-1">Está persona es integrante en lo(s) siguiente(s)
                grupo(s) ¿Desea desvincularla?</div>
              <div class="d-flex flex-column">
                @foreach ($gruposDondeAsisteActualmente as $grupoActual)
                <label class="switch switch-lg mb-2">
                  <input wire:model="idsDesviculacionDeLosGruposModalAsignacion"
                    value="{{ $grupoActual->id }}" type="checkbox"
                    class="switch-input" />
                  <span class="switch-toggle-slider">
                    <span class="switch-on">SI</span>
                    <span class="switch-off">NO</span>
                  </span>
                  <span class="switch-label mx-auto"><b> Grupo: </b>
                    {{ $grupoActual->nombre }}</span>

                </label>
                @endforeach
              </div>

            </div>
            @endif
            <!-- / Desvinculacion de grupos donde asiste-->

            <div class="col-12 text-center">
              <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
              <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                aria-label="Close">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ modalInformeAsignacion -->
  @endif

  @if (
  $tieneInformeDeDesvinculacion &&
  ($rolActivo->hasPermissionTo('grupos.mostar_modal_informe_desvinculacion_de_lideres') ||
  $rolActivo->hasPermissionTo('grupos.mostar_modal_informe_desvinculacion_de_asistentes')))
  <!-- modalInformeDesvinculacion-->
  <div wire:ignore.self class="modal fade" id="modalInformeDesvinculacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
      <div class="modal-content p-3 p-md-5">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
          <div class="text-center mb-4">
            <h3 class="mb-2"><i class="ti ti-file-info ti-lg"></i> Informe de desvinculación al
              grupo</h3>
            <p class="text-muted">Los campos con <span class="badge badge-dot bg-info me-1"></span>
              son obligatorios</p>
          </div>
          <form wire:submit="informeDesvinculacion" class="row g-3">

            <!-- Motivo de asignación -->
            <div id="divSelectTipoPeticion" class="mb-2 col-12 col-md-12">
              <label class="form-label">
                <span class="badge badge-dot bg-info me-1"></span>
                Motivo de desvinculación @error('motivoModalDesvinculacion')
                <span class="error"> Este campo es obligatorio </span>
                @enderror
              </label>
              <select class="form-select" wire:model="motivoModalDesvinculacion"
                data-allow-clear="true">
                <option value="">Ninguno</option>
                @foreach ($tiposDesvinculacion as $tipoDesvinculacion)
                <option value="{{ $tipoDesvinculacion->id }}">
                  {{ $tipoDesvinculacion->nombre }}
                </option>
                @endforeach
              </select>
            </div>
            <!-- /Motivo de asignación -->

            <!-- Observacion -->
            <div class="mb-2 col-12 col-md-12">
              <label class="form-label">
                Observaciones
              </label>
              <textarea wire:model="observacionModalDesvinculacion" class="form-control" rows="2" maxlength="500"
                spellcheck="false" data-ms-editor="true" placeholder="Detalla aquí las observaciones adicionales .">{{ old('descripcion_peticion') }}</textarea>
            </div>
            <!--/Observacion-->

            <!-- Desvinculacion -->
            <div class="mb-2 col-12 col-md-12">
              <div class=" small fw-medium mb-1">¿Desvincular a esta persona de los servicios que
                presta en los grupos?</div>
              <label class="switch switch-lg">
                <input wire:model="desvinculacionDeServiciosModalDesvinculacion" type="checkbox"
                  class="switch-input" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">SI</span>
                  <span class="switch-off">NO</span>
                </span>
                <span class="switch-label"></span>
              </label>
            </div>
            <!-- / Desvinculacion -->

            <div class="col-12 text-center">
              <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
              <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                aria-label="Close">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ modalInformeDesvinculacion -->
  @endif

  @if ($modulo == 'servidores-grupo')
  <!-- modalGestionarServicios-->
  <div wire:ignore.self class="modal fade" id="modalGestionarServicios" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
      <div class="modal-content p-3 p-md-5">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
          <div class="text-center mb-4">
            <h3 class="mb-2"><i class="ti ti-lg"></i> Gestionar servicios</h3>
            <p class="text-muted">Selecciona los servicios prestados</p>
          </div>
          <form wire:submit="guardarServicios" class="row g-3">
            @foreach ($tiposServicioGrupo as $tipoServicio)
            <div class="col-12 col-md-12 text-center">
              <label class="switch switch-lg">
                {{ $tipoServicio->nombre }}
                <input wire:model="idsServiciosUsuario" value="{{ $tipoServicio->id }}"
                  type="checkbox" class="switch-input"
                  @if (in_array($tipoServicio->id, $idsServiciosUsuario)) checked @endif />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
                <span class="switch-label"></span>
              </label>
            </div>
            @endforeach

            <div class="col-12 text-center mt-3">
              <br>
              <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
              <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                aria-label="Close">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ modalGestionarServicios -->
  @endif

  @if ($modulo == 'integrantes-grupo')
  <!-- modalConfirmacionDesviculacionGruposDondeAsiste-->
  <div wire:ignore.self class="modal fade" id="modalConfirmacionDesviculacionGruposDondeAsiste" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
      <div class="modal-content p-3 p-md-5">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
          <div class="text-center mb-4">
            <h3 class="mb-2"><i class="ti ti-file-info ti-lg"></i> Observación</h3>
            <p class="text-muted">Está persona es integrante en lo(s) siguiente(s) grupo(s) ¿Desea
              desvincularla?</p>
          </div>
          <form wire:submit="desvinculacionGruposAnteriores({{ $modalUserId }})" class="row g-3">
            <!-- Desvinculacion de grupos donde asiste -->
            @if ($gruposDondeAsisteActualmente && count($gruposDondeAsisteActualmente) > 0)
            <div class="mb-2 col-12 col-md-12">
              <div class=" small fw-medium mb-1 d-none">Está persona es integrante en lo(s)
                siguiente(s) grupo(s) ¿Desea desvincularla?</div>
              <div class="d-flex flex-column">
                @foreach ($gruposDondeAsisteActualmente as $grupoActual)
                <label class="switch switch-lg mb-2">
                  <input wire:model="idsDesviculacionDeLosGruposModalAsignacion"
                    value="{{ $grupoActual->id }}" type="checkbox"
                    class="switch-input" />
                  <span class="switch-toggle-slider">
                    <span class="switch-on">SI</span>
                    <span class="switch-off">NO</span>
                  </span>
                  <span class="switch-label mx-auto"><b> Grupo: </b>
                    {{ $grupoActual->nombre }}</span>
                </label>
                @endforeach
              </div>
            </div>
            @endif
            <!-- / Desvinculacion de grupos donde asiste-->

            <div class="col-12 text-center">
              <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
              <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                aria-label="Close">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ modalConfirmacionDesviculacionGruposDondeAsiste -->
  @endif
</div>



@assets
@vite('resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss')
@vite('resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')
@vite('resources/assets/js/extended-ui-perfect-scrollbar.js')
@endassets



@script
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

  Livewire.on('cerrarModal', () => {
    $('#' + event.detail.nombreModal).modal('hide');
  });

  Livewire.on('abrirModal', () => {
    $('#' + event.detail.nombreModal).modal('show');
  });
</script>
@endscript
