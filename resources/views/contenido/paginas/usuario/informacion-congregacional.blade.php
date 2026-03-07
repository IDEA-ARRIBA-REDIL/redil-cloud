@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Información congregacional')


@section('vendor-style')
@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',

])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/js/app.js',

])
@endsection


@section('page-script')

<script type="module">
  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });

    $(".fecha-picker").flatpickr({
      dateFormat: "Y-m-d"
    });
  });
</script>

<script>
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron = /[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }
</script>

<script type="module">
  $('.modificarEstadoProceso').click(function() {

    var estados = <?php echo json_encode($estados);?>;
    let dataEstado = $(this).attr("data-estado");
    let pasoId = $(this).attr("data-id");

    $("#fecha_paso_" + pasoId).attr("disabled", false);
    if (dataEstado == 1) {
      var dataEstadoNuevo = 2;
    } else if (dataEstado == 2) {
      var dataEstadoNuevo = 3;
    } else if (dataEstado == 3) {
      var dataEstadoNuevo = 1;
      $("#fecha_paso_" + pasoId).attr("disabled", true);
    }

    $("#estado_paso_" + pasoId).val(dataEstadoNuevo);

    for (let i in estados) {
      if (estados[i].id == dataEstado) {
        $(this).removeClass("btn-" + estados[i].color);
        $("#icono_paso_" + pasoId).removeClass("timeline-indicator-" + estados[i].color);
      }
    }

    for (let j in estados) {
      if (estados[j].id == dataEstadoNuevo) {
        $(this).attr("data-estado", dataEstadoNuevo);
        $(this).addClass("btn-" + estados[j].color);
        $("#icono_paso_" + pasoId).addClass("timeline-indicator-" + estados[j].color);
        $(this).html(estados[j].nombre);
      }
    }

  });
</script>

<script type="module">
  $('#formulario').submit(function(){
    $('.btnGuardar').attr('disabled','disabled');

    Swal.fire({
      title: "Espera un momento",
      text: "Ya estamos guardando...",
      icon: "info",
      showCancelButton: false,
      showConfirmButton: false,
      showDenyButton: false
    });
  });
</script>

@endsection

@section('content')


<h4 class="mb-1 fw-semibold text-primary">Información congregacional</h4>
<p class="mb-4 text-black">Aquí podrás gestionar toda la información de <b>{{$usuario->nombre(3)}}</b> relacionada con la congregación.</p>

@include('layouts.status-msn')

<!-- Navbar pills -->
<div class="row">
  <div class="col-md-12">
    <div class="card mb-10 p-1 border-1">
      <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">

        @can('modificarUsuarioPolitica', [App\Models\User::class, $formulario])
          <li class="nav-item flex-fill">
            <a id="tap-principal" href="{{ route('usuario.modificar', [$formulario, $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="principal">
              <i class='ti-xs ti ti-user-check me-2'></i> Datos principales
            </a>
          </li>
        @endcan

        @can('informacionCongregacionalPolitica', $usuario)
          <li class="nav-item flex-fill">
            <a id="tap-info-congregacional" href="{{ route('usuario.informacionCongregacional', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light active" data-tap="info-congregacional">
              <i class='ti-xs ti ti-building-church me-2'></i> Información congregacional
            </a>
          </li>
        @endif

        @can('geoasignacionUsuarioPolitica', $usuario)
          <li class="nav-item flex-fill">
            <a id="tap-geoasignacion" href="{{ route('usuario.geoAsignacion', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="geoasignacion">
              <i class='ti-xs ti ti-map-pin-2 me-2'></i>Geo asignación
            </a>
          </li>
        @endif

        @can('relacionesFamiliaresUsuarioPolitica', $usuario)
          <li class="nav-item flex-fill">
            <a id="tap-familia" href="{{ route('usuario.relacionesFamiliares', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="familia">
              <i class='ti-xs ti ti-home-heart me-2'></i>Relaciones familiares
            </a>
          </li>
        @endif
      </ul>
    </div>
  </div>
</div>
<!--/ Navbar pills -->

