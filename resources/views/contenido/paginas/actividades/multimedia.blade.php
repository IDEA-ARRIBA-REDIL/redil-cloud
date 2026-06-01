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
    ///confirmación para eliminar portada
    $('.confirmacionEliminarPortada').on('click', function() {

        let id = $(this).data('id');

        Swal.fire({
            title: "¿Estás seguro que deseas eliminar la portada?"
            , html: "Esta acción restablecerá la portada de la actividad a la predeterminada global."
            , icon: "warning"
            , showCancelButton: true
            , confirmButtonText: 'Sí, eliminar'
            , cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#eliminarPortadaForm').attr('action', "/actividades/" + id + "/eliminar-portada");
                $('#eliminarPortadaForm').submit();
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
      inputPortadaNombre = document.querySelector('#portada-nombre'),
      uploadStatus = document.querySelector('#upload-status'),
      cropper = '';

    setTimeout(() => {
      cropper = new Cropper(croppingImagePortada, {
        zoomable: false,
        aspectRatio: 16 / 5,
        cropBoxResizable: true
      });
    }, 1000);

    upload.addEventListener('change', function(e) {
      if (e.target.files.length) {
        var fileType = e.target.files[0].type;
        if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
          cropper.destroy();
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

    cropBtnPortada.addEventListener('click', function(e) {
      e.preventDefault();

      cropBtnPortada.disabled = true;
      uploadStatus.style.display = 'block';
      uploadStatus.textContent = 'Subiendo imagen...';
      uploadStatus.className = 'text-primary fw-bold';

      cropper.getCroppedCanvas({
        width: 1000
      }).toBlob(function(blob) {
        var formData = new FormData();
        formData.append('portada', blob, 'portada.png');
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("actividades.uploadPortada") }}', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            inputPortadaNombre.value = data.nombre;
            uploadStatus.textContent = 'Imagen subida correctamente.';
            uploadStatus.className = 'text-success fw-bold';

            fetch('{{ route("actividades.updatePortada", $actividad) }}', {
              method: 'PATCH',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({ portada_nombre: data.nombre })
            })
            .then(response => response.json())
            .then(res => {
              if (res.success) {
                uploadStatus.textContent = 'Portada guardada correctamente.';
                setTimeout(() => { location.reload(); }, 1000);
              } else {
                uploadStatus.textContent = 'Error al guardar la portada.';
                uploadStatus.className = 'text-danger fw-bold';
                cropBtnPortada.disabled = false;
              }
            })
            .catch(() => {
              uploadStatus.textContent = 'Error al guardar la portada.';
              uploadStatus.className = 'text-danger fw-bold';
              cropBtnPortada.disabled = false;
            });
          } else {
            uploadStatus.textContent = 'Error al subir la imagen.';
            uploadStatus.className = 'text-danger fw-bold';
            cropBtnPortada.disabled = false;
          }
        })
        .catch(() => {
          uploadStatus.textContent = 'Error al subir la imagen.';
          uploadStatus.className = 'text-danger fw-bold';
          cropBtnPortada.disabled = false;
        });
      }, 'image/png');
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
            <span class="align-middle">Subir portada </span>
        </button>
    </div>
    @if ($actividad->portada && $actividad->portada !== 'default.png')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Portada Actividad (Personalizada)</h5>
            <button data-id="{{ $actividad->id }}" type="button" class="btn btn-danger confirmacionEliminarPortada float-end py-2 px-3">
                <i class="ti ti-trash"></i> Eliminar Portada
            </button>
        </div>
        <div class="card-body">

            <img src="{{ $actividad->portada_url }}" alt="Portada actividad" class="rounded-top w-100">

        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header">
            <h5>Portada Predeterminada (Global)</h5>
        </div>
        <div class="card-body">

            <img src="{{ $actividad->portada_url }}" alt="Portada predeterminada" class="rounded-top w-100">

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
<div class="modal fade modal-img" id="modalPortada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2"><i class="ti ti-camera  ti-lg"></i> Subir portada</h3>
                    <p class="text-muted">Selecciona y recorta tu portada</p>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="mb-2">
                            <label class="mb-2"><span class="fw-bold">Paso #1</span> Selecciona tu
                                portada</label><br>
                            <input class="form-control" type="file" id="cropperImageUploadPortada">
                        </div>
                        <div class="mb-2">
                            <label class="mb-2"><span class="fw-bold">Paso #2</span> Recorta tu
                                portada</label><br>
                            <center>
                                <img src="{{ Storage::url('generales/img/otros/placeholder.jpg') }}" class="w-100" id="croppingImagePortada" alt="cropper">
                            </center>
                            <input class="form-control d-none" type="text" value="" id="portada-nombre" name="portada_nombre">
                        </div>
                        <div id="upload-status" style="display:none;" class="text-center mt-2"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-center">
                <div class="col-12 text-center">
                    <button type="button" id="cropSubmitPortada" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                    <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ modal foto -->
<!--/ modal - form para eliminar portada-->
<form id="eliminarPortadaForm" method="POST" action="">
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
