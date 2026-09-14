@php
use App\Models\Configuracion;

$containerFooter = (isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';

$configuracion = $configuracion ?? Configuracion::find(1);

$usarMarcaBlanca = $configuracion && ($configuracion->marca_blanca || !empty($configuracion->nombre_creador));

$nombreCreador = ($usarMarcaBlanca && !empty($configuracion->nombre_creador))
    ? $configuracion->nombre_creador
    : (!empty(config('variables.creatorName')) ? config('variables.creatorName') : 'IDEARRIBA');

$urlCreador = ($usarMarcaBlanca && !empty($configuracion->url_creador))
    ? $configuracion->url_creador
    : (!empty(config('variables.creatorUrl')) ? config('variables.creatorUrl') : '');

$versionApp = $configuracion->version_app ?? ($configuracion->version ? $configuracion->version : config('variables.templateVersion'));
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme d-none d-md-block">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body">
        © <script>document.write(new Date().getFullYear())</script>, hecho con el corazón ❤️ por
        @if(!empty($urlCreador))
          <a href="{{ $urlCreador }}" target="_blank" class="footer-link">{{ $nombreCreador }}</a>
        @else
          <span class="footer-link">{{ $nombreCreador }}</span>
        @endif
      </div>
      <div>
        @if(!empty($versionApp))
          <span class="footer-link text-muted">v{{ ltrim($versionApp, 'v') }}</span>
        @endif
      </div>
    </div>
  </div>
</footer>

<footer class="content-footer footer d-block d-md-none p-0" style="margin-top: 80px;">
  <div class="mobile-nav bg-menu-theme">
    <a href="{{ route('dashboard') }}" class="mobile-nav-item d-flex flex-column">
      <i class="ti ti-smart-home"></i>
      <span>Inicio</span>
    </a>

    <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBirthday" class="mobile-nav-item btn-primary mobile-nav-center">
      <i class="ti ti-cake"></i>
    </a>

    <a href="javascript:void(0);" class="mobile-nav-item d-flex flex-column layout-menu-toggle">
      <i class="ti ti-menu-2"></i>
      <span>Menú</span>
    </a>
  </div>
</footer>
<!--/ Footer-->
