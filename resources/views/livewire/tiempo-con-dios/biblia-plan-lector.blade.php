@php
  $gradients = [
      'linear-gradient(135deg, #7367f0 0%, #a8a1f3 100%)', // Original Purple
      'linear-gradient(135deg, #28c76f 0%, #81ebb1 100%)', // Green
      'linear-gradient(135deg, #ea5455 0%, #feb692 100%)', // Red/Orange
      'linear-gradient(135deg, #00cfe8 0%, #7367f0 100%)', // Blue/Purple
      'linear-gradient(135deg, #ff9f43 0%, #ffc085 100%)', // Orange
      'linear-gradient(135deg, #4b4b4b 0%, #282828 100%)', // Dark
      'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)', // Deep Blue
      'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', // Indigo
  ];
@endphp

<div class="{{ $class }}" x-data="{
    showModal: false,
    selectedPlan: null
}">

    @if($planSeleccionadoId && $diaSeleccionado)
        <!-- Mostrar contenido del día -->
     <div>
                <div class="text-center">
                    <h5 class="mb-0 text-truncate fw-semibold text-uppercase">{{ $planSeleccionado->titulo }}</h5>
                    <small class="text-black">Día {{ $diaSeleccionado->dia }} - {{ $diaSeleccionado->titulo }}</small>
                </div>
                <hr>
                <div class="px-2 mb-4 text-start">
                    @foreach($diaSeleccionado->contenidos as $contenido)
                        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: #fdfdfd;">
                            <div class="card-body p-4 text-black  text-start">
                                @if($contenido->tipoContenido->slug == 'reflexion')
                                    <div class="prose">
                                        {!! $contenido->contenido !!}
                                    </div>
                                @elseif($contenido->tipoContenido->slug == 'pasaje')
                                    @php
                                        $data = json_decode($contenido->contenido, true);
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
     </div>
        

    @else
        <!-- Estado Inicial: Seleccionar Plan -->
        <div class="text-center py-5 border rounded-3 bg-white shadow-sm my-3">
            <div class="mb-4">
                <i class="ti ti-books text-primary" style="font-size: 4rem;"></i>
            </div>
            <h4 class="fw-bold text-black">Estás en Modo Plan Lector</h4>
            <p class="text-muted mb-4">Selecciona el plan que vas a realizar hoy para comenzar.</p>
            <button type="button" class="btn btn-primary btn-lg rounded-pill px-5" data-bs-toggle="modal" data-bs-target="#modalSeleccionarPlan">
                Seleccionar Plan Lector
            </button>
        </div>
        
        <!-- Modal Seleccionar Plan (Pantalla Completa o Grande) -->
        @teleport('body')
        <div class="modal fade" id="modalSeleccionarPlan" tabindex="-1" aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content" style="background-color: #f8f9fa;">
                    <div class="modal-header border-bottom pb-3 bg-white">
                        <h4 class="modal-title fw-bold text-black" id="modalSeleccionarPlanTitle">Elige un Plan Lector</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 p-md-5">
                        
                        <!-- Continuar Leyendo -->
                        @if($planesInscritos->isNotEmpty())
                        <div class="mb-5">
                            <h5 class="fw-semibold text-black fs-4 mb-4">Continuar leyendo</h5>
                            <div class="row g-4">
                                @foreach($planesInscritos as $planInscrito)
                                @php
                                    $urlImagenInscrito = $planInscrito->portada_url;
                                    $selectedGradientInscrito = $gradients[$planInscrito->id % count($gradients)];
                                @endphp
                                <div class="col-12 col-md-4">
                                    <div class="card shadow-sm border-0 h-100 position-relative overflow-hidden card-plan-explorar">
                                        @if($urlImagenInscrito)
                                            <img src="{{ $urlImagenInscrito }}" class="card-img-top" alt="Imagen del plan" style="height: 150px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top d-flex align-items-center justify-content-center text-white" style="height: 150px; background: {{ $selectedGradientInscrito }};">
                                                <i class="ti ti-book fs-1"></i>
                                            </div>
                                        @endif
                                        <div class="card-body d-flex flex-column p-3">
                                            <h6 class="fw-semibold text-black mb-1 text-truncate" title="{{ $planInscrito->titulo }}">{{ $planInscrito->titulo }}</h6>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-black fw-bold">{{ $planInscrito->pivot->porcentaje_progreso }}%</small>
                                                    <small class="text-warning fw-bold d-flex align-items-center">
                                                    <i class="ti ti-star-filled me-1" style="font-size: 0.8rem;"></i>
                                                    {{ number_format($planInscrito->calificacion, 1) }}
                                                    </small>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $planInscrito->pivot->porcentaje_progreso }}%;"></div>
                                                </div>
                                            </div>
                                            <div class="mt-auto">
                                                <button type="button" class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-semibold" wire:click="comenzarPlan({{ $planInscrito->id }})">
                                                    Continuar hoy
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Explorar planes lectores -->
                        <div>
                            <h5 class="fw-semibold text-black fs-4 mb-4 mt-5">Explorar planes lectores</h5>

                            <!-- Buscador -->
                            <div class="row mb-3 align-items-center">
                                <!-- Buscador -->
                                <div class="col-12 col-md-9 mb-3 mb-md-0">
                                    <div class="input-group input-group-merge shadow-sm">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" class="form-control" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por título palabra clave...">
                                    </div>
                                </div>

                                <!-- Selector de Ordenamiento -->
                                <div class="col-12 col-md-3 d-flex justify-content-md-end">
                                    <div class="dropdown">
                                    @php
                                        $opcionesOrden = [
                                            'nuevos' => 'Últimos',
                                            'populares' => 'Populares',
                                            'valorados' => 'Más estrellas',
                                            'breves' => 'Cortos',
                                            'extensos' => 'Largos'
                                        ];
                                    @endphp
                                    <button class="btn btn-outline-secondary text-black dropdown-toggle shadow-sm py-2 px-4 d-flex align-items-center" 
                                            type="button" id="dropdownMenuSort" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text-black small me-2">Ordenar por:</span>
                                        <span class="fw-bold">{{ $opcionesOrden[$ordenarPor] ?? 'Últimos' }}</span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg py-2" aria-labelledby="dropdownMenuSort">
                                        @foreach($opcionesOrden as $key => $label)
                                            <a class="dropdown-item d-flex align-items-center py-2 " 
                                                href="javascript:void(0);" 
                                                wire:click="$set('ordenarPor', '{{ $key }}')">
                                                @if($ordenarPor === $key)
                                                    <i class="ti ti-check me-2 text-primary"></i>
                                                @else
                                                    <span class="me-4"></span>
                                                @endif
                                                {{ $label }}
                                            </a>
                                        @endforeach
                                    </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtros Tipo Tags con Swiper horizontales -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="swiper overflow-hidden" 
                                        x-data="swiperComponent()" 
                                        x-init="initSwiper($el)" 
                                        wire:ignore>
                                        <div class="swiper-wrapper" style="align-items: center; padding: 5px 0px;">
                                            
                                            <!-- Tab de "Todos" -->
                                            <div class="swiper-slide" style="width: auto; margin-right: 15px;">
                                                <button type="button" 
                                                        class="btn btn-sm px-3 rounded-pill shadow-sm border" 
                                                        :class="categoriaSeleccionada === null ? 'btn-primary' : 'btn-outline-secondary btn-white text-dark'"
                                                        @click="seleccionarCategoria(null)">
                                                Todos
                                                </button>
                                            </div>

                                            <!-- Tags iterados de categorías -->
                                            @foreach($categorias as $categoria)
                                            <div class="swiper-slide" style="width: auto; margin-right: 15px;">
                                                <button type="button" 
                                                        class="btn btn-sm px-3 rounded-pill shadow-sm border" 
                                                        :class="categoriaSeleccionada === {{ $categoria->id }} ? 'btn-primary' : 'btn-outline-secondary btn-white text-dark'"
                                                        @click="seleccionarCategoria({{ $categoria->id }})">
                                                {{ $categoria->nombre }}
                                                </button>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 position-relative">
                                <div wire:loading.flex wire:target="buscar, seleccionarCategoria, gotoPage, previousPage, nextPage" class="position-absolute w-100 h-100 justify-content-center align-items-center" style="background: rgba(255,255,255,0.7); z-index: 10;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                @foreach($planesDisponibles as $plan)
                                @php
                                    $urlImagenExplorar = $plan->portada_url;
                                    $selectedGradientExplorar = $gradients[$plan->id % count($gradients)];
                                @endphp
                                <div class="col-12 col-md-4">
                                    <div class="card shadow-sm border-0 h-100 position-relative overflow-hidden card-plan-explorar">
                                        @if($urlImagenExplorar)
                                            <img src="{{ $urlImagenExplorar }}" class="card-img-top" alt="Imagen del plan" style="height: 180px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top d-flex align-items-center justify-content-center text-white" style="height: 180px; background: {{ $selectedGradientExplorar }};">
                                                <i class="ti ti-book fs-1"></i>
                                            </div>
                                        @endif
                                        <div class="card-body d-flex flex-column p-3">
                                            <h6 class="fw-semibold text-black mb-1 text-truncate" title="{{ $plan->titulo }}">{{ $plan->titulo }}</h6>
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="text-black small">{{ $plan->dias_count }} {{ Str::plural('día', $plan->dias_count) }}</span>
                                                <span class="mx-2 text-muted">•</span>
                                                <span class="text-warning small fw-bold d-flex align-items-center">
                                                    <i class="ti ti-star-filled me-1" style="font-size: 0.8rem;"></i>
                                                    {{ number_format($plan->calificacion, 1) }}
                                                </span>
                                            </div>
                                            <div class="mt-auto">
                                                <button type="button" class="btn btn-outline-primary btn-sm w-100 rounded-pill" 
                                                        @click="$dispatch('ver-plan-modal', {{ Js::from([
                                                            'id' => $plan->id,
                                                            'titulo' => $plan->titulo,
                                                            'descripcion' => $plan->descripcion,
                                                            'imagen_url' => $urlImagenExplorar,
                                                            'gradient' => $selectedGradientExplorar,
                                                            'dias_count' => $plan->dias_count,
                                                            'calificacion' => (float) $plan->calificacion
                                                        ]) }})">
                                                    Ver plan
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-black pagination-container">
                                {{ $planesDisponibles->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @endteleport

        <!-- Modal de Vista Previa (Detalle del Plan para Explorar) -->
        @teleport('body')
        <div class="modal fade" id="modalDetallePlanExplorar" tabindex="-1" aria-hidden="true" wire:ignore.self
             x-data="{ selectedPlan: null }"
             x-on:ver-plan-modal.window="selectedPlan = $event.detail; (new bootstrap.Modal(document.getElementById('modalDetallePlanExplorar'))).show();">
            <div class="modal-dialog modal-lg modal-dialog-centered" x-show="selectedPlan" x-cloak>
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <template x-if="selectedPlan">
                        <div>
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-semibold text-black fs-4" x-text="selectedPlan.titulo"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-3">
                                <!-- Imagen Destacada -->
                                <div class="rounded-3 overflow-hidden mb-5 mt-3 shadow-sm" style="max-height: 400px;">
                                    <template x-if="selectedPlan.imagen_url">
                                        <img :src="selectedPlan.imagen_url" class="w-100 h-100" style="object-fit: cover;" alt="Plan image">
                                    </template>
                                    <template x-if="!selectedPlan.imagen_url">
                                        <div :style="`background: ${selectedPlan.gradient};`" class="d-flex align-items-center justify-content-center text-white py-10" style="height: 300px;">
                                            <i class="ti ti-book fs-1" style="font-size: 80px !important;"></i>
                                        </div>
                                    </template>
                                </div>

                                <!-- Info Bar -->
                                <div class="bg-light rounded-3 p-4 mb-5 d-flex flex-column flex-md-row justify-content-between align-items-center" style="background-color: #f8f9fa !important;">
                                    <div class="d-flex align-items-center">
                                        <div class="me-4">
                                            <span class="text-black fw-bold fs-5 me-1" x-text="selectedPlan.dias_count"></span>
                                            <span class="text-black fs-5" x-text="selectedPlan.dias_count == 1 ? 'día' : 'días'"></span>
                                        </div>
                                        <div class="d-flex align-items-center border-start ps-4">
                                            <i class="ti ti-star-filled text-warning me-2 fs-4"></i>
                                            <span class="text-black fw-bold fs-5" x-text="parseFloat(selectedPlan.calificacion || 0).toFixed(1)"></span>
                                        </div>
                                    </div>
                                    <div class="mt-3 mt-md-0">
                                        <button type="button" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold d-flex align-items-center" 
                                                style="background-color: #2d3436; border-color: #2d3436; color: white;"
                                                @click="$wire.comenzarPlan(selectedPlan.id); bootstrap.Modal.getInstance(document.getElementById('modalDetallePlanExplorar')).hide();">
                                            Comenzar plan <i class="ti ti-chevron-right ms-2"></i>
                                        </button>
                                    </div> 
                                </div>              

                                <!-- Descripción -->
                                <div class="description-content px-1 mt-2 text-black fs-6" style="line-height:1.0;" x-html="selectedPlan.descripcion"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    <style>
        .card-plan-explorar {
            transition: transform 0.2s ease, shadow 0.2s ease;
            cursor: pointer;
        }
        .card-plan-explorar:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }
    </style>

    @assets
        @vite(['resources/assets/vendor/libs/swiper/swiper.scss', 'resources/assets/vendor/libs/swiper/swiper.js'])
    @endassets

    @script
    <script>
        $wire.on('cerrarModalSeleccion', () => {
            // Cerrar instancias si aún existen
            const modalEl = document.getElementById('modalSeleccionarPlan');
            if(modalEl) {
                const modalInst = bootstrap.Modal.getInstance(modalEl);
                if(modalInst) modalInst.hide();
            }
            
            const modalDetalle = document.getElementById('modalDetallePlanExplorar');
            if(modalDetalle) {
                const modalDetalleInst = bootstrap.Modal.getInstance(modalDetalle);
                if(modalDetalleInst) modalDetalleInst.hide();
            }

            // Forzar limpieza del DOM ya que Livewire destruye el HTML del modal 
            // y Bootstrap deja el backdrop 'huérfano'
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 100);
        });

        Alpine.data('swiperComponent', () => ({
            swiper: null,
            categoriaSeleccionada: @entangle('categoriaSeleccionada'),
            
            initSwiper(el) {
                if (typeof Swiper !== 'undefined') {
                this.swiper = new Swiper(el, {
                    slidesPerView: 'auto',
                    spaceBetween: 10,
                    freeMode: true,
                    mousewheel: {
                        forceToAxis: true
                    },
                    grabCursor: true,
                    observer: true,
                    observeParents: true
                });
                }
            },

            seleccionarCategoria(id) {
                this.$wire.seleccionarCategoria(id);
            }
        }));
    </script>
    @endscript
</div>
