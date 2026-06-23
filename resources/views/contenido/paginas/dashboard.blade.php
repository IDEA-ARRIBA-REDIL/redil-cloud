@php
    $configData = Helper::appClasses();
    use App\Models\Actividad;
    use App\Models\TagGeneral;

    $iglesiaData = \App\Models\Iglesia::first();
    $mostrarAlertaLicencia = false;
    $diasRestantesLicencia = 0;

    if ($iglesiaData && $iglesiaData->fecha_vencimiento_licencia) {
        $fechaVencimiento = \Carbon\Carbon::parse($iglesiaData->fecha_vencimiento_licencia)->startOfDay();
        $diasRestantesLicencia = now()->startOfDay()->diffInDays($fechaVencimiento, false);

        // Si faltan 30 días o menos (o ya venció) y no se ha mostrado hoy
        if ($diasRestantesLicencia <= 30) {
            $keyAlerta = 'alerta_licencia_' . now()->format('Y-m-d');
            if (!session()->has($keyAlerta)) {
                $mostrarAlertaLicencia = true;
                session()->put($keyAlerta, true);
            }
        }
    }
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Inicio')


@section('page-style')
    @vite([
        'resources/assets/vendor/libs/swiper/swiper.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
    ])
    <style>
        /* Estilos para el header y navbar solo para dashboard  */
            .dashboard-header {
                margin-left: -1.5rem;
                margin-right: -1.5rem;
                padding: 100px 1.5rem 80px 1.5rem;
                margin-top: -6.7rem;
            }

            /* Estilos para el navbar SOLO cuando está en el tope de la página */
            .layout-navbar.navbar-at-top {
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                box-shadow: none !important;
                border: none !important;
                background-color: transparent !important;
            }
            /* Eapps Instagram Feed - Ajustes  */

            .eapps-instagram-feed-title
 {
    font-size: 24px;
    font-weight: 700 !important;
    text-align: start !important;
    line-height: 32px;
    padding: 24px 10px;
    color: #000;
    font-family: 'Poppins';
}

#container-instagram{
    background: #fff;
    margin-top: 10px;
    padding: 20px;
    width: 97%;
    margin-left: 12px;
    border-radius: 10px;
}
.eapps-instagram-feed-posts-item-template-classic {
    border: 2px solid rgb(255 255 255);
}

            #rueda-vida-card {
                margin-top: -140px !important;
            }

            /* El degradado/difuminado superior también solo debería quitarse en el tope */
            .layout-navbar-fixed .layout-page.navbar-at-top:before {
                background: none !important;
                backdrop-filter: none !important;
            }


            @media (min-width: 1200px) {
                .dashboard-header {
                    padding-left: 2rem;
                    padding-right: 2rem;
                }
            }

            @media (min-width: 1400px) {
                .container-xxl, .container-xl, .container-lg, .container-md, .container-sm, .container {
                    max-width: 100% !important;
                }

                .ajuste{
                    padding-left: 10rem !important;
                    padding-right: 10rem  !important;
                }
            }

            @media (max-width: 767px) {
                .dashboard-header {
                    padding: 100px 1.5rem 50px 1.5rem !important;
                }
                #rueda-vida-card {
                    margin-top: 0px !important;
                }
            }
        /* fin de estilos para el header y navbar */

        /* Estilos para los botones de acción */
            .action-buttons-container {
                margin-top: -90px;
                display: flex;
                gap: 15px;
                padding: 0 10px 20px 10px;
                overflow-x: auto;
                scrollbar-width: none;
            }

            .action-buttons-container::-webkit-scrollbar {
                display: none;
            }

            .action-card {
                min-width: 110px;
                height: 110px;
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                transition: all 0.3s ease;
                cursor: pointer;
                border: none;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 5px;
            }

            .action-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            }

            .action-icon-container {
                width: 50px;
                height: 50px;
                margin-bottom: 5px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .action-icon-container img, .action-icon-container span {
                font-size: 35px;
            }

            .action-title {
                font-size: 0.9rem;
                font-weight: 600;
                color: #444;
                margin: 0;
                text-align: center;
            }

            @media (max-width: 767px) {
                .action-card {
                    background: transparent !important;
                    box-shadow: none !important;
                    min-width: 110px !important;
                    height: auto !important;
                    padding: 0 !important;
                }

                .action-buttons-container {
                    margin-top: -60px;
                }

                .action-icon-container {
                    width: 70px !important;
                    height: 70px !important;
                    background: #fff !important;
                    border-radius: 50% !important;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
                    margin-bottom: 12px !important;
                    border: 1px solid #eee;
                }

                .action-icon-container img {
                    width: 55px !important;
                    height: 55px !important;
                }

                .action-title {
                    font-size: 1rem !important;
                    color: #222 !important;
                }
            }
        /* fin de estilos para los botones de acción */

       #swiper-temas .swiper-pagination{
            position:unset !important;

       }

        /* Estilos para las flechas de navegación de Swiper */

        .btn-banner-mas :hover{

        }
        .swiper-button-next-banners,
        .swiper-button-prev-banners {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            transition: all 0.3s ease;
        }

        .swiper-button-next-banners:after,
        .swiper-button-prev-banners:after {
            font-size: 18px !important;
            font-weight: bold;
        }

        .swiper-button-next-banners:hover,
        .swiper-button-prev-banners:hover {
            background-color: rgba(255, 255, 255, 0.4);
            transform: scale(1.1);
        }
    </style>
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/swiper/swiper.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
    ])
