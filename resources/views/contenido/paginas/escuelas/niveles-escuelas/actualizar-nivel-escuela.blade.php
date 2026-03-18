@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Actualizar Grado Escuela')

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

        // Cargamos contenido actual
        editor.root.innerHTML = "{!! old('descripción', $nivel->descripcion) !!}";

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

            // --- Lógica para campos de límite de reporte ---
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

            // Validación del formulario
            $('#formActualizarNivel').on('submit', function(e) {
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
            });
        });
    </script>
@endsection

@section('content')
    <form id="formActualizarNivel" action="{{ route('niveles-escuelas.actualizar', [$escuela, $nivel]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="col-md-12">
            <div class="card mb-4 rounded rounded-3">
                <img id="preview-foto" class="cropped-img card-img-top mb-1"
                    src="{{ $nivel->portada ? Storage::url($configuracion->ruta_almacenamiento . '/img/niveles/' . $nivel->portada) : asset('storage/global/img/temas/default.png') }}"
                    alt="Portada {{ $nivel->nombre }}">
                <button type="button" style="background-color: rgba(255, 255, 255, 0.5);"
                    class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2"
                    data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;"
                        class="ti ti-camera"></i></button>
                <input class="form-control d-none" type="text" value="" id="imagen-recortada" name="foto">

                <div class="row p-4 m-0 d-flex card-body">
                    <h5 class="mb-1 fw-semibold text-black">Actualizar Grado: {{ $nivel->nombre }}</h5>
                    <p class=" text-black">Modifica la configuración de este grado escolar.</p>
                </div>
            </div>
        </div>

        @include('layouts.status-msn')

        <div class="row equal-height-row ">
            <div class="col mb-3 equal-height-col col-12 ">
                <div class="card h-100 p-6">
                    <h5 class="mb-4">Configuración inicial</h5>
                    <div class="row ">
                        <div class="mb-3 col-12 col-md-6">
                            <label for="nombre" class="form-label">Nombre del grado</label>
                            <input value="{{ old('nombre', $nivel->nombre) }}" type="text"
                                class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">¿Habilitar asistencia?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="togglehabilitarAsistencias"
                                    name="habilitarAsistencias" @checked(old('habilitarAsistencias', $nivel->habilitar_asistencias)) />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="row-asistencias @if (!old('habilitarAsistencias', $nivel->habilitar_asistencias)) d-none @endif">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="asistenciasMinimas" class="form-label">Asistencias mínimas (opcional)</label>
                                <input value="{{ old('asistenciasMinimas', $nivel->asistencias_minimas) }}" type="number"
                                    class="form-control @error('asistenciasMinimas') is-invalid @enderror"
                                    id="asistenciasMinimas" name="asistenciasMinimas">
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="limiteReportes" class="form-label">Límite reportes asistencia</label>
                                <input value="{{ old('limiteReportes', $nivel->limite_reporte_asistencias) }}"
                                    type="number" class="form-control" id="limiteReportes" name="limiteReportes">
                            </div>
                        </div>

                        <div class="row">
                            <div class=" mb-3 col-md-6">
                                <label class="form-label">¿Tiene día límite de reporte?</label><br>
                                <label class="switch switch-lg">
                                    <input type="checkbox" class="switch-input" id="diaLimiteHabilitado"
                                        name="diaLimiteHabilitado" @checked(old('diaLimiteHabilitado', $nivel->tiene_dia_limite)) />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on">Si</span>
                                        <span class="switch-off">No</span>
                                    </span>
                                </label>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="dia" class="form-label">Día límite reporte</label>
                                <select id="dia" name="dia" class="select2 form-select">
                                    <option value="" @selected(old('dia', $nivel->dia_limite_reporte) === null)>Sin definir</option>
                                    <option value="1" @selected(old('dia', $nivel->dia_limite_reporte) == '1')>Lunes</option>
                                    <option value="2" @selected(old('dia', $nivel->dia_limite_reporte) == '2')>Martes</option>
                                    <option value="3" @selected(old('dia', $nivel->dia_limite_reporte) == '3')>Miércoles</option>
                                    <option value="4" @selected(old('dia', $nivel->dia_limite_reporte) == '4')>Jueves</option>
                                    <option value="5" @selected(old('dia', $nivel->dia_limite_reporte) == '5')>Viernes</option>
                                    <option value="6" @selected(old('dia', $nivel->dia_limite_reporte) == '6')>Sábado</option>
                                    <option value="0" @selected(old('dia', $nivel->dia_limite_reporte) == '0')>Domingo</option>
                                </select>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="cantidadReportesSemana" class="form-label">Cantidad de reportes semana</label>
                                <input value="{{ old('cantidadReportesSemana', $nivel->cantidad_limite_reportes_semana) }}"
                                    type="number" class="form-control" id="cantidadReportesSemana"
                                    name="cantidadReportesSemana">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="diasPlazoReporte" class="form-label">Días de plazo reporte</label>
                                <input value="{{ old('diasPlazoReporte', $nivel->dias_plazo_reporte) }}" type="number"
                                    class="form-control" id="diasPlazoReporte" name="diasPlazoReporte">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">¿Habilitar inasistencia?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" @checked(old('habilitarInasistencias', $nivel->habilitar_inasistencias)) class="switch-input"
                                    id="togglehabilitarInasistencias" name="habilitarInasistencias" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                        </div>

                        <div id="containesAsistenciasAlerta"
                            class="mb-3 col-md-6 @if (!old('habilitarInasistencias', $nivel->habilitar_inasistencias)) d-none @endif">
                            <label for="cantidadInasistencias" class="form-label">Cantidad inasistencia (alerta)</label>
                            <input value="{{ old('cantidadInasistencias', $nivel->asistencias_minima_alerta) }}"
                                type="number" class="form-control" id="cantidadInasistencias"
                                name="cantidadInasistencias">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">¿Habilitar calificaciones?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="togglehabilitarCalificaciones"
                                    name="habilitarCalificaciones" @checked(old('habilitarCalificaciones', $nivel->habilitar_calificaciones)) />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">¿Habilitar traslado?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="togglehabilitarTraslado"
                                    name="habilitarTraslado" @checked(old('habilitarTraslado', $nivel->habilitar_traslado)) />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">¿Carácter obligatorio?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="toggleobligatorio" name="obligatorio"
                                    @checked(old('obligatorio', $nivel->caracter_obligatorio)) />
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
                        <input id="descripcion" name="descripción" class='d-none'
                            value="{{ old('descripción', $nivel->descripcion) }}">
                    </div>

                    <div class="col-12 mb-3">
                        <label for="tipoUsuarioInicial" class="form-label">Tipo usuario inicial (Al matricular)</label>
                        <select id="tipoUsuarioInicial" name="tipoUsuarioInicial" class="select2 form-select">
                            <option value="">Seleccione...</option>
                            @foreach ($tipoUsuariosObjetivo as $tipo)
                                <option value="{{ $tipo->id }}" @selected(old('tipoUsuarioInicial', $nivel->tipo_usuario_inicial_id) == $tipo->id)>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="tipoUsuarioObjetivo" class="form-label">Tipo usuario objetivo (Al finalizar)</label>
                        <select id="tipoUsuarioObjetivo" name="tipoUsuarioObjetivo" class="select2 form-select">
                            <option value="">Seleccione...</option>
                            @foreach ($tipoUsuariosObjetivo as $tipo)
                                <option value="{{ $tipo->id }}" @selected(old('tipoUsuarioObjetivo', $nivel->tipo_usuario_objetivo_id) == $tipo->id)>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Grados requeridos (Prerrequisitos)</label>
                        <select class="form-select select2" name="niveles_prerrequisito[]" multiple>
                            @foreach ($nivelesDisponibles as $nive)
                                <option value="{{ $nive->id }}" @selected(in_array($nive->id, old('niveles_prerrequisito', $prerrequisitosIds)))>
                                    {{ $nive->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex mb-1 mt-5">
                        <div class="me-auto">
                            <a href="{{ route('escuelas.niveles', $escuela) }}"
                                class="btn rounded-pill btn-outline-secondary">Volver</a>
                            <button type="submit" class="btn btn-primary rounded-pill me-1">Actualizar Grado</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <h5 class="mb-4 mt-4 fw-semibold">Configuración de Pasos y Tareas</h5>
    <div class="col-12 mb-3">
        <div class="p-3 border rounded">
            <div class="col-12 col-md-12 mb-4">
                @livewire('escuelas.niveles-escuelas.gestionar-pasos-iniciar', ['nivel' => $nivel])
            </div>
            <hr class="my-4">
            @livewire('escuelas.niveles-escuelas.gestionar-pasos-requisito', ['nivel' => $nivel])
            <hr class="my-4">
            @livewire('escuelas.niveles-escuelas.gestionar-pasos-culminados', ['nivel' => $nivel])
            <hr class="my-4">
            @livewire('escuelas.niveles-escuelas.gestionar-tareas-requisito', ['nivel' => $nivel])
            <hr class="my-4">
            @livewire('escuelas.niveles-escuelas.gestionar-tareas-culminadas', ['nivel' => $nivel])
        </div>
    </div>





    <!-- Modal para portada (Copiado de crear) -->
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
