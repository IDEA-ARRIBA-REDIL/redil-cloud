@extends('layouts.layoutMaster')

@section('title', 'Crear Tipo de Pago')

{{-- 1. ESTILOS --}}
@section('page-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
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

  /* Estilo para el contenedor de la previsualización */
  .preview-container {
    min-height: 100px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f8f9fa;
    border: 1px dashed #d9dee3;
    border-radius: 0.375rem;
    margin-bottom: 10px;
    overflow: hidden;
  }

  .preview-container img {
    max-height: 100px;
    max-width: 100%;
    object-fit: contain;
  }
</style>
@endsection

{{-- 2. SCRIPTS DE VENDOR --}}
@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
@endsection

{{-- 3. LOGICA JS DEL CROPPER --}}
@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // --- Variables Globales del Cropper ---
    var croppingImage = document.getElementById('croppingImage');
    var cropBtn = document.querySelector('.crop-btn');
    var modalRecorteEl = document.getElementById('modalRecorte');
    var modalRecorte = new bootstrap.Modal(modalRecorteEl);
    var cropper = null;

    // Variables para saber qué input se está editando actualmente
    var activeFileInput = null;
    var activeHiddenInput = null;
    var activePreviewImg = null;

    // Detectar todos los inputs con la clase 'crop-input'
    var fileInputs = document.querySelectorAll('.crop-input');

    fileInputs.forEach(function(input) {
      input.addEventListener('change', function(e) {
        if (e.target.files.length) {
          var file = e.target.files[0];
          var fileType = file.type;

          if (fileType.startsWith('image/')) {
            // Guardamos referencia a los elementos actuales basados en los atributos data
            activeFileInput = input;
            activeHiddenInput = document.getElementById(input.dataset.hidden);
            activePreviewImg = document.getElementById(input.dataset.preview);

            var reader = new FileReader();
            reader.onload = function(e) {
              croppingImage.src = e.target.result;

              // Si ya había una instancia, la limpiamos
              if (cropper) {
                cropper.destroy();
                cropper = null;
              }

              // Abrir modal
              modalRecorte.show();
            };
            reader.readAsDataURL(file);
          } else {
            Swal.fire('Error', 'Formato de archivo no soportado', 'error');
            input.value = ''; // Limpiar input
          }
        }
      });
    });

    // Al mostrar el modal, iniciar Cropper
    modalRecorteEl.addEventListener('shown.bs.modal', function() {
      cropper = new Cropper(croppingImage, {
        zoomable: false,
        viewMode: 1,
        // aspectRatio: 16 / 9, // Puedes usar NaN para libre
        autoCropArea: 1,
        responsive: true,
        restore: false,
        checkCrossOrigin: false,
      });
    });

    // Al ocultar el modal, destruir Cropper y limpiar si se canceló
    modalRecorteEl.addEventListener('hidden.bs.modal', function() {
      if (cropper) {
        cropper.destroy();
        cropper = null;
      }
      // Si el input hidden está vacío (se canceló), limpiamos el input file
      if (activeHiddenInput && activeHiddenInput.value === "") {
        activeFileInput.value = "";
      }
    });

    // Botón "Recortar y Guardar" del modal
    cropBtn.addEventListener('click', function() {
      if (!cropper) return;

      // Obtener el canvas recortado
      var canvas = cropper.getCroppedCanvas({
        width: 800, // Reducir tamaño para optimizar
        fillColor: '#fff',
      });

      // Convertir a Base64
      var imgSrc = canvas.toDataURL('image/jpeg');

      // 1. Guardar en el input hidden correspondiente
      if (activeHiddenInput) {
        activeHiddenInput.value = imgSrc;
      }

      // 2. Actualizar la vista previa correspondiente
      if (activePreviewImg) {
        activePreviewImg.src = imgSrc;
        activePreviewImg.style.display = 'block'; // Asegurar que se vea
      }

      Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Imagen recortada correctamente',
        showConfirmButton: false,
        timer: 1500
      });

      // Cerrar modal
      modalRecorte.hide();
    });
  });
</script>
@endsection

@section('content')
<!-- PORTADA -->
<div class="col-md-12">
  <div class="card mb-4 rounded rounded-3">
    <img id="preview-foto" class="cropped-img card-img-top mb-2" src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png') }}" alt="Portada">
    <button type="button" style="background-color: rgba(255, 255, 255, 0.5);" class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2" data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;" class="ti ti-camera"></i></button>
    <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada" name="foto">

    <div class="row p-4 m-0 d-flex card-body">
      <h5 class="mb-1 fw-semibold text-black">Crear tipo de pago</h5>
      <p class="mb-4 text-black">Aquí podras modificar un tipo de pago, por favor llena los campos que son requeridos.</p>
    </div>
  </div>
</div>
<!-- PORTADA -->

<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-body">

        {{-- Formulario --}}
        {{-- CAMBIO IMPORTANTE: Apunta a la ruta de crear y usa POST --}}
        <form action="{{ route('tipo-pagos.crearTipoPagos') }}" method="POST" enctype="multipart/form-data">
          @csrf

          {{-- 1. CADENAS DE TEXTO E IMÁGENES --}}
          <h6 class="mt-2 text-primary text-black">Información General</h6>
          <div class="row g-3">

            {{-- Nombre --}}
            <div class="col-md-4">
              <label class="form-label">Nombre (obligatorio)</label>
              <input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required maxlength="30">
            </div>

            {{-- Enlace --}}
            <div class="col-md-4">
              <label class="form-label">Enlace </label>
              <input type="text" class="form-control" name="enlace" value="{{ old('enlace') }}" maxlength="100">
            </div>

            {{-- Cuenta SAP --}}
            <div class="col-md-4">
              <label class="form-label">Cuenta SAP </label>
              <input type="text" class="form-control" name="cuenta_sap" value="{{ old('cuenta_sap') }}"maxlength="30">
            </div>

            {{-- Campos Opcionales Strings --}}
            <div class="col-md-4">
              <label class="form-label">Client ID</label>
              <input type="text" class="form-control" name="client_id" value="{{ old('client_id') }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">Key ID</label>
              <input type="text" class="form-control" name="key_id" value="{{ old('key_id') }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">Business ID</label>
              <input type="text" class="form-control" name="bussines_id" value="{{ old('bussines_id') }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">URL Retorno</label>
              <input type="text" class="form-control" name="url_retorno" value="{{ old('url_retorno') }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">Identity Token</label>
              <input type="text" class="form-control" name="identity_token" value="{{ old('identity_token') }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">Key Reservada</label>
              <input type="text" class="form-control" name="key_reservada" value="{{ old('key_reservada') }}" maxlength="50">
            </div>
            <div class="col-md-4">
              <label class="form-label">Account ID</label>
              <input type="text" class="form-control" name="account_id" value="{{ old('account_id') }}" maxlength="50">
            </div>

            {{-- Color --}}
            <div class="col-md-4">
              <label class="form-label">Color</label>
              <input type="color" class="form-control form-control-color" name="color" value="{{ old('color', '#ffffff') }}">
            </div>

            {{-- Textareas --}}
            <div class="col-md-6">
              <label class="form-label">Label Destinatario</label>
              <textarea class="form-control" name="label_destinatario" rows="2">{{ old('label_destinatario') }}</textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Observaciones </label>
              <textarea class="form-control" name="observaciones" rows="2" >{{ old('observaciones') }}</textarea>
            </div>

            {{-- CAMPO: IMAGEN (LOGO) - CON CROPPER --}}
            <div class="col-md-6">
              <label class="form-label">Imagen (obligatorio)</label>
              {{-- Previsualización vacía --}}
              <div class="preview-container">
                <img id="preview_imagen_logo" src="" alt="Previsualización" style="display:none;">
              </div>
              {{-- Input file requerido --}}
              <input type="file" class="form-control crop-input"
                id="input_imagen_logo"
                accept="image/*"
               
                data-hidden="imagen_recortada"
                data-preview="preview_imagen_logo">
              <input type="hidden" name="imagen_recortada" id="imagen_recortada" >
            </div>

            {{-- CAMPO: FONDO (IMAGEN) - CON CROPPER --}}
            <div class="col-md-6">
              <label class="form-label">Fondo (Imagen)</label>
              {{-- Previsualización vacía --}}
              <div class="preview-container">
                <img id="preview_imagen_fondo" src="" alt="Previsualización" style="display:none;">
              </div>
              <input type="file" class="form-control crop-input"
                id="input_imagen_fondo"
                accept="image/*"
                data-hidden="fondo_recortado"
                data-preview="preview_imagen_fondo">
              <input type="hidden" name="fondo_recortado" id="fondo_recortado">
            </div>

          </div>

          <hr class="my-4">

          {{-- 2. CONFIGURACIÓN NUMÉRICA --}}
          <h6 class="text-primary text-black">Configuración Numérica</h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" for="unica_moneda_id">Moneda Única (obligatorio)</label>

              <select class="form-select @error('unica_moneda_id') is-invalid @enderror"
                name="unica_moneda_id"
                id="unica_moneda_id" required>

                <option value="">Seleccione una moneda...</option>

                @foreach ($monedas as $moneda)
                <option value="{{ $moneda->id }}"
                  {{ old('unica_moneda_id') == $moneda->id ? 'selected' : '' }}>
                  {{ $moneda->nombre }} ({{ $moneda->nombre_corto }})
                </option>
                @endforeach

              </select>

              @error('unica_moneda_id')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">% Tax 1</label>
              <input type="number" class="form-control" name="porcentaje_tax1" value="{{ old('porcentaje_tax1') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">% Tax 2</label>
              <input type="number" class="form-control" name="porcentaje_tax2" value="{{ old('porcentaje_tax2') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Transacción Mínima</label>
              <input type="number" class="form-control" name="transaccion_minima" value="{{ old('transaccion_minima') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Transacción Máxima</label>
              <input type="number" class="form-control" name="transaccion_maxima" value="{{ old('transaccion_maxima') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Incremento PDP</label>
              <input type="number" class="form-control" name="incremento_pdp" value="{{ old('incremento_pdp') }}">
            </div>
          </div>

          <hr class="my-4">

          {{-- 3. OPCIONES BOOLEANAS --}}
          <h6 class="text-primary text-black">Opciones y Permisos</h6>
          <div class="row g-3">
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="activo" value="0">
                {{-- Por defecto CHECKED para crear nuevo (según migración default true) --}}
                <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" {{ old('activo', 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="activo">Activo</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="habilitado_punto_pago" value="0">
                <input class="form-check-input" type="checkbox" name="habilitado_punto_pago" value="1" id="hab_pp" {{ old('habilitado_punto_pago') ? 'checked' : '' }}>
                <label class="form-check-label" for="hab_pp">Hab. Punto Pago</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="subir_archivo_pagos" value="0">
                <input class="form-check-input" type="checkbox" name="subir_archivo_pagos" value="1" id="subir_arch" {{ old('subir_archivo_pagos') ? 'checked' : '' }}>
                <label class="form-check-label" for="subir_arch">Subir Archivos</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="botones_valores_moneda" value="0">
                <input class="form-check-input" type="checkbox" name="botones_valores_moneda" value="1" id="btn_mon" {{ old('botones_valores_moneda') ? 'checked' : '' }}>
                <label class="form-check-label" for="btn_mon">Botones Valores</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="habilitado_donacion" value="0">
                <input class="form-check-input" type="checkbox" name="habilitado_donacion" value="1" id="hab_don" {{ old('habilitado_donacion') ? 'checked' : '' }}>
                <label class="form-check-label" for="hab_don">Hab. Donación</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="tiene_limite_dinero_acumulado" value="0">
                <input class="form-check-input" type="checkbox" name="tiene_limite_dinero_acumulado" value="1" id="lim_din" {{ old('tiene_limite_dinero_acumulado') ? 'checked' : '' }}>
                <label class="form-check-label" for="lim_din">Límite Acumulado</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="punto_de_pago" value="0">
                <input class="form-check-input" type="checkbox" name="punto_de_pago" value="1" id="is_pdp" {{ old('punto_de_pago') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_pdp">Es Punto de Pago</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="permite_personas_externas" value="0">
                <input class="form-check-input" type="checkbox" name="permite_personas_externas" value="1" id="ext_pers" {{ old('permite_personas_externas') ? 'checked' : '' }}>
                <label class="form-check-label" for="ext_pers">Permite Externos</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="codigo_datafono" value="0">
                <input class="form-check-input" type="checkbox" name="codigo_datafono" value="1" id="cod_data" {{ old('codigo_datafono') ? 'checked' : '' }}>
                <label class="form-check-label" for="cod_data">Código Datáfono</label>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('tipo-pagos.listarTipoPagos') }}" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- MODAL DE RECORTE --}}
<div class="modal fade" id="modalRecorte" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Recortar Imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="img-container">
          <img src="" id="croppingImage" alt="Imagen para recortar">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary crop-btn">Recortar y Guardar</button>
      </div>
    </div>
  </div>
</div>

@endsection