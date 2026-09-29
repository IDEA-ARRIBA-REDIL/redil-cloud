<!DOCTYPE html>
@php
    use App\Helpers\Helpers;
    $configData = Helpers::appClasses();
    $configData['style'] = 'light';
    $configData['styleOptVal'] = 'light';
@endphp

<html lang="es" class="{{ $configData['style'] }}-style customizer-hide"
    dir="{{ $configData['textDirection'] }}" data-theme="{{ $configData['theme'] }}"
    data-assets-path="{{ asset('/assets') . '/' }}" data-base-url="{{ url('/') }}" data-framework="laravel"
    data-template="{{ $configData['layout'] . '-menu-' . $configData['themeOpt'] . '-' . $configData['styleOpt'] }}"
    data-style="{{ $configData['styleOptVal'] }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>REDIL Cloud - Panel Central</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    @vite([
      'resources/assets/vendor/fonts/tabler-icons.scss',
      'resources/assets/vendor/fonts/fontawesome.scss',
      'resources/assets/vendor/fonts/flag-icons.scss',
      'resources/assets/vendor/libs/node-waves/node-waves.scss',
    ])

    <!-- Core CSS -->
    @vite([
        'resources/assets/vendor/scss'.$configData['rtlSupport'].'/core' .($configData['style'] !== 'light' ? '-' . $configData['style'] : '') .'.scss',
        'resources/assets/vendor/scss'.$configData['rtlSupport'].'/' .$configData['theme'] .($configData['style'] !== 'light' ? '-' . $configData['style'] : '') .'.scss',
        'resources/assets/css/demo.css',
        'resources/assets/css/redil.css'
    ])

    <!-- Vendor Styles -->
    @vite([
      'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
      'resources/assets/vendor/libs/typeahead-js/typeahead.scss'
    ])

    @livewireStyles
    @include('layouts.sections.central-styles')
</head>
<body class="redil-central">
    <a class="redil-skip" href="#central-contenido">Saltar al contenido</a>
    <header class="redil-topbar">
        <a class="redil-brand" href="{{ url(auth('admin')->check() ? '/admin/dashboard' : '/admin/login') }}" aria-label="REDIL, administración central">
            <span class="redil-brand-mark" aria-hidden="true">R<span>•</span></span>
            <span><strong>REDIL</strong><small>PASTOREO INTELIGENTE</small></span>
        </a>
        @auth('admin')
            <nav class="redil-nav" aria-label="Administración central">
                @foreach(['dashboard' => 'Iglesias', 'planes' => 'Planes', 'invitaciones' => 'Invitaciones', 'super-admins' => 'Administradores', 'login-predeterminado' => 'Diseño de acceso'] as $ruta => $titulo)
                    <a href="{{ url('/admin/'.$ruta) }}" @if(request()->is('admin/'.$ruta) || ($ruta === 'dashboard' && request()->is('admin/tenants/*'))) aria-current="page" @endif>{{ $titulo }}</a>
                @endforeach
            </nav>
            <form method="POST" action="{{ url('/admin/logout') }}">@csrf<button type="submit" class="btn btn-sm btn-label-secondary">Salir</button></form>
        @else
            <span class="redil-eyebrow">ADMINISTRACIÓN CENTRAL</span>
        @endauth
    </header>

    <!-- Content starts here -->
    <main id="central-contenido" tabindex="-1">{{ $slot }}</main>
    <footer class="redil-footer">REDIL Cloud <span>·</span> Tecnología al servicio de tu comunidad.</footer>
    <!-- Content ends here -->

    <!-- Core JS -->
    @vite([
      'resources/assets/vendor/libs/jquery/jquery.js',
      'resources/assets/vendor/libs/popper/popper.js',
      'resources/assets/vendor/js/bootstrap.js',
      'resources/assets/vendor/libs/node-waves/node-waves.js',
      'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
      'resources/assets/vendor/libs/hammer/hammer.js',
      'resources/assets/vendor/libs/typeahead-js/typeahead.js',
      'resources/assets/vendor/js/menu.js',
      'resources/assets/js/main.js'
    ])

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireScripts
</body>
</html>
