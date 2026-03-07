@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Perfil del grupo')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
])
@endsection

@section('page-script')

@if($grupos && $grupos->count() > 0)
<script>
  // Nos aseguramos de que el DOM esté completamente cargado antes de ejecutar el script
  document.addEventListener('DOMContentLoaded', function () {
    // 1. Obtenemos el elemento <select> por su ID
    const grupoSelect = document.getElementById('grupoSeleccionado');

    // 2. Añadimos un "oyente" para el evento 'change'
    grupoSelect.addEventListener('change', function () {
      const nuevoGrupoId = this.value;
      let urlDestino = "{{ route('grupo.perfil', ['grupo' => 'ID_REEMPLAZABLE', 'encargado' => $encargado->id ?? null]) }}";
      urlDestino = urlDestino.replace('ID_REEMPLAZABLE', nuevoGrupoId);
      window.location.href = urlDestino;
    });
  });
</script>
@endif

<script>
  function darBajaAlta(grupoId, tipo)
  {
    Livewire.dispatch('abrirModalBajaAlta', { grupoId: grupoId, tipo: tipo });
  }

  function eliminacion(grupoId)
  {
    Livewire.dispatch('confirmarEliminacion', { grupoId: grupoId });
  }
</script>

<script>
  $(".btnModalNuevoReporte").click(function() {
    fechaAutomatica = $(this).data('fecha-automatica');
    grupoId = $(this).data('id');
    Livewire.dispatch('abrirModalNuevoReporte', { fechaAutomatica: fechaAutomatica, grupoId: grupoId });
  });
</script>
@endsection

