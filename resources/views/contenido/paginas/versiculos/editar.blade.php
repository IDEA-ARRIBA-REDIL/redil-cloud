@extends('layouts/layoutMaster')

@section('title', 'Editar Versículo Diario')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')
    <script type="module">
        $(function() {
            'use strict';

            const flatpickrDate = document.querySelector('#fecha_publicacion');
            if (flatpickrDate) {
                flatpickrDate.flatpickr({
                    monthSelectorType: 'static',
                    disable: @json($fechasOcupadas ?? [])
                });
            }

            $(".select2").select2();

            // Renderizar previsualización inicial si hay datos
            @if ($versiculo->texto_versiculo)
                const initialData = @json($versiculo->texto_versiculo);
                if (Array.isArray(initialData)) {
                    renderizarPrevisualizacionVersiculo(initialData[0]);
                } else {
                    renderizarPrevisualizacionVersiculo(initialData);
                }
            @endif

            // Sincronización con el selector de Biblia
            Livewire.on('bibliaSeleccionada', (data) => {
                if (data && data.length > 0) {
                    const selection = data[0];
                    document.getElementById('version_uri').value = selection.version;
                    document.getElementById('libro_nombre').value = selection.libro;
                    document.getElementById('cita_referencia').value = selection.cita;

                    document.getElementById('texto_versiculo').value = JSON.stringify(data);
                    renderizarPrevisualizacionVersiculo(selection);
                } else if (data && typeof data === 'object' && data.versiculos) {
                    document.getElementById('version_uri').value = data.version;
                    document.getElementById('libro_nombre').value = data.libro;
                    document.getElementById('cita_referencia').value = data.cita;

                    document.getElementById('texto_versiculo').value = JSON.stringify([data]);
                    renderizarPrevisualizacionVersiculo(data);
                }
            });

            function renderizarPrevisualizacionVersiculo(data) {
                const placeholder = document.getElementById('placeholder-versiculo');
                const content = document.getElementById('content-versiculo');
                const citation = document.getElementById('preview-cita');
                const versesText = document.getElementById('preview-texto-versiculos');

                if (data && data.versiculos && data.versiculos.length > 0) {
                    placeholder.classList.add('d-none');
                    content.classList.remove('d-none');

                    citation.innerHTML = data.cita;

                    let htmlVerses = "";
                    data.versiculos.forEach(v => {
                        htmlVerses += `<sup class="text-primary fw-bold me-1">${v.numero}</sup>${v.texto} `;
                    });
                    versesText.innerHTML = htmlVerses.trim();
                } else {
                    placeholder.classList.remove('d-none');
                    content.classList.add('d-none');
                }
            }

            // Lógica de Cropper
            var croppingImage = document.querySelector('#croppingImage'),
                cropBtn = document.querySelector('.crop'),
                croppedImg = document.querySelector('#preview-foto'),
                upload = document.querySelector('#cropperImageUpload'),
                inputResultado = document.querySelector('#imagen-recortada'),
                cropper = '';

            setTimeout(() => {
                cropper = new Cropper(croppingImage, {
                    zoomable: false,
                    aspectRatio: 9 / 16,
                    cropBoxResizable: true
                });
            }, 1000);

            upload.addEventListener('change', function(e) {
                if (e.target.files.length) {
                    var fileType = e.target.files[0].type;
                    if (fileType.includes('image/')) {
                        if (cropper && typeof cropper.destroy === 'function') {
                            cropper.destroy();
                        }
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (e.target.result) {
                                croppingImage.src = e.target.result;
                                cropper = new Cropper(croppingImage, {
                                    zoomable: false,
                                    aspectRatio: 9 / 16,
                                    cropBoxResizable: true
                                });
                            }
                        };
                        reader.readAsDataURL(e.target.files[0]);
                    } else {
                        alert('Selected file type is not supported.');
                    }
                }
            });

            cropBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let imgSrc = cropper.getCroppedCanvas({
                    width: 720
                }).toDataURL('image/jpeg');
                croppedImg.src = imgSrc;
                inputResultado.value = imgSrc;
            });

            $('#formulario').submit(function() {
                $('.btnGuardar').attr('disabled', 'disabled');

                Swal.fire({
                    title: "Espera un momento",
                    text: "Ya estamos guardando...",
                    icon: "info",
                    showCancelButton: false,
                    showConfirmButton: false,
                    showDenyButton: false
                });
            });
        });
    </script>
@endsection

@section('content')
    <h4 class="mb-1 fw-semibold text-primary mb-4">Editar Versículo Diario</h4>

    @include('layouts.status-msn')

    <form action="{{ route('versiculos.update', $versiculo) }}" method="POST" enctype="multipart/form-data" id="formulario">
        @csrf
        @method('PUT')
        <div class="row mt-10">
            <div class="col-md-4">
                <div class="card mb-4">
                    <h5 class="card-header text-black fw-semibold">
                        Imagen
                    </h5>

                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-12 d-flex justify-content-center align-items-center flex-column">
                                <div class="position-relative d-inline-block">
                                    @php
                                        $imgUrl = $versiculo->ruta_imagen
                                            ? Storage::url(
                                                $configuracion->ruta_almacenamiento .
                                                    '/img/versiculo-diario/' .
                                                    $versiculo->ruta_imagen,
                                            )
                                            : asset('assets/img/illustrations/page-pricing-enterprise.png');
                                    @endphp
                                    <img id="preview-foto" src="{{ $imgUrl }}" alt="Preview" class="rounded border"
                                        style="width: 200px; aspect-ratio: 9/16; object-fit: cover;">
                                    <button type="button"
                                        class="btn btn-sm btn-icon btn-primary rounded-circle position-absolute bottom-0 end-0 mb-n2 me-n2"
                                        data-bs-toggle="modal" data-bs-target="#modalFoto">
                                        <i class="ti ti-camera"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="imagen-recortada" name="imagen_base64">
                                <small class="text-black d-block mt-3">Relación de aspecto recomendada 9:16</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-4">
                    <h5 class="card-header text-black fw-semibold">
                        Datos del versículo
                    </h5>

                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-12 col-12">
                                <label class="form-label">Cambiar Versículo de la Biblia (Opcional)</label>
                                @livewire('TiempoConDios.biblia', [
                                    'name_id' => 'referencia_biblica',
                                    'despacharEvento' => true,
                                ])

                                <input type="hidden" name="version_uri" id="version_uri"
                                    value="{{ $versiculo->version_uri }}">
                                <input type="hidden" name="libro_nombre" id="libro_nombre"
                                    value="{{ $versiculo->libro_nombre }}">
                                <input type="hidden" name="cita_referencia" id="cita_referencia"
                                    value="{{ $versiculo->cita_referencia }}">
                            </div>

                            <div class="mb-3 col-md-12 col-12">
                                <label class="form-label">Previsualización del Versículo</label>
                                <div class="border rounded p-3" id="preview-versiculo-panel" style="min-height: 100px;">
                                    <div id="placeholder-versiculo" class="text-black text-center py-4 d-none">
                                        Selecciona un versículo arriba para ver la previsualización...
                                    </div>
                                    <div id="content-versiculo">
                                        <h6 id="preview-cita" class="fw-bold mb-2 text-primary"></h6>
                                        <p id="preview-texto-versiculos" class="mb-0 text-dark"
                                            style="line-height: 1.6; text-align: justify;"></p>
                                    </div>
                                </div>
                                <input type="hidden" id="texto_versiculo" name="texto_versiculo"
                                    value="{{ json_encode($versiculo->texto_versiculo) }}">
                            </div>
                            <div class="mb-3 col-md-4 col-12">
                                <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                                <input type="text" class="form-control" id="fecha_publicacion" name="fecha_publicacion"
                                    value="{{ $versiculo->fecha_publicacion ? $versiculo->fecha_publicacion->format('Y-m-d') : '' }}"
                                    required />
                            </div>

                            <div class="mb-3 col-md-8 col-12">
                                <label for="url_video_reflexion" class="form-label">URL Video de Reflexión
                                    (YouTube/Vimeo)</label>
                                <input class="form-control" type="url" id="url_video_reflexion"
                                    name="url_video_reflexion" value="{{ $versiculo->url_video_reflexion }}" />
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- botonera -->
        <div class="d-flex mb-1 mt-5 text-end justify-content-start">
            <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2">
                <span class="align-middle me-sm-1 me-0 ">Guardar</span>
            </button>
        </div>
        <!-- /botonera -->
    </form>

    <!-- Modal Foto -->
    <div class="modal fade modal-img" id="modalFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-simple">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-camera ti-lg"></i> Subir Foto</h3>
                        <p class="text-muted">Selecciona y recorta la foto (9:16)</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Paso #1 Selecciona la foto</label>
                                <input class="form-control" type="file" id="cropperImageUpload" accept="image/*">
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">Paso #2 Recorta la foto</label>
                                <center>
                                    <img src="{{ Storage::url('generales/img/otros/placeholder.jpg') }}" class="w-100"
                                        id="croppingImage" alt="cropper">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-primary crop me-sm-3 me-1"
                            data-bs-dismiss="modal">Guardar</button>
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal"
                            aria-label="Close">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
