@php
$configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Editar tipo de pago')

@section('page-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'
])
<style>
  .preview-img {
    width: 100%;
    max-height: 150px;
    object-fit: contain;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
  }
</style>
@endsection

@section('vendor-script')
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
    $('.select2').select2();

    // Previsualización de imágenes
    $('.input-preview').on('change', function() {
      const input = this;
      const previewId = $(this).data('preview');
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          $(`#${previewId}`).attr('src', e.target.result).removeClass('d-none');
        }
        reader.readAsDataURL(input.files[0]);
      }
    });

    // Manejador del formulario
    $('#formulario').submit(function(){
      $('.btnGuardar').attr('disabled','disabled');

      Swal.fire({
        title: "Espera un momento",
        text: "Ya estamos guardando los cambios...",
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
<h4 class="fw-semibold text-primary mb-1">Editar tipo de pago</h4>
<p class="mb-4 text-black">Modifica los parámetros de configuración del tipo de pago: <strong>{{ $tipoPago->nombre }}</strong>.</p>

@include('layouts.status-msn')

<form id="formulario" action="{{ route('tipo-pagos.actualizarTipoPagos', $tipoPago->id) }}" method="POST" enctype="multipart/form-data">
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
              <label for="nombre" class="form-label">Nombre *</label>
              <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $tipoPago->nombre) }}" placeholder="Ej: PSE" required maxlength="30">
              @error('nombre')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label for="enlace" class="form-label">Enlace</label>
              <input type="text" name="enlace" id="enlace" class="form-control" value="{{ old('enlace', $tipoPago->enlace) }}" placeholder="https://..." maxlength="100">
              @error('enlace')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label for="cuenta_sap" class="form-label">Cuenta SAP</label>
              <input type="text" name="cuenta_sap" id="cuenta_sap" class="form-control" value="{{ old('cuenta_sap', $tipoPago->cuenta_sap) }}" placeholder="Código SAP" maxlength="30">
              @error('cuenta_sap')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label for="color" class="form-label">Color representativo</label>
              <input type="color" name="color" id="color" class="form-control form-control-color w-100" value="{{ old('color', $tipoPago->color ?? '#666CE8') }}">
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="imagen" class="form-label">Logo (Imagen)</label>
              <div class="mb-2">
                @if($tipoPago->logo_url)
                  <img id="preview_logo" src="{{ $tipoPago->logo_url }}" class="preview-img border" alt="Logo">
                @else
                  <img id="preview_logo" src="" class="preview-img border d-none" alt="Logo">
                @endif
              </div>
              <input class="form-control input-preview" type="file" name="imagen" id="imagen" accept="image/*" data-preview="preview_logo">
              @error('imagen')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="fondo" class="form-label">Fondo (Imagen)</label>
              <div class="mb-2">
                @if($tipoPago->fondo_url)
                  <img id="preview_fondo" src="{{ $tipoPago->fondo_url }}" class="preview-img border" alt="Fondo">
                @else
                  <img id="preview_fondo" src="" class="preview-img border d-none" alt="Fondo">
                @endif
              </div>
              <input class="form-control input-preview" type="file" name="fondo" id="fondo" accept="image/*" data-preview="preview_fondo">
              @error('fondo')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            
            <div class="col-12 col-md-4 mb-3">
              <label for="unica_moneda_id" class="form-label">Moneda Única *</label>
              <select name="unica_moneda_id" id="unica_moneda_id" class="form-select select2" required>
                <option value="">Seleccione una moneda...</option>
                @foreach ($monedas as $moneda)
                <option value="{{ $moneda->id }}" {{ old('unica_moneda_id', $tipoPago->unica_moneda_id) == $moneda->id ? 'selected' : '' }}>
                  {{ $moneda->nombre }} ({{ $moneda->nombre_corto }})
                </option>
                @endforeach
              </select>
              @error('unica_moneda_id')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-12 col-md-4 mb-3">
              <label for="porcentaje_tax1" class="form-label">% Tax 1</label>
              <input type="number" step="0.01" name="porcentaje_tax1" id="porcentaje_tax1" class="form-control" value="{{ old('porcentaje_tax1', $tipoPago->porcentaje_tax1) }}">
            </div>
            <div class="col-12 col-md-4 mb-3">
              <label for="porcentaje_tax2" class="form-label">% Tax 2</label>
              <input type="number" step="0.01" name="porcentaje_tax2" id="porcentaje_tax2" class="form-control" value="{{ old('porcentaje_tax2', $tipoPago->porcentaje_tax2) }}">
            </div>
            <div class="col-12 col-md-4 mb-3">
              <label for="transaccion_minima" class="form-label">Transacción Mínima</label>
              <input type="number" step="0.01" name="transaccion_minima" id="transaccion_minima" class="form-control" value="{{ old('transaccion_minima', $tipoPago->transaccion_minima) }}">
            </div>
            <div class="col-12 col-md-4 mb-3">
              <label for="transaccion_maxima" class="form-label">Transacción Máxima</label>
              <input type="number" step="0.01" name="transaccion_maxima" id="transaccion_maxima" class="form-control" value="{{ old('transaccion_maxima', $tipoPago->transaccion_maxima) }}">
            </div>
            <div class="col-12 col-md-4 mb-3">
              <label for="incremento_pdp" class="form-label">Incremento PDP</label>
              <input type="number" step="0.01" name="incremento_pdp" id="incremento_pdp" class="form-control" value="{{ old('incremento_pdp', $tipoPago->incremento_pdp) }}">
            </div>
          </div>
        </div>
      </div>

      <!-- Card: Configuración de Pasarela -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Configuración de pasarela</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <label for="client_id" class="form-label">Client ID</label>
              <input type="text" name="client_id" id="client_id" class="form-control" value="{{ old('client_id', $tipoPago->client_id) }}" maxlength="500">
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label for="key_id" class="form-label">Key ID</label>
              <input type="text" name="key_id" id="key_id" class="form-control" value="{{ old('key_id', $tipoPago->key_id) }}" maxlength="500">
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label for="bussines_id" class="form-label">Business ID</label>
              <input type="text" name="bussines_id" id="bussines_id" class="form-control" value="{{ old('bussines_id', $tipoPago->bussines_id) }}" maxlength="500">
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label for="account_id" class="form-label">Account ID</label>
              <input type="text" name="account_id" id="account_id" class="form-control" value="{{ old('account_id', $tipoPago->account_id) }}" maxlength="50">
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label for="url_retorno" class="form-label">URL retorno</label>
              <input type="text" name="url_retorno" id="url_retorno" class="form-control" value="{{ old('url_retorno', $tipoPago->url_retorno) }}" maxlength="500">
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label for="identity_token" class="form-label">Identity token</label>
              <input type="text" name="identity_token" id="identity_token" class="form-control" value="{{ old('identity_token', $tipoPago->identity_token) }}" maxlength="500">
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label for="key_reservada" class="form-label">Key reservada</label>
              <input type="text" name="key_reservada" id="key_reservada" class="form-control" value="{{ old('key_reservada', $tipoPago->key_reservada) }}" maxlength="50">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Columna Derecha -->
    <div class="col-12">
      <!-- Card: Opciones y Permisos -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Opciones y permisos</h5>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" {{ old('activo', $tipoPago->activo) ? 'checked' : '' }}>
                <label for="activo" class="form-check-label fw-medium text-black">Activo</label>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="habilitado_punto_pago" value="0">
                <input type="checkbox" class="form-check-input" id="habilitado_punto_pago" name="habilitado_punto_pago" value="1" {{ old('habilitado_punto_pago', $tipoPago->habilitado_punto_pago) ? 'checked' : '' }}>
                <label for="habilitado_punto_pago" class="form-check-label fw-medium text-black">Hab. Punto Pago</label>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="subir_archivo_pagos" value="0">
                <input type="checkbox" class="form-check-input" id="subir_archivo_pagos" name="subir_archivo_pagos" value="1" {{ old('subir_archivo_pagos', $tipoPago->subir_archivo_pagos) ? 'checked' : '' }}>
                <label for="subir_archivo_pagos" class="form-check-label fw-medium text-black">Subir Archivos</label>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="botones_valores_moneda" value="0">
                <input type="checkbox" class="form-check-input" id="botones_valores_moneda" name="botones_valores_moneda" value="1" {{ old('botones_valores_moneda', $tipoPago->botones_valores_moneda) ? 'checked' : '' }}>
                <label for="botones_valores_moneda" class="form-check-label fw-medium text-black">Botones Valores</label>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="habilitado_donacion" value="0">
                <input type="checkbox" class="form-check-input" id="habilitado_donacion" name="habilitado_donacion" value="1" {{ old('habilitado_donacion', $tipoPago->habilitado_donacion) ? 'checked' : '' }}>
                <label for="habilitado_donacion" class="form-check-label fw-medium text-black">Hab. Donación</label>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="tiene_limite_dinero_acumulado" value="0">
                <input type="checkbox" class="form-check-input" id="tiene_limite_dinero_acumulado" name="tiene_limite_dinero_acumulado" value="1" {{ old('tiene_limite_dinero_acumulado', $tipoPago->tiene_limite_dinero_acumulado) ? 'checked' : '' }}>
                <label for="tiene_limite_dinero_acumulado" class="form-check-label fw-medium text-black">Límite Acumulado</label>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="permite_personas_externas" value="0">
                <input type="checkbox" class="form-check-input" id="permite_personas_externas" name="permite_personas_externas" value="1" {{ old('permite_personas_externas', $tipoPago->permite_personas_externas) ? 'checked' : '' }}>
                <label for="permite_personas_externas" class="form-check-label fw-medium text-black">Permite Externos</label>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input type="hidden" name="codigo_datafono" value="0">
                <input type="checkbox" class="form-check-input" id="codigo_datafono" name="codigo_datafono" value="1" {{ old('codigo_datafono', $tipoPago->codigo_datafono) ? 'checked' : '' }}>
                <label for="codigo_datafono" class="form-check-label fw-medium text-black">Código Datáfono</label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detalles adicionales (ancho completo abajo) -->
    <div class="col-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Detalles adicionales</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="label_destinatario" class="form-label">Label destinatario</label>
              <textarea name="label_destinatario" id="label_destinatario" class="form-control" rows="2" placeholder="Ej: ¿A quién va dirigida esta donación?">{{ old('label_destinatario', $tipoPago->label_destinatario) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
              <label for="observaciones" class="form-label">Observaciones</label>
              <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Notas internas...">{{ old('observaciones', $tipoPago->observaciones) }}</textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex mb-1 mt-3">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2">Guardar</button>
    </div>
  </div>
</form>
@endsection