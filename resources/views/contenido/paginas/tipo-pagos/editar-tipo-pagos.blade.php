@extends('layouts.layoutMaster')

@section('title', 'Editar Tipo de Pago')

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
      <h5 class="mb-1 fw-semibold text-black">Editar tipo de pago</h5>
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
        <form action="{{ route('tipo-pagos.actualizarTipoPagos', $tipoPago->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <h5 class="text-black fw-semibold">Información general</h5>
          <div class="row g-3">
            {{-- Nombre --}}
            <div class="col-md-4">
              <label class="form-label">Nombre (obligatorio)</label>
              <input type="text"
                class="form-control @error('nombre') is-invalid @enderror"
                name="nombre"
                value="{{ old('nombre', $tipoPago->nombre) }}"
                required maxlength="30">
              @error('nombre')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Enlace --}}
            <div class="col-md-4">
              <label class="form-label">Enlace</label>
              <input type="text"
                class="form-control @error('enlace') is-invalid @enderror"
                name="enlace"
                value="{{ old('enlace', $tipoPago->enlace) }}"
                 maxlength="100">
              @error('enlace')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Cuenta SAP --}}
            <div class="col-md-4">
              <label class="form-label">Cuenta SAP </label>
              <input type="text"
                class="form-control @error('cuenta_sap') is-invalid @enderror"
                name="cuenta_sap"
                value="{{ old('cuenta_sap', $tipoPago->cuenta_sap) }}"
                 maxlength="30">
              @error('cuenta_sap')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Campos Opcionales Strings --}}
            <div class="col-md-4">
              <label class="form-label">Client ID</label>
              <input type="text" class="form-control" name="client_id" value="{{ $tipoPago->client_id }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">Key ID</label>
              <input type="text" class="form-control" name="key_id" value="{{ $tipoPago->key_id }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">Business ID</label>
              <input type="text" class="form-control" name="bussines_id" value="{{ $tipoPago->bussines_id }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">URL Retorno</label>
              <input type="text" class="form-control" name="url_retorno" value="{{ $tipoPago->url_retorno }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">Identity Token</label>
              <input type="text" class="form-control" name="identity_token" value="{{ $tipoPago->identity_token }}" maxlength="500">
            </div>
            <div class="col-md-4">
              <label class="form-label">Key Reservada</label>
              <input type="text" class="form-control" name="key_reservada" value="{{ $tipoPago->key_reservada }}" maxlength="50">
            </div>
            <div class="col-md-4">
              <label class="form-label">Account ID</label>
              <input type="text" class="form-control" name="account_id" value="{{ $tipoPago->account_id }}" maxlength="50">
            </div>
            {{-- ======================================================= --}}
            {{-- CAMPO: IMAGEN (LOGO) - CON CROPPER Y PRECARGA CORRECTA --}}
            {{-- ======================================================= --}}
            <div class="col-md-4">
              <label class="form-label">Imagen </label>

              {{-- Previsualización con lógica inteligente --}}
              <div class="preview-container">
                @php
                // 1. URL por defecto (Base de datos)
                $rutaLogo = '';
                $mostrarLogo = 'none';

                if ($tipoPago->imagen) {
                // Agregamos ?v=time() para evitar que el navegador use una imagen vieja en caché
                $rutaLogo = asset('storage/logos/' . $tipoPago->imagen) . '?v=' . time();
                $mostrarLogo = 'block';
                }

                // 2. Si hubo un error de validación y hay una imagen recortada en memoria, tiene prioridad
                if (old('imagen_recortada')) {
                $rutaLogo = old('imagen_recortada'); // Es una cadena Base64
                $mostrarLogo = 'block';
                }
                @endphp

                <img id="preview_imagen_logo" src="{{ $rutaLogo }}" alt="Logo" style="display: {{ $mostrarLogo }};">
              </div>

              {{-- Input File --}}
              <input type="file" class="form-control crop-input"
                id="input_imagen_logo"
                accept="image/*"
                data-hidden="imagen_recortada"
                data-preview="preview_imagen_logo">

              {{-- Input Hidden: Importante agregar value="{{ old(...) }}" para no perder la imagen si falla validación --}}
              <input type="hidden" name="imagen_recortada" id="imagen_recortada" value="{{ old('imagen_recortada') }}">
            </div>

            {{-- ======================================================= --}}
            {{-- CAMPO: FONDO (IMAGEN) - CON CROPPER Y PRECARGA CORRECTA --}}
            {{-- ======================================================= --}}
            <div class="col-md-4">
              <label class="form-label">Fondo (Imagen)</label>

              {{-- Previsualización con lógica inteligente --}}
              <div class="preview-container">
                @php
                $rutaFondo = '';
                $mostrarFondo = 'none';

                if ($tipoPago->fondo) {
                $rutaFondo = asset('storage/fondos/' . $tipoPago->fondo) . '?v=' . time();
                $mostrarFondo = 'block';
                }

                if (old('fondo_recortado')) {
                $rutaFondo = old('fondo_recortado');
                $mostrarFondo = 'block';
                }
                @endphp

                <img id="preview_imagen_fondo" src="{{ $rutaFondo }}" alt="Fondo" style="display: {{ $mostrarFondo }};">
              </div>

              {{-- Input File --}}
              <input type="file" class="form-control crop-input"
                id="input_imagen_fondo"
                accept="image/*"
                data-hidden="fondo_recortado"
                data-preview="preview_imagen_fondo">

              {{-- Input Hidden --}}
              <input type="hidden" name="fondo_recortado" id="fondo_recortado" value="{{ old('fondo_recortado') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Color</label>
              <input type="color" class="form-control form-control-color" name="color" value="{{ $tipoPago->color ?? '#ffffff' }}">
            </div>
          </div>

          <hr class="my-4">

          {{-- Configuración Numérica --}}
          <h5 class="text-black fw-semibold">Configuración numérica</h5>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" for="unica_moneda_id">Moneda Única (obligatorio)</label>

              <select class="form-select @error('unica_moneda_id') is-invalid @enderror"
                name="unica_moneda_id"
                id="unica_moneda_id">

                <option value="">Seleccione una moneda...</option>

                @foreach ($monedas as $moneda)
                {{-- CORRECCIÓN AQUÍ: Agregamos el segundo parámetro a old() --}}
                <option value="{{ $moneda->id }}"
                  {{ (old('unica_moneda_id', $tipoPago->unica_moneda_id) == $moneda->id) ? 'selected' : '' }}>
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
              <input type="number" class="form-control" name="porcentaje_tax1" value="{{ $tipoPago->porcentaje_tax1 }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">% Tax 2</label>
              <input type="number" class="form-control" name="porcentaje_tax2" value="{{ $tipoPago->porcentaje_tax2 }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Transacción Mínima</label>
              <input type="number" class="form-control" name="transaccion_minima" value="{{ $tipoPago->transaccion_minima }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Transacción Máxima</label>
              <input type="number" class="form-control" name="transaccion_maxima" value="{{ $tipoPago->transaccion_maxima }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">% Incremento PDP</label>
              <input type="number" class="form-control" name="incremento_pdp" value="{{ $tipoPago->incremento_pdp }}">
            </div>
          </div>

          <hr class="my-4">

          {{-- Opciones Booleanas --}}
          <h5 class="text-black fw-semibold">Opciones y permisos</h5>
          <div class="row g-3">
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="activo" value="0">
                <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" {{ $tipoPago->activo ? 'checked' : '' }}>
                <label class="form-check-label" for="activo">Activo</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="habilitado_punto_pago" value="0">
                <input class="form-check-input" type="checkbox" name="habilitado_punto_pago" value="1" id="hab_pp" {{ $tipoPago->habilitado_punto_pago ? 'checked' : '' }}>
                <label class="form-check-label" for="hab_pp">Hab. punto pago</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="subir_archivo_pagos" value="0">
                <input class="form-check-input" type="checkbox" name="subir_archivo_pagos" value="1" id="subir_arch" {{ $tipoPago->subir_archivo_pagos ? 'checked' : '' }}>
                <label class="form-check-label" for="subir_arch">Subir archivos</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="botones_valores_moneda" value="0">
                <input class="form-check-input" type="checkbox" name="botones_valores_moneda" value="1" id="btn_mon" {{ $tipoPago->botones_valores_moneda ? 'checked' : '' }}>
                <label class="form-check-label" for="btn_mon">Botones valores</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="habilitado_donacion" value="0">
                <input class="form-check-input" type="checkbox" name="habilitado_donacion" value="1" id="hab_don" {{ $tipoPago->habilitado_donacion ? 'checked' : '' }}>
                <label class="form-check-label" for="hab_don">Hab. donación</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="tiene_limite_dinero_acumulado" value="0">
                <input class="form-check-input" type="checkbox" name="tiene_limite_dinero_acumulado" value="1" id="lim_din" {{ $tipoPago->tiene_limite_dinero_acumulado ? 'checked' : '' }}>
                <label class="form-check-label" for="lim_din">Límite acumulado</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="punto_de_pago" value="0">
                <input class="form-check-input" type="checkbox" name="punto_de_pago" value="1" id="is_pdp" {{ $tipoPago->punto_de_pago ? 'checked' : '' }}>
                <label class="form-check-label" for="is_pdp">Es punto de pago</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="permite_personas_externas" value="0">
                <input class="form-check-input" type="checkbox" name="permite_personas_externas" value="1" id="ext_pers" {{ $tipoPago->permite_personas_externas ? 'checked' : '' }}>
                <label class="form-check-label" for="ext_pers">Permite externos</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="codigo_datafono" value="0">
                <input class="form-check-input" type="checkbox" name="codigo_datafono" value="1" id="cod_data" {{ $tipoPago->codigo_datafono ? 'checked' : '' }}>
                <label class="form-check-label" for="cod_data">Código datáfono</label>
              </div>
            </div>
          </div>

          <hr class="my-4">

          {{-- Detalles Adicionales --}}
          <h5 class="text-black fw-semibold">Detalles adicionales</h5>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Label destinatario</label>
              <textarea class="form-control" name="label_destinatario" rows="2">{{ $tipoPago->label_destinatario }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Observaciones </label>
              <textarea class="form-control" name="observaciones" rows="4" >{{ $tipoPago->observaciones }}</textarea>
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary">Actualizar</button>
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