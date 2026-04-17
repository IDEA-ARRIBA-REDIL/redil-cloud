<div class="d-flex flex-column overflow-hidden" style="height: 100vh; height: 100dvh;">
    {{-- Header --}}
    <header class="bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between sticky-top shadow-sm" style="z-index: 1001;">
        <a href="{{ route('planes-lectores.inicio') }}" class="btn btn-icon btn-label-secondary rounded-circle shadow-none">
            <i class="ti ti-chevron-left"></i>
        </a>
        <div class="text-center overflow-hidden mx-3">
            <h5 class="mb-0 text-truncate fw-semibold text-uppercase">{{ $plan->titulo }}</h5>
            <small class="text-black">Día {{ $diaActualId ? ($dias->firstWhere('id', $diaActualId)->dia ?? '') : '' }}</small>
        </div>
        {{-- Espaciador para mantener simetría (Centrado del título) --}}
        <div style="width: 40px;"></div>
    </header>

    {{-- Day Scroller --}}
    @if(!$finalizado)
    <div class="day-scroll-container px-2" style="z-index: 1001;">
        <div class="container p-0 border-bottom" style="max-width: 800px;">
            <div class="swiper-container overflow-hidden py-3" 
                 x-data="swiperComponent({
                    dias: {{ json_encode($dias->map(fn($d) => ['id' => $d->id, 'dia' => $d->dia])) }}
                 })" 
                 x-init="initSwiper($el)"
                 wire:ignore>
                <div class="swiper-wrapper d-flex align-items-center">
                    @foreach($dias as $index => $dia)
                        <div class="swiper-slide" style="width: auto;">
                            <button 
                                type="button"
                                @click="seleccionarDia({{ $dia->id }}, {{ $index }})"
                                class="day-card-v2"
                                :class="getDayClass({{ $dia->id }}, {{ $index }})"
                                :disabled="isDiaBloqueado({{ $dia->id }}, {{ $index }})"
                                id="btn-dia-{{ $dia->id }}"
                            >
                                <template x-if="isDiaBloqueado({{ $dia->id }}, {{ $index }})">
                                    <i class="ti ti-lock fs-3" style="opacity: 0.6;"></i>
                                </template>
                                <template x-if="!isDiaBloqueado({{ $dia->id }}, {{ $index }})">
                                    <div class="d-flex flex-column align-items-center justify-content-center" style="position: relative; gap: 1px;">
                                        <span class="text-uppercase" 
                                              style="font-size: 0.5rem; font-weight: 800; line-height: 1; opacity: 0.9;">Día</span>
                                        <span class="fw-bold" 
                                              style="line-height: 1; {{ strlen($dia->dia) > 2 ? 'font-size: 0.95rem;' : 'font-size: 1.15rem;' }}">
                                            {{ $dia->dia }}
                                        </span>
                                        
                                        <template x-if="completados.includes({{ $dia->id }}) && diaActualId != {{ $dia->id }}">
                                            <div class="position-absolute" style="top: -8px; right: -14px; z-index: 10;">
                                                <i class="ti ti-circle-check-filled fs-6"></i>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Estilos personalizados --}}
    <style>
        .day-card-v2 {
            height: 56px !important;
            min-width: 56px !important;
            width: auto !important;
            padding: 0 12px !important;
            border-radius: 12px !important;
            border: 2px solid #e9ecef !important;
            background: #f8f9fa !important;
            color: #a1acb8 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.2s ease !important;
            cursor: pointer;
            outline: none !important;
            box-shadow: none !important;
            flex-shrink: 0 !important;
            margin: 0 !important;
        }

        .day-card-v2.active {
            background: #000000ff !important;
            border-color: #000000ff !important;
            color: #ffffff !important;
        }

        .day-card-v2.completed {
            background: #ffffffff !important;
            border-color: #45b609ff !important;
            color: #45b609ff !important;
        }

        .day-card-v2.locked {
            background: #f1f1f1 !important;
            border-color: #e9e9e9 !important;
            color: #d1d9e2 !important;
            cursor: not-allowed !important;
        }

        .day-card-v2:hover:not(.locked):not(.active) {
            transform: translateY(-2px);
            border-color: #7a7a7aff !important;
            color: #7a7a7aff !important;
        }

        .swiper-slide {
            width: fit-content !important;
        }
        [x-cloak] { display: none !important; }
    </style>

    {{-- Main Content AREA --}}
    <div class="flex-grow-1 overflow-auto p-4" id="content-scroll-area" style="padding-bottom: 0px !important;">
        @if($finalizado)
            {{-- Completion Screen --}}
            <div class="container h-100 d-flex align-items-center justify-content-center" style="position: relative; z-index: 1100;">
                <div class="card shadow-lg border-0 text-center p-5 rounded-4 animate__animated animate__zoomIn" style="max-width: 500px; margin-top: -50px;">
                    <div style="margin: 0 auto 20px auto;">
                        <img src="{{ Storage::disk('global_media')->url('Felicidades.png') }}"
                            alt="¡Felicidades!"
                            style="width: 120px; height: 120px; object-fit: contain;">
                    </div>
                    <h2 class="fw-semibold mb-1 text-primary">¡Felicidades!</h2>
                    <p class="text-black mb-2">Has completado el plan <strong>{{ $plan->titulo }}</strong>. Esperamos que haya sido de gran bendición para tu vida.</p>
                    
                    <hr class="my-2">
                    
                    <h5 class="mb-1">¿Cómo calificarías este plan?</h5>
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

                    <div class="d-flex flex-column flex-md-row gap-3">
                        <button wire:click="finalizar" class="btn btn-primary btn-md w-100 rounded-pill shadow">
                            Ver más planes
                        </button> 
                        <button wire:click="seleccionarDia({{ $dias->first()->id }})" class="btn btn-outline-secondary btn-md w-100 rounded-pill">
                            Revisar este plan
                        </button>
                    </div>
                </div>
            </div>
        @elseif($diaActual)
            <div class="container p-0" style="max-width: 800px;">
                <h3 class="fw-semibold mb-4">{{ $diaActual->titulo }}</h3>
                
                @foreach($diaActual->contenidos as $contenido)
                    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                        <div class="card-body p-4">
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
    @if(!$finalizado && $diaActual)
        <footer class="bg-white border-top p-4 shadow-lg position-fixed bottom-0 w-100" style="z-index: 1000;">
            <div class="container d-flex justify-content-center p-0" style="max-width: 800px;">
                @if($planCompletado)
                    <div class="d-flex flex-column flex-md-row w-100 gap-3">
                        <button wire:click="finalizar" class="btn btn-outline-secondary btn-md rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2 flex-grow-1">
                            <span class="fw-semibold">Explorar otros planes</span>
                        </button>
                        <button wire:click="marcarComoLeido" class="btn btn-primary btn-md rounded-pill shadow-lg d-flex align-items-center justify-content-center gap-2 flex-grow-1">
                            <span class="fw-semibold">{{ $diaActualId == $dias->last()->id ? 'Completar plan' : 'Siguiente día' }}</span>
                            <i class="ti ti-chevron-right fs-4"></i>
                        </button>
                    </div>
                @else
                    @if(in_array($diaActualId, $completados))
                        <button wire:click="marcarComoLeido" class="btn btn-outline-success btn-md w-100 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-circle-check fs-4"></i>
                            <span class="fw-semibold">{{ $diaActualId == $dias->last()->id ? 'Completar plan' : 'Siguiente día' }}</span>
                        </button>
                    @else
                        <button wire:click="marcarComoLeido" class="btn btn-primary btn-md w-100 rounded-pill shadow-lg d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-check fs-4"></i>
                            <span class="fw-semibold">{{ $diaActualId == $dias->last()->id ? 'Completar plan' : 'Marcar como completado' }}</span>
                        </button>
                    @endif
                @endif
            </div>
        </footer>
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

            initSwiper(el) {
                if (typeof Swiper !== 'undefined') {
                    this.swiper = new Swiper(el, {
                        slidesPerView: 'auto',
                        spaceBetween: 8,
                        freeMode: true,
                        grabCursor: true,
                        centeredSlides: false,
                        slidesOffsetBefore: 16,
                        slidesOffsetAfter: 16,
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
