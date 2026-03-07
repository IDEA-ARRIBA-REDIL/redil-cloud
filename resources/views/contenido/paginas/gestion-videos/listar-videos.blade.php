@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gestión de Videos')

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // 1. Inicializar Flatpickr
        $(".flatpickr-date").flatpickr({
            dateFormat: "Y-m-d",
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febreo', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                },
            }
        });

        // 2. Buscador
        const buscarInput = document.getElementById('buscar');
        const formularioBuscar = document.getElementById('formBuscar');
        let timeoutId;

        if(buscarInput){
            buscarInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                if (this.value.length >= 3 || this.value.length == 0) {
                    timeoutId = setTimeout(() => {
                        formularioBuscar.submit();
                    }, 1000);
                }
            });
        }

        // 3. Funciones Globales
        window.crearVideo = function() {
            document.getElementById('modalTitle').innerText = 'Nuevo video';
            let form = document.getElementById('formVideo');
            form.action = "{{ route('gestion-videos.crearVideos') }}";
            form.reset();

            // Limpiar input hidden de PUT si existe
            let methodInput = document.getElementById('methodPut');
            if(methodInput) methodInput.remove();

            var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVideo'));
            myModal.show();
        }

        window.editarVideo = function(id, nombre, url, fecha, visible) {
            document.getElementById('modalTitle').innerText = 'Editar Video';
            let form = document.getElementById('formVideo');
            // Ajustamos la acción a la ruta de actualizar concatenando el ID
            form.action = "{{ route('gestion-videos.actualizarVideos', '') }}/" + id;

            // Simulación PUT
            let methodInput = document.getElementById('methodPut');
            if(!methodInput){
                methodInput = document.createElement('input');
                methodInput.setAttribute('type', 'hidden');
                methodInput.setAttribute('name', '_method');
                methodInput.setAttribute('value', 'PUT');
                methodInput.setAttribute('id', 'methodPut');
                form.appendChild(methodInput);
            }

            // Asignar valores
            document.getElementById('nombre').value = nombre;
            document.getElementById('url_video').value = url;
            document.getElementById('visible').value = visible;

            // Asignar fecha al Flatpickr
            const fp = document.querySelector("#fecha_publicacion")._flatpickr;
            if(fp && fecha) fp.setDate(fecha);

            var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVideo'));
            myModal.show();
        }

        window.confirmarEliminacion = function(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    });
</script>
@endsection

@section('content')
<h4 class="mb-1 fw-semibold text-primary">Gestión de videos</h4>
<p class="text-muted">Administra los enlaces a videos de YouTube o externos.</p>

<form id="formBuscar" class="forms-sample" method="GET" action="{{ route('gestion-videos.listarVideos') }}">
    <div class="row mt-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="input-group input-group-merge bg-white">
                <input id="buscar" name="buscar" type="text" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar por nombre..." aria-describedby="btnBusqueda">
                <span class="input-group-text"><i class="ti ti-search"></i></span>
            </div>
        </div>
        <div class="col-12 col-md-6 d-flex justify-content-end mt-3 mt-md-0">
            <button type="button" class="btn btn-primary waves-effect waves-light" onclick="crearVideo()">
                <i class="ti ti-plus me-1"></i> Nuevo video
            </button>
        </div>
    </div>
</form>

