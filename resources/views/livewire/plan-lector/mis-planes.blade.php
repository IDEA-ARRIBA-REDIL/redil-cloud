@php
  $configData = Helper::appClasses();
  
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

<div x-data="{ 
    showModal: false
}">
  <!-- BLOQUE 1: Continuar Leyendo -->
  <div class="card">
    <div class="card-body rounded-3">
        <h5 class="fw-semibold text-black fs-4">Continuar leyendo</h5>
  
      
      <!-- Selector de estado estilo Botones (Pills) -->
      <div class="d-flex gap-3 mb-5 mt-3">
          <button type="button" 
                  class="btn btn-sm {{ $pestanaActiva === 'inscrito' ? 'btn-primary' : 'btn-outline-secondary btn-white text-dark shadow-sm border' }} px-4 rounded-pill" 
                  wire:click="cambiarPestana('inscrito')">
            Mis planes
          </button>
          
          <button type="button" 
                  class="btn btn-sm {{ $pestanaActiva === 'completado' ? 'btn-primary' : 'btn-outline-secondary btn-white text-dark shadow-sm border' }} px-4 rounded-pill" 
                  wire:click="cambiarPestana('completado')">
            Completados
          </button>
      </div>
    
      @if($planesInscritos->isEmpty())
        <div class="text-center text-black py-10">
          <p>Aún no hay planes en esta sección. ¡Explora los planes preparados para ti!</p>
        </div>
      @else
        <div class="row g-4 position-relative">
          <!-- Transición de carga -->
          <div wire:loading.flex wire:target="cambiarPestana, gotoPage, previousPage, nextPage" class="position-absolute w-100 h-100 justify-content-center align-items-center" style="background: rgba(255,255,255,0.7); z-index: 10;">
             <div class="spinner-border text-primary" role="status">
               <span class="visually-hidden">Loading...</span>
             </div>
          </div>

          @foreach($planesInscritos as $planInscrito)
          @php
            $urlImagenInscrito = $planInscrito->portada_url;
            $selectedGradientInscrito = $gradients[$planInscrito->id % count($gradients)];
          @endphp
          <div class="col-12 col-md-4">
            <div class="card shadow-none border h-100">
              @if($urlImagenInscrito)
                <img src="{{ $urlImagenInscrito }}" class="card-img-top" alt="Imagen del plan" style="height: 150px; object-fit: cover;">
              @else
                <div class="card-img-top d-flex align-items-center justify-content-center text-white" style="height: 150px; background: {{ $selectedGradientInscrito }};">
                  <i class="ti ti-book fs-1"></i>
                </div>
              @endif
              <div class="card-body d-flex flex-column">
                <h5 class="card-title fs-6 fw-semibold" title="{{ $planInscrito->titulo }}">{{ Str::limit($planInscrito->titulo, 45) }}</h5>
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-black">
                      @if($pestanaActiva === 'completado')
                        ¡Completado!
                      @else
                        {{ $planInscrito->pivot->porcentaje_progreso }}%
                      @endif
                    </small>
                    <small class="text-warning fw-bold d-flex align-items-center">
                      <i class="ti ti-star-filled me-1" style="font-size: 0.8rem;"></i>
                      {{ number_format($planInscrito->calificacion, 1) }}
                    </small>
                  </div>
                  <div class="progress" style="height: 8px;">
                    <div class="progress-bar {{ $pestanaActiva === 'completado' ? 'bg-success' : 'bg-primary' }}" 
                         role="progressbar" 
                         style="width: {{ $pestanaActiva === 'completado' ? 100 : $planInscrito->pivot->porcentaje_progreso }}%;"></div>
                  </div>
                </div>
                <div class="d-flex gap-2 flex-column flex-md-row mt-auto">
                  <a href="{{ route('planes-lectores.lectura', $planInscrito->slug) }}" class="btn btn-outline-primary rounded-pill flex-grow-1">
                    <span class="ti-xs ti ti-book-open me-1"></span>
                    {{ $pestanaActiva === 'completado' ? 'Repasar' : 'Continuar' }}
                  </a>
                  <button type="button" 
                          class="btn btn-outline-danger rounded-pill flex-grow-1"
                          title="Abandonar plan y borrar progreso"
                          @click="confirmarAbandono({{ $planInscrito->id }}, '{{ addslashes($planInscrito->titulo) }}')">
                    Abandonar
                  </button>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        
        <div class="mt-4 pagination-container">
          {{ $planesInscritos->links() }}
        </div>
      @endif
    </div>
  </div>

  <!-- BLOQUE 2: Explorador de Planes -->
  <div class="card mt-10">
    <div class="card-body rounded-3">
      <h5 class="fw-semibold text-black fs-4">Explorar planes lectores</h5>
      
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
          <!-- Usaremos Alpine.js y wire:ignore para que Livewire no rompa el slider -->
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

      <!-- Grid de Planes Disponibles -->
      <div class="row g-4 position-relative">
        <div wire:loading.flex wire:target="buscar, seleccionarCategoria, gotoPage, previousPage, nextPage" class="position-absolute w-100 h-100 justify-content-center align-items-center" style="background: rgba(255,255,255,0.7); z-index: 10;">
           <div class="spinner-border text-primary" role="status">
             <span class="visually-hidden">Loading...</span>
           </div>
        </div>

        @forelse($planesDisponibles as $plan)
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
                <form action="{{ route('planes-lectores.inscribirse', $plan->id) }}" method="POST" id="form-inscribirse-{{ $plan->id }}" class="d-none">
                  @csrf
                </form>
                <button type="button" 
                        class="btn btn-outline-primary btn-sm w-100 rounded-pill" 
                        @click="$dispatch('ver-plan', {{ Js::from([
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
        @empty
        <div class="col-12 text-center text-black py-10">
          <i class="ti ti-books mb-2" style="font-size: 3rem;"></i>
          <h5>No se encontraron planes disponibles</h5>
          <p>No hay planes nuevos que explorar o tal vez ya estas inscrito en ellos.</p>
        </div>
        @endforelse
      </div>
      
      <div class="mt-4 text-black pagination-container">
        {{ $planesDisponibles->links() }}
      </div>
    </div>
  </div>

  @assets
    @vite(['resources/assets/vendor/libs/swiper/swiper.scss', 'resources/assets/vendor/libs/swiper/swiper.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @endassets

  @script
  <script>
    window.confirmarAbandono = function(planId, titulo) {
        Swal.fire({
            title: '¿Abandonar plan?',
            text: `Se eliminará todo tu progreso en "${titulo}". Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea5455',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Sí, borrar progreso',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.abandonarPlan(planId);
            }
        });
    }

    $wire.on('alert', (data) => {
        Swal.fire({
            icon: data.icon || 'success',
            title: data.title || '¡Listo!',
            text: data.text || '',
            timer: 3000,
            showConfirmButton: false
        });
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

  <!-- Modal de Vista Previa -->
  @teleport('body')
  <div class="modal fade" id="modalDetallePlan" tabindex="-1" aria-hidden="true"
       x-data="{ selectedPlan: null }"
       x-on:ver-plan.window="selectedPlan = $event.detail; (new bootstrap.Modal($el)).show();">
    <div class="modal-dialog modal-lg modal-dialog-centered" x-show="selectedPlan" x-cloak>
      <div class="modal-content border-0 shadow-lg">
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
              <div class="bg-light rounded-3 p-4 mb-5 d-flex flex-column flex-md-row justify-content-between align-items-center">
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
                           @click="document.getElementById('form-inscribirse-' + selectedPlan.id).submit()">
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

  <style>
    .card-plan-explorar {
      transition: transform 0.2s ease, shadow 0.2s ease;
      cursor: pointer;
    }
    .card-plan-explorar:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
    .modal-content {
      border-radius: 20px;
    }
    #modalDetallePlan .bg-light {
      background-color: #f8f9fa !important;
    }
    #modalDetallePlan .btn-dark {
      background-color: #2d3436;
      border-color: #2d3436;
    }

    /* Ajuste responsivo para la paginación (Ocultar texto Anterior/Siguiente en móviles) */
    @media (max-width: 768px) {
      .pagination .page-item .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0;
        border-radius: 50% !important;
        margin: 0 2px;
        font-size: 0; /* Oculta el texto */
      }
      
      .pagination .page-item:first-child .page-link::before {
        content: "\ea60"; /* ti-chevron-left */
        font-family: 'tabler-icons' !important;
        font-size: 1.25rem;
      }
      
      .pagination .page-item:last-child .page-link::after {
        content: "\ea61"; /* ti-chevron-right */
        font-family: 'tabler-icons' !important;
        font-size: 1.25rem;
      }

      /* Asegurar que los números de página (si aparecen) se sigan viendo */
      .pagination .page-item:not(:first-child):not(:last-child) .page-link {
        font-size: 0.9rem;
      }
      
      /* Ocultar el texto de "Mostrando X de Y" en móviles si ocupa mucho espacio, o dejarlo centrado */
      .pagination-container .text-muted {
        display: block;
        text-align: center;
        margin-bottom: 10px;
      }
    }
  </style>

  <script>
    // Hacks adicionales de JS si fueran necesarios fuera del scope de Livewire
  </script>

</div>
