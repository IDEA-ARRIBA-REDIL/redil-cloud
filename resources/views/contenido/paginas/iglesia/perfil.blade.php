@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@section('page-script')
<script type="module">
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron = /[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }

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
<script type="module">
  $(function() {
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
      cropper = new Cropper(croppingImage, {
        zoomable: false,
        aspectRatio: 1,
        cropBoxResizable: true
      });
    }, 1000);

    // on change show image with crop options
    upload.addEventListener('change', function(e) {
      if (e.target.files.length) {
        console.log(e.target.files[0]);
        var fileType = e.target.files[0].type;
        if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
          cropper.destroy();
          // start file reader
          const reader = new FileReader();
          reader.onload = function(e) {
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
    cropBtn.addEventListener('click', function(e) {
      e.preventDefault();
      // get result to data uri
      let imgSrc = cropper
        .getCroppedCanvas({
          width: 400 // input value
        })
        .toDataURL();
      croppedImg.src = imgSrc;
      inputResultado.value = imgSrc;
      //dwn.setAttribute('href', imgSrc);
      //dwn.download = 'imagename.png';
    });
  });
</script>
@endsection

@section('content')



<h4 class="mb-1">Gestiona tu iglesia</h4>
<p class="mb-4">Aquí podrás configurar y actualizar la información de tu congregación.</p>

@include('layouts.status-msn')

<form id="formulario" role="form" class="
    forms-sample" method="POST" action="{{ route('iglesia.update', $iglesia) }}" enctype="multipart/form-data">
  @csrf
  @method('PATCH')
  <!-- PORTADA -->
  <center>
    <img id="preview-foto" src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/iglesia/'.$iglesia->logo) }}" class="cropped-img  avatar-initial rounded-circle border border-5 border-white bg-info mb-2" src="" alt="imagen">
  </center>

  <div class="col-12 mb-5">
    <center>
      <button type="button" class="btn rounded-pill  btn-icon-text btn-primary" data-bs-toggle="modal" data-bs-target="#modalFoto">
        <i class="ti ti-camera px-1"></i>Subir logo
      </button>
    </center>
    <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada" name="foto">
  </div>
  <!-- PORTADA -->

  <div class="card p-4 w-100">
    <div class="card-header px-0 py-1">
      <h5>Información básica</h5>
    </div>
    <div class="row">
      <div class="col-4 mb-3">
        <span class="badge badge-dot bg-info me-1"></span>
        <label class="form-label">Nombre</label>
        <input required type="text" value="{{ $iglesia->nombre }}" id="" name="nombre" class="form-control">
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Fecha de creación de la iglesia</label>
        <input type="text" value="{{ $iglesia->fecha_apertura }}" name="fechaApertura" placeholder="YYYY-MM-DD"
          class="fecha form-control fecha-picker">
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Fecha de Suscripción de la Iglesia</label>
        <input type="text" value="{{ $iglesia->fecha_suscripcion }}" name="fechaSuscripcion" placeholder="YYYY-MM-DD"
          class="fecha form-control fecha-picker">
      </div>

      <!-- Segunda fila -->
      <div class="col-4 mb-3">
        <label class="form-label">Cantidad Estimada de Membresía</label>
        <input type="number" value="{{ $iglesia->membresia_estimada }}" name="cantidadMembresia" class="form-control">
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Teléfono fijo</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
          <input type="text" value="{{ $iglesia->telefono1 }}" name="telefonoFijo" class="form-control">
        </div>
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Otro Teléfono</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
          <input type="text" value="{{ $iglesia->telefono2 }}" name="otroTelefono" class="form-control">
        </div>
      </div>
    </div>
  </div>


  <!-- Segunda Card -->
  <div class="card p-4 w-100 mt-4">
    <div class="card-header px-0 py-1">
      <h5>Ubicación</h5>
    </div>
    <div class="row">
      <!-- Primera fila -->
      <div class="col-4 mb-3">
        <label class="form-label">Continente</label>
        <select id="continente" name="continente" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($continentes as $continente)
          <option @if ($continente->id == $iglesia->continente_id) selected @endif value="{{$continente->id}}">
            {{$continente->nombre}}
          </option>
          @endforeach
        </select>
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">País</label>
        <select id="pais" name="pais" class="grupoSelect select2 selectorGenero form-select" data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($paises as $pais)
          <option @if ($pais->id == $iglesia->pais_id) selected @endif
            value={{ $pais->id }}>{{ $pais->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Región</label>
        <select id="region" name="region" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($regiones as $region)
          <option @if ($region->id == $iglesia->region_id) selected @endif
            value={{ $region->id }}>{{ $region->nombre }}</option>
          @endforeach
        </select>
      </div>

      <!-- Segunda fila -->
      <div class="col-4 mb-3">
        <label class="form-label">Departamento</label>
        <select id="departamento" name="departamento" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($departamentos as $departamento)
          <option @if ($departamento->id == $iglesia->departamento_id) selected @endif value={{ $departamento->id }}>{{ $departamento->nombre }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-4 mb-3">
        <label class="form-label">Ciudad</label>
        <select id="ciudad" name="ciudad" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($ciudades as $ciudad)
          <option @if ($ciudad->id == $iglesia->municipio_id) selected @endif value={{ $ciudad->id }}>{{ $ciudad->nombre }}</option>
          @endforeach
        </select>
      </div>

    </div>
    <div class="mb-2 col-12 col-md-6">
      <label class="form-label">Dirección</label>
      <div class="input-group input-group-merge">
        <span class="input-group-text"><i class="ti ti-map"></i></span>
        <input onkeypress="return sinComillas(event)" id="direccion" name="direccion" value="{{ $iglesia->direccion }}"
          type="text" class="form-control" spellcheck="false" data-ms-editor="true"
          placeholder="Digita la dirección, la ciudad y el país, donde vives.">
      </div>
    </div>
  </div>
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btn-primary rounded-pill me-1 btnGuardar">Guardar</button>
      <button type="reset" class="btn rounded-pill btn-label-secondary">Cancelar</button>
    </div>
    <div class="p-2 bd-highlight">
      <p class="text-muted"><span class="badge badge-dot bg-info me-1"></span> Campos obligatorios</p>
    </div>
  </div>

</form>
</div>

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
          <button type="submit" class="btn btn-primary rounded-pill crop me-sm-3 me-1" data-bs-dismiss="modal">Guardar</button>
          <button type="reset" class="btn rounded-pill btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ modal foto -->

@endsection
