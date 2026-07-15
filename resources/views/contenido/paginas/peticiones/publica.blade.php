@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Nueva Petición')

@section('page-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  ])
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,700;1,9..40,300&display=swap" rel="stylesheet">
  <style>
    @font-face{font-family:'BrittanySignature';src:url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780004789/brittany-signature-script.regular_t6h0ni.ttf') format('truetype');font-weight:normal;font-style:normal;font-display:swap}
    @font-face{font-family:'CreatoDisplay';src:url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780005392/CreatoDisplay-Light_yumiiz.otf') format('truetype');font-weight:300;font-display:swap}
    @font-face{font-family:'CreatoDisplay';src:url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780005138/CreatoDisplay-Regular_lwbfti.otf') format('truetype');font-weight:400;font-display:swap}
    @font-face{font-family:'CreatoDisplay';src:url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780005427/CreatoDisplay-ExtraBold_ps2j04.otf') format('truetype');font-weight:800;font-display:swap}
    @font-face{font-family:'CreatoDisplay';src:url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780005466/CreatoDisplay-Black_ikfdg1.otf') format('truetype');font-weight:900;font-display:swap}

    :root{
      /* tema claro — secciones blancas del sistema Manantial */
      --page:#f3f3f1;--card:#ffffff;--field:#f3f3f1;--field-focus:#ebebe8;
      --ink:#040407;--ink-mute:rgba(4,4,7,.56);--ink-dim:rgba(4,4,7,.36);
      --border:rgba(4,4,7,.12);--border-soft:rgba(4,4,7,.07);
      --b:#0089c4;--b2:#0077ad;--bline:#0099d9;--bsoft:rgba(0,153,217,.08);
      --err:#d23b3b;
      --cd:'CreatoDisplay',sans-serif;--sc:'BrittanySignature',cursive;--sa:'DM Sans',sans-serif;
      --radius:14px;
    }
    
    body{background:var(--page) !important;color:var(--ink) !important;font-family:var(--sa) !important;min-height:100vh;overflow-x:hidden}
    
    header{position:relative;z-index:5;display:flex;align-items:center;justify-content:space-between;padding:24px clamp(20px,5vw,60px)}
    .back{display:flex;align-items:center;gap:8px;font-size:14px;color:var(--ink-mute);transition:color .25s;text-decoration:none}
    .back:hover{color:var(--ink)}
    .back svg{width:16px;height:16px}
    .logo-mini{font-family:var(--cd);font-weight:900;font-size:15px;letter-spacing:.06em;color:var(--ink)}
    .logo-mini span{color:var(--b)}

    .hero{position:relative;z-index:2;text-align:center;padding:18px 24px 8px}
    .eyebrow{font-size:11px;letter-spacing:.22em;color:var(--b2);font-weight:700;text-transform:uppercase;margin-bottom:14px}
    .hero h1{font-family:var(--cd);font-weight:900;text-transform:uppercase;font-size:clamp(30px,5.4vw,46px);line-height:1.05;letter-spacing:-.01em;color:var(--ink)}
    .hero h1 .sc{font-family:var(--sc);font-weight:400;text-transform:none;color:var(--b2);font-size:1.4em;display:inline-block;margin:0 .08em;vertical-align:-.08em}
    .hero p{max-width:480px;margin:18px auto 0;color:var(--ink-mute);font-size:15.5px;line-height:1.65}

    main{position:relative;z-index:2;display:flex;justify-content:center;padding:36px 20px 80px}
    .main-card{width:100%;max-width:560px;background:var(--card);border:1px solid var(--border-soft);border-radius:var(--radius);padding:clamp(24px,4vw,44px);box-shadow:0 1px 2px rgba(4,4,7,.03),0 18px 50px -28px rgba(4,4,7,.18)}

    /* SECCION TITLE entre peticion y datos */
    .section-title{font-family:var(--cd);font-weight:800;font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-dim);margin:30px 0 16px;padding-top:24px;border-top:1px solid var(--border-soft)}

    /* TOGGLE NUEVO / YA TENGO CUENTA */
    .mode-toggle{display:flex;background:var(--field);border-radius:10px;padding:4px;margin-bottom:22px}
    .mode-btn{flex:1;border:none;background:transparent;padding:10px 12px;font-family:var(--sa);font-size:13.5px;font-weight:600;color:var(--ink-mute);border-radius:7px;cursor:pointer;transition:all .2s}
    .mode-btn.active{background:var(--card);color:var(--ink);box-shadow:0 1px 3px rgba(4,4,7,.1)}

    @keyframes fade{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
    .subpanel{display:none}
    .subpanel.active{display:block;animation:fade .3s ease}

    .field{margin-bottom:18px}
    .field label{display:block;font-size:13px;font-weight:600;margin-bottom:8px;color:var(--ink)}
    .field label .opt{color:var(--ink-dim);font-weight:400}
    .field input, .field textarea, .field select{width:100%;background:var(--field) !important;border:1px solid var(--border) !important;border-radius:10px !important;padding:13px 14px !important;color:var(--ink) !important;font-family:var(--sa) !important;font-size:15px !important;transition:border-color .25s,background .25s}
    .field input::placeholder,.field textarea::placeholder{color:var(--ink-dim) !important}
    .field input:focus, .field textarea:focus, .field select:focus{border-color:var(--bline) !important;background:var(--card) !important;outline:none !important}
    .field textarea{resize:vertical;min-height:110px;line-height:1.5}
    
    .field small.err{display:none;color:var(--err);font-size:12.5px;margin-top:6px}
    .field.invalid input, .field.invalid select, .field.invalid textarea{border-color:var(--err) !important}
    .field.invalid small.err{display:block}

    .login-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;font-size:13px}
    .chk{display:flex;align-items:center;gap:7px;color:var(--ink-mute);cursor:pointer}
    .chk input{width:15px;height:15px;accent-color:var(--bline)}
    .forgot{color:var(--b2);font-weight:600}
    .forgot:hover{text-decoration:underline}

    .motivos{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:22px}
    .motivo{position:relative}
    .motivo input{position:absolute;opacity:0;inset:0;cursor:pointer;width:100%;height:100%;margin:0;z-index:2}
    .motivo .card-inner{display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid var(--border);border-radius:10px;padding:16px 12px;text-align:center;transition:all .2s;background:var(--field);min-height:88px}
    .motivo .card-inner svg, .motivo .card-inner i{width:20px;height:20px;font-size:20px;margin-bottom:8px;stroke:var(--ink-mute);color:var(--ink-mute);transition:stroke .2s, color .2s;flex-shrink:0;fill:none}
    .motivo .card-inner span{display:block;font-size:13px;color:var(--ink-mute);font-weight:500;line-height:1.3}
    
    .motivo input:checked + .card-inner{border-color:var(--bline);background:var(--bsoft)}
    .motivo input:checked + .card-inner svg{stroke:var(--b2)}
    .motivo input:checked + .card-inner i{color:var(--b2)}
    .motivo input:checked + .card-inner span{color:var(--ink);font-weight:700}
    .motivo input:focus-visible + .card-inner{outline:2px solid var(--bline);outline-offset:2px}

    .row-btns{display:flex;gap:12px;margin-top:8px}
    .btn{width:100%;padding:15px 20px;border-radius:10px;font-family:var(--sa);font-weight:700;font-size:14.5px;cursor:pointer;border:none;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .25s}
    .btn-primary, #submitBtn{background:var(--b) !important;color:#fff !important;border:none !important;border-color:transparent !important;box-shadow:none !important}
    .btn-primary:hover, #submitBtn:hover{background:var(--b2) !important;border:none !important;border-color:transparent !important}
    .btn-primary:disabled, #submitBtn:disabled{background:var(--border) !important;color:var(--ink-dim) !important;cursor:not-allowed !important;border:none !important}
    
    .spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;display:none;animation:spin .7s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .btn-primary.loading .spinner{display:inline-block}
    .btn-primary.loading .btn-text{opacity:.7}

    .account-note{text-align:center;margin-top:18px;font-size:13px;color:var(--ink-mute)}
    .account-note a{color:var(--b2);font-weight:600;border-bottom:1px solid transparent}
    .account-note a:hover{border-color:var(--b2)}

    @media(max-width:520px){
      .motivos{grid-template-columns:1fr}
      header{padding:18px 18px} 
    } 

    .swal2-actions {
      flex-direction: column !important;
      gap: 10px !important;
      width: 100% !important;
      padding: 0 10px !important;
      margin-top: 1.5rem !important;
    }
    .swal-btn-confirm {
      width: 100% !important;
      padding: 12px 20px !important;
      border-radius: 10px !important;
      font-family: var(--sa) !important;
      font-weight: 700 !important;
      font-size: 14.5px !important;
      cursor: pointer !important;
      border: none !important;
      background: var(--b) !important;
      color: #fff !important;
      transition: background .25s !important;
    }
    .swal-btn-confirm:hover {
      background: var(--b2) !important;
    }
    .swal-btn-deny {
      width: 100% !important;
      padding: 12px 20px !important;
      border-radius: 10px !important;
      font-family: var(--sa) !important;
      font-weight: 700 !important;
      font-size: 14.5px !important;
      cursor: pointer !important;
      border: 1px solid var(--border) !important;
      background: transparent !important;
      color: var(--ink-mute) !important;
      transition: all .25s !important;
    }
    .swal-btn-deny:hover {
      background: var(--field) !important;
      color: var(--ink) !important;
    }
    .swal-btn-cancel {
      background: transparent !important;
      border: none !important;
      color: var(--ink-dim) !important;
      font-family: var(--sa) !important;
      font-weight: 500 !important;
      font-size: 13.5px !important;
      cursor: pointer !important;
      padding: 8px !important;
    }
    .swal-btn-cancel:hover {
      color: var(--ink-mute) !important;
      text-decoration: underline !important;
    }
  </style>
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  ])
@endsection

@section('page-script')
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <script>
    function sinComillas(e) {
      var tecla = (document.all) ? e.keyCode : e.which;
      var patron =/[\x5C'"]/;
      var te = String.fromCharCode(tecla);
      return !patron.test(te);
    }
  </script>

  <script type="module">
    let verified = false;

    function enviarFormularioDirecto() {
      verified = true;
      $('#submitBtn').addClass('loading').attr('disabled', 'disabled');

      Swal.fire({
        title: "Espera un momento",
        text: "Ya estamos guardando tu petición...",
        icon: "info",
        showCancelButton: false,
        showConfirmButton: false,
        showDenyButton: false
      });

      $('#formulario').submit();
    }

    $('#formulario').submit(function(e) {
      if (verified) {
        return true;
      }

      // Limpiar errores previos
      $('.field').removeClass('invalid');
      $('.custom-error').remove();
      $('#motivoErr').hide();
      
      let isValid = true;
      let esInvitado = {{ auth()->check() ? 'false' : 'true' }};

      // 1. Validar Motivo
      let tipoPeticion = $('input[name="tipo_de_petición"]:checked').val();
      if (!tipoPeticion) {
        $('#f-motivo').addClass('invalid');
        $('#motivoErr').show();
        isValid = false;
      }

      // 2. Validar Descripción
      let descripcion = $('#descripcion').val().trim();
      if (descripcion.length <= 3) {
        $('#f-detalle').addClass('invalid');
        isValid = false;
      }

      // Si ya asociamos un usuario, no necesitamos el resto de validaciones de invitado
      let asociarUsuarioId = $('#asociar_usuario_id').val();

      if (esInvitado && !asociarUsuarioId) {
        // Validar Nombre
        let nombreExterno = $('#nombre_externo').val().trim();
        if (nombreExterno.length <= 1) {
          $('#f-nombre').addClass('invalid');
          isValid = false;
        }

        // Validar Email
        let emailExterno = $('#email_externo').val().trim();
        if (!emailExterno || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailExterno)) {
          $('#f-email').addClass('invalid');
          isValid = false;
        }

        // Validar Género
        let generoExterno = $('#genero_externo').val();
        if (generoExterno === "" || generoExterno === null) {
          $('#f-genero').addClass('invalid');
          isValid = false;
        }

        // Validar País
        let paisId = $('#pais_id').val();
        if (!paisId) {
          $('#f-pais').addClass('invalid');
          isValid = false;
        }
      }

      // reCAPTCHA es necesario siempre para invitados
      if (esInvitado) {
        if (typeof grecaptcha !== 'undefined') {
          let recaptchaResponse = grecaptcha.getResponse();
          if (recaptchaResponse.length === 0) {
            $('#container_recaptcha').after('<div class="text-danger form-label custom-error small mt-1">Por favor, verifica que no eres un robot.</div>');
            isValid = false;
          }
        }
      }

      if (!isValid) {
        e.preventDefault();
        // llevar al usuario al primer campo invalido visible
        const firstInvalid = $('.field.invalid, #motivoErr:visible');
        if (firstInvalid.length > 0) {
          $('html, body').animate({
            scrollTop: firstInvalid.first().offset().top - 100
          }, 300);
        }
        return false;
      }

      e.preventDefault(); // Detener el envío por defecto para verificación AJAX

      // Si ya está autenticado, enviar directamente
      if (!esInvitado) {
        enviarFormularioDirecto();
        return;
      }

      // Si el usuario ya eligió enviar como invitado
      if ($('#enviar_como_invitado').val() === '1' || asociarUsuarioId) {
        enviarFormularioDirecto();
        return;
      }

      // Verificar correo electrónico
      let emailExterno = $('#email_externo').val().trim();
      $('#submitBtn').addClass('loading').attr('disabled', 'disabled');

      fetch("{{ route('peticion.publica.verificar-correo') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ email: emailExterno })
      })
      .then(response => response.json())
      .then(data => {
        if (data.exists) {
          // Restaurar botón de submit
          $('#submitBtn').removeClass('loading').removeAttr('disabled');

          // Mostrar SweetAlert2 con las 3 opciones
          Swal.fire({
            title: '¿Asociar esta petición a tu cuenta?',
            html: `
              <div class="text-center my-3" style="font-family: var(--sa);">
                <img src="${data.user.foto_url}" class="rounded-circle mb-3 border border-2 border-primary" style="width: 80px; height: 80px; object-fit: cover;">
                <h5 class="fw-bold mb-1" style="color: var(--ink); font-family: var(--cd);">${data.user.nombre}</h5>
                <p class="text-muted small mb-0">${data.user.email}</p>
              </div>
              <p class="text-start mb-0" style="color: var(--ink-mute); font-size: 0.95rem; line-height: 1.5;">
                Hemos detectado que este correo pertenece a una cuenta registrada. ¿Deseas enviar esta petición asociándola al usuario detectado o enviar esta petición como invitado?
              </p>
            `,
            icon: 'info',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Sí, enviar a mi nombre',
            denyButtonText: 'No, enviar como invitado',
            cancelButtonText: 'Cancelar envío',
            customClass: {
              actions: 'swal2-actions',
              confirmButton: 'swal-btn-confirm',
              denyButton: 'swal-btn-deny',
              cancelButton: 'swal-btn-cancel'
            },
            buttonsStyling: false
          }).then((result) => {
            if (result.isConfirmed) {
              // Sí, enviar petición a nombre del usuario
              $('#asociar_usuario_id').val(data.user.id);
              enviarFormularioDirecto();
            } else if (result.isDenied) {
              // No, enviar petición como invitado
              $('#enviar_como_invitado').val('1');
              enviarFormularioDirecto();
            }
            // Si cancela, vuelve al formulario sin enviar
          });
        } else {
          // No existe cuenta asociada, enviar directamente
          enviarFormularioDirecto();
        }
      })
      .catch(error => {
        console.error("Error al verificar correo:", error);
        // En caso de error, enviar como invitado directamente
        enviarFormularioDirecto();
      });
    });
  </script>
@endsection

@section('content')
<header>
  @auth
    <a class="back" href="{{ route('dashboard') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
      Ir a plataforma
    </a>
  @else
    <a class="back" href="{{ session('peticion_retorno_url', url()->previous()) }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
      Volver
    </a>
  @endauth
  
  <div class="logo-mini">
    @if ($configuracion && $configuracion->logo_personalizado && $configuracion->logo_app)
      <img src="{{ tenant_asset('img/branding/'.$configuracion->logo_app) }}" alt="Logo" style="height: 24px; vertical-align: middle;">
    @else
      MANANTIAL<span>.</span>
    @endif
  </div>
</header>

<section class="hero">
  <div class="eyebrow">Queremos orar por ti</div>
  <h1>Cuéntanos <span class="sc">qué</span><br>necesita tu corazón</h1>
  <p>No estás solo en esto. Nuestro equipo de oración recibe cada petición y la lleva delante de Dios esta misma semana.</p>
</section>

<main>
  <div class="card main-card">
    
    @include('layouts.status-msn')

    <form id="formulario" role="form" method="POST" action="{{ route('peticion.publica.crear') }}" enctype="multipart/form-data" novalidate>
      @csrf
      <input type="hidden" name="es_externo" value="{{ auth()->check() ? '0' : '1' }}">

      <!-- 1. Motivos de Petición -->
      <div class="field @error('tipo_de_petición') invalid @enderror" id="f-motivo">
        <label>¿Cuál es el motivo de tu petición?</label>
        <div class="motivos" id="container_tipo_peticion_wrapper">
          @foreach ($tiposPeticiones as $tipoPeticion)
            @php
              $iconDb = $tipoPeticion->icono ?: 'ti ti-help-circle';
            @endphp
            <label class="motivo">
              <input type="radio" name="tipo_de_petición" value="{{ $tipoPeticion->id }}" {{ old('tipo_de_petición') == $tipoPeticion->id ? 'checked' : '' }} required>
              <span class="card-inner">
                  <i class="{{ $iconDb }}"></i>
                <span>{{ $tipoPeticion->nombre }}</span>
              </span>
            </label>
          @endforeach
        </div>
        <small class="err" id="motivoErr" style="@error('tipo_de_petición') display:block; @else display:none; @enderror color:var(--err)">
          @error('tipo_de_petición') {{ $message }} @else Selecciona un motivo para continuar. @enderror
        </small>
      </div>

      <!-- 2. Detalles (Cuéntanos más) -->
      <div class="field @error('descripción') invalid @enderror" id="f-detalle">
        <label for="descripcion">Cuéntanos más</label>
        <textarea onkeypress="return sinComillas(event)" id="descripcion" name="descripción" placeholder="Comparte lo que quieras que sepamos. Esto es completamente confidencial." required>{{ old('descripción') }}</textarea>
        <small class="err">@error('descripción') {{ $message }} @else Escribe brevemente tu petición para que podamos orar con propósito. @enderror</small>
      </div>

      @guest
        <!-- 3. Tus Datos -->
        <div class="section-title">Tus datos</div>

        <input type="hidden" name="asociar_usuario_id" id="asociar_usuario_id" value="">
        <input type="hidden" name="enviar_como_invitado" id="enviar_como_invitado" value="0">

        <!-- Formulario Unificado -->
        <div class="field @error('nombre_externo') invalid @enderror" id="f-nombre">
          <label for="nombre_externo">Nombre completo</label>
          <input type="text" id="nombre_externo" name="nombre_externo" placeholder="Ej. María Fernández" autocomplete="name" value="{{ old('nombre_externo', auth()->check() ? auth()->user()->name : '') }}" required>
          <small class="err">@error('nombre_externo') {{ $message }} @else Cuéntanos tu nombre para poder orar por ti. @enderror</small>
        </div>

        <div class="field @error('email_externo') invalid @enderror" id="f-email">
          <label for="email_externo">Correo electrónico</label>
          <input type="email" id="email_externo" name="email_externo" placeholder="tucorreo@ejemplo.com" autocomplete="email" value="{{ old('email_externo', auth()->check() ? auth()->user()->email : '') }}" required>
          <small class="err">@error('email_externo') {{ $message }} @else Escribe un correo válido — te avisaremos cuando oremos por ti. @enderror</small>
        </div>

        <div class="field @error('telefono_externo') invalid @enderror" id="f-telefono">
          <label for="telefono_externo">Teléfono <span class="opt">(opcional)</span></label>
          <input type="tel" id="telefono_externo" name="telefono_externo" placeholder="+57 300 000 0000" autocomplete="tel" value="{{ old('telefono_externo') }}">
          <small class="err">@error('telefono_externo') {{ $message }} @enderror</small>
        </div>

        <div class="field @error('genero_externo') invalid @enderror" id="f-genero">
          <label for="genero_externo">Género</label>
          <select id="genero_externo" name="genero_externo" class="form-select" required>
            <option value="" disabled {{ old('genero_externo') === null ? 'selected' : '' }}>Selecciona tu género</option>
            <option value="0" {{ old('genero_externo') == '0' ? 'selected' : '' }}>Hombre</option>
            <option value="1" {{ old('genero_externo') == '1' ? 'selected' : '' }}>Mujer</option>
          </select>
          <small class="err">@error('genero_externo') {{ $message }} @else El género es obligatorio. @enderror</small>
        </div>

        <div class="field @error('pais_id') invalid @enderror" id="f-pais">
          <label for="pais_id">País</label>
          <select id="pais_id" name="pais_id" class="form-select" required>
            @foreach($paises as $pais)
              <option value="{{ $pais->id }}" {{ old('pais_id', 1) == $pais->id ? 'selected' : '' }}>{{ $pais->nombre }}</option>
            @endforeach
          </select>
          <small class="err">@error('pais_id') {{ $message }} @else El país es obligatorio. @enderror</small>
        </div>

        <!-- reCAPTCHA -->
        <div class="mb-4 mt-2 d-flex flex-column align-items-start" id="container_recaptcha">
          <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
          @if($errors->has('g-recaptcha-response')) 
            <div class="text-danger form-label small mt-1" style="color:var(--err)">{{ $errors->first('g-recaptcha-response') }}</div> 
          @endif
        </div>
      @endguest

      <div class="row-btns">
        <button type="submit" class="btn btn-primary py-3" id="submitBtn">
          <span class="spinner"></span>
          <span class="btn-text btnGuardarText">Enviar petición</span>
        </button>
      </div>

    </form>

  </div>
</main>
@endsection
