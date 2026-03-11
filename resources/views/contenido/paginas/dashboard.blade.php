@php
    $configData = Helper::appClasses();
    use App\Models\Actividad;
    use App\Models\TagGeneral;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Inicio')


@section('page-style')
    @vite(['resources/assets/vendor/libs/swiper/swiper.scss'])
    <style>
        /* Estilos para la barra lateral derecha de cumpleaños */
        .birthday-sidebar {
            width: 40px !important;
            /* Ancho colapsado suficiente para ver el botón pequeño */
            transition: width 0.3s ease !important;
            overflow: hidden;
            cursor: pointer;
            background-color: #382B76 !important;
        }

        .birthday-sidebar:hover {
            width: 60px !important;
            /* Ancho expandido */
        }

        .birthday-btn {
            width: 45px !important;
            height: 45px !important;
            background-color: #ffffff !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2) !important;
            border: none !important;
            text-decoration: none !important;
            margin-top: 10px !important;
            transform: scale(0.6);
            /* Pequeño por defecto */
            transform-origin: center;
        }

        .birthday-sidebar:hover .birthday-btn {
            transform: scale(1);
            /* Tamaño normal al expandir */
        }

        .birthday-btn:hover {
            transform: scale(1.1) !important;
            background-color: #f8f9fa !important;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15) !important;
        }

        /* Ajuste para el contenido principal para que no quede debajo de la barra en pantallas pequeñas si fuera necesario */
        @media (max-width: 1200px) {
            .birthday-sidebar:hover {
                width: 50px !important;
            }
        }

        /* Estilos para las flechas de navegación de Swiper */
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
    @vite(['resources/assets/vendor/libs/swiper/swiper.js'])
@endsection

@section('page-script')
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
    </script>
@endsection



@section('content')

    <div class="d-md-block d-none">
        <div class="birthday-sidebar shadow-md position-fixed z-3 d-flex align-items-center flex-column border  theme-bg-secondary vh-100"
            style=" z-index: 1090 !important; top: 0 !important; right: 0 !important; padding-top: 10px !important;">
            <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBirthday"
                class="birthday-btn d-flex align-items-center justify-content-center" title="Ver cumpleaños">
                <i class="ti ti-cake text-secondary ti-lg"></i>
            </a>
        </div>
    </div>

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

    <div class="row  me-md-3">
        <div class="col-12 col-lg-6 mt-3">
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
                                    <img class="img-fluid w-100 object-fit-cover" style="height: 350px;"
                                        src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/banners/' . $banner->imagen) }}"
                                        alt="{{ $banner->nombre }}">
                                    <div class="card-img-overlay d-flex flex-column justify-content-end p-4"
                                        style="background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 60%);">
                                        <h6 class="text-white fw-semibold mb-0">{{ $banner->nombre }}</h6>
                                        @if ($banner->link)
                                            <small class="text-white">Ver más</small>
                                        @endif
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

        <div class="col-12 col-lg-6 mt-3">

            <h5 class="text-black fw-bold">Racha</h5>

            <div class="card rounder shadow mt-3">
                <div class="card-body p-2">
                    @livewire('TiempoConDios.racha-semanal', [
                        'tamaño' => '80px',
                        'formato' => 'compacto',
                    ])

                </div>
            </div>

            <div class="card rounder shadow mt-3 d-none d-md-block">
                <div class="card-body p-2">
                    @livewire('TiempoConDios.racha-diaria', [
                        'largoLinea' => '40px',
                        'ocultarDispositivosMoviles' => true,
                    ])
                </div>
            </div>


            @if ($rolActivo->hasPermissionTo('rueda_de_la_vida.item_rueda_de_la_vida'))
                <div class="card rounded shadow mt-3 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-3">
                            <img class="img-fluid w-100 h-100 object-fit-cover rounded-start"
                                src="https://demos.pixinvent.com/vuexy-html-laravel-admin-template/demo/assets/img/elements/12.png"
                                alt="Card image">
                        </div>
                        <div class="col-9 bg-warning bg-opacity-25">
                            <div class="card-body">

                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title text-black mb-0 fw-semibold">Rueda de la vida</h5>

                                    <a href="{{ route('ruedaDeLaVida.gestor') }}"
                                        class="btn btn-icon rounded-pill btn-text-dark waves-effect">
                                        <i class="ti ti-chevron-right"></i>
                                    </a>
                                </div>

                                <small class="card-text text-black">Establece tus metas y mejora tu promedio con
                                    Dios.</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        @livewire('dashboard.versiculo-del-dia', ['claseColumnas' => 'col-12 col-md-6 mt-3'])

        @if ($rolActivo->hasPermissionTo('tiempo_con_dios.item_tiempo_con_dios'))
            <div class="col-12 col-md-6 mb-3">
                <div class="card h-100 rounded-3 shadow mt-3 overflow-hidden border-0" style="border-radius: 15px;">
                    <div class="card-img-top-wrapper position-relative overflow-hidden"
                        style="width: 100%; height: 0; padding-bottom: 100%; background-color: #f8f9fa;">
                        <img src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/dashboard/tiempo-con-dios-card.png') }}"
                            class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" alt="Árbol de engranajes">
                    </div>

                    <div class="card-body bg-white px-5 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title fw-semibold mb-0 text-black">
                                Mi tiempo con DIOS
                            </h6>

                            <a href="{{ route('tiempoConDios.historial') }}"
                                class="btn btn-outline-dark rounded-pill px-4 text-decoration-none"
                                style="font-size: 0.85rem;">
                                Comenzar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @livewire('dashboard.posts-widget', ['claseColumnas' => 'col-12 col-lg-12 mt-3'])



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
        </script>
    @endif

@endsection
