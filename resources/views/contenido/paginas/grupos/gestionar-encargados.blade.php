@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Grupos')

<!-- Page -->
@section('page-style')
@vite([

'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
])
@endsection

@section('page-script')
<script type="model">


</script>
@endsection

@section('content')

<div class="row mb-2">
  <ul class="nav nav-pills mb-3 d-flex justify-content-end" role="tablist">
    @if($rolActivo->hasPermissionTo('grupos.pestana_actualizar_grupo'))
    <li class="nav-item">
      <a href="{{ route('grupo.modificar',$grupo) }}">
        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
          <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1"><i class="ti ti-edit"></i></span>
          Datos principales
        </button>
      </a>
    </li>
    @endif

    @if($rolActivo->hasPermissionTo('grupos.pestana_anadir_lideres_grupo'))
    <li class="nav-item">
      <a href="{{ route('grupo.gestionarEncargados',$grupo) }}">
        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
          <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1"><i class="ti ti-user-check"></i></span>
          Encargados
        </button>
      </a>
    </li>
    @endif

    @if($rolActivo->hasPermissionTo('grupos.pestana_anadir_integrantes_grupo'))
    <li class="nav-item">
      <a href="{{ route('grupo.gestionarIntegrantes',$grupo) }}">
        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
          <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1"><i class="ti ti-users"></i></span>
          Integrantes
        </button>
      </a>
    </li>
    @endif

    @if($rolActivo->hasPermissionTo('grupos.pestana_georreferencia_grupo'))
    <li class="nav-item">
      <a href="{{ route('grupo.georreferencia',$grupo) }}">
        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
          <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1"><i class="ti ti-map-pin"></i></span>
          Georeferencia
        </button>
      </a>
    </li>
    @endif
  </ul>
</div>


<h4 class="mb-1">Gestionar encargados</h4>
<p class="mb-4">Aquí podras agregar las que dirigen un grupo y tambien las personas que tiene una función especial dentro del grupo.</p>

<div class="row">
  <div class="{{$grupo->tipoGrupo->contiene_servidores ? 'col-12 col-md-6' : 'col-12' }} ">
    <!-- Encargados -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between">
        <p class="card-text text-uppercase fw-bold"><i class="ti ti-user ms-n1 me-2"></i>Encargados</p>
      </div>
      <div class="card-body pb-20 row">
        @livewire('Usuarios.usuarios-para-busqueda', [
          'id' => 'encargados',
          'class' => 'col-12 col-md-12 mb-3',
          'label' => 'Seleccione los encargados que dirigen este grupo',
          'tipoBuscador' => 'multiple',
          'queUsuariosCargar' => $queUsuariosCargarEncargados,
          'conDadosDeBaja' => 'no',
          'modulo' => 'encargados-grupo',
          'grupo' => $grupo,
          'usuariosSeleccionadosIds' => $idsEncargadosSeleccionados,
          'validarPrivilegiosTipoGrupo' => TRUE,
          'tieneInformeDeVinculacion' => TRUE,
          'tieneInformeDeDesvinculacion' => TRUE

        ])
      </div>
    </div>
    <!--/ Encargados -->
  </div>

  @if($grupo->tipoGrupo->contiene_servidores)
  <div class=" col-12 col-md-6">
    <!-- Servidores -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between">
        <p class="card-text text-uppercase fw-bold"><i class="ti ti-user ms-n1 me-2"></i>Servidores</p>
      </div>
      <div class="card-body pb-20 row">
        @livewire('Usuarios.usuarios-para-busqueda', [
          'id' => 'servidores',
          'class' => 'col-12 col-md-12 mb-3',
          'label' => 'Seleccione los servidores',
          'tipoBuscador' => 'multiple',
          'queUsuariosCargar' => $queUsuariosCargarServidores,
          'conDadosDeBaja' => 'no',
          'modulo' => 'servidores-grupo',
          'grupo' => $grupo,
          'usuariosSeleccionadosIds' => $idsServidoresSeleccionados
        ])
      </div>
    </div>
    <!--/ Servidores -->
  </div>
  @endif
</div>


@endsection
