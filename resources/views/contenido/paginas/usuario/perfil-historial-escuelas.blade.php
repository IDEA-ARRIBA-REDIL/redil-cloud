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
          @php
            $items = $escuela->es_por_niveles ? $escuela->niveles : $escuela->materias;
            $tipoItemTexto = $escuela->es_por_niveles ? 'Nivel' : 'Materia';
            $tipoItemPlural = $escuela->es_por_niveles ? 'Niveles' : 'Materias';
          @endphp
          <div class="accordion-item card mb-3 border-0 shadow-sm overflow-hidden">
            <h2 class="accordion-header" id="heading{{ $escuela->id }}">
              <button class="accordion-button collapsed px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $escuela->id }}" aria-expanded="false" aria-controls="collapse{{ $escuela->id }}">
                <div class="d-flex flex-column w-100 me-3">
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <span class="h5 mb-0 fw-bold text-truncate"><i class="ti ti-bookmark me-2 text-primary"></i>{{ $escuela->nombre }}</span>
                  </div>

                  {{-- Mini-cards consolidadas estilo KPI --}}
                  <div class="row my-2 w-100">
                    {{-- Card 1: Materias / Niveles Obligatorios --}}
                    @if($escuela->total_obligatorias > 0)
                      <div class="col-12 {{ !$escuela->es_por_niveles && $escuela->total_opcionales > 0 ? 'col-sm-6 col-lg-3' : 'col-sm-6' }}">
                        <div class="card shadow-sm border-0 h-100 bg-white">
                          <div class="card-body d-flex align-items-center p-3">
                            <div class="avatar me-3 flex-shrink-0">
                              <span class="avatar-initial rounded-circle bg-info text-white">
                                <i class="ti ti-book fs-4"></i>
                              </span>
                            </div>
                            <div class="d-flex flex-column text-start">
                              <small class="text-black mb-0">{{ $escuela->es_por_niveles ? 'Niveles obligatorios' : 'Materias obligatorias' }}</small>
                              <h5 class="mb-0 fw-bold text-black">{{ $escuela->aprobadas_obligatorias }} / {{ $escuela->total_obligatorias }}</h5>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif

                    {{-- Card 2: Materias / Niveles Opcionales --}}
                    @if($escuela->total_opcionales > 0)
                      <div class="col-12 {{ !$escuela->es_por_niveles ? 'col-sm-6 col-lg-3' : 'col-sm-6' }}">
                        <div class="card shadow-sm border-0 h-100 bg-white">
                          <div class="card-body d-flex align-items-center p-3">
                            <div class="avatar me-3 flex-shrink-0">
                              <span class="avatar-initial rounded-circle bg-success text-white">
                                <i class="ti ti-bookmark fs-4"></i>
                              </span>
                            </div>
                            <div class="d-flex flex-column text-start">
                              <small class="text-black mb-0">{{ $escuela->es_por_niveles ? 'Niveles opcionales' : 'Materias opcionales' }}</small>
                              <h5 class="mb-0 fw-bold text-black">{{ $escuela->aprobadas_opcionales }} / {{ $escuela->total_opcionales }}</h5>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif

                    {{-- Si es escuela por materias: Cards de Créditos --}}
                    @if(!$escuela->es_por_niveles)
                      {{-- Card 3: Créditos de Materias Obligatorias --}}
                      @if($escuela->total_obligatorias > 0)
                        <div class="col-12 {{ $escuela->total_opcionales > 0 ? 'col-sm-6 col-lg-3' : 'col-sm-6' }}">
                          <div class="card shadow-sm border-0 h-100 bg-white">
                            <div class="card-body d-flex align-items-center p-3">
                              <div class="avatar me-3 flex-shrink-0">
                                <span class="avatar-initial rounded-circle bg-info text-white">
                                  <i class="ti ti-award fs-4"></i>
                                </span>
                              </div>
                              <div class="d-flex flex-column text-start">
                                <small class="text-black mb-0">Créditos obligatorios</small>
                                <h5 class="mb-0 fw-bold text-black">{{ $escuela->creditos_aprobados_obligatorias }} / {{ $escuela->creditos_totales_obligatorias }}</h5>
                              </div>
                            </div>
                          </div>
                        </div>
                      @endif

                      {{-- Card 4: Créditos de Materias Opcionales --}}
                      @if($escuela->total_opcionales > 0)
                        <div class="col-12 col-sm-6 col-lg-3">
                          <div class="card shadow-sm border-0 h-100 bg-white">
                            <div class="card-body d-flex align-items-center p-3">
                              <div class="avatar me-3 flex-shrink-0">
                                <span class="avatar-initial rounded-circle bg-info text-white">
                                  <i class="ti ti-stars fs-4"></i>
                                </span>
                              </div>
                              <div class="d-flex flex-column text-start">
                                <small class="text-black mb-0">Créditos opcionales</small>
                                <h5 class="mb-0 fw-bold text-black">{{ $escuela->creditos_aprobados_opcionales }} / {{ $escuela->creditos_totales_opcionales }}</h5>
                              </div>
                            </div>
                          </div>
                        </div>
                      @endif
                    @endif
                  </div>

                  @if($escuela->total_obligatorias > 0)
                    <div class="progress mt-2" style="height: 10px;">
                      <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: {{ $escuela->progreso }}%;" aria-valuenow="{{ $escuela->progreso }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-1 flex-wrap gap-1">
                      <small class="text-black">
                        Avance plan obligatorio: <span class="fw-bold text-success">{{ $escuela->progreso }}%</span>
                      </small>
                    </div>
                  @endif
                </div>
              </button>
            </h2>
            <div id="collapse{{ $escuela->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $escuela->id }}" data-bs-parent="#accordionEscuelas">
              <div class="accordion-body p-4">
                <div class="row g-3">
                  @forelse($items as $item)
                    <div class="col-12 col-xl-4 col-md-6 mb-4">
                        <div class="h-100 card border shadow">
                            <div class="card-header">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $item->nombre }}</h5>
                                    </div>
                                    @if($item->caracter_obligatorio)
                                        <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-semibold">{{ $escuela->es_por_niveles ? 'Obligatorio' : 'Obligatoria' }}</span>
                                    @else
                                        <span class="badge bg-info text-white rounded-pill px-3 py-1 fw-semibold">{{ $escuela->es_por_niveles ? 'Opcional' : 'Opcional' }}</span>
                                    @endif
                                </div>

                               

                                <div class="d-flex flex-row align-items-center mt-3">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted"><i class="ti ti-bookmark text-black me-2"></i>Estado:</small>
                                        <small class="fw-semibold text-black">
                                            @if($item->resultado)
                                                @if((int)$item->resultado->aprobado === 1)
                                                    Aprobado
                                                @elseif((int)$item->resultado->aprobado === 2)
                                                    En proceso
                                                @else
                                                    No aprobado
                                                @endif
                                            @else
                                                Disponible para cursar
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                @if($item->resultado)
                                    <div class="row justify-content-between mb-2">
                                        <div class="col-12 col-md-6 align-items-center">
                                            <div class="d-flex flex-column">
                                                <small class="text-muted"><i class="ti ti-checklist text-black me-2"></i>Nota Final:</small>
                                                <small class="fw-semibold text-black">
                                                    {{ $item->resultado->nota_final !== null ? number_format((float)$item->resultado->nota_final, 2) : '-' }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 align-items-center">
                                            <div class="d-flex flex-column text-start">
                                                <small class="text-muted"><i class="ti ti-calendar-event text-black me-2"></i>Fecha:</small>
                                                <small class="fw-semibold text-black">
                                                    @if($item->resultado->es_homologacion)
                                                        {{ $item->resultado->fecha_homologacion ? \Carbon\Carbon::parse($item->resultado->fecha_homologacion)->format('d/m/Y') : ($item->resultado->created_at ? $item->resultado->created_at->format('d/m/Y') : '-') }}
                                                    @else
                                                        {{ $item->resultado->created_at ? $item->resultado->created_at->format('d/m/Y') : '-' }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row justify-content-between mb-2">
                                        <div class="col-12 col-md-6 align-items-center">
                                            @if($item->resultado->es_homologacion)
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted"><i class="ti ti-certificate text-black me-2"></i>Observación:</small>
                                                    <small class="fw-semibold text-black" title="{{ $item->resultado->observacion_homologacion }}">
                                                        Homologación
                                                    </small>
                                                </div>
                                            @else
                                                @if(isset($item->resultado->total_asistencias))
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted"><i class="ti ti-user-check text-black me-2"></i>Asistencias:</small>
                                                        <small class="fw-semibold text-black">{{ $item->resultado->total_asistencias }}</small>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>

                                        @if(!$escuela->es_por_niveles && (int)$item->resultado->aprobado === 1 && (($item->resultado->creditos_aprobados ?? $item->creditos) !== null))
                                            <div class="col-12 col-md-6 align-items-center">
                                                <div class="d-flex flex-column text-start">
                                                    <small class="text-muted"><i class="ti ti-award text-black me-2"></i>Créditos obtenidos:</small>
                                                    <small class="fw-semibold text-black">
                                                        {{ $item->resultado->creditos_aprobados ?? $item->creditos }} {{ ($item->resultado->creditos_aprobados ?? $item->creditos) == 1 ? 'crédito' : 'créditos' }}
                                                    </small>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-label-secondary mb-0 p-3 text-center">
                                        <i class="ti ti-clock mb-2 fs-4"></i><br>
                                        <small class="fw-semibold">Aún no has cursado este {{ strtolower($tipoItemTexto) }}.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                  @empty
                    <div class="col-12">
                        <div class="alert alert-info py-4 text-center">
                            <i class="ti ti-info-circle fs-1 mb-2"></i>
                            <p class="mb-0">No hay {{ strtolower($tipoItemPlural) }} registradas para esta escuela.</p>
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
                      <img src="{{ Storage::disk('global_media')->url('placeholder.jpg') }}" class="w-100" id="croppingImagePortada" alt="cropper">
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