@endsection

@section('page-script')
    {{-- Notificaciones: activar / blocked --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navbar = document.querySelector('.layout-navbar');
            const layoutPage = document.querySelector('.layout-page');

            function handleScroll() {
                if (window.scrollY > 10) {
                    navbar.classList.remove('navbar-at-top');
                    if(layoutPage) layoutPage.classList.remove('navbar-at-top');
                } else {
                    navbar.classList.add('navbar-at-top');
                    if(layoutPage) layoutPage.classList.add('navbar-at-top');
                }
            }

            window.addEventListener('scroll', handleScroll);
            handleScroll(); // Ejecutar al cargar para setear estado inicial
        });

        (function () {
            const esMobil = window.innerWidth < 900;
            if (!esMobil) return;

            const permisoActual = ('Notification' in window) ? Notification.permission : 'granted';

            if (permisoActual === 'default') {
                const banner = document.getElementById('banner-notif-permiso');
                if (banner) banner.style.display = 'block';
            } else if (permisoActual === 'denied') {
                const bannerBloqueado = document.getElementById('banner-notif-bloqueado');
                if (bannerBloqueado) bannerBloqueado.style.display = 'block';
            }
        })();

        async function pedirPermisoNotificaciones() {
            if (!('Notification' in window)) return;
            await Notification.requestPermission();
            cerrarBannerNotif();
        }

        function cerrarBannerNotif() {
            const banner = document.getElementById('banner-notif-permiso');
            if (banner) {
                banner.style.transition = 'opacity 0.3s ease';
                banner.style.opacity = '0';
                setTimeout(() => banner.style.display = 'none', 300);
            }
        }

        function cerrarBannerBloqueado() {
            const banner = document.getElementById('banner-notif-bloqueado');
            if (banner) {
                banner.style.transition = 'opacity 0.3s ease';
                banner.style.opacity = '0';
                setTimeout(() => banner.style.display = 'none', 300);
            }
        }
    </script>

    <script type="module">
        const swiperContainer = document.querySelector('#swiper-with-pagination-cards');
        const swiper = new Swiper(swiperContainer, {
            // En móviles muestra 1.2 cartas, en tablets 2.4 y en desktop 3.5
            slidesPerView: 1.2,
            spaceBetween: 20,
            centeredSlides: false, // Importante para que la primera empiece a la izquierda
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                // Cuando la pantalla es >= 768px
                768: {
                    slidesPerView: 2.4,
                    spaceBetween: 25,
                },
                // Cuando la pantalla es >= 1200px (Desktop)
                1200: {
                    slidesPerView: 3.5, // Aquí es donde se ve la 4ta card asomada
                    spaceBetween: 10,
                },
            },
        });

        const swiperBannersContainer = document.querySelector('#swiper-banners');
        if (swiperBannersContainer) {
            const swiperBanners = new Swiper(swiperBannersContainer, {
                slidesPerView: 1,
                spaceBetween: 10,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".swiper-button-next-banners",
                    prevEl: ".swiper-button-prev-banners",
                },
                pagination: {
                    el: ".swiper-pagination-banners",
                    clickable: true
                },
            });
        }

        window.initTemasSwiper = function() {
            const temasContainer = document.querySelector('#swiper-temas');
            if (temasContainer && typeof Swiper !== 'undefined') {
                console.log('Iniciando carrusel de temas...');
                const temasSwiper = new Swiper(temasContainer, {
                    slidesPerView: 1.2,
                    spaceBetween: 15,
                    initialSlide: 0,
                    centeredSlides: false,
                    observer: true,
                    observeParents: true,
                    pagination: {
                        el: "#swiper-temas .swiper-pagination",
                        clickable: true,
                    },
                    breakpoints: {
                        768: {
                            slidesPerView: 2.2,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 3.2,
                            spaceBetween: 20,
                        },
                        1200: {
                            slidesPerView: 4,
                            spaceBetween: 15,
                        },
                    },
                });
                console.log('Carrusel de temas inicializado:', temasSwiper);
            } else {
                if (!temasContainer) console.warn('No se encontró el contenedor #swiper-temas');
                if (typeof Swiper === 'undefined') console.error('Swiper no está definido en el momento de la carga');
            }
        };

        // Forzar un pequeño delay para asegurar la carga de assets
        setTimeout(initTemasSwiper, 500);
    </script>

    @if($mostrarAlertaLicencia)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let htmlContent = '';
            @if($diasRestantesLicencia > 0)
                htmlContent += 'Tu licencia de software REDIL está próxima a vencer en <strong>{{ intval($diasRestantesLicencia) }} días</strong> ({{ \Carbon\Carbon::parse($iglesiaData->fecha_vencimiento_licencia)->format("d/m/Y") }}).<br><br>';
            @elseif($diasRestantesLicencia == 0)
                htmlContent += 'Tu licencia de software REDIL vence <strong>hoy</strong>.<br><br>';
            @else
                htmlContent += 'Tu licencia de software REDIL ha <strong>vencido</strong> hace {{ abs(intval($diasRestantesLicencia)) }} días.<br><br>';
            @endif
            htmlContent += '{{ $iglesiaData->mensaje_vencimiento_licencia ?? "Por favor, comunícate con soporte o renueva tu plan para evitar interrupciones en el servicio." }}';

            Swal.fire({
                title: '¡Aviso importante!',
                html: htmlContent,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-pill'
                },
                buttonsStyling: false
            });
        });
    </script>
    @endif
