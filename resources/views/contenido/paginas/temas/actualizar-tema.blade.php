@extends('layouts/layoutMaster')

@section('title', 'Actualizar tema')


@section('vendor-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/quill/typography.scss',
'resources/assets/vendor/libs/quill/editor.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/quill/quill.js'
])
@endsection

@section('page-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

<script type="module">
  const editor = new Quill('#editor', {
    bounds: '#editor',
    placeholder: 'Escribe aquí la respuesta de la persona',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{
          'header': 1
        }, {
          'header': 2
        }],
        [{
          'color': []
        }, {
          'background': []
        }],
        [{
          'align': []
        }],
        [{
          'size': ['small', false, 'large', 'huge']
        }],
        [{
          'header': [1, 2, 3, 4, 5, 6, false]
        }],
        [{
          'font': []
        }],
        [{
          'list': 'ordered'
        }, {
          'list': 'bullet'
        }, {
          'list': 'check'
        }],
        [{
          'indent': '-1'
        }, {
          'indent': '+1'
        }],
        ['link', 'image', 'video'],
        ['clean']
      ],
      imageResize: {
        modules: ['Resize', 'DisplaySize']
      },
    },
    theme: 'snow'
  });

  editor.root.innerHTML = `{!! old( 'contenidoEditor', $tema->contenido) !!}`;

  editor.on('text-change', (delta, oldDelta, source) => {
    $('#contenidoEditor').val(editor.root.innerHTML);
  });
</script>