<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route( 'usuario.actualizarInformacionCongregacional', $usuario->id) }}" enctype="multipart/form-data">
  @csrf
  @method('PATCH')


  <div class="row">
    <div class="col-md-6">
      <!-- tipoUsuario -->
      @if($rolActivo->hasPermissionTo('personas.panel_tipos_asistente'))
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <p class="card-text text-uppercase fw-bold"><i class="ti ti-building-church  ms-n1 me-2"></i>Información principal</p>
        </div>
        <div class="card-body pb-2">
          <!-- Tipo de asistente -->
          @if($rolActivo->hasPermissionTo('personas.editar_tipos_asistente'))
          <div class="mb-3">
            <label class="form-label" for="tipo_identificacion">
              Tipo usuario
            </label>
            <select id="tipo_usuario" name="tipo_usuario" class="select2 form-select" data-allow-clear="true">
              <option value="" selected>Ninguno</option>
              @foreach ($tiposUsuarios as $tiposUsuarios)
              <option value="{{$tiposUsuarios->id}}" {{ old('tipo_usuario', $usuario->tipo_usuario_id )==$tiposUsuarios->id ? 'selected' : '' }}>{{$tiposUsuarios->nombre}}</option>
              @endforeach
            </select>
            @if($errors->has('tipo_usuario')) <div class="text-danger form-label">{{ $errors->first('tipo_usuario') }}</div> @endif
          </div>
          @else
          <ul class="list-unstyled mb-0">
            <small class="card-text text-uppercase">Tipo de usuario</small>
            <li class="mb-1 mt-1 p-2 border rounded">
              <div class="d-flex align-items-start d-flex">
                <div class="d-flex align-items-center">
                  <div class="badge" style="background-color: {{$usuario->tipoUsuario->color}};">
                    <i class="ti {{$usuario->tipoUsuario->icono}} fs-1"></i>
                  </div>
                  <div class="me-3 ms-1 d-flex ">
                    <h5 class="mb-0 d-flex align-items-center">{{$usuario->tipoUsuario->nombre}}</h5>
                  </div>
                </div>

              </div>
            </li>
          </ul>
          @endif
          <!-- /Tipo de asistente-->
        </div>
      </div>
      @endif
      <!--/ tipoUsuario -->

      @if($rolActivo->hasPermissionTo('personas.panel_procesos_asistente'))
      <!-- Procesos de crecimiento -->
      <div class="card">
        <div class="card-header d-flex justify-content-between">
          <p class="card-text text-uppercase fw-bold"><i class="ti ti-versions ms-n1 me-2"></i>Procesos de crecimiento</p>
        </div>
        <div class="card-body pb-20">
          <ul class="timeline ms-1 mb-0">
            @if($rolActivo->hasPermissionTo('personas.editar_procesos_asistente'))

              @foreach($seccionesPasoDeCrecimiento as $seccion)
                <p class="card-text text-uppercase fw-bold"><i class="ti ti-list-details ms-n1 me-2"></i>{{ $seccion->nombre }}</p>
                @foreach ($seccion->pasos as $paso)
                <li class="timeline-item timeline-item-transparent ps-4">
                  <span id="icono_paso_{{$paso->id}}" class="timeline-indicator-advanced timeline-indicator-{{ $paso->clase_color }}">
                    <i class="ti ti-square rounded-circle scaleX-n1-rtl"></i>
                  </span>
                  <div class="timeline-event">
                    <div class="timeline-header">
                      <h6 class="mb-0 ml-1 fw-bold">{{ $paso->nombre }}</h6>
                      <button type="button" data-id="{{ $paso->id }}" data-estado="{{ $paso->estado_paso}}" class="modificarEstadoProceso btn btn-sm rounded-pill btn-{{ $paso->clase_color }} waves-effect waves-light">{{ $paso->estado_nombre }}</button>
                    </div>
                    <input id="fecha_paso_{{$paso->id}}" name="fecha_paso_{{$paso->id}}" value="{{ $paso->estado_fecha }}"  placeholder="YYYY-MM-DD" class="mt-2 form-control fecha-picker" type="text" {{ $paso->estado_paso != 1 ? '' : 'disabled'}} />
                    <input id="estado_paso_{{$paso->id}}" name="estado_paso_{{$paso->id}}" value="{{ $paso->estado_paso}}" class="d-none" />
                    <div class="accordion mt-1" id="accordion{{$paso->id}}">
                      <div class="card accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                          <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionPaso{{$paso->id}}" aria-expanded="true" aria-controls="accordionPaso{{$paso->id}}">
                            Detalle
                          </button>
                        </h2>
                        <div id="accordionPaso{{$paso->id}}" class="accordion-collapse collapse" data-bs-parent="#accordion{{$paso->id}}">
                          <div class="accordion-body">
                            <textarea onkeypress="return sinComillas(event)" id="detalle_paso_{{$paso->id}}" name="detalle_paso_{{$paso->id}}" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true" placeholder="">{{ $paso->detalle_paso }}</textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                </li>
                @endforeach
              @endforeach
            @else
            @foreach ($pasosDeCrecimiento as $paso)
            <li class="timeline-item timeline-item-transparent ps-4">
              <span class="timeline-point timeline-point-{{ $paso->clase_color }}"></span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0 ml-1 fw-bold">{{ $paso->nombre }}</h6>
                  <span class="badge rounded-pill bg-label-{{ $paso->clase_color }}">
                    {{ $paso->estado_nombre }}
                  </span>
                </div>
                <small class="text-muted"><i class="ti ti-calendar"></i> {{ $paso->estado_fecha ?  Carbon\Carbon::parse($paso->estado_fecha)->locale('es')->isoFormat(('DD MMMM Y')) : 'Sin dato' }}</small>
                <p class="mb-2 d-none"><b>Detalle: </b>{{ $paso->detalle_paso }}</p>

                @if($paso->detalle_paso)
                <div class="accordion mt-3" id="accordionExample">
                  <div class="card accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionOne" aria-expanded="true" aria-controls="accordionOne">
                        Detalle
                      </button>
                    </h2>

                    <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                        {{ $paso->detalle_paso }}
                      </div>
                    </div>
                  </div>
                </div>
                @endif
            </li>
            @endforeach
            @endif
          </ul>
        </div>
      </div>
      <!--/ Procesos de crecimiento -->
      @endif
    </div>

    <div class="col-md-6">
      <!-- Grupos -->
      @if($rolActivo->hasPermissionTo('personas.panel_asignar_grupo_al_asistente'))
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <p class="card-text text-uppercase fw-bold"><i class="ti ti-users-group ms-n1 me-2"></i>Grupos</p>
        </div>
        <div class="card-body pb-20">
          @livewire('Grupos.grupos-para-busqueda', [
          'id' => 'inputGrupos',
          'class' => 'col-12 col-md-12 mb-3',
          'label' => 'Seleccione el grupo donde asiste la persona',
          'conDadosDeBaja' => 'no',
          'gruposSeleccionadosIds' => $gruposDondeAsisteIds,
          'multiple' => TRUE,
          'validarPrivilegiosTipoGrupo' => TRUE,
          'tieneInformeDeVinculacion' => TRUE,
          'tieneInformeDeDesvinculacion' => TRUE,
          'usuario' => $usuario
          ])

        </div>
      </div>
      @endif
      <!--/ Grupos -->

      <!-- Tipo usuarios independientes -->
      @if($rolActivo->hasPermissionTo('personas.ver_panel_asignar_tipo_usuario'))
      <div class="card ">
        <div class="card-header d-flex justify-content-between">
          <p class="card-text text-uppercase fw-bold"><i class="ti ti-checkbox ms-n1 me-2"></i>Asignar roles independientes</p>
        </div>
        <div class="card-body pb-20">
          <div class="table-responsive">
            <table class="table table-flush-spacing">
              <tbody>
                <tr>
                  <td class=""></td>
                  <td class="text-nowrap fw-medium fw-bold text-center">
                    ¿Asignar?
                  </td>
                </tr>
                @foreach( $rolesNoDependientes as $rol)
                <tr>
                  <td class="text-nowrap fw-medium">{{ $rol->name }}</td>
                  <td class="text-center">
                    <label class="switch switch-lg">
                      <input id="rolIndependiente{{$rol->id}}" name="rolIndependiente{{$rol->id}}" @if($rol->tiene=="si") checked @endif type="checkbox" class="switch-input" />
                      <span class="switch-toggle-slider">
                        <span class="switch-on">Si</span>
                        <span class="switch-off">No</span>
                      </span>
                      <span class="switch-label"></span>
                    </label>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif
      <!--/ Tipo usuarios independientes -->
    </div>
  </div>


  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2" >Guardar</button>
    </div>
  </div>
  <!-- /botonera -->


</form>

@endsection
