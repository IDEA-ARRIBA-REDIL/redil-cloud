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
    $('input[name="es_externo"]').change(function() {
      if ($(this).val() == '1') {
        $('#persona-container').fadeOut('fast', function() {
          $('#campos_externos').fadeIn('fast');
        });
      } else {
        $('#campos_externos').fadeOut('fast', function() {
          $('#persona-container').fadeIn('fast');
        });
      }
    });

    $('#formulario').submit(function(e){

      // Limpiar errores previos
      $('.custom-error').remove();
      let isValid = true;
      let esExterno = $('input[name="es_externo"]:checked').val() == '1';

      if (!esExterno) {
        // Validar Persona (solo si el campo existe)
        let personaInput = $('input[name="persona"]');
        if (personaInput.length > 0 && !personaInput.val()) {
          $('#persona-container').append('<div class="text-danger form-label custom-error mt-1">Este campo es obligatorio.</div>');
          isValid = false;
        }
      } else {
        // Validar Nombre Externo
        let nombreExterno = $('#nombre_externo').val().trim();
        if (!nombreExterno) {
          $('#nombre_externo').parent().append('<div class="text-danger form-label custom-error mt-1">Este campo es obligatorio.</div>');
          isValid = false;
        }
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

        <!-- Información principal -->
        <div class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
          <div class="card-header pb-1">
            <h6 class="card-title mb-0 fw-semibold">
              Información principal
            </h6>
          </div>
          <div class="card-body">
            <div class="row mt-3">

              @if($crearPeticionOtros)
                <!-- Opciones para Tipo de Persona (Registrada vs No Registrada) -->
                <div class="col-12 mb-4">
                  <label class="form-label">¿Para quién es la petición?</label>
                  <div class="row">
                    <div class="col-md-6 mb-md-0 mb-2">
                      <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border {{ !old('es_externo') ? 'checked' : '' }}">
                        <label class="form-check-label custom-option-content p-3" for="es_externo_no">
                          <span class="custom-option-header m-0 pb-0">
                            <span class="h6 mb-0 d-flex align-items-center text-black"><i class="ti ti-user-check me-3 text-black" style="color: black !important;"></i> Persona registrada</span>
                            <input name="es_externo" class="form-check-input" type="radio" value="0" id="es_externo_no" {{ !old('es_externo') ? 'checked' : '' }} />
                          </span>
                        </label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border {{ old('es_externo') == '1' ? 'checked' : '' }}">
                        <label class="form-check-label custom-option-content p-3" for="es_externo_si">
                          <span class="custom-option-header m-0 pb-0">
                            <span class="h6 mb-0 d-flex align-items-center text-black"><i class="ti ti-user-plus me-3 text-black" style="color: black !important;"></i> Persona no registrada</span>
                            <input name="es_externo" class="form-check-input" type="radio" value="1" id="es_externo_si" {{ old('es_externo') == '1' ? 'checked' : '' }} />
                          </span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mb-3 col-12 mb-md-3" id="persona-container" style="display: {{ old('es_externo') == '1' ? 'none' : 'block' }};">
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

                <div id="campos_externos" class="col-12" style="display: {{ old('es_externo') == '1' ? 'block' : 'none' }};">
                  <div class="mb-3">
                      <label class="form-label" for="nombre_externo">Nombre completo de la persona</label>
                      <input type="text" id="nombre_externo" name="nombre_externo" class="form-control" value="{{ old('nombre_externo') }}" placeholder="Ej: Juan Pérez">
                  </div>
                  <div class="row">
                      <div class="mb-3 col-md-6">
                          <label class="form-label" for="email_externo">Correo electrónico (opcional)</label>
                          <input type="email" id="email_externo" name="email_externo" class="form-control" value="{{ old('email_externo') }}" placeholder="ejemplo@correo.com">
                      </div>
                      <div class="mb-3 col-md-6">
                          <label class="form-label" for="telefono_externo">Teléfono / WhatsApp (opcional)</label>
                          <input type="text" id="telefono_externo" name="telefono_externo" class="form-control" value="{{ old('telefono_externo') }}" placeholder="Ej: +573001234567">
                      </div>
                  </div>
                  <div class="row">
                      <div class="mb-3 col-md-6">
                          <label class="form-label" for="genero_externo">Género</label>
                          <select id="genero_externo" name="genero_externo" class="form-select">
                              <option value="0" {{ old('genero_externo') == '0' ? 'selected' : '' }}>Hombre</option>
                              <option value="1" {{ old('genero_externo') == '1' ? 'selected' : '' }}>Mujer</option>
                          </select>
                      </div>
                      <div class="mb-3 col-md-6">
                          <label class="form-label" for="pais_id">País</label>
                          <select id="pais_id" name="pais_id" class="form-select">
                              @foreach($paises as $pais)
                                  <option value="{{ $pais->id }}" {{ old('pais_id', 1) == $pais->id ? 'selected' : '' }}>{{ $pais->nombre }}</option>
                              @endforeach
                          </select>
                      </div>
                  </div>
                </div>
              @else
                <input type="hidden" name="es_externo" value="0">
              @endif

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
