@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
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
</script>

<style>
</style>
@endsection

@section('content')

<h4 class="mb-1">Crear Ingreso</h4>
<p class="mb-4">Registra tus ingresos financieros.</p>

@include('layouts.status-msn')


<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalDocumentos">
  <i class="ti ti-file-spreadsheet me-2"></i>
  Crear Documento Equivalente
</button>
<!-- Modal de Documentos -->
<div class="modal fade" id="modalDocumentos" tabindex="-1" aria-labelledby="modalDocumentosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="formDocumentos" method="GET" role="form" action="{{ route('finanzas.documento') }}""
                enctype=" multipart/form-data">
      <input type="hidden" name="_token" value="{{ csrf_token() }}" />


      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDocumentosLabel">Agregar Documento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nombreDocumento" class="form-label">Nombre</label>
              <input type="text" name="nombre" id="nombre" class="form-control"
                placeholder="Nombre del documento">
            </div>

            <div class="col-md-6 mb-3">
              <label for="identificacionDocumento" class="form-label">Identificación</label>
              <input type="text" name="identificacion" id="identificacionDocumento"
                class="form-control" placeholder="Número de identificación">
            </div>

            <div class="col-md-6 mb-3">
              <label for="cantidadDocumento" class="form-label">Cantidad</label>
              <input type="number" name="cantidad" id="cantidadDocumento" class="form-control"
                placeholder="Cantidad">
            </div>

            <div class="col-md-6 mb-3">
              <label for="detalleDocumento" class="form-label">Detalle</label>
              <input type="text" name="detalle" id="detalleDocumento" class="form-control"
                placeholder="Detalle del documento">
            </div>

            <div class="col-md-6 mb-3">
              <label for="telefonoDocumento" class="form-label">Teléfono</label>
              <input type="text" name="telefono" id="telefonoDocumento" class="form-control"
                placeholder="Teléfono de contacto">
            </div>

            <div class="col-md-6 mb-3">
              <label for="direccionDocumento" class="form-label">Dirección</label>
              <input type="text" name="direccion" id="direccionDocumento" class="form-control"
                placeholder="Dirección del contacto">
            </div>

            <div class="col-12 mb-3">
              <label for="valorDocumento" class="form-label">Valor</label>
              <input type="number" step="0.01" name="valor" id="valorDocumento" class="form-control"
                placeholder="Valor">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar Documento</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection