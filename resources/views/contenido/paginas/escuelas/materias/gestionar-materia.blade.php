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

        editor.on('text-change', (delta, oldDelta, source) => {
            $('#descripcion').val(editor.root.innerHTML);
        });
    </script>

    <script type="module">
        $(function() {
            'use strict';

            var croppingImage = document.querySelector('#croppingImage'),
                //img_w = document.querySelector('.img-w'),
                cropBtn = document.querySelector('.crop'),
                croppedImg = document.querySelector('.cropped-img'),
                dwn = document.querySelector('.download'),
                upload = document.querySelector('#cropperImageUpload'),
                modalImg = document.querySelector('.modal-img'),
                inputResultado = document.querySelector('#imagen-recortada'),
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
                    console.log(e.target.files[0]);
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

            // crop on click
            cropBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // get result to data uri
                let imgSrc = cropper
                    .getCroppedCanvas({
                        height: 376,
                        width: 1693 // input value
                    })
                    .toDataURL();
                croppedImg.src = imgSrc;
                inputResultado.value = imgSrc;
                //dwn.setAttribute('href', imgSrc);
                //dwn.download = 'imagename.png';
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100px',
                allowClear: true,
                placeholder: 'Ninguno'
            });

        });

        $(document).ready(function() {
            // Mostrar/ocultar asistencias mínimas según el estado del toggle
            if ($('#togglehabilitarAsistencias').is(':checked')) {
                $('#cantidadReportesSemana').removeClass('d-none').show();
            }

            $('#togglehabilitarAsistencias').on('change', function() {
                if ($('#togglehabilitarAsistencias').is(':checked')) {
                    $('#containesAsistenciasMinimas').removeClass('d-none').show();
                } else {
                    $('#containesAsistenciasMinimas').addClass('d-none').hide();
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            // Función para validación antes de enviar el formulario
            $('#formEditarMateria').on('submit', function(e) {
                e.preventDefault();
                let form = this;
                let errors = [];

                // Validación 1: Asistencias mínimas si está habilitado
                if ($('#togglehabilitarAsistencias').is(':checked')) {
                    let asistencias = $('#asistenciasMinimas').val();
                    if (!asistencias || asistencias < 1) {
                        errors.push('Debe ingresar un valor válido (≥1) para asistencias mínimas');
                    }
                }

                // Validación 2: Alertas de inasistencia si está habilitado
                if ($('#togglehabilitarInasistencias').is(':checked')) {
                    let alerta = $('#cantidadInasistencias').val();
                    if (!alerta || alerta < 1) {
                        errors.push('Debe ingresar un valor válido (≥1) para alerta de inasistencias');
                    }
                }

                // Validación 3: Al menos calificaciones o asistencias habilitadas
                if (!$('#togglehabilitarCalificaciones').is(':checked') &&
                    !$('#togglehabilitarAsistencias').is(':checked')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de configuración',
                        text: 'Debe habilitar al menos Calificaciones o Asistencias',
                        confirmButtonText: 'Entendido'
                    });
                    return false;
                }

                // Mostrar errores si existen
                if (errors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errores de validación',
                        html: errors.join('<br>'),
                        confirmButtonText: 'Entendido'
                    });
                    return false;
                }

                // Validación de pasos de crecimiento
                let pasoInicio = $('[name="paso_iniciar_id"]').val();
                // Removed pasoFin validation as it is now livewire

                if (!pasoInicio) {
                    Swal.fire({
                        title: '¿Está seguro?',
                        text: "Está creando o editando una materia sin paso de inicio relacionado",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Continuar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    form.submit();
                }
            });

            // Validación en tiempo real para asistencias
            $('#togglehabilitarAsistencias').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#containesAsistenciasMinimas').removeClass('d-none');
                } else {
                    $('#containesAsistenciasMinimas').addClass('d-none');
                }
            });

            // Validación en tiempo real para inasistencias
            $('#togglehabilitarInasistencias').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#containesAsistenciasAlerta').removeClass('d-none');
                } else {
                    $('#containesAsistenciasAlerta').addClass('d-none');
                }
            });
        });

        /// validaciones de restricciones de reportes asistencias
        $(document).ready(function() {
            // --- INICIO: Lógica para campos de límite de reporte ---

            // Seleccionar los elementos del DOM
            const switchDiaLimite = $('#diaLimiteHabilitado');
            const selectDia = $('#dia');
            const inputCantidadReportesSemana = $('#cantidadReportesSemana');
            const inputDiasPlazoReporte = $('#diasPlazoReporte');

            // Contenedores de los campos para mostrar/ocultar o modificar etiquetas si es necesario
            // (Ajusta estos selectores si los labels están fuera o si quieres manipularlos de otra forma)
            const containerDia = $(
                '#containesAsistenciasMinimas'
            ); // Parece que reutilizas este ID, sería mejor uno específico para el día
            const labelCantidadReportesSemana = $(
                '#labelCantidadReportesSemana label'); // Asumiendo que el label está dentro
            const labelDiasPlazoReporte = $('#labeldiasPlazoReporte label'); // Asumiendo que el label está dentro

            // Función para actualizar el estado y la obligatoriedad de los campos
            function actualizarCamposLimiteReporte() {
                if (switchDiaLimite.is(':checked')) {
                    // Caso 1: tiene_dia_limite es TRUE
                    // Hacer 'dia' obligatorio y habilitado
                    selectDia.prop('disabled', false);
                    selectDia.prop('required', true);
                    // Podrías añadir una clase o texto para indicar que es obligatorio visualmente
                    // Ejemplo: $('label[for="dia"]').addClass('required-field-label');

                    // Hacer 'cantidadReportesSemana' y 'diasPlazoReporte' no obligatorios, deshabilitados y limpiar
                    inputCantidadReportesSemana.prop('disabled', true);
                    inputCantidadReportesSemana.prop('required', false);
                    inputCantidadReportesSemana.val(''); // Limpiar valor
                    // Ejemplo: labelCantidadReportesSemana.removeClass('required-field-label');

                    inputDiasPlazoReporte.prop('disabled', true);
                    inputDiasPlazoReporte.prop('required', false);
                    inputDiasPlazoReporte.val(''); // Limpiar valor
                    // Ejemplo: labelDiasPlazoReporte.removeClass('required-field-label');

                    // Asegurarse que el contenedor del select de día esté visible
                    // (Si usas 'd-none' para ocultar, asegúrate que el ID 'containesAsistenciasMinimas' sea el correcto para el día)
                    $('#containesAsistenciasMinimas').removeClass(
                        'd-none'); // Este ID parece ser de otro campo, revisa tu HTML.
                    // Si el campo 'dia' ya es visible, no necesitas esto.

                } else {
                    // Caso 2: tiene_dia_limite es FALSE
                    // Hacer 'dia' no obligatorio, deshabilitado y limpiar
                    selectDia.prop('disabled', true);
                    selectDia.prop('required', false);
                    selectDia.val('').trigger('change'); // Limpiar valor y refrescar select2 si aplica
                    // Ejemplo: $('label[for="dia"]').removeClass('required-field-label');
                    // Opcional: Ocultar el contenedor del select de día
                    // $('#containesAsistenciasMinimas').addClass('d-none'); // Revisa si este es el contenedor correcto.

                    // Hacer 'cantidadReportesSemana' y 'diasPlazoReporte' obligatorios y habilitados
                    inputCantidadReportesSemana.prop('disabled', false);
                    inputCantidadReportesSemana.prop('required', true);
                    // Ejemplo: labelCantidadReportesSemana.addClass('required-field-label');

                    inputDiasPlazoReporte.prop('disabled', false);
                    inputDiasPlazoReporte.prop('required', true);
                    // Ejemplo: labelDiasPlazoReporte.addClass('required-field-label');
                }
            }

            // Ejecutar la función al cargar la página para establecer el estado inicial
            actualizarCamposLimiteReporte();

            // Ejecutar la función cada vez que el switch cambie
            switchDiaLimite.on('change', function() {
                actualizarCamposLimiteReporte();
            });

            // --- FIN: Lógica para campos de límite de reporte ---

            // --- AJUSTE A TU VALIDACIÓN DE FORMULARIO EXISTENTE ---
            $('#formEditarMateria').on('submit', function(e) {
                // Tu código de validación existente...
                let errors = [];

                // Validación 1: Asistencias mínimas si está habilitado (ya lo tienes)
                if ($('#togglehabilitarAsistencias').is(':checked')) {
                    let asistencias = $('#asistenciasMinimas').val();
                    if (!asistencias || asistencias < 1) {
                        errors.push('Debe ingresar un valor válido (≥1) para asistencias mínimas');
                    }
                }

                // Validación 2: Alertas de inasistencia si está habilitado (ya lo tienes)
                if ($('#togglehabilitarInasistencias').is(':checked')) {
                    let alerta = $('#cantidadInasistencias').val();
                    if (!alerta || alerta < 1) {
                        errors.push('Debe ingresar un valor válido (≥1) para alerta de inasistencias');
                    }
                }

                // NUEVA VALIDACIÓN: Campos de límite de reporte según el switch
                if (switchDiaLimite.is(':checked')) {
                    // Si 'diaLimiteHabilitado' está activo, 'dia' es obligatorio
                    if (!selectDia.val()) { // Si el valor es vacío o null
                        errors.push('Debe seleccionar un día límite para el reporte.');
                    }
                } else {
                    // Si 'diaLimiteHabilitado' está inactivo, 'cantidadReportesSemana' y 'diasPlazoReporte' son obligatorios
                    let reportesSemana = inputCantidadReportesSemana.val();
                    if (!reportesSemana || parseInt(reportesSemana) <
                        0) { // O la validación que necesites, ej. >= 0 o >= 1
                        errors.push('Debe ingresar una cantidad válida para reportes por semana (ej. ≥0).');
                    }

                    let diasPlazo = inputDiasPlazoReporte.val();
                    if (!diasPlazo || parseInt(diasPlazo) < 0) { // O la validación que necesites
                        errors.push(
                            'Debe ingresar una cantidad válida para días de plazo de reporte (ej. ≥0).');
                    }
                }

                // Validación 3: Al menos calificaciones o asistencias habilitadas (ya lo tienes)
                if (!$('#togglehabilitarCalificaciones').is(':checked') &&
                    !$('#togglehabilitarAsistencias').is(':checked')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de configuración',
                        text: 'Debe habilitar al menos Calificaciones o Asistencias',
                        confirmButtonText: 'Entendido'
                    });
                    e.preventDefault(); // Detener envío
                    return false;
                }

                // Mostrar errores si existen
                if (errors.length > 0) {
                    e.preventDefault(); // Detener envío del formulario
                    Swal.fire({
                        icon: 'error',
                        title: 'Errores de validación',
                        html: errors.join('<br>'),
                        confirmButtonText: 'Entendido'
                    });
                    return false;
                }

                // Validación de pasos de crecimiento (ya lo tienes)
                let pasoInicio = $('[name="paso_iniciar_id"]').val();
                let pasoFin = $('[name="paso_culminar_id"]').val();

                if (!pasoInicio && !pasoFin) {
                    // No prevenir el default aquí, solo mostrar la advertencia
                    // y dejar que el usuario decida si continuar o no.
                    // El e.preventDefault() debe estar DENTRO del then((result)) si result.isConfirmed es false
                } else {
                    // Si hay pasos, o si no hay y el usuario confirma, se envía.
                    // No necesitas un form.submit() explícito aquí si no hay confirmación pendiente.
                }

                // Si todo está bien, el formulario se enviará.
                // Si necesitas la confirmación de SweetAlert para los pasos, asegúrate
                // de que el e.preventDefault() esté al inicio del submit handler,
                // y luego llames a form.submit() explícitamente solo cuando todo sea válido Y confirmado.
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
                    src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/materias/' . $materia->portada) }}"
                    alt="Portada {{ $escuela->nombre }}">
                <button type="button" style="background-color: rgba(255, 255, 255, 0.5);"
                    class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2"
                    data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;"
                        class="ti ti-camera"></i></button>
                <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada"
                    name="foto">

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


        <div class="row equal-height-row ">
            @csrf
            @method('POST')

            <div class="col mb-3 equal-height-col col-12 ">
                <div class="card h-100 p-6">
                    <h5 class="mb-1 fw-semibold text-black">Configuración inicial</h5>
                    <div class="row ">
                        <div class="mb-3 col-12 col-md-6 ">

                            <label for="nombre" class="form-label">Nombre de la Materia</label>
                            <input value="{{ old('nombre', $materia->nombre) }}" type="text" class="form-control @error('nombre') is-invalid @enderror"
                                id="nombre" name="nombre">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="" class="mb-3 col-12 col-md-6 ">
                            <label for="limiteReportes" class="form-label">Limite reportes asistencia </label>

                            <input value="{{ $materia->limite_reporte_asistencias }}" type="number" class="form-control @error('limiteReportes') is-invalid @enderror"
                                id="limiteReportes" name="limiteReportes">
                            @error('limiteReportes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div style="    min-height: 75px;" class="row">


                        <div class="col-md-6 col-sm-12">
                          <label class="form-label">¿Tiene día limite de reporte?</label><br>
                            <label class="switch switch-lg">
                                <input {{ $materia->tiene_dia_limite ? 'checked' : '' }} type="checkbox"
                                    class="switch-input" id="diaLimiteHabilitado" name="diaLimiteHabilitado" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                            @error('diaLimiteHabilitado')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div id="containerDiaLimiteReporte" class="mb-3 col-md-6  col-sm-12 ">
                            <label for="dia" class="form-label">Día limite reporte</label><br>
                            <select id="dia" name="dia" class="select2 form-select" data-allow-clear="true">
                                <option value="">Sin definir</option>
                                <option @if ($materia->dia_limite_reporte == '1') selected @endif value="1"
                                    {{ old('dia') }}>Lunes</option>
                                <option @if ($materia->dia_limite_reporte == '2') selected @endif value="2"
                                    {{ old('dia') }}>Martes</option>
                                <option @if ($materia->dia_limite_reporte == '3') selected @endif value="3"
                                    {{ old('dia') }}>Miércoles</option>
                                <option @if ($materia->dia_limite_reporte == '4') selected @endif value="4"
                                    {{ old('dia') }}>Jueves</option>
                                <option @if ($materia->dia_limite_reporte == '5') selected @endif value="5"
                                    {{ old('dia') }}>Viernes</option>
                                <option @if ($materia->dia_limite_reporte == '6') selected @endif value="6"
                                    {{ old('dia') }}>Sábado</option>
                                <option @if ($materia->dia_limite_reporte == '0') selected @endif value="0"
                                    {{ old('dia') }}>Domingo</option>
                            </select>
                            @if ($errors->has('dia'))
                                <div class="text-danger form-label">{{ $errors->first('dia') }}</div>
                            @endif
                        </div>

                        <div id="labelCantidadReportesSemana" class="mb-3  col-md-6  col-sm-12 ">
                            <label for="cantidadReportesSemana" class="form-label">Cantidad de reportes semana</label><br>
                            <input value="{{ $materia->cantidad_limite_reportes_semana }}" type="number"
                                class="form-control @error('cantidadReportesSemana') is-invalid @enderror" id="cantidadReportesSemana" name="cantidadReportesSemana">
                            @error('cantidadReportesSemana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="labeldiasPlazoReporte" class="mb-3  col-md-6  col-sm-12 ">
                            <label for="diasPlazoReporte" class="form-label">dias de plazo reparto</label><br>
                            <input value="{{ $materia->dias_plazo_reporte }}" type="number" class="form-control @error('diasPlazoReporte') is-invalid @enderror"
                                id="diasPlazoReporte" name="diasPlazoReporte">
                            @error('diasPlazoReporte')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div style="    min-height: 75px;" class="row">

                        <div class="col-md-6  col-sm-12">
                            <label for="togglehabilitarAsistencias" class="form-label">¿Habilitar asistencia?</label><br>
                            <label class="switch switch-lg">
                                <input {{ $materia->habilitar_asistencias ? 'checked' : '' }} type="checkbox"
                                    class="switch-input" id="togglehabilitarAsistencias" name="habilitarAsistencias" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                            @error('habilitarAsistencias')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div id="containesAsistenciasMinimas"
                            class="mb-3 {{ $materia->habilitar_asistencias ? '' : 'd-none' }} col-md-6  col-sm-12">
                            <label for="asistenciasMinimas" class="form-label">Asistencias Mínimas (opcional)</label><br>
                            <input value="{{ $materia->asistencias_minimas }}" type="number" class="form-control @error('asistenciasMinimas') is-invalid @enderror"
                                id="asistenciasMinimas" name="asistenciasMinimas">
                            @error('asistenciasMinimas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div style="    min-height: 75px;" class="row">

                        <div style="margin-top: -12px;" class="col-6">
                          <label for="habilitarInasistencias" class="form-label">¿Habilitar inasistencia?</label><br>
                            <label class="switch switch-lg">
                                <input {{ $materia->habilitar_inasistencias ? 'checked' : '' }} type="checkbox"
                                    @checked(old('habilitarInasistencias')) class="switch-input" id="togglehabilitarInasistencias"
                                    name="habilitarInasistencias" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                            @error('habilitarInasistencias')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div id="containesAsistenciasAlerta"
                            class="mb-3  {{ $materia->habilitar_inasistencias ? '' : 'd-none' }}  col-md-6  col-sm-12">
                            <label for="asistenciasAlerta" class="form-label">Cantidad inasistencia (alerta)</label><br>
                            <input value="{{ $materia->asistencias_minima_alerta }}" type="number" class="form-control @error('cantidadInasistencias') is-invalid @enderror"
                                id="cantidadInasistencias" name="cantidadInasistencias">
                            @error('cantidadInasistencias')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">

                        <div class="mb-3  col-12 col-md-6 ">
                            <label for="habilitarCalificaciones" class="form-label">¿Habilitar calificaciones?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" {{ $materia->habilitar_calificaciones ? 'checked' : '' }}
                                    class="switch-input" id="togglehabilitarCalificaciones"
                                    name="habilitarCalificaciones" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>

                            @error('habilitarCalificaciones')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3   col-12 col-md-6">
                            <label for="habilitarTraslado" class="form-label">¿Habilitar traslado?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" {{ $materia->habilitar_traslado ? 'checked' : '' }}
                                    class="switch-input" id="togglehabilitarTraslado" name="habilitarTraslado" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                            @error('habilitarTraslado')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3  col-12 col-md-6">
                            <label for="obligatorio" class="form-label">¿Cáracter obligatorio?</label><br>
                            <label class="switch switch-lg">
                                <input type="checkbox" {{ $materia->habilitar_traslado ? 'checked' : '' }}
                                    class="switch-input" id="toggleobligatorio" name="obligatorio" />
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
                </div>
            </div>

            <div class=" col equal-height-col  col-12 col-md-12">
                <div class="card p-6 h-100">
                    @if ($escuela->tipo_matriculas == 'niveles_agrupados')
                        <div class="mb-3 col-12 col-md-4">
                            <label for="nivel_id" class="form-label">Nivel (opcional)</label><br>
                            <input type="number" class="form-control" id="nivel_id" name="nivel_id">
                            @error('nivel_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <!-- Pasos de Crecimiento -->

                    <h5 class="mb-1 fw-semibold text-black">Configuración de progreso</h5>

                    <div class="col-12 col-md-12 mb-4">
                        <label class="form-label">Paso al iniciar <small class="text-muted">(Se asignará al inscribirse)</small></label><br>
                        <select class="form-select select2 @error('paso_iniciar_id') is-invalid @enderror" name="paso_iniciar_id">
                            <option value="">Seleccionar paso inicial</option>
                            @foreach ($pasosCrecimiento as $paso)
                                <option value="{{ $paso->id_paso }}|{{ $paso->estado_id }}"
                                    {{ $pasoInicioSeleccionado == $paso->id_paso . '|' . $paso->estado_id ? 'selected' : '' }}>
                                    {{ $paso->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('paso_iniciar_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label for="tipoUsuarioObjetivo" class="form-label">Definir tipo usuario objetivo (Cambio por Asistencia)</label><br>
                        <select id="tipoUsuarioObjetivo" name="tipoUsuarioObjetivo" class="select2 form-select @error('tipoUsuarioObjetivo') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($tipoUsuariosObjetivo as $tipo)
                            <option value="{{ $tipo->id }}" {{ old('tipoUsuarioObjetivo', $materia->tipo_usuario_objetivo_id) == $tipo->id ? 'selected' : '' }}>
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

                        <select class="form-select select2 @error('materias_prerrequisito') is-invalid @enderror" name="materias_prerrequisito[]" multiple>

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


                        <!-- /Editor -->
                </div>

            </div>
               <div class=" col equal-height-col mt-3 col-12 col-md-12">
                            <div class="card p-6">
                                <!-- Campo oculto para escuela_id, se asigna automáticamente -->
                                <div class="mb-3 col-12">
                                    <label for="descripcion" class="form-label">Descripción (opcional)</label>

                                    <div id="editor"></div>
                                    <input id="descripcion" name="descripción" class='d-none'>
                                </div>
                                @error('descripción')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror

                            </div>

                        </div>

            {{-- SECCIÓN: PROCESOS Y TAREAS (NUEVO) --}}
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 text-black">Procesos y Tareas de Crecimiento</h5>
                        <small class="text-dark">Gestiona los requisitos para inscribirse y los logros al culminar</small>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row">
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




        </div>

        <div class="d-flex mb-1 mt-5">
            <div class="me-auto">
                <button onclick="window.history.back()" type="reset"
                    class="btn rounded-pill btn-outline-secondary">Volver</button>
                <button type="submit" class="btn btn-primary rounded-pill me-1 btnGuardar">Guardar</button>

            </div>
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
