@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Actividades')

<!-- Page -->
@section('page-style')


@section('vendor-style')
<style>
    .color-picker-container {
        width: 100px;
        /* Ajusta este valor al tamaño que necesites */

    }

    .pickr .pcr-button {
        height: 38px !important;
        width: 40px !important;
        border: solid 1px #3e3e3e;
    }
    }

    /* Custom Radio Cards for Gender Selection */
    .custom-radio-card {
        border: 1px solid #d9dee3;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #fff;
        height: 100%;
    }

    .custom-radio-card:hover {
        border-color: #696cff;
        background-color: #f8f9fa;
    }

    .custom-radio-card:has(input[type="radio"]:checked) {
        border-color: #696cff;
        background-color: #f7f7ff;
        box-shadow: 0 2px 4px rgba(105, 108, 255, 0.15);
    }

    .custom-radio-card .form-check-input {
        width: 1.2em;
        height: 1.2em;
        cursor: pointer;
        float: right;
    }

    .custom-radio-card span.fw-medium {
        font-weight: 500 !important;
        color: #566a7f;
    }

    .custom-radio-card:has(input[type="radio"]:checked) span.fw-medium {
        color: #696cff;
    }
</style>


@vite(['resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])


@endsection


@section('vendor-script')
@vite(['resources/js/app.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])

@endsection


@section('page-script')

<script type="module">
    const editor = new Quill('#descripcion', {
            bounds: '#descripcion',
            placeholder: 'Escribe aquí la conclusión de tu actividad',
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

        // Decodificar entidades HTML antes de cargar
        const decodedContent = document.createElement('textarea');
        decodedContent.innerHTML = '{!! html_entity_decode($actividad->descripcion) !!}';
        editor.root.innerHTML = decodedContent.value;

        editor.on('text-change', (delta, oldDelta, source) => {
            $('#contenidoDescripcion').val(editor.root.innerHTML);
        });
    </script>

<script type="module">
    const editor = new Quill('#instruccionesFinales', {
            bounds: '#instruccionesFinales',
            placeholder: 'Escribe aquí la conclusión de tu actividad',
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

        // Decodificar entidades HTML antes de cargar
        const decodedContent = document.createElement('textarea');
        decodedContent.innerHTML = '{!! html_entity_decode($actividad->instrucciones_finales) !!}';
        editor.root.innerHTML = decodedContent.value;

        editor.on('text-change', (delta, oldDelta, source) => {
            $('#contenidoFinal').val(editor.root.innerHTML);
        });
    </script>


<script type="module">
    const editorTerminos = new Quill('#terminosCondiciones', {
            bounds: '#terminosCondiciones',
            placeholder: 'Escribe aquí los términos y condiciones...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'font': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
                    [{ 'indent': '-1' }, { 'indent': '+1' }],
                    ['link', 'image', 'video'],
                    ['clean']
                ],
                imageResize: {
                    modules: ['Resize', 'DisplaySize']
                },
            },
            theme: 'snow'
        });

        // Decodificar entidades HTML antes de cargar
        const decodedContentTerminos = document.createElement('textarea');
        decodedContentTerminos.innerHTML = '{!! html_entity_decode($actividad->terminos_y_condiciones) !!}';
        editorTerminos.root.innerHTML = decodedContentTerminos.value;

        editorTerminos.on('text-change', (delta, oldDelta, source) => {
            $('#contenidoTerminos').val(editorTerminos.root.innerHTML);
        });
    </script>

<script type="module">
    $(".fecha-picker").flatpickr({
            dateFormat: "Y-m-d",
            disableMobile: true
        });

        $(document).ready(function() {

            $('.select2').select2({
                width: '100px',
                allowClear: true,
                placeholder: 'Ninguno',
                dropdownParent: $('#formulario')
            });

            // Picker color for first input
            const monolithPicker1 = document.querySelector('#color-picker-monolith-1');
            const inputColor1 = document.getElementById('fondo');

            const pickr1 = pickr.create({
                el: monolithPicker1,
                theme: 'monolith',
                default: '{{ $actividad->fondo ? $actividad->fondo : "rgba(102, 108, 232, 1)" }}',
                swatches: [
                    'rgba(102, 108, 232, 1)',
                    'rgba(40, 208, 148, 1)',
                    'rgba(255, 73, 97, 1)',
                    'rgba(255, 145, 73, 1)',
                    'rgba(30, 159, 242, 1)'
                ],
                components: {
                    preview: true,

                    hue: true,
                    interaction: {
                        hex: true,
                        rgba: true,
                        cmyk: true,
                        input: true,
                        clear: true,
                        save: true
                    }
                },
                i18n: {
                    // Strings visible in the UI
                    'ui:dialog': 'color picker dialog 2',
                    'btn:toggle': 'toggle color picker dialog 2',
                    'btn:swatch': 'color swatch 2',
                    'btn:last-color': 'use previous color 2',
                    'btn:save': 'Guardar',
                    'btn:cancel': 'Cancelar',
                    'btn:clear': 'Limpiar',

                    // Strings used for aria-labels
                    'aria:btn:save': 'save and close 3',
                    'aria:btn:cancel': 'cancel and close 3',
                    'aria:btn:clear': 'clear and close 3',
                    'aria:input': 'color input field 3',
                    'aria:palette': 'color selection area 3',
                    'aria:hue': 'hue selection slider 3',
                    'aria:opacity': 'selection slider 3'
                },
            });

            pickr1.on('save', (color, instance) => {
                inputColor1.value = color.toHEXA().toString();
                pickr1.hide();
            });

            // Picker color for second input
            const monolithPicker2 = document.querySelector('#color-picker-monolith-2');
            const inputColor2 = document.getElementById('letra');

            const pickr2 = pickr.create({
                el: monolithPicker2,
                theme: 'monolith',
                default: '{{ $actividad->color ? $actividad->color : "rgba(214, 214, 214, 1)" }}',
                swatches: [
                    'rgba(102, 108, 232, 1)',
                    'rgba(40, 208, 148, 1)',
                    'rgba(255, 73, 97, 1)',
                    'rgba(255, 145, 73, 1)',
                    'rgba(30, 159, 242, 1)'
                ],
                components: {
                    preview: true,
                    opacity: true,
                    hue: true,
                    interaction: {
                        hex: true,
                        rgba: true,
                        hsla: true,
                        hsva: true,
                        cmyk: true,
                        input: true,
                        clear: true,
                        save: true
                    }
                },
                i18n: {
                    // Strings visible in the UI
                    'ui:dialog': 'color picker dialog 2',
                    'btn:toggle': 'toggle color picker dialog 2',
                    'btn:swatch': 'color swatch 2',
                    'btn:last-color': 'use previous color 2',
                    'btn:save': 'Guardar',
                    'btn:cancel': 'Cancelar',
                    'btn:clear': 'Limpiar',

                    // Strings used for aria-labels
                    'aria:btn:save': 'save and close 3',
                    'aria:btn:cancel': 'cancel and close 3',
                    'aria:btn:clear': 'clear and close 3',
                    'aria:input': 'color input field 3',
                    'aria:palette': 'color selection area 3',
                    'aria:hue': 'hue selection slider 3',
                    'aria:opacity': 'selection slider 3'
                },
            });

            pickr2.on('save', (color, instance) => {
                inputColor2.value = color.toHEXA().toString();
                pickr2.hide();
            });
        });
    </script>


<script type="module">
    function sinComillas(e) {
            tecla = (document.all) ? e.keyCode : e.which;
            patron = /[\x5C'"]/;
            te = String.fromCharCode(tecla);
            return !patron.test(te);
        }
    </script>


<script type="module">
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

        ///confirmación cancelar actividad
        $('.confirmacionCancelar').on('click', function(e) {
            e.preventDefault();
            let nombre = $(this).data('nombre');
            let id = $(this).data('id');
            let checkbox = $(this);

            Swal.fire({
                title: "¿Estás seguro que deseas cancelar la actividad <b>" + nombre + "</b>?",
                html: "Esta acción desaparecera la actividad del calendario para todos los usuarios.",
                icon: "warning",
                showCancelButton: false,
                confirmButtonText: 'Si, Cancelar',
                cancelButtonText: 'Atrás'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#activarActividad').attr('action', "/actividades/" + id + "/cancelar");
                    $('#activarActividad').submit();
                } else {
                     // If cancelled, do nothing (checkbox remains validated as 'checked' visually until reload, or we should revert)
                     // Since preventDefault was called, the checkbox shouldn't have changed visual state if strictly handled.
                     // But in some browsers preventDefault on click works.
                }
            })
        });

        ///confirmación para activar actividad
        $('.confirmacionActivar').on('click', function(e) {
            e.preventDefault();
            let nombre = $(this).data('nombre');
            let id = $(this).data('id');
            let checkbox = $(this);

            Swal.fire({
                title: "¿Estás seguro que deseas Activar la actividad <b>" + nombre + "</b>?",
                html: "Esta acción listara la actividad del calendario para todos los usuarios.",
                icon: "warning",
                showCancelButton: false,
                confirmButtonText: 'Si, Activar',
                cancelButtonText: 'Atrás'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#activarActividad').attr('action', "/actividades/" + id + "/activar");
                    $('#activarActividad').submit();
                }
            })
        });
    </script>

<script>
    $('#vistaTodos').on('change', function() {

        if ($(this).is(":checked")) {
            $('#container-restricciones').addClass('d-none');
        } else {

            $('#container-restricciones').removeClass('d-none');


        }
    });

    $('#restriccionCategoria').on('change', function() {

        if ($(this).is(":checked")) {
            $('#containerVistaTodos').addClass('d-none');
            $('#vistaTodos').prop("checked", false);
            $('#container-restricciones').addClass('d-none');
            $('#inicioSesion').prop("checked", true);

        } else {
            $('#containerVistaTodos').removeClass('d-none');
            if ($('#vistaTodos').is(":checked")) {
                $('#container-restricciones').addClass('d-none');
            } else {
                $('#container-restricciones').removeClass('d-none');
            }



        }
    });

    $('#inicioSesion').on('change', function() {

        if ($(this).is(":checked")) {
            if ($('#vistaTodos').is(":checked")) {
                $('#container-restricciones').addClass('d-none');
            } else {

                $('#vistaTodos').prop("checked", false);
                $('#container-restricciones').removeClass('d-none');
            }


        } else {
            $('#containerVistaTodos').removeClass('d-none');
            if ($('#vistaTodos').is(":checked")) {
                $('#container-restricciones').addClass('d-none');

            } else {

                $('#container-restricciones').addClass('d-none');
            }

        }
    });

</script>
<script>
    @if (session('swal_error'))
    Swal.fire({
        title: 'No es posible',
        html: "{!! session('swal_error') !!}",
        icon: 'error',
        confirmButtonText: 'Entendido'
    });
    @endif
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header Title & Toggle -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary" >Actualizar actividad</h4>
            <p class="mb-0 text-black">Aquí puedes actualizar la información de tu actividad: <b class="text-dark">{{ $actividad->nombre }}</b></p>
        </div>

    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-center gap-3">
             <span class="small fw-semibold">Activar actividad</span>
             <label class="switch switch-lg">
                @if ($actividad->activa == true)
                    <input type="checkbox" class="switch-input confirmacionCancelar" data-nombre="{{ $actividad->nombre }}" data-id="{{ $actividad->id }}" checked>
                @else
                    <input type="checkbox" class="switch-input confirmacionActivar" data-nombre="{{ $actividad->nombre }}" data-id="{{ $actividad->id }}">
                @endif
                <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                </span>
             </label>
        </div>
    </div>

    @include('layouts.status-msn')

    <form id="formulario" role="form" class="forms-sample" method="POST"
        @if ($actividad->tipo->tipo_escuelas == false)
            action="{{ route('actividades.update', $actividad) }}"
        @else
            action="{{ route('actividades.updateEscuelas', $actividad) }}"
        @endif
        enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="accordion" id="accordionActividad">

            <!-- Item 1: Información principal -->
            <div class="accordion-item card mb-3 border-0 shadow-sm active">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Información principal
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionActividad">
                    <div class="accordion-body">
                        <div class="row">
                             <!-- nombre actividad -->
                            <div class="col-12 mb-3">
                                <label class="form-label" for="nombre">Nombre</label>
                                <input type="text" value="{{ old('nombre', $actividad->nombre) }}" class="form-control" id="nombre" name="nombre" placeholder="Ingresa el nombre" />
                            </div>

                            <!-- color fondo-->
                            <div class="col-md-6 mb-3 monolith">
                                <label class="form-label">Color de fondo</label>
                                <div class="input-group">
                                    <div id="color-picker-monolith-1" class="color-picker-container rounded-start"></div>
                                    <input value="{{ $actividad->fondo }}" type="text" id="fondo" name="fondo" placeholder="#......" class="form-control" />
                                </div>
                            </div>

                            <!-- color letra-->
                            <div class="col-md-6 mb-3 monolith">
                                <label class="form-label">Color de letra</label>
                                <div class="input-group">
                                    <div id="color-picker-monolith-2" class="color-picker-container rounded-start"></div>
                                    <input value="{{ $actividad->color }}" type="text" id="letra" name="letra" placeholder="#......" class="form-control" />
                                </div>
                            </div>

                             <!-- monedas actividad-->
                             <div class="col-md-6 mb-3">
                                <label for="monedas" class="form-label">Monedas</label>
                                <select id="monedas" name="monedas[]" class="select2 form-select" multiple data-placeholder="Ingresa la moneda">
                                    @foreach ($monedas as $moneda)
                                    <option value="{{ $moneda->id }}" {{ in_array($moneda->id, old('monedas', $monedasActividad)) ? 'selected' : '' }}>
                                        {{ $moneda->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                             <!-- / tags-->
                             <div class="col-md-6 mb-3">
                                <label class="form-label" for="tags">Tags de referencia</label>
                                <select id="tags" name="tags[]" class="select2 form-select" multiple data-placeholder="Tags asignados">
                                    @foreach ($tagsGenerales as $tagGeneral)
                                    <option {{ in_array($tagGeneral->id, $tagsActividad) ? 'selected' : '' }} value="{{ $tagGeneral->id }}">{{ $tagGeneral->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- tipos pago-->
                            <div class="col-md-6 mb-3">
                                <label for="tiposPago" class="form-label">Tipos de pago</label>
                                <select id="tiposPago" name="tiposPago[]" class="select2 form-select" multiple data-placeholder="Ingresa el tipo de pago">
                                    @foreach ($tiposPago as $tipo)
                                    <option value="{{ $tipo->id }}" {{ in_array($tipo->id, old('tiposPago', $tiposPagoActividad)) ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- incremento PDP-->
                            <div class="col-md-6  mb-3">
                                <label class="form-label" for="incremento">Porcentaje de incremento</label>
                                <input type="number" min="0" max="100" step="1" value="{{ old('incremento', $actividad->incremento_pdp) }}" class="form-control" id="incremento" name="incremento" placeholder="Ingresa un valor" />
                            </div>

                             <!-- campos adicionales-->
                             <div class="col-md-6 mb-3">
                                <label for="camposAdicionales" class="form-label">Campos adicionales actividad</label>
                                <select id="camposAdicionales" name="camposAdicionales[]" class="select2 form-select" multiple data-placeholder="Tags asignados">
                                    @foreach ($camposAdicionales as $campo)
                                    <option value="{{ $campo->id }}" {{ in_array($campo->id, old('camposAdicionales', $camposAdicionalesActividad)) ? 'selected' : '' }}>
                                        {{ $campo->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- habilitado para pdp (Estado inscripcion) -->
                            <div class="col-md-6 mb-3">
                                <label for="estadoInscripcion" class="form-label">Ingresa una opción</label>
                                <select id="estadoInscripcion" name="estadoInscripcion" class="select2 form-select" data-placeholder="Selecciona una opción">
                                    <option @if ($actividad->estado_inscripcion_defecto == 1) selected @endif value="1" {{ old('estadoInscripcion') == 1 ? 'selected' : '' }}>
                                        Iniciada
                                    </option>
                                    <option @if ($actividad->estado_inscripcion_defecto == 2) selected @endif value="2" {{ old('estadoInscripcion') == 2 ? 'selected' : '' }}>
                                        Pendiente
                                    </option>
                                    <option @if ($actividad->estado_inscripcion_defecto == 3) selected @endif value="3" {{ old('estadoInscripcion') == 3 ? 'selected' : '' }}>
                                        Finalizada
                                    </option>
                                </select>
                            </div>

                             <!-- Contraseña -->
                             <div class="mb-3 col-12">
                                <label class="form-label" for="password">Contraseña</label>
                                <input type="text" value="{{ old('password', $actividad->password) }}" class="form-control" id="password" name="password" placeholder="Ingresa un valor" />
                            </div>

                            <!-- TOGGLES ROW -->
                            <div class="col-12 mt-2">
                                <div class="row">
                                     <!-- ¿Tiene invitados? -->
                                    <div class="mb-3 col-md-3">
                                        <div class="small fw-medium mb-1">¿Invitados al grupo?</div>
                                        <label class="switch switch-lg">
                                            <input id="tieneInvitados" name="tieneInvitados" type="checkbox" @checked(old('tieneInvitados', $actividad->tiene_invitados == true)) class="switch-input" />
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                        </label>
                                    </div>

                                    <!-- Habilitado para PD (using PDP field for now based on name similarity or just Habilitado PDP logic) -->
                                    <div class="mb-3 col-md-3">
                                        <div class="small fw-medium mb-1">¿Habilitado para PDP?</div>
                                        <label class="switch switch-lg">
                                            <input id="habilitadoPDP" name="habilitadoPDP" type="checkbox" @checked(old('habilitadoPDP', $actividad->punto_de_pago == true)) class="switch-input habilitadoPDP" />
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                        </label>
                                    </div>

                                     <!-- Pagos/Abonos con valores cerrados -->
                                     <div class="mb-3 col-md-3">
                                        <div class="small fw-medium mb-1">¿Abonos con valores cerrados?</div>
                                        <label class="switch switch-lg">
                                            <input id="valoresCerrados" name="valoresCerrados" type="checkbox" @checked(old('valoresCerrados', $actividad->pagos_abonos_con_valores_cerrados == true)) class="switch-input" />
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                        </label>
                                    </div>

                                     <!-- Permitir editar formulario -->
                                     <div class="mb-3 col-md-3">
                                        <div class="small fw-medium mb-1">¿Permitir editar formulario?</div>
                                        <label class="switch switch-lg">
                                            <input id="editarFormulario" name="editarFormulario" type="checkbox" @checked(old('editarFormulario', $actividad->editar_formulario == true)) class="switch-input" />
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                        </label>
                                    </div>

                                     <!-- Mostrar en próximas actividades -->
                                     <div class="mb-3 col-md-3">
                                        <div class="small fw-medium mb-1">¿Ver en próximas actividades?</div>
                                        <label class="switch switch-lg">
                                            <input id="mostrarProximas" name="mostrarProximas" type="checkbox" @checked(old('mostrarProximas', $actividad->mostrar_en_proximas_actividades == true)) class="switch-input" />
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                        </label>
                                    </div>

                                     <!-- Vista por todos -->
                                     @if ($actividad->tipo->tipo_escuelas == false)
                                     <div id="containerVistaTodos" class="mb-3 col-md-3 @if ($actividad->restriccion_por_categoria == true) d-none @endif">
                                         <div class="small fw-medium mb-1">¿Vista por todos?</div>
                                         <label class="switch switch-lg">
                                             <input id="vistaTodos" name="vistaTodos" type="checkbox" @checked(old('vistaTodos', $actividad->totalmente_publica == true)) class="switch-input vistaTodos" />
                                             <span class="switch-toggle-slider">
                                                 <span class="switch-on"></span>
                                                 <span class="switch-off"></span>
                                             </span>
                                         </label>
                                     </div>
                                     @endif

                                </div>
                            </div>

                            @if ($actividad->tipo->tipo_escuelas == true)
                            <div class="col-12 mt-3">
                                <label for="periodoRelacionado" class="form-label">Elige el periodo relacionado</label>
                                <select required id="periodoRelacionado" name="periodoRelacionado" class="select2 form-select">
                                    @foreach ($periodos as $periodo)
                                    <option @if ($actividad->periodo_id == $periodo->id) selected @endif value="{{ $periodo->id }}">
                                        {{ $periodo->nombre }} // Escuela: {{ $periodo->escuela->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            <!-- Item: Términos y Condiciones -->
            <div class="accordion-item card mb-3 border-0 shadow-sm">
                <h2 class="accordion-header" id="headingTerms">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTerms" aria-expanded="false" aria-controls="collapseTerms">
                        Términos y Condiciones
                    </button>
                </h2>
                <div id="collapseTerms" class="accordion-collapse collapse" aria-labelledby="headingTerms" data-bs-parent="#accordionActividad">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Contenido</label>
                                <div id="terminosCondiciones" style="min-height: 150px;"></div>
                                <input type="hidden" name="contenidoTerminos" id="contenidoTerminos">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item 2: Restricciones de la actividad -->
            @if ($actividad->tipo->tipo_escuelas == false)
            <div class="accordion-item card mb-3 border-0 shadow-sm">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Restricciones de la actividad
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionActividad">
                    <div class="accordion-body">
                         <div class="row">
                             <div class="mb-3 col-12">
                                <div class="small fw-medium mb-1">Activar restricciones por categoria</div>
                                <label class="switch switch-lg">
                                    <input id="restriccionCategoria" name="restriccionCategoria" type="checkbox" @checked(old('restriccionCategoria', $actividad->restriccion_por_categoria == true)) class="switch-input" />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on"></span>
                                        <span class="switch-off"></span>
                                    </span>
                                </label>
                            </div>

                            <div id="container-restricciones" class="row w-100 m-0 p-0
                                @if ($actividad->restriccion_por_categoria == false) @else
                                     @if ($actividad->restriccion_por_categoria == true || $actividad->totalmente_publica == true)
                                         d-none @endif
                                 @endif">

                                <div class="mb-3 col-md-6">
                                    <div class="small fw-medium mb-1">¿Es excluyente con otras actividades?</div>
                                    <label class="switch switch-lg">
                                        <input id="excluyente" name="excluyente" type="checkbox" @checked(old('excluyente', $actividad->excluyente == true)) class="switch-input excluyente" />
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on"></span>
                                            <span class="switch-off"></span>
                                        </span>
                                    </label>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label d-block mb-2 me-2">Género</label>
                                    <div class="row">
                                        <div class="col-md-4 mb-2  ">
                                            <label class="custom-radio-card w-100 border rounded p-4" for="genero_1">
                                                <span class="fw-medium text-black">Masculino</span>
                                                <input class="form-check-input mt-0 " type="radio" name="generos" id="genero_1" value="1"
                                                    {{ old('generos', $actividad->genero) == 1 ? 'checked' : '' }}>
                                            </label>
                                        </div>
                                        <div class="col-md-4 mb-2  ">
                                            <label class="custom-radio-card w-100 border rounded p-4" for="genero_2">
                                                <span class="fw-medium text-black">Femenino</span>
                                                <input class="form-check-input mt-0" type="radio" name="generos" id="genero_2" value="2"
                                                    {{ old('generos', $actividad->genero) == 2 ? 'checked' : '' }}>
                                            </label>
                                        </div>
                                        <div class="col-md-4 mb-2  ">
                                            <label class="custom-radio-card w-100 border rounded p-4" for="genero_3">
                                                <span class="fw-medium text-black">Ambos</span>
                                                <input class="form-check-input mt-0" type="radio" name="generos" id="genero_3" value="3"
                                                    {{ old('generos', $actividad->genero) == 3 ? 'checked' : '' }}>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="vinculacionGrupo" class="form-label">Definir vinculación a grupo</label>
                                    <select id="vinculacionGrupo" name="vinculacionGrupo" class="select2 form-select">
                                        <option @if ($actividad->vinculacion_grupo == 1) selected @endif value="1">Pertenece a grupo</option>
                                        <option @if ($actividad->vinculacion_grupo == 2) selected @endif value="2">No pertenece</option>
                                        <option @if ($actividad->vinculacion_grupo == 3) selected @endif value="3">Ambos</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="actividadGrupo" class="form-label">Definir actividad en grupo</label>
                                    <select id="actividadGrupo" name="actividadGrupo" class="select2 form-select">
                                        <option @if ($actividad->actividad_grupo == 1) selected @endif value="1">Activos</option>
                                        <option @if ($actividad->actividad_grupo == 2) selected @endif value="2">Inactivos</option>
                                        <option @if ($actividad->actividad_grupo == 3) selected @endif value="3">Ambos</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="sedes" class="form-label">Sedes habilitadas</label>
                                    <select id="sedes" name="sedes[]" class="select2 form-select" multiple>
                                        @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}" {{ in_array($sede->id, old('sedes', $sedesActividad)) ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="rangosEdad" class="form-label">Definir rangos de edad</label>
                                    <select id="rangosEdad" name="rangosEdad[]" class="select2 form-select" multiple>
                                        @foreach ($rangosEdad as $rango)
                                        <option value="{{ $rango->id }}" {{ in_array($rango->id, old('rangosEdad', $rangosEdadActividad)) ? 'selected' : '' }}>{{ $rango->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="tipoUsuarios" class="form-label">Definir tipo usuario</label>
                                    <select id="tipoUsuarios" name="tipoUsuarios[]" class="select2 form-select" multiple>
                                        @foreach ($tipoUsuarios as $tipo)
                                        <option value="{{ $tipo->id }}" {{ in_array($tipo->id, old('tipoUsuarios', $tipoUsuariosActividad)) ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="tipoUsuarioObjetivo" class="form-label">Definir tipo usuario objetivo (Cambio por Asistencia)</label>
                                    <select id="tipoUsuarioObjetivo" name="tipoUsuarioObjetivo" class="select2 form-select">
                                        <option value="">Seleccione...</option>
                                        @foreach ($tipoUsuariosObjetivo as $tipo)
                                        <option value="{{ $tipo->id }}" {{ old('tipoUsuarioObjetivo', $actividad->tipo_usuario_objetivo_id) == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="estadosCiviles" class="form-label">Definir estados civiles</label>
                                    <select id="estadosCiviles" name="estadosCiviles[]" class="select2 form-select" multiple>
                                        @foreach ($estadosCiviles as $estado)
                                        <option value="{{ $estado->id }}" {{ in_array($estado->id, old('estadosCiviles', $estadosCivilesActividad)) ? 'selected' : '' }}>{{ $estado->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="tipoServicios" class="form-label">Definir tipos servicios</label>
                                    <select id="tipoServicios" name="tipoServicios[]" class="select2 form-select" multiple>
                                        @foreach ($tipoServicios as $tipoSer)
                                        <option value="{{ $tipoSer->id }}" {{ in_array($tipoSer->id, old('tipoServicios', $tipoServiciosActividad)) ? 'selected' : '' }}>{{ $tipoSer->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

             <!-- Item: Procesos y Tareas -->
             <div class="accordion-item card mb-3 border-0 shadow-sm">
                <h2 class="accordion-header" id="headingTareas">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTareas" aria-expanded="false" aria-controls="collapseTareas">
                        Procesos y tareas de crecimiento
                    </button>
                </h2>
                <div id="collapseTareas" class="accordion-collapse collapse" aria-labelledby="headingTareas" data-bs-parent="#accordionActividad">
                    <div class="accordion-body">
                        <div class="mb-5 border rounded-3 p-3">
                            @livewire('actividad.gestionar-pasos-requisito', ['actividad' => $actividad])

                            @livewire('actividad.gestionar-pasos-culminados', ['actividad' => $actividad])
                        </div>
                         <div class="mb-5 border rounded-3 p-3">
                            @livewire('actividad.gestionar-tareas-requisito', ['actividad' => $actividad])

                            @livewire('actividad.gestionar-tareas-culminadas', ['actividad' => $actividad])
                        </div>
                    </div>
                </div>
            </div>

            @endif

            <!-- Item 3: Información actividad -->
            <div class="accordion-item card mb-3 border-0 shadow-sm">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        Información actividad
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionActividad">
                    <div class="accordion-body">
                         <div class="row">
                             <div class="col-12 mb-3">
                                <label for="descripcionCorta" class="form-label">Descripción Corta</label>
                                <textarea class="form-control" style="width:100%" maxlength="300" id="descripcionCorta" name="descripcionCorta" placeholder="Limite de 300 caracteres">{{ $actividad->descripcion_corta }}</textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="mensajeInformativo" class="form-label">Mensaje informativo</label>
                                <textarea class="form-control" style="width:100%" maxlength="500" id="mensajeInformativo" name="mensajeInformativo" placeholder="Limite de 500 caracteres">{{ $actividad->mensaje_informativo }}</textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Descripción</label>
                                <div id="descripcion" class="mt-2"></div>
                                <input id="contenidoDescripcion" name="contenidoDescripcion" class='d-none'>
                            </div>
                             <div class="col-12 mb-3">
                                <label class="form-label">Instrucciones finales</label>
                                <div id="instruccionesFinales" class="mt-2"></div>
                                <input id="contenidoFinal" name="contenidoFinal" class='d-none'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item 4: Configuración de fechas -->
             <div class="accordion-item card mb-3 border-0 shadow-sm">
                <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                         Configuración de fechas
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionActividad">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="fecha_visualizacion">Fecha visualización (Inicio de inscripciones)</label>
                                <input id="fecha_visualizacion" value="{{ old('fecha_visualizacion', $actividad->fecha_visualizacion) }}" placeholder="YYYY-MM-DD" name="fecha_visualizacion" class="fecha form-control fecha-picker" type="text" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="fecha_cierre">Fecha cierre inscripciones (limite de compras o inscripciones)</label>
                                <input id="fecha_cierre" value="{{ old('fecha_cierre', $actividad->fecha_cierre) }}" placeholder="YYYY-MM-DD" name="fecha_cierre" class="fecha form-control fecha-picker" type="text" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="fecha_inicio">Fecha inicio (cuando se lleva a cabo la actividad)</label>
                                <input id="fecha_inicio" value="{{ old('fecha_inicio', $actividad->fecha_inicio) }}" placeholder="YYYY-MM-DD" name="fecha_inicio" class="fecha form-control fecha-picker" type="text" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="fecha_fin">Fecha finalización (cuando concluye la actividad)</label>
                                <input id="fecha_fin" value="{{ old('fecha_fin', $actividad->fecha_finalizacion) }}" placeholder="YYYY-MM-DD" name="fecha_fin" class="fecha form-control fecha-picker" type="text" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item 5: Configuraciones SAP -->
            <div class="accordion-item card mb-3 border-0 shadow-sm">
                <h2 class="accordion-header" id="headingFive">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                        Configuraciones SAP
                    </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionActividad">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="cuentaSAP">Cuenta SAP</label>
                                <input type="text" value="{{ old('cuentaSAP', $actividad->codigo_sap) }}" class="form-control" id="cuentaSAP" name="cuentaSAP" placeholder="cuenta SAP" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="proyectoSAP">Proyecto SAP</label>
                                <input type="text" value="{{ old('proyectoSAP', $actividad->proyecto_sap) }}" class="form-control" id="proyectoSAP" name="proyectoSAP" placeholder="proyecto SAP" />
                            </div>
                             <div class="col-md-6 mb-3">
                                <label class="form-label" for="centro_costoSAP">Centro costo de SAP</label>
                                <input type="text" value="{{ old('centro_costoSAP', $actividad->centro_costo_sap) }}" class="form-control" id="centro_costoSAP" name="centro_costoSAP" placeholder="Centro SAP" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="labelDestinatario">Label destinatario</label>
                                <input type="text" value="{{ old('labelDestinatario', $actividad->label_destinatario) }}" class="form-control" id="labelDestinatario" name="labelDestinatario" placeholder="Ejemplo: selecciona la sede" />
                            </div>
                             <div class="col-12 mb-3">
                                <label for="destinatarios" class="form-label">Destinatarios </label>
                                <select id="destinatarios" name="destinatarios[]" class="select2 form-select" multiple>
                                    @foreach ($destinatarios as $destinatario)
                                    <option value="{{ $destinatario->id }}" {{ in_array($destinatario->id, old('destinatarios', $destinatariosActividad)) ? 'selected' : '' }}>{{ $destinatario->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item 6: Evaluar actividad -->
            <div class="accordion-item card mb-3 border-0 shadow-sm">
                <h2 class="accordion-header" id="headingSix">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                         Evaluar actividad
                    </button>
                </h2>
                <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionActividad">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label" for="evaluacionGeneral">Evaluación general de la actividad:</label>
                                <textarea class="form-control" id="evaluacionGeneral" name="evaluacionGeneral">{{ $actividad->evaluacion_general }}</textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="evaluacionFinanciera">Evaluación financiera de la actividad:</label>
                                <textarea class="form-control" id="evaluacionFinanciera" name="evaluacionFinanciera">{{ $actividad->evaluacion_financiera }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- botonera -->
        <div class="d-flex justify-content-start mt-4 mb-4 pb-5">
            <button type="submit" class="btn btn-primary rounded-pill btnGuardar px-5">Guardar cambios</button>
        </div>
        <!-- /botonera -->
    </form>

</div>

<form id="cancelarActividad" method="POST" action="">
    @csrf
</form>

<form id="activarActividad" method="POST" action="">
    @csrf
</form>

@endsection
