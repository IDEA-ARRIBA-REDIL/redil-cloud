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
      let urlDestino = "{{ route('grupo.perfil.integrantes', ['grupo' => 'ID_REEMPLAZABLE', 'encargado' => $encargado->id ?? null]) }}";
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


  @livewire('Grupos.modal-baja-alta-grupo')
  @livewire('ReporteGrupos.modal-nuevo-reporte')

  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12 ">
      <div class="nav-align-top">
        <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0 justify-content-center gap-2">
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasGrupo', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-chart-bar me-1'></i> Estadisticas del grupo </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasCobertura', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-chart-dots-2 me-1'></i> Estadisticas cobertura </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-info-square-rounded me-1'></i> Información básica</a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.integrantes', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="tapControl nav-link waves-effect waves-light active"><i class='ti-xs ti ti-users me-1'></i> Integrantes </a></li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->

  <div id="div-principal" class="row">

    <div class="col-lg-12 col-md-12">
      @livewire('Grupos.listado-integrantes-grupo', [
        'grupo' => $grupo,
      ])
    </div>

  </div>
@endsection
