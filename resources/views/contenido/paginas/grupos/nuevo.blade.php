@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Grupos')

<!-- Page -->
@section('page-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/js/app.js',
])


<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')

<script type="module">

  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d"
  });

  $(".hora-picker").flatpickr({
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
  });

  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });
  });
</script>

<script>
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron =/[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }
</script>

<script type="module">

  $(function () {
    'use strict';

    var croppingImage = document.querySelector('#croppingImage'),
      //img_w = document.querySelector('.img-w'),
      cropBtn = document.querySelector('.crop'),
      croppedImg = document.querySelector('.cropped-img'),
      dwn = document.querySelector('.download'),
      upload = document.querySelector('#cropperImageUpload'),
      modalImg = document.querySelector('.modal-img'),
      inputResultado = document.querySelector('#imagen-recortada'),
      cropper = '';

    setTimeout(() => {
      cropper = new Cropper( croppingImage, {
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
              croppingImage.src = e.target.result;
              cropper = new Cropper(croppingImage, {
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
    cropBtn.addEventListener('click', function (e) {
      e.preventDefault();
      // get result to data uri
      let imgSrc = cropper
        .getCroppedCanvas({
          height: 376,
          width: 1693 // input value
        })
        .toDataURL();
      croppedImg.src = imgSrc;
      inputResultado.value = imgSrc;
      //dwn.setAttribute('href', imgSrc);
      //dwn.download = 'imagename.png';
    });
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


@include('layouts.status-msn')

<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('grupo.crear') }}" enctype="multipart/form-data">
  @csrf

  <div class="row">
    <!-- PORTADA -->
    <div class="col-md-12">
      <div class="card mb-4 rounded rounded-3">
        <img id="preview-foto" class="cropped-img card-img-top mb-2" src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png') }}" alt="Portada">
        <button type="button" style="background-color: rgba(255, 255, 255, 0.5);" class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2" data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;" class="ti ti-camera"></i></button>
        <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada" name="foto">

        <div class="row p-4 m-0 d-flex card-body">
          <h5 class="mb-1 fw-semibold text-black">Nuevo grupo</h5>
          <p class="mb-4 text-black">Aquí podras ingresar un nuevo grupo, por favor llena los campos que son requeridos.</p>
        </div>
      </div>
    </div>
    <!-- PORTADA -->

    <!-- Información principal -->
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
          <img src="{{ Storage::url('generales/img/grupos/icono_seccion_informacion_principal.png') }}" alt="icono" class="me-2" width="30">
          Información principal
        </h5>
        <div class="card-body">
          <div class="row">
            <!-- nombre -->
            @if($configuracion->habilitar_nombre_grupo)
            <div class="mb-3 col-12 col-md-3">
              <label class="form-label" for="nombre">
                Nombre
              </label>
              <input id="nombre" name="nombre" value="{{ old('nombre') }}" onkeypress="return sinComillas(event)" type="text" class="form-control" />
              @if($errors->has('nombre')) <div class="text-danger form-label">{{ $errors->first('nombre') }}</div> @endif
            </div>
            @endif
            <!-- nombre -->

            <!--  Tipo de grupo  -->
            @if($configuracion->habilitar_tipo_grupo)
            <div class="mb-3 col-12 col-md-3">
              <label class="form-label" for="tipo_grupo">
                ¿Qué tipo de grupo es?
              </label>
              <select id="tipo_grupo" name="tipo_de_grupo" class="select2 form-select" data-allow-clear="true">
                <option value="" selected>Ninguno</option>
                @foreach ($tipoGrupos as $tipoGrupo)
                <option value="{{$tipoGrupo->id}}" {{ old('tipo_de_grupo')==$tipoGrupo->id ? 'selected' : '' }}>{{$tipoGrupo->nombre}}</option>
                @endforeach
              </select>
              @if($errors->has('tipo_de_grupo')) <div class="text-danger form-label">{{ $errors->first('tipo_de_grupo') }}</div> @endif
            </div>
            @endif
            <!--  Tipo de grupo  -->

            <!-- fecha -->
            @if($configuracion->habilitar_fecha_creacion_grupo)
            <div class="mb-3 col-12 col-md-3">
              <label class="form-label" for="fecha">
                {{ $configuracion->label_fecha_creacion_grupo ? $configuracion->label_fecha_creacion_grupo : 'Fecha de creación'}}
              </label>
              <input id="fecha" value="{{ old('fecha') }}" placeholder="YYYY-MM-DD" name="fecha" class="fecha form-control fecha-picker" type="text" />
              @if($errors->has('fecha')) <div class="text-danger form-label">{{ $errors->first('fecha') }}</div> @endif
            </div>
            @endif
            <!-- fecha -->

            <!-- Telefono -->
            @if($configuracion->habilitar_telefono_grupo)
            <div class="mb-3 col-12 col-md-3">
              <label class="form-label" for="telefono">
                Teléfono
              </label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
                <input id="telefono" name="teléfono" value="{{ old('teléfono') }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
              </div>
              @if($errors->has('teléfono')) <div class="text-danger form-label">{{ $errors->first('teléfono') }}</div> @endif
            </div>
            @endif
            <!-- /Telefono fijo -->

            <!-- vivienda en calidad de -->
            @if($configuracion->habilitar_tipo_vivienda_grupo)
            <div class="mb-3 col-12 col-md-6">
              <label class="form-label" for="vivienda_en_calidad_de">
                Vivienda en calidad de
              </label>
              <select id="vivienda_en_calidad_de" name="tipo_de_vivienda" class="select2 form-select" data-allow-clear="true">
                <option  value="">Ninguno</option>
                @foreach ($tiposDeVivienda as $tipoDeVivienda)
                <option  value="{{$tipoDeVivienda->id}}" {{ old('tipo_de_vivienda')==$tipoDeVivienda->id ? 'selected' : '' }}>{{ucwords ($tipoDeVivienda->nombre)}}</option>
                @endforeach
              </select>
              @if($errors->has('tipo_de_vivienda')) <div class="text-danger form-label">{{ $errors->first('tipo_de_vivienda') }}</div> @endif
            </div>
            @endif
            <!-- /vivienda en calidad de -->

            <!-- Direccion -->
            @if($configuracion->habilitar_direccion_grupo == true)
              @if($configuracion->usa_listas_geograficas==TRUE)
                @livewire('Generales.direccion-con-lista-geografica', ['modulo' => 'grupos', 'classDireccion' => 'mb-3 col-12 col-md-6'])
              @else
                <div class="mb-3 col-12 col-md-6">
                  <label class="form-label" for="direccion">
                    @if($configuracion->direccion_grupo_obligatorio) <span class="badge badge-dot bg-info me-1"></span>@endif
                    @if($configuracion->label_direccion_grupo!="")
                    {{$configuracion->label_direccion_grupo}}
                    @else
                    Dirección
                    @endif
                  </label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                    <input onkeypress="return sinComillas(event)" id="direccion" name="dirección" value="{{ old('dirección') }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true" placeholder="Digita la dirección, la ciudad y el país, donde vives.">
                  </div>
                  @if($errors->has('dirección')) <div class="text-danger form-label">{{ $errors->first('dirección') }}</div> @endif
                </div>
              @endif
            @endif
            <!-- Direccion -->

            <!-- Campo opcional -->
            @if($configuracion->habilitar_campo_opcional1_grupo)
            <div class="mb-3 col-12 col-md-12">
              <label class="form-label" for="campo_opcional1">
                {{ $configuracion->label_campo_opcional1 }}
              </label>
              <textarea onkeypress="return sinComillas(event)" id="campo_opcional1" name="adiccional" class="form-control" rows="2" spellcheck="false" data-ms-editor="true" placeholder="">{{ old('adiccional') }}</textarea>
              @if($errors->has('adiccional')) <div class="text-danger form-label">{{ $errors->first('adiccional') }}</div> @endif
            </div>
            @endif
            <!-- /Campo opcional -->

            @if($configuracion->version==2)
            <!-- AMO -->
            <div class="mb-2 col-12 col-md-4">
              <div class=" small fw-medium mb-2">¿Este Grupo tiene AMO?</div>
              <label class="switch switch-lg">
                <input id="amo" name="amo" type="checkbox" @checked(old("amo")) class="switch-input" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">SI</span>
                  <span class="switch-off">NO</span>
                </span>
                <span class="switch-label"></span>
              </label>
            </div>
            <!-- / AMO -->
            @endif
          </div>
        </div>
      </div>
    </div>
    <!-- Información principal  -->

    <!-- Horario -->
    @if($configuracion->habilitar_dia_reunion_grupo || $configuracion->habilitar_hora_reunion_grupo)
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
          <img src="{{ Storage::url('generales/img/grupos/icono_seccion_horarios.png') }}" alt="icono" class="me-2" width="30">
          {{$configuracion->titulo_seccion_reunion_grupo ? $configuracion->titulo_seccion_reunion_grupo : '¿En qué horario se reúne grupo?'}}
        </h5>
        <div class="card-body">
          <div class="row">
            <!-- fecha -->
            @if($configuracion->habilitar_dia_reunion_grupo)
            <div class="mb-3 col-12 col-md-6">
              <label class="form-label" for="dia_reunion">
                {{ $configuracion->label_campo_dia_reunion_grupo }}
              </label>
              <select id="dia_reunion" name="día_de_reunión" class="select2 form-select" data-allow-clear="true">
                <option value="" selected>Ninguno</option>
                @foreach (Helper::diasDeLaSemana() as $dia)
                <option value="{{$dia->id}}" {{ old('día_de_reunión')==$dia->id ? 'selected' : '' }}>{{$dia->nombre}} {{$dia->id}}</option>
                @endforeach
              </select>
              @if($errors->has('día_de_reunión')) <div class="text-danger form-label">{{ $errors->first('día_de_reunión') }}</div> @endif
            </div>
            @endif
            <!-- /fecha -->

            <!-- hora -->
            @if($configuracion->habilitar_hora_reunion_grupo)
            <div class="mb-2 col-12 col-md-6">
              <label class="form-label" for="hora_reunion">
                {{$configuracion->label_campo_hora_reunion_grupo }}
              </label>
              <input id="hora_reunion" value="{{ old('hora_de_reunión') }}" placeholder="HH-MM" name="hora_de_reunión" class="fecha form-control hora-picker" type="text" />
              @if($errors->has('hora_de_reunión')) <div class="text-danger form-label">{{ $errors->first('hora_de_reunión') }}</div> @endif
            </div>
            @endif
            <!-- /hora -->
          </div>
        </div>
      </div>
    </div>
    @endif
    <!-- Horario -->

    <!-- Campos extras -->
    @if($configuracion->visible_seccion_campos_extra_grupo == TRUE && $rolActivo->hasPermissionTo('grupos.visible_seccion_campos_extra_grupo') )
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
          <img src="{{ Storage::url('generales/img/grupos/icono_seccion_campos_extras.png') }}" alt="icono" class="me-2" width="30">
          {{$configuracion->label_seccion_campos_extra}}
        </h5>
        <div class="card-body">
          <div class="row">

            @foreach($camposExtras as $campo)
              @if($campo->visible != FALSE)
                <div class="mb-3 {{$campo->class_col}}">
                  <label class="form-label" for="{{$campo->class_id}}">
                    {{$campo->nombre}}
                  </label>

                  <!-- campo tipo 1 -->
                  @if($campo->tipo_de_campo == 1 && $campo->visible)
                    <input id="{{$campo->class_id}}" name="{{$campo->class_id}}" value="{{ old($campo->class_id) }}" class="form-control">
                  @endif
                  <!-- /campo tipo 1 -->

                  <!-- campo tipo 2 -->
                  @if($campo->tipo_de_campo == 2 && $campo->visible)
                    <textarea id="{{$campo->class_id}}" name="{{$campo->class_id}}" class="form-control">{{ old($campo->class_id) }}</textarea>
                  @endif
                  <!-- /campo tipo 2 -->

                  <!-- campo tipo 3 -->
                  @if($campo->tipo_de_campo == 3 && $campo->visible)
                    <select id="{{$campo->class_id}}" name="{{$campo->class_id}}" class="form-control">
                      <option value="">Ninguno</option>
                      @foreach (json_decode($campo->opciones_select) as $opcion)
                        <option value="{{$opcion->value}}" {{ old($campo->class_id)==$opcion->value ? 'selected' : '' }} > {{ ucwords($opcion->nombre) }} </option>
                      @endforeach
                    </select>
                  @endif
                  <!-- /campo tipo 3 -->

                  <!-- campo tipo 4 -->
                  @if($campo->tipo_de_campo == 4 && $campo->visible)
                    <select id="{{$campo->class_id}}" name="{{$campo->class_id}}[]" multiple class="select2 form-control">
                      @foreach (json_decode($campo->opciones_select) as $opcion)
                        <option value="{{$opcion->value}}" {{ in_array($opcion->value, old($campo->class_id, []))  ? "selected" : "" }}>  {{ ucwords($opcion->nombre) }} </option>
                      @endforeach
                    </select>
                  @endif
                  <!-- /campo tipo 4 -->

                  @if($errors->has($campo->class_id)) <div class="text-danger form-label">{{ $errors->first($campo->class_id) }}</div> @endif
                </div>
              @endif
            @endforeach

          </div>
        </div>
      </div>
    </div>
    @endif
    <!-- Campos extras -->

  </div>

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2" >Guardar</button>
    </div>
  </div>
  <!-- /botonera -->

</form>

  <!-- modal foto-->
  <div class="modal fade modal-img" id="modalFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-simple modal-edit-user">
      <div class="modal-content">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="text-center mb-4">
            <h3 class="mb-2"><i class="ti ti-camera  ti-lg"></i> Subir foto</h3>
            <p class="text-muted">Selecciona y recorta la foto</p>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="mb-2">
                <label class="mb-2"><span class="fw-bold">Paso #1</span> Selecciona la foto</label><br>
                <input class="form-control" type="file" id="cropperImageUpload">
              </div>
              <div class="mb-2">
                <label class="mb-2"><span class="fw-bold">Paso #2</span> Recorta la foto</label><br>
                <center>
                <img src="{{ Storage::url('generales/img/otros/placeholder.jpg') }}" class="w-100" id="croppingImage" alt="cropper">
                </center>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer text-center">
          <div class="col-12 text-center">
            <button type="submit" class="btn rounded-pill  btn-primary crop me-sm-3 me-1" data-bs-dismiss="modal">Guardar</button>
            <button type="reset" class="btn rounded-pill  btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ modal foto -->

@endsection
