<div class="row g-2">
  <div class="col-12 offset-md-3 col-md-6 mb-4">
    <div class="input-group input-group-merge ">
      <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
      <input wire:model.live.debounce.30ms="busqueda" type="text" class="form-control" placeholder="Buscar integrante por nombre, email, identificación" aria-label="Buscar grupo" aria-describedby="basic-addon-search31" spellcheck="false" data-ms-editor="true">
    </div>
  </div>
    @if($integrantes->count() > 0)

      @foreach($integrantes as $persona)


        <div class="col equal-height-col col-lg-4 col-md-4 col-sm-6 col-12">
            <!-- esta linea es para igualar en altura todas las col e igualar la altura de las cards -->
          <div  class="h-100 card border rounded">
            <img class="card-img-top object-fit-cover" style="height: 100px;" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img//usuarios/banner-usuario/'.$persona->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img//usuarios/banner-usuario/'.$persona->portada)  }}" alt="portada {{$persona->primer_nombre}}" />

            <div class="card-body">

              <div class="user-profile-header d-flex flex-row text-start mb-2 ">
                <div class="flex-grow-1 mt-n5 mx-auto text-start">
                  @if($persona->foto == "default-m.png" || $persona->foto == "default-f.png")
                  <div class="avatar avatar-xl">
                    <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $persona->inicialesNombre() }} </span>
                  </div>
                  @else
                  <div class="avatar avatar-xl">
                    <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto }}" alt="{{ $persona->foto }}" class="avatar-initial rounded-circle border border-3 border-white bg-info">
                  </div>
                  @endif
                </div>
              </div>

              <div class="d-flex justify-content-between mb-5">
                <div class="d-flex align-items-start">
                  <div class="me-2 mt-1">
                    <h5 class="mb-1 fw-semibold text-black lh-sm"> {{ $persona->nombre(3) }} </h5>
                    <div class="client-info text-black">
                      <b>Edad:</b> {{ $persona->edad() > 1 ?  $persona->edad().' años' : $persona->edad().' año'}}
                    </div>
                  </div>
                </div>
                <div class="ms-auto">

                  <div class="dropdown zindex-2 p-1 float-end">
                    <a href="{{ route('usuario.perfil', $persona) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                      <i class="ti ti-user-check m-1 ti-sm"></i>
                    </a>
                  </div>
                </div>
              </div>


              <div class="d-flex my-2 mb-5">
                <span class="badge rounded-pill px-6 fw-light " style="background-color: {{ $persona->tipoUsuario->color }}">
                  <i class="{{ $persona->tipoUsuario->icono }} fs-6"></i> <span class="text-white"> {{ $persona->tipoUsuario->nombre }}</span>
                </span>
              </div>

              <div class="d-flex flex-column mb-3">
                  @if(isset($persona->tipoUsuario->id))
                    <!-- Actividad en grupo -->
                    <div class="d-flex flex-row  mb-3">
                      <i class="ti ti-users-group text-black"></i>
                      <div class="d-flex flex-column">
                        <small class="text-black ms-1">Actividad en grupos:</small>
                        @if($persona->tipoUsuario->seguimiento_actividad_grupo==FALSE)
                        <small class="fw-semibold ms-1 text-black">Sin seguimiento</small>
                        @else
                          @if($persona->estadoActividadGrupos())
                          <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_grupo}}">Última actividad el {{ Carbon\Carbon::parse($persona->ultimo_reporte_grupo)->format('Y-m-d') }}</small>
                          @else
                          <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_grupo}}">Inactivo desde {{ Carbon\Carbon::parse($persona->ultimo_reporte_grupo)->format('Y-m-d') }}</small>
                          @endif
                        @endif
                      </div>
                    </div>

                    <!-- Actividad en reuniones -->
                    <div class="d-flex flex-row  mb-3">
                      <i class="ti ti-building-church text-black"></i>
                      <div class="d-flex flex-column">
                        <small class="text-black ms-1">Actividad en reuniones:</small>
                        @if($persona->tipoUsuario->seguimiento_actividad_reunion==FALSE)
                        <small class="fw-semibold ms-1 text-black">Sin seguimiento</small>
                        @else
                          @if($persona->estadoActividadReuniones())
                          <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_grupo}}">Última actividad el  {{ Carbon\Carbon::parse($persona->ultimo_reporte_grupo)->format('Y-m-d') }}</small>
                          @else
                          <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_grupo}}">Inactivo desde {{ Carbon\Carbon::parse($persona->ultimo_reporte_grupo)->format('Y-m-d') }}</small>
                          @endif
                        @endif
                      </div>
                    </div>

                    <!-- Actividad en escuelas -->
                    <div class="d-flex flex-row  mb-3">
                      <i class="ti ti-school text-black"></i>
                      <div class="d-flex flex-column">
                        <small class="text-black ms-1">Actividad en escuelas:</small>
                        @if($persona->tipoUsuario->seguimiento_actividad_reunion==FALSE)
                        <small class="fw-semibold ms-1 text-black">Sin seguimiento</small>
                        @else
                          @if($persona->estadoActividadReuniones())
                          <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_grupo}}">Última actividad el  {{ Carbon\Carbon::parse($persona->ultimo_reporte_grupo)->format('Y-m-d') }}</small>
                          @else
                          <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_grupo}}">Inactivo desde {{ Carbon\Carbon::parse($persona->ultimo_reporte_grupo)->format('Y-m-d') }}</small>
                          @endif
                        @endif
                      </div>
                    </div>

                    <!-- Actividad en tiempo con Dios -->
                    <div class="d-flex flex-row  mb-3">
                      <i class="ti ti-pray text-black"></i>
                      <div class="d-flex flex-column">
                        <small class="text-black ms-1">Racha tiempo con Dios:</small>
                        <small class="fw-semibold ms-1 text-black">{{ $persona->cantidadRachaDiaria() == 0 ? 'Nunca realizado' : ( $persona->cantidadRachaDiaria() == 1 ? $persona->cantidadRachaDiaria().' día' : $persona->cantidadRachaDiaria().' días') }} </small>
                      </div>
                    </div>

                    <!-- Actividad en rueda de la vida -->
                    <div class="d-flex flex-row  mb-3">
                      <i class="ti ti-circle-dashed-check text-black"></i>
                      <div class="d-flex flex-column">
                        <small class="text-black ms-1">Actividad en rueda de la vida:</small>
                        <small class="fw-semibold ms-1 text-black"> {{ $persona->ultimaRuedaDeLaVida() }} </small>

                      </div>
                    </div>
                  @endif

                <!-- Servicios prestados en grupos  -->
                @if($persona->ultimoTipoServicioGrupo())
                <div class="d-flex flex-row  mb-3">
                  <i class="ti ti-circle-check text-black"></i>
                  <div class="d-flex flex-column">
                    <small class="text-black ms-1">Servicios prestados en grupos:</small>
                    <small class="fw-semibold ms-1 text-black">{{ $persona->ultimoTipoServicioGrupo()->nombre }}</small>
                  </div>
                </div>
                @endif
              </div>

            </div>
          </div>
        </div>
      @endforeach
      @elseif($busqueda == '')
        <div class="py-4 border rounded mt-2">
          <center>
            <i class="ti ti-users ti-xl pb-1"></i>
            <h6 class="text-center">¡Ups! este grupo no posee integrantes.</h6>
            @if($rolActivo->hasPermissionTo('grupos.pestana_anadir_integrantes_grupo'))
              <a href="{{ route('grupo.gestionarIntegrantes',$grupo) }}" target="_blank" class="btn btn-primary pendiente" data-bs-toggle="tooltip" aria-label="Gestionar integrantes" data-bs-original-title="Este grupo no tiene integrantes, agrégalos aquí">
                <i class="ti ti-user-plus me-2 ti-sm"></i> Gestionar integrantes
              </a>
            @endif
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