@section('content')

  @include('layouts.status-msn')

  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-6">
        <div class="user-profile-header-banner">
          <img src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/'.$grupo->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png')}}" alt="Banner image" class="rounded-top">
        </div>
        <div class="user-profile-header d-flex flex-column flex-md-row text-sm-start text-center mb-5">
          <div class="flex-shrink-0 mt-n2 mx-0 mx-auto">
            <div class="card rounded-pill icon-card text-center mb-0 mx-3 p-2" style="background-color: {{ $grupo->tipoGrupo->color }}">
              <div class="card-body text-white"> <i class="ti ti-users-group ti-xl"></i>
              </div>
            </div>
          </div>
          <div class="flex-grow-1 mt-3 mt-md-5">
            <div class="d-flex align-items-md-end align-items-md-start align-items-center justify-content-md-between justify-content-start mx-2 flex-md-row flex-column gap-4">
              <div class="user-profile-info">
                @if($grupos && $grupos->count() > 0)
                  <select id="grupoSeleccionado" name="grupoSeleccionado" class="form-select mt-md-7">
                    @foreach ($grupos as $gr)
                    <option value="{{ $gr->id }}" {{ $gr->id == $grupo->id ? 'selected' : ''}} >{{ $gr->nombre }}</option>
                    @endforeach
                  </select>
                @else
                <h4 class="mb-0 mt-md-4 fw-bold">{{ $grupo->nombre }}</h4>
                @endif
                <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-md-start justify-content-center gap-4 my-0 mt-1">
                  <li class="list-inline-item d-flex gap-2 align-items-center">
                    <span class="fw-medium"> {{ $grupo->tipoGrupo->nombre }}</span>
                  </li>
                </ul>
              </div>
              <div id="divBotonOpciones" class="d-flex flex-row ">

                <div class="me-2">
                  @if($grupo->varificarProcesoReporte() == 'botonCrearDeshabilitado' || $grupo->varificarProcesoReporte() == 'botonEditarDeshabilitado')
                    @if($grupo->varificarProcesoReporte() == 'botonCrearDeshabilitado')
                    <button disabled class=" btn btn-sm rounded-3 w-100 btn-primary waves-effect waves-light mx-1 p-2 fw-light" >Crear reporte </button>
                    @elseif ($grupo->varificarProcesoReporte() == 'botonEditarDeshabilitado')
                    <button disabled class=" btn btn-sm rounded-3 w-100 btn-primary waves-effect waves-light mx-1 p-2 fw-light">Editar reporte </button>
                    @endif
                  @else
                    @if($grupo->varificarProcesoReporte() == 'botonCrearReporte')
                    <button data-id="{{ $grupo->id }}" data-fecha-automatica="{{ $grupo->verificaFechaAutomaticaReporte() }}" class="btnModalNuevoReporte btn btn-sm rounded-3 w-100 btn-primary waves-effect waves-light mx-1 p-2 fw-light" >Crear reporte </button>
                    @elseif ($grupo->varificarProcesoReporte() == 'botonEditarReporte')
                    <a href="{{ route('reporteGrupo.asistencia', $grupo->ultimoReporteDelGrupo()->id ) }}" class="btn btn-sm rounded-3 w-100 btn-primary waves-effect waves-light mx-1 p-2 fw-light">Editar reporte </a>
                    @endif
                  @endif
                </div>

                <div class="dropdown">
                  <button type="button" class="btn btn-sm p-2 rounded-3 btn-outline-primary waves-effectdropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="mx-1">Opciones</span>
                    <i class="pl-5 ti ti-edit"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">

                    @if($grupo->dado_baja == 0)
                      @if($rolActivo->hasPermissionTo('grupos.opcion_modificar_grupo'))
                        <li><a class="dropdown-item" href="{{ route('grupo.modificar', $grupo)}}">Modificar</a></li>
                      @endif

                      @if($rolActivo->hasPermissionTo('grupos.opcion_excluir_grupo'))
                        <form id="excluirGrupo" method="POST" action="{{ route('grupo.excluir', ['grupo' => $grupo]) }}">
                          @csrf
                          <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('excluirGrupo').submit();" >Excluir grupo</a></li>
                        </form>
                      @endif
                      <hr class="dropdown-divider">
                      @if($rolActivo->hasPermissionTo('grupos.opcion_dar_de_baja_alta_grupo'))
                        <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="darBajaAlta('{{$grupo->id}}', 'baja')">Dar de baja</a></li>
                      @endif

                      @if($rolActivo->hasPermissionTo('grupos.opcion_eliminar_grupo'))
                      <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="eliminacion('{{$grupo->id}}')">Eliminar</a></li>
                      @endif
                    @else
                      @if($rolActivo->hasPermissionTo('grupos.opcion_dar_de_baja_alta_grupo'))
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="darBajaAlta('{{$grupo->id}}', 'alta')">Dar de alta</a></li>
                      @endif
                    @endif

                  </ul>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Header -->


  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12 ">
      <div class="nav-align-top">
        <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0 justify-content-center gap-2">
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasGrupo', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-chart-bar me-1'></i> Estadisticas del grupo </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasCobertura', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-chart-dots-2 me-1'></i> Estadisticas cobertura </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="tapControl nav-link waves-effect waves-light active"><i class='ti-xs ti ti-info-square-rounded me-1'></i> Información básica</a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.integrantes', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-users me-1'></i> Integrantes </a></li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->

  <div id="div-principal" class="row">


    <div class="col-lg-6 col-md-6">
      <!-- Información principal -->
      <div class="card mb-4">
        <div class="card-header align-items-center">
          <p class="card-text text-uppercase fw-bold"> Información </p>
        </div>
        <div class="card-body pb-3">

          <div class="row mb-4">
            <div class="col-12 d-flex flex-column">
              <small class="text-black">Día de reunión</small>
              <small class="fw-semibold text-black ">{{ Helper::obtenerDiaDeLaSemana($grupo->dia) ? Helper::obtenerDiaDeLaSemana($grupo->dia) : 'Día no indicado' }}</small>
              <hr class=" border-2">
            </div>

            <div class="col-12 d-flex flex-column">
              <small class="text-black">Hora de reunión</small>
              <small class="fw-semibold text-black ">{{ Carbon\Carbon::parse($grupo->hora)->format(('g:i a')) }}</small>
              <hr class=" border-2">
            </div>

            <div class="col-12 d-flex flex-column">
              <small class="text-black">Cantidad de integrantes</small>
              <small class="fw-semibold text-black ">{{ $grupo->asistentes()->select('users.id')->count() }}</small>
              <hr class=" border-2">
            </div>

            <a href="{{ route('grupo.graficoDelMinisterio', ['idNodo' => 'G-'.$grupo->id]  ) }}" target="_blank" class="" data-bs-toggle="tooltip" aria-label="Ver gráfico ministerial " data-bs-original-title="Ver gráfico ministerial">
            <div class="col-12 d-flex flex-column">
              <small class="text-black">Gráfico ministerial</small>
              <small class="fw-semibold text-black ">Ver gráfico</small>
              <hr class=" border-2">
            </div>
            </a>

            <div class="col-12 d-flex flex-column">
              <small class="text-black">Sede</small>
              <small class="fw-semibold text-black ">{{ $grupo->sede ? $grupo->sede->nombre : 'Sin dato'}}</small>
              <hr class=" border-2">
            </div>

            <div class="col-12 d-flex flex-column">
              <small class="text-black">Teléfono</small>
              <small class="fw-semibold text-black ">{{ $grupo->telefono ? $grupo->telefono : 'Sin dato'}}</small>
              <hr class=" border-2">
            </div>

            <div class="col-12 d-flex flex-column">
              <small class="text-black">Dirección</small>
              <small class="fw-semibold text-black ">{{ $grupo->direccion ? $grupo->direccion : 'Sin dato'}}</small>
              <hr class=" border-2">
            </div>

            <div class="col-12 d-flex flex-column">
              <small class="text-black">{{$configuracion->label_campo_opcional1}}</small>
              <small class="fw-semibold text-black ">{{ $grupo->rhema ? $grupo->rhema : 'Sin dato'}}</small>
              <hr class=" border-2">
            </div>
          </div>
        </div>
      </div>
      <!-- Información principal /-->

      <!-- Encargados -->
      <div class="card mb-4">
        <div class="card-header align-items-center">
          <p class="card-text text-uppercase fw-bold"> Encargados y servidores</p>
        </div>
        <div class="card-body pb-3">

          <ul class="list-unstyled mb-4">
            <small class="card-text text-uppercase">Encargados</small>
            @if($encargados->count() > 0)
              @foreach($encargados as $encargado)
              <li class="mb-1 mt-1 p-2 border rounded">
                <div class="d-flex align-items-start">
                  <div class="d-flex align-items-start">
                    <div class="avatar avatar-md me-2 my-auto">
                      <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$encargado->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$encargado->foto }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div class="me-2 ms-1">
                      <h6 class="mb-0">{{ $encargado->nombre(3) }}</h6>
                      <small class="text-muted"><i class="ti {{ $encargado->tipoUsuario->icono }} text-heading fs-6"></i> {{ $encargado->tipoUsuario->nombre}}</small>
                    </div>
                  </div>

                  <div class="ms-auto pt-1 my-auto">
                    @if($rolActivo->hasPermissionTo('personas.lista_asistentes_todos'))
                    <a href="{{ route('usuario.perfil', $encargado) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                      <i class="ti ti-user-check me-2 ti-sm"></i></a>
                    </a>
                    @endif
                  </div>
                </div>
              </li>
              @endforeach
            @else
              <div class="py-4 border rounded mt-2">
                <center>
                  <i class="ti ti-user-star ti-xl pb-1"></i>
                  <h6 class="text-center">¡Ups! este grupo no tiene encargados asignados.</h6>
                  @if($rolActivo->hasPermissionTo('grupos.pestana_anadir_lideres_grupo'))
                  <a href="{{ route('grupo.gestionarEncargados',$grupo) }}" target="_blank" class="btn btn-primary pendiente" data-bs-toggle="tooltip" aria-label="Gestionar encargados" data-bs-original-title="Este grupo no tiene encargados, agrégalos aquí">
                    <i class="ti ti-user-plus me-2 ti-sm"></i> Gestionar encargados
                  </a>
                  @endif
                </center>
              </div>
            @endif
          </ul>

          @if($grupo->tipoGrupo->contiene_servidores)
          <ul class="list-unstyled mb-0">
            <small class="card-text text-uppercase">Servidores</small>
            @if($servidores->count() > 0)
              @foreach($servidores as $servidor)
              <li class="mb-1 mt-1 p-2 border rounded">
                <div class="d-flex align-items-start">
                  <div class="d-flex align-items-start">
                    <div class="avatar avatar-md me-2 my-auto">
                      <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$servidor->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$servidor->foto }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <div class="me-2 ms-1">
                      <h6 class="mb-0">{{ $servidor->nombre(3) }}</h6>
                      <small class="text-muted"><i class="ti {{ $servidor->tipoUsuario->icono }} text-heading fs-6"></i> {{ $servidor->tipoUsuario->nombre}}</small>
                      <div class="">
                        @if(count($servidor->servicios) > 0)
                          @foreach($servidor->servicios as $servicio)
                          <span class="mt-1 badge rounded-pill bg-label-primary">{{$servicio}}</span>
                          @endforeach
                        @else
                          <span class="mt-1 badge badge rounded-pill bg-label-secondary">No tiene asignado ningun servicio</span>
                        @endif
                      </div>

                    </div>
                  </div>

                  <div class="ms-auto pt-1 my-auto">
                    @if($rolActivo->hasPermissionTo('personas.lista_asistentes_todos'))
                    <a href="{{ route('usuario.perfil', $servidor) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                      <i class="ti ti-user-check me-2 ti-sm"></i></a>
                    </a>
                    @endif
                  </div>
                </div>
              </li>
              @endforeach
            @else
            <div class="py-4 border rounded mt-2">
              <center>
                <i class="ti ti-user-star ti-xl pb-1"></i>
                <h6 class="text-center">¡Ups! este grupo no tiene servidores asignados.</h6>
                @if($rolActivo->hasPermissionTo('grupos.pestana_anadir_lideres_grupo'))
                  <a href="{{ route('grupo.gestionarEncargados',$grupo) }}" target="_blank" class="btn btn-primary pendiente" data-bs-toggle="tooltip" aria-label="Gestionar servidores" data-bs-original-title="Este grupo no tiene servidores, agrégalos aquí">
                    <i class="ti ti-user-plus me-2 ti-sm"></i> Gestionar servidores
                  </a>
                @endif
              </center>
            </div>
            @endif
          </ul>
          @endif
        </div>
      </div>
      <!--/ Encargados -->
    </div>

    <div class="col-lg-6 col-md-6">
      <!-- Mapa -->
      <div class="card mb-4">
        <div class="card-header align-items-center">
          <p class="card-text text-uppercase fw-bold"> Mapa </p>
        </div>
        <div class="card-body pb-3">
            <!-- mapa -->
            @if( $grupo->latitud )
            <iframe width="100%" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q={{$grupo->latitud}},{{$grupo->longitud}}&hl=es&z=14&amp;output=embed">
            </iframe>
            @else
              <div class="py-5 border rounded">
                <center>
                  <i class="ti ti-brand-google-maps ti-xl pb-1"></i>
                  <h6 class="text-center">¡Ups! no se puede mostrar el mapa debido a que no se ha asignado la georrefencia.</h6>
                  @if($rolActivo->hasPermissionTo('grupos.pestana_georreferencia_grupo'))
                  <a href="{{ route('grupo.georreferencia',$grupo) }}" target="_blank" class="btn btn-primary pendiente" data-bs-toggle="tooltip" aria-label="Agregar georeferencia" data-bs-original-title="Este grupo no está ubicado en el mapa, por favor agrega la ubicación aquí">
                    <i class="ti ti-map-pin-plus me-2 ti-sm"></i> Agregar georreferencia
                  </a>
                  @endif
                </center>
              </div>
            @endif
            <!-- /mapa -->
        </div>
      </div>
      <!--/ Mapa -->

      <!-- Más Información -->
      <div class="card mb-4">
        <div class="card-header align-items-center">
          <p class="card-text text-uppercase fw-bold"> Más información </p>
        </div>
        <div class="card-body pb-3">

        <div class="row mb-4">
          @if($configuracion->visible_seccion_campos_extra_grupo == TRUE && $rolActivo->hasPermissionTo('grupos.visible_seccion_campos_extra_grupo') )
            @foreach($camposExtras as $campo)
              <div class="col-12 d-flex flex-column">
                <small class="text-black">{{$campo->nombre}}</small>
                <small class="fw-semibold text-black ">{{ $campo->valor ? $campo->valor : 'Sin dato'}}</small>
                <hr class=" border-2">
              </div>
            @endforeach
          @endif

          <div class="col-12 d-flex flex-column">
            <small class="text-black">{{ $configuracion->label_fecha_creacion_grupo ? $configuracion->label_fecha_creacion_grupo : 'Fecha de apertura'}}</small>
            <small class="fw-semibold text-black ">{{ $grupo->fecha_apertura ? $grupo->fecha_apertura : 'Sin dato'}}</small>
            <hr class=" border-2">
          </div>

          <div class="col-12 d-flex flex-column">
            <small class="text-black">Fecha y hora de creación</small>
            <small class="fw-semibold text-black ">{{ $grupo->created_at ? $grupo->created_at : 'Sin dato'}}</small>
            <hr class=" border-2">
          </div>

          <div class="col-12 d-flex flex-column">
            <small class="text-black">Fecha y hora de creación</small>
            <small class="fw-semibold text-black ">{{ $grupo->created_at ? $grupo->created_at : 'Sin dato'}}</small>
            <hr class=" border-2">
          </div>

          <div class="col-12 d-flex flex-column">
            <small class="text-black">Creado por</small>
            <small class="fw-semibold text-black ">{{ $grupo->usuarioCreacion ? $grupo->usuarioCreacion->nombre(3) : 'Sin dato'}}</small>
            <hr class=" border-2">
          </div>
        </div>

        </div>
      </div>
      <!--/ Más Información -->
    </div>


  </div>


  @livewire('Grupos.modal-baja-alta-grupo')
  @livewire('ReporteGrupos.modal-nuevo-reporte')

@endsection
