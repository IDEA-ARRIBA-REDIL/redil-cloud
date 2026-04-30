@extends('layouts.layoutMaster')

@section('title', 'Crear Tipo de Pago')

{{-- 1. ESTILOS --}}
@section('page-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
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
              @error('enlace')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            {{-- Cuenta SAP --}}
            <div class="col-md-4">
              <label class="form-label">Cuenta SAP (obligatorio)</label>
              <input type="text" class="form-control" name="cuenta_sap" value="{{ old('cuenta_sap') }}" maxlength="30">
              @error('cuenta_sap')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
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
              @error('label_destinatario')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Observaciones </label>
              <textarea class="form-control" name="observaciones" rows="2" >{{ old('observaciones') }}</textarea>
              @error('observaciones')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            {{-- CAMPO: IMAGEN (LOGO) --}}
            <div class="col-md-6 mb-3">
              <label for="imagen" class="form-label">Imagen (obligatorio)</label>
              <div id="contenedor-input-imagen">
                <div class="input-group">
                  <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*">
                </div>
                @error('imagen')
                <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
                @enderror
              </div>
            </div>

            {{-- CAMPO: FONDO (IMAGEN) --}}
            <div class="col-md-6 mb-3">
              <label for="fondo" class="form-label">Fondo (Imagen)</label>
              <div id="contenedor-input-fondo">
                <div class="input-group">
                  <input type="file" id="fondo" name="fondo" class="form-control" accept="image/*">
                </div>
                @error('fondo')
                <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
                @enderror
              </div>
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
@endsection