@extends('layouts/layoutMaster')

@section('title', 'Nueva publicación')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill-emoji@0.2.0/dist/quill-emoji.css">
    <style>
        /* Estilos para el editor Quill */
        .ql-toolbar.ql-snow {
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
            border-color: #dbdade !important;
            background-color: #f8f7fa;
        }

        .ql-container.ql-snow {
            border-bottom-left-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
            border-color: #dbdade !important;
        }

        .ql-editor {
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
            font-family: inherit;
            font-size: 0.9375rem;
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/quill/quill.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')
    <script>
        $(function() {
            'use strict';

            const flatpickrDateInicio = document.querySelector('#fecha_inicio');
            if (flatpickrDateInicio) {
                flatpickrDateInicio.flatpickr({
                    monthSelectorType: 'static',
                    defaultDate: 'today'
                });
            }

            const flatpickrDateFin = document.querySelector('#fecha_fin');
            if (flatpickrDateFin) {
                flatpickrDateFin.flatpickr({
                    monthSelectorType: 'static'
                });
            }

            $(".select2").select2();

            // Cargar quill-emoji dinámicamente para asegurar que window.Quill ya existe (por Vite)
            $.getScript('https://cdn.jsdelivr.net/npm/quill-emoji@0.2.0/dist/quill-emoji.js', function() {
                // Inicializar Quill Simple con Emojis
                const quillEditor = new Quill('#editor-descripcion', {
                    placeholder: 'Escribe aquí el contenido de la publicación...',
                    modules: {
                        toolbar: {
                            container: [
                                ['bold', 'italic', 'underline'],
                                [{
                                    'list': 'ordered'
                                }, {
                                    'list': 'bullet'
                                }],
                                ['link', 'emoji'],
                                ['clean']
                            ],
                        },
                        "emoji-toolbar": true,
                        "emoji-textarea": false,
                        "emoji-shortname": true,
                    },
                    theme: 'snow'
                });

                // Sincronizar Quill con el campo oculto
                quillEditor.on('text-change', function() {
                    $('#descripcion').val(quillEditor.root.innerHTML);
                });
            });

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
                    width: 1280
                }).toDataURL('image/jpeg');
                croppedImg.src = imgSrc;
                inputResultado.value = imgSrc;
            });

            $('#formulario').submit(function() {
                $('.btnGuardar').attr('disabled', 'disabled');

                Swal.fire({
                    title: "Espera un momento",
                    text: "Ya estamos guardando la publicación...",
                    icon: "info",
                    showCancelButton: false,
                    showConfirmButton: false,
                    showDenyButton: false
                });
            });

            // Alternar visualización de fecha fin
            $('#visualizar_siempre').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#fecha_fin_container').fadeOut();
                    $('#fecha_fin').val('');
                } else {
                    $('#fecha_fin_container').fadeIn();
                }
            });

            // Lógica de Restricciones
            function toggleRestricciones() {
                const isVisibleTodosChecked = $('#visible_todos').is(':checked');
                const seccion = $('#seccionRestricciones');

                if (isVisibleTodosChecked) {
                    seccion.hide();
                } else {
                    seccion.show();
                    // Reinicializar Select2 para asegurar que se vean bien dentro del div mostrado
                    setTimeout(() => {
                        seccion.find('.select2').each(function() {
                            if ($(this).data('select2')) {
                                $(this).select2('destroy');
                            }
                            $(this).select2();
                        });
                    }, 10);
                }
            }

            $('#visible_todos').on('change', toggleRestricciones);

            // Estado inicial de restricciones
            toggleRestricciones();

            let contadorPasos = 0;
            let contadorTareas = 0;

            $('#btn-agregar-paso').on('click', function() {
                contadorPasos++;
                const template = document.getElementById('templatePaso').innerHTML;
                const html = template.replace(/INDEX/g, contadorPasos);
                $('#contenedorPasos').append(html);
                // Inicializar Select2 en la nueva fila si es necesario
                $('#contenedorPasos .select2').last().select2();
            });

            $('#btn-agregar-tarea').on('click', function() {
                contadorTareas++;
                const template = document.getElementById('templateTarea').innerHTML;
                const html = template.replace(/INDEX/g, contadorTareas);
                $('#contenedorTareas').append(html);
                // Inicializar Select2 en la nueva fila si es necesario
                $('#contenedorTareas .select2').last().select2();
            });

            // Eliminar filas dinámicas (event delegation)
            $(document).on('click', '.btn-eliminar-fila', function() {
                $(this).closest('.row').remove();
            });
        });
    </script>
@endsection

