@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
])
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@section('page-script')
<script type="module">
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron = /[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }

  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d"
  });

  $(".hora-picker").flatpickr({
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
  });

  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });
  });
</script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Gestiona tu iglesia</h4>
<p class="mb-4 text-black">Aquí podrás configurar y actualizar la información de tu congregación.</p>

@include('layouts.status-msn')

<form id="formulario" role="form" class="
    forms-sample" method="POST" action="{{ route('iglesia.update', $iglesia) }}" enctype="multipart/form-data">
  @csrf
  @method('PATCH')

  <div class="card p-4 w-100"> 
    <div class="card-header px-0 py-1">
      <h5 class="fw-semibold" >Información básica</h5>
    </div>
    <div class="row">
      <div class="col-4 mb-3">
        <label class="form-label">Nombre</label>
        <input required type="text" value="{{ $iglesia->nombre }}" id="" name="nombre" class="form-control">
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Fecha de creación de la iglesia</label>
        <input type="text" value="{{ $iglesia->fecha_apertura }}" name="fechaApertura" placeholder="YYYY-MM-DD"
          class="fecha form-control fecha-picker">
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Fecha de suscripción de la iglesia</label>
        <input type="text" value="{{ $iglesia->fecha_suscripcion }}" name="fechaSuscripcion" placeholder="YYYY-MM-DD"
          class="fecha form-control fecha-picker">
      </div>

      <!-- Segunda fila -->
      <div class="col-4 mb-3">
        <label class="form-label">Cantidad estimada de membresía</label>
        <input type="number" value="{{ $iglesia->membresia_estimada }}" name="cantidadMembresia" class="form-control">
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Teléfono fijo</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
          <input type="text" value="{{ $iglesia->telefono1 }}" name="telefonoFijo" class="form-control">
        </div>
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Otro teléfono</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
          <input type="text" value="{{ $iglesia->telefono2 }}" name="otroTelefono" class="form-control">
        </div>
      </div>
    </div>
  </div>


  <!-- Segunda Card -->
  <div class="card p-4 w-100 mt-4">
    <div class="card-header px-0 py-1">
      <h5 class="fw-semibold">Ubicación</h5>
    </div>
    <div class="row">
      <!-- Primera fila -->
      <div class="col-4 mb-3">
        <label class="form-label">Continente</label>
        <select id="continente" name="continente" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($continentes as $continente)
          <option @if ($continente->id == $iglesia->continente_id) selected @endif value="{{$continente->id}}">
            {{$continente->nombre}}
          </option>
          @endforeach
        </select>
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">País</label>
        <select id="pais" name="pais" class="grupoSelect select2 selectorGenero form-select" data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($paises as $pais)
          <option @if ($pais->id == $iglesia->pais_id) selected @endif
            value={{ $pais->id }}>{{ $pais->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Región</label>
        <select id="region" name="region" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($regiones as $region)
          <option @if ($region->id == $iglesia->region_id) selected @endif
            value={{ $region->id }}>{{ $region->nombre }}</option>
          @endforeach
        </select>
      </div>

      <!-- Segunda fila -->
      <div class="col-4 mb-3">
        <label class="form-label">Departamento</label>
        <select id="departamento" name="departamento" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($departamentos as $departamento)
          <option @if ($departamento->id == $iglesia->departamento_id) selected @endif value={{ $departamento->id }}>{{ $departamento->nombre }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-4 mb-3">
        <label class="form-label">Ciudad</label>
        <select id="ciudad" name="ciudad" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($ciudades as $ciudad)
          <option @if ($ciudad->id == $iglesia->municipio_id) selected @endif value={{ $ciudad->id }}>{{ $ciudad->nombre }}</option>
          @endforeach
        </select>
      </div>

    </div>
    <div class="mb-2 col-12 col-md-6">
      <label class="form-label">Dirección</label>
      <div class="input-group input-group-merge">
        <span class="input-group-text"><i class="ti ti-map"></i></span>
        <input onkeypress="return sinComillas(event)" id="direccion" name="direccion" value="{{ $iglesia->direccion }}"
          type="text" class="form-control" spellcheck="false" data-ms-editor="true"
          placeholder="Digita la dirección, la ciudad y el país, donde vives.">
      </div>
    </div>
  </div>
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btn-primary rounded-pill me-1 btnGuardar">Guardar</button>
    </div>
  </div>

</form>
</div>
@endsection
