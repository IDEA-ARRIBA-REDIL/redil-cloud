@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Nuevo Grado Escuela')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')
    <script type="module">
        // Inicialización del editor de texto enriquecido (Quill) para la descripción
        const editor = new Quill('#editor', {
            bounds: '#editor',
            placeholder: 'Descripción del grado',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{
                        'header': 1
                    }, {
                        'header': 2
                    }],
                    [{
                        'color': []
                    }, {
                        'background': []
                    }],
                    [{
                        'align': []
                    }],
                    [{
                        'size': ['small', false, 'large', 'huge']
                    }],
                    [{
                        'header': [1, 2, 3, 4, 5, 6, false]
                    }],
                    [{
                        'font': []
                    }],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }, {
                        'list': 'check'
                    }],
                    [{
                        'indent': '-1'
                    }, {
                        'indent': '+1'
                    }],
                    ['link', 'image', 'video'],
                    ['clean']
                ],
            },
            theme: 'snow'
        });

        // Cargamos contenido previo si existe (por validación fallida)
        editor.root.innerHTML = "{!! old('descripción', '') !!}";

        // Actualizamos el campo oculto cuando cambia el contenido del editor
        editor.on('text-change', (delta, oldDelta, source) => {
            $('#descripcion').val(editor.root.innerHTML);
        });
    </script>

    <script type="module">
        $(function() {
            'use strict';

            // Lógica para el recorte de imagen de portada (Cropper.js)
            var croppingImage = document.querySelector('#croppingImage'),
                cropBtn = document.querySelector('.crop'),
                croppedImg = document.querySelector('.cropped-img'),
                upload = document.querySelector('#cropperImageUpload'),
                inputResultado = document.querySelector('#imagen-recortada'),
                cropper = '';

            setTimeout(() => {
                if (croppingImage.complete && croppingImage.naturalHeight !== 0) {
                    cropper = new Cropper(croppingImage, {
                        zoomable: false,
                        aspectRatio: 1693 / 376,
                        cropBoxResizable: true
                    });
                } else {
                    croppingImage.onload = function() {
                        cropper = new Cropper(croppingImage, {
                            zoomable: false,
                            aspectRatio: 1693 / 376,
                            cropBoxResizable: true
                        });
                    };
                }
            }, 500);

            upload.addEventListener('change', function(e) {
                if (e.target.files.length) {
                    var fileType = e.target.files[0].type;
                    if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
                        if (cropper) {
                            cropper.destroy();
                        }
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            if (ev.target.result) {
                                croppingImage.src = ev.target.result;
                                cropper = new Cropper(croppingImage, {
                                    zoomable: false,
                                    aspectRatio: 1693 / 376,
                                    cropBoxResizable: true
                                });
                            }
                        };
                        reader.readAsDataURL(e.target.files[0]);
                    } else {
                        alert('Tipo de archivo no soportado. Por favor intente de nuevo.');
                    }
                }
            });

            cropBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (cropper) {
                    let imgSrc = cropper
                        .getCroppedCanvas({
                            height: 376,
                            width: 1693
                        })
                        .toDataURL();
                    croppedImg.src = imgSrc;
                    inputResultado.value = imgSrc;
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Inicialización de Select2
            $('.select2').select2({
                placeholder: 'Seleccionar opciones',
                allowClear: true
            });

            // --- INICIO: Lógica para campos de límite de reporte ---
            const switchDiaLimite = $('#diaLimiteHabilitado');
            const selectDia = $('#dia');
            const inputCantidadReportesSemana = $('#cantidadReportesSemana');
            const inputDiasPlazoReporte = $('#diasPlazoReporte');

            function actualizarCamposLimiteReporte() {
                if (switchDiaLimite.is(':checked')) {
                    selectDia.prop('disabled', false).prop('required', true);
                    inputCantidadReportesSemana.prop('disabled', true).prop('required', false).val('');
                    inputDiasPlazoReporte.prop('disabled', true).prop('required', false).val('');
                } else {
                    selectDia.prop('disabled', true).prop('required', false).val('').trigger('change');
                    inputCantidadReportesSemana.prop('disabled', false).prop('required', true);
                    inputDiasPlazoReporte.prop('disabled', false).prop('required', true);
                }
            }

            actualizarCamposLimiteReporte();
            switchDiaLimite.on('change', actualizarCamposLimiteReporte);

            // Toggles visibility
            $('#togglehabilitarAsistencias').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.row-asistencias').removeClass('d-none').show();
                } else {
                    $('.row-asistencias').addClass('d-none').hide();
                }
            });

            $('#togglehabilitarInasistencias').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#containesAsistenciasAlerta').removeClass('d-none').show();
                } else {
                    $('#containesAsistenciasAlerta').addClass('d-none').hide();
                }
            });

            // Validación del formulario antes de enviar
            $('#formNuevoNivel').on('submit', function(e) {
                let errors = [];

                if (switchDiaLimite.is(':checked') && !selectDia.val()) {
                    errors.push('Debe seleccionar un día límite para el reporte.');
                }

                if (!$('#togglehabilitarCalificaciones').is(':checked') &&
                    !$('#togglehabilitarAsistencias').is(':checked')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de configuración',
                        text: 'Debe habilitar al menos Calificaciones o Asistencias',
                        confirmButtonText: 'Entendido'
                    });
                    e.preventDefault();
                    return false;
                }

                if (errors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errores de validación',
                        html: errors.join('<br>'),
                        confirmButtonText: 'Entendido'
                    });
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endsection