@endsection



@section('content')



    <!-- Offcanvas de Cumpleaños -->
    <div style="" class="offcanvas offcanvas-end theme-bg-secondary" tabindex="-1" id="offcanvasBirthday"
        aria-labelledby="offcanvasBirthdayLabel">
        <div class="offcanvas-header d-flex align-items-center justify-content-between px-2">

            <button type="button" class="btn text-white" data-bs-dismiss="offcanvas">
                <i class="ti ti-x"></i>
            </button>
            <h5 id="offcanvasBirthdayLabel" class="offcanvas-title text-white fw-semibold">
                <i class="ti ti-cake me-2 ti-xl"></i>
            </h5>
        </div>
        <div class="offcanvas-body">
            @livewire('proximos-cumpleanos', [])
        </div>
    </div>

    <div class="">

        <div class="dashboard-header ajuste " style="background: #2F5D50 !important;">
            <h5 class="text-white fw-normal">¿Qué deseas hacer hoy?</h5>
        </div>

        <div class="row ajuste">

            <div class="col-12 col-md-6">
                <div class="action-buttons-container pt-5">
                    <!-- Botón 1 -->
                    <div class="action-card">
                        <div class="action-icon-container">
                            <a href="{{ route('escuelas.dashboard') }}">
                            <img src="{{ Storage::disk('global_media')->url('Funcionalidad-escuelas.png') }}" style="width: 45px; height: 45px; object-fit: contain;">
                            </a>
                        </div>
                        <p class="action-title">Academia</p>
                    </div>

                    <!-- Botón 2 -->
                    <div class="action-card">
                        <div class="action-icon-container">
                            <a href="{{ request()->routeIs('grupo.lista') ? 'active' : '' }}">
                            <img src="{{ Storage::disk('global_media')->url('Funcionalidad-grupos.png') }}" style="width: 45px; height: 45px; object-fit: contain;">
                            </a>
                        </div>
                        <p class="action-title">Grupos</p>
                    </div>

                    <!-- Botón 3: Cumpleaños -->
                    <div>
                        <div class="action-card" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBirthday">
                            <div class="action-icon-container">
                                <img src="{{ Storage::disk('global_media')->url('Funcionalidad-cumpleaños.png') }}" style="width: 45px; height: 45px; object-fit: contain;">
                            </div>
                            <p class="action-title">Cumpleaños</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">

                @if ($rolActivo->hasPermissionTo('rueda_de_la_vida.item_rueda_de_la_vida'))
                 <div id="rueda-vida-card" class="card border-0 shadow-sm overflow-hidden" style="border-radius: 15px;">
                    <a href="{{ route('ruedaDeLaVida.gestor') }}" class="text-decoration-none">
                    <div class="row g-0">
                        <div class="col-3 col-lg-3 col-xxl-2 bg-warning bg-opacity-25">
                            <img class="card-img card-img-start object-fit-cover h-100 " src="{{Storage::disk('global_media')->url('/rueda-de-la-vida/img-card-dashboard.png')  }}" alt="Card image">
                        </div>
                        <div class="col-8 col-lg-8 col-xxl-9 bg-warning bg-opacity-25 card-body d-flex align-items-start flex-column justify-content-center">

                            <h5 class="card-title text-black mb-0 fw-semibold">Rueda de la vida</h5>
                            <p class="card-text text-black text-decoration-none fs-6" style="font-size: 0.8rem !important;">Establece tus metas y mejora tu promedio con Dios.</p>

                        </div>
                        <div class="col-1 d-flex align-items-center bg-warning bg-opacity-25">
                            <button class="btn btn-icon rounded-pill btn-text-dark waves-effect">
                                <i class="ti ti-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                   </a>
                </div>
                @endif
            </div>

        </div>

        <div id="row-contenido-general" class="row ajuste">

            {{--  habilitar notificaciones (solo móvil, solo si no están activas) --}}
            <div id="banner-notif-permiso" class="col-12 my-2 " style="display:none;">
                <div style="
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 16px;
                    padding: 14px 18px;
                    display: flex;
                    align-items: center;
                    gap: 14px;
                    box-shadow: 0 4px 18px rgba(102,126,234,0.3);
                ">
                    <span style="font-size:28px;flex-shrink:0;">🔔</span>
                    <div style="flex:1;">
                        <p style="margin:0;color:white;font-weight:700;font-size:14px;">Activa las notificaciones</p>
                        <p style="margin:0;color:rgba(255,255,255,0.8);font-size:12px;">Recibe avisos importantes en tiempo real.</p>
                    </div>
                    <button onclick="pedirPermisoNotificaciones()" style="
                        background: white;
                        color: #667eea;
                        border: none;
                        border-radius: 50px;
                        padding: 8px 16px;
                        font-size: 13px;
                        font-weight: 700;
                        cursor: pointer;
                        white-space: nowrap;
                        flex-shrink: 0;
                    ">Activar</button>
                    <button onclick="cerrarBannerNotif()" style="
                        background: transparent;
                        border: none;
                        color: rgba(255,255,255,0.7);
                        font-size: 18px;
                        cursor: pointer;
                        padding: 0 0 0 4px;
                        flex-shrink:0;
                    ">&times;</button>
                </div>
            </div>

            {{-- notificaciones bloqueadas (denied) --}}
            <div id="banner-notif-bloqueado" class="col-12 mb-2" style="display:none;">
                <div style="
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                    border-radius: 16px;
                    padding: 14px 18px;
                    display: flex;
                    align-items: flex-start;
                    gap: 14px;
                    box-shadow: 0 4px 18px rgba(245,87,108,0.3);
                ">
                    <span style="font-size:28px;flex-shrink:0;margin-top:2px;">🔕</span>
                    <div style="flex:1;">
                        <p style="margin:0 0 3px;color:white;font-weight:700;font-size:14px;">Notificaciones bloqueadas</p>
                        <p style="margin:0 0 8px;color:rgba(255,255,255,0.9);font-size:12px;line-height:1.5;"
                        >Para recibirlas, ve a la <strong>configuración de tu navegador</strong>, busca los permisos de este sitio y cambia las notificaciones a <strong>Permitir</strong>.</p>
                        <p style="margin:0;color:rgba(255,255,255,0.75);font-size:11px;">
                            📱 Android: Menú → Configuración → Configuración del sitio → Notificaciones<br>
                            🍏 iOS: Configuración → Safari → Notificaciones
                        </p>
                    </div>
                    <button onclick="cerrarBannerBloqueado()" style="
                        background: transparent;
                        border: none;
                        color: rgba(255,255,255,0.7);
                        font-size: 20px;
                        cursor: pointer;
                        padding: 0;
                        flex-shrink:0;
                        line-height:1;
                    ">&times;</button>
                </div>
            </div>
            {{-- Fin notificaciones instalación --}}

            @livewire('dashboard.versiculo-del-dia', ['claseColumnas' => 'col-12  col-lg-6 mt-3'])

            <div id="col-racha-tiempo-con-dios" class="col-12  col-lg-6 mt-3" >

                <h5 class="text-black fw-bold">Racha</h5>

                <div class="card shadow" style="border-radius: 15px;">
                    <div class="card-body">
                       <div class="border-bottom">
                       @livewire('TiempoConDios.racha-semanal', [
                            'tamaño' => '70px',
                            'formato' => 'compacto',
                        ])
                       </div>

                       <div>
                        @livewire('TiempoConDios.racha-diaria', [
                            'largoLinea' => '40px',
                            'ocultarDispositivosMoviles' => false,
                            'mostrarRacha' => true,
                            'mostrar-animacion' => 'false'
                        ])
                       </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm overflow-hidden mt-3" style="border-radius: 15px;">
                    <div class="d-flex flex-column flex-sm-row">
                        <!-- Contenedor de Texto: Crece para ocupar el espacio disponible -->
                        <div class="flex-grow-1 px-8 py-10 d-flex flex-column justify-content-center text-white" style="background-color: #2D5046;">
                            <div class="mb-4">
                                <h5 class="text-white mb-2">Mi tiempo con Dios</h5>
                                <p class="opacity-75 fw-light mb-0" style="font-size: 0.9rem;">
                                    Dedica tiempo diario a tu relación íntima con Él.
                                </p>
                            </div>

                            <div>
                                <a href="{{ route('tiempoConDios.historial') }}" class="btn btn-outline-light rounded-pill px-4 py-2 mt-5" style="border-width: 1.5px; font-weight: 500;">
                                    Comenzar
                                </a>
                            </div>
                        </div>

                        <!-- Contenedor de Animación: Ajuste flexible -->
                        <div class="d-flex align-items-center justify-content-center bg-white p-3" style="min-width: 180px;">
                            @livewire('TiempoConDios.racha-animacion', [
                                'ancho' => '160px',
                                'alto' => '160px'
                            ])
                        </div>
                    </div>
                </div>




            </div>

            <div id="col-novedades" class="col-12 col-lg-12 mt-3">
                <h5 class="text-black fw-bold">Novedades</h5>

                @if ($banners->count() > 0)
                    <div class="swiper-container swiper" id="swiper-banners">
                        <div class="swiper-wrapper">
                            @foreach ($banners as $banner)
                                <div class="swiper-slide mb-5">
                                    @if ($banner->link)
                                        <a href="{{ $banner->link }}" target="_blank">
                                    @endif
                                    <div class="card shadow-none border-0 overflow-hidden rounded-3 position-relative">
                                        <img class=" w-100 " style="height: auto;width:100px;"
                                            src="{{ $banner->imagen_vinculada }}"
                                            alt="{{ $banner->nombre }}">
                                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4"
                                            style="background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 60%);">
                                            <div class="d-flex flex-row align-items-center ">
                                                    <h6 class="text-white fw-semibold mb-0">{{ $banner->nombre }}</h6>
                                                    @if ($banner->link)
                                                        <small  style="    color: white;
                                                                        border: solid 1px #fff !important;" class="btn-banner-mas btn btn-outline-ligght   rounded-pill ms-3">Ver más</small>
                                                    @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if ($banner->link)
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-button-next swiper-button-next-banners text-white"></div>
                        <div class="swiper-button-prev swiper-button-prev-banners text-white"></div>
                        <div class="swiper-pagination swiper-pagination-banners mb-5 d-none"></div>
                    </div>
                @else
                    <div class="card rounder shadow">
                        <div class="card-body p-5">
                            <p class="text-center text-muted mb-0">No hay novedades disponibles en este momento.</p>
                        </div>
                    </div>
                @endif

            </div>

            <div class="col-12 col-lg-12 mt-3">
                <h5 class="text-black fw-bold">Proximas actividades</h5>
                @if ($actividades->isNotEmpty())
                    <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg"
                        id="swiper-with-pagination-cards">
                        <div class="swiper-wrapper">
                            <!-- Cards with few info -->

                            @foreach ($actividades as $actividad)
                                <div class="swiper-slide" style="height: auto;">
                                    <div class="card border rounded-3 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex flex-row justify-content-between mb-2">
                                                <p class="fw-semibold text-black text-truncate mb-0">{{ $actividad->nombre }}
                                                </p>
                                                <span
                                                    class="badge rounded-pill bg-label-info">{{ $actividad->tipo->es_gratuita ? 'Gratuita' : 'De pago' }}</span>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-7">
                                                    <small class="text-black d-block">Fecha:</small>
                                                    <small
                                                        class="fw-bold">{{ \Illuminate\Support\Carbon::parse($actividad->fecha_inicio)->format('d-m-Y') }}</small>
                                                </div>
                                                <div class="col-5 text-end">
                                                    <a href="{{ route('actividades.perfil', $actividad->id) }}"
                                                        class="btn btn-sm btn-primary rounded-pill">Ver más</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!--/ Cards with few info -->
                        </div>
                        <div class="d-flex mt-10">
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                @else
                    <div class="row equal-height-row  g-1">
                        <div class="col equal-height-col col-12">
                            <div class="card border rounded-3 shadow-sm py-5">
                                <div class="card-body m-0 text-center">
                                    <p class="text-black"> <i class="ti ti-calendar ti-lg me-2"></i>No hay actividades
                                        disponibles para ti en este momento.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <!-- Elfsight Instagram Feed | Iglesia Manantial de Vida Eterna -->
            @if(1==2)
            <h5 class="text-black fw-bold  mt-3">Siguenos en Instagram</h5>

            <div id="container-instagram" class="col-12 col-lg-12">

                <script src="https://elfsightcdn.com/platform.js" async></script>
                <div class="elfsight-app-867b00f3-dd5b-4505-9098-befd1df883a8" data-elfsight-app-lazy></div>
            </div>
            @endif
            @livewire('dashboard.posts-widget', ['claseColumnas' => 'col-12 col-lg-12 mt-3 d-nonex'])


            <div class="col-12 col-lg-12 mt-5 d-none">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-black fw-bold mb-0">Temas para ti</h5>
                    <a href="{{ route('tema.lista') }}" class="btn btn-sm btn-outline-primary rounded-pill">Ver todos los
                        temas</a>
                </div>

                @if ($temas->isNotEmpty())
                    <div class="swiper-container swiper-container-horizontal swiper swiper-multiple-slides mb-3"
                        id="swiper-temas">
                        <div class="swiper-wrapper">
                            @foreach ($temas as $tema)
                                <div class="swiper-slide h-auto">
                                    <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden">
                                        <div class="position-relative">
                                            <a href="{{ route('tema.ver', $tema) }}">
                                                <img class="card-img-top object-fit-cover" style="height: 160px;"
                                                    src="{{ $tema->portada_url }}"
                                                    alt="{{ $tema->titulo }}">
                                            </a>
                                        </div>
                                        <div class="card-body p-3">
                                            <a href="{{ route('tema.ver', $tema) }}">
                                                <h6 class="card-title fw-bold text-black mb-0 text-truncate"
                                                    title="{{ $tema->titulo }}">
                                                    {{ $tema->titulo }}</h6>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination mt-4"></div>
                    </div>
                @else
                    <div class="col-12">
                        <div class="card border rounded-3 py-4 text-center bg-light">
                            <p class="text-muted mb-0">No hay temas disponibles en este momento.</p>
                        </div>
                    </div>
                @endif
            </div>


        </div>
    </div>


    @if (session('show_children_modal') && $formularioMenores)
        <div class="modal fade" id="modalMsnCrearMenor" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered modal-simple">
                <div class="modal-content p-0">
                    <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
                        <p class="text-black fw-semibold mb-0">¿Deseas registrar a tus hijos menor de edad?</p>
                        <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close"><i
                                class="ti ti-x ti-sm"></i></button>
                    </div>

                    <div class="modal-body px-5 py-5">

                        <div class="row">

                            <div class="col-12 mb-3">
                                <small class="text-black">
                                    Nos contaste que tienes hijos menores de edad. ¿Te gustaría registrarlos ahora? También
                                    puedes hacerlo más adelante desde el menú lateral, en la opción <b>Personas</b>.
                                </small>
                            </div>

                            <div class="d-flex">

                                @foreach ($formularioMenores as $formulario)
                                    <a href="{{ route('usuario.nuevo', $formulario) }}" type="button"
                                        class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light me-1">{{ $formulario->label }}</a>
                                @endforeach

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer border-top p-5">
                        <button id="no-volver-a-mostrar-btn" type="button" data-bs-dismiss="modal"
                            class="btn btn-sm py-2 px-4 rounded-pill btn-outline-primary waves-effect">No volver a
                            mostrar</button>
                        <button type="button" data-bs-dismiss="modal"
                            class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // --- Lógica para MOSTRAR el modal ---
                const modalElement = document.getElementById('modalMsnCrearMenor');
                if (modalElement) {
                    const miModal = new bootstrap.Modal(modalElement);
                    miModal.show();
                }

                // --- Lógica para el botón 'NO VOLVER A MOSTRAR' ---
                const noMostrarBtn = document.getElementById('no-volver-a-mostrar-btn');
                if (noMostrarBtn) {
                    noMostrarBtn.addEventListener('click', function() {
                        fetch("{{ route('usuario.noVolverMostrarModalAgregarHijos') }}", {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    // Si la respuesta no es 2xx, muestra un error
                                    console.error('Error en el servidor:', response.status, response
                                        .statusText);
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.status === 'success') {
                                    console.log('Preferencia guardada: no se volverá a mostrar el modal.');
                                } else {
                                    console.error('Error al guardar la preferencia:', data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error en la petición fetch:', error);
                            });
                    });
                }
            });

            document.addEventListener('livewire:navigated', () => {
                setTimeout(initTemasSwiper, 100);
            });
        </script>
    @endif

@endsection
