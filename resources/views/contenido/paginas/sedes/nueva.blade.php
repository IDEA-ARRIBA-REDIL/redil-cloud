@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Sedes')

<!-- Page -->
@section('page-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
  @vite([
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
  ])


  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')
<script type="module">
  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d"
  });

  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });
  });

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

<script type="text/javascript">
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

<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('sede.crear') }}" enctype="multipart/form-data">
  @csrf


  <div class="row">

    <!-- PORTADA -->
    <div class="col-md-12">
      <div class="card mb-4 rounded rounded-3">
        <img id="preview-foto" class="cropped-img card-img-top mb-2" src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png') }}" alt="Portada">
        <button type="button" style="background-color: rgba(255, 255, 255, 0.5);" class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2" data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;" class="ti ti-camera"></i></button>
        <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada" name="foto">

        <div class="row p-4 m-0 d-flex card-body">
          <h5 class="mb-1 fw-semibold text-black">Nueva sede</h5>
          <p class="mb-4 text-black">Aquí podras ingresar una nueva sede, por favor llena los campos que son requeridos.</p>
        </div>
      </div>
    </div>
    <!-- PORTADA -->

    <!-- Información principal -->
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
          <img src="{{ Storage::url('generales/img/sedes/icono_seccion_informacion_principal.png') }}" alt="icono" class="me-2" width="30">
          Información principal
        </h5>
        <div class="card-body">
          <div class="row">

          <!-- nombre -->
          <div class="mb-3 col-12 col-md-6">
            <label class="form-label" for="nombre">
              Nombre
            </label>
            <input id="nombre" name="nombre" value="{{ old('nombre') }}" onkeypress="return sinComillas(event)" type="text" class="form-control" />
            @if($errors->has('nombre')) <div class="text-danger form-label">{{ $errors->first('nombre') }}</div> @endif
          </div>
          <!-- nombre -->

          <!-- Nivel academico -->
          <div class="mb-3 col-12 col-md-3">
            <label class="form-label" for="tipo_de_sede">
              Tipo de sede
            </label>
            <select id="tipo_de_sede" name="tipo_de_sede" class="select2 form-select" data-allow-clear="true">
              <option  value="">Ninguno</option>
              @foreach ($tiposSedes as $tipoSede)
              <option  value="{{$tipoSede->id}}" {{ old('tipo_de_sede')==$tipoSede->id ? 'selected' : '' }}>{{ucwords ($tipoSede->nombre)}}</option>
              @endforeach
            </select>
            @if($errors->has('tipo_de_sede')) <div class="text-danger form-label">{{ $errors->first('tipo_de_sede') }}</div> @endif
          </div>
          <!-- /Nivel academico -->

          <!-- capacidad -->
          <div class="mb-3 col-12 col-md-3">
            <label class="form-label" for="capacidad">
              Capacidad (Cantidad de sillas)
            </label>
            <input id="capacidad" name="capacidad" value="{{ old('capacidad', 0) }}" type="number" class="form-control" />
            @if($errors->has('capacidad')) <div class="text-danger form-label">{{ $errors->first('capacidad') }}</div> @endif
          </div>
          <!-- capacidad -->

          <!-- Telefono -->
          <div class="mb-3 col-12 col-md-3">
            <label class="form-label" for="telefono_fijo">
              Teléfono
            </label>
            <div class="input-group input-group-merge">
              <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
              <input id="telefono" name="teléfono" value="{{ old('teléfono') }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
            </div>
          </div>
          <!-- /Telefono -->

          <!-- fecha creacion -->
          <div class="mb-3 col-12 col-md-3">
            <label for="fecha_creacion" class="form-label">
              Fecha de creación
            </label>
            <input id="fecha_creacion" value="{{ old('fecha_creación') }}" placeholder="YYYY-MM-DD" name="fecha_creación" class="fecha_nacimiento form-control fecha-picker" type="text" />
            @if($errors->has('fecha_creación')) <div class="text-danger form-label">{{ $errors->first('fecha_creación') }}</div> @endif
          </div>
          <!-- fecha creacion -->

          <!-- Direccion -->
          @if($configuracion->habilitar_direccion_grupo == true)
            @if($configuracion->usa_listas_geograficas==TRUE)
              @livewire('Generales.direccion-con-lista-geografica', ['modulo' => 'sedes'])
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

          <!-- Descripción -->
          <div class="mb-3 col-12">
            <label class="form-label" for="indicaciones_medicas">
              Descripción
            </label>
            <textarea onkeypress="return sinComillas(event)"  id="descripcion" name="descripcion" class="form-control" rows="2"  maxlength="500" spellcheck="false" data-ms-editor="true" placeholder="Escribe aquí la descripción.">{{ old('descripcion') }}</textarea>
            @if($errors->has('descripcion')) <div class="text-danger form-label">{{ $errors->first('descripcion') }}</div> @endif
          </div>
          <!-- /Descripción -->

          @livewire('Grupos.grupos-para-busqueda',[
            'id' => 'grupoId',
            'class' => 'col-12 col-md-6 mb-2',
            'label' => 'Seleccione el grupo principal de la sede',
            'obligatorio' => true,
            'conDadosDeBaja' => 'no',
            'estiloSeleccion' => 'pequeno'
          ])

          <!-- default -->
          <div class="col-12 col-md-6 mb-2">
            <div class=" small fw-medium mb-2">¿Marcar como sede defecto?</div>
            <label class="switch switch-lg">
              <input id="default" name="default" type="checkbox" @checked(old("default")) class="switch-input" />
              <span class="switch-toggle-slider">
                <span class="switch-on">SI</span>
                <span class="switch-off">NO</span>
              </span>
              <span class="switch-label"></span>
            </label>
          </div>
          <!-- / default -->
          </div>
        </div>
      </div>
    </div>
    <!-- Información principal  -->

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
            <button type="submit" class="btn rounded-pill btn-primary crop me-sm-3 me-1" data-bs-dismiss="modal">Guardar</button>
            <button type="reset" class="btn rounded-pill btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ modal foto -->

@endsection
