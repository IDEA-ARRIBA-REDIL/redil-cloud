
@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('vendor-style')
<!-- Vendor -->
@vite(['resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])
<style>

</style>
@endsection

@section('page-style')
<!-- Page -->
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js',
'resources/assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js',
'resources/assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js',
])
@endsection

@section('page-script')
@vite(['resources/assets/js/pages-auth.js'])
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover bg-login-left">
  <div class="authentication-inner row">


    <!-- Login -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-12 p-6 ">
      <div class="w-px-400 mx-auto mt-2 mt-lg-3 pt-5">

        <!-- Logo -->
        <div class="app-brand demo d-flex mb-3">
          <a href="{{url('/')}}" class="app-brand-link gap-0 d-flex align-items-center">
            <img style="width:90%" class="app-brand-logo me-2" src="{{ config('variables.logoApp') }}">
            
          </a>
        </div>
        <div>
          <span class=" menu-text fw-bold h1 titulo-login m-0">{{config('variables.templateName')}}</span>
        </div>
        <!-- /Logo -->

        <h3 class=" mb-1 d-none">{{config('variables.templateName')}}</h3>
        <p class="text-muted  fw-light p-0 titulo-descripcion" >{{config('variables.templateDescriptionLogin')}}</p>

        <form id="" class="mb-3" action="{{ route('login') }}" method="POST">
          @csrf

          @include('layouts.status-msn')

          <div class="mb-2">
            <label for="email" class="form-label d-none">Email or Username</label>
            <input  type="text" class="form-control input-login" id="email" name="email" value="{{ old('email',$emailDefault) }}" placeholder="Email" autofocus>
          </div>
          <div class="mb-2 form-password-toggle">
            <div class="d-flex justify-content-between">
              <label class="form-label d-none" for="password">Password</label>
            </div>
            <div class="input-group input-group-merge">
              <input type="password" id="password" class=" input-login form-control" name="password" placeholder="Contraseña" aria-describedby="password" />
              <span style=""  class="input-group-text input-login  cursor-pointer"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          <div class="">

          <div class="mt-4">
            <a href="{{ route('password.request') }}">
              <p class="mb-1 text-muted p-0 titulo-descripcion"> ¿Olvidaste tu contraseña? </p>
            </a>
            </div>
            <div class="form-check d-none">
              <input class="form-check-input" type="checkbox" id="remember-me">
              <label class="form-check-label  text-muted titulo-descripcion " for="remember-me">
                Recordarme
              </label>
            </div>
          </div>
          <div class="mt-5">
            <button class="btn rounded-pill btn-primary d-grid w-100 titulo-descripcion">
              Ingresar
            </button>
          </div>
        </form>

        <div id="container-redes" class="container mt-4">
          <div class="divider m-1">
            <div class="divider-text text-muted titulo-descripcion">Siguenos en redes</div>
          </div>

          <div class="d-flex justify-content-center">

            <a href="javascript:;" class="btn btn-icon btn-label-facebook mx-1">
              <i class="tf-icons fa-brands fa-facebook-f fs-5"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon btn-label-instagram mx-1">
              <i class="tf-icons fa-brands fa-instagram fs-5"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon btn-label-youtube mx-1">
              <i class="tf-icons fa-brands fa-youtube fs-5"></i>
            </a>

          </div>
        </div>

        <div id="container-footer" class="mt-10">
          <p id="footer" class="text-algin-start">
            <span class="titulo-descripcion">¿No tienes cuenta ?</span>
            @foreach($formularios as $formulario)
            <a href="{{ route('usuario.nuevoExterior', $formulario) }}">
              <span>{{ $formulario->label }} </span>
            </a>
            @endforeach
          </p>
        </div>

      </div>
    </div>
    <!-- /Login -->

     <!-- /Left Text -->
     <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center"  style="background-image: url({{ asset('assets/img/illustrations/bg-redil2.jpg') }}); background-size: cover;">

      </div>
    </div>
    <!-- /Left Text -->
  </div>
</div>

{{-- ===== BOTÓN FLOTANTE INSTALAR APP (Solo Móvil) ===== --}}
<style>
@media (min-width: 900px) {
    #pwa-install-bar { display: none !important; }
}
</style>
<div id="pwa-install-bar" style="
    display: none;
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    width: calc(100% - 48px);
    max-width: 360px;
">
    <button id="btn-pwa-instalar"
        onclick="accionPWA()"
        style="
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 14px 24px;
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 8px 25px rgba(102,126,234,0.5);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.3px;
        ">
        <span style="font-size: 20px;">📲</span>
        <span>Instalar App en tu celular</span>
    </button>
</div>

{{-- Modal instrucciones iOS --}}
<div id="modal-ios-pwa" style="
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 99999;
    align-items: flex-end;
    justify-content: center;
    backdrop-filter: blur(4px);
