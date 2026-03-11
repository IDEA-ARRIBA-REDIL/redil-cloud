@php
$containerFooter = (isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme d-none d-md-block">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body">
        © <script>document.write(new Date().getFullYear())</script>, made with ❤️ by <a href="{{ (!empty(config('variables.creatorUrl')) ? config('variables.creatorUrl') : '') }}" target="_blank" class="footer-link">{{ (!empty(config('variables.creatorName')) ? config('variables.creatorName') : '') }}</a>
      </div>
      <div class="d-none d-lg-inline-block">
        <a href="{{ config('variables.licenseUrl') ? config('variables.licenseUrl') : '#' }}" class="footer-link me-4" target="_blank">License</a>
        <a href="{{ config('variables.moreThemes') ? config('variables.moreThemes') : '#' }}" target="_blank" class="footer-link me-4">More Themes</a>
        <a href="{{ config('variables.documentation') ? config('variables.documentation').'/laravel-introduction.html' : '#' }}" target="_blank" class="footer-link me-4">Documentation</a>
        <a href="{{ config('variables.support') ? config('variables.support') : '#' }}" target="_blank" class="footer-link d-none d-sm-inline-block">Support</a>
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
