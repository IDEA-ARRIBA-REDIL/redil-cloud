@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Relaciones Familiares')

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
<script type="module">
  window.abrirModalActualizarPariente= function(relacionId,usuarioId)
  {
    Livewire.dispatch('abrirModalActualizarPariente', { relacionId: relacionId , usuarioId: usuarioId} );
  }

  ///confirmación para eliminar tema
  $('.confirmacionEliminar').on('click', function () {
    let nombre = $(this).data('nombre');
    let pariente = $(this).data('id');

    Swal.fire({
      title: "¿Estás seguro que deseas eliminar la relación familiar</b>?",
      html: "Esta acción no es reversible.",
      icon: "warning",
      showCancelButton: false,
      confirmButtonText: 'Si, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#eliminarRelacion').attr('action',"/usuario/"+pariente+"/eliminar-relacion-familiar");
        $('#eliminarRelacion').submit();
      }
    })
  });
</script>
@endsection

@section('content')


@include('layouts.status-msn')

<h4 class="mb-1 fw-semibold text-primary">Relaciones familiares</h4>
<p class="mb-4 text-black">Aquí podras gestionar las relaciones familiares de <b>{{ $usuario->nombre(3) }}</b></p>

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
              <a id="tap-info-congregacional" href="{{ route('usuario.informacionCongregacional', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="info-congregacional">
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
              <a id="tap-familia" href="{{ route('usuario.relacionesFamiliares', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light active" data-tap="familia">
                <i class='ti-xs ti ti-home-heart me-2'></i>Relaciones familiares
              </a>
            </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
<!--/ Navbar pills -->

<div class="row">

  <div class=" col-lg-12 col-md-7 col-xs-12">

    <div class="card mb-4">

      <div class="card-body">
        <div class="d-flex justify-content-end mb-3 mt-5">
          @if(isset($userId))
            @if( $rolActivo->hasPermissionTo('familiar.ver_boton_nueva_relacion_familiar'))
            <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-5 me-1" data-bs-toggle="offcanvas" data-bs-target="#modalNuevaRelacion">
              <span class="d-none d-md-block fw-semibold">Nueva relación </span>
              <i class="ti ti-user-plus pb-1"></i>
            </button>
            @endif
          @endif
        </div>
        <div class="row g-4">
          @if(count($parientes) > 0)
          @foreach($parientes as $pariente)
          <div class="col-lg-4 col-md-12 col-xs-12">
            <div class="card border rounded">

              <div class="dropdown btn-pinned border rounded p-1">
                <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  @if( $rolActivo->hasPermissionTo('familiar.opcion_modificar_relacion_familiar'))
                  <li>
                    <a href="javascript:void(0);" onclick="abrirModalActualizarPariente('{{$pariente->id}}', '{{$userId}}')" class="dropdown-item">
                      <span class="me-2">Editar relación</span>
                    </a>
                  </li>
                  @endif
                  @if( $rolActivo->hasPermissionTo('familiar.opcion_eliminar_relacion_familiar'))
                  <hr class="dropdown-divider">
                  <li>
                    <a data-id="{{$pariente->id}}" data-nombre="" class=" confirmacionEliminar dropdown-item text-danger">
                      <span class="me-2">Eliminar relación</span>
                    </a>
                  </li>
                  @endif
                </ul>
              </div>

              <div class="card-body text-center">
                <div class="mx-auto my-3">
                  <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$pariente->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$pariente->foto }}" alt="foto {{$pariente->primer_nombre}}" class="rounded-circle w-px-100" />
                </div>

                <span class="pb-1"><span></span><b>Relación:</b> {{ $usuario->genero == 0 ? $pariente->nombre_masculino : $pariente->nombre_femenino }} de </span>
                <h4 class="mb-1 lh-sm card-title">{{ $pariente->nombre(3) }}</h4>

                <div class="d-flex align-items-center justify-content-center my-3 gap-2">
                  <span>¿Soy el responsable?</span>
                  @if($pariente->es_el_responsable)
                  <a href="javascript:;" class="me-1"><span class="badge bg-label-success">Si</span></a>
                  @else
                  <a href="javascript:;"><span class="badge bg-label-secondary">No</span></a>
                  @endif
                </div>
              </div>
            </div>
          </div>
          @endforeach
          @else
          <div class="py-4">
            <center>
              <i class="ti ti-home-heart fs-1 pb-1"></i>
              <h6 class="text-center">No hay personas en tu grupo familiar</h6>
            </center>
          </div>
          @endif
        </div>
      </div>

    </div>

  </div>

    <!-- SECCIÓN MODALES -->
    @if($userId)
    <!-- offcanvas crear nueva relación -->
    <form class="forms-sample" method="GET" action="{{ route('familias.crear') }}">
      <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalNuevaRelacion" aria-labelledby="modalNuevaRelacionLabel">
          <div class="offcanvas-header my-1 px-8">
              <h4 class="offcanvas-title fw-bold text-primary" id="modalNuevaRelacionLabel">
                Crear relación familiar
              </h4>
              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>

          <div class="offcanvas-body pt-6 px-8">
           <div class="mb-4">
              <span class="text-black ti-14px mb-4">LLena toda la información y da en el botón guardar.</span>
            </div>

            <div class="row">
              <!-- Familiar principal -->
              <div class="col-12 mb-3">
                @livewire('Usuarios.usuarios-para-busqueda', [
                  'id' => 'buscador_asistente_modal',
                  'tipoBuscador' => 'unico',
                  'conDadosDeBaja' => 'no',
                  'class' => '',
                  'label' => 'Selecciona el pariente',
                  'queUsuariosCargar'=>'todos',
                  'modulo' => 'familiar-secundario'
                ])
              </div>

              <!--/ Familiar principal -->
              <div class="col-lg-12 mt-3">
                <div class="mb-3">
                  <label class="form-label">¿Qué relación tiene <b>{{$usuario->nombre(3)}}</b> con el pariente?</label>
                  <select id="tipoParentesco" name="tipoParentesco" class="form-select" tabindex="0" id="roleEx7">
                    <option value="">Selecciona una opción</option>
                        @foreach($tiposParentesco as $tipo)
                              <option value="{{$tipo->id}}">{{$tipo->nombre}}</option>
                      @endforeach
                  </select>
                </div>
              </div>

              <div class="col-lg-12 col-md-12  col-sm-12  mt-3">
                <div class="mb-3">
                  <label class="form-label">Responsabilidad</label>
                  <select id="responsabilidad" name="responsabilidad" class="form-select" tabindex="0" id="roleEx7">
                    <option value="1">Ninguna</option>
                    <option value="2"><b>{{$usuario->nombre(3)}}</b> es el responsable del pariente</option>
                    <option value="3"> El pariente es el responsable de <b>{{$usuario->nombre()}} </b></option>
                  </select>
                </div>
              </div>

              @if(isset($userId))
              <input id="parientePrincipal" name="parientePrincipal" class="d-none" value="{{$userId}}">
              @endif
            </div>
        </div>

          <div class="offcanvas-footer p-5 border-top border-2 px-8">
              <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
              <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
          </div>
      </div>
    </form>
    @endif

    @livewire('Familias.actualizar-pariente')

    <form id="eliminarRelacion" method="POST" action="">
      @csrf
    </form>

</div>


@endsection
