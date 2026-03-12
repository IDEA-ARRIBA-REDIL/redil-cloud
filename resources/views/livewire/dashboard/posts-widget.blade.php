<div class="{{ $claseColumnas }}">
    @if ($posts->isNotEmpty())
        <h5 class="text-black fw-bold mb-3 d-flex align-items-center justify-content-between">
            Vive Manantial
            @if ($cargandoMas)
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            @endif
        </h5>

        <div class="swiper-container swiper swiper-posts position-relative" id="swiper-posts-widget" wire:ignore.self
            style="min-height: 250px;">
            <!-- Skeleton Loader (se oculta cuando Swiper se inicializa) -->
            <div id="posts-skeleton" class="d-flex gap-2 overflow-hidden position-absolute top-0 start-0 w-100 h-100"
                style="z-index: 10; background: #fff;">
                @for ($i = 0; $i < 5; $i++)
                    <div class="flex-shrink-0"
                        style="width: calc(20% - 10px); height: 100%; border-radius: 12px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: skeleton-loading 1.5s infinite;">
                    </div>
                @endfor
            </div>
            <div class="swiper-wrapper">
                @foreach ($posts as $index => $post)
                    <div class="swiper-slide list-post-item" wire:key="post-{{ $post->id }}" wire:ignore.self>
                        <!-- Post Card Full-Bleed -->
                        <div class="card h-100 border-0 overflow-hidden position-relative shadow-none cursor-pointer"
                            style="border-radius: 12px; background: #000;" onclick="openPostModal({{ $index }})">
                            <!-- Top Image/Text -->
                            <div class="card-img-top position-relative overflow-hidden"
                                style="width: 100%; height: 0; padding-bottom: 177.77%;">
                                @if ($post->image_path)
                                    @php
                                        $relativeUrl = Storage::url($configuracion->ruta_almacenamiento . '/img/publicaciones/' . $post->image_path);
                                    @endphp
                                    <div id="capture-post-{{ $post->id }}" class="position-absolute top-0 start-0 w-100 h-100">
                                        <img src="{{ $relativeUrl }}" alt="Publicación"
                                            class="w-100 h-100"
                                            style="object-fit: cover; object-position: center;"
                                            crossorigin="anonymous">
                                    </div>
                                @else
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
                                        $gradientIndex = $post->id % count($gradients);
                                        $selectedGradient = $gradients[$gradientIndex];
                                    @endphp
                                    <div id="capture-post-{{ $post->id }}"
                                        class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3 text-center"
                                        style="background: {{ $selectedGradient }};">
                                        <div class="text-white">
                                            <p class="mb-0 fw-medium"
                                                style="font-size: 0.9rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden;">
                                                {!! $post->descripcion !!}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Capa de gradiente sutil para legibilidad de iconos -->
                                <div class="position-absolute top-0 start-0 w-100 h-100"
                                    style="background: linear-gradient(to bottom, rgba(0,0,0,0) 60%, rgba(0,0,0,0.4) 100%);">
                                </div>

                                <!-- Botón Like, Compartir y Descargar (Esquina inferior derecha) -->
                                <div class="position-absolute bottom-0 end-0 mb-2 me-2 d-flex flex-column align-items-center gap-2"
                                    style="z-index: 5;">
                                    <!-- Like -->
                                    <div x-data="{
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
                                        @post-liked.window="if ($event.detail.id === postId) { liked = $event.detail.liked; count = $event.detail.count; }"
                                        class="d-flex flex-column align-items-center">
                                        <i class="ti cursor-pointer"
                                            style="font-size: 1.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"
                                            :class="liked ? 'ti-heart-filled text-danger' : 'ti-heart text-white'"
                                            @click.stop="toggleLike()"></i>
                                        <span class="text-white fw-bold"
                                            style="font-size: 0.75rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"
                                            x-text="count >= 1000 ? (count / 1000).toFixed(1).replace(/\.0$/, '') + 'K' : count"></span>
                                    </div>

                                    <!-- Compartir / Descargar -->
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
                                    <i class="ti ti-share text-white cursor-pointer btn-share-post d-none"
                                        style="font-size: 1.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"
                                        title="Compartir"
                                        onclick="handleSharePost(event, {{ Js::from($postText) }}, {{ Js::from($post->image_path ? $postImageUrl : '') }}, 'capture-post-{{ $post->id }}')"></i>

                                    <i class="ti ti-download text-white cursor-pointer btn-download-post d-none"
                                        style="font-size: 1.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"
                                        title="Descargar imagen"
                                        onclick="downloadPostImage(event, {{ Js::from($post->image_path ? $postImageUrl : '') }}, 'capture-post-{{ $post->id }}')"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Modal Inmersivo (Vertical Swiper) -->
        <div class="modal fade immersive-modal" id="modalPostsInmersivo" tabindex="-1" aria-hidden="true"
            wire:ignore.self data-bs-backdrop="false">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down modal-lg h-100 my-0 py-3">
                <div class="modal-content bg-transparent border-0 shadow-none h-100">
                    <div
                        class="modal-body p-0 h-100 position-relative d-flex justify-content-center align-items-center">
                        <!-- Contenedor con aspecto vertical máximo -->
                        <div class="w-100 shadow-lg position-relative"
                            style="max-width: 450px; height: 90vh; max-height: 850px; background: #000; border-radius: 15px; overflow: hidden;">

                            <!-- Indicador Desliza -->
                            @if ($posts->count() > 1)
                                <div id="indicador-desliza"
                                    class="position-absolute top-50 start-50 text-white text-center transition-all duration-500"
                                    style="z-index: 100; opacity: 0.7; pointer-events: none; animation: bounce-vertical 2s infinite; transition: opacity 0.5s ease-out; text-shadow: 0 2px 10px rgba(0,0,0,0.8); transform: translate(-50%, -50%);">
                                    <i class="ti ti-chevrons-up d-block mb-1" style="font-size: 3rem;"></i>
                                    <span class="fs-4 fw-bold letter-spacing-1">DESLIZA</span>
                                    <i class="ti ti-chevrons-down d-block mt-n1" style="font-size: 3rem;"></i>
                                </div>
                            @endif

                            <!-- Swiper Vertical -->
                            <div class="swiper-container swiper swiper-posts-vertical w-100 h-100"
                                id="swiper-posts-vertical">
                                <div class="swiper-wrapper h-100">
                                    @foreach ($posts as $index => $post)
                                        <div class="swiper-slide list-post-item-vertical h-100 w-100 position-relative"
                                            wire:key="modal-post-{{ $post->id }}">

                                            <!-- Top Header: User Info y Botón Cerrar -->
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
                                                <button type="button" class="btn btn-label-dark ms-auto"
                                                    data-bs-dismiss="modal" aria-label="Close"
                                                    style="background-color: #ffffff; border-radius: 50%; padding: 10px; opacity: 1;"><i
                                                        class="ti ti-x"></i></button>
                                            </div>

                                            <!-- Fondo: Imagen o Gradiente -->
                                            @if ($post->image_path)
                                                @php
                                                    $relativeUrl = Storage::url($configuracion->ruta_almacenamiento . '/img/publicaciones/' . $post->image_path);
                                                @endphp
                                                <div id="modal-capture-post-{{ $post->id }}" class="w-100 h-100">
                                                    <img src="{{ $relativeUrl }}" alt="Publicación" class="w-100 h-100"
                                                        style="object-fit: cover; object-position: center;"
                                                        crossorigin="anonymous">
                                                </div>
                                            @else
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
                                                    $selectedGradient = $gradients[$post->id % count($gradients)];
                                                @endphp
                                                <div id="modal-capture-post-{{ $post->id }}"
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

                                            <!-- Degradado Inferior oscuro para legibilidad del texto -->
                                            @if ($post->image_path)
                                                <div class="position-absolute bottom-0 start-0 w-100 p-4 pb-5"
                                                    style="z-index: 10; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0) 100%);">
                                                    <!-- Descripción -->
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

                                            <!-- Botones Flotantes (Derecha Abajo) -->
                                            <div class="position-absolute bottom-0 end-0 mb-4 me-3 d-flex flex-column align-items-center gap-4"
                                                style="z-index: 20;">
                                                <!-- Botón Like -->
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
                                                    @post-liked.window="if ($event.detail.id === postId) { liked = $event.detail.liked; count = $event.detail.count; }">
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
                                                        onclick="handleSharePost(event, {{ Js::from($postText) }}, {{ Js::from($post->image_path ? $postImageUrl : '') }}, 'modal-capture-post-{{ $post->id }}')">
                                                        <i class="ti ti-share text-white"
                                                            style="font-size: 1.8rem;"></i>
                                                    </div>

                                                    <div class="rounded-circle d-flex align-items-center justify-content-center cursor-pointer btn-download-post d-none mb-1 shadow"
                                                        style="width: 50px; height: 50px; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);"
                                                        title="Descargar imagen"
                                                        onclick="downloadPostImage(event, {{ Js::from($post->image_path ? $postImageUrl : '') }}, 'modal-capture-post-{{ $post->id }}')">
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

        @once
            <style>
                @keyframes skeleton-loading {
                    0% {
                        background-position: 200% 0;
                    }

                    100% {
                        background-position: -200% 0;
                    }
                }

                @keyframes bounce-vertical {

                    0%,
                    100% {
                        transform: translate(-50%, -50%);
                    }

                    50% {
                        transform: translate(-50%, calc(-50% - 15px));
                    }
                }

                #swiper-posts-widget.swiper-initialized #posts-skeleton {
                    display: none !important;
                }

                /* Prevenir imagen gigante si Swiper no ha cargado */
                #swiper-posts-widget:not(.swiper-initialized) .swiper-slide {
                    width: 100% !important;
                    max-width: 300px;
                    display: inline-block;
                    margin-right: 10px;
                }

                @media (max-width: 768px) {
                    #swiper-posts-widget:not(.swiper-initialized) .swiper-slide {
                        max-width: 85vw;
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
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

            <script>
                // These functions remain global just like in VersiculoDelDia
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
                            console.error("Error al descargar URL original mediante blob:", err);
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
                            console.error("Error al generar captura con html2canvas:", err);
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
                let postsSwiper;
                let postsVerticalSwiper;

                function initPostsSwiper() {
                    const container = document.querySelector('#swiper-posts-widget');
                    if (!container) return;

                    let currentIndex = 0;
                    if (postsSwiper && typeof postsSwiper.destroy === 'function') {
                        currentIndex = postsSwiper.activeIndex || 0;
                        postsSwiper.destroy(true, true);
                        postsSwiper = null;
                    }

                    postsSwiper = new Swiper(container, {
                        initialSlide: currentIndex,
                        slidesPerView: 1.2,
                        spaceBetween: 10,
                        observer: true,
                        observeParents: true,
                        breakpoints: {
                            768: {
                                slidesPerView: 4.2,
                                spaceBetween: 15
                            },
                            1200: {
                                slidesPerView: 5.2,
                                spaceBetween: 10
                            }
                        },
                        on: {
                            reachEnd: function() {
                                if ($wire.get('hasMore') && !$wire.get('cargandoMas')) {
                                    $wire.call('loadMore');
                                }
                            }
                        }
                    });
                }

                function initVerticalSwiper() {
                    const container = document.querySelector('#swiper-posts-vertical');
                    if (!container) return;

                    let currentIndex = 0;
                    if (postsVerticalSwiper && typeof postsVerticalSwiper.destroy === 'function') {
                        currentIndex = postsVerticalSwiper.activeIndex || 0;
                        postsVerticalSwiper.destroy(true, true);
                        postsVerticalSwiper = null;
                    }

                    postsVerticalSwiper = new Swiper(container, {
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
                }

                function ocultarIndicadorDesliza() {
                    const indicador = document.getElementById('indicador-desliza');
                    if (indicador && indicador.style.opacity !== '0') {
                        indicador.style.opacity = '0';
                        setTimeout(() => {
                            indicador.classList.add('d-none');
                        }, 500); // 500ms tras la transicion
                        if (window.timerIndicadorDesliza) {
                            clearTimeout(window.timerIndicadorDesliza);
                        }
                    }
                }

                function resetearMostrarIndicadorDesliza() {
                    const indicador = document.getElementById('indicador-desliza');
                    if (indicador) {
                        indicador.classList.remove('d-none');
                        indicador.style.opacity = '0.7';

                        if (window.timerIndicadorDesliza) clearTimeout(window.timerIndicadorDesliza);

                        window.timerIndicadorDesliza = setTimeout(() => {
                            ocultarIndicadorDesliza();
                        }, 4000); // Ocultar después de 4 segundos
                    }
                }

                window.openPostModal = function(index) {
                    const modalEl = document.getElementById('modalPostsInmersivo');
                    if (!modalEl) return;

                    // Inicializar vertical swiper de inmediato si no se ha hecho
                    if (!postsVerticalSwiper) {
                        initVerticalSwiper();
                    }

                    // Mover la diapositiva sin animación
                    if (postsVerticalSwiper) {
                        postsVerticalSwiper.slideTo(index, 0);
                    }

                    // Usamos Bootstrap modal nativo si existe, si no, lo instanciamos
                    let modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (!modalInstance) {
                        modalInstance = new bootstrap.Modal(modalEl);
                    }
                    modalInstance.show();
                };

                // Reparación crucial para Swiper en PC dentro de modales ocultos
                document.addEventListener('shown.bs.modal', function(event) {
                    if (event.target.id === 'modalPostsInmersivo') {
                        resetearMostrarIndicadorDesliza(); // Iniciar temporizador
                        if (postsVerticalSwiper) {
                            setTimeout(() => {
                                postsVerticalSwiper.update();
                                // Forzar que dispare el redibujado de las slides
                                postsVerticalSwiper.slideTo(postsVerticalSwiper.activeIndex, 0);
                            }, 50);
                        }
                    }
                });

                // Inicialización inicial
                setTimeout(initPostsSwiper, 100);

                // Re-inicializar garantizando reconstrucción completa (Livewire 3)
                if (window.Livewire) {
                    Livewire.hook('commit', ({
                        component,
                        succeed
                    }) => {
                        if (component.name === 'dashboard.posts-widget') {
                            succeed(() => {
                                setTimeout(() => {
                                    initPostsSwiper();
                                    if (document.getElementById('modalPostsInmersivo').classList.contains(
                                            'show')) {
                                        initVerticalSwiper();
                                    }
                                    evaluarBotones();
                                }, 50);
                            });
                        }
                    });
                }

                // Set up share/download buttons
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                function evaluarBotones() {
                    document.querySelectorAll('.btn-share-post').forEach(el => {
                        if (isMobile && navigator.share) el.classList.remove('d-none');
                    });
                    document.querySelectorAll('.btn-download-post').forEach(el => {
                        if (!isMobile || !navigator.share) el.classList.remove('d-none');
                    });
                }

                evaluarBotones();

                // Re-eval on morph o navegacion
                document.addEventListener('livewire:navigated', () => {
                    setTimeout(() => {
                        if (postsSwiper) {
                            postsSwiper.destroy(true, true);
                            postsSwiper = null;
                        }
                        initPostsSwiper();
                        evaluarBotones();
                    }, 100);
                });
            </script>
        @endscript

    @assets
        @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    @endassets

    @endif
</div>