@section('content')
    <form id="formNuevoNivel" action="{{ route('niveles-escuelas.guardar', $escuela) }}" method="POST">
        @csrf
        @method('POST')

        <div class="col-md-12">
            <div class="card mb-4 rounded rounded-3">
                <img id="preview-foto" class="cropped-img card-img-top mb-1"
                    src="{{ old('foto') ? old('foto') : Storage::url($configuracion->ruta_almacenamiento . '/img/niveles/default.png') }}"
                    alt="Portada {{ $escuela->nombre }}">
                <button type="button" style="background-color: rgba(255, 255, 255, 0.5);"
                    class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2"
                    data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;"
                        class="ti ti-camera"></i></button>
                <input class="form-control d-none" type="text" value="{{ old('foto', '') }}" id="imagen-recortada"
                    name="foto">

                <div class="row p-4 m-0 d-flex card-body">
                    <h5 class="mb-1 fw-semibold text-black">Crear Grado Escuela</h5>
                    <p class=" text-black">Aquí podrás crear la información de su grado.</p>
                </div>
            </div>
        </div>

        @include('layouts.status-msn')

        <div class="row equal-height-row ">
            <div class="col mb-3 equal-height-col col-12 ">
                <div class="card h-100 p-6">
                    <h5 class="mb-4">Configuración inicial</h5>
                    <div class="row ">
                        <div class="mb-3 col-12 col-md-6  col-sm-12">
                            <label for="nombre" class="form-label">Nombre del grado</label>
                            <input value="{{ old('nombre', '') }}" type="text"
                                class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6 col-sm-12 ">
                            <label class="form-label">¿Habilitar asistencia?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="togglehabilitarAsistencias"
                                    name="habilitarAsistencias" @checked(old('habilitarAsistencias')) />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="row-asistencias @if (!old('habilitarAsistencias')) d-none @endif">
                        <div class="row">
                            <div id="containesAsistenciasMinimas" class="mb-3 col-md-6 col-sm-12">
                                <label for="asistenciasMinimas" class="form-label">Asistencias mínimas (opcional)</label>
                                <input value="{{ old('asistenciasMinimas', '') }}" type="number"
                                    class="form-control @error('asistenciasMinimas') is-invalid @enderror"
                                    id="asistenciasMinimas" name="asistenciasMinimas">
                                @error('asistenciasMinimas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-12 col-md-6 col-sm-12 ">
                                <label for="limiteReportes" class="form-label">Límite reportes asistencia</label>
                                <input value="{{ old('limiteReportes', '') }}" type="number"
                                    class="form-control @error('limiteReportes') is-invalid @enderror" id="limiteReportes"
                                    name="limiteReportes">
                                @error('limiteReportes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class=" mb-3  col-md-6 col-sm-12">
                                <label class="form-label">¿Tiene día límite de reporte?</label><br>
                                <label class="switch switch-lg">
                                    <input type="checkbox" class="switch-input" id="diaLimiteHabilitado"
                                        name="diaLimiteHabilitado" @checked(old('diaLimiteHabilitado')) />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on">Si</span>
                                        <span class="switch-off">No</span>
                                    </span>
                                </label>
                            </div>

                            <div id="containerDiaLimiteReporte" class="  mb-3 col-md-6  col-sm-12">
                                <label for="dia" class="form-label">Día límite reporte</label>
                                <select id="dia" name="dia" class="select2 form-select" data-allow-clear="true">
                                    <option value="" @selected(old('dia') == '')>Sin definir</option>
                                    <option value="1" @selected(old('dia') == '1')>Lunes</option>
                                    <option value="2" @selected(old('dia') == '2')>Martes</option>
                                    <option value="3" @selected(old('dia') == '3')>Miércoles</option>
                                    <option value="4" @selected(old('dia') == '4')>Jueves</option>
                                    <option value="5" @selected(old('dia') == '5')>Viernes</option>
                                    <option value="6" @selected(old('dia') == '6')>Sábado</option>
                                    <option value="0" @selected(old('dia') == '0')>Domingo</option>
                                </select>
                            </div>

                            <div id="containerCantidadReportesSemana" class="mb-5 col-12 col-md-6 col-sm-12">
                                <label for="cantidadReportesSemana" class="form-label">Cantidad de reportes semana</label>
                                <input value="{{ old('cantidadReportesSemana', '') }}" type="number"
                                    class="form-control @error('cantidadReportesSemana') is-invalid @enderror"
                                    id="cantidadReportesSemana" name="cantidadReportesSemana">
                                @error('cantidadReportesSemana')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="containerDiasPlazoReporte" class="mb-5 col-12 col-md-6 col-sm-12">
                                <label for="diasPlazoReporte" class="form-label">Días de plazo reporte</label>
                                <input value="{{ old('diasPlazoReporte', '') }}" type="number"
                                    class="form-control @error('diasPlazoReporte') is-invalid @enderror"
                                    id="diasPlazoReporte" name="diasPlazoReporte">
                                @error('diasPlazoReporte')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div style="min-height: 75px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-label">¿Habilitar inasistencia?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" @checked(old('habilitarInasistencias')) class="switch-input"
                                    id="togglehabilitarInasistencias" name="habilitarInasistencias" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                        </div>

                        <div id="containesAsistenciasAlerta"
                            class="mb-3 col-md-6 col-sm-12 @if (!old('habilitarInasistencias')) d-none @endif">
                            <label for="cantidadInasistencias" class="form-label">Cantidad inasistencia (alerta)</label>
                            <input value="{{ old('cantidadInasistencias', '') }}" type="number"
                                class="form-control @error('cantidadInasistencias') is-invalid @enderror"
                                id="cantidadInasistencias" name="cantidadInasistencias">
                            @error('cantidadInasistencias')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-12 col-md-4 ">
                            <label class="form-label">¿Habilitar calificaciones?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="togglehabilitarCalificaciones"
                                    name="habilitarCalificaciones" @checked(old('habilitarCalificaciones')) />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                        </div>

                        <div class="mb-3 col-12 col-md-4">
                            <label class="form-label">¿Habilitar traslado?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="togglehabilitarTraslado"
                                    name="habilitarTraslado" @checked(old('habilitarTraslado')) />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                        </div>

                        <div class="mb-3 col-12 col-md-4">
                            <label class="form-label">¿Carácter obligatorio?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="toggleobligatorio" name="obligatorio"
                                    @checked(old('obligatorio')) />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col mt-5 col-12">
                <div class="card p-6">
                    <h5 class="mb-4">Descripción básica y de progreso</h5>

                    <div class="mb-3 col-12">
                        <label for="descripcion" class="form-label">Descripción (obligatorio)</label>
                        <div id="editor" style="height: 300px;"></div>
                        <input id="descripcion" name="descripción" class='d-none' value="{{ old('descripción', '') }}">
                    </div>
                    @error('descripción')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <div class="col-12 mb-3">
                        <label for="tipoUsuarioObjetivo" class="form-label">Definir tipo usuario objetivo (Cambio por
                            Asistencia)</label>
                        <select id="tipoUsuarioObjetivo" name="tipoUsuarioObjetivo"
                            class="select2 form-select @error('tipoUsuarioObjetivo') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($tipoUsuariosObjetivo as $tipo)
                                <option value="{{ $tipo->id }}" @selected(old('tipoUsuarioObjetivo') == $tipo->id)>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-12 mb-3">
                        <label class="form-label">Grados requeridos (Prerrequisitos)</label>
                        <select class="form-select select2 @error('niveles_prerrequisito') is-invalid @enderror"
                            name="niveles_prerrequisito[]" multiple>
                            @foreach ($nivelesDisponibles as $nive)
                                <option value="{{ $nive->id }}" @selected(in_array($nive->id, old('niveles_prerrequisito', [])))>
                                    {{ $nive->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex mb-1 mt-5">
            <div class="me-auto">
                <button onclick="window.history.back()" type="reset"
                    class="btn rounded-pill btn-outline-secondary">Volver</button>
                <button type="submit" class="btn btn-primary rounded-pill me-1">Guardar Grado</button>
            </div>
        </div>
    </form>

    <!-- Modal para subir y recortar foto -->
    <div class="modal fade modal-img" id="modalFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-simple">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-camera ti-lg"></i> Subir foto</h3>
                        <p class="text-muted">Selecciona y recorta la foto de portada para el grado</p>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label class="mb-2"><span class="fw-bold">Paso #1</span> Selecciona la foto</label><br>
                                <input class="form-control" type="file" id="cropperImageUpload"
                                    accept="image/png, image/jpeg, image/gif">
                            </div>
                            <div class="mb-2">
                                <label class="mb-2"><span class="fw-bold">Paso #2</span> Recorta la foto</label><br>
                                <center>
                                    <img src="{{ Storage::url('generales/img/otros/placeholder.jpg') }}" class="w-100"
                                        id="croppingImage" alt="cropper">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="col-12 text-center">
                        <button type="button" class="btn rounded-pill btn-primary crop me-sm-3 me-1"
                            data-bs-dismiss="modal">Guardar recorte</button>
                        <button type="reset" class="btn rounded-pill btn-label-secondary" data-bs-dismiss="modal"
                            aria-label="Close">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