">
    <div style="
        background: white;
        border-radius: 28px 28px 0 0;
        padding: 28px 24px 40px;
        width: 100%;
        max-width: 480px;
        animation: slideUp 0.3s ease;
    ">
        {{-- Handle --}}
        <div style="width:40px;height:4px;background:#e0e0e0;border-radius:4px;margin:0 auto 20px;"></div>

        <h5 style="font-weight:700;font-size:18px;margin:0 0 4px;">Instala la app 🚀</h5>
        <p style="color:#888;font-size:13px;margin:0 0 24px;">Sigue estos pasos en Safari para agregar la app a tu pantalla de inicio.</p>

        {{-- Paso 1 --}}
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:20px;">
            <div style="
                min-width:44px;height:44px;
                background:linear-gradient(135deg,#667eea,#764ba2);
                border-radius:12px;
                display:flex;align-items:center;justify-content:center;
                font-size:22px;
            ">⬆️</div>
            <div>
                <p style="font-weight:600;margin:0 0 2px;font-size:14px;">Paso 1: Toca el botón Compartir</p>
                <p style="color:#888;font-size:13px;margin:0;">Es el ícono con una flecha hacia arriba en la barra inferior de Safari.</p>
            </div>
        </div>

        {{-- Paso 2 --}}
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:20px;">
            <div style="
                min-width:44px;height:44px;
                background:linear-gradient(135deg,#f093fb,#f5576c);
                border-radius:12px;
                display:flex;align-items:center;justify-content:center;
                font-size:22px;
            ">➕</div>
            <div>
                <p style="font-weight:600;margin:0 0 2px;font-size:14px;">Paso 2: "Agregar a pantalla de inicio"</p>
                <p style="color:#888;font-size:13px;margin:0;">Desplázate en el menú que aparece y toca esa opción.</p>
            </div>
        </div>

        {{-- Paso 3 --}}
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:28px;">
            <div style="
                min-width:44px;height:44px;
                background:linear-gradient(135deg,#4facfe,#00f2fe);
                border-radius:12px;
                display:flex;align-items:center;justify-content:center;
                font-size:22px;
            ">✅</div>
            <div>
                <p style="font-weight:600;margin:0 0 2px;font-size:14px;">Paso 3: Toca "Agregar"</p>
                <p style="color:#888;font-size:13px;margin:0;">Confirma el nombre y toca Agregar. ¡Listo!</p>
            </div>
        </div>

        <button onclick="cerrarModalIos()" style="
            width:100%;
            background:#f0f0f0;
            border:none;
            border-radius:50px;
            padding:14px;
            font-size:15px;
            font-weight:600;
            color:#333;
            cursor:pointer;
        ">Entendido</button>
    </div>
</div>

<style>
@keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
</style>

<script>
    // Variables globales
    let deferredPrompt = null;
    const esIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const esSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    const esPWA = window.matchMedia('(display-mode: standalone)').matches
                  || window.navigator.standalone === true;
    const barraInstalar = document.getElementById('pwa-install-bar');

    // Mostrar botón solo si no está instalada como PWA
    function mostrarBotonInstalar() {
        if (esPWA) return; // Ya está instalada, no mostrar
        if (esIos && esSafari) {
            // iOS Safari: mostramos siempre (no hay evento beforeinstallprompt)
            barraInstalar.style.setProperty('display', 'block');
        }
        // Android: se mostrará cuando se capture el evento beforeinstallprompt
    }

    // Capturar evento de Android
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (!esPWA) {
            barraInstalar.style.setProperty('display', 'block');
        }
    });

    // Acción al presionar el botón
    async function accionPWA() {
        if (esIos && esSafari) {
            // iOS: mostrar modal de instrucciones
            const modal = document.getElementById('modal-ios-pwa');
            modal.style.display = 'flex';
            return;
        }

        // Android: disparar el install prompt
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;

            // Si aceptó instalar, pedir permisos de notificación también
            if (outcome === 'accepted' && 'Notification' in window && Notification.permission === 'default') {
                setTimeout(async () => {
                    await Notification.requestPermission();
                }, 1500);
            }

            // Ocultar el botón
            barraInstalar.style.setProperty('display', 'none');
        } else {
            // Si el prompt ya no está (fue instalado en otra sesión)
            // Intentar pedir solo permisos de notificación
            if ('Notification' in window && Notification.permission === 'default') {
                await Notification.requestPermission();
            }
        }
    }

    function cerrarModalIos() {
        document.getElementById('modal-ios-pwa').style.display = 'none';
    }

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', mostrarBotonInstalar);

    // Ocultar botón si la PWA se instala en esta sesión
    window.addEventListener('appinstalled', () => {
        barraInstalar.style.setProperty('display', 'none');
    });
</script>

@endsection
