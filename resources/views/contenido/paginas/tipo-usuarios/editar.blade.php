@php
$configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Editar Tipo de usuario')

@section('page-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'
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
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'
])
@endsection

@section('page-script')
<script type="module">
  $(function() {
    // Inicializar Select2
    $('#id_rol_dependiente').select2({
      placeholder: 'Seleccione un rol dependiente',
      allowClear: true
    });

    // Lógica para reemplazar la imagen
    $('#btn-reemplazar').on('click', function() {
      $('#info-imagen-actual').addClass('d-none');
      $('#contenedor-input-imagen').removeClass('d-none');
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
  });
</script>
@endsection

@section('content')
<h4 class="fw-semibold text-primary mb-1">Editar Tipo de Usuario: {{ $tipoUsuario->nombre }}</h4>
<p class="mb-4 text-black">Actualiza los parámetros de configuración para este tipo de usuario.</p>

@include('layouts.status-msn')

<form id="formulario" action="{{ route('tipo-usuario.actualizar', $tipoUsuario) }}" method="POST" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <div class="row">
    <!-- Columna Izquierda -->
    <div class="col-12">
      <!-- Card: Información básica -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Información básica</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nombre" class="form-label">Nombre</label>
              <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $tipoUsuario->nombre) }}" placeholder="Ej: Líder" required>
              @error('nombre')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label for="nombre_plural" class="form-label">Nombre plural</label>
              <input type="text" name="nombre_plural" id="nombre_plural" class="form-control" value="{{ old('nombre_plural', $tipoUsuario->nombre_plural) }}" placeholder="Ej: Líderes">
            </div>
            <div class="col-12 mb-3">
              <label for="descripcion" class="form-label">Descripción</label>
              <textarea name="descripcion" id="descripcion" class="form-control" rows="2" placeholder="Breve descripción del rol...">{{ old('descripcion', $tipoUsuario->descripcion) }}</textarea>
            </div>
            <div class="col-md-3 mb-3">
              <label for="color" class="form-label">Color representativo</label>
              <input type="color" name="color" id="color" class="form-control form-control-color w-100" value="{{ old('color', $tipoUsuario->color ?? '#666CE8') }}">
            </div>
            <div class="col-md-4 mb-3">
              <label for="icono" class="form-label">Clase del ícono</label>
              <input type="text" name="icono" id="icono" class="form-control" value="{{ old('icono', $tipoUsuario->icono) }}" placeholder="ti ti-user">
            </div>
             <div class="col-md-5 mb-3">
               <label for="imagen" class="form-label">Imagen (100x100 PNG)</label>
               
               @if ($tipoUsuario->imagen)
               <div id="info-imagen-actual">
                 <div class="border rounded p-2 d-flex justify-content-between align-items-center mb-3">
                   <div class="d-flex align-items-center">
                     <img src="{{ $tipoUsuario->imagen_url }}" alt="Imagen actual" class="rounded me-3 border" style="width: 50px; height: 50px; object-fit: cover;">
                     <div class="d-flex flex-column">
                       <span style="font-size: 0.75rem;" class="text-muted">Imagen actual</span>
                       <span class="text-truncate fw-semibold" style="font-size: 0.85rem;">{{ $tipoUsuario->imagen }}</span>
                     </div>
                   </div>
                   <button type="button" id="btn-reemplazar" class="btn btn-icon btn-label-danger btn-sm" title="Quitar y reemplazar">
                     <i class="ti ti-trash"></i>
                   </button>
                 </div>
               </div>
               @endif

               <div id="contenedor-input-imagen" class="{{ $tipoUsuario->imagen ? 'd-none' : '' }}">
                 <input class="form-control" type="file" id="imagen" accept="image/*">
                 <div class="form-text">La imagen se recortará a 100x100 px.</div>
                 <input type="hidden" id="imagen_recortada" name="imagen_recortada" value="{{ old('imagen_recortada') }}">
               </div>
               
               @error('imagen')
               <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
               @enderror
             </div>
          </div>
        </div>
      </div>

      <!-- Card: Parámetros de configuración -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Parámetros de configuración</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="orden" class="form-label">Orden de jerarquía</label>
              <input type="number" name="orden" id="orden" class="form-control" value="{{ old('orden', $tipoUsuario->orden) }}">
            </div>
            <div class="col-md-4 mb-3">
              <label for="puntaje" class="form-label">Puntaje acumulable</label>
              <input type="number" name="puntaje" id="puntaje" class="form-control" value="{{ old('puntaje', $tipoUsuario->puntaje) }}">
            </div>
            <div class="col-md-4 mb-3">
              <label for="id_rol_dependiente" class="form-label">Rol dependiente</label>
              <select name="id_rol_dependiente" id="id_rol_dependiente" class="form-select select2">
                <option value="">Sin dependencia</option>
                @foreach ($tiposUsuarios as $rol)
                <option value="{{ $rol->id }}" {{ old('id_rol_dependiente', $tipoUsuario->id_rol_dependiente) == $rol->id ? 'selected' : '' }}>
                  {{ $rol->nombre }}
                </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Columna Derecha -->
    <div class="col-12">
      <!-- Card: Atributos y visibilidad -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Atributos y visibilidad</h5>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="tipo_pastor" name="tipo_pastor" value="1" {{ old('tipo_pastor', $tipoUsuario->tipo_pastor) ? 'checked' : '' }}>
                <label for="tipo_pastor" class="form-check-label fw-medium text-black">Es tipo pastor</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="tipo_pastor_principal" name="tipo_pastor_principal" value="1" {{ old('tipo_pastor_principal', $tipoUsuario->tipo_pastor_principal) ? 'checked' : '' }}>
                <label for="tipo_pastor_principal" class="form-check-label fw-medium text-black">Es pastor principal</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="visible" name="visible" value="1" {{ old('visible', $tipoUsuario->visible) ? 'checked' : '' }}>
                <label for="visible" class="form-check-label fw-medium text-black">Mostrar en búsquedas</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card: Seguimiento y consolidación -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Seguimiento y consolidación</h5>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="seguimiento_actividad_grupo" name="seguimiento_actividad_grupo" value="1" {{ old('seguimiento_actividad_grupo', $tipoUsuario->seguimiento_actividad_grupo) ? 'checked' : '' }}>
                <label for="seguimiento_actividad_grupo" class="form-check-label fw-medium text-black">Seguimiento de grupo</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="seguimiento_actividad_reunion" name="seguimiento_actividad_reunion" value="1" {{ old('seguimiento_actividad_reunion', $tipoUsuario->seguimiento_actividad_reunion) ? 'checked' : '' }}>
                <label for="seguimiento_actividad_reunion" class="form-check-label fw-medium text-black">Seguimiento de reunión</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="habilitado_para_consolidacion" name="habilitado_para_consolidacion" value="1" {{ old('habilitado_para_consolidacion', $tipoUsuario->habilitado_para_consolidacion) ? 'checked' : '' }}>
                <label for="habilitado_para_consolidacion" class="form-check-label fw-medium text-black">Listar en consolidación</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card: Inactividad y automatización -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Inactividad y automatización</h5>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-4 mt-3  ">
              <label for="dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion" class="form-label">Días de inactividad para baja</label>
              <input type="number" name="dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion" id="dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion" class="form-control" value="{{ old('dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion', $tipoUsuario->dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion) }}">
              <small class="text-muted">0 para deshabilitar.</small>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="seguimiento_para_dar_de_baja_automaticamente" name="seguimiento_para_dar_de_baja_automaticamente" value="1" {{ old('seguimiento_para_dar_de_baja_automaticamente', $tipoUsuario->seguimiento_para_dar_de_baja_automaticamente) ? 'checked' : '' }}>
                <label for="seguimiento_para_dar_de_baja_automaticamente" class="form-check-label fw-medium text-black">Habilitar baja automática</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="default" name="default" value="1" {{ old('default', $tipoUsuario->default) ? 'checked' : '' }}>
                <label for="default" class="form-check-label fw-medium text-black">Rol predeterminado</label>
              </div>
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
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header pb-4">
        <h5 class="modal-title">Recortar imagen</h5>
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
    var uploadImagen = document.getElementById('imagen');
    var modalRecorteEl = document.getElementById('modalRecorte');
    var inputResultadoImagen = document.getElementById('imagen_recortada');
    var cropper = null;

    if (uploadImagen) {
        uploadImagen.addEventListener('change', function(e) {
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
            aspectRatio: 1,
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
        if(inputResultadoImagen.value === ""){
            uploadImagen.value = "";
        }
    });

    cropBtn.addEventListener('click', function() {
        if(!cropper) return;

        var canvas = cropper.getCroppedCanvas({
            width: 100,
            height: 100,
        });

        var imgSrc = canvas.toDataURL('image/png');
        inputResultadoImagen.value = imgSrc;

        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Imagen recortada',
            showConfirmButton: false,
            timer: 1500
        });

        var modalRecorte = bootstrap.Modal.getInstance(modalRecorteEl);
        modalRecorte.hide();
    });
});
</script>
@endsection
