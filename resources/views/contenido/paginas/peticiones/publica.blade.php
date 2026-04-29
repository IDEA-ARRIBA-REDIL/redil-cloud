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
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
    $(document).ready(function() {
      $('input[name="tengo_cuenta"]').change(function() {
        if ($(this).val() == '1') {
          $('#seccion_invitado').fadeOut('fast', function() {
            $('#seccion_login').fadeIn('fast');
          });
        } else {
          $('#seccion_login').fadeOut('fast', function() {
            $('#seccion_invitado').fadeIn('fast');
          });
        }
      });
    });
  </script>

  <script type="module">
    $('#formulario').submit(function(e){

      // Limpiar errores previos
      $('.custom-error').remove();
      let isValid = true;
      let esInvitado = {{ auth()->check() ? 'false' : '$(\'input[name="tengo_cuenta"]:checked\').val() == "0"' }};

      if (esInvitado) {
        // Validar Nombre
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

      // Validar reCAPTCHA
      if (esInvitado && typeof grecaptcha !== 'undefined') {
        let recaptchaResponse = grecaptcha.getResponse();
        if (recaptchaResponse.length === 0) {
          $('#container_recaptcha').append('<div class="text-danger form-label custom-error mt-1">Por favor, verifica que no eres un robot.</div>');
          isValid = false;
        }
      }

      if (!isValid) {
        e.preventDefault();
        return false;
      }

      $('.btnGuardar').attr('disabled','disabled').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

      Swal.fire({
        title: "Espera un momento",
        text: "Ya estamos guardando tu petición...",
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
      @auth
        <a href="{{ route('dashboard') }}" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="ti-xs ti ti-home mx-2"></span>
          <span class="d-none d-md-inline-block fw-normal">Ir a plataforma</span>
        </a>
      @endauth
    </div>
    <div class="col-6 pl-5 text-center">
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Petición de oración</h5>
    </div>
    <div class="col-3 text-end">
      @auth
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
          @csrf
          <input type="hidden" name="redirect" value="{{ route('peticion.publica.nueva') }}">
          <button type="submit" class="btn rounded-pill waves-effect waves-light text-white" style="background: transparent; border: none;">
            <span class="d-none d-md-inline-block fw-normal">Cerrar sesión</span>
            <span class="ti-xs ti ti-logout mx-2"></span>
          </button>
        </form>
      @else
        <a href="{{ url()->previous() }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-inline-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      @endauth
    </div>
  </nav>

  <div class="container my-5" style="padding-bottom: 100px;">
    <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2 mt-4">

      @include('layouts.status-msn')

      @auth
        <h4 class="fw-semibold text-black ps-0 mb-5 text-center">Hola {{ auth()->user()->primer_nombre }}, escribe tu petición</h4>
      @else
        <h4 class="fw-semibold text-black ps-0 mb-5 text-center">Envíanos tu petición de oración</h4>
      @endauth

        @guest

          <div class="col-12 mb-4">
            <div class="card shadow-sm" >
              <div class="card-body">
                
              <label class="form-label">¿Tienes una cuenta creada?</label>
              <div class="row">
                <div class="col-md-6 mb-md-0 mb-2">
                  <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border checked">
                    <label class="form-check-label custom-option-content p-3" for="tengo_cuenta_no">
                      <span class="custom-option-header m-0 pb-0">
                        <span class="h6 mb-0 d-flex align-items-center text-black"><i class="ti ti-user-plus me-3 text-black" style="color: black !important;"></i> Persona no registrada</span>
                        <input name="tengo_cuenta" class="form-check-input" type="radio" value="0" id="tengo_cuenta_no" checked />
                      </span>
                    </label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
                    <label class="form-check-label custom-option-content p-3" for="tengo_cuenta_si">
                      <span class="custom-option-header m-0 pb-0">
                        <span class="h6 mb-0 d-flex align-items-center text-black"><i class="ti ti-user-check me-3 text-black" style="color: black !important;"></i> Persona registrada</span>
                        <input name="tengo_cuenta" class="form-check-input" type="radio" value="1" id="tengo_cuenta_si" />
                      </span>
                    </label>
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>
        @endguest

        <hr>

        <div id="seccion_login" style="display: none;" class="mb-5">
        
              <p class="mb-4 text-black">Inicia sesión para que tu petición quede asociada a tu cuenta.</p>
              @livewire('auth.inline-login')
         
        </div>

        <form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('peticion.publica.crear') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="es_externo" value="{{ auth()->check() ? '0' : '1' }}">

        <!-- Información principal -->
        <div id="seccion_invitado" class="card mb-5 shadow-sm" style="display: {{ auth()->check() ? 'none' : 'block' }};">
          <div class="card-header pb-1">
             <h6 class="card-title mb-0 fw-semibold">
              Tus datos de contacto
            </h6>
          </div>
          <div class="card-body">
            <div class="row mt-3">

              <div id="campos_externos" class="col-12">
                <div class="mb-3">
                    <label class="form-label" for="nombre_externo">Nombre completo</label>
                    <input type="text" id="nombre_externo" name="nombre_externo" class="form-control" value="{{ old('nombre_externo') }}" placeholder="Ej: Juan Pérez">
                </div>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="email_externo">Email</label>
                        <input type="email" id="email_externo" name="email_externo" class="form-control" value="{{ old('email_externo') }}" placeholder="ejemplo@correo.com">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="telefono_externo">Teléfono</label>
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
            </div>
          </div>
        </div>

        <!-- Detalles de la petición -->
        <div class="card mb-5 shadow-sm" >
          <div class="card-header pb-1">
             <h6 class="card-title mb-0 fw-semibold">
              Detalles de la petición
            </h6>
          </div>
          <div class="card-body">
            <div class="row mt-3">

              <!-- Tipos de petición -->
              <div class="mb-3 col-12 mb-md-3" id="container_tipo_peticion">
                <label class="form-label" for="tipo_de_peticion">
                  ¿Cuál es el motivo de tu petición?
                </label>
                <select id="tipo_de_peticion" name="tipo_de_petición" class="select2 form-select" data-allow-clear="true">
                  <option value="" selected>Selecciona un motivo...</option>
                  @foreach ($tiposPeticiones as $tipoPeticion)
                  <option value="{{$tipoPeticion->id}}" {{ old('tipo_de_petición') == $tipoPeticion->id ? 'selected' : '' }}>{{$tipoPeticion->nombre}}</option>
                  @endforeach
                </select>
                @if($errors->has('tipo_de_petición')) <div class="text-danger form-label">{{ $errors->first('tipo_de_petición') }}</div> @endif
              </div>

              <!--  Escribe la petición -->
              <div class="mb-3 col-12 mb-md-3">
                <label class="form-label" for="descripcion">
                  Describe tu petición
                </label>
                <textarea onkeypress="return sinComillas(event)" id="descripcion" name="descripción" class="form-control" rows="5" spellcheck="false" data-ms-editor="true" placeholder="Escribe aquí lo que necesites..."></textarea>
                @if($errors->has('descripción')) <div class="text-danger form-label">{{ $errors->first('descripción') }}</div> @endif
              </div>

              @guest
              <!-- reCAPTCHA -->
              <div class="mb-3 col-12 mt-2 mb-md-3 d-flex flex-column align-items-start" id="container_recaptcha">
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                @if($errors->has('g-recaptcha-response')) <div class="text-danger form-label mt-1">{{ $errors->first('g-recaptcha-response') }}</div> @endif
              </div>
              @endguest

            </div>
          </div>
        </div>


         <div class="w-100 fixed-bottom py-4 px-6 px-sm-0 border-top shadow-lg" style="background-color: #f8f7fa; z-index: 1040;">
          <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex justify-content-sm-end">
             <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-7 py-2" >
              <span class="align-middle me-sm-1 me-0"> Enviar petición </span>
            </button>
          </div>
        </div>

      </form>

    </div>
  </div>
</div>
@endsection
