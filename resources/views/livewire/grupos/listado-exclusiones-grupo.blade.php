<div class="">

    <div class="row mt-10">
      <div class="col-9 col-md-4">
        <div class="input-group input-group-merge bg-white">
          <input wire:model.live.debounce.30ms="busqueda" id="buscar" name="buscar" type="text" value="{{$busqueda}}" class="form-control"  placeholder="Buscar integrante por nombre" aria-describedby="btnBusqueda">
          @if($busqueda)
          <span id="borrarBusquedaPorPalabra" wire:click="limpiarBusqueda" class="input-group-text"><i class="ti ti-x"></i></span>
          @else
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          @endif
        </div>
      </div>

      <div class="col-3 col-md-8 d-flex justify-content-end">
          <button data-bs-toggle="offcanvas" data-bs-target="#addNuevaExclusion" class="btn rounded-pill float-end btn-primary rounded-pill waves-effect waves-light"><span class="d-none d-md-block fw-semibold">Nueva exclusión</span><i class="ti ti-plus ms-1"></i> </button>
      </div>
    </div>

    <div class="row equal-height-row g-4 mt-10">


      @if($exclusiones->count() > 0)
        @foreach($exclusiones as $exclusion)
          <div class="col equal-height-col col-lg-4 col-md-4 col-sm-6 col-12">
            <!-- esta linea es para igualar en altura todas las col e igualar la altura de las cards -->
            <div  class="h-100 card border rounded">
              <div class="card-body">

                <div class="user-profile-header d-flex flex-row text-start mb-2 ">
                  <div class="flex-grow-1 mt-8 mx-auto text-start">
                    @if($exclusion->foto == "default-m.png" || $exclusion->foto == "default-f.png")
                    <div class="avatar avatar-xl">
                      <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $exclusion->inicialesNombre() }} </span>
                    </div>
                    @else
                    <div class="avatar avatar-xl">
                      <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$exclusion->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$exclusion->foto }}" alt="{{ $exclusion->foto }}" class="avatar-initial rounded-circle border border-3 border-white bg-info">
                    </div>
                    @endif
                  </div>
                </div>

                <div class="d-flex justify-content-between mb-5">
                  <div class="d-flex align-items-start">
                    <div class="me-2 mt-1">
                      <h5 class="mb-1 fw-semibold text-black lh-sm"> {{ $exclusion->nombre(3) }} </h5>
                      <div class="d-flex my-2">
                        <span class="badge rounded-pill px-6 fw-light " style="background-color: {{ $exclusion->tipoUsuario->color }}">
                          <i class="{{ $exclusion->tipoUsuario->icono }} fs-6"></i> <span class="text-white"> {{ $exclusion->tipoUsuario->nombre }}</span>
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="ms-auto">
                    <a class="dropdown-item" href="#"  wire:click="eliminar({{$exclusion->id}})">
                    <i class="ti ti-trash m-1 ti-sm"></i></a>
                  </div>
                </div>

                <div class="d-flex flex-row  mb-3">
                  <i class="ti ti-users-group text-black"></i>
                  <div class="d-flex flex-column">
                    <small class="text-black ms-1">Excluido del grupo:</small>
                    <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" >{{ $exclusion->nombreGrupo }}</small>
                  </div>
                </div>

              </div>
            </div>
          </div>
        @endforeach


      @elseif($busqueda == '')
        <div class="py-4 border rounded mt-2">
          <center>
            <i class="ti ti-alert-square-rounded ti-xl pb-1"></i>
            <h6 class="text-center">¡Ups! No hay exclusiones creadas.</h6>
          </center>
        </div>
      @else
        <div class="py-4 border rounded mt-2">
          <center>
            <i class="ti ti-search ti-xl pb-1"></i>
            <h6 class="text-center">¡Ups! la busqueda no arrojo ningun resultado.</h6>
          </center>
        </div>
      @endif


    </div>


  <!--/ Add nueva exclusion  -->
  <form class="forms-sample" method="POST" action="{{ route('grupo.crearExclusion') }}">
    <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="addNuevaExclusion" aria-labelledby="addNuevaExclusionLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="addNuevaExclusionLabel">
              Nueva exclusión
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body pt-6 px-8">
          <div class="mb-4">
            <span class="text-black ti-14px mb-4">Por favor, selecciona el grupo y el usuario.</span>
          </div>

          <div class="row">
            @csrf
            @livewire('Grupos.grupos-para-busqueda',[
              'id' => 'grupo',
              'class' => 'col-12 col-md-12 mb-3',
              'label' => 'Selecciona el grupo',
              'conDadosDeBaja' => 'no',
              'grupoSeleccionadoId' => ''
            ])

            @livewire('Usuarios.usuarios-para-busqueda', [
              'id' => 'usuario',
              'class' => 'col-12 col-md-12 mb-3',
              'label' => 'Seleccione el usuario',
              'tipoBuscador' => 'unico',
              'queUsuariosCargar' => $queUsuariosCargar,
              'conDadosDeBaja' => 'no',
              'modulo' => 'exclusiones-grupo'
            ])
          </div>
      </div>

        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>
  <!--/ Add nueva exclusion  -->

</div>
