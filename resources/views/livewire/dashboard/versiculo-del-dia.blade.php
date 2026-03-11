<div class="{{ $claseColumnas }}" wire:key="versiculo-dia-root-{{ $versiculoId ?? 'none' }}">
    @if ($versiculo)
        @php
            $relativeUrl = $versiculo->ruta_imagen
                ? Storage::url($configuracion->ruta_almacenamiento . '/img/versiculo-diario/' . $versiculo->ruta_imagen)
                : '';
            $imageUrl = $relativeUrl
                ? (str_starts_with($relativeUrl, 'http')
                    ? $relativeUrl
                    : config('app.url') . $relativeUrl)
                : url()->current();
        @endphp

        <div class="card h-100 shadow-sm border-0 overflow-hidden position-relative" style="border-radius: 15px;">
            <!-- Imagen Cuadrada o Texto si no hay imagen -->
            <div class="card-img-top position-relative overflow-hidden"
                style="width: 100%; height: 0; padding-bottom: 100%; background-color: #f8f9fa;">
                @if ($versiculo->ruta_imagen)
                    <img src="{{ $relativeUrl }}" alt="{{ $versiculo->cita_referencia }}"
                        class="position-absolute top-0 start-0 w-100 h-100"
                        style="object-fit: cover; object-position: center;">
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
                        $gradientIndex = $versiculo->id % count($gradients);
                        $selectedGradient = $gradients[$gradientIndex];
                    @endphp
                    <div id="capture-container-{{ $versiculo->id }}"
                        class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-4 text-center"
                        style="background: {{ $selectedGradient }};" wire:ignore>
                        <div class="text-white">
                            <i class="ti ti-bible mb-2 d-block opacity-75" style="font-size: 2.5rem;"></i>
                            <p class="mb-0 fw-medium"
                                style="font-size: 1rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $plainText ?: $versiculo->cita_referencia }}
                            </p>
                            <small class="mt-2 d-block"
                                style="font-size: 0.7rem;">{{ $versiculo->cita_referencia }}</small>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Contenido Card -->
            <div class="card-body px-5 py-2">
                <div class="d-flex align-items-center justify-content-between py-1">
                    <h6 class="card-title fw-bold text-black mt-1" wire:ignore>{{ $versiculo->cita_referencia }}</h6>

                    <div class="d-flex">
                        <div class="d-flex align-items-center gap-2" wire:ignore>
                            @if ($versiculo->url_video_reflexion)
                                <button type="button"
                                    class="btn btn-sm btn-outline-dark rounded-pill py-1 px-3 text-nowrap me-2"
                                    data-bs-toggle="modal" data-bs-target="#modalVideoDashboard"
                                    data-url="{{ $versiculo->url_video_reflexion }}" style="font-size: 0.75rem;">
                                    Ver reflexión
                                </button>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-3 text-black">
                            <div wire:ignore>
                                <button type="button" class="btn p-0" title="Ver versículo" data-bs-toggle="modal"
                                    data-bs-target="#modalVersiculoDashboard"
                                    data-cita="{{ $versiculo->cita_referencia }}"
                                    data-contenido="{{ $fullTextModal }}">
                                    <i class="ti ti-book ti-sm cursor-pointer text-black"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-1" wire:key="like-container-{{ $versiculo->id }}"
                                x-data="{
                                    liked: {{ $versiculo->usuariosQueDieronLike->contains('id', auth()->id()) ? 'true' : 'false' }},
                                    count: {{ $versiculo->usuariosQueDieronLike->count() }},
                                    toggleLike() {
                                        if (this.liked) {
                                            this.liked = false;
                                            this.count--;
                                        } else {
                                            this.liked = true;
                                            this.count++;
                                        }
                                        $wire.toggleLike({{ $versiculo->id }});
                                    }
                                }">
                                <i class="ti ti-sm cursor-pointer"
                                    :class="liked ? 'ti-heart-filled text-danger' : 'ti-heart text-black'"
                                    @click="toggleLike()"></i>
                                <span style="font-size: 0.8rem;"
                                    x-text="count >= 1000 ? (count / 1000).toFixed(1).replace(/\.0$/, '') + 'K' : count"></span>
                            </div>
                            <!-- Botón dinámico: Compartir (Móvil) o Descargar (PC) -->
                            <div id="containerAccionVersiculo" wire:ignore>
                                <i class="ti ti-share ti-sm cursor-pointer text-black d-none" id="btnShareMobile"
                                    title="Compartir versículo"
                                    onclick="handleShareClick(event, '{{ $versiculo->cita_referencia }}', '{{ $versiculo->ruta_imagen ? $imageUrl : '' }}', '{{ addslashes($plainText) }}', 'capture-container-{{ $versiculo->id }}')"></i>

                                <i class="ti ti-download ti-sm cursor-pointer text-black d-none" id="btnDownloadPC"
                                    title="Descargar imagen"
                                    onclick="downloadImage('{{ $versiculo->ruta_imagen ? $imageUrl : '' }}', '{{ $versiculo->cita_referencia }}', 'capture-container-{{ $versiculo->id }}')"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="ti ti-bible ti-xl text-black mb-3"></i>
            <p class="text-black">Hoy no hay versículo del día.</p>
        </div>
    @endif

    <!-- Modales específicos para el Widget (para evitar conflictos si hay otros modales en el dashboard) -->
    @once
        <div class="modal fade modal-transparent" id="modalVideoDashboard" tabindex="-1" aria-hidden="true"
            wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 p-2 pb-0 justify-content-end">
                        <button type="button" class="btn btn-label-dark" data-bs-dismiss="modal" aria-label="Close"
                            style="background-color: #ffffff; border-radius: 50%; padding: 10px; opacity: 1;"><i
                                class="ti ti-x"></i></button>
                    </div>
                    <div class="modal-body px-5 py-2 position-relative">
                        <div id="ratioContainerDashboard"
                            class="ratio ratio-16x9 bg-black rounded-3 overflow-hidden shadow-lg border border-secondary border-opacity-25">
                            <iframe id="videoIframeDashboard" src="" title="Video reflexión" allowfullscreen
                                allow="autoplay; encrypted-media"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalVersiculoDashboard" tabindex="-1" aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header border-0 pb-0 justify-content-end">
                        <button type="button" class="btn btn-label-dark" data-bs-dismiss="modal" aria-label="Close"
                            style="background-color: #ffffff; border-radius: 50%; padding: 10px; opacity: 1;"><i
                                class="ti ti-x"></i></button>
                    </div>
                    <div class="modal-body text-center pt-0 pb-5 px-4 overflow-auto" style="max-height: 80vh;">

                        <h4 class="fw-semibold text-black mb-0">Versículo del día</h4>
                        <h5 class="text-primary fw-semibold mb-2" id="modalVersiculoCitaDashboard">Cita Bíblica</h5>

                        <div class="divider divider-dark px-5 mb-4">
                            <div class="divider-text opacity-50">
                                <i class="ti ti-bible fs-3 text-black"></i>
                            </div>
                        </div>

                        <div id="modalVersiculoTextoDashboard" class="fs-6 px-md-5 text-black text-start"
                            style="line-height: 1.6; font-style: italic;">
                            <!-- El texto se cargará vía JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .ratio-9x16 {
                --bs-aspect-ratio: 177.77%;
            }

            .ratio-16x9 {
                --bs-aspect-ratio: 56.25%;
            }

            .modal-transparent .modal-content {
                background: transparent !important;
                border: 0 !important;
                box-shadow: none !important;
            }

            /* Asegurar que los modales del widget estén por encima de la navegación móvil */
            .modal {
                z-index: 99999 !important;
            }

            .modal-backdrop {
                z-index: 99998 !important;
            }

            /* Backdrop personalizado: oscuro y translúcido */
            .modal-backdrop.show {
                opacity: 0.8 !important;
                background-color: #000 !important;
                backdrop-filter: blur(4px);
            }
        </style>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script>
            // ... (handleShareClick y downloadImage se mantienen igual)
            async function handleShareClick(e, cita, urlImagen, texto, captureId) {
                const shareData = {
                    title: 'Versículo del Día: ' + cita,
                    text: '\"' + texto + '\" - ' + cita,
                    url: '{{ url()->current() }}'
                };

                if (navigator.share) {
                    try {
                        let file;
                        if (urlImagen) {
                            const response = await fetch(urlImagen);
                            const blob = await response.blob();
                            file = new File([blob], 'versiculo.jpg', {
                                type: blob.type
                            });
                        } else if (captureId) {
                            const canvas = await html2canvas(document.getElementById(captureId), {
                                scale: 2
                            });
                            const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.9));
                            file = new File([blob], 'versiculo.jpg', {
                                type: 'image/jpeg'
                            });
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
                            return;
                        } else {
                            await navigator.share(shareData);
                        }
                    } catch (err) {
                        console.error("Error al compartir:", err);
                        navigator.share(shareData).catch(console.error);
                    }
                }
            }

            async function downloadImage(url, cita, captureId) {
                if (window.Helpers && window.Helpers.openToast) window.Helpers.openToast('info', 'Preparando descarga...');

                if (url) {
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'Versiculo_' + cita.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.jpg';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else if (captureId) {
                    try {
                        const canvas = await html2canvas(document.getElementById(captureId), {
                            scale: 3
                        });
                        const link = document.createElement('a');
                        link.href = canvas.toDataURL('image/jpeg', 1.0);
                        link.download = 'Versiculo_' + cita.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.jpg';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } catch (err) {
                        console.error("Error al generar imagen:", err);
                    }
                }

                if (window.Helpers && window.Helpers.openToast) window.Helpers.openToast('success', '¡Imagen lista!');
            }

            document.addEventListener('DOMContentLoaded', function() {
                // MOVER MODALES AL BODY: Esto soluciona los problemas de Z-index y stacking context
                const modalesWidget = ['modalVideoDashboard', 'modalVersiculoDashboard'];
                modalesWidget.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) document.body.appendChild(el);
                });

                function detectDevice() {
                    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator
                        .userAgent);
                    const btnShare = document.getElementById('btnShareMobile');
                    const btnDownload = document.getElementById('btnDownloadPC');

                    if (isMobile && navigator.share) {
                        if (btnShare) btnShare.classList.remove('d-none');
                        if (btnDownload) btnDownload.classList.add('d-none');
                    } else {
                        if (btnDownload) btnDownload.classList.remove('d-none');
                        if (btnShare) btnShare.classList.add('d-none');
                    }
                }

                detectDevice();

                // Re-detectar después de actualizaciones de Livewire (para el botón con wire:ignore)
                if (window.Livewire) {
                    Livewire.hook('commit', ({
                        component,
                        succeed
                    }) => {
                        if (component.name === 'dashboard.versiculo-del-dia') {
                            succeed(() => {
                                setTimeout(() => {
                                    detectDevice();
                                }, 50);
                            });
                        }
                    });
                }

                // Delegar eventos para que funcionen tras actualizaciones de Livewire
                document.addEventListener('show.bs.modal', function(event) {
                    const targetId = event.target.id;
                    const button = event.relatedTarget;
                    if (!button) return;

                    if (targetId === 'modalVideoDashboard') {
                        let url = button.getAttribute('data-url');
                        if (!url) return;

                        const videoIframe = document.getElementById('videoIframeDashboard');
                        const ratioContainer = document.getElementById('ratioContainerDashboard');
                        const modalDialog = event.target.querySelector('.modal-dialog');

                        let isVertical = url.includes('shorts') || url.includes('reels') || url.includes(
                            'tiktok.com');

                        if (url.includes('youtube.com/watch?v=')) url = url.replace('watch?v=', 'embed/');
                        else if (url.includes('youtu.be/')) url =
                            `https://www.youtube.com/embed/${url.split('/').pop().split('?')[0]}`;
                        else if (url.includes('youtube.com/shorts/')) {
                            url = url.replace('shorts/', 'embed/');
                            isVertical = true;
                        } else if (url.includes('vimeo.com/')) url =
                            `https://player.vimeo.com/video/${url.split('/').pop().split('?')[0]}`;

                        if (isVertical) {
                            ratioContainer.classList.replace('ratio-16x9', 'ratio-9x16');
                            modalDialog.style.maxWidth = '400px';
                        } else {
                            ratioContainer.classList.replace('ratio-9x16', 'ratio-16x9');
                            modalDialog.style.maxWidth = '800px';
                        }

                        const separator = url.includes('?') ? '&' : '?';
                        const autoplayParam = url.includes('vimeo') ? 'autoplay=1&muted=1' : 'autoplay=1&rel=0';
                        videoIframe.src = url + separator + autoplayParam;
                    }

                    if (targetId === 'modalVersiculoDashboard') {
                        const cita = button.getAttribute('data-cita');
                        const contenido = button.getAttribute('data-contenido');

                        const citaEl = document.getElementById('modalVersiculoCitaDashboard');
                        const textoEl = document.getElementById('modalVersiculoTextoDashboard');

                        if (citaEl) citaEl.innerText = cita || 'Cita Bíblica';
                        if (textoEl) textoEl.innerHTML = contenido || 'Sin contenido.';
                    }
                });

                document.addEventListener('hide.bs.modal', function(event) {
                    if (event.target.id === 'modalVideoDashboard') {
                        const videoIframe = document.getElementById('videoIframeDashboard');
                        if (videoIframe) videoIframe.src = '';
                    }
                });
            });
        </script>
    @endonce
</div>
