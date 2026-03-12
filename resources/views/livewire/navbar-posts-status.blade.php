<div>
    @if ($posts->isNotEmpty())
        <!-- Botón de Estado (Estilo WhatsApp) -->
        <li class="nav-item me-3 d-flex align-items-center">
            <a class="nav-link p-0 position-relative cursor-pointer" onclick="openNavbarPostModal(0)"
                title="Nuevas publicaciones">
                <div class="avatar avatar-md border border-2 rounded-circle"
                    style="border-color: #28c76f !important; padding: 2px;">
                    @php
                        $latestPost = $posts->first();
                        $hasImage = !empty($latestPost->image_path);
                    @endphp

                    @if ($hasImage)
                        <img src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/publicaciones/' . $latestPost->image_path) }}"
                            alt="Estado" class="rounded-circle w-100 h-100 object-fit-cover">
                    @else
                        @php
                            $gradients = [
                                'linear-gradient(135deg, #7367f0 0%, #a8a1f3 100%)',
                                'linear-gradient(135deg, #28c76f 0%, #81ebb1 100%)',
                                'linear-gradient(135deg, #ea5455 0%, #feb692 100%)',
                                'linear-gradient(135deg, #00cfe8 0%, #7367f0 100%)',
                            ];
                            $selectedGradient = $gradients[$latestPost->id % count($gradients)];
                        @endphp
                        <div class="rounded-circle w-100 h-100 d-flex align-items-center justify-content-center text-white"
                            style="background: {{ $selectedGradient }}; font-size: 0.8rem;">
                            <i class="ti ti-quote"></i>
                        </div>
                    @endif
                </div>
            </a>
        </li>

        <!-- Modal Inmersivo Navbar (Aislado, Z-index aplicado mediante CSS cuando está activo) -->
        @teleport('body')
            <div class="modal fade border-0 immersive-modal" id="modalNavbarPostsInmersivo" tabindex="-1"
                aria-hidden="true" wire:ignore.self data-bs-backdrop="false">
                <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down modal-lg h-100 my-0 py-0">
                    <div class="modal-content bg-transparent border-0 shadow-none h-100">
                        <div
                            class="modal-body p-0 h-100 position-relative d-flex justify-content-center align-items-center">
                            <!-- Contenedor con aspecto vertical máximo -->
                            <div class="w-100 shadow-lg position-relative"
                                style="max-width: 450px; height: 90vh; max-height: 850px; background: #000; border-radius: 15px; overflow: hidden;">

                                <!-- Indicador Desliza -->
                                @if ($posts->count() > 1)
                                    <div id="indicador-desliza-navbar"
                                        class="position-absolute top-50 start-50 text-white text-center transition-all duration-500"
                                        style="z-index: 100; opacity: 0.7; pointer-events: none; animation: bounce-vertical-nav 2s infinite; transition: opacity 0.5s ease-out; text-shadow: 0 2px 10px rgba(0,0,0,0.8); transform: translate(-50%, -50%);">
                                        <i class="ti ti-chevrons-up d-block mb-1" style="font-size: 3rem;"></i>
                                        <span class="fs-4 fw-bold letter-spacing-1">DESLIZA</span>
                                        <i class="ti ti-chevrons-down d-block mt-n1" style="font-size: 3rem;"></i>
                                    </div>
                                @endif

                                <!-- Swiper Vertical Navbar -->
                                <div class="swiper-container swiper swiper-posts-vertical-navbar w-100 h-100"
                                    id="swiper-posts-vertical-navbar">
                                    <div class="swiper-wrapper h-100">
                                        @foreach ($posts as $index => $post)
                                            <div class="swiper-slide list-post-item-vertical h-100 w-100 position-relative"
                                                wire:key="navbar-modal-post-{{ $post->id }}">

                                                <!-- Top Header -->
                                                <div class="position-absolute top-0 start-0 w-100 p-3 pt-4 d-flex align-items-center justify-content-between"
                                                    style="z-index: 50; background: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 100%);">
                                                    <div class="d-flex align-items-center">
                                                        @if ($post->user)
                                                            @if ($post->user->foto == 'default-m.png' || $post->user->foto == 'default-f.png')
                                                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-3 border border-white fw-bold fs-5 shadow"
                                                                    style="width: 45px; height: 45px;">
                                                                    {{ $post->user->inicialesNombre() }}
                                                                </div>
                                                            @else
                                                                <img src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $post->user->foto) }}"
                                                                    alt="{{ $post->user->nombre(3) }}"
                                                                    class="rounded-circle me-3 border border-white shadow"
                                                                    width="45" height="45"
                                                                    style="object-fit: cover;">
                                                            @endif
                                                            <h6 class="text-white mb-0 fw-bold"
                                                                style="text-shadow: 0 1px 3px rgba(0,0,0,0.8);">
                                                                {{ $post->user->nombre(3) }}</h6>
                                                        @else
                                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3 border border-white fw-bold fs-5"
                                                                style="width: 45px; height: 45px;">
                                                                U
                                                            </div>
                                                            <h6 class="text-white mb-0 fw-bold"
                                                                style="text-shadow: 0 1px 3px rgba(0,0,0,0.8);">Usuario</h6>
                                                        @endif
                                                    </div>
                                                    <button type="button" class="btn btn-label-dark ms-auto mt-2 me-2"
                                                        data-bs-dismiss="modal" aria-label="Close"
                                                        style="background-color: #ffffff; border-radius: 50%; padding: 10px; opacity: 1;"><i
                                                            class="ti ti-x"></i></button>
                                                </div>

                                                @if ($post->image_path)
                                                    <div id="nav-modal-capture-{{ $post->id }}" class="w-100 h-100">
                                                        <img src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/publicaciones/' . $post->image_path) }}"
                                                            alt="Publicación" class="w-100 h-100"
                                                            style="object-fit: cover; object-position: center;"
                                                            crossorigin="anonymous">
                                                    </div>
                                                @else
                                                    @php
                                                        $selectedGradient = $gradients[$post->id % count($gradients)];
                                                    @endphp
                                                    <div id="nav-modal-capture-{{ $post->id }}"
                                                        class="w-100 h-100 d-flex align-items-center justify-content-center p-4 text-center"
                                                        style="background: {{ $selectedGradient }};">
                                                        <div class="text-white">
                                                            <p class="mb-0 fw-medium"
                                                                style="font-size: 1.2rem; line-height: 1.5; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">
                                                                {!! $post->descripcion !!}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Degradado Inferior e Info -->
                                                @if ($post->image_path)
                                                    <div class="position-absolute bottom-0 start-0 w-100 p-4 pb-5"
                                                        style="z-index: 10; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0) 100%);">
                                                        <div x-data="{ expanded: false }" class="text-white mb-2"
                                                            style="max-height: 50vh; overflow-y: auto; cursor: pointer; margin-right: 80px;"
                                                            @click="expanded = !expanded">
                                                            <p class="mb-0 lh-base" :class="expanded ? '' : 'line-clamp-2'"
                                                                style="font-size: 0.95rem; text-shadow: 0 1px 3px rgba(0,0,0,0.9);">
                                                                {!! nl2br(strip_tags($post->descripcion)) !!}
                                                            </p>
                                                            <div
                                                                x-show="!expanded && {{ strlen(strip_tags($post->descripcion)) > 80 ? 'true' : 'false' }}">
                                                                <span class="fw-bold text-white"
                                                                    style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0,0,0,0.9);">leer
                                                                    más</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Botones Flotantes (Like) -->
                                                <div class="position-absolute bottom-0 end-0 mb-4 me-3 d-flex flex-column align-items-center gap-4"
                                                    style="z-index: 20;">
                                                    <div class="d-flex flex-column align-items-center"
                                                        x-data="{
                                                            postId: {{ $post->id }},
                                                            liked: {{ $post->likes->contains('id', auth()->id()) ? 'true' : 'false' }},
                                                            count: {{ $post->likes->count() }},
                                                            toggleLike() {
                                                                this.liked = !this.liked;
                                                                this.count = this.liked ? this.count + 1 : this.count - 1;
                                                                $wire.toggleLike(this.postId);
                                                                window.dispatchEvent(new CustomEvent('post-liked', { detail: { id: this.postId, liked: this.liked, count: this.count } }));
                                                            }
                                                        }"
                                                        @post-liked.window="if ($event.detail.id === postId && $event.detail.idWidget !== 'nav') { liked = $event.detail.liked; count = $event.detail.count; }">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center cursor-pointer mb-1 shadow"
                                                            style="width: 50px; height: 50px; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);"
                                                            @click.stop="toggleLike()">
                                                            <i class="ti" style="font-size: 1.8rem;"
                                                                :class="liked ? 'ti-heart-filled text-danger' :
                                                                    'ti-heart text-white'"></i>
                                                        </div>
                                                        <span class="text-white fw-bold"
                                                            style="font-size: 0.85rem; text-shadow: 0 1px 3px rgba(0,0,0,0.9);"
                                                            x-text="count >= 1000 ? (count / 1000).toFixed(1).replace(/\.0$/, '') + 'K' : count"></span>
                                                    </div>

                                                    <!-- Botón Compartir / Descargar -->
                                                    @php
                                                        $postImageUrl = $post->image_path
                                                            ? Storage::url($configuracion->ruta_almacenamiento . '/img/publicaciones/' . $post->image_path)
                                                            : '';
                                                        
                                                        if ($postImageUrl) {
                                                            $postImageUrl = str_starts_with($postImageUrl, 'http') 
                                                                ? $postImageUrl 
                                                                : request()->getSchemeAndHttpHost() . $postImageUrl;
                                                        } else {
                                                            $postImageUrl = url()->current();
                                                        }

                                                        $postText = trim(strip_tags($post->descripcion));
                                                    @endphp
                                                    <div class="d-flex flex-column align-items-center">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center cursor-pointer btn-share-post d-none mb-1 shadow"
                                                            style="width: 50px; height: 50px; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);"
                                                            title="Compartir"
                                                            onclick="handleSharePost(event, {{ Js::from($postText) }}, {{ Js::from($post->image_path ? $postImageUrl : '') }}, 'nav-modal-capture-{{ $post->id }}')">
                                                            <i class="ti ti-share text-white"
                                                                style="font-size: 1.8rem;"></i>
                                                        </div>

                                                        <div class="rounded-circle d-flex align-items-center justify-content-center cursor-pointer btn-download-post d-none mb-1 shadow"
                                                            style="width: 50px; height: 50px; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);"
                                                            title="Descargar imagen"
                                                            onclick="downloadPostImage(event, {{ Js::from($post->image_path ? $postImageUrl : '') }}, 'nav-modal-capture-{{ $post->id }}')">
                                                            <i class="ti ti-download text-white"
                                                                style="font-size: 1.8rem;"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endteleport

        @once
            <style>
                @keyframes bounce-vertical-nav {

                    0%,
                    100% {
                        transform: translate(-50%, -50%);
                    }

                    50% {
                        transform: translate(-50%, calc(-50% - 15px));
                    }
                }

                .line-clamp-2 {
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }

                /* Elevamos el modal inmersivo y aplicamos el backdrop SOLO cuando está activo */
                .immersive-modal.show {
                    z-index: 99999 !important;
                    background-color: rgba(0, 0, 0, 0.8);
                    backdrop-filter: blur(4px);
                }
            </style>

            <!-- Script para html2canvas si no existe ya globalmente -->
            <script>
                if (typeof html2canvas === 'undefined' && !document.getElementById('html2canvas-script')) {
                    const script = document.createElement('script');
                    script.id = 'html2canvas-script';
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                    document.head.appendChild(script);
                }
            </script>

            <script>
                async function handleSharePost(e, texto, urlImagen, captureId) {
                    const fullText = (texto ? '\"' + texto + '\"' : '');
                    const shareData = {
                        title: 'Publicación',
                        text: fullText
                    };

                    if (navigator.share) {
                        try {
                            let file;

                            // PRIORIDAD 1: Intentar compartir el archivo original (preserva proporción y calidad)
                            if (urlImagen && urlImagen !== window.location.href) {
                                try {
                                    const response = await fetch(urlImagen, { mode: 'cors' });
                                    const blob = await response.blob();
                                    file = new File([blob], 'publicacion.jpg', { type: blob.type });
                                } catch (error) {
                                    console.error("Error fetching original image for share:", error);
                                }
                            }

                            // PRIORIDAD 2: Si no hay imagen original o falló el fetch, usar la captura de pantalla
                            if (!file && captureId) {
                                const captureEl = document.getElementById(captureId);
                                if (captureEl) {
                                    if (typeof html2canvas === 'undefined') return alert('Cargando librería gráfica, intenta de nuevo en un segundo.');
                                    const canvas = await html2canvas(captureEl, {
                                        scale: 2,
                                        useCORS: true,
                                        allowTaint: true,
                                        backgroundColor: null
                                    });
                                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.9));
                                    file = new File([blob], 'publicacion.jpg', { type: 'image/jpeg' });
                                }
                            }

                            if (file && navigator.canShare && navigator.canShare({
                                    files: [file]
                                })) {
                                e.stopPropagation();
                                e.preventDefault();
                                await navigator.share({
                                    files: [file],
                                    title: shareData.title,
                                    text: shareData.text
                                });
                            } else {
                                await navigator.share(shareData);
                            }
                        } catch (err) {
                            if (err.name !== 'AbortError') {
                                console.error("Error al compartir:", err);
                                navigator.share(shareData).catch(() => {});
                            }
                        }
                    } else {
                        alert('Compartir no está soportado en este navegador de escritorio.');
                    }
                }

                async function downloadPostImage(e, url, captureId) {
                    if (e) e.stopPropagation();
                    
                    Swal.fire({
                        title: 'Preparando descarga...',
                        text: 'Estamos procesando tu imagen',
                        icon: 'info',
                        showConfirmButton: false,
                        showCancelButton: false,
                        showDenyButton: false,
                        timer: 3000
                    });

                    const fileName = 'Publicacion_' + new Date().getTime() + '.jpg';

                    // PRIORIDAD 1: Si hay URL, descargar el archivo original vía Blob (preserva proporción y calidad)
                    if (url && url !== window.location.href) {
                        try {
                            const response = await fetch(url, { mode: 'cors' });
                            const blob = await response.blob();
                            const blobUrl = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = blobUrl;
                            link.download = fileName;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            window.URL.revokeObjectURL(blobUrl);
                            if (window.Helpers && window.Helpers.openToast) window.Helpers.openToast('success', '¡Imagen original descargada!');
                            return;
                        } catch (err) {
                            console.error("Error al descargar URL original:", err);
                            // Si falla el fetch (CORS), continuamos a la Prioridad 2 (Captura)
                        }
                    }

                    // PRIORIDAD 2: Si no hay URL o falló el fetch, usar html2canvas (captura lo que se ve en pantalla)
                    const captureEl = document.getElementById(captureId);
                    if (captureId && captureEl) {
                        try {
                            const canvas = await html2canvas(captureEl, {
                                scale: 2,
                                useCORS: true,
                                allowTaint: true,
                                backgroundColor: null
                            });
                            const link = document.createElement('a');
                            link.href = canvas.toDataURL('image/jpeg', 0.9);
                            link.download = fileName;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            if (window.Helpers && window.Helpers.openToast) window.Helpers.openToast('success', '¡Imagen capturada!');
                            return;
                        } catch (err) {
                            console.error("Error al generar captura:", err);
                        }
                    }

                    // FALLBACK FINAL: Abrir en pestaña nueva si todo lo anterior falla
                    if (url) {
                        window.open(url, '_blank');
                    }
                }
            </script>
        @endonce

        @script
            <script>
                let navbarPostsSwiper;

                function ensureSwiperLoaded(callback) {
                    if (typeof Swiper !== 'undefined') {
                        callback();
                        return;
                    }

                    if (document.getElementById('swiper-cdn')) {
                        const checkInterval = setInterval(() => {
                            if (typeof Swiper !== 'undefined') {
                                clearInterval(checkInterval);
                                callback();
                            }
                        }, 50);
                        return;
                    }

                    // Cargar CSS The Swiper CSS
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
                    document.head.appendChild(link);

                    // Cargar JS
                    const script = document.createElement('script');
                    script.id = 'swiper-cdn';
                    script.src = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
                    script.onload = () => callback();
                    document.head.appendChild(script);
                }

                function initNavbarVerticalSwiper() {
                    ensureSwiperLoaded(() => {
                        const container = document.querySelector('#swiper-posts-vertical-navbar');
                        if (!container) return;

                        let currentIndex = 0;
                        if (navbarPostsSwiper && typeof navbarPostsSwiper.destroy === 'function') {
                            currentIndex = navbarPostsSwiper.activeIndex || 0;
                            navbarPostsSwiper.destroy(true, true);
                            navbarPostsSwiper = null;
                        }

                        navbarPostsSwiper = new Swiper(container, {
                            initialSlide: currentIndex,
                            direction: 'vertical',
                            slidesPerView: 1,
                            spaceBetween: 0,
                            mousewheel: true,
                            observer: true,
                            observeParents: true,
                            on: {
                                reachEnd: function() {
                                    if ($wire.get('hasMore') && !$wire.get('cargandoMas')) {
                                        $wire.call('loadMore');
                                    }
                                }
                            }
                        });
                    });
                }

                function resetearIndicadorNav() {
                    const indicador = document.getElementById('indicador-desliza-navbar');
                    if (indicador) {
                        indicador.classList.remove('d-none');
                        indicador.style.opacity = '0.7';

                        if (window.timerIndicadorNav) clearTimeout(window.timerIndicadorNav);

                        window.timerIndicadorNav = setTimeout(() => {
                            indicador.style.opacity = '0';
                            setTimeout(() => {
                                indicador.classList.add('d-none');
                            }, 500);
                        }, 4000);
                    }
                }

                window.openNavbarPostModal = function(index) {
                    const modalEl = document.getElementById('modalNavbarPostsInmersivo');
                    if (!modalEl) return;

                    let modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (!modalInstance) {
                        modalInstance = new bootstrap.Modal(modalEl);
                    }
                    modalInstance.show();

                    // Guardar el índice para deslizar luego de que se abra
                    window.navPostIndexToSlide = index;
                };

                document.addEventListener('shown.bs.modal', function(event) {
                    if (event.target.id === 'modalNavbarPostsInmersivo') {
                        resetearIndicadorNav();

                        if (!navbarPostsSwiper) {
                            initNavbarVerticalSwiper();
                            // slide to index needs to happen after init completes (async if loading CDN)
                            setTimeout(() => {
                                if (navbarPostsSwiper && window.navPostIndexToSlide !== undefined) {
                                    navbarPostsSwiper.slideTo(window.navPostIndexToSlide, 0);
                                    window.navPostIndexToSlide = undefined;
                                }
                            }, 200);
                        } else {
                            // Forzar el recálculo de dimensiones cuando el modal ya es visible
                            navbarPostsSwiper.update();

                            if (window.navPostIndexToSlide !== undefined) {
                                navbarPostsSwiper.slideTo(window.navPostIndexToSlide, 0);
                                window.navPostIndexToSlide = undefined;
                            } else {
                                navbarPostsSwiper.slideTo(navbarPostsSwiper.activeIndex, 0);
                            }
                        }
                    }
                });

                if (window.Livewire) {
                    Livewire.hook('commit', ({
                        component,
                        succeed
                    }) => {
                        if (component.name === 'navbar-posts-status') {
                            succeed(() => {
                                setTimeout(() => {
                                    if (document.getElementById('modalNavbarPostsInmersivo') && document
                                        .getElementById('modalNavbarPostsInmersivo').classList.contains(
                                            'show')) {
                                        initNavbarVerticalSwiper();
                                    }
                                    evaluarBotonesNavbar();
                                }, 50);
                            });
                        }
                    });
                }

                // Set up share/download buttons
                const isMobileDevice = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                function evaluarBotonesNavbar() {
                    // Seleccionar solo dentro de este modal
                    const modal = document.getElementById('modalNavbarPostsInmersivo');
                    if (!modal) return;

                    modal.querySelectorAll('.btn-share-post').forEach(el => {
                        if (isMobileDevice && navigator.share) el.classList.remove('d-none');
                    });
                    modal.querySelectorAll('.btn-download-post').forEach(el => {
                        if (!isMobileDevice || !navigator.share) el.classList.remove('d-none');
                    });
                }

                // Ejecutar al inicio por si ya hay slides cargadas
                setTimeout(evaluarBotonesNavbar, 500);

                document.addEventListener('livewire:navigated', () => {
                    setTimeout(evaluarBotonesNavbar, 100);
                });
            </script>
        @endscript
    @endif
    @assets
        @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    @endassets
</div>
