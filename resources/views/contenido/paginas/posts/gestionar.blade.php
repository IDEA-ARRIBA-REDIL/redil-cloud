@extends('layouts/layoutMaster')

@section('title', 'Gestionar Publicaciones')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('page-script')
<script type="module">
  $(function () {
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
            
            setTimeout(() => {
              $('#filter-form').submit();
            }, 600);
          }
        }
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
</script>
@endsection

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
  <h4 class="mb-0 fw-semibold text-primary">Gestionar publicaciones</h4>
  @if ($rolActivo->hasPermissionTo('posts.subitem_nueva_publicacion'))
    <a href="{{ route('posts.crear') }}" class="btn btn-primary rounded-pill px-12 py-2">
      <i class="ti ti-plus me-1"></i> Nueva
    </a>
  @endif
</div>

<!-- Filtros -->
<form id="filter-form" action="{{ route('posts.gestionar') }}" method="GET" class="row g-3 align-items-center mb-4">
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
  @forelse($posts as $post)
  <div class="col">
    <div class="card h-100 shadow-sm border-0 overflow-hidden position-relative" style="border-radius: 15px;">
      
      <!-- Imagen -->
      <div class="card-img-top position-relative overflow-hidden" style="width: 100%; height: 0; padding-bottom: 100%; background-color: #f8f9fa;">
        @if($post->image_path)
          <img src="{{ asset('storage/'.$configuracion->ruta_almacenamiento.'/img/publicaciones/'.$post->image_path) }}" 
               alt="Imagen de publicación" 
               class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; object-position: center;">
        @else
          <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted">
            <i class="ti ti-photo-off" style="font-size: 3rem;"></i>
          </div>
        @endif 
      </div>

      <!-- Contenido Footer -->
      <div class="card-body p-3">
        <div class="d-flex align-items-start justify-content-between mb-2">
          <div class="flex-fill d-flex flex-column">
            <div class="">
              <p class="card-text mb-1 text-black" style="font-size: 0.9rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {!! $post->descripcion ?: 'Sin descripción' !!}
              </p>
            </div>
              <small class="text-black"><i class="ti ti-user me-1"></i> {{ $post->user->nombre(3) }}</small>
              <small class="text-black">
                <i class="ti ti-calendar me-1"></i> 
                {{ $post->fecha_inicio ? $post->fecha_inicio->format('d M, Y') : 'Sin fecha' }} 
                {{ ($post->visualizar_siempre || !$post->fecha_fin) ? '' : ' a '.$post->fecha_fin->format('d M, Y') }}
              </small>
          </div>

          
          


          <div class="dropdown zindex-2 p-1">
            <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
            <ul class="dropdown-menu dropdown-menu-end">
              @if($post->image_path)
                <a class="dropdown-item" href="{{ asset('storage/'.$configuracion->ruta_almacenamiento.'/img/publicaciones/'.$post->image_path) }}" download="post-{{ $post->id }}.png">
                  <i class="ti ti-download me-1"></i> Descargar imagen
                </a>
              @endif
              @if ($rolActivo->hasPermissionTo('posts.opcion_modificar_publicacion'))
                <a class="dropdown-item" href="{{ route('posts.edit', $post) }}">
                  <i class="ti ti-pencil me-1"></i> Editar
                </a>
              @endif
              <form action="{{ route('posts.destroy', $post) }}" method="POST">
                @csrf 
                @method('DELETE')
                <button type="submit" class="dropdown-item delete-record">
                  <i class="ti ti-trash me-1"></i> Eliminar
                </button>
              </form> 
            </ul>
          </div>
        </div>
        
        

        <div class="d-flex align-items-center justify-content-end gap-3 text-muted">
          <div class="d-flex align-items-center gap-1 text-black">
            <i class="ti ti-heart ti-sm @if($post->likes->count() > 0) text-danger @endif"></i>
            <span style="font-size: 0.8rem;">{{ $post->likes->count() }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12 w-100 mt-5">
    <div class="text-center py-5">
      <i class="ti ti-news-off ti-lg text-black mb-3 d-block" style="font-size: 4rem;"></i>
      <h5 class="text-black">No se encontraron publicaciones.</h5>
      <a href="{{ route('posts.crear') }}" class="btn btn-primary mt-3 rounded-pill">Crear mi primera publicación</a>
    </div>
  </div>
  @endforelse
</div>

<!-- Paginación -->
<div class="d-flex justify-content-center mt-5">
  {{ $posts->appends(request()->input())->links() }}
</div>

@endsection
