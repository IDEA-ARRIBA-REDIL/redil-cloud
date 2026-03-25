@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Nueva Petición')

@section('page-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
  ])
  <style>
    body {
      overflow-x: hidden;
    }
  </style>
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
      $('#tipo_de_peticion').select2({
        width: '100%',
        allowClear: true,
        placeholder: 'Ninguno'
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
    $('#formulario').submit(function(e){

      // Limpiar errores previos
      $('.custom-error').remove();
      let isValid = true;

      // Validar Persona
      // El componente 'usuarios-para-busqueda' genera un input oculto con id 'persona' y name 'persona' solo si hay selección
      let personaInput = $('input[name="persona"]');
      if (personaInput.length === 0 || !personaInput.val()) {
        $('#persona-container').append('<div class="text-danger form-label custom-error mt-1">Este campo es obligatorio.</div>');
        isValid = false;
      }

      // Validar Tipo de petición
      let tipoPeticion = $('#tipo_de_peticion').val();
      if (!tipoPeticion) {
        $('#container_tipo_peticion').append('<div class="text-danger form-label custom-error mt-1">Este campo es obligatorio.</div>');
        isValid = false;
      }

      // Validar Descripción
      let descripcion = $('#descripcion').val().trim();
      if (!descripcion) {
        $('#descripcion').parent().append('<div class="text-danger form-label custom-error mt-1">Este campo es obligatorio.</div>');
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
        return false;
      }

      $('.btnGuardar').attr('disabled','disabled').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

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
<div class="min-vh-100 bg-body">

  <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center shadow-none border-bottom">
    <div class="col-3 text-start">
      <a href="{{ url()->previous() }}" type="button" class=" d-none btn rounded-pill waves-effect waves-light text-white prev-step">
        <span class="ti-xs ti ti-arrow-left me-2"></span>
        <span class="d-none d-md-block fw-normal">Volver</span>
      </a>
    </div>
    <div class="col-6 pl-5 text-center">
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Nueva petición</h5>
    </div>
    <div class="col-3 text-end">
      <a href="{{ url()->previous() }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
        <span class="d-none d-md-block fw-normal">Salir</span>
        <span class="ti-xs ti ti-x mx-2"></span>
      </a>
    </div>
  </nav>

  <div class="container my-5" style="padding-bottom: 100px;">
    <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2 mt-4">

      <form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('peticion.crear') }}" enctype="multipart/form-data">
        @csrf

        @include('layouts.status-msn')

        <h4 class="fw-semibold text-black ps-0 mb-5">Agendando nueva petición</h4>

        <!-- Información principal -->
        <div class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
          <div class="card-header pb-1">
            <h6 class="card-title mb-0 fw-semibold">
              Información principal
            </h6>
          </div>
          <div class="card-body">
            <div class="row mt-3">

              <div class="mb-3 col-12 mb-md-3" id="persona-container">
                @livewire('Usuarios.usuarios-para-busqueda', [
                  'id' => 'persona',
                  'class' => 'col-12 mb-3',
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
              <div class="mb-3 col-12 mb-md-3" id="container_tipo_peticion">
                <label class="form-label" for="tipo_de_peticion">
                  ¿Qué tipo de petición es?
                </label>
                <select id="tipo_de_peticion" name="tipo_de_petición" class="select2 form-select" data-allow-clear="true">
                  <option value="" selected>Selecciona un motivo...</option>
                  @foreach ($tiposPeticiones as $tipoPeticion)
                  <option value="{{$tipoPeticion->id}}" {{ old('tipo_de_petición') == $tipoPeticion->id ? 'selected' : '' }}>{{$tipoPeticion->nombre}}</option>
                  @endforeach
                </select>
                @if($errors->has('tipo_de_petición')) <div class="text-danger form-label">{{ $errors->first('tipo_de_petición') }}</div> @endif
              </div>
              <!-- Tipos de petición -->

              <!--  Escribe la petición -->
              <div class="mb-3 col-12 mb-md-3">
                <label class="form-label" for="descripcion">
                  Describe brevemente tu situación
                </label>
                <textarea onkeypress="return sinComillas(event)" id="descripcion" name="descripción" class="form-control" rows="4" spellcheck="false" data-ms-editor="true" placeholder="">{{ old('descripción') }}</textarea>
                @if($errors->has('descripción')) <div class="text-danger form-label">{{ $errors->first('descripción') }}</div> @endif
              </div>
              <!--  Escribe la petición -->

            </div>
          </div>
        </div>
        <!-- Información principal  -->

        <div class="w-100 fixed-bottom py-4 px-6 px-sm-0 border-top shadow-lg" style="background-color: #f8f7fa; z-index: 1040;">
          <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex justify-content-sm-end">
            <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-7 py-2" >
              <span class="align-middle me-sm-1 me-0"> Guardar </span>
            </button>
          </div>
        </div>

      </form>

    </div>
  </div>
</div>
@endsection