@section('content')

    <h4 class="mb-1 fw-semibold text-primary">Nueva publicación</h4>

    @include('layouts.status-msn')

    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" id="formulario">
        @csrf
        <div class="row mt-10">
            <div class="col col-md-4 col-12 mb-4">
                <div class="card mb-4 shadow-sm h-100">
                    <h5 class="card-header text-black fw-semibold">
                        Imagen
                    </h5>

                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-12 d-flex justify-content-center align-items-center flex-column">
                                <div class="position-relative d-inline-block">
                                    <img id="preview-foto"
                                        src="{{ asset('assets/img/illustrations/page-pricing-enterprise.png') }}"
                                        alt="Preview" class="rounded border shadow-sm"
                                        style="width: 200px; aspect-ratio: 9/16; object-fit: cover;">
                                    <button type="button"
                                        class="btn btn-sm btn-icon btn-primary rounded-circle position-absolute bottom-0 end-0 mb-n2 me-n2 shadow"
                                        data-bs-toggle="modal" data-bs-target="#modalFoto">
                                        <i class="ti ti-camera"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="imagen-recortada" name="imagen_base64">
                                <small class="d-block mt-3 text-center">Relación de aspecto recomendada 9:16</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col col-md-8 col-12 mb-4">
                <div class="card mb-4 shadow-sm h-100">

                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-12">
                                <label for="editor-descripcion" class="form-label">Descripción</label>
                                <div id="editor-descripcion"></div>
                                <input type="hidden" name="descripcion" id="descripcion">
                            </div>

                            <div class="mb-3 col-12 d-flex align-items-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="visualizar_siempre"
                                        name="visualizar_siempre" value="1">
                                    <label class="form-check-label text-black" for="visualizar_siempre">
                                        <strong>Visualizar siempre</strong>
                                        <small class="d-block text-black">Si se activa, la publicación no tendrá fecha de
                                            vencimiento.</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3 col-md-6 col-12">
                                <label for="fecha_inicio" class="form-label">Inicio de visualización</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                        placeholder="YYYY-MM-DD" required />
                                </div>
                            </div>


                            <div class="mb-3 col-md-6 col-12" id="fecha_fin_container">
                                <label for="fecha_fin" class="form-label">Fin de visualización</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" class="form-control" id="fecha_fin" name="fecha_fin"
                                        placeholder="YYYY-MM-DD" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col col-12 mb-4">
                <div class="card border shadow-none mb-0">
                    <div class="card-header border-bottom py-2 cursor-pointer d-flex justify-content-between align-items-center"
                        data-bs-toggle="collapse" data-bs-target="#collapseRestricciones" aria-expanded="false"
                        aria-controls="collapseRestricciones">
                        <h5 class="card-header text-black fw-semibold">
                            Restricciones de visibilidad
                        </h5>
                        <i class="ti ti-chevron-down"></i>
                    </div>
                    <div id="collapseRestricciones" class="collapse">
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <!-- Visible para todos -->
                                <div class="col-12 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="visible_todos"
                                            name="visible_todos" checked>
                                        <label class="form-check-label w-100 cursor-pointer" for="visible_todos">
                                            <strong>Publicación visible para todos los usuarios</strong>
                                            <small class="d-block text-">Si se desactiva, solo los usuarios que cumplan los
                                                requisitos podrán verla.</small>
                                        </label>
                                    </div>
                                </div>

                                <div id="seccionRestricciones" style="display: none;">
                                    <hr class="my-3">
                                    <div class="row g-3">
                                        <!-- Género -->
                                        <div class="col-12">
                                            <label class="form-label text-black fw-bold">Género</label>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div class="form-check custom-option custom-option-basic">
                                                        <label
                                                            class="form-check-label custom-option-content d-flex justify-content-between align-items-center"
                                                            for="genero_masculino">
                                                            <span class="custom-option-header p-0 border-0">
                                                                <span class="fw-medium">Masculino</span>
                                                            </span>
                                                            <input name="genero" class="form-check-input" type="radio"
                                                                value="1" id="genero_masculino" />
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check custom-option custom-option-basic">
                                                        <label
                                                            class="form-check-label custom-option-content d-flex justify-content-between align-items-center"
                                                            for="genero_femenino">
                                                            <span class="custom-option-header p-0 border-0">
                                                                <span class="fw-medium">Femenino</span>
                                                            </span>
                                                            <input name="genero" class="form-check-input" type="radio"
                                                                value="2" id="genero_femenino" />
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check custom-option custom-option-basic">
                                                        <label
                                                            class="form-check-label custom-option-content d-flex justify-content-between align-items-center"
                                                            for="genero_ambos">
                                                            <span class="custom-option-header p-0 border-0">
                                                                <span class="fw-medium">Ambos</span>
                                                            </span>
                                                            <input name="genero" class="form-check-input" type="radio"
                                                                value="3" id="genero_ambos" checked />
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sedes -->
                                        <div class="col-12">
                                            <label class="form-label" for="sedes">Sedes permitidas</label>
                                            <select id="sedes" name="sedes[]" class="select2 form-select" multiple>
                                                @foreach ($sedes as $sede)
                                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Estados Civiles -->
                                        <div class="col-12">
                                            <label class="form-label" for="estadosCiviles">Estados civiles</label>
                                            <select id="estadosCiviles" name="estadosCiviles[]"
                                                class="select2 form-select" multiple>
                                                @foreach ($estadosCiviles as $estado)
                                                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Rangos de Edad -->
                                        <div class="col-12">
                                            <label class="form-label" for="rangosEdad">Rangos de edad</label>
                                            <select id="rangosEdad" name="rangosEdad[]" class="select2 form-select"
                                                multiple>
                                                @foreach ($rangosEdad as $rango)
                                                    <option value="{{ $rango->id }}">{{ $rango->nombre }}
                                                        ({{ $rango->edad_minima }}-{{ $rango->edad_maxima }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Tipos de Usuario -->
                                        <div class="col-12">
                                            <label class="form-label" for="tiposUsuario">Tipos de usuario</label>
                                            <select id="tiposUsuario" name="tiposUsuario[]" class="select2 form-select"
                                                multiple>
                                                @foreach ($tiposUsuario as $tipo)
                                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Pasos de Crecimiento Requisito -->
                                        <div class="col-12 mt-4">
                                            <label class="form-label fw-bold">Pasos de crecimiento</label>
                                            <div id="contenedorPasos">
                                                <!-- Filas dinámicas -->
                                            </div>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary mt-2 rounded-pill"
                                                id="btn-agregar-paso">
                                                <i class="ti ti-plus me-1"></i> Agregar paso
                                            </button>
                                        </div>

                                        <!-- Tareas Requisito -->
                                        <div class="col-12 mt-4">
                                            <label class="form-label fw-bold">Tareas de consolidación</label>
                                            <div id="contenedorTareas">
                                                <!-- Filas dinámicas -->
                                            </div>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary mt-2 rounded-pill"
                                                id="btn-agregar-tarea">
                                                <i class="ti ti-plus me-1"></i> Agregar tarea
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Templates para filas dinámicas -->
        <template id="templatePaso">
            <div class="row g-2 mt-1 align-items-center fila-paso">
                <div class="col-6 col-md-6">
                    <select name="pasos[INDEX][id]" class="form-select select-paso" required>
                        <option value="">Seleccionar Paso...</option>
                        @foreach ($pasosCrecimiento as $paso)
                            <option value="{{ $paso->id }}">{{ $paso->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-5 col-md-5">
                    <select name="pasos[INDEX][estado]" class="form-select select-estado-paso" required>
                        <option value="">Estado Requerido...</option>
                        @foreach ($estadosPasos as $estado)
                            <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-1 col-md-1 text-center">
                    <button type="button" class="btn btn-link text-danger p-0 btn-eliminar-fila">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </template>

        <template id="templateTarea">
            <div class="row g-2 mt-1 align-items-center fila-tarea">
                <div class="col-6 col-md-6">
                    <select name="tareas[INDEX][id]" class="form-select select-tarea" required>
                        <option value="">Seleccionar Tarea...</option>
                        @foreach ($tareasConsolidacion as $tarea)
                            <option value="{{ $tarea->id }}">{{ $tarea->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-5 col-md-5">
                    <select name="tareas[INDEX][estado]" class="form-select select-estado-tarea" required>
                        <option value="">Estado Requerido...</option>
                        @foreach ($estadosTareas as $estado)
                            <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-1 col-md-1 text-center">
                    <button type="button" class="btn btn-link text-danger p-0 btn-eliminar-fila">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </template>

        @push('page-script')
        @endpush

        <!-- botonera -->
        <div class="d-flex mb-1 mt-5">
            <div class="me-auto">
                <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2">
                    <span class="align-middle me-sm-1 me-0 ">Guardar</span>
                </button>
            </div>
        </div>
        <!-- /botonera -->
    </form>

    <!-- Modal Foto -->
    <div class="modal fade modal-img" id="modalFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-simple">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4 p-4">
                        <h3 class="mb-2"><i class="ti ti-camera ti-lg"></i> Subir foto</h3>
                        <p class="text-muted">Selecciona y recorta la foto para la publicación</p>
                    </div>

                    <div class="row px-4">
                        <div class="col-12">
                            <div class="mb-3">
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
                <div class="modal-footer border-0 p-4">
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-primary crop me-sm-3 me-1 px-5"
                            data-bs-dismiss="modal">Recortar y guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
