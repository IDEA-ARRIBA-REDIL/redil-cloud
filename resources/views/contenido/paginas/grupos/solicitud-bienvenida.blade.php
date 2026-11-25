@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Unirme a un grupo')

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

    .hero{position:relative;z-index:2;text-align:center;padding:18px 24px 8px}
    .eyebrow{font-size:11px;letter-spacing:.22em;color:var(--b2);font-weight:700;text-transform:uppercase;margin-bottom:14px}
    .hero h1{font-family:var(--cd);font-weight:900;text-transform:uppercase;font-size:clamp(30px,5.4vw,46px);line-height:1.05;letter-spacing:-.01em;color:var(--ink)}
    .hero h1 .sc{font-family:var(--sc);font-weight:400;text-transform:none;color:var(--b2);font-size:1.4em;display:inline-block;margin:0 .08em;vertical-align:-.08em}
    .hero p{max-width:500px;margin:18px auto 0;color:var(--ink-mute);font-size:15.5px;line-height:1.65}

    main{position:relative;z-index:2;display:flex;justify-content:center;padding:36px 20px 80px}
    .main-card{width:100%;max-width:560px;background:var(--card);border:1px solid var(--border-soft);border-radius:var(--radius);padding:clamp(24px,4vw,44px);box-shadow:0 1px 2px rgba(4,4,7,.03),0 18px 50px -28px rgba(4,4,7,.18)}

    .welcome-badge{display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:14px;background:var(--bsoft);color:var(--b2);margin-bottom:20px}
    .welcome-badge i{font-size:28px}

    .question-title{font-family:var(--cd);font-weight:800;font-size:22px;line-height:1.25;color:var(--ink);margin-bottom:10px}
    .question-desc{font-size:14.5px;line-height:1.6;color:var(--ink-mute);margin-bottom:28px}

    .action-stack{display:flex;flex-direction:column;gap:12px}
    .btn-proto{width:100%;padding:15px 20px;border-radius:10px;font-family:var(--sa);font-weight:700;font-size:14.5px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:all .25s;text-decoration:none}
    .btn-proto-primary{background:var(--b) !important;color:#fff !important;border:none !important;box-shadow:none !important}
    .btn-proto-primary:hover{background:var(--b2) !important;color:#fff !important}
    .btn-proto-outline{background:var(--field) !important;color:var(--ink) !important;border:1px solid var(--border) !important}
    .btn-proto-outline:hover{background:var(--field-focus) !important;border-color:var(--bline) !important;color:var(--b2) !important}

    .account-note{text-align:center;margin-top:22px;padding-top:18px;border-top:1px solid var(--border-soft);font-size:13px;color:var(--ink-mute)}

    @media(max-width:520px){
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
  <script type="module">
    const btnTengoCuenta = document.getElementById('btnTengoCuenta');
    const btnNoTengoCuenta = document.getElementById('btnNoTengoCuenta');

    if (btnTengoCuenta) {
      btnTengoCuenta.addEventListener('click', function () {
        Swal.fire({
          title: 'Te llevaremos a iniciar sesión',
          html: `
            <p class="mb-0" style="color: var(--ink-mute); font-size: 0.95rem; line-height: 1.55;">
              Serás redirigido a la pantalla de inicio de sesión para acceder con tu cuenta y continuar con tu solicitud de ingreso al grupo.
            </p>
          `,
          icon: 'info',
          showCancelButton: true,
          confirmButtonText: 'Ir a iniciar sesión',
          cancelButtonText: 'Cancelar',
          customClass: {
            actions: 'swal2-actions',
            confirmButton: 'swal-btn-confirm',
            cancelButton: 'swal-btn-cancel'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "{{ route('login') }}";
          }
        });
      });
    }

    if (btnNoTengoCuenta && btnNoTengoCuenta.dataset.sinFormulario === '1') {
      btnNoTengoCuenta.addEventListener('click', function (e) {
        e.preventDefault();
        Swal.fire({
          title: 'Sin formulario externo disponible',
          text: 'No se encontró un formulario de inscripción exterior configurado en este momento.',
          icon: 'warning',
          confirmButtonText: 'Entendido',
          customClass: {
            actions: 'swal2-actions',
            confirmButton: 'swal-btn-confirm'
          },
          buttonsStyling: false
        });
      });
    }
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
    <a class="back" href="{{ url()->previous() }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
      Volver
    </a>
  @endauth

  <div class="logo-mini">
    <span class="app-brand-logo demo p-0">
      @include('_partials.logo_iglesia', [
          'width' => '120px',
          'logo_negro' => true
      ])
    </span>
  </div>
</header>

<section class="hero">
  <div class="eyebrow">Comunidad y Grupos</div>
  <h1>Queremos caminar <span class="sc">juntos</span><br>en comunidad</h1>
  <p class="text-black">Nos alegra que quieras pertenecer a uno de nuestros grupos. Es necesario tener una cuenta para enviar la solicitud.</p>
</section>

<main>
  <div class="card main-card text-center">
    <div class="mx-auto welcome-badge">
      <i class="ti ti-users-group"></i>
    </div>

    <h2 class="question-title">¿Tienes una cuenta en nuestra plataforma?</h2>
    <p class="question-desc">
      Al tener una cuenta te ayudaremos a llegar a tu grupo de forma más efectiva.
    </p>

    <div class="action-stack">
      <button type="button" id="btnTengoCuenta" class="btn-proto btn-proto-primary">
        <i class="ti ti-login"></i>
        <span>Sí tengo una cuenta</span>
      </button>

      <a
        id="btnNoTengoCuenta"
        href="{{ $formularioExterno ? route('usuario.nuevoExterior', ['formulario' => $formularioExterno->id]) : '#' }}"
        data-sin-formulario="{{ $formularioExterno ? '0' : '1' }}"
        class="btn-proto btn-proto-outline"
      >
        <i class="ti ti-user-plus"></i>
        <span>No tengo una cuenta</span>
      </a>
    </div>

    <div class="account-note">
      Si aún no tienes cuenta, te guiaremos para crear tu registro y luego podrás completar tu solicitud.
    </div>
  </div>
</main>
@endsection
