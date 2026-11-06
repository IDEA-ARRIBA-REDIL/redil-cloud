<div>
    @if ($posts->isNotEmpty())
        @php
            $gradients = [
                'linear-gradient(135deg, #7367f0 0%, #a8a1f3 100%)',
                'linear-gradient(135deg, #28c76f 0%, #81ebb1 100%)',
                'linear-gradient(135deg, #ea5455 0%, #feb692 100%)',
                'linear-gradient(135deg, #00cfe8 0%, #7367f0 100%)',
                'linear-gradient(135deg, #ff9f43 0%, #ffc085 100%)',
                'linear-gradient(135deg, #4b4b4b 0%, #282828 100%)',
                'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
                'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            ];
        @endphp
        <!-- Botón de Estado (Estilo WhatsApp) -->
        <li class="nav-item ms-2 me-3 d-flex align-items-center">
            <a class="nav-link p-0 position-relative cursor-pointer" onclick="openNavbarPostModal(0)"
                title="Nuevas publicaciones">
                <div class="avatar avatar-md border border-2 rounded-circle"
                    style="border-color: #28c76f !important; padding: 2px;">
                    @php
                        $latestPost = $posts->first();
                        $latestEsVideo = ($latestPost->es_video || !empty($latestPost->video_url));
                        $latestThumb = $latestPost->video_thumbnail_url;
                        if ($latestEsVideo && !$latestThumb && !empty($latestPost->video_url)) {
                            if (preg_match('/(?:youtube\.com\/(?:shorts\/|watch\?.*v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $latestPost->video_url, $m)) {
                                $latestThumb = 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg';
                            }
                        }
                    @endphp

                    @if ($latestEsVideo && $latestThumb)
                        <img src="{{ $latestThumb }}"
                            alt="Estado" class="rounded-circle w-100 h-100 object-fit-cover">
                    @elseif ($latestPost->image_url)
                        <img src="{{ $latestPost->image_url }}"
                            alt="Estado" class="rounded-circle w-100 h-100 object-fit-cover">
                    @elseif ($latestEsVideo)
                        <div class="rounded-circle w-100 h-100 d-flex align-items-center justify-content-center bg-dark text-white"
                            style="font-size: 0.85rem;">
                            <i class="ti ti-brand-youtube"></i>
                        </div>
                    @else
                        @php
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
                aria-hidden="true" wire:ignore data-bs-backdrop="false">
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
                                                            <img src="{{ $post->user->foto_url }}"
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
                                                    <div class="d-flex align-items-center ms-auto gap-2">
                                                        @if ($post->es_video || !empty($post->video_url))
                                                            <button type="button" class="btn btn-sm btn-icon btn-dark rounded-circle shadow btn-toggle-sound"
                                                                style="background-color: rgba(0,0,0,0.6); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.4); width: 38px; height: 38px;"
                                                                title="Silenciar"
                                                                data-muted="false"
                                                                onclick="toggleSoundPost(this)">
                                                                <i class="ti ti-volume text-white" style="font-size: 1.2rem;"></i>
                                                            </button>
                                                        @endif
                                                        <button type="button" class="btn btn-label-dark"
                                                            data-bs-dismiss="modal" aria-label="Close"
                                                            style="background-color: #ffffff; border-radius: 50%; padding: 10px; opacity: 1;"><i
                                                                class="ti ti-x"></i></button>
                                                    </div>
                                                </div>

                                                @php
                                                    $imgUrlModal = $post->image_url;
                                                    $esVideo = ($post->es_video || !empty($post->video_url));
                                                @endphp

                                                <!-- Fondo: Video, Imagen o Gradiente -->
                                                @if ($esVideo)
                                                    @php
                                                        $rawUrl = trim($post->video_url ?? '');
                                                        $isVertical = $post->es_video_vertical || str_contains($rawUrl, 'shorts') || str_contains($rawUrl, 'reels');
                                                        $embedUrl = $post->video_embed_url;
                                                        if (!$embedUrl && !empty($rawUrl)) {
                                                            if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/', $rawUrl, $m)) {
                                                                $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                                                                $isVertical = true;
                                                            } elseif (preg_match('/(?:youtube\.com\/(?:watch\?.*v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $rawUrl, $m)) {
                                                                $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                                                            } elseif (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)/', $rawUrl, $m)) {
                                                                $embedUrl = 'https://player.vimeo.com/video/' . $m[1];
                                                            } else {
                                                                $embedUrl = $rawUrl;
                                                            }
                                                        }
                                                        $separator = str_contains($embedUrl ?? '', '?') ? '&' : '?';
                                                        // Autoplay con sonido activo (iniciado por clic del usuario)
                                                        $videoParams = 'autoplay=1&enablejsapi=1&playsinline=1&rel=0';
                                                        $videoSrc = $embedUrl ? ($embedUrl . $separator . $videoParams) : '';
                                                    @endphp
                                                    <div id="nav-modal-capture-{{ $post->id }}" 
                                                         class="w-100 h-100 bg-black d-flex align-items-center justify-content-center position-relative overflow-hidden"
                                                         style="z-index: 5;">
                                                        <div class="w-100 {{ $isVertical ? 'h-100' : 'ratio ratio-16x9' }}" 
                                                             style="{{ $isVertical ? '' : 'max-height: 100%;' }}; pointer-events: auto;">
                                                            <iframe class="w-100 h-100 border-0 post-video-iframe-nav"
                                                                    data-src="{{ $videoSrc }}"
                                                                    src=""
                                                                    title="Video post"
                                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                    allowfullscreen>
                                                            </iframe>
                                                        </div>
                                                    </div>
                                                @elseif ($imgUrlModal)
                                                    <div id="nav-modal-capture-{{ $post->id }}" class="w-100 h-100">
                                                        <img src="{{ $imgUrlModal }}"
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
                                                @if ($post->image_path || $esVideo)
                                                    <div class="position-absolute bottom-0 start-0 w-100 p-4 pb-5"
                                                        style="z-index: 10; background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 60%, rgba(0,0,0,0) 100%); pointer-events: none;">
                                                        <div x-data="{ expanded: false }" class="text-white mb-2"
                                                            style="max-height: 35vh; overflow-y: auto; cursor: pointer; margin-right: 80px; pointer-events: auto;"
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
                                                        $postImageUrl = $imgUrlModal;
                                                        $postText = trim(strip_tags($post->descripcion));
                                                        $shareUrlModal = $esVideo ? ($post->video_url ?? '') : ($post->image_path ? $postImageUrl : '');
                                                    @endphp
                                                    <div class="d-flex flex-column align-items-center">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center cursor-pointer btn-share-post d-none mb-1 shadow"
                                                            style="width: 50px; height: 50px; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);"
                                                            title="Compartir"
                                                            onclick="handleSharePost(event, {{ Js::from($postText) }}, {{ Js::from($shareUrlModal) }}, '{{ $esVideo ? '' : 'nav-modal-capture-'.$post->id }}')">
                                                            <i class="ti ti-share text-white"
                                                                style="font-size: 1.8rem;"></i>
                                                        </div>

                                                        @if (!$esVideo)
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center cursor-pointer btn-download-post d-none mb-1 shadow"
                                                                style="width: 50px; height: 50px; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);"
                                                                title="Descargar imagen"
                                                                onclick="downloadPostImage(event, {{ Js::from($post->image_path ? $postImageUrl : '') }}, 'nav-modal-capture-{{ $post->id }}')">
                                                                <i class="ti ti-download text-white"
                                                                    style="font-size: 1.8rem;"></i>
                                                            </div>
                                                        @endif
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
                            if (urlImagen && (urlImagen.includes('youtube') || urlImagen.includes('youtu.be') || urlImagen.includes('vimeo'))) {
                                shareData.url = urlImagen;
                                e.stopPropagation();
                                e.preventDefault();
                                await navigator.share(shareData);
                                return;
                            }

                            let file;

                            // PRIORIDAD 1: Intentar compartir el archivo original (preserva proporción y calidad)
                            if (urlImagen) {
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
                        timer: 3000
                    });

                    const fileName = 'Publicacion_' + new Date().getTime() + '.jpg';

                    // PRIORIDAD 1: Si hay URL, descargar el archivo original vía Blob (preserva proporción y calidad)
                    if (url) {
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
                            return;
                        } catch (err) {
                            console.error("Error al descargar URL original:", err);
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
                            mousewheel: {
                                sensitivity: 0.8,
                                thresholdDelta: 40,
                                thresholdTime: 800, // Evita saltar varios posts de golpe (1 solo post por cada impulso)
                            },
                            observer: true,
                            observeParents: true,
                            on: {
                                slideChange: function() {
                                    sincronizarVideosNavbar();
                                },
                                reachEnd: function() {
                                    if ($wire.get('hasMore') && !$wire.get('cargandoMas')) {
                                        $wire.call('loadMore');
                                    }
                                }
                            }
                        });
                    });
                }

                function sincronizarVideosNavbar(forceIndex) {
                    const activeIndex = (forceIndex !== undefined) ? forceIndex : (navbarPostsSwiper ? (navbarPostsSwiper.activeIndex || 0) : 0);
                    const slides = document.querySelectorAll('#swiper-posts-vertical-navbar .swiper-slide');

                    slides.forEach((slide, idx) => {
                        const iframe = slide.querySelector('.post-video-iframe-nav');
                        const soundBtn = slide.querySelector('.btn-toggle-sound');
                        if (iframe) {
                            const dataSrc = iframe.getAttribute('data-src');
                            if (idx === activeIndex) {
                                if (dataSrc && iframe.getAttribute('src') !== dataSrc) {
                                    iframe.setAttribute('src', dataSrc);
                                    if (soundBtn) {
                                        soundBtn.setAttribute('data-muted', 'false');
                                        soundBtn.setAttribute('title', 'Silenciar');
                                        const icon = soundBtn.querySelector('i');
                                        if (icon) icon.className = 'ti ti-volume text-white';
                                    }
                                }
                            } else {
                                if (iframe.getAttribute('src') !== '') {
                                    iframe.setAttribute('src', '');
                                }
                            }
                        }
                    });
                }

                // Control interactivo de audio (YouTube / Vimeo)
                window.toggleSoundPost = function(btn) {
                    const slide = btn.closest('.swiper-slide');
                    if (!slide) return;
                    const iframe = slide.querySelector('.post-video-iframe, .post-video-iframe-nav');
                    if (!iframe || !iframe.contentWindow) return;

                    let isMuted = btn.getAttribute('data-muted') !== 'false';
                    const icon = btn.querySelector('i');

                    if (isMuted) {
                        // YouTube: unMute y volumen 100
                        iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'unMute', args: [] }), '*');
                        iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'setVolume', args: [100] }), '*');
                        // Vimeo: setMuted false y setVolume 1
                        iframe.contentWindow.postMessage(JSON.stringify({ method: 'setMuted', value: false }), '*');
                        iframe.contentWindow.postMessage(JSON.stringify({ method: 'setVolume', value: 1 }), '*');

                        btn.setAttribute('data-muted', 'false');
                        btn.setAttribute('title', 'Silenciar');
                        if (icon) icon.className = 'ti ti-volume text-white';
                    } else {
                        // YouTube: mute
                        iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'mute', args: [] }), '*');
                        // Vimeo: setMuted true
                        iframe.contentWindow.postMessage(JSON.stringify({ method: 'setMuted', value: true }), '*');

                        btn.setAttribute('data-muted', 'true');
                        btn.setAttribute('title', 'Activar sonido');
                        if (icon) icon.className = 'ti ti-volume-off text-white';
                    }
                };

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

                    // Iniciar carga del video directamente con el gesto del clic
                    sincronizarVideosNavbar(index);

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
                            setTimeout(() => {
                                if (navbarPostsSwiper && window.navPostIndexToSlide !== undefined) {
                                    navbarPostsSwiper.slideTo(window.navPostIndexToSlide, 0);
                                    window.navPostIndexToSlide = undefined;
                                }
                                sincronizarVideosNavbar();
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
                            sincronizarVideosNavbar();
                        }
                    }
                });

                // Detener cualquier video al cerrar el modal del navbar
                document.addEventListener('hide.bs.modal', function(event) {
                    if (event.target.id === 'modalNavbarPostsInmersivo') {
                        document.querySelectorAll('#swiper-posts-vertical-navbar .post-video-iframe-nav').forEach(iframe => {
                            iframe.setAttribute('src', '');
                        });
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
