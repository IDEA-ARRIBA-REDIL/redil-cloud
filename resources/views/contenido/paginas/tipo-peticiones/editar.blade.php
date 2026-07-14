@php
$configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Editar tipo de petición')

@section('page-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css',
  'resources/assets/vendor/libs/quill/typography.scss',
  'resources/assets/vendor/libs/quill/editor.scss'
])
<style>
    .img-container {
        min-height: 300px;
        max-height: 80vh;
        background-color: #f7f7f7;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }
    .img-container img {
        display: block;
        max-width: 100%;
    }
</style>
@endsection

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js',
  'resources/assets/vendor/libs/quill/quill.js'
])
@endsection

@section('page-script')
<script type="module">
  $(function() {
    // Lógica para reemplazar el banner actual
    $('#btn-reemplazar').on('click', function() {
      $('#info-banner-actual').addClass('d-none');
      $('#contenedor-input-banner').removeClass('d-none');
    });

    // Manejador del formulario
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

    // Configuración común de Quill
    const quillConfig = {
      placeholder: 'Escribe el contenido del mensaje aquí...',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline', 'strike'],
          [{ 'header': 1 }, { 'header': 2 }],
          [{ 'color': [] }, { 'background': [] }],
          [{ 'align': [] }],
          [{ 'size': ['small', false, 'large', 'huge'] }],
          [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
          [{ 'font': [] }],
          [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
          [{ 'indent': '-1'}, { 'indent': '+1' }],
          ['link', 'image', 'video'],
          ['clean']
        ],
      },
      theme: 'snow'
    };

    const setupQuill = (selector, inputId, initialContent) => {
      const editor = new Quill(selector, quillConfig);
      editor.root.innerHTML = initialContent;
      
      editor.on('text-change', () => {
        let html = editor.root.innerHTML;
        if (editor.getText().trim().length === 0 && editor.root.querySelectorAll('img, video, iframe').length === 0) {
          html = '';
        }
        $(inputId).val(html);
      });
      return editor;
    };

    setupQuill('#editor_mensaje_1', '#mensaje_parte_1', `{!! old('mensaje_parte_1', $tipoPeticion->mensaje_parte_1) !!}`);
    setupQuill('#editor_mensaje_2', '#mensaje_parte_2', `{!! old('mensaje_parte_2', $tipoPeticion->mensaje_parte_2) !!}`);
  });
</script>
@endsection

@section('content')
<h4 class="fw-semibold text-primary mb-1">Editar tipo de petición: {{ $tipoPeticion->nombre }}</h4>
<p class="mb-4 text-black">Actualiza los parámetros del tipo de petición.</p>

@include('layouts.status-msn')

