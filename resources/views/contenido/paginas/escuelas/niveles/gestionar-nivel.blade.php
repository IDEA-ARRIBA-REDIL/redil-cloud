{{-- Marcar la sección del menú como activa --}}
@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

{{-- Título de la página --}}
@section('title', 'Gestionar Nivel ')

{{-- Estilos específicos de Vendor (copiados de crear-materia) --}}
@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        // 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', // Descomenta si usas date pickers
        'resources/assets/vendor/libs/quill/typography.scss',
        'resources/assets/vendor/libs/quill/editor.scss',
        // 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss' // Descomenta si usas bootstrap-select
    ])
@endsection

{{-- Scripts específicos de Vendor (copiados de crear-materia) --}}
@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/select2/select2.js',
        // 'resources/assets/vendor/libs/flatpickr/flatpickr.js', // Descomenta si usas date pickers
        'resources/assets/vendor/libs/quill/quill.js',
        // 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js' // Descomenta si usas bootstrap-select
    ])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

{{-- Scripts específicos de la página --}}
@section('page-script')
    <script type="module">
        // --- Inicialización del Editor Quill para Descripción del Nivel ---
        const editorNivel = new Quill('#editorDescripcionNivel', { // ID único para este editor
            bounds: '#editorDescripcionNivel',
            placeholder: 'Descripción detallada del nivel (opcional)',
            modules: {
                toolbar: [ // Barra de herramientas estándar
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
                ]
                // Si necesitas redimensionar imágenes en Quill, añade:
                // imageResize: { modules: ['Resize', 'DisplaySize'] }
            },
            theme: 'snow'
        });

        // Precargar contenido si viene de 'old' (error de validación)
        editorNivel.root.innerHTML = `{!! old('descripcion', $nivel->descripcion) !!}`;

        // Sincronizar contenido de Quill con el input hidden
        editorNivel.on('text-change', () => {
            document.getElementById('descripcionOcultaNivel').value = editorNivel.root.innerHTML;
        });

        // --- Inicialización de CropperJS (igual que en crear-materia) ---
        var croppingImageNivel = document.querySelector('#croppingImageNivel'), // Usar ID específico si es necesario
            cropBtnNivel = document.querySelector('.crop'), // Asumiendo que el botón tiene la clase .crop
            croppedImgNivel = document.querySelector('.cropped-img'), // El <img> de previsualización
            uploadNivel = document.querySelector('#cropperImageUploadNivel'), // Input file específico
            modalImgNivel = document.querySelector('#modalFoto'), // El modal
            inputResultadoNivel = document.querySelector('#imagen-recortada-nivel'), // Input hidden específico
            cropperNivel = '';

        if (modalImgNivel) { // Solo inicializar si el modal existe
            // Usar un event listener para inicializar Cropper cuando el modal se muestre por primera vez
            // Esto evita problemas si la imagen no es visible inicialmente.
            modalImgNivel.addEventListener('shown.bs.modal', function() {
                if (!cropperNivel && croppingImageNivel) { // Inicializar solo una vez
                    cropperNivel = new Cropper(croppingImageNivel, {
                        zoomable: false,
                        aspectRatio: 16 / 5, // Aspect ratio para portada de nivel (ajustar si es necesario)
                        viewMode: 1,
                        responsive: true,
                        cropBoxResizable: true,
                        autoCropArea: 0.9 // Iniciar con un área de recorte grande
                    });
                }
            }, {
                once: true
            }); // Ejecutar el listener solo la primera vez

            // Manejar cambio en el input de archivo
            if (uploadNivel) {
                uploadNivel.addEventListener('change', function(e) {
                    if (e.target.files.length) {
                        var fileType = e.target.files[0].type;
                        if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
                            if (cropperNivel) {
                                cropperNivel.destroy(); // Destruir instancia anterior
                            }
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                if (event.target.result) {
                                    croppingImageNivel.src = event.target.result;
                                    cropperNivel = new Cropper(croppingImageNivel, { // Re-inicializar
                                        zoomable: false,
                                        aspectRatio: 16 / 5,
                                        viewMode: 1,
                                        responsive: true,
                                        cropBoxResizable: true,
                                        autoCropArea: 0.9
                                    });
                                }
                            };
                            reader.readAsDataURL(e.target.files[0]);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Tipo no soportado',
                                text: 'Selecciona JPG, PNG o GIF.'
                            });
                        }
                    }
                });
            }

            // Manejar click en el botón de recortar
            if (cropBtnNivel) {
                cropBtnNivel.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (cropperNivel) {
                        let imgSrc = cropperNivel.getCroppedCanvas({
                            // width: 1600, height: 500 // Opcional: definir tamaño final
                        }).toDataURL('image/jpeg'); // o image/png

                        if (croppedImgNivel) croppedImgNivel.src = imgSrc;
                        if (inputResultadoNivel) inputResultadoNivel.value = imgSrc;
                    }
                });
            }
        } // Fin de if (modalImgNivel)

        // --- Inicialización de Select2 (igual que en crear-materia) ---
        $('.select2').each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
                placeholder: $this.data('placeholder') || 'Seleccionar',
                allowClear: true,
                dropdownParent: $this.parent()
            });
        });


        // --- Validación del Formulario Antes de Enviar (adaptado para nivel) ---
        $('#formNuevoNivel').on('submit', function(e) {
            e.preventDefault(); // Prevenir envío normal
            let form = this;
            let errors = []; // Array para posibles errores futuros

            // Validación 1: Nombre del Nivel (ya cubierto por 'required' HTML)

            // Validación 2: ¿Advertir si no hay Pasos de Crecimiento? (Opcional)
            let pasoInicio = $('[name="paso_iniciar_id"]').val();
            let pasoFin = $('[name="paso_culminar_id"]').val();

            if (!pasoInicio && !pasoFin) {
                Swal.fire({
                    title: '¿Confirmar?',
                    text: "Estás creando un nivel sin pasos de crecimiento inicial o final asociados. ¿Deseas continuar?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary me-2',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Si el usuario confirma, ahora sí envía el formulario
                        form.submit();
                    }
                    // Si cancela, no hace nada
                });
            } else {
                // Si hay pasos seleccionados o no se requiere la advertencia, envía directamente
                form.submit();
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            // Función para alternar la visibilidad del contenedor de asistencias mínimas
            $('#togglehabilitarAsistencias').on('change', function() {
                if ($('#togglehabilitarAsistencias').is(':checked')) {
                    $('#containesAsistenciasMinimas').removeClass('d-none').show();
                } else {
                    $('#containesAsistenciasMinimas').addClass('d-none').hide();
                }
            });

            $('.select2').select2({
                placeholder: 'Seleccionar opciones',
                allowClear: true
            });

        });
    </script>

    <script>
        $(document).ready(function() {
            // Función para validación antes de enviar el formulario
            $('#formNuevaMateria').on('submit', function(e) {
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
                let pasoFin = $('[name="paso_culminar_id"]').val();

                if (!pasoInicio && !pasoFin) {
                    Swal.fire({
                        title: '¿Está seguro?',
                        text: "Está creando una materia sin pasos de crecimiento relacionados",
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
    </script>
@endsection

@section('content')
    {{-- Formulario principal para crear el nivel --}}
    <form id="formNuevoNivel" action="{{ route('niveles.actualizar', $nivel) }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Sección Portada (copiada y adaptada de crear-materia) --}}

        <div class="col-md-12">
            <div class="card mb-4 rounded rounded-3">
                <img id="preview-foto" class="cropped-img card-img-top mb-2"
                    @if ($nivel->portada == 'default.png') src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/niveles/default.png') }}"
                @else
                    src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/niveles/' . $nivel->id) }}" @endif
                    {{-- Cambia a ruta por defecto para niveles --}} alt="Portada Nivel {{ $nivel->nombre }}"
                    style="aspect-ratio: 16 / 5; object-fit: cover;"> {{-- Estilo para mantener proporciones --}}
                <button type="button" style="background-color: rgba(255, 255, 255, 0.5);"
                    class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2"
                    data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;"
                        class="ti ti-camera"></i></button>
                {{-- Input oculto para la imagen recortada --}}
                <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada-nivel"
                    name="foto">

                <div class="row p-4 m-0 d-flex card-body">
                    <h5 class="mb-1 fw-semibold text-black">Actualizar Nivel</h5>
                    <p class="text-black">Define la información y configuración del nuevo nivel académico.</p>
                </div>
            </div>
        </div>
        {{-- Mensajes de estado/error --}}
        @include('layouts.status-msn')

        {{-- Contenedor principal con columnas --}}
        <div class="row equal-height-row">

            {{-- Columna Izquierda: Configuración Inicial del Nivel --}}
            <div class="col equal-height-col col-12 col-md-6">
                <div class="card h-100 p-6">
                    <h5 class="mb-4">Configuración inicial</h5>


                    {{-- Nombre del Nivel --}}
                    <div class="mb-3 col-12">
                        <label for="nombre" class="form-label">Nombre del Nivel <span class="text-danger">*</span></label>
                        <input value="{{ old('nombre', $nivel->nombre) }}" type="text" class="form-control"
                            id="nombre" name="nombre" required>
                        @error('nombre')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="    min-height: 75px;" class="row">

                        <div class="small col-12 fw-medium mb-3">¿Habilitar asistencia?</div>
                        <div style="margin-top: -12px;" class="col-6">
                            <label class="switch switch-lg">
                                <input type="checkbox" @checked(old('habilitarAsistencias', $nivel->habilitar_asistencias)) class="switch-input"
                                    id="togglehabilitarAsistencias" name="habilitarAsistencias" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                            @error('habilitarAsistencias')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div style="    margin-top: -40px;" id="containesAsistenciasMinimas"
                            class="mb-3 {{ old('habilitarAsistencias', $nivel->habilitar_asistencias) ? '' : 'd-none' }} col-6">
                            <label for="asistenciasMinimas" class="form-label">Asistencias
                                Mínimas (opcional)</label>
                            <input value="{{ old('asistenciasMinimas', $nivel->asistencias_minimas) }}" type="number"
                                class="form-control" id="asistenciasMinimas" name="asistenciasMinimas">
                            @error('asistenciasMinimas')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                    </div>
                    <div style="    min-height: 75px;" class="row">

                        <div class="small col-12 fw-medium mb-3">¿Habilitar alerta inasistencia?</div>
                        <div style="margin-top: -12px;" class="col-6">
                            <label class="switch switch-lg">
                                <input type="checkbox" @checked(old('habilitarInasistencias', $nivel->habilitar_inasistencias)) class="switch-input"
                                    id="togglehabilitarInasistencias" name="habilitarInasistencias" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                            </label>
                            @error('habilitarInasistencias')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div style="    margin-top: -40px;" id="containesAsistenciasAlerta"
                            class="mb-3 {{ old('habilitarAsistencias', $nivel->habilitar_inasistencias) ? '' : 'd-none' }} col-6">
                            <label for="asistenciasAlerta" class="form-label">Cantidad inasistencia (alerta)</label>
                            <input value="{{ old('cantidadInasistencias', $nivel->asistencias_minima_alerta) }}"
                                type="number" class="form-control" id="cantidadInasistencias" name="cantidadInasistencias">
                            @error('cantidadInasistencias')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                    </div>

                    <div class="row">

                        <div class="mb-3  col-12 col-md-6 ">
                            <div class="small fw-medium mb-3">¿Habilitar calificaciones?</div>
                            <label class="switch switch-lg">
                                <input type="checkbox" @checked(old('habilitarCalificaciones', $nivel->habilitar_calificaciones)) class="switch-input"
                                    id="togglehabilitarCalificaciones" name="habilitarCalificaciones" />
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
                            <div class="small fw-medium mb-3">¿Habilitar traslado?</div>
                            <label class="switch switch-lg">
                                <input type="checkbox" @checked(old('habilitarTraslado', $nivel->habilitar_traslado)) class="switch-input"
                                    id="togglehabilitarTraslado" name="habilitarTraslado" />
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
                            <div class="small fw-medium mb-3">¿Cáracter obligatorio?</div>
                            <label class="switch switch-lg">
                                <input type="checkbox" @checked(old('obligatorio', $nivel->caracter_obligatorio)) class="switch-input"
                                    id="toggleobligatorio" name="obligatorio" />
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
            </div> {{-- Fin Columna Izquierda --}}

            {{-- Columna Derecha: Configuración de Progreso y Prerrequisitos --}}
            <div class=" col equal-height-col  col-12 col-md-6 ">
                <div class="card h-100 p-6">

                    <h5 class="mb-4 ">Configuración de progreso</h5>

                    {{-- Paso al Iniciar --}}
                    <div class="col-12 col-md-12 mb-3"> {{-- Añadido mb-3 --}}
                        <label class="form-label">Paso al iniciar (Opcional)</label>
                        <select class="form-select select2" name="paso_iniciar_id"
                            data-placeholder="Seleccionar paso inicial">
                            <option value="">Seleccionar paso inicial</option>
                            {{-- MODIFICACIÓN: Usar formato ID|Estado --}}
                            @foreach ($pasosCrecimiento as $paso)
                                <option value="{{ $paso->id_paso }}|{{ $paso->estado }}"
                                    {{ $pasoIniciarSeleccionado == $paso->id_paso . '|' . $paso->estado ? 'selected' : '' }}>
                                    {{ $paso->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('paso_iniciar_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Paso al Culminar --}}
                    <div class="col-12 col-md-12 mb-3"> {{-- Añadido mb-3 --}}
                        <label class="form-label">Paso al culminar (Opcional)</label>
                        <select class="form-select select2" name="paso_culminar_id"
                            data-placeholder="Seleccionar paso final">
                            <option value="">Seleccionar paso final</option>
                            {{-- MODIFICACIÓN: Usar formato ID|Estado --}}
                            @foreach ($pasosCrecimiento as $paso)
                                <option value="{{ $paso->id_paso }}|{{ $paso->estado }}"
                                    {{ $pasoCulminarSeleccionado == $paso->id_paso . '|' . $paso->estado ? 'selected' : '' }}>
                                    {{ $paso->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('paso_culminar_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>


                    <h5 class="mb-4 mt-4">Configuración de prerrequisitos</h5>

                    {{-- Prerrequisitos de Nivel (Select Múltiple) - Sin cambios en el value --}}
                    <div class="mb-3 col-12">
                        <label for="prerrequisitos" class="form-label">Niveles requeridos (Opcional)</label>
                        <select id="prerrequisitos" class="form-select select2" name="prerrequisitos[]" multiple
                            data-placeholder="Seleccionar niveles requeridos">

                            @foreach ($otrosNiveles as $otroNivel)
                                <option value="{{ $otroNivel->id }}"
                                    {{ in_array($otroNivel->id, $prerrequisitosActuales) ? 'selected' : '' }}>
                                    {{ $otroNivel->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('prerrequisitos')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        @error('prerrequisitos.*')
                            <span class="text-danger d-block mt-1">Uno de los niveles prerrequisito no es válido.</span>
                        @enderror
                        <small class="form-text text-muted">Niveles que deben aprobarse antes de poder cursar este.</small>
                    </div>

                    {{-- Prerrequisitos de Proceso (Select Múltiple) --}}
                    <div class="mb-3 col-12">
                        <label for="procesos_prerrequisito" class="form-label">Procesos requeridos (Opcional)</label>
                        <select id="procesos_prerrequisito" class="form-select select2" name="procesos_prerrequisito[]"
                            multiple data-placeholder="Seleccionar procesos requeridos">
                            {{-- MODIFICACIÓN: Usar formato ID|Estado --}}
                            @foreach ($pasosCrecimiento as $paso)
                                <option value="{{ $paso->id_paso }}|{{ $paso->estado }}" {{-- Revisa si necesitas la lógica compleja de preselección como en crear-materia, adaptada para 'old' --}}
                                    {{ is_array(old('procesos_prerrequisito', $procesosPrerrequisitoActuales)) && in_array($paso->id_paso . '|' . $paso->estado, old('procesos_prerrequisito', $procesosPrerrequisitoActuales)) ? 'selected' : '' }}>
                                    {{ $paso->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('procesos_prerrequisito')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        @error('procesos_prerrequisito.*')
                            <span class="text-danger d-block mt-1">Uno de los procesos prerrequisito no es válido.</span>
                        @enderror
                        <small class="form-text text-muted">Pasos de crecimiento que deben completarse antes de iniciar
                            este nivel.</small>
                    </div>
                </div>
            </div>{{-- Fin Columna Derecha --}}

            {{-- Descripción del Nivel (en una fila/tarjeta aparte, ancho completo) --}}
            <div class="col-12 mt-4"> {{-- mt-4 para separar de las columnas --}}
                <div class="card p-6">
                    <label for="editorDescripcionNivel" class="form-label">Descripción detallada (Opcional)</label>
                    {{-- Contenedor para el editor Quill --}}
                    <div id="editorDescripcionNivel" style="min-height: 200px;"></div>
                    {{-- Input oculto para guardar el HTML de Quill --}}
                    <input type="hidden" id="descripcionOcultaNivel" name="descripcion"
                        value="{{ old('descripcion') }}">
                    @error('descripcion')
                        <span class="text-danger d-block mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            {{-- Botones de Acción Finales --}}
            <div class="col-12 mt-4">
                <div class="d-flex justify-content-start">
                    <button type="submit" class="btn btn-primary rounded-pill me-2 btnGuardar">Actualizar nivel</button>
                    <a href="" class="btn btn-label-secondary rounded-pill">Cancelar</a>
                </div>
            </div>

        </div> {{-- Fin de .row.equal-height-row --}}
    </form>

    {{-- Modal para la Foto (copiado de crear-materia) --}}
    <div class="modal fade modal-img" id="modalFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple"> {{-- Usar modal-lg para más espacio --}}
            <div class="modal-content p-3 p-md-5">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-camera ti-lg me-1"></i> Cambiar Portada del nivel</h3>
                        <p class="text-muted">Selecciona y recorta la imagen.</p>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label"><span class="fw-bold">Paso 1:</span> Selecciona la imagen</label>
                            {{-- Usar ID específico para el input file --}}
                            <input class="form-control" type="file" id="cropperImageUploadNivel"
                                accept="image/png, image/jpeg, image/gif">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label"><span class="fw-bold">Paso 2:</span> Recorta la imagen (Aspecto
                                16:5)</label>
                            <div style="max-height: 400px; overflow: hidden;">
                                {{-- Usar ID específico para la imagen del cropper --}}

                            </div>
                        </div>
                    </div>
                    <div class="col-12 text-center mt-4">
                        {{-- El botón .crop aplicará el recorte y cerrará --}}
                        <button type="button" class="btn rounded-pill btn-primary crop me-sm-3 me-1"
                            data-bs-dismiss="modal">Aplicar recorte</button>
                        <button type="reset" class="btn rounded-pill btn-label-secondary" data-bs-dismiss="modal"
                            aria-label="Close">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
