<div class="bg-white d-flex flex-column overflow-hidden" style="height: 100vh; height: 100dvh;">
    @if($modoPreview)
        {{-- Header Oscuro de Vista Previa --}}
        <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center shadow-none border-bottom">
            <div class="col-3 text-start">
            <a href="{{ route('planes-lectores.inicio') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step">
                <span class="ti-xs ti ti-arrow-left me-2"></span>
                <span class="d-none d-md-block fw-normal">Volver</span>
            </a>
            </div>
            <div class="col-6 pl-5 text-center">
            <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">{{ $plan->titulo }}</h5>
            </div>
            <div class="col-3 text-end">
            <a href="{{ route('planes-lectores.inicio') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
                <span class="d-none d-md-block fw-normal">Salir</span>
                <span class="ti-xs ti ti-x mx-2"></span>
            </a>
            </div>
        </nav>


        {{-- Contenido de Vista Previa --}}
        <div class="flex-grow-1 overflow-auto p-4" style="padding-bottom: 110px !important; background-color: #f8f9fa;">
            <div class="container p-0 animate__animated animate__fadeIn" style="max-width: 800px;">
                
                {{-- Portada --}}
                <div class="rounded-4 overflow-hidden mb-4 mt-2 shadow-sm" style="max-height: 380px;">
                    @if($plan->portada_url)
                        <img src="{{ $plan->portada_url }}" class="w-100 h-100" style="object-fit: cover;" alt="Imagen del plan">
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
                            $selectedGradient = $gradients[$plan->id % count($gradients)];
                        @endphp
                        <div style="background: {{ $selectedGradient }}; height: 260px;" class="d-flex align-items-center justify-content-center text-white py-5 rounded-4">
                            <i class="ti ti-book fs-1" style="font-size: 80px !important;"></i>
                        </div>
                    @endif
                </div>

                {{-- Categorías --}}
                <div class="text-primary fw-bold text-uppercase tracking-wider" style="font-size: 0.8rem; letter-spacing: 1px;">
                    @if($plan->categorias->isNotEmpty())
                        {{ $plan->categorias->pluck('nombre')->join(' • ') }}
                    @else
                        Introducción
                    @endif
                </div>

                {{-- Título --}}
                <h4 class="fw-semibold text-black mb-1">{{ $plan->titulo }}</h4>

                {{-- Metadata --}}
                <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom text-muted">
                    <span class="d-flex align-items-center gap-2 fs-5 text-black">
                        <i class="ti ti-calendar text-primary fs-5"></i> {{ $dias->count() }} {{ Str::plural('día', $dias->count()) }}
                    </span>
                    <span class="d-flex align-items-center gap-2 fs-5 text-black">
                        <i class="ti ti-star-filled text-warning fs-5"></i> {{ number_format($plan->calificacion, 1) }}
                    </span>
                </div>

                {{-- Descripción --}}
                <div class="description-content text-black fs-6 lh-base" style="text-align: justify;">
                    {!! $plan->descripcion !!}
                </div>

            </div>
        </div>

        {{-- Footer de Vista Previa --}}
        <footer class="bg-white border-top p-4 shadow-lg position-fixed bottom-0 w-100" style="z-index: 1000;">
            <div class="container d-flex justify-content-between align-items-center p-0" style="max-width: 800px;">
                <a href="{{ route('planes-lectores.inicio') }}" class="btn btn-outline-secondary btn-md rounded-pill px-4">
                    Volver
                </a>
                <button wire:click="comenzarPlan" class="btn btn-primary btn-md rounded-pill px-5 fw-semibold shadow-md">
                    Comenzar
                </button>
            </div>
        </footer>
    @else
        @if($finalizado)
            {{-- 1. Pantalla de Éxito Total (Completó todo el plan) --}}
            <div class="container h-100 d-flex flex-column align-items-center justify-content-center bg-white" style="position: relative; z-index: 1100; min-height: 100vh;">
                <div class="text-center p-5 animate__animated animate__zoomIn" style="max-width: 500px;">
                    <div style="margin: 0 auto 20px auto;">
                        <img src="{{ Storage::disk('global_media')->url('Felicidades.png') }}"
                            alt="¡Felicidades!"
                            style="width: 260px; height: 260px; object-fit: contain;">
                    </div>
                    <h2 class="fw-bold text-black mb-2" style="font-size: 2.2rem;">¡Felicidades!</h2>
                    <p class="text-black mb-4 fs-6">Has completado el plan <strong>{{ $plan->titulo }}</strong>. Esperamos que haya sido de gran bendición para tu vida.</p>
                    
                    <hr class="my-3">
                    
                    <h5 class="mb-2 fw-semibold">¿Cómo calificarías este plan?</h5>
                    <div class="d-flex justify-content-center gap-2 mb-5 fs-1">
                        @for($i = 1; $i <= 5; $i++)
                            <i 
                                wire:click="guardarCalificacion({{ $i }})"
                                class="ti {{ $i <= $calificacion ? 'ti-star-filled text-warning' : 'ti-star text-secondary' }} cursor-pointer star-btn"
                                style="transition: transform 0.2s;"
                                onmouseover="this.style.transform='scale(1.2)'"
                                onmouseout="this.style.transform='scale(1.0)'"
                            ></i>
                        @endfor
                    </div>

                    <div class="d-flex flex-column gap-3 justify-content-center">
                        <button wire:click="finalizar" class="btn btn-success btn-md rounded-pill shadow-md fw-semibold px-5 mx-auto" style="background-color: #3a750c; border-color: #3a750c; color: white; min-width: 200px;">
                            Ver más planes
                        </button> 
                    </div>
                </div>
            </div>
        @elseif($mostrandoExitoDia)
            {{-- 2. Pantalla de Felicitación Diaria (Completó un día intermedio) --}}
            <div class="container h-100 d-flex flex-column align-items-center justify-content-center bg-white" style="position: relative; z-index: 1100; min-height: 100vh;">
                <div class="text-center p-5 animate__animated animate__zoomIn" style="max-width: 500px;">
                    <div class="mb-4">
                        <img src="{{ Storage::disk('global_media')->url('Felicidades.png') }}"
                            alt="¡Felicidades!"
                            style="width: 260px; height: 260px; object-fit: contain;">
                    </div>
                    <h2 class="fw-bold text-black mb-2" style="font-size: 2.2rem;">¡Felicidades!</h2>
                    <p class="text-black fs-6 mb-5">Completaste tu día <strong class="text-black">{{ $diaCompletadoNumero }} de {{ count($dias) }}</strong>, sigue así</p>
                    
                    <button wire:click="finalizar" class="btn btn-success btn-md rounded-pill px-5 fw-semibold shadow-md">
                        Salir
                    </button>
                </div>
            </div>
        @else
            {{-- 3. Visor de Lectura Tradicional --}}
            {{-- Header Oscuro de Vista Previa --}}
            <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center shadow-none border-bottom">
                <div class="col-3 text-start">
                <a href="{{ route('planes-lectores.inicio') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step">
                    <span class="ti-xs ti ti-arrow-left me-2"></span>
                    <span class="d-none d-md-block fw-normal">Volver</span>
                </a>
                </div>
                <div class="col-6 pl-5 text-center">
                <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">{{ $plan->titulo }}</h5>
                </div>
                <div class="col-3 text-end">
                <a href="{{ route('planes-lectores.inicio') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
                    <span class="d-none d-md-block fw-normal">Salir</span>
                    <span class="ti-xs ti ti-x mx-2"></span>
                </a>
                </div>
            </nav>

            {{-- Barra de progreso del día actual --}}
            @if($diaActual)
            <div class="bg-white py-3 px-4" style="z-index: 1000;">
                <div class="container p-0 animate__animated animate__fadeIn" style="max-width: 800px;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 btn-primary" style="width: 42px; height: 42px; color: white;">
                            <i class="ti ti-book fs-4"></i>
                        </div>
                        <div>
                            @php
                                $diaActualNumero = $dias->firstWhere('id', $diaActualId)->dia ?? 1;
                                $totalDias = $dias->count();
                            @endphp
                            <div class="text-muted small">Día {{ $diaActualNumero }} de {{ $totalDias }}</div>
                            <div class="fw-semibold text-black fs-6">{{ $diaActual->titulo }}</div>
                        </div>
                    </div>
                    
                    @php
                        $porcentajeAvance = ($totalDias > 0) ? round(($diaActualNumero / $totalDias) * 100, 1) : 0;
                    @endphp
                    <div class="progress mb-3" style="height: 6px; background-color: #e9ecef; border-radius: 3px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $porcentajeAvance }}%; background-color: #7367f0; border-radius: 3px;"></div>
                    </div>

                    {{-- Day Scroller ( stepper circular ) --}}
                    <div class="day-scroll-container mt-4 pt-3 border-top">
                        <div class="swiper-container overflow-hidden py-2" 
                             x-data="swiperComponent({
                                dias: {{ json_encode($dias->map(fn($d) => ['id' => $d->id, 'dia' => $d->dia])) }}
                             })" 
                             x-init="initSwiper($el)"
                             wire:ignore>
                            <div class="swiper-wrapper d-flex align-items-center">
                                @foreach($dias as $index => $dia)
                                    @php
                                        // Determinamos si el día actual o anterior está completado para colorear la línea conectora hacia el siguiente
                                        $esCompletado = in_array($dia->id, $completados);
                                    @endphp
                                    <div class="swiper-slide {{ $esCompletado ? 'line-green' : 'line-gray' }}" style="width: auto;">
                                        <button 
                                            type="button"
                                            @click="seleccionarDia({{ $dia->id }}, {{ $index }})"
                                            class="day-card-v2"
                                            :class="getDayClass({{ $dia->id }}, {{ $index }})"
                                            :disabled="isDiaBloqueado({{ $dia->id }}, {{ $index }})"
                                            id="btn-dia-{{ $dia->id }}"
                                        >
                                            <span class="fw-bold">{{ $dia->dia }}</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Estilos personalizados para los días en formato Stepper --}}
            <style>
                .day-scroll-container .swiper-container {
                    padding: 10px 0;
                }
                .swiper-slide {
                    position: relative;
                    display: flex;
                    align-items: center;
                    width: auto !important;
                }
                .swiper-slide::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 36px; /* Inicia justo al borde derecho del círculo */
                    width: 24px; /* Ancho de la línea conectora, debe coordinar con spaceBetween en JS */
                    height: 3px;
                    transform: translateY(-50%);
                    z-index: 1;
                }
                .swiper-slide.line-green::after {
                    background-color: #0b7a42 !important;
                }
                .swiper-slide.line-gray::after {
                    background-color: #e9ecef !important;
                }
                .swiper-slide:last-child::after {
                    display: none !important;
                }

                .day-card-v2 {
                    width: 36px !important;
                    height: 36px !important;
                    min-width: 36px !important;
                    border-radius: 50% !important;
                    border: 2px solid #d1d9e2 !important;
                    background: #ffffff !important;
                    color: #a1acb8 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    font-weight: bold !important;
                    font-size: 0.95rem !important;
                    transition: all 0.2s ease !important;
                    cursor: pointer;
                    outline: none !important;
                    box-shadow: none !important;
                    flex-shrink: 0 !important;
                    margin: 0 !important;
                    position: relative;
                    z-index: 2; /* Encima de la línea conectora */
                }

                .day-card-v2.active {
                    background: #0b7a42 !important;
                    border-color: #0b7a42 !important;
                    color: #ffffff !important;
                }

                .day-card-v2.completed {
                    background: #0b7a42 !important;
                    border-color: #0b7a42 !important;
                    color: #ffffff !important;
                }

                .day-card-v2.locked {
                    background: #ffffff !important;
                    border-color: #d1d9e2 !important;
                    color: #a1acb8 !important;
                    cursor: not-allowed !important;
                }

                .day-card-v2.pending-current {
                    background: #ffffff !important;
                    border-color: #0b7a42 !important;
                    color: #0b7a42 !important;
                }
                .day-card-v2.pending-current:hover {
                    background: #eef7f2 !important;
                    transform: scale(1.1);
                }

                .day-card-v2:hover:not(.locked):not(.active) {
                    transform: scale(1.1);
                    border-color: #0b7a42 !important;
                    color: #0b7a42 !important;
                }

                [x-cloak] { display: none !important; }
            </style>
            @endif

            {{-- Main Content AREA --}}
            <div class="flex-grow-1 overflow-auto p-4" id="content-scroll-area" style="padding-bottom: 110px !important;">
                @if($diaActual)
                    <div class="container p-0" style="max-width: 800px;">

                        <div class="text-primary fw-bold text-uppercase tracking-wider" style="font-size: 0.8rem; letter-spacing: 1px;">
                            Día {{ $diaActualNumero }} 
                        </div>
                        <h4 class="fw-semibold mb-4">{{ $diaActual->titulo }}</h4>
                        
                        @foreach($diaActual->contenidos as $contenido)
                            <div class="mb-4 rounded-4 overflow-hidden">
                                <div class="text-black">
                                    @if($contenido->tipoContenido->slug == 'reflexion')
                                        <div class="prose">
                                            {!! $contenido->contenido !!}
                                        </div>
                                    @elseif($contenido->tipoContenido->slug == 'pasaje')
                                        @php
                                            $data = json_decode($contenido->contenido, true);
                                            $plainText = "";
                                            $htmlText = "";
                                            
                                            if(isset($data) && is_array($data)) {
                                                foreach($data as $selection) {
                                                    $referencia = $selection['cita_larga'] ?? ($selection['cita'] ?? '');
                                                    if($referencia) {
                                                        $htmlText .= "<div class='fw-bold mb-2 text-primary'>" . $referencia . "</div>";
                                                    }
                                                    
                                                    $versiculosArray = $selection['versiculos'] ?? [];
                                                    foreach($versiculosArray as $v) {
                                                        $num = $v['numero'] ?? '';
                                                        $texto = $v['texto'] ?? '';
                                                        $htmlText .= "<span class='badge bg-label-primary rounded-circle p-1 me-1' style='font-size: 0.65rem; min-width: 1.2rem; display: inline-flex; align-items: center; justify-content: center;'>" . $num . "</span>" . $texto . " ";
                                                    }
                                                    $htmlText .= "<br><br>";
                                                }
                                            } else {
                                                $htmlText = $contenido->contenido; // Fallback if not JSON
                                            }
                                        @endphp
                                        <div class="bible-passage fst-italic lh-lg">
                                            {!! $htmlText !!}
                                        </div>
                                    @elseif($contenido->tipoContenido->slug == 'video')
                                        @php
                                            $videoData = json_decode($contenido->contenido, true);
                                            $plat = $videoData['plataforma'] ?? 'otro';
                                            $vId = $videoData['id'] ?? '';
                                            $embedUrl = '';
                                            
                                            if ($plat === 'youtube') {
                                                $embedUrl = "https://www.youtube.com/embed/{$vId}";
                                            } elseif ($plat === 'vimeo') {
                                                $embedUrl = "https://player.vimeo.com/video/{$vId}";
                                            } else {
                                                $embedUrl = $videoData['url'] ?? $contenido->contenido;
                                            }
                                        @endphp
                                        @if($embedUrl)
                                            <div class="ratio ratio-16x9 rounded overflow-hidden shadow">
                                                <iframe src="{{ $embedUrl }}" allowfullscreen></iframe>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div> 

            {{-- Footer Fijo al borde inferior del viewport --}}
            @if($diaActual)
                @php
                    // Habilitar finalizar únicamente si el día de pantalla coincide con el día por completar y el plan no está completado
                    $diaActualRealId = $this->getDiaActualRealId();
                    $isDiaActual = !$planCompletado && ($diaActualId == $diaActualRealId);
                @endphp
                <footer class="bg-white border-top p-4 shadow-lg position-fixed bottom-0 w-100" style="z-index: 1000;">
                    <div class="container d-flex justify-content-between align-items-center p-0" style="max-width: 800px;">
                        <button wire:click="retrocederDia" class="btn btn-outline-secondary btn-md rounded-pill px-4">
                            Volver
                        </button>
                        <button wire:click="marcarComoLeido" class="btn btn-primary btn-md rounded-pill px-5 fw-semibold shadow-md" {{ !$isDiaActual ? 'disabled' : '' }}>
                            Finalizar
                        </button>
                    </div>
                </footer>
            @endif

            {{-- Reproductor en Modo Flotante --}}
            @if($diaActual)
                <livewire:tiempo-con-dios.reproductor modo="flotante" />
            @endif
        @endif
    @endif

    @assets
        @vite(['resources/assets/vendor/libs/swiper/swiper.scss'])
        @vite(['resources/assets/vendor/libs/swiper/swiper.js'])
    @endassets

    @script
    <script>
        Alpine.data('swiperComponent', (config) => ({
            swiper: null,
            listadoDias: config.dias,
            diaActualId: @entangle('diaActualId'),
            completados: @entangle('completados'),
            planCompletado: @entangle('planCompletado'),
            diaActualRealId: @entangle('diaActualRealId'),

            initSwiper(el) {
                if (typeof Swiper !== 'undefined') {
                    this.swiper = new Swiper(el, {
                        slidesPerView: 'auto',
                        spaceBetween: 24,
                        freeMode: true,
                        grabCursor: true,
                        centeredSlides: false,
                        slidesOffsetBefore: 8,
                        slidesOffsetAfter: 8,
                        watchSlidesProgress: true,
                        initialSlide: this.getInitialIndex(),
                        observer: true,
                        observeParents: true
                    });
                    
                    this.$nextTick(() => {
                        this.scrollToActive();
                    });
                }

                // Escuchar cambios de día desde el backend (por si acaso)
                this.$watch('diaActualId', (value) => {
                    this.scrollToActive();
                    document.getElementById('content-scroll-area').scrollTop = 0;
                });
            },

            getInitialIndex() {
                return this.listadoDias.findIndex(d => d.id == this.diaActualId) || 0;
            },

            scrollToActive() {
                if (this.swiper) {
                    const activeIndex = this.listadoDias.findIndex(d => d.id == this.diaActualId);
                    if (activeIndex !== -1) {
                        this.swiper.slideTo(activeIndex, 300);
                    }
                }
            },

            seleccionarDia(id, index) {
                if (this.isDiaBloqueado(id, index)) return;
                this.$wire.seleccionarDia(id);
            },


            isDiaBloqueado(id, index) {
                if (this.planCompletado) return false;
                
                const isCompletado = this.completados.includes(id);
                const isActual = id == this.diaActualId;
                
                if (isCompletado || isActual) return false;
                if (index === 0) return false;

                // Bloqueado si el anterior no está completado
                const diaAnteriorId = this.listadoDias[index - 1].id;
                return !this.completados.includes(diaAnteriorId);
            },

            getDayClass(id, index) {
                if (this.isDiaBloqueado(id, index)) return 'locked';
                if (id == this.diaActualId) return 'active';
                if (this.completados.includes(id)) return 'completed';
                if (id == this.diaActualRealId) return 'pending-current';
                return '';
            }
        }));

        // Mantener compatibilidad con eventos externos si existen
        window.addEventListener('update-swiper', () => {
            // El watch de Alpine ya se encarga, pero esto fuerza si fuera necesario
            const swiperEl = document.querySelector('.swiper-container');
            if (swiperEl && swiperEl.__x) {
                swiperEl.__x.$data.scrollToActive();
            }
        });
    </script>
    @endscript
</div>