<form id="formulario" action="{{ route('tipo-peticiones.actualizar', $tipoPeticion->id) }}" method="POST" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <div class="row">
    <div class="col-12">
      <!-- Card: Información principal -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Información principal</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="nombre" class="form-label">Nombre *</label>
              <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $tipoPeticion->nombre) }}" placeholder="Ej: Petición de Salud" required>
              @error('nombre')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4 mb-3">
              <label for="icono" class="form-label">Icono (Clase CSS, ej. ti ti-heart)</label>
              <input type="text" name="icono" id="icono" class="form-control" value="{{ old('icono', $tipoPeticion->icono) }}" placeholder="Ej: ti ti-heart">
              @error('icono')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4 mb-3">
              <label for="orden" class="form-label">Orden de visualización *</label>
              <input type="number" name="orden" id="orden" class="form-control" value="{{ old('orden', $tipoPeticion->orden) }}" placeholder="Ej: 1" required>
              @error('orden')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            
            <div class="col-md-12 mb-3">
              <label for="banner_email" class="form-label">Banner para el Email</label>
              
              @if ($tipoPeticion->banner_email)
              <div id="info-banner-actual" class="mb-3">
                <div class="border rounded p-2 d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                    <img src="{{ $tipoPeticion->banner_email_url }}" alt="Banner actual" class="rounded me-3 border" style="width: 100px; height: 50px; object-fit: cover;">
                    <div class="d-flex flex-column">
                      <span style="font-size: 0.75rem;" class="text-muted">Banner actual</span>
                      <span class="text-truncate fw-semibold" style="font-size: 0.85rem;">{{ basename($tipoPeticion->banner_email) }}</span>
                    </div>
                  </div>
                  <button type="button" id="btn-reemplazar" class="btn btn-icon btn-label-danger btn-sm" title="Quitar y reemplazar">
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
              </div>
              @endif

              <div id="contenedor-input-banner" class="{{ $tipoPeticion->banner_email ? 'd-none' : '' }}">
                <input class="form-control" type="file" id="banner_email" accept="image/*">
                <div class="form-text">El banner se recortará a una proporción de 1200x600 px.</div>
                <input type="hidden" id="banner_email_recortado" name="banner_email_recortado" value="{{ old('banner_email_recortado') }}">
              </div>
              
              @error('banner_email')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Card: Mensajes personalizados -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Mensajes personalizados del Email</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-12 mb-3">
              <label class="form-label">Mensaje - Parte 1</label>
              <div id="editor_mensaje_1" style="min-height: 150px;"></div>
              <input type="hidden" id="mensaje_parte_1" name="mensaje_parte_1" value="{{ old('mensaje_parte_1', $tipoPeticion->mensaje_parte_1) }}">
              @error('mensaje_parte_1')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">Mensaje - Parte 2</label>
              <div id="editor_mensaje_2" style="min-height: 150px;"></div>
              <input type="hidden" id="mensaje_parte_2" name="mensaje_parte_2" value="{{ old('mensaje_parte_2', $tipoPeticion->mensaje_parte_2) }}">
              @error('mensaje_parte_2')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Card: Citas bíblicas relacionadas -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Citas bíblicas relacionadas</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-12 mb-4">
              <label class="form-label d-block">Seleccionar cita de la Biblia</label>
              @livewire('TiempoConDios.biblia', [
                  'name_id' => 'versiculos_peticion',
                  'despacharEvento' => true,
              ])
            </div>
            <div class="col-12">
              <label class="form-label">Citas seleccionadas</label>
              <div class="border rounded p-3 bg-light" style="min-height: 80px;">
                <div id="no-versiculos-msg" class="text-muted text-center py-2">
                  No has seleccionado citas bíblicas aún. Abre la Biblia arriba y subraya los versículos que desees agregar.
                </div>
                <ul id="lista-versiculos" class="list-group list-group-flush d-none">
                  <!-- Se listarán dinámicamente -->
                </ul>
              </div>
              <input type="hidden" id="json_versiculos" name="json_versiculos" value="{{ old('json_versiculos', $tipoPeticion->json_versiculos ?? '[]') }}">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex mb-1 mt-3">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2">Actualizar</button>
    </div>
  </div>
</form>

