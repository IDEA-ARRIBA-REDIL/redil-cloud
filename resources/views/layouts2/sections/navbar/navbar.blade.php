@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use App\Models\User;
    use App\Models\Configuracion;
    $containerNav = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';
    $navbarDetached = $navbarDetached ?? '';
    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $roles =  auth()->user()->roles()->orderByPivot('activo', 'desc')->get();
    if ($rolActivo != '') {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $usuario = App\Models\User::find($rolActivo->pivot->model_id);
    }

@endphp

<!-- Navbar -->
@if (isset($rolActivo->id))
    @if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
        <nav  class="layout-navbar pe-5 ps-0 {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
            id="layout-navbar" style="box-shadow: none !important; background-color: rgba(0, 0, 0, 0) !important">
    @endif
    @if (isset($navbarDetached) && $navbarDetached == '')
        <nav  class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
            <div class="{{ $containerNav }}">
    @endif

    <!--  Brand demo (display only for navbar-full and hide on below xl) -->
    @if (isset($navbarFull))
        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
            <a href="{{ url('/') }}" class="app-brand-link">
                <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20])</span>
                <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }} </span>
            </a>
            @if (isset($menuHorizontal))
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                    <i class="ti ti-x ti-md align-middle"></i>
                </a>
            @endif
        </div>
    @endif

    <!-- ! Not required for layout-without-menu -->
        <div
            class="d-none layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="ti ti-menu-2 ti-md"></i>
            </a>
        </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <!-- Estado (Publicaciones Recientes) -->
        @livewire('navbar-posts-status')
        <!--/ Estado -->

        @if(Route::is('dashboard'))
        <div class="">
            <p class="text-black fs-5 my-auto">Hola, <b>{{ $usuario->nombre(2) }}</b> </p>
        </div>
        @endif

        @if (!isset($menuHorizontal))
        @endif

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            @if (isset($menuHorizontal))
                <!-- Search -->
                <li class="nav-item navbar-search-wrapper">
                    <a class="nav-link btn btn-text-secondary btn-icon rounded-pill search-toggler"
                        href="javascript:void(0);">
                        <i class="ti ti-search ti-md"></i>
                    </a>
                </li>
                <!-- /Search -->
            @endif

            

            <!-- Notification -->
            <li class="nav-item d-nonex dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
                    href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                    aria-expanded="false">
                    <span class="position-relative">
                        <i class="ti ti-bell ti-md"></i>
                        <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                    </span>
                </a>

            </li>
            <!--/ Notification -->

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    @if($usuario->foto == "default-m.png" || $usuario->foto == "default-f.png")
                      <span class="avatar-initial h-auto rounded-circle border border-3 border-white bg-info"> {{ $usuario->inicialesNombre() }} </span>
                    @else
                      <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$usuario->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$usuario->foto }}" alt="{{ $usuario->foto }}" class="avatar-initial rounded-circle border border-3 border-white bg-info">
                    @endif
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                         @foreach ($roles as $rol)
                            {{-- Solo mostramos la opción de cambiar para los roles inactivos --}}
                            @if ($rol->pivot->activo)
                                {{-- Estilo para el rol que ya está activo (no es clickeable) --}}
                                <a class="dropdown-item mt-0 disabled" href="#">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-2">
                                            <div class="avatar me-2 avatar-online">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    <i class="ti {{ $rol->icono }}"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">{{ $rol->name }}</h6>
                                            <small class="text-muted">Activo</small>
                                        </div>
                                    </div>
                                </a>
                            @else
                                {{-- Formulario para cambiar a un rol inactivo --}}
                                <form action="{{ route('user.roles.switch', $rol->id) }}" method="POST" class="dropdown-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item mt-0">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="avatar me-2 avatar-offline">
                                                    <span class="avatar-initial rounded-circle bg-label-secondary">
                                                        <i class="ti {{ $rol->icono }}"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ $rol->name }}</h6>
                                                <small class="text-muted">Cambiar a este rol</small>
                                            </div>
                                        </div>
                                    </button>
                                </form>
                            @endif
                        @endforeach
                    </li>
                    <li>
                        <div class="dropdown-divider my-1 mx-n2"></div>
                    </li>
                    <li>

                      @can('verPerfilUsuarioPolitica', [auth()->user(), 'principal'])
                        <a class="dropdown-item" href=" {{ route('usuario.perfil', auth()->user()) }}">
                          <i class="ti ti-user me-3 ti-md"></i><span class="align-middle">Mi perfil</span>
                        </a>
                      @endcan
                    </li>

                    <li>
                        <div class="dropdown-divider my-1 mx-n2"></div>
                    </li>
                    @if (Auth::check())
                        <li>
                            <div class="d-grid px-2 pt-2 pb-1">
                                <a class="btn btn-sm btn-primary d-flex rounded-pill" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <small class="align-middle">Cerrar sesión</small>
                                    <i class="ti ti-logout ms-2 ti-14px"></i>
                                </a>
                            </div>
                        </li>
                        <form method="POST" id="logout-form" action="{{ route('logout') }}">
                            @csrf
                        </form>
                    @else
                        <li>
                            <div class="d-grid px-2 pt-2 pb-1">
                                <a class="btn btn-sm btn-danger d-flex"
                                    href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                                    <small class="align-middle">Login</small>
                                    <i class="ti ti-login ms-2 ti-14px"></i>
                                </a>
                            </div>
                        </li>
                    @endif
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>

    <!-- Search Small Screens -->
    <div class="navbar-search-wrapper search-input-wrapper {{ isset($menuHorizontal) ? $containerNav : '' }} d-none">
        <input type="text"
            class="form-control search-input {{ isset($menuHorizontal) ? '' : $containerNav }} border-0"
            placeholder="Search..." aria-label="Search...">
        <i class="ti ti-x search-toggler cursor-pointer"></i>
    </div>
    <!--/ Search Small Screens -->
    @if (isset($navbarDetached) && $navbarDetached == '')
        </div>
    @endif
    </nav>
    <!-- / Navbar -->
@endif
