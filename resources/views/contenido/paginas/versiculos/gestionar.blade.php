@extends('layouts/layoutMaster')

@section('title', 'Gestionar Versículos Diarios')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
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
  .btn-close-video {
    z-index: 2000 !important;
    filter: invert(1) grayscale(100%) brightness(200%);
    background-color: rgba(0,0,0,0.5);
    border-radius: 50%;
    padding: 10px;
  }
  /* Backdrop personalizado para una experiencia de reflexión premium */
  .modal-backdrop.show {
    opacity: 0.8 !important;
    background-color: #000 !important;
    backdrop-filter: blur(4px);
  }
</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endsection

@section('page-script')
<script type="module">
  $(function () {
    // Modal de Versículo
    const modalVersiculo = document.getElementById('modalVersiculo');
    if (modalVersiculo) {
        modalVersiculo.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const cita = button.getAttribute('data-cita');
            const contenido = button.getAttribute('data-contenido');
            
            document.getElementById('modalVersiculoCita').innerText = cita;
            document.getElementById('modalVersiculoTexto').innerHTML = contenido || 'Sin contenido de versículo.';
        });
    }

    // Flatpickr para rango de fechas
    const flatpickrRange = document.querySelector('#fecha_rango');
    if (flatpickrRange) {
      flatpickrRange.flatpickr({
        mode: 'range',
        dateFormat: 'Y-m-d',
        defaultDate: ['{{ $fechaInicio }}', '{{ $fechaFin }}'],
        onClose: function(selectedDates, dateStr, instance) {
          if (selectedDates.length === 2) {
            const start = instance.formatDate(selectedDates[0], 'Y-m-d');
            const end = instance.formatDate(selectedDates[1], 'Y-m-d');
            $('#fecha_inicio').val(start);
            $('#fecha_fin').val(end);
            
            // Espera un momento antes de enviar
            setTimeout(() => {
              $('#filter-form').submit();
            }, 600);
          }
        }
      });
    }

    // Modal Video
    const modalVideo = document.getElementById('modalVideo');
    const videoIframe = document.getElementById('videoIframe');

    if (modalVideo) {
      modalVideo.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        let url = button.getAttribute('data-url');
        const ratioContainer = document.getElementById('ratioContainer');
        const modalDialog = modalVideo.querySelector('.modal-dialog');
        
        let isVertical = false;

        // Detectar si es Shorts o Reels (o si la URL indica verticalidad)
        if (url.includes('shorts') || url.includes('reels') || url.includes('tiktok.com')) {
            isVertical = true;
        }

        // Convertir YouTube
        if (url.includes('youtube.com/watch?v=')) {
          url = url.replace('watch?v=', 'embed/');
        } else if (url.includes('youtu.be/')) {
          const videoId = url.split('/').pop().split('?')[0];
          url = `https://www.youtube.com/embed/${videoId}`;
        } else if (url.includes('youtube.com/shorts/')) {
          url = url.replace('shorts/', 'embed/');
          isVertical = true;
        }
        
        // Convertir Vimeo
        if (url.includes('vimeo.com/')) {
          // Manejar vimeo.com/ID o vimeo.com/channels/staffpicks/ID
          const parts = url.split('/');
          const vimeoId = parts[parts.length - 1].split('?')[0];
          url = `https://player.vimeo.com/video/${vimeoId}`;
        }

        // Ajustar Ratio y Ancho
        if (isVertical) {
            ratioContainer.classList.remove('ratio-16x9');
            ratioContainer.classList.add('ratio-9x16');
            modalDialog.style.maxWidth = '400px';
        } else {
            ratioContainer.classList.remove('ratio-9x16');
            ratioContainer.classList.add('ratio-16x9');
            modalDialog.style.maxWidth = '800px';
        }
        
        // Parámetros de Autoplay
        const separator = url.includes('?') ? '&' : '?';
        const autoplayParam = url.includes('vimeo') ? 'autoplay=1&muted=1' : 'autoplay=1&rel=0';
        videoIframe.src = url + separator + autoplayParam;
      });

      modalVideo.addEventListener('hide.bs.modal', function () {
        videoIframe.src = '';
      });
    }

    // SweetAlert2 para eliminar
    $('.delete-record').on('click', function (e) {
      e.preventDefault();
      var form = $(this).closest('form');
      Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto! La imagen también se borrará físicamente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {
        if (result.value) {
          form.submit();
        }
      });
    });
  });

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
              const canvas = await html2canvas(document.getElementById(captureId), { scale: 3 });
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
</script>
@endsection

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
  <h4 class="mb-0 fw-semibold text-primary">Gestionar versículos diarios</h4>
  @if ($rolActivo->hasPermissionTo('versiculos.subitem_nuevo_versiculo'))
    <a href="{{ route('versiculos.create') }}" class="btn btn-primary rounded-pill px-12 py-2">
      <i class="ti ti-plus me-1"></i> Nuevo
    </a>
  @endif
</div>


<!-- Filtros -->
<form id="filter-form" action="{{ route('versiculos.index') }}" method="GET" class="row g-3 align-items-center mb-4">
  <div class="col-12 col-md-4">
    <label for="fecha_rango" class="form-label text-black fw-semibold text-uppercase" style="font-size: 0.75rem;">Filtrar por rango de fecha</label>
    <div class="input-group">
      <span class="input-group-text"><i class="ti ti-calendar"></i></span>
      <input type="text" id="fecha_rango" class="form-control" placeholder="Seleccionar rango" readonly>
    </div>
    <input type="hidden" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio }}">
    <input type="hidden" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin }}">
  </div>
</form>


@include('layouts.status-msn')

<!-- Listado de Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 g-4 mb-4">
  @forelse($versiculos as $versiculo)
  <div class="col">
    <div class="card h-100 shadow-sm border-0 overflow-hidden position-relative" style="border-radius: 15px;">
      
      <!-- Imagen Cuadrada o Texto si no hay imagen -->
      <div class="card-img-top position-relative overflow-hidden" style="width: 100%; height: 0; padding-bottom: 100%; background-color: #f8f9fa;">
        @if($versiculo->ruta_imagen)
          <img src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/versiculo-diario/'.$versiculo->ruta_imagen) }}" 
               alt="{{ $versiculo->cita_larga }}" 
               class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; object-position: center;">
        @else
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
              $gradientIndex = $versiculo->id % count($gradients);
              $selectedGradient = $gradients[$gradientIndex];
          @endphp
          <div id="capture-container-{{ $versiculo->id }}" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-4 text-center" style="background: {{ $selectedGradient }};">
            <div class="text-white">
                <i class="ti ti-bible mb-2 d-block opacity-75" style="font-size: 2rem;"></i>
                <p class="mb-0" style="font-size: 0.9rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden;">
                    @if(isset($versiculo->texto_versiculo) && is_array($versiculo->texto_versiculo))
                        @php
                            $fullText = "";
                            foreach($versiculo->texto_versiculo as $selection) {
                                foreach($selection['versiculos'] as $v) {
                                    $fullText .= $v['texto'] . " ";
                                }
                            }
                        @endphp
                        {{ trim($fullText) }}
                    @else
                        {{ $versiculo->cita_larga }}
                    @endif
                </p>
            </div> 
          </div>
        @endif 
      
      </div>

      <!-- Contenido Footer -->
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="d-flex flex-column ">
            <h5 class="card-title mb-0 fw-semibold text-black text-black" style="font-size: 1.1rem;">{{ $versiculo->cita_larga }}</h5>
            <small class="text-black"><i class="ti ti-calendar me-1 "></i> {{ $versiculo->fecha_publicacion->format('d M, Y') }}</small>
          </div>
          <!-- Menu de acciones aquí ahora -->

          <div class="dropdown zindex-2 p-1 float-end">
            <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item cursor-pointer" onclick="downloadImage('{{ $versiculo->ruta_imagen ? Storage::url($configuracion->ruta_almacenamiento.'/img/versiculo-diario/'.$versiculo->ruta_imagen) : '' }}', '{{ $versiculo->cita_larga }}', 'capture-container-{{ $versiculo->id }}')">
                <i class="ti ti-download me-1"></i> Descargar imagen
              </a>
              @if ($rolActivo->hasPermissionTo('versiculos.opcion_modificar_versiculo'))
                <a class="dropdown-item" href="{{ route('versiculos.edit', $versiculo) }}">
                  <i class="ti ti-pencil me-1"></i> Editar
                </a>
              @endif

              @if ($rolActivo->hasPermissionTo('versiculos.opcion_eliminar_versiculo'))
                <form action="{{ route('versiculos.destroy', $versiculo) }}" method="POST">
                  @csrf 
                  @method('DELETE')
                  <button type="submit" class="dropdown-item delete-record">
                    <i class="ti ti-trash me-1"></i> Eliminar
                  </button>
                </form> 
              @endif
            </ul>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            @if($versiculo->url_video_reflexion)
              <button type="button" 
                      class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-3 text-nowrap" 
                      data-bs-toggle="modal" 
                      data-bs-target="#modalVideo" 
                      data-url="{{ $versiculo->url_video_reflexion }}"
                      style="font-size: 0.75rem;">
                Ver reflexión
              </button>
            @endif
          </div>
          
          <div class="d-flex align-items-center gap-3 text-muted">
            @php
              $fullText = "";
              if(isset($versiculo->texto_versiculo) && is_array($versiculo->texto_versiculo)) {
                  foreach($versiculo->texto_versiculo as $selection) {
                      foreach($selection['versiculos'] as $v) {
                          $fullText .= "<strong>".$v['numero']."</strong> " . $v['texto'] . "<br>";
                      }
                  }
              }
            @endphp
            <button type="button" class="btn p-0 btn-ver-versiculo text-black" 
                    title="Ver versículo" 
                    data-bs-toggle="modal" 
                    data-bs-target="#modalVersiculo" 
                    data-cita="{{ $versiculo->cita_larga }}"
                    data-contenido="{{ $fullText }}">
              <i class="ti ti-book ti-sm cursor-pointer border-0"></i>
            </button>
            <div class="d-flex align-items-center gap-1 text-black">
              <i class="ti ti-heart ti-sm cursor-pointer @if($versiculo->usuariosQueDieronLike()->count() > 0) text-danger @endif"></i>
              <span style="font-size: 0.8rem;">{{ $versiculo->usuariosQueDieronLike()->count() }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
  @empty
  <div class="col-12 w-100 mt-5">
    <div class="text-center py-5">
      <i class="ti ti-clipboard-off ti-lg text-black mb-3 d-block" style="font-size: 4rem;"></i>
      <h5 class="text-black">No se encontraron versículos en este rango de fechas.</h5>
      <a href="{{ route('versiculos.create') }}" class="btn btn-primary mt-3 rounded-pill">Crear mi primer versículo</a>
    </div>
  </div>
  @endforelse
</div>

<!-- Paginación -->
<div class="d-flex justify-content-center mt-5">
  {{ $versiculos->appends(request()->input())->links() }}
</div>

<!-- Modal para Video Dinámico -->
<div class="modal fade modal-transparent" id="modalVideo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-0 px-10">
        <button type="button" class="btn-close btn-close-white btn-close-video position-absolute top-0 end-3 m-2 pe-10" data-bs-dismiss="modal" aria-label="Close"></button>
      </div> 
      <div class="modal-body p-5 position-relative">
        <div id="ratioContainer" class="ratio ratio-16x9 bg-black rounded-3 overflow-hidden shadow-lg border border-secondary border-opacity-25">
          <iframe id="videoIframe" src="" title="Video reflexión" allowfullscreen allow="autoplay; encrypted-media"></iframe>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Ver Versículo Completo -->
<div class="modal fade" id="modalVersiculo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header border-0 pb-0 justify-content-end">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center pt-0 pb-5 px-4 overflow-auto" style="max-height: 80vh;">
        
        <h4 class="fw-semibold text-black mb-0">Versículo del día</h4>
        <h5 class="text-primary fw-semibold mb-2" id="modalVersiculoCita">Cita Bíblica</h5>
        
        <div class="divider divider-dark px-5 mb-4">
            <div class="divider-text opacity-50">
                <i class="ti ti-bible fs-3 text-black"></i>
            </div>
        </div>

        <div id="modalVersiculoTexto" class="fs-6 px-md-5 text-black text-start" style="line-height: 1.6; font-style: italic;">
          <!-- El texto se cargará vía JS -->
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