<div class="modal fade" id="modalRecorte" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header pb-4">
        <h5 class="modal-title">Recortar Banner</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
         <div class="img-container">
            <img src="" id="croppingImage" alt="Imagen para recortar">
        </div>
      </div>
      <div class="modal-footer pt-5">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-outline-primary crop-btn rounded-pill">Recortar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var croppingImage = document.getElementById('croppingImage');
    var cropBtn = document.querySelector('.crop-btn');
    var uploadBanner = document.getElementById('banner_email');
    var modalRecorteEl = document.getElementById('modalRecorte');
    var inputResultadoBanner = document.getElementById('banner_email_recortado');
    var cropper = null;

    if (uploadBanner) {
        uploadBanner.addEventListener('change', function(e) {
            if (e.target.files.length) {
                var file = e.target.files[0];
                var fileType = file.type;

                if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png' || fileType === 'image/webp') {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        croppingImage.src = e.target.result;
                        if(cropper) {
                            cropper.destroy();
                            cropper = null;
                        }
                        var modalRecorte = bootstrap.Modal.getOrCreateInstance(modalRecorteEl);
                        modalRecorte.show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    Swal.fire('Error', 'Formato de archivo no soportado', 'error');
                }
            }
        });
    }

    modalRecorteEl.addEventListener('shown.bs.modal', function () {
        cropper = new Cropper(croppingImage, {
            zoomable: false,
            viewMode: 1,
            aspectRatio: 1200 / 600,
            autoCropArea: 1,
            responsive: true,
            restore: false,
            checkCrossOrigin: false,
        });
    });

    modalRecorteEl.addEventListener('hidden.bs.modal', function () {
        if(cropper){
            cropper.destroy();
            cropper = null;
        }
        if(inputResultadoBanner.value === ""){
            uploadBanner.value = "";
        }
    });

    cropBtn.addEventListener('click', function() {
        if(!cropper) return;

        var canvas = cropper.getCroppedCanvas({
            width: 1200,
            height: 600,
        });

        var imgSrc = canvas.toDataURL('image/jpeg', 0.8);
        inputResultadoBanner.value = imgSrc;

        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Banner recortado',
            showConfirmButton: false,
            timer: 1500
        });

        var modalRecorte = bootstrap.Modal.getInstance(modalRecorteEl);
        modalRecorte.hide();
    });

    // --- LOGICA DE CITAS BIBLICAS ---
    var versiculosArray = [];

    try {
        var prevJson = document.getElementById('json_versiculos').value;
        versiculosArray = JSON.parse(prevJson || '[]');
        actualizarListaUI();
    } catch(e) {
        versiculosArray = [];
    }

    var seleccionTemporal = null;

    if (typeof Livewire !== 'undefined') {
        Livewire.on('bibliaSeleccionada', function(eventData) {
            var data = Array.isArray(eventData) ? eventData[0] : eventData;

            if (data && data.versiculos && data.versiculos.length > 0) {
                var textoCita = data.versiculos.map(function(v) { return v.texto; }).join(' ').trim();
                var tituloCita = data.cita_larga.split(' (')[0].trim();

                seleccionTemporal = {
                    cita: textoCita,
                    titulo: tituloCita
                };
            } else {
                seleccionTemporal = null;
            }
        });
    }

    // Escuchar el cierre del modal de la Biblia para consolidar e insertar la selección
    var modalBibliaEl = document.getElementById('modalBiblia_versiculos_peticion');
    if (modalBibliaEl) {
        modalBibliaEl.addEventListener('hidden.bs.modal', function () {
            if (seleccionTemporal) {
                var existe = versiculosArray.some(function(v) { return v.titulo === seleccionTemporal.titulo; });
                if (!existe) {
                    versiculosArray.push(seleccionTemporal);
                    actualizarListaUI();
                } else {
                    Swal.fire({
                        title: 'Cita ya agregada',
                        text: 'Esta cita bíblica ya se encuentra en la lista.',
                        icon: 'info',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
                seleccionTemporal = null;
            }
        });
    }

    window.eliminarCita = function(index) {
        versiculosArray.splice(index, 1);
        actualizarListaUI();
    };

    function actualizarListaUI() {
        var listaUl = document.getElementById('lista-versiculos');
        var noMsg = document.getElementById('no-versiculos-msg');
        var hiddenInput = document.getElementById('json_versiculos');

        hiddenInput.value = JSON.stringify(versiculosArray);

        if (versiculosArray.length > 0) {
            noMsg.classList.add('d-none');
            listaUl.classList.remove('d-none');

            listaUl.innerHTML = '';
            versiculosArray.forEach(function(item, index) {
                var li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-start p-3 border rounded mb-2 bg-white';
                li.innerHTML = `
                    <div class="ms-2 me-auto text-black text-start">
                        <div class="fw-bold text-primary">${item.titulo}</div>
                        <small style="font-style: italic;">"${item.cita}"</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger ms-2 rounded-circle mt-1" onclick="eliminarCita(${index})">
                        <i class="ti ti-trash"></i>
                    </button>
                `;
                listaUl.appendChild(li);
            });
        } else {
            noMsg.classList.remove('d-none');
            listaUl.classList.add('d-none');
            listaUl.innerHTML = '';
        }
    }
});
</script>
@endsection