<div class="row equal-height-row g-4">
    @if($videos->count() > 0)
        @foreach($videos as $video)
        @php
            // Lógica simple para intentar sacar thumbnail de YouTube
            $thumbnail = 'https://img.youtube.com/vi/ERROR/hqdefault.jpg';
            $esYoutube = false;

            if (str_contains($video->url_video, 'youtube.com/watch?v=')) {
                $parts = parse_url($video->url_video);
                parse_str($parts['query'], $query);
                if(isset($query['v'])) {
                    $thumbnail = 'https://img.youtube.com/vi/'.$query['v'].'/hqdefault.jpg';
                    $esYoutube = true;
                }
            } elseif (str_contains($video->url_video, 'youtu.be/')) {
                $path = parse_url($video->url_video, PHP_URL_PATH);
                $videoId = substr($path, 1);
                $thumbnail = 'https://img.youtube.com/vi/'.$videoId.'/hqdefault.jpg';
                $esYoutube = true;
            } else {
                // Imagen genérica si no es youtube
                $thumbnail = asset('assets/img/elements/1.jpg'); // Asegúrate de tener una imagen por defecto
            }
        @endphp

        <div class="col-12 col-sm-6 col-lg-4 col-xl-3 equal-height-col">
            <div class="card h-100 border rounded shadow-sm">
                <div class="position-relative">
                    <img class="card-img-top object-fit-cover" style="height: 180px; width: 100%;"
                         src="{{ $esYoutube ? $thumbnail : 'https://placehold.co/600x400?text=Video' }}"
                         alt="{{ $video->nombre }}" />

                    <span class="badge position-absolute top-0 end-0 m-2 {{ $video->visible ? 'bg-label-success' : 'bg-label-secondary' }}">
                        {{ $video->visible ? 'Visible' : 'Oculto' }}
                    </span>

                    <a href="{{ $video->url_video }}" target="_blank" class="btn btn-icon btn-primary position-absolute top-50 start-50 translate-middle rounded-circle shadow-sm" style="width: 50px; height: 50px;">
                        <i class="ti ti-player-play-filled fs-2"></i>
                    </a>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0 text-truncate" title="{{ $video->nombre }}">
                            {{ $video->nombre }}
                        </h5>

                        <div class="dropdown zindex-2">
                            <button type="button" class="btn dropdown-toggle hide-arrow btn-sm p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical fs-4"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);"
                                       onclick="editarVideo(
                                           '{{$video->id}}',
                                           '{{$video->nombre}}',
                                           '{{$video->url_video}}',
                                           '{{$video->fecha_publicacion ? $video->fecha_publicacion->format('Y-m-d') : ''}}',
                                           '{{$video->visible}}'
                                       )">
                                        <i class="ti ti-pencil me-1"></i> Editar
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmarEliminacion('{{$video->id}}')">
                                        <i class="ti ti-trash me-1"></i> Eliminar
                                    </a>
                                    <form id="delete-form-{{ $video->id }}" action="{{ route('gestion-videos.eliminarVideos', $video->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center text-muted">
                            <i class="ti ti-calendar me-2"></i>
                            <small>{{ $video->fecha_publicacion ? $video->fecha_publicacion->format('d/m/Y') : 'Sin fecha' }}</small>
                        </div>
                        <div class="d-flex align-items-center text-muted">
                            <i class="ti ti-link me-2"></i>
                            <small class="text-truncate" style="max-width: 150px;">{{ $video->url_video }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="col-12 mt-5 text-center">
            <i class="ti ti-video-off ti-xl text-muted mb-2"></i>
            <p class="text-muted">No se encontraron videos.</p>
        </div>
    @endif
</div>

<div class="row my-4">
  <div class="col-12">
    @if($videos->hasPages())
        {{ $videos->appends(request()->input())->links() }}
    @endif
  </div>
</div>

<div class="modal fade" id="modalVideo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="modalTitle" class="modal-title fw-bold text-primary">Nuevo video</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formVideo" method="POST">
          @csrf

          <div class="mb-3">
            <label class="form-label" for="nombre">Nombre del video</label>
            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej: Resumen del evento..." required />
          </div>

          <div class="mb-3">
            <label class="form-label" for="url_video">URL del video (YouTube / MP4)</label>
            <input type="url" id="url_video" name="url_video" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required />
            <div class="form-text">Copia y pega el enlace completo.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="fecha_publicacion">Fecha de publicación</label>
            <input type="text" id="fecha_publicacion" name="fecha_publicacion" class="form-control flatpickr-date" placeholder="YYYY-MM-DD" required />
          </div>

          <div class="mb-4">
            <label class="form-label" for="visible">Estado</label>
            <select id="visible" name="visible" class="form-select">
                <option value="1">Visible</option>
                <option value="0">Oculto</option>
            </select>
          </div>

          <div class="modal-footer mt-3">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

@endsection
