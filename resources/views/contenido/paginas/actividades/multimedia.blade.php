@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Actividades')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

<!-- Page -->
@section('page-style')


@section('vendor-style')
<style>
    .color-picker-container {
        width: 100px;
        /* Ajusta este valor al tamaño que necesites */

    }

    .pickr .pcr-button {
        height: 38px !important;
        width: 40px !important;
        border: solid 1px #3e3e3e;
    }

</style>


@vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])


@endsection


@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])

@endsection


@section('page-script')

<script type="module">
    function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron = /[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }
</script>

<script>
    ///confirmación para eliminar tema
    $('.confirmacionEliminar').on('click', function() {

        let id = $(this).data('id');

        Swal.fire({
            title: "¿Estás seguro que deseas eliminar el banner?"
            , html: "Esta acción no es reversible."
            , icon: "warning"
            , showCancelButton: false
            , confirmButtonText: 'Si, eliminar'
            , cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#eliminarBanner').attr('action', "/actividades/" + id + "/eliminar-banner");
                $('#eliminarBanner').submit();
            }
        })
    });

    ///confirmación para eliminar tema
    $('.confirmacionEliminarVideo').on('click', function() {

        let id = $(this).data('id');

        Swal.fire({
            title: "¿Estás seguro que deseas eliminar el video?"
            , html: "Esta acción no es reversible."
            , icon: "warning"
            , showCancelButton: false
            , confirmButtonText: 'Si, eliminar'
            , cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#eliminarBanner').attr('action', "/actividades/" + id + "/eliminar-video");
                $('#eliminarBanner').submit();
            }
        })
    });

</script>

<!-- foto portada -->
<script type="module">
    $(function() {
    'use strict';

    var croppingImagePortada = document.querySelector('#croppingImagePortada'),
      cropBtnPortada = document.querySelector('#cropSubmitPortada'),
      upload = document.querySelector('#cropperImageUploadPortada'),
      inputResultadoPortada = document.querySelector('#imagen-recortada-portada'),
      formularioPortada = document.querySelector('#formularioPortada'),
      cropper = '';

    setTimeout(() => {
      cropper = new Cropper(croppingImagePortada, {
        zoomable: false,
        aspectRatio: 16 / 5,
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
              croppingImagePortada.src = e.target.result;
              cropper = new Cropper(croppingImagePortada, {
                zoomable: false,
                aspectRatio: 16 / 5,
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
    cropBtnPortada.addEventListener('click', function(e) {
      e.preventDefault();

      // get result to data uri
      let imgSrc = cropper
        .getCroppedCanvas({
          width: 1000 // input value
        })
        .toDataURL();

      inputResultadoPortada.value = imgSrc;
      cropBtnPortada.disabled = true;
      formularioPortada.submit();
    });
  });
</script>
<!-- foto portada -->


@endsection


@section('content')


<h4 class="mb-1 fw-semibold text-primary">Gestión multimedia</h4>
<p class="mb-4 text-dark">Crea y asigna banners y videos para tu actividad: <b>{{ $actividad->nombre }}</b></p>

@include('layouts.status-msn')

<div class="row">
    <div class="col-lg-3 col-sm-12 mb-3">
        <button type="button" class="btn-primary rounded-pill float-start waves-effect waves-light  text-white p-3" data-bs-toggle="modal" data-bs-target="#modalPortada">

            <i style="padding-left: 5px;" class="ti ti-camera"></i>
            <span class="align-middle">crear banner </span>
        </button>
    </div>
    @if (isset($bannerActual->id))
    <div class="card">
        <div class="card-header">
            <h5>Banner Actividad </h5>
        </div>
        <div class="card-body">

            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $bannerActual->nombre) : $configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $bannerActual->nombre }}" alt="Banner image" class="rounded-top">

        </div>

        <div class="card-footer">
            <button data-id="{{ $bannerActual->id }}" type="button" class="btn ms-3 btn-editar-input btn-secondary confirmacionEliminar float-end p-1_5">
                <i class="ti ti-trash"></i>
            </button>
        </div>
    </div>
    @endif
</div>

<div class="row mt-5">
    <div class="col-lg-3 col-sm-12 mb-3">
        <button type="button" class="btn-primary rounded-pill float-start waves-effect waves-light  text-white p-3" data-bs-toggle="modal" data-bs-target="#modalVideo">

            <i style="padding-left: 5px;" class="ti ti-video"></i>
            <span class="align-middle">Crear Video</span>
        </button>
    </div>
    @if (isset($video->id))
    <div class="card">
        <div class="card-header">
            <h5>Video Actividad </h5>
        </div>
        <div class="card-body">

            <iframe width="100%" height="415" src="https://www.youtube.com/embed/{{ $video->url }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
            </iframe>

        </div>

        <div class="card-footer">
            <button data-id="{{ $video->id }}" type="button" class="btn ms-3 btn-editar-input btn-secondary confirmacionEliminarVideo float-end p-1_5">
                <i class="ti ti-trash"></i>
            </button>
        </div>
    </div>
    @endif
</div>


<!--/ modal - form para cargar el banner de la actividad -->
<form id="formularioPortada" role="form" class="forms-sample" method="POST" action="{{ route('actividades.uploadBanner', $actividad) }}" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <div class="modal fade modal-img" id="modalPortada" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-camera  ti-lg"></i> Subir banner</h3>
                        <p class="text-muted">Selecciona y tu banner</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label class="mb-2"><span class="fw-bold">Paso #1</span> Selecciona tu
                                    banner</label><br>
                                <input class="form-control" type="file" id="cropperImageUploadPortada">
                            </div>
                            <div class="mb-2">
                                <label class="mb-2"><span class="fw-bold">Paso #2</span> Recorta tu
                                    banner</label><br>
                                <center>
                                    <img src="{{ Storage::url('generales/img/otros/placeholder.jpg') }}" class="w-100" id="croppingImagePortada" alt="cropper">
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
<!--/ modal - form para eliminar banner-->
<form id="eliminarBanner" method="POST" action="">
    @csrf
</form>

<form id="formularioVideo" role="form" class="forms-sample" method="POST" action="{{ route('actividades.newVideo', $actividad) }}" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <div class="modal fade modal-img" id="modalVideo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-camera  ti-lg"></i> Crear video</h3>
                        <p class="text-muted">Escribe la direccion url de tu video de youtube</p>
                    </div>

                    <div class="row">

                        <div class="col-12 mb-2">
                            <label> Nombre </label>
                            <input id='nombre' name="nombre" type="text" class="form-control">
                        </div>
                        <div class="col-12 mb-2">
                            <div class="mt-2 mb-2">
                                <label> URL (https://www.youtube.com/watch?v=<b>pI2sYS3ov0w) </b> solo copia el codigo
                                    de tu enlace, tomando como ejemplo el texto en negrilla </label>
                            </div>
                            <input id='iframe' name="iframe" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer mt-3 text-center">
                        <div class="col-12 text-center">
                            <button type="submit" id="btnVideo" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ modal foto -->
</form>



@endsection
