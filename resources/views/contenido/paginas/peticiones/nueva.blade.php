@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Peticiones')

<!-- Page -->
@section('page-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js',
  ])
@endsection

@section('page-script')
<script type="module">
  $(document).ready(function() {
    $('#select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguna'
    });
  });
</script>

<script>
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron =/[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }
</script>

<script type="module">
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
</script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Nueva petición</h4>
<p class="mb-4 text-black">Aquí podras ingresar una nueva petición, por favor llena los campos que son requeridos.</p>

@include('layouts.status-msn')

<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('peticion.crear') }}" enctype="multipart/form-data">
  @csrf

  <!-- Información principal -->
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header text-black fw-semibold">
        <img src="{{ Storage::url('generales/img/peticiones/icono_seccion_informacion_principal.png') }}" alt="icono" class="me-2" width="30">
        Información principal
      </h5>
      <div class="card-body">
        <div class="row">

          <div class="mb-3 col-12 col-md-6">
            @livewire('Usuarios.usuarios-para-busqueda', [
              'id' => 'persona',
              'class' => 'col-12 col-md-12 mb-3',
              'label' => '¿De quién es la petición?',
              'estiloSeleccion' => 'pequeno',
              'tipoBuscador' => 'unico',
              'queUsuariosCargar' => $queUsuariosCargar,
              'conDadosDeBaja' => 'no',
              'modulo' => 'peticiones',
              'obligatorio' => true,
              'usuarioSeleccionadoId' => old('persona') ?  old('persona') : ''
            ])
          </div>

          <!-- Tipos de petición -->
          <div class="mb-3 col-12 col-md-6">
            <label class="form-label" for="tipo_de_peticion">
              ¿Qué tipo de petición es?
            </label>
            <select id="tipo_de_peticion" name="tipo_de_petición" class="select2 form-select" data-allow-clear="true">
              <option value="" selected>Ninguno</option>
              @foreach ($tiposPeticiones as $tipoPeticion)
              <option value="{{$tipoPeticion->id}}" {{ old('tipo_de_grupo')==$tipoPeticion->id ? 'selected' : '' }}>{{$tipoPeticion->nombre}}</option>
              @endforeach
            </select>
            @if($errors->has('tipo_de_petición')) <div class="text-danger form-label">{{ $errors->first('tipo_de_petición') }}</div> @endif
          </div>
          <!-- Tipos de petición -->

          <!--  Escribe la petición -->
          <div class="mb-3 col-12 col-md-12">
            <label class="form-label" for="descripcion">
              Escribe la petición
            </label>
            <textarea onkeypress="return sinComillas(event)" id="descripcion" name="descripción" class="form-control" rows="2" spellcheck="false" data-ms-editor="true" placeholder="">{{ old('adiccional') }}</textarea>
            @if($errors->has('descripción')) <div class="text-danger form-label">{{ $errors->first('descripción') }}</div> @endif
          </div>
          <!--  Escribe la petición -->

        </div>
      </div>
    </div>
  </div>
  <!-- Información principal  -->

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2" >Guardar</button>
    </div>
  </div>
  <!-- /botonera -->

</form>

@endsection
