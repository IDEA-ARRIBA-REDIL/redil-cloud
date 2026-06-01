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

@section('page-script')
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

  @if($configuracion->vista_perfil_usuario_clasica==false)
  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-10 p-1 border-1">
         <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">
            @can('verPerfilUsuarioPolitica', [$usuario, 'principal'])
            <li class="nav-item flex-fill"><a id="tap-principal" href="{{ route('usuario.perfil', $usuario) }}" class="nav-link p-3 waves-effect waves-light" data-tap="principal"><i class='ti-xs ti ti-user-check me-2'></i> Principal</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'familia'])
            <li class="nav-item flex-fill"><a id="tap-familia" href="{{ route('usuario.perfil.familia', $usuario) }}" class="nav-link p-3 waves-effect waves-light active"  data-tap="familia"><i class='ti-xs ti ti-home-heart me-2'></i> Familia</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'congregacion'])
            <li class="nav-item flex-fill"><a id="tap-congregacion" href="{{ route('usuario.perfil.congregacion', $usuario) }}" class="nav-link p-3 waves-effect waves-light" data-tap="congregacion"><i class='ti-xs ti ti-building-church me-2'></i> Congregación</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'escuelas'])
            <li class="nav-item flex-fill"><a id="tap-otro1" href="{{ route('usuario.historial-escuelas', $usuario) }}" class="nav-link p-3 waves-effect waves-light" data-tap="escuelas"><i class='ti-xs ti ti-school me-2'></i> Escuelas</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'financiera'])
            <li class="nav-item flex-fill"><a id="tap-otro2"href="javascript:void(0);" class="nav-link p-3 waves-effect waves-light" data-tap="otro2"><i class='ti-xs ti ti-report-money me-2'></i> Financiera</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'hitos'])
            <li class="nav-item flex-fill"><a id="tap-otro3"href="javascript:void(0);" class="nav-link p-3 waves-effect waves-light" data-tap="otro3"><i class='ti-xs ti ti-album me-2'></i> Hitos</a></li>
            @endcan
          </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->
  @endif

  <!-- Principal-->
  <div id="div-principal" class="row g-4">

    <!-- Grupo familiar -->
    <div class="col-lg-8 col-md-12">
      <div class="card mb-4">
        <div class="card-header align-items-center">
          <p class="card-text text-uppercase fw-bold">Grupo familiar</p>
        </div>
        <div class="card-body">
          <div class="row g-4">
            @if($parientes->count() > 0)
            @foreach($parientes as $pariente)
            <div class="col-lg-6 col-md-6 col-12">
              <div class="card border rounded">
                <div class="card-body text-center">
                  <div class="mx-auto my-3">
                    <img src="{{ $pariente->foto_url }}" alt="foto {{$pariente->primer_nombre}}" class="rounded-circle w-px-100" />
                  </div>

                  <span class="pb-1"><span></span><b>Relación:</b> {{ $usuario->genero == 0 ? $pariente->nombre_masculino : $pariente->nombre_femenino }} de </span>
                  <h4 class="mb-1 card-title">{{ $pariente->nombre(3) }}</h4>

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
    <!-- / Grupo familiar -->

    <div class="col-lg-4 col-md-12">
      <div class="card mb-4">
        <div class="card-header align-items-center">
          <p class="card-text text-uppercase fw-bold">Datos del acudiente</p>
        </div>
        <div class="card-body pb-1">
          <ul class="list-unstyled mb-4 mt-2">
            <li class="d-flex align-items-center mb-1"><i class="ti ti-point text-heading"></i><span class="fw-medium mx-2 text-heading">Nombre:</span> <span>{{ $usuario->nombre_acudiente ? $usuario->nombre_acudiente : 'Sin dato' }}</span></li>
            <li class="d-flex align-items-center mb-1"><i class="ti ti-point text-heading"></i><span class="fw-medium mx-2 text-heading">Teléfono:</span> <span>{{ $usuario->telefono_acudiente ? $usuario->telefono_acudiente : 'Sin dato' }}</span></li>
            <li class="d-flex align-items-center mb-1"><i class="ti ti-point text-heading"></i><span class="fw-medium mx-2 text-heading">Tipo identificación:</span> <span>{{ $usuario->tipo_identificacion_acudiente_id ? $usuario->tipoIdentificacionAcudiente->nombre : 'Sin dato' }}</span></li>
            <li class="d-flex align-items-center mb-1"><i class="ti ti-point text-heading"></i><span class="fw-medium mx-2 text-heading">Tipo identificación:</span> <span>{{ $usuario->identificacion_acudiente ? $usuario->identificacion_acudiente : 'Sin dato' }}</span></li>
          </ul>
        </div>
      </div>
    </div>

  </div>
  <!--/ Principal-->

  <!-- Modal cambio de contraseña -->
  <div class="modal fade" id="modalCambioContrasena" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
      <form id="formCambioContrasena" class="forms-sample" method="POST" action="">
        @csrf
        <div class="modal-content">
          <div class="modal-header d-flex flex-column">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            <div class="text-center mb-4">
              <h3 class="role-title mb-2"><i class="ti ti-password ti-lg"></i> Cambio de contraseña</h3>
              <p class="text-black">La contraseña debe contener como mínimo 5 caracteres, una letra minúscula y un número.</p>
            </div>

            <div class="row">

              <!-- Nueva Contrasena -->
              <div class="col-12 mb-3">
                <label class="form-label" for="nueva_contrasena">Nueva contraseña</label>
                <input id="nueva_contrasena" name="password" value="" type="password" class="form-control" required pattern="(?=.*\d)(?=.*[A-Za-z]).{5,}" title="La contraseña debe contener como minimo 5 caracteres alfanumericos, es decir, debe contener como minimo letras y numeros.  "/>
              </div>

              <!-- Confirmar Contrasena -->
              <div class="col-12 mb-3">
                <label class="form-label" for="confirmar_contrasena">Confirmar contraseña</label>
                <input id="confirmar_contrasena" name="password_confirmation" value="" type="password" class="form-control" required pattern="(?=.*\d)(?=.*[A-Za-z]).{5,}" title="La contraseña debe contener como minimo 5 caracteres alfanumericos, es decir, debe contener como minimo letras y numeros.  "/>
              </div>

            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn rounded-pill btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn rounded-pill btn-primary "><i class="ti ti-check"></i> Guardar </button>
          </div>
        </div>
      </form>
    </div>
  </div>

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
                <button type="submit" id="cropSubmitPortada" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ modal foto -->
    </form>
  @endif

@endsection
