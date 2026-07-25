@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Materia')

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
        // Inicializar editor Quill con el contenido existente
        const editor = new Quill('#editor', {
            bounds: '#editor',
            placeholder: 'Descripción',
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
                imageResize: {
                    modules: ['Resize', 'DisplaySize']
                },
            },
            theme: 'snow'
        });

        // Cargar contenido existente en el editor
        editor.root.innerHTML = `{!! $materia->descripcion !!}`;
        $('#descripcion').val(editor.root.innerHTML);

        editor.on('text-change', (delta, oldDelta, source) => {
            $('#descripcion').val(editor.root.innerHTML);
        });
    </script>

    <script type="module">
        $(function() {
            'use strict';

            var croppingImage = document.querySelector('#croppingImage'),
                cropBtn = document.querySelector('.crop'),
                croppedImg = document.querySelector('.cropped-img'),
                upload = document.querySelector('#cropperImageUpload'),
                inputNombreArchivo = document.querySelector('#portada-nombre'),
                cropper = '';

            setTimeout(() => {
                cropper = new Cropper(croppingImage, {
                    zoomable: false,
                    aspectRatio: 1693 / 376,
                    cropBoxResizable: true
                });
            }, 1000);

            // on change show image with crop options
            upload.addEventListener('change', function(e) {
                if (e.target.files.length) {
                    var fileType = e.target.files[0].type;
                    if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
                        cropper.destroy();
                        // start file reader
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (e.target.result) {
                                croppingImage.src = e.target.result;
                                cropper = new Cropper(croppingImage, {
                                    zoomable: false,
                                    aspectRatio: 1693 / 376,
                                    cropBoxResizable: true
                                });
                            }
                        };
                        reader.readAsDataURL(e.target.files[0]);
                    } else {
                        alert('Selected file type is not supported. Please try again');
                    }
                }
            });

            // crop on click - upload async
            cropBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (cropper) {
                    // Mostrar indicador de carga
                    const statusEl = document.querySelector('#upload-status');
                    statusEl.classList.remove('d-none');

                    cropper.getCroppedCanvas({
                        height: 376,
                        width: 1693
                    }).toBlob(function(blob) {
                        // Crear FormData con el archivo
                        const formData = new FormData();
                        formData.append('portada', blob, 'portada.png');

                        // Subir via fetch
                        fetch('{{ route("materias.uploadPortada") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            statusEl.classList.add('d-none');
                            if (data.success) {
                                // Mostrar preview
                                croppedImg.src = cropper.getCroppedCanvas({ height: 376, width: 1693 }).toDataURL();
                                // Guardar nombre del archivo en input hidden
                                inputNombreArchivo.value = data.nombre;
                            } else {
                                alert(data.message || 'Error al subir la imagen.');
                            }
                        })
                        .catch(error => {
                            statusEl.classList.add('d-none');
                            alert('Error de conexión al subir la imagen.');
                        });
                    }, 'image/png');
                } else {
                    alert('Por favor, selecciona una imagen primero y espera a que cargue el recortador.');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Toggles visibility de asistencias
            $('#togglehabilitarAsistencias').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.row-asistencias').removeClass('d-none').show();
                } else {
                    $('.row-asistencias').addClass('d-none').hide();
                }
            });
            if ($('#togglehabilitarAsistencias').is(':checked')) {
                $('.row-asistencias').removeClass('d-none').show();
            } else {
                $('.row-asistencias').addClass('d-none').hide();
            }

            // Toggle visibility de inasistencias
            $('#togglehabilitarInasistencias').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#containesAsistenciasAlerta').removeClass('d-none').show();
                } else {
                    $('#containesAsistenciasAlerta').addClass('d-none').hide();
                }
            });
            if ($('#togglehabilitarInasistencias').is(':checked')) {
                $('#containesAsistenciasAlerta').removeClass('d-none').show();
            } else {
                $('#containesAsistenciasAlerta').addClass('d-none').hide();
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            // Inicializar Select2 en todos los selects de la página
            $('.select2').select2({
                allowClear: true,
                placeholder: 'Ninguno'
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
            switchDiaLimite.on('change', function() {
                actualizarCamposLimiteReporte();
            });

            // --- Validación del formulario principal ---
            // Nota: los pasos y tareas se gestionan directamente en BD a través de los
            // componentes Livewire; no dependen del submit del formulario principal.
            $('#formEditarMateria').on('submit', function(e) {
                let errors = [];

                // Validación: Asistencias mínimas
                if ($('#togglehabilitarAsistencias').is(':checked')) {
                    let asistencias = $('#asistenciasMinimas').val();
                    if (asistencias === '' || parseInt(asistencias) < 0) {
                        errors.push('Debe ingresar un valor válido (≥0) para asistencias mínimas');
                    }
                }

                // Validación: Alertas de inasistencia
                if ($('#togglehabilitarInasistencias').is(':checked')) {
                    let alerta = $('#cantidadInasistencias').val();
                    if (alerta === '' || parseInt(alerta) < 0) {
                        errors.push('Debe ingresar un valor válido (≥0) para alerta de inasistencias');
                    }
                }

                // Validación: Campos de límite de reporte
                if (switchDiaLimite.is(':checked')) {
                    if (!selectDia.val()) {
                        errors.push('Debe seleccionar un día límite para el reporte.');
                    }
                } else {
                    let reportesSemana = inputCantidadReportesSemana.val();
                    if (!reportesSemana || parseInt(reportesSemana) < 0) {
                        errors.push('Debe ingresar una cantidad válida para reportes por semana (ej. ≥0).');
                    }
                    let diasPlazo = inputDiasPlazoReporte.val();
                    if (!diasPlazo || parseInt(diasPlazo) < 0) {
                        errors.push('Debe ingresar una cantidad válida para días de plazo de reporte (ej. ≥0).');
                    }
                }

                // Validación: Al menos calificaciones o asistencias habilitadas
                if (!$('#togglehabilitarCalificaciones').is(':checked') &&
                    !$('#togglehabilitarAsistencias').is(':checked')) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de configuración',
                        text: 'Debe habilitar al menos Calificaciones o Asistencias',
                        confirmButtonText: 'Entendido'
                    });
                    return false;
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Errores de validación',
                        html: errors.join('<br>'),
                        confirmButtonText: 'Entendido'
                    });
                    return false;
                }
            });
        });
    </script>
@endsection


@section('content')
    <!-- PORTADA -->
    <form id="formEditarMateria" action="{{ route('materias.actualizar', $materia) }}" method="POST">
        <div class="col-md-12">
            <div class="card mb-4 rounded rounded-3">
                <img id="preview-foto" class="cropped-img card-img-top mb-2"
                    src="{{ $materia->portada_url ??  Storage::disk('global_media')->url('Banner-escuelas.png') }}"
                    alt="Portada {{ $escuela->nombre }}">
                <button type="button" style="background-color: rgba(255, 255, 255, 0.5);"
                    class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2"
                    data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;"
                        class="ti ti-camera"></i></button>
                {{-- Input hidden para guardar solo el nombre del archivo --}}
                <input class="form-control d-none" type="text" value="" id="portada-nombre"
                    name="portada_nombre">
                {{-- Indicador de carga --}}
                <div id="upload-status" class="d-none small text-muted p-2">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Subiendo imagen...
                </div>

                <div class="row p-4 m-0 d-flex card-body">
                    <h5 class="mb-1 fw-semibold text-black">Actualizar materia: {{ $materia->nombre }}</h5>
                    <p class="mb-4 text-black">Aquí podras actualizar la información de su materia junto con la creación
                        de los horarios.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-0 p-1 border-1">
                    <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">

                        <li class="nav-item flex-fill"><a id="tap-principal"
                                href="{{ route('materias.gestionar', $materia->id) }} "
                                class="nav-link p-3 waves-effect
                                    waves-light active"
                                data-tap="principal"><i class="ti-xs ti me-2 ti-info-hexagon "></i>
                                Datos
                                principales</a>
                        </li>

                        <li class="nav-item flex-fill"><a id="tap-horarios"
                                href="{{ route('materias.horarios', $materia->id) }} "
                                class="nav-link p-3 waves-effect waves-light" data-tap="horarios"><i
                                    class="ti-xs ti me-2 ti-clock"></i> Listado de horarios</a>
                        </li>

                        <li class="nav-item flex-fill"><a id="tap-modelo"
                                href="{{ route('materias.modelo', $materia->id) }}"
                                class="nav-link p-3 waves-effect waves-light" data-tap="modelo"><i
                                    class="ti-xs ti me-2 ti-template"></i> Modelo de calificación</a>

                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- PORTADA -->
        @include('layouts.status-msn')


        @if ($materia->nivel_id)
            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <span class="alert-icon text-info me-2">
                    <i class="ti ti-info-circle ti-xs"></i>
                </span>
                <div>
                    Esta materia pertenece al grado <strong>{{ $materia->nivel->nombre ?? 'N/A' }}</strong>.
                    Las configuraciones de asistencias, calificaciones y traslados se heredan automáticamente del grado.
                    <a href="{{ route('niveles-escuelas.gestionar-materias', [$escuela, $materia->nivel_id]) }}"
                        class="fw-bold text-info ms-2">Volver al Grado</a>
                </div>
            </div>
        @endif

        <div class="row equal-height-row ">
            @csrf
            @method('POST')

            <div class="col mb-3 equal-height-col col-12 ">
                <div class="card h-100 p-6">
                    <h5 class="mb-1 fw-semibold text-black">Configuración principal</h5>
                    <div class="row ">
                        <div class="mb-3 col-12 col-md-6 col-sm-12">
                            <label for="nombre" class="form-label">Nombre de la Materia</label>
                            <input value="{{ old('nombre', $materia->nombre) }}" type="text"
                                class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6 col-sm-12 ">
                            <label class="form-label">¿Habilitar asistencia?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="togglehabilitarAsistencias"
                                    name="habilitarAsistencias" @checked(old('habilitarAsistencias', $materia->habilitar_asistencias)) />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                            @error('habilitarAsistencias')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    @if (!$materia->nivel_id)
                        <div class="row-asistencias">
                            <div class="row">
                                <div id="containesAsistenciasMinimas" class="mb-3 col-md-6 col-sm-12">
                                    <label for="asistenciasMinimas" class="form-label">Asistencias Mínimas (opcional)</label>
                                    <input value="{{ old('asistenciasMinimas', $materia->asistencias_minimas) }}" type="number" min="0"
                                        class="form-control @error('asistenciasMinimas') is-invalid @enderror"
                                        id="asistenciasMinimas" name="asistenciasMinimas">
                                    @error('asistenciasMinimas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-12 col-md-6 col-sm-12">
                                    <label for="limiteReportes" class="form-label">Limite reportes asistencia </label>
                                    <input value="{{ old('limiteReportes', $materia->limite_reporte_asistencias) }}" type="number" min="0"
                                        class="form-control @error('limiteReportes') is-invalid @enderror" id="limiteReportes"
                                        name="limiteReportes">
                                    @error('limiteReportes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6 col-sm-12">
                                    <label class="form-label">¿Tiene día limite de reporte?</label><br>
                                    <label class="switch switch-lg">
                                        <input type="checkbox"
                                            class="switch-input" id="diaLimiteHabilitado" name="diaLimiteHabilitado"
                                            @checked(old('diaLimiteHabilitado', $materia->tiene_dia_limite)) />
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">Si</span>
                                            <span class="switch-off">No</span>
                                        </span>
                                    </label>
                                    @error('diaLimiteHabilitado')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div id="containerDiaLimiteReporte" class="mb-3 col-md-6 col-sm-12">
                                    <label for="dia" class="form-label">Día limite reporte</label>
                                    <select id="dia" name="dia" class="select2 form-select" data-allow-clear="true">
                                        <option value="">Sin definir</option>
                                        <option value="1" @selected(old('dia', $materia->dia_limite_reporte) == '1')>Lunes</option>
                                        <option value="2" @selected(old('dia', $materia->dia_limite_reporte) == '2')>Martes</option>
                                        <option value="3" @selected(old('dia', $materia->dia_limite_reporte) == '3')>Miércoles</option>
                                        <option value="4" @selected(old('dia', $materia->dia_limite_reporte) == '4')>Jueves</option>
                                        <option value="5" @selected(old('dia', $materia->dia_limite_reporte) == '5')>Viernes</option>
                                        <option value="6" @selected(old('dia', $materia->dia_limite_reporte) == '6')>Sábado</option>
                                        <option value="0" @selected(old('dia', $materia->dia_limite_reporte) == '0')>Domingo</option>
                                    </select>
                                    @if ($errors->has('dia'))
                                        <div class="text-danger form-label">{{ $errors->first('dia') }}</div>
                                    @endif
                                </div>

                                <div id="containerCantidadReportesSemana" class="mb-3 col-12 col-md-6 col-sm-12">
                                    <label for="cantidadReportesSemana" class="form-label">Cantidad de reportes semana</label>
                                    <input value="{{ old('cantidadReportesSemana', $materia->cantidad_limite_reportes_semana) }}" type="number" min="0"
                                        class="form-control @error('cantidadReportesSemana') is-invalid @enderror"
                                        id="cantidadReportesSemana" name="cantidadReportesSemana">
                                    @error('cantidadReportesSemana')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="containerDiasPlazoReporte" class="mb-3 col-12 col-md-6 col-sm-12">
                                    <label for="diasPlazoReporte" class="form-label">Días de plazo reporte</label>
                                    <input value="{{ old('diasPlazoReporte', $materia->dias_plazo_reporte) }}" type="number" min="0"
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
                                <label for="habilitarInasistencias" class="form-label">¿Habilitar inasistencia?</label><br>
                                <label class="switch switch-lg">
                                    <input type="checkbox" class="switch-input" id="togglehabilitarInasistencias"
                                        name="habilitarInasistencias" @checked(old('habilitarInasistencias', $materia->habilitar_inasistencias)) />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on">Si</span>
                                        <span class="switch-off">No</span>
                                    </span>
                                </label>
                                @error('habilitarInasistencias')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div id="containesAsistenciasAlerta" class="mb-3 col-md-6 col-sm-12">
                                <label for="cantidadInasistencias" class="form-label">Cantidad inasistencia (alerta)</label>
                                <input value="{{ old('cantidadInasistencias', $materia->asistencias_minima_alerta) }}" type="number" min="0"
                                    class="form-control @error('cantidadInasistencias') is-invalid @enderror"
                                    id="cantidadInasistencias" name="cantidadInasistencias">
                                @error('cantidadInasistencias')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-12 col-md-6">
                                <label for="habilitarCalificaciones" class="form-label">¿Habilitar calificaciones?</label><br>
                                <label class="switch switch-lg">
                                    <input type="checkbox" class="switch-input" id="togglehabilitarCalificaciones"
                                        name="habilitarCalificaciones" @checked(old('habilitarCalificaciones', $materia->habilitar_calificaciones)) />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on">Si</span>
                                        <span class="switch-off">No</span>
                                    </span>
                                </label>
                                @error('habilitarCalificaciones')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3 col-12 col-md-6">
                                <label for="habilitarTraslado" class="form-label">¿Habilitar traslado?</label><br>
                                <label class="switch switch-lg">
                                    <input type="checkbox" class="switch-input" id="togglehabilitarTraslado"
                                        name="habilitarTraslado" @checked(old('habilitarTraslado', $materia->habilitar_traslado)) />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on">Si</span>
                                        <span class="switch-off">No</span>
                                    </span>
                                </label>
                                @error('habilitarTraslado')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3 col-12 col-md-6">
                                <label for="obligatorio" class="form-label">¿Cáracter obligatorio?</label><br>
                                <label class="switch switch-lg">
                                    <input type="checkbox" class="switch-input" id="toggleobligatorio"
                                        name="obligatorio" @checked(old('obligatorio', $materia->caracter_obligatorio)) />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on">Si</span>
                                        <span class="switch-off">No</span>
                                    </span>
                                </label>
                                @error('obligatorio')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <div class=" col equal-height-col  col-12 col-md-12">
            <div class="card p-6 h-100">
                @if ($escuela->tipo_matriculas == 'niveles_agrupados')
                    <div class="mb-3 col-12 col-md-4">
                        <label for="nivel_id" class="form-label">Nivel (opcional)</label><br>
                        <input type="number" class="form-control" id="nivel_id" name="nivel_id" min="0">
                        @error('nivel_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                @if (!$materia->nivel_id)
                    <!-- Pasos de Crecimiento -->
                    <h5 class="mb-1 fw-semibold text-black">Configuración de progreso</h5>

                    <div class="col-12 mb-3">
                        <label for="tipoUsuarioInicial" class="form-label">Tipo usuario inicial (Al
                            matricular)</label><br>
                        <select id="tipoUsuarioInicial" name="tipoUsuarioInicial"
                            class="select2 form-select @error('tipoUsuarioInicial') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($tipoUsuariosObjetivo as $tipo)
                                <option value="{{ $tipo->id }}"
                                    {{ old('tipoUsuarioInicial', $materia->tipo_usuario_inicial_id) == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipoUsuarioInicial')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="tipoUsuarioObjetivo" class="form-label">Tipo usuario objetivo (Al
                            finalizar)</label><br>
                        <select id="tipoUsuarioObjetivo" name="tipoUsuarioObjetivo"
                            class="select2 form-select @error('tipoUsuarioObjetivo') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($tipoUsuariosObjetivo as $tipo)
                                <option value="{{ $tipo->id }}"
                                    {{ old('tipoUsuarioObjetivo', $materia->tipo_usuario_objetivo_id) == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipoUsuarioObjetivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Prerrequisitos Materias -->
                    <h5 class="mb-1 fw-semibold text-black">Configuración Módulos</h5>
                    <div class="col-12 col-md-12">
                        <label class="form-label">Materias requeridas</label><br>
                        @php
                            $materiasPrerequisito = $materia
                                ->prerrequisitosMaterias()
                                ->pluck('materia_prerrequisito.materia_prerrequisito_id')
                                ->toArray();
                        @endphp
                        <select class="form-select select2 @error('materias_prerrequisito') is-invalid @enderror"
                            name="materias_prerrequisito[]" multiple>
                            @foreach ($materiasEscuela as $mate)
                                <option value="{{ $mate->id }}" @if (in_array($mate->id, $materiasPrerequisito)) selected @endif>
                                    {{ $mate->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('materias_prerrequisito')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif


                <!-- /Editor -->
            </div>

        </div>
        <div class=" col equal-height-col mt-3 col-12 col-md-12">
            <div class="card p-6">
                <!-- Campo oculto para escuela_id, se asigna automáticamente -->
                <div class="mb-3 col-12">
                    <label for="descripcion" class="form-label">Descripción (opcional)</label>

                    <div id="editor"></div>
                    <input id="descripcion" name="descripción" class='d-none' value="{!! old('descripción', $materia->descripcion) !!}">
                </div>
                @error('descripción')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror

            </div>

            <div class="d-flex mb-1 mt-5">
                <div @if ($materia->nivel_id) class="ms-5" @endif>
                    <button onclick="window.history.back()" type="reset"
                        class="btn rounded-pill btn-outline-secondary">Volver</button>
                    <button type="submit" class="btn btn-primary rounded-pill me-1 btnGuardar">Guardar</button>

                </div>
            </div>
        </div>

        @if (!$materia->nivel_id)
            {{-- SECCIÓN: PROCESOS Y TAREAS (NUEVO) --}}
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 text-black">Procesos y Tareas de Crecimiento</h5>
                        <small class="text-dark">Gestiona los requisitos para inscribirse y los logros al culminar</small>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row">
                            {{-- 0. Procesos al Iniciar --}}
                            <div class="col-12 mb-4">
                                @livewire('escuelas.materias.gestionar-pasos-iniciar', ['materia' => $materia])
                            </div>
                            <hr class="my-4">

                            {{-- 1. Procesos Prerrequisito --}}
                            <div class="col-12 mb-4">
                                @livewire('escuelas.materias.gestionar-pasos-requisito', ['materia' => $materia])
                            </div>

                            {{-- 2. Procesos a Culminar --}}
                            <div class="col-12 mb-4">
                                @livewire('escuelas.materias.gestionar-pasos-culminados', ['materia' => $materia])
                            </div>

                            {{-- 3. Tareas Prerrequisito --}}
                            <div class="col-12 mb-4">
                                @livewire('escuelas.materias.gestionar-tareas-requisito', ['materia' => $materia])
                            </div>

                            {{-- 4. Tareas a Culminar --}}
                            <div class="col-12 mb-4">
                                @livewire('escuelas.materias.gestionar-tareas-culminadas', ['materia' => $materia])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif




        </div>



    </form>

    <!-- modal foto-->
    <div class="modal fade modal-img" id="modalFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-camera  ti-lg"></i> Subir foto</h3>
                        <p class="text-muted">Selecciona y recorta la foto</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <label class="mb-2"><span class="fw-bold">Paso #1</span> Selecciona la
                                    foto</label><br>
                                <input class="form-control" type="file" id="cropperImageUpload">
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
                        <button type="submit" class="btn rounded-pill  btn-primary crop me-sm-3 me-1"
                            data-bs-dismiss="modal">Guardar</button>
                        <button type="reset" class="btn rounded-pill  btn-label-secondary" data-bs-dismiss="modal"
                            aria-label="Close">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ modal foto -->





@endsection
