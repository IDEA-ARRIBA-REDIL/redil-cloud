@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')
@vite(['resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])

@endsection

@section('vendor-script')
@vite(['resources/js/app.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])

@endsection
@section('page-script')
<script type="module">
  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d"
  });

  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });
  });

  document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', () => {
      if (input.value < 0) input.value = 0;
    });
  });

  $(document).ready(function() {
    $('#tipoIdentificacion').select2({
      dropdownParent: $('#modalProveedor')
    });
  });
</script>
<script>
  function mostrarModalProveedores() {
    // document.getElementById('ingresoIdInput').value = ingresoId;
    // document.getElementById('justificacion').value = '';
    let modal = new bootstrap.Modal(document.getElementById('modalProveedor'));
    modal.show();
  }

  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalDocumentos');

    modal.addEventListener('show.bs.modal', function() {
      // Limpiar campos al abrir el modal
      modal.querySelectorAll('input').forEach(input => input.value = '');
    });
  });
</script>

<style>
</style>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Crear egreso</h4>
<p class="mb-4 text-black">Registra los egresos financieros.</p>

@include('layouts.status-msn')

<form role="form" method="POST" id="formulario" class="forms-sample" action="{{ route('finanzas.crearEgreso') }}"
  enctype="multipart/form-data">
  @csrf

  <div class="d-flex justify-content-end">
    <button type="button" onclick="mostrarModalProveedores()" class="btn btn-outline-primary waves-effect">
      <i class="ti ti-plus me-2"></i>
      Proveedor
    </button>
    <button type="button" class="btn btn-outline-secondary waves-effect ms-2" data-bs-toggle="modal"
      data-bs-target="#modalDocumentos">
      <i class="ti ti-file-plus me-2"></i>
       Documento equivalente
    </button>
  </div>

  <div class="card mb-4 mt-5">
    <h5 class="card-header text-black fw-semibold">
      Información principal
    </h5>

    <div class="card-body">
      <div class="row">

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Fecha</label>
          <input type="text" value="{{ old('fecha') }}" id="fecha" name="fecha" placeholder="Fecha del egreso"
            class="fecha form-control fecha-picker">
          @if ($errors->has('fecha'))
          <div class="text-danger form-label">{{ $errors->first('fecha') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="defaultFormControlInput" class="form-label">Valor</label>
          <input type="number" id="valor" name="valor" value="{{ old('valor') }}" class="form-control"
            id="defaultFormControlInput" placeholder="Valor" aria-describedby="defaultFormControlHelp" />
          @if ($errors->has('valor'))
          <div class="text-danger form-label">{{ $errors->first('valor') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Monedas</label>
          <select id="continente" name="moneda" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione una moneda" data-allow-clear="true">
            <option value="">Seleccione una moneda</option>
            @foreach ($monedas as $moneda)
            <option value="{{ $moneda->id }}" {{ old('moneda') == $moneda->id ? 'selected' : '' }}>{{ $moneda->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('moneda'))
          <div class="text-danger form-label">{{ $errors->first('moneda') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Proveedor</label>
          <select id="proveedor" name="proveedor" class="grupoSelect select2 selectorGenero form-select"
            data-placeholder="Seleccione un proveedor" data-allow-clear="true">
            <option value="">Seleccione un proveedor</option>
            @foreach ($proveedores as $proveedor)
            <option value="{{ $proveedor->id }}" {{ old('proveedor') == $proveedor->id ? 'selected' : '' }}>{{ $proveedor->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('proveedor'))
          <div class="text-danger form-label">{{ $errors->first('proveedor') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Tipo de egreso</label>
          <select id="tipoEgreso" name="tipoEgreso" class="grupoSelect select2 selectorGenero form-select"
            data-placeholder="Seleccione un tipo de egreso" data-allow-clear="true">
            <option value="">Seleccione un egreso</option>
            @foreach ($tipoEgresos as $tipoEgreso)
            <option value="{{ $tipoEgreso->id }}" {{ old('tipoEgreso') == $tipoEgreso->id ? 'selected' : '' }} >{{ $tipoEgreso->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('tipoEgreso'))
          <div class="text-danger form-label">{{ $errors->first('tipoEgreso') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Sedes</label>
          <select id="sede" name="sede" class="grupoSelect select2 selectorGenero form-select"
            data-placeholder="Seleccione una sede" data-allow-clear="true">
            <option value="">Seleccione una sede</option>
            @foreach ($sedes as $sede)
            <option value="{{ $sede->id }}" {{ old('sede') == $sede->id ? 'selected' : '' }}>{{ $sede->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('sede'))
          <div class="text-danger form-label">{{ $errors->first('sede') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-12 mb-6">
          <label class="form-label" for="payment-note">Descrición</label>
          <textarea class="form-control" name="descripcion" id="descripcion" rows="2">{{ old('descripcion') }}</textarea>
          @if ($errors->has('descripcion'))
          <div class="text-danger form-label">{{ $errors->first('descripcion') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Documentos equivalentes</label>
          <select id="documento" name="documento" class="grupoSelect select2 selectorGenero form-select"
            data-placeholder="Seleccione un documento" data-allow-clear="true">
            <option value="">Seleccione un documento</option>
            @foreach ($documentos as $documento)
            <option value="{{ $documento->id }}" {{ old('documento') == $documento->id ? 'selected' : '' }} >{{ $documento->nombre }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Cajas</label>
          <select id="caja" name="caja" class="grupoSelect select2 selectorGenero form-select"
            data-placeholder="Seleccione una caja" data-allow-clear="true">
            <option value="">Seleccione una caja</option>
            @foreach ($cajas as $caja)
            <option value="{{ $caja->id }}" {{ old('caja') == $caja->id ? 'selected' : '' }} >{{ $caja->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('caja'))
          <div class="text-danger form-label">{{ $errors->first('caja') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Centro de costos</label>
          <select id="centro_de_costos_egresos" name="centro_de_costos_egresos" class="grupoSelect select2 selectorGenero form-select"
            data-placeholder="Seleccione un centro de costos" data-allow-clear="true">
            <option value="">Seleccione un centro de costos</option>
            @foreach ($centroDeCostosEgresos as $centro)
            <option value="{{ $centro->id }}" {{ old('centro_de_costos_egresos') == $centro->id ? 'selected' : '' }}>{{ $centro->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('centro_de_costos_egresos'))
          <div class="text-danger form-label">{{ $errors->first('centro_de_costos_egresos') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="campoAdicional1" class="form-label">Campo adicional 1</label>
          <input type="text" id="campoAdicional1" name="campoAdicional1" value="{{ old('campoAdicional1') }}" class="form-control"  class="form-control"placeholder="Campo adicional 1" aria-describedby="defaultFormControlHelp" />
          @if ($errors->has('campoAdicional1'))
          <div class="text-danger form-label" >{{ $errors->first('campoAdicional2') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="campoAdicional2" class="form-label">Campo adicional 2</label>
          <input type="text" id="campoAdicional2" name="campoAdicional2" value="{{ old('campoAdicional2') }}" class="form-control"  placeholder="campoAdicional2" aria-describedby="campoAdicional2" />
          @if ($errors->has('campoAdicional2'))
          <div class="text-danger form-label">{{ $errors->first('campoAdicional2') }}</div>
          @endif
        </div>

      </div>
    </div>
  </div>

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2" >
        <span class="align-middle me-sm-1 me-0 ">Guardar</span>
      </button>
    </div>
  </div>
  <!-- /botonera -->
</form>

<!-- Modal para crear proveedor -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedorLabel"
  aria-hidden="true">
  <div class="modal-dialog">
    <form id="formCrearProveedor" method="POST" action="{{ route('finanzas.crearProveedor') }}">
      @csrf
      <input type="hidden" name="ingreso_id" id="ingresoIdInput">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalProveedorLabel">Crear proveedor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nombre" class="form-label">Nombre</label>
              <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="form-control" placeholder="Nombre" />
              @if ($errors->has('nombre'))<div class="text-danger">{{ $errors->first('nombre') }}</div>@endif
            </div>

            <div class="col-md-6 mb-3">
              <label for="identificacion" class="form-label">Identificación</label>
              <input type="text" id="identificacion" name="identificacion"
                value="{{ old('identificacion') }}" class="form-control" placeholder="Identificación" />
              @if ($errors->has('identificacion'))<div class="text-danger">{{ $errors->first('identificacion') }}</div>@endif
            </div>

            <div class="col-md-6 mb-3">
              <label for="tipoIdentificacion" class="form-label">Tipo de Identificación</label>
              <select id="tipoIdentificacion" name="tipoIdentificacion" class="form-select select2"
                data-placeholder="Seleccione un tipo de identificación">
                <option value="">Seleccione un tipo de identificación</option>
                @foreach ($tipoIdentificaciones as $tipoIdentificacion)
                <option value="{{ $tipoIdentificacion->id }}">{{ $tipoIdentificacion->nombre }}</option>
                @endforeach
              </select>
              @if ($errors->has('tipoIdentificacion'))<div class="text-danger">{{ $errors->first('tipoIdentificacion') }}</div>@endif
            </div>

            <div class="col-md-6 mb-3">
              <label for="telefono" class="form-label">Teléfono</label>
              <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}"
                class="form-control" placeholder="Teléfono" />
              @if ($errors->has('telefono'))<div class="text-danger">{{ $errors->first('telefono') }}</div>@endif
            </div>

            <div class="col-12 mb-3">
              <label for="direccion" class="form-label">Dirección</label>
              <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" class="form-control" placeholder="Dirección" />
              @if ($errors->has('direccion'))<div class="text-danger">{{ $errors->first('direccion') }}</div>@endif
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal de Documentos -->
<div class="modal fade" id="modalDocumentos" tabindex="-1" aria-labelledby="modalDocumentosLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="formDocumentos" method="GET" role="form" action="{{ route('finanzas.documento') }}""
                enctype=" multipart/form-data">
      <input type="hidden" name="_token" value="{{ csrf_token() }}" />

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDocumentosLabel">Agregar documento equivalente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nombreDocumento" class="form-label">Nombre</label>
              <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre del documento">
              @if ($errors->has('nombre'))<div class="text-danger form-label">{{ $errors->first('nombre') }}</div>@endif
            </div>

            <div class="col-md-6 mb-3">
              <label for="identificacionDocumento" class="form-label">Identificación</label>
              <input type="text" name="identificacion" id="identificacionDocumento"
                class="form-control" placeholder="Número de identificación">
              @if ($errors->has('identificacion'))<div class="text-danger form-label">{{ $errors->first('identificacion') }}</div>@endif
            </div>

            <div class="col-md-6 mb-3">
              <label for="cantidadDocumento" class="form-label">Cantidad</label>
              <input type="number" name="cantidad" id="cantidadDocumento" class="form-control"
                placeholder="Cantidad">
              @if ($errors->has('cantidad'))<div class="text-danger form-label">{{ $errors->first('cantidad') }}</div>@endif
            </div>

            <div class="col-md-6 mb-3">
              <label for="telefonoDocumento" class="form-label">Teléfono</label>
              <input type="text" name="telefono" id="telefonoDocumento" class="form-control" placeholder="Teléfono de contacto">
            </div>

            <div class="col-md-6 mb-3">
              <label for="direccionDocumento" class="form-label">Dirección</label>
              <input type="text" name="direccion" id="direccionDocumento" class="form-control" placeholder="Dirección del contacto">
            </div>

            <div class="col-md-6 mb-3">
              <label for="valorDocumento" class="form-label">Valor</label>
              <input type="number" step="0.01" name="valor" id="valorDocumento" class="form-control" placeholder="Valor">
              @if ($errors->has('valor'))<div class="text-danger form-label">{{ $errors->first('valor') }}</div>@endif
            </div>
          </div>

          <div class="col-md-12 mb-6">
            <label for="detalleDocumento" class="form-label">Detalle</label>
            <textarea type="text" name="detalle" id="detalleDocumento" class="form-control"
              placeholder="Detalle del documento"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
