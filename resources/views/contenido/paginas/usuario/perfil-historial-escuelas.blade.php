@extends('layouts/layoutMaster')

@section('title', 'Perfil')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

@section('vendor-style')

@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

<!-- Page -->
@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',

])
@endsection

@section('page-style')


<style>
  .shadow-hover:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    transform: translateY(-2px);
  }
  .transition-all {
    transition: all 0.3s ease;
  }
  .accordion-button:not(.collapsed) {
    background-color: transparent !important;
    color: inherit !important;
    box-shadow: none !important;
  }
  .accordion-button::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%235d596c'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
  }
</style>
@endsection

@section('page-script')
<!-- foto portada -->
<script type="module">

  $(function () {
    'use strict';

    var croppingImagePortada = document.querySelector('#croppingImagePortada'),
      cropBtnPortada = document.querySelector('#cropSubmitPortada'),
      upload = document.querySelector('#cropperImageUploadPortada'),
      inputResultadoPortada = document.querySelector('#imagen-recortada-portada'),
      formularioPortada  =document.querySelector('#formularioPortada'),
      cropper = '';

    setTimeout(() => {
      cropper = new Cropper( croppingImagePortada, {
        zoomable: false,
        aspectRatio: 1693 / 376,
        cropBoxResizable: true
      });
    }, 1000);

    // on change show image with crop options
    upload.addEventListener('change', function (e) {
      if (e.target.files.length) {
        console.log(e.target.files[0]);
        var fileType = e.target.files[0].type;
        if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
          cropper.destroy();
          // start file reader
          const reader = new FileReader();
          reader.onload = function (e) {
            if (e.target.result) {
              croppingImagePortada.src = e.target.result;
              cropper = new Cropper(croppingImagePortada, {
                zoomable: false,
                aspectRatio: 1693 / 376,
                cropBoxResizable: true
              });
            }
          };
          reader.readAsDataURL(e.target.files[0]);
        } else {
          alert('Selected file type is not supported. Please try again');
        }
      }
    });

    // crop on click
    cropBtnPortada.addEventListener('click', function (e) {
      e.preventDefault();

      // get result to data uri
      let imgSrc = cropper
        .getCroppedCanvas({
          height: 376,
          width: 1693 // input value
        })
        .toDataURL('image/jpeg', 0.8);

      inputResultadoPortada.value = imgSrc;
      cropBtnPortada.disabled = true;
      formularioPortada.submit();
    });
  });

</script>
<!-- foto portada -->

<!-- foto perfil -->
<script type="module">

  $(function () {
    'use strict';

    var croppingImage = document.querySelector('#croppingImage'),
      cropBtn = document.querySelector('#cropSubmit'),
      upload = document.querySelector('#cropperImageUpload'),
      modalImg = document.querySelector('#modalFoto'),
      inputResultado = document.querySelector('#imagen-recortada'),
      formulario  =document.querySelector('#formularioFoto'),
      cropper = '';

    setTimeout(() => {
      cropper = new Cropper( croppingImage, {
        zoomable: false,
        aspectRatio: 1,
        cropBoxResizable: true
      });
    }, 1000);

    // on change show image with crop options
    upload.addEventListener('change', function (e) {
      if (e.target.files.length) {
        console.log(e.target.files[0]);
        var fileType = e.target.files[0].type;
        if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
          cropper.destroy();
          // start file reader
          const reader = new FileReader();
          reader.onload = function (e) {
            if (e.target.result) {
              croppingImage.src = e.target.result;
              cropper = new Cropper(croppingImage, {
                zoomable: false,
                aspectRatio: 1,
                cropBoxResizable: true
              });
            }
          };
          reader.readAsDataURL(e.target.files[0]);
        } else {
          alert('Selected file type is not supported. Please try again');
        }
      }
    });

    // crop on click
    cropBtn.addEventListener('click', function (e) {
      e.preventDefault();

      // get result to data uri
      let imgSrc = cropper
        .getCroppedCanvas({
          width: 300 // input value
        })
        .toDataURL('image/jpeg', 0.8);

      inputResultado.value = imgSrc;
      cropBtn.disabled = true;
      formularioFoto.submit();
    });
  });

</script>
<!-- foto perfil -->

  <script>
    function darBajaAlta(usuarioId, $tipo)
    {
      Livewire.dispatch('abrirModalBajaAlta', { usuarioId: usuarioId, tipo: $tipo });
    }

    function comprobarSiTieneRegistros(usuarioId)
    {
      Livewire.dispatch('comprobarSiTieneRegistros', { usuarioId: usuarioId });
    }

    function eliminacionForzada(usuarioId)
    {
      Livewire.dispatch('confirmarEliminacion', { usuarioId: usuarioId });
    }
  </script>
@endsection



@section('content')
  @include('layouts.status-msn')

  @if($configuracion->vista_perfil_usuario_clasica==false)

   @if($errors->isEmpty())
    @include('layouts.status-msn')
  @endif

  @livewire('Usuarios.modal-baja-alta')
  
  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-5">
        <div class="user-profile-header-banner ">
          <img src="{{ $usuario->banner_url }}" alt="Banner image" class="rounded-top">
          @if( $usuario->id == auth()->user()->id )
          <button type="button" style="background-color: rgba(255, 255, 255, 0.5);" class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2" data-bs-toggle="modal" data-bs-target="#modalPortada">Cambiar portada <i style="padding-left: 5px;" class="ti ti-camera"></i></button>
          @endif
        </div>
        <div class="user-profile-header d-flex flex-column flex-md-row text-md-start text-center mb-8 mx-5">
          <div class="flex-shrink-0 mt-n5 mx-md-0 mx-auto">
            @if($usuario->foto == "default-m.png" || $usuario->foto == "default-f.png")
            <div class="avatar avatar-xxl">
              <span class="avatar-initial rounded-circle border border-5 border-white bg-info"> {{ $usuario->inicialesNombre() }} </span>
              @if( $usuario->id == auth()->user()->id )
                <button class="btn btn-sm rounded-pill btn-icon btn-secondary waves-effect waves-light position-absolute bottom-0 end-0 mb-2 mr-2" data-bs-toggle="modal" data-bs-target="#modalFoto"><i class="ti ti-camera"></i></button>
              @endif
            </div>
            @else
            <div class="avatar avatar-xxl">
              <img src="{{ $usuario->foto_url }}" alt="{{ $usuario->foto }}" class="avatar-initial rounded-circle border border-5 border-white bg-info">
              @if( $usuario->id == auth()->user()->id )
                <button class="btn btn-sm rounded-pill btn-icon btn-secondary waves-effect waves-light position-absolute bottom-0 end-0 mb-2 mr-2" data-bs-toggle="modal" data-bs-target="#modalFoto"><i class="ti ti-camera"></i></button>
              @endif
            </div>
             @endif
          </div>

          <div class="flex-grow-1 mt-3 mt-md-5">
            <div class="d-flex align-items-md-end align-items-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
              <div class="user-profile-info">
                <h5 class="mb-2 mt-md-6 fw-semibold">{{ $usuario->nombre(3) }}</h5>
                <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                  <li class="list-inline-item d-flex gap-1">
                    <span class="badge  rounded-pill px-6 fw-light " style="background-color: {{ $usuario->tipoUsuario->color }}">
                      <i class="{{ $usuario->tipoUsuario->icono }} fs-6"></i> <span class="text-white"> {{ $usuario->tipoUsuario->nombre }}</span>
                    </span>
                  </li>
                </ul>
              </div>
              <div id="divBotonOpciones" class="dropdown">
                <button type="button" class="btn btn-sm p-2 rounded-3 btn-outline-primary waves-effectdropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="mx-1">Gestionar perfil</span>
                  <i class="pl-5 ti ti-edit"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">

                  @if($rolActivo->hasPermissionTo('personas.opcion_dar_de_alta_asistente'))
                    @if($usuario->trashed())
                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="darBajaAlta('{{$usuario->id}}', 'alta')">Dar de alta</a></li>
                    @endif
                  @endif

                  <!-- opcion modificar  -->
                  @if($usuario->esta_aprobado==TRUE)
                    @foreach( auth()->user()->formularios(2, $usuario->edad()) as $formulario)
                      @can('modificarUsuarioPolitica', [App\Models\User::class, $formulario])
                        <li><a class="dropdown-item" href="{{ route('usuario.modificar', [$formulario, $usuario]) }}">{{$formulario->label}}</a></li>
                      @endcan
                    @endforeach
                  @elseif ($usuario->esta_aprobado==FALSE)
                    @if($rolActivo->hasPermissionTo('personas.privilegio_modificar_asistentes_desaprobados'))
                      @foreach( auth()->user()->formularios(2, $usuario->edad()) as $formulario)
                        @can('modificarUsuarioPolitica', [App\Models\User::class, $formulario])
                          <li><a class="dropdown-item" href="{{ route('usuario.modificar', [$formulario, $usuario]) }}">{{$formulario->label}}</a></li>
                        @endcan
                      @endforeach
                    @endif
                  @endif
                  <!-- / opcion modificar  -->

                  @can('informacionCongregacionalPolitica', $usuario)
                  <li><a class="dropdown-item" href="{{ route('usuario.informacionCongregacional', ['formulario' => 0 ,'usuario' => $usuario]) }}">Info. congregacional</a></li>
                  @endcan

                  @can('relacionesFamiliaresUsuarioPolitica', $usuario)
                  <li><a class="dropdown-item" href="{{ route('usuario.relacionesFamiliares', ['formulario' => 0 , 'usuario' => $usuario]) }}">Relaciones familiares</a></li>
                  @endcan

                  @can('geoasignacionUsuarioPolitica', $usuario)
                  <li><a class="dropdown-item" href="{{ route('usuario.geoAsignacion', ['formulario' => 0 ,'usuario' => $usuario]) }}">Geo asignación</a></li>
                  @endif

                  @if($rolActivo->hasPermissionTo('personas.opcion_cambiar_contrasena_asistente'))
                  <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalCambioContrasena" onclick="event.preventDefault(); document.getElementById('formCambioContrasena').setAttribute('action', 'usuarios/{{$usuario->id}}/cambiar-contrasena');">Cambiar contraseña</a></li>

                  <form method="POST" id="cambiarContraseñaDefault_{{$usuario->id}}" action="{{ route('usuario.cambiarContrasenaDefault',  ['usuario' => $usuario ]) }}">
                    @csrf
                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('cambiarContraseñaDefault_{{$usuario->id}}').submit();">Cambiar contraseña default</a></li>
                  </form>
                  @endif

                  @if($rolActivo->hasPermissionTo('personas.opcion_descargar_qr'))
                  <li><a class="dropdown-item" href="{{ route('usuario.descargarCodigoQr', $usuario) }}">Código QR</a></li>
                  @endif

                  <hr class="dropdown-divider">
                  @if($rolActivo->hasPermissionTo('personas.opcion_dar_de_baja_asistente'))
                    @if($usuario->trashed()!=TRUE)
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="darBajaAlta('{{$usuario->id}}', 'baja')">Dar de baja</a></li>
                    @endif
                  @endif
                  @if($rolActivo->hasPermissionTo('personas.opcion_eliminar_asistente'))
                    @if($usuario->trashed()!=TRUE)
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="comprobarSiTieneRegistros('{{$usuario->id}}')">Eliminar</a></li>
                    @endif
                  @endif
                  @if($rolActivo->hasPermissionTo('personas.eliminar_asistentes_forzadamente'))
                    @if($usuario->trashed()==TRUE)
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="eliminacionForzada('{{$usuario->id}}')">Eliminación forzada </a></li>
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
  <!--/ Header -->


  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4 p-1 border-0 shadow-sm">
         <ul class="nav nav-pills justify-content-start flex-column flex-md-row gap-2">
            @can('verPerfilUsuarioPolitica', [$usuario, 'principal'])
            <li class="nav-item flex-fill"><a id="tap-principal" href="{{ route('usuario.perfil', $usuario) }}" class="nav-link p-3 waves-effect waves-light" data-tap="principal"><i class='ti-xs ti ti-user-check me-2'></i> Principal</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'familia'])
            <li class="nav-item flex-fill"><a id="tap-familia" href="{{ route('usuario.perfil.familia', $usuario) }}" class="nav-link p-3 waves-effect waves-light"  data-tap="familia"><i class='ti-xs ti ti-home-heart me-2'></i> Familia</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'congregacion'])
            <li class="nav-item flex-fill"><a id="tap-congregacion" href="{{ route('usuario.perfil.congregacion', $usuario) }}" class="nav-link p-3 waves-effect waves-light" data-tap="congregacion"><i class='ti-xs ti ti-building-church me-2'></i> Congregación</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'escuelas'])
            <li class="nav-item flex-fill"><a id="tap-otro1" href="{{ route('usuario.historial-escuelas', $usuario) }}" class="nav-link p-3 waves-effect waves-light active" data-tap="otro1"><i class='ti-xs ti ti-school me-2'></i> Escuelas</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'financiera'])
            <li class="nav-item flex-fill"><a id="tap-otro2" href="javascript:void(0);" class="nav-link p-3 waves-effect waves-light" data-tap="otro2"><i class='ti-xs ti ti-report-money me-2'></i> Financiera</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'hitos'])
            <li class="nav-item flex-fill"><a id="tap-otro3" href="javascript:void(0);" class="nav-link p-3 waves-effect waves-light" data-tap="otro3"><i class='ti-xs ti ti-album me-2'></i> Hitos</a></li>
            @endcan
          </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->
  @endif

  <div class="row">
    <div class="col-12">
      <div class="accordion" id="accordionEscuelas">
        @foreach($escuelas as $escuela)
          <div class="accordion-item card mb-3 border-0 shadow-sm overflow-hidden">
            <h2 class="accordion-header" id="heading{{ $escuela->id }}">
              <button class="accordion-button collapsed px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $escuela->id }}" aria-expanded="false" aria-controls="collapse{{ $escuela->id }}">
                <div class="d-flex flex-column w-100 me-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="h5 mb-0 fw-bold"><i class="ti ti-bookmark me-2 text-primary"></i>{{ $escuela->nombre }}</span>
                    <span class="badge bg-label-primary px-3">{{ $escuela->aprobadas_obligatorias }} / {{ $escuela->total_obligatorias }} Obligatorias</span>
                  </div>
                  <div class="progress" style="height: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: {{ $escuela->progreso }}%;" aria-valuenow="{{ $escuela->progreso }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <small class="text-black mt-1">Avance académico: {{ $escuela->progreso }}%</small>
                </div>
              </button>
            </h2>
            <div id="collapse{{ $escuela->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $escuela->id }}" data-bs-parent="#accordionEscuelas">
              <div class="accordion-body p-4">
                <div class="row g-3">
                  @forelse($escuela->materias as $materia)
                    <div class="col-12 col-xl-4 col-md-6 mb-4 ">
                        <div class="h-100 card border shadow">
                            <div class="card-header">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $materia->nombre }}</h5>
                                    </div>
                                    @if($materia->caracter_obligatorio)
                                        <span class="badge bg-label-primary">Obligatoria</span>
                                    @else
                                        <span class="badge bg-label-warning">Opcional</span>
                                    @endif
                                </div>

                                @if($materia->resultado)
                                    @if($materia->resultado->aprobado)
                                        <span class="badge bg-label-success rounded-pill my-2">Aprobada</span>
                                    @else
                                        <span class="badge bg-label-danger rounded-pill my-2">Reprobada</span>
                                    @endif
                                @else
                                    <span class="badge bg-label-secondary rounded-pill my-2">Pendiente</span>
                                @endif

                                <div class="d-flex flex-row align-items-center mt-3">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted"><i class="ti ti-bookmark text-black me-2"></i>Estado:</small>
                                        <small class="fw-semibold text-black">
                                            @if($materia->resultado)
                                                {{ $materia->resultado->aprobado ? 'Aprobado satisfactoriamente' : 'No aprobado' }}
                                            @else
                                                Disponible para cursar
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                @if($materia->resultado)
                                    <div class="row justify-content-between mb-2">
                                        <div class="col-12 col-md-6 align-items-center">
                                            <div class="d-flex flex-column">
                                                <small class="text-muted"><i class="ti ti-checklist text-black me-2"></i>Nota Final:</small>
                                                <small class="fw-semibold text-black">
                                                    {{ $materia->resultado->nota_final }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 align-items-center">
                                            <div class="d-flex flex-column text-start">
                                                <small class="text-muted"><i class="ti ti-calendar-event text-black me-2"></i>Fecha:</small>
                                                <small class="fw-semibold text-black">
                                                    @if($materia->resultado->es_homologacion)
                                                        {{ \Carbon\Carbon::parse($materia->resultado->fecha_homologacion)->format('d/m/Y') }}
                                                    @else
                                                        {{ $materia->resultado->created_at->format('d/m/Y') }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row justify-content-between mb-2">
                                        <div class="col-12 align-items-center">
                                            @if($materia->resultado->es_homologacion)
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted"><i class="ti ti-certificate text-black me-2"></i>Observación:</small>
                                                    <small class="fw-semibold text-black" title="{{ $materia->resultado->observacion_homologacion }}">
                                                        Homologación
                                                    </small>
                                                </div>
                                            @else
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted"><i class="ti ti-user-check text-black me-2"></i>Asistencias:</small>
                                                    <small class="fw-semibold text-black">{{ $materia->resultado->total_asistencias }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-label-secondary mb-0 p-3 text-center">
                                        <i class="ti ti-clock mb-2 fs-4"></i><br>
                                        <small class="fw-semibold">Aún no has cursado esta materia.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                  @empty
                    <div class="col-12">
                        <div class="alert alert-info py-4 text-center">
                            <i class="ti ti-info-circle fs-1 mb-2"></i>
                            <p class="mb-0">No hay materias registradas para esta escuela.</p>
                        </div>
                    </div>
                  @endforelse
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>


  
  @if( $usuario->id == auth()->user()->id )
    @foreach ($formulario->secciones()->orderBy('orden','asc')->get() as $seccion)
      <form id="formularioSeccion{{ $seccion->id }}"  role="form" class="forms-sample formulariosSecciones" method="POST" action="{{ route($formulario->tipo->action, [ 'formulario' => $formulario, 'seccion' => $seccion,'usuario' => $usuario]) }}"  enctype="multipart/form-data">
        <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="modalSeccion{{ $seccion->id }}" aria-labelledby="modalSeccion{{ $seccion->id }}Label">
          <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalSeccion{{ $seccion->id }}Label">{{ $seccion->titulo }}</h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body pt-6 px-8">
              <div class="mb-4">
                <span class="text-black ti-14px mb-4">Realiza la actualización de tu información que encuentras a continuación, al finalizar puedes guardar el proceso.</span>
              </div>
              @csrf
              @method('PATCH')
              <div class="pt-3 row">
                @foreach ($seccion->campos()->orderBy('campo_seccion_formulario_usuario.orden', 'asc')->orderBy('nombre', 'asc')->get() as $campo)
                  @if($campo->es_campo_extra)
                  <div class="mb-3 col-12">
                    <label class="form-label" for="{{$campo->name_id}}">
                      {{ $campo->nombre }}
                    </label>

                    <!-- campo tipo 1 -->
                    @if($campo->tipo_de_campo == 1)
                    <input id="{{$campo->name_id}}" placeholder="{{ $campo->placeholder }}" name="{{$campo->name_id}}" value="{{ old($campo->name_id, $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first() ? $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()->pivot->valor : '' ) }}" class="form-control">
                    @endif
                    <!-- /campo tipo 1 -->

                    <!-- campo tipo 2 -->
                    @if($campo->tipo_de_campo == 2)
                    <textarea id="{{$campo->name_id}}" placeholder="{{ $campo->placeholder }}" name="{{$campo->name_id}}" class="form-control">{{ old($campo->name_id, $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first() ? $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()->pivot->valor : '' ) }}</textarea>
                    @endif
                    <!-- /campo tipo 2 -->

                    <!-- campo tipo 3 -->
                    @if($campo->tipo_de_campo == 3)
                    <select id="{{$campo->name_id}}" data-placeholder="{{ $campo->placeholder }}" name="{{$campo->name_id}}" class="select2 form-control" data-allow-clear="true">
                      <option value="">Ninguno</option>
                      @foreach (json_decode($campo->opciones_select) as $opcion)
                      <option value="{{$opcion->value}}" {{ old($campo->name_id, $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first() ? $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()->pivot->valor : '' ) == $opcion->value ? 'selected' : '' }}> {{ ucwords($opcion->nombre) }} </option>
                      @endforeach
                    </select>
                    @endif
                    <!-- /campo tipo 3 -->

                    <!-- campo tipo 4 -->
                    @if($campo->tipo_de_campo == 4)
                    <select id="{{$campo->name_id}}" data-placeholder="{{ $campo->placeholder }}" name="{{$campo->name_id}}[]" multiple class="select2 form-control" data-allow-clear="true">
                      @foreach (json_decode($campo->opciones_select) as $opcion)
                      <option value="{{$opcion->value}}" {{ in_array($opcion->value, old( $campo->name_id,
                                      $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()
                                      ? json_decode($usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()->pivot->valor)
                                      : [] )
                                    )
                                  ? "selected" : "" }}> {{ ucwords($opcion->nombre) }} </option>
                      @endforeach
                    </select>
                    @endif
                    <!-- /campo tipo 4 -->

                    <div id="error{{$campo->name_id}}"></div>

                  </div>
                  @else
                    <!-- fecha nacimiento -->
                    @if($campo->nombre_bd == 'fecha_nacimiento')
                      @livewire('Usuarios.formularios.fecha-nacimiento', [
                        'fechaDefault' => $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento : $fechaDefault,
                        'usuario' => $usuario,
                        'class' => 'col-12',
                        'label' => $campo->nombre,
                        'nameId' => $campo->name_id,
                        'formulario' => $formulario
                      ])
                    @endif
                    <!-- fecha nacimiento -->

                    <!--  Tipo de id  -->
                    @if($campo->nombre_bd == 'tipo_identificacion_id')
                    <div class="mb-3">
                      <label class="form-label" for="tipo_identificacion_id">
                        {{ $campo->nombre }}
                      </label>
                      <select id="tipo_identificacion_id" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="select2 form-select" data-allow-clear="true">
                        <option value="" selected>Ninguno</option>
                        @foreach ($tiposIdentificaciones as $tipoIdentificacion)
                        <option value="{{$tipoIdentificacion->id}}" {{ old($campo->name_id, $usuario->tipo_identificacion_id )==$tipoIdentificacion->id ? 'selected' : '' }}>{{$tipoIdentificacion->nombre}}</option>
                        @endforeach
                      </select>
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!--  Tipo de id  -->

                    <!-- identificacion -->
                    @if($campo->nombre_bd == 'identificacion')
                    <div class="mb-3">
                      <label class="form-label" for="identificacion">
                        {{ $campo->nombre }}
                      </label>
                      <input id="identificacion" name="{{ $campo->name_id }}"  placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id, $usuario->identificacion) }}" onkeyup="javascript:this.value=this.value.replace('.', '').replace(' ', '')" type="text" class="form-control" autocomplete="off" />
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /identificacion -->

                    <!-- /Email -->
                    @if($campo->nombre_bd == 'email')
                    <div class="mb-3 form-group col-12">
                      <label class="form-label" for="email">
                        {{ $campo->nombre }}
                      </label>
                      <div class="input-group input-group-merge">
                        <input type="email" id="email" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id, $usuario->email) }}" onkeyup="javascript:this.value=this.value.toLowerCase()" class="form-control" />
                      </div>
                      <div id="error{{$campo->name_id}}"></div>

                    </div>
                    @endif
                    <!-- /Email -->

                    <!-- Primer Nombre -->
                    @if($campo->nombre_bd == 'primer_nombre')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="primer_nombre">
                        {{ $campo->nombre }}
                      </label>
                      <input id="primer_nombre" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id, $usuario->primer_nombre) }}" type="text" class="form-control" />
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Primer Nombre  -->

                    <!-- Segundo Nombre  -->
                    @if($campo->nombre_bd == 'segundo_nombre')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="segundo_nombre">
                        {{ $campo->nombre }}
                      </label>
                      <input id="segundo_nombre" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id, $usuario->segundo_nombre) }}" type="text" class="form-control" />
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Segundo Nombre -->

                    <!-- Primer apellido -->
                    @if($campo->nombre_bd == 'primer_apellido')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="primer_apellido">
                        {{ $campo->nombre }}
                      </label>
                      <input id="primer_apellido" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id, $usuario->primer_apellido) }}" type="text" class="form-control" />
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Primer apellido  -->

                    <!-- Segundo apellido  -->
                    @if($campo->nombre_bd == 'segundo_apellido')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="segundo_apellido">
                        {{ $campo->nombre }}
                      </label>
                      <input id="segundo_apellido" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id, $usuario->segundo_apellido) }}" type="text" class="form-control" />
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Segundo apellido -->

                    <!-- Genero sexual -->
                    @if($campo->nombre_bd == 'genero')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="genero">
                        {{ $campo->nombre }}
                      </label>
                      <select id="genero" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="grupoSelect select2 selectorGenero form-select" data-allow-clear="true">
                        <option id="genero-m" value="0" {{ old($campo->name_id, $usuario->genero)==0 ? 'selected' : '' }}>Masculino</option>
                        <option id="genero-f" value="1" {{ old($campo->name_id, $usuario->genero)==1 ? 'selected' : '' }}>Femenino</option>
                        <option value="" {{ old($campo->name_id, $usuario->genero) === '' ? 'selected' : '' }}>Ninguno</option>
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Genero sexual -->

                    <!-- Estado Civil -->
                    @if($campo->nombre_bd == 'estado_civil_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="estado_civil_id">
                        {{ $campo->nombre }}
                      </label>
                      <select id="estado_civil_id" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="select2 form-select" data-allow-clear="true">
                        <option value="" selected>Ninguno</option>
                        @foreach ($tiposDeEstadosCiviles as $tiposDeEstadoCivil)
                        <option value="{{$tiposDeEstadoCivil->id}}" {{ old($campo->name_id, $usuario->estado_civil_id)==$tiposDeEstadoCivil->id ? 'selected' : '' }}>{{$tiposDeEstadoCivil->nombre}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Estado Civil -->

                    <!-- Nacionalidad -->
                    @if($campo->nombre_bd == 'pais_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="pais_id">
                      {{ $campo->nombre }}
                      </label>
                      <select id="pais_id" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($paises as $pais)
                        <option value="{{$pais->id}}" {{ old($campo->name_id, $usuario->pais_id)==$pais->id ? 'selected' : '' }}>{{ucwords ($pais->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Nacionalidad -->

                    <!-- Telefono fijo -->
                    @if($campo->nombre_bd == 'telefono_fijo')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="telefono_fijo">
                        {{$campo->nombre}}
                      </label>
                      <div class="input-group input-group-merge">
                        <input id="telefono_fijo" name="{{ $campo->name_id }}" placeholder="{{$campo->placeholder}}" value="{{ old($campo->name_id, $usuario->telefono_fijo) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                        <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
                      </div>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Telefono fijo -->

                    <!-- Telefono Movil #1 -->
                    @if($campo->nombre_bd == 'telefono_movil')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="telefono_movil">
                        {{ $campo->nombre }}
                      </label>
                      <div class="input-group input-group-merge">
                        <input id="telefono_movil" name="{{ $campo->name_id }}" placeholder="{{$campo->placeholder}}" value="{{ old($campo->name_id, $usuario->telefono_movil) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                        <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-device-mobile"></i></span>
                      </div>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Telefono Movil #1 -->

                    <!-- Telefono  otro Telefono -->
                    @if($campo->nombre_bd == 'telefono_otro')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="telefono_otro">
                        {{ $campo->nombre }}
                      </label>
                      <div class="input-group input-group-merge">
                        <input id="telefono_otro" name="{{ $campo->name_id }}" placeholder="{{$campo->placeholder}}" value="{{ old($campo->name_id, $usuario->telefono_otro) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                        <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
                      </div>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Telefono otro Telefono -->

                    <!-- vivienda_en_calidad_de -->
                    @if($campo->nombre_bd == 'tipo_vivienda_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="vivienda_en_calidad_de">
                        {{ $campo->nombre }}
                      </label>
                      <select id="vivienda_en_calidad_de" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($tiposDeVivienda as $tipoDeVivienda)
                        <option value="{{$tipoDeVivienda->id}}" {{ old($campo->name_id, $usuario->tipo_vivienda_id )==$tipoDeVivienda->id ? 'selected' : '' }}>{{ucwords ($tipoDeVivienda->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /vivienda_en_calidad_de -->

                    <!-- Direccion -->
                    @if($campo->nombre_bd == 'direccion')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="direccion">
                        {{ $campo->nombre }}
                      </label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-map"></i></span>
                        <input id="direccion" name="{{ $campo->name_id }}" placeholder="{{$campo->placeholder}}" value="{{ old($campo->name_id, $usuario->direccion) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                      </div>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Direccion -->

                    <!-- pregunta_vives_en -->
                    @if($campo->nombre_bd == 'pregunta_vives_en')
                      <div class="mb-3 col-12">
                        <div class=" small fw-medium mb-1">{{ $campo->nombre }}</div>
                        <label class="switch switch-lg">
                          <input id="preguntaVivesEn" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" type="checkbox" @checked(old($campo->name_id, $usuario->localidad_id ? TRUE : FALSE)) class="switch-input preguntaVivesEn" />
                          <span class="switch-toggle-slider">
                            <span class="switch-on">SI</span>
                            <span class="switch-off">NO</span>
                          </span>
                          <span class="switch-label"></span>
                        </label>
                      </div>
                    @endif
                    <!-- / pregunta_vives_en -->

                    <!-- / ubicacion-->
                    @if($tieneCampoPreguntaViveEn)
                      @if($campo->nombre_bd == 'ubicacion')
                        @livewire('Generales.barrio-localidad-buscador', [
                          'class' => 'col-12',
                          'label' => $campo->nombre,
                          'nameId' => $campo->name_id,
                          'conPreguntaAdiccional' => 'si',
                          'mostrar' => false,
                          'placeholder' => $campo->placeholder,
                          'usuario' => $usuario
                        ])
                      @endif
                    @else
                      @if($campo->nombre_bd == 'ubicacion')
                        @livewire('Generales.barrio-localidad-buscador', [
                          'class' => $campo->pivot->class,
                          'label' => $campo->nombre,
                          'nameId' => $campo->name_id,
                          'conPreguntaAdiccional' => 'no',
                          'mostrar' => true,
                          'placeholder' => $campo->placeholder,
                          'usuario' => $usuario
                        ])
                      @endif
                    @endif
                    <!-- / ubicacion-->

                    <!-- Nivel academico -->
                    @if($campo->nombre_bd == 'nivel_academico_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="nivel_academico">
                        {{ $campo->nombre }}
                      </label>
                      <select id="nivel_academico" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($nivelesAcademicos as $nivelAcademico)
                        <option value="{{$nivelAcademico->id}}" {{ old($campo->name_id, $usuario->nivel_academico_id)==$nivelAcademico->id ? 'selected' : '' }}>{{ucwords ($nivelAcademico->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Nivel academico -->

                    <!-- Estado Nivel Academico -->
                    @if($campo->nombre_bd == 'estado_nivel_academico_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="estado_nivel_academico">
                        {{ $campo->nombre }}
                      </label>
                      <select id="estado_nivel_academico" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($estadosNivelesAcademicos as $estadoNivelAcademico)
                        <option value="{{$estadoNivelAcademico->id}}" {{ old($campo->name_id, $usuario->estado_nivel_academico_id)==$estadoNivelAcademico->id ? 'selected' : '' }}>{{ucwords ($estadoNivelAcademico->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Estado Nivel Academico -->

                    <!-- Profesión -->
                    @if($campo->nombre_bd == 'profesion_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="profesion">
                        {{ $campo->nombre }}
                      </label>
                      <select id="profesion" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($profesiones as $profesion)
                        <option value="{{$profesion->id}}" {{ old($campo->name_id, $usuario->profesion_id)==$profesion->id ? 'selected' : '' }}>{{ucwords ($profesion->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Profesión -->

                    <!-- Ocupación -->
                    @if($campo->nombre_bd == 'ocupacion_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="ocupacion">
                      {{ $campo->nombre }}
                      </label>
                      <select id="ocupacion" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($ocupaciones as $ocupacion)
                        <option value="{{$ocupacion->id}}" {{ old($campo->name_id, $usuario->ocupacion_id)==$ocupacion->id ? 'selected' : '' }}>{{ucwords ($ocupacion->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Ocupación -->

                    <!-- Sector económico -->
                    @if($campo->nombre_bd == 'sector_economico_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="sector_economico">
                        {{ $campo->nombre }}
                      </label>
                      <select id="sector_economico" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($sectoresEconomicos as $sectorEconomico)
                        <option value="{{$sectorEconomico->id}}" {{ old($campo->name_id, $usuario->sector_economico_id)==$sectorEconomico->id ? 'selected' : '' }}>{{ucwords ($sectorEconomico->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Sector económico -->

                    <!-- Entidad relacionada -->
                    @if($campo->nombre_bd == 'entidad_relacionada_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="entidad_relacionada">
                        {{ $campo->nombre }}
                      </label>
                      <select id="entidad_relacionada" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($entidadesRelacionadas as $entidadRelacionada)
                        <option value="{{$entidadRelacionada->id}}" {{ old($campo->name_id, $usuario->entidad_relacionada_id)==$entidadRelacionada->id ? 'selected' : '' }}>{{ucwords ($entidadRelacionada->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Entidad relacionada -->

                    <!-- Tipo de sangre -->
                    @if($campo->nombre_bd == 'tipo_sangre_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="tipo_sangre">
                        {{ $campo->nombre }}
                      </label>
                      <select id="tipo_sangre" name="{{ $campo->name_id }}" data-placeholder="{{ $campo->placeholder }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($tiposDeSangres as $tipoSangre)
                        <option value="{{$tipoSangre->id}}" {{ old($campo->name_id, $usuario->tipo_sangre_id)==$tipoSangre->id ? 'selected' : '' }}>{{ucwords ($tipoSangre->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Tipo de sangre -->

                    <!-- Indicaciones medicas -->
                    @if($campo->nombre_bd == 'indicaciones_medicas')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="indicaciones_medicas">
                        {{ $campo->nombre }}
                      </label>
                      <textarea onkeypress="return sinComillas(event)" id="indicaciones_medicas" name="{{ $campo->name_id }}" data-placeholder="{{ $campo->placeholder }}" placeholder="{{ $campo->placeholder }}" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true" >{{ old($campo->name_id, $usuario->indicaciones_medicas) }}</textarea>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Indicaciones medicas -->

                    <!-- Tienes una petición -->
                    @if($campo->nombre_bd == 'tienes_una_peticion')
                    <div class="mb-3 col-12">
                      <div class=" small fw-medium mb-1">{{ $campo->nombre }}</div>
                      <label class="switch switch-lg">
                        <input id="tienesUnaPeticion" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" type="checkbox" @checked(old($campo->name_id)) class="switch-input tienesUnaPeticion" />
                        <span class="switch-toggle-slider">
                          <span class="switch-on">SI</span>
                          <span class="switch-off">NO</span>
                        </span>
                        <span class="switch-label"></span>
                      </label>
                    </div>
                    @endif
                    <!-- / Tienes una petición -->

                    <!-- Tipo de Petición -->
                    @if($campo->nombre_bd == 'tipo_peticion_id')
                    <div id="divSelectTipoPeticion" class="mb-2 {{ old('tienesUnaPeticion') ? '' : 'd-none' }} col-12">
                      <label class="form-label" for="tipo_peticion">
                        {{ $campo->nombre }}
                      </label>
                      <select id="tipo_peticion" name="{{ $campo->name_id }}" data-placeholder="{{ $campo->placeholder }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($tipoPeticiones as $tipoPeticion)
                        <option value="{{$tipoPeticion->id}}" {{ old($campo->name_id)==$tipoPeticion->id ? 'selected' : '' }}>{{ucwords ($tipoPeticion->nombre)}}</option>
                        @endforeach
                      </select>

                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Tipo de Petición -->

                    <!-- Descripción de la petición -->
                    @if($campo->nombre_bd == 'descripcion_peticion')
                    <div id="divDescripcionPeticion" class="mb-2 {{ old('tienesUnaPeticion') ? '' : 'd-none' }} col-12">
                      <label class="form-label" for="descripcion_peticion">
                        {{ $campo->nombre }}
                      </label>
                      <textarea onkeypress="return sinComillas(event)" id="descripcion_peticion" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true">{{ old($campo->name_id) }}</textarea>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Descripción de la petición -->

                    <!-- Sede -->
                    @if($campo->nombre_bd == 'sede_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="sede">
                        {{ $campo->nombre }}
                      </label>
                      <select id="sede" data-placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($sedes as $sede)
                        <option value="{{$sede->id}}" {{ old($campo->name_id, $usuario->sede_id)==$sede->id ? 'selected' : '' }}>{{ucwords ($sede->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Sede -->

                    <!-- Tipo de vinculación-->
                    @if($campo->nombre_bd == 'tipo_vinculacion_id')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="tipo_vinculacion">
                        {{ $campo->nombre }}
                      </label>
                      <select id="tipo_vinculacion" name="{{ $campo->name_id }}" data-placeholder="{{ $campo->placeholder }}" class="select2 form-select" data-allow-clear="true">
                        <option value="">Ninguno</option>
                        @foreach ($tiposDeVinculacion as $tipoDeVinculacion)
                        <option value="{{$tipoDeVinculacion->id}}" {{ old($campo->name_id, $usuario->tipo_vinculacion_id)==$tipoDeVinculacion->id ? 'selected' : '' }}>{{ucwords ($tipoDeVinculacion->nombre)}}</option>
                        @endforeach
                      </select>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Tipo de vinculación -->

                    <!-- información opcional -->
                    @if($campo->nombre_bd == 'informacion_opcional')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="informacion_opcional">
                        {{ $campo->nombre }}
                      </label>
                      <textarea onkeypress="return sinComillas(event)" id="informacion_opcional" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" class="form-control" rows="5" maxlength="10000" placeholder="">{{ old($campo->name_id, $usuario->informacion_opcional) }}</textarea>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- información opcional-->

                    <!-- campo extra reservado -->
                    @if($campo->nombre_bd == 'campo_reservado')
                    <div class="mb-3 col-12">
                      <label class="form-label" for="campo_reservado">
                        {{ $campo->nombre }}
                      </label>
                      <textarea onkeypress="return sinComillas(event)" id="campo_reservado" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" class="form-control" rows="5" maxlength="50000" placeholder="">{{ old($campo->name_id, $usuario->campo_reservado) }}</textarea>
                    <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- campo extra reservado-->

                    <!-- archivo_a -->
                    @if($campo->nombre_bd == 'archivo_a')
                    <div class="mb-3 col-12">
                      <label id="label_archivo_a" class="form-label" for="archivo_a">
                        {{ $campo->nombre }}
                        @if($campo->tiene_descargable)
                        (<a href="{{ tenant_asset('archivos/descargable_archivo_a.pdf') }}" target="_blank">Descargar formato</a>)
                        @endif
                      </label>

                      @if($usuario->archivo_a!='')
                      <div id="mensaje_remplazar_archivo_a" class="d-grid mx-auto">
                          <button type="button" data-input="archivo_a" class="btn-remplazar-archivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                          <span class="ti-xs ti ti-file-upload me-2"></span>Remplazar
                          </button>
                          <span class="ti-12px mt-2"><i class="ti ti-info-circle text-info"></i>  Ya adjuntaste un archivo, ¿deseo reemplazarlo?</span>
                      </div>
                      @endif
                      <div id="div_input_archivo_a" class="row g-0 {{ $usuario->archivo_a!='' ? 'd-none' : '' }}">
                        <div class="d-grid col-6 mx-auto">
                          <button type="button" data-input="archivo_a" class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                          <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                          </button>
                        </div>
                        <div class="col-6 d-flex">
                            <input type="text" id="nombre_archivo_a" class="form-control" placeholder="{{ $campo->placeholder }}" readonly>
                        </div>
                      </div>
                      <input type="file" id="archivo_a" name="{{ $campo->name_id }}" data-input="archivo_a" class="form-control inputFile d-none" accept=".gif, .jpg, .png, .jpeg, .pdf">
                      <div id="error{{$campo->name_id}}"></div>
                      @if($errors->has('archivo_a')) <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i> {{ $errors->first('archivo_a') }} </div> @endif
                    </div>
                    @endif
                    <!--/ archivo_a -->

                    <!-- archivo_b -->
                    @if($campo->nombre_bd == 'archivo_b')
                    <div class="mb-3 col-12">
                      <label id="label_archivo_b" class="form-label" for="archivo_b">
                        {{ $campo->nombre }}
                        @if($campo->tiene_descargable)
                        (<a href="{{ tenant_asset('archivos/descargable_archivo_b.pdf') }}" target="_blank">Descargar formato</a>)
                        @endif
                      </label>

                      @if($usuario->archivo_b!='')
                      <div id="mensaje_remplazar_archivo_b" class="d-grid mx-auto">
                          <button type="button" data-input="archivo_b" class="btn-remplazar-archivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                          <span class="ti-xs ti ti-file-upload me-2"></span>Remplazar
                          </button>
                          <span class="ti-12px mt-2"><i class="ti ti-info-circle text-info"></i>  Ya adjuntaste un archivo, ¿deseo reemplazarlo?</span>
                      </div>
                      @endif
                      <div id="div_input_archivo_b" class="row g-0 {{ $usuario->archivo_b!='' ? 'd-none' : '' }}">
                        <div class="d-grid col-6 mx-auto">
                          <button type="button" data-input="archivo_b" class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                          <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                          </button>
                        </div>
                        <div class="col-6 d-flex">
                            <input type="text" id="nombre_archivo_b" class="form-control" placeholder="{{ $campo->placeholder }}" readonly>
                        </div>
                      </div>
                      <input type="file" id="archivo_b" name="{{ $campo->name_id }}" data-input="archivo_b" class="form-control inputFile d-none" accept=".gif, .jpg, .png, .jpeg, .pdf">
                      @if($errors->has('archivo_b')) <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i> {{ $errors->first('archivo_b') }} </div> @endif
                    </div>
                    @endif
                    <!--/ archivo_b -->

                    <!-- archivo_c -->
                    @if($campo->nombre_bd == 'archivo_c')
                    <div class="mb-3 col-12">
                      <label id="label_archivo_c" class="form-label" for="archivo_c">
                        {{ $campo->nombre }}
                        @if($campo->tiene_descargable)
                        (<a href="{{ tenant_asset('archivos/descargable_archivo_c.pdf') }}" target="_blank">Descargar formato</a>)
                        @endif
                      </label>

                      @if($usuario->archivo_c!='')
                      <div id="mensaje_remplazar_archivo_c" class="d-grid mx-auto">
                          <button type="button" data-input="archivo_c" class="btn-remplazar-archivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                          <span class="ti-xs ti ti-file-upload me-2"></span>Remplazar
                          </button>
                          <span class="ti-12px mt-2"><i class="ti ti-info-circle text-info"></i>  Ya adjuntaste un archivo, ¿deseo reemplazarlo?</span>
                      </div>
                      @endif
                      <div id="div_input_archivo_c" class="row g-0 {{ $usuario->archivo_c!='' ? 'd-none' : '' }}">
                        <div class="d-grid col-6 mx-auto">
                          <button type="button" data-input="archivo_c" class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                          <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                          </button>
                        </div>
                        <div class="col-6 d-flex">
                            <input type="text" id="nombre_archivo_c" class="form-control" placeholder="{{ $campo->placeholder }}" readonly>
                        </div>
                      </div>
                      <input type="file" id="archivo_c" name="{{ $campo->name_id }}" data-input="archivo_c" class="form-control inputFile d-none" accept=".gif, .jpg, .png, .jpeg, .pdf">
                      @if($errors->has('archivo_c')) <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i> {{ $errors->first('archivo_c') }} </div> @endif
                    </div>
                    @endif
                    <!--/ archivo_c -->

                    <!-- archivo_d -->
                    @if($campo->nombre_bd == 'archivo_d')
                    <div class="mb-3 col-12">
                      <label id="label_archivo_d" class="form-label" for="archivo_d">
                        {{ $campo->nombre }}
                        @if($campo->tiene_descargable)
                        (<a href="{{ tenant_asset('archivos/descargable_archivo_d.pdf') }}" target="_blank">Descargar formato</a>)
                        @endif
                      </label>

                      @if($usuario->archivo_d!='')
                      <div id="mensaje_remplazar_archivo_d" class="d-grid mx-auto">
                          <button type="button" data-input="archivo_d" class="btn-remplazar-archivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                          <span class="ti-xs ti ti-file-upload me-2"></span>Remplazar
                          </button>
                          <span class="ti-12px mt-2"><i class="ti ti-info-circle text-info"></i>  Ya adjuntaste un archivo, ¿deseo reemplazarlo?</span>
                      </div>
                      @endif
                      <div id="div_input_archivo_d" class="row g-0 {{ $usuario->archivo_d!='' ? 'd-none' : '' }}">
                        <div class="d-grid col-6 mx-auto">
                          <button type="button" data-input="archivo_d" class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                          <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                          </button>
                        </div>
                        <div class="col-6 d-flex">
                            <input type="text" id="nombre_archivo_d" class="form-control" placeholder="{{ $campo->placeholder }}" readonly>
                        </div>
                      </div>
                      <input type="file" id="archivo_d" name="{{ $campo->name_id }}" data-input="archivo_d" class="form-control inputFile d-none" accept=".gif, .jpg, .png, .jpeg, .pdf">
                      @if($errors->has('archivo_d')) <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i> {{ $errors->first('archivo_d') }} </div> @endif
                    </div>
                    @endif
                    <!--/ archivo_d -->

                    <!--  Tipo de id acudiente -->
                    @if($campo->nombre_bd == 'tipo_identificacion_acudiente_id')
                    <div class="mb-2 col-12">
                      <label class="form-label" for="tipo_identificacion_acudiente_id">
                        {{ $campo->nombre }}
                      </label>
                      <select id="tipo_identificacion_acudiente_id"  data-placeholder="{{ $campo->placeholder }}"  name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                        <option value="" selected>Ninguno</option>
                        @foreach ($tiposIdentificaciones as $tipoIdentificacion)
                        <option value="{{$tipoIdentificacion->id}}" {{ old($campo->name_id, $usuario->tipo_identificacion_acudiente_id)==$tipoIdentificacion->id ? 'selected' : '' }}>{{$tipoIdentificacion->nombre}}</option>
                        @endforeach
                      </select>
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!--  Tipo de id acudiente -->

                    <!-- identificacion acudiente -->
                    @if($campo->nombre_bd == 'identificacion_acudiente')
                    <div class="mb-2 col-12">
                      <label class="form-label" for="identificacion_acudiente">
                        {{ $campo->nombre }}
                      </label>
                      <input id="identificacion_acudiente" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" value="{{ old($campo->name_id, $usuario->identificacion_acudiente) }}" type="text" class="form-control" />
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /identificacion acudiente -->

                    <!-- Nombre acudiente -->
                    @if($campo->nombre_bd == 'nombre_acudiente')
                    <div class="mb-2 col-12">
                      <label class="form-label" for="nombre_acudiente">
                        {{ $campo->nombre }}
                      </label>
                      <input id="nombre_acudiente" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id, $usuario->nombre_acudiente) }}" type="text" class="form-control" />
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Nombre acudiente  -->

                    <!-- Telefono acudiente -->
                    @if($campo->nombre_bd == 'telefono_acudiente')
                    <div class="mb-2 col-12">
                      <label class="form-label" for="telefono_acudiente">
                        {{ $campo->nombre }}
                      </label>
                      <div class="input-group input-group-merge">
                        <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
                        <input id="telefono_acudiente" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" value="{{ old($campo->name_id, $usuario->telefono_acudiente) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                      </div>
                      <div id="error{{$campo->name_id}}"></div>
                    </div>
                    @endif
                    <!-- /Telefono acudiente -->
                  @endif
                @endforeach
              </div>
          </div>
          <div class="offcanvas-footer p-5  border-top border-2 px-8">
            <button type="button" data-seccion="{{$seccion->id}}" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
          </div>
        </div>
      </form>
    @endforeach
  @endif

  @if( $usuario->id == auth()->user()->id )
    <!-- modal foto-->
    <form id="formularioFoto"  role="form" class="forms-sample" method="POST" action="{{ route('usuario.cambiarFoto', $usuario) }}"  enctype="multipart/form-data">
      @csrf
      @method('PATCH')
      <div class="modal fade modal-img" id="modalFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-simple modal-edit-user">
          <div class="modal-content">
            <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-4">
                <h3 class="mb-2 text-black"><i class="ti ti-camera  ti-lg"></i> Subir foto</h3>
                <p class="text-muted text-black">Selecciona y recorta la foto</p>
              </div>

              <div class="row">
                <div class="col-12">
                  <div class="mb-2">
                    <label class="mb-2 text-black"><span class="fw-bold">Paso #1</span> Selecciona la foto</label><br>
                    <input class="form-control" type="file" id="cropperImageUpload">
                  </div>
                  <div class="mb-2">
                    <label class="mb-2 text-black"><span class="fw-bold">Paso #2</span> Recorta la foto</label><br>
                    <center>
                      <img src="{{ Storage::disk('global_media')->url('placeholder.jpg') }}" class="w-100" id="croppingImage" alt="cropper">
                    </center>
                    <input class="form-control d-none" type="text" value="" id="imagen-recortada" name="foto">
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer text-center">
              <div class="col-12 text-center">
                <button type="reset" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="submit" id="cropSubmit" class="btn btn-primary rounded-pill me-sm-3 me-1">Guardar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ modal foto -->
    </form>

    <!-- modal portada-->
    <form id="formularioPortada"  role="form" class="forms-sample" method="POST" action="{{ route('usuario.cambiarPortada', $usuario) }}"  enctype="multipart/form-data">
      @csrf
      @method('PATCH')
      <div class="modal fade modal-img" id="modalPortada" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
          <div class="modal-content">
            <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-4">
                <h3 class="mb-2"><i class="ti ti-camera  ti-lg"></i> Subir portada</h3>
                <p class="text-muted">Selecciona y recorta la portada</p>
              </div>

              <div class="row">
                <div class="col-12">
                  <div class="mb-2">
                    <label class="mb-2"><span class="fw-bold">Paso #1</span> Selecciona la portada</label><br>
                    <input class="form-control" type="file" id="cropperImageUploadPortada">
                  </div>
                  <div class="mb-2">
                    <label class="mb-2"><span class="fw-bold">Paso #2</span> Recorta la portada</label><br>
                    <center>
                      <img src="{{ Storage::disk('global_media')->url('placeholders/placeholder.jpg') }}" class="w-100" id="croppingImagePortada" alt="cropper">
                    </center>
                    <input class="form-control d-none" type="text" value="" id="imagen-recortada-portada" name="foto">
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer text-center">
              <div class="col-12 text-center">
                <button type="reset" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="submit" id="cropSubmitPortada" class="btn btn-primary rounded-pill me-sm-3 me-1">Guardar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ modal foto -->
    </form>
  @endif


@endsection