<script>
  $(document).ready(function() {
    $('.select2').select2({
      dropdownParent: $('#formulario')
    });
  });

  $(".clearAllItems").click(function() {
    value = $(this).data('select');
    $('#' + value).val(null).trigger('change');
  });

  $(".selectAllItems").click(function() {
    value = $(this).data('select');
    $("#" + value + " > option").prop("selected", true);
    $("#" + value).trigger("change");
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
        aspectRatio: 1693 / 376,
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
    cropBtn.addEventListener('click', function(e) {
      e.preventDefault();
      // get result to data uri
      let imgSrc = cropper
        .getCroppedCanvas({
          height: 376,
          width: 1693 // input value aspectRatio: ,
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
  $('#formulario').submit(function() {
    $('.btnGuardar').attr('disabled', 'disabled');

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
<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('tema.update', $tema) }}" enctype="multipart/form-data">
  @csrf
  @method('PATCH')

  <!-- PORTADA -->
  <div class="col-md-12">
    <div class="card mb-4 rounded rounded-3">
      <img id="preview-foto" class="cropped-img card-img-top mb-2" src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/temas/'.$tema->portada) }}" alt="imagen {{$tema->titulo}}">
      <button type="button" style="background-color: rgba(255, 255, 255, 0.5);" class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2" data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;" class="ti ti-camera"></i></button>
      <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada" name="foto">

      <div class="row p-4 m-0 d-flex card-body">
        <h5 class="mb-1 fw-semibold text-black">Nuevo tema</h5>
        <p class="mb-4 text-black">Aquí podras modificar el tema "<b>{{$tema->titulo}}</b>"", por favor llena los campos que son requeridos.</p>
      </div>
    </div>
  </div>
  <!-- PORTADA -->

  <!-- Información principal -->
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header text-black fw-semibold">
        <img src="{{ Storage::url('generales/img/temas/icono_seccion_informacion_principal.png') }}" alt="icono" class="me-2" width="30">
        Información principal
      </h5>
      <div class="card-body">
        <div class="row">
          <!-- Nombre -->
          <div class="col-md-6 mb-2 px-2 ">
            <label class="form-label" for="nombre_tema">
              Nombre del tema
            </label>
            <input id="nombre_tema" name="nombre_del_tema" value="{{ old('nombre_del_tema', $tema->titulo) }}" type="text" class="form-control" />
            @if($errors->has('nombre_del_tema')) <div class="text-danger form-label">{{ $errors->first('nombre_del_tema') }}</div> @endif
            <div class="text-danger form-label"></div>
          </div>
          <!-- /Nombre -->

          <!-- URL ENLACE -->
          <div class="col-md-6 mb-2 px-2 ">
            <label class="form-label" for="url_externo">
              Url de enlace externo
            </label>
            <input id="url_externo" name="url_externo" value="{{ old('url_externo', $tema->url) }}" type="text" class="form-control" />
          </div>
          <!-- /URL ENLACE  -->

          <!--  Categoria -->
          <div class="col-md-12 mb-2 px-2">
            <label for="filtroPorCategoria" class="form-label">¿A que categorías pertenece?</label>
            <select id="categorias" name="categorias[]" class="select2 form-select" multiple>
              @foreach($categorias as $categoria)
              <option value="{{ $categoria->id }}" {{ in_array($categoria->id, old('categorias', $temas_categoria)) ? "selected" : "" }}>{{ $categoria->nombre }}</option>
              @endforeach
            </select>
          </div>
          <!-- / Categoria  -->

          <!-- Editor -->
          <div class="col-12 mb-2 px-2">
            <label for="filtroPorCategoria" class="form-label">Contenido del tema</label>

            <div id="editor"></div>
            <input id="contenidoEditor" name="contenidoEditor" class='d-none'>
          </div>
          <!-- /Editor -->

          <!--  Tipo Usuario -->
          <div class="col-md-12 mb-2 px-2">
            <label for="filtroPorTipoUsuarios" class="form-label">¿Qué tipos de usuarios pueden ver el tema?</label>
            <select id="tipoUsuarios" name="tipoUsuarios[]" class="select2 form-select" multiple>
              @foreach($tiposUsuarios as $tipoUsuario)
              <option value="{{ $tipoUsuario->id }}" {{ in_array($tipoUsuario->id, old('tipoUsuarios', $tiposUsuarioTema)) ? "selected" : "" }}>{{ $tipoUsuario->nombre }}</option>
              @endforeach
            </select>
          </div>
          <!-- / Tipo Usuario -->

          <!--  Sede -->
          <div class="col-md-12 mb-2 px-2">
            <label for="filtroPorSede" class="form-label">¿Qué sedes pueden ver el tema?</label>
            <select id="sedes" name="sedes[]" class="select2 form-select" multiple>
              @foreach($sedes as $sede)
              <option value="{{ $sede->id }}" {{ in_array($sede->id, old('sedes', $sedesTema)) ? "selected" : "" }}>{{ $sede->nombre }}</option>
              @endforeach
            </select>
          </div>
          <!-- / Sede  -->

          <!--  Tipo Grupo -->
          <div class="col-md-12 mb-2 px-2">
            <label for="filtroPorTipoGrupo" class="form-label">¿Qué tipos de grupo pueden ver?</label>
            <select id="tipoGrupo" name="tipoGrupo[]" class="select2 form-select" multiple>
              @foreach($tiposGrupo as $tipo)
              <option value="{{ $tipo->id }}" {{ in_array($tipo->id, old('tipoGrupo', $tiposGrupoTema)) ? "selected" : "" }}>{{ $tipo->nombre }}</option>
              @endforeach
            </select>
          </div>
          <!-- / Tipo Grupo  -->

          <!--  Grupos -->
          <div class="col-md-12 mb-2 px-2">
            @livewire('Grupos.grupos-para-busqueda', [
            'id' => 'inputGruposIds',
            'class' => 'col-12 col-md-12 mb-3',
            'label' => '¿Qué grupos pueden ver el tema?',
            'gruposSeleccionadosIds' => old('inputGruposIds',$gruposSeleccionadosIds) ? ( is_array(old('inputGruposIds',$gruposSeleccionadosIds)) ? old('inputGruposIds',$gruposSeleccionadosIds) : json_decode(old('inputGruposIds',$gruposSeleccionadosIds)) ) : [] ,
            'conDadosDeBaja' => 'no',
            'multiple' => TRUE,
            ])
          </div>
          <!-- / Grupos  -->

        </div>
      </div>
    </div>
  </div>
  <!-- Información principal  -->

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2">Guardar</button>
    </div>
  </div>
  <!-- /botonera -->
</form>

<!-- modal foto-->
<div class="modal fade modal-img" id="modalFoto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-simple modal-edit-user">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class=" btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
          <button type="reset" class="btn rounded-pill  btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ modal foto -->
@endsection