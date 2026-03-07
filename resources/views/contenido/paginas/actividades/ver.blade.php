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

</style>


@vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss'])


@endsection


@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js', 'resources/assets/vendor/libs/quill/quill.js'])

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

  editor.root.innerHTML = "{!! old('contenidoDescripcion', $actividad->descripcion) !!}";

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

  editor.root.innerHTML = "{!! old('contenidoFinal', $actividad->instrucciones_finales) !!}";

  editor.on('text-change', (delta, oldDelta, source) => {
    $('#contenidoFinal').val(editor.root.innerHTML);
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
      placeholder: 'Ninguno'
    });

    $('.select2').select2({
      dropdownParent: $('#formulario')
    });

    // Picker color for first input
    const monolithPicker1 = document.querySelector('#color-picker-monolith-1');
    const inputColor1 = document.getElementById('fondo');

    const pickr1 = pickr.create({
      el: monolithPicker1,
      theme: 'monolith',
      default: '{{ $actividad->color != null ? $actividad->fondo : rgba(102, 108, 232, 1) }}',
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
      default: '{{ $actividad->color ? $actividad->color : rgba(214, 214, 214, 1) }}',
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


<script>
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
</script>

<script></script>
@endsection

@section('content')

<div class="row mb-2">
    <ul class="nav nav-pills mb-3 d-flex justify-content-end" role="tablist">

        <li class="nav-item">
            <a href="">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
                    <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">1</span>
                    Datos principales
                </button>
            </a>
        </li>
        <li class="nav-item">
            <a href="">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
                    <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">2</span>
                    Categorias
                </button>
            </a>
        </li>
        <li class="nav-item">
            <a href="">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
                    <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">3</span>
                    Abonos
                </button>
            </a>
        </li>
        <li class="nav-item">
            <a href="">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
                    <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">4</span>
                    Encargados
                </button>
            </a>
        </li>

    </ul>
</div>

<h4 class="mb-1">Modificar Actividad</h4>
<p class="mb-4">Descripción...</p>

@include('layouts.status-msn')

<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('actividades.update', $actividad) }}" enctype="multipart/form-data">
    @csrf
    @method('PATCH')

    <!-- botonera -->
    <div class="d-flex mb-1 mt-5">
        <div class="me-auto">
            <button type="submit" class="btn btn-primary me-1 btnGuardar">Guardar</button>
            <button type="reset" class="btn btn-label-secondary">Cancelar</button>
        </div>
        <div class="p-2 bd-highlight">
            <p class="text-muted">Campos obligatorios</p>
        </div>
    </div>
    <!-- /botonera -->

    <div class="row">

        <div class="col-12 col-lg-8">
            <!-- Información Principal -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-tile mb-0">Información principal</h5>
                </div>
                <div class="row card-body">

                    <div class="mb-3">
                        <label class="form-label" for="eventTitle">Nombre</label>
                        <input type="text" value="{{ old('nombre', $actividad->nombre) }}" class="form-control" id="nombre" name="nombre" placeholder="Nombre Actividad" />
                    </div>
                    <div class="col-6  mb-3 monolith">
                        <label class="form-label">Color fondo</label>
                        <div class="input-group">

                            <div id="color-picker-monolith-1" class="color-picker-container"></div>
                            <input value="{{ $actividad->fondo }}" type="text" id="fondo" name="fondo" placeholder="Color seleccionado" class="form-control" />

                        </div>
                    </div>
                    <div class="col-6  mb-3 monolith">
                        <label class="form-label">Color letra</label>
                        <div class="input-group">
                            <div id="color-picker-monolith-2" class="color-picker-container"></div>
                            <input value="{{ $actividad->color }}" type="text" id="letra" name="letra" placeholder="Color seleccionado" class="form-control" />
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="moneda" class="form-label">Monedas</label>
                        <select id="monedas" name="monedas[]" class="select2 form-select" multiple>
                            @foreach ($monedas as $moneda)
                            <option value="{{ $moneda->id }}" {{ in_array($moneda->id, old('monedas', [])) ? 'selected' : '' }}>
                                {{ $moneda->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 mb-3">
                        <label for="tipospago" class="form-label">Tipos de pago</label>
                        <select id="tiposPago" name="tiposPago[]" class="select2 form-select" multiple>
                            @foreach ($tiposPago as $tipo)
                            <option value="{{ $tipo->id }}" {{ in_array($tipo->id, old('tiposPago', [])) ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6  mb-3">
                        <label class="form-label" for="">% incremento para PDP (Ej. 10%) </label>
                        <input type="number" min="0" max="100" step="1" value="{{ old('nombre') }}" class="form-control" id="incremento" name="incremento" placeholder="Porcentaje inceremento" />
                    </div>
                    <div class="col-6  mb-3">
                        <label for="camposAdicionales" class="form-label">Campos adicionales actividad</label>
                        <select id="camposAdicionales" name="camposAdicionales[]" class="select2 form-select" multiple>
                            @foreach ($camposAdicionales as $campo)
                            <option value="{{ $campo->id }}" {{ in_array($campo->id, old('camposAdicionales', [])) ? 'selected' : '' }}>
                                {{ $campo->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 col-6">
                        <div class=" small fw-medium mb-1">¿Habilitado para PDP?</div>
                        <label class="switch switch-lg">
                            <input id="habilitadoPDP" name="habilitadoPDP" type="checkbox" @checked(old('habilitadoPDP', $actividad->punto_de_pago == true)) class="switch-input habilitadoPDP" />
                            <span class="switch-toggle-slider">
                                <span class="switch-on">SI</span>
                                <span class="switch-off">NO</span>
                            </span>
                            <span class="switch-label"></span>
                        </label>
                    </div>
                    <div class="mb-3 col-6">
                        <div class=" small fw-medium mb-1">¿Es excluyente con otras actividades?</div>
                        <label class="switch switch-lg">
                            <input id="excluyente" name="excluyente" type="checkbox" @checked(old('excluyente', $actividad->excluyente == true))
                            class="switch-input excluyente" />
                            <span class="switch-toggle-slider">
                                <span class="switch-on">SI</span>
                                <span class="switch-off">NO</span>
                            </span>
                            <span class="switch-label"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-tile mb-0">Restricciones de la actividad</h5>
                </div>
                <div class="row card-body">
                    <div class="col-6 mb-3">
                        <label for="generos" class="form-label">Genero</label>
                        <select id="generos" name="generos" class="select2 form-select">
                            <option value="1" {{ old('genero') == 1 ? 'selected' : '' }}>Hombres</option>
                            <option value="2" {{ old('genero') == 2 ? 'selected' : '' }}>Mujeres</option>
                            <option value="3" {{ old('genero') == 3 ? 'selected' : '' }}>Ambos</option>
                        </select>
                    </div>
                    <div class="mb-3 col-6">
                        <div class=" small fw-medium mb-1">¿Vista por todos?</div>
                        <label class="switch switch-lg">
                            <input id="vistaTodos" name="vistaTodos" type="checkbox" @checked(old('vistaTodos', $actividad->totalmente_publica == true))
                            class="switch-input vistaTodos" />
                            <span class="switch-toggle-slider">
                                <span class="switch-on">SI</span>
                                <span class="switch-off">NO</span>
                            </span>
                            <span class="switch-label"></span>
                        </label>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="vinculacionGrupo" class="form-label">Definir vinculación a grupo</label>
                        <select id="vinculacionGrupo" name="vinculacionGrupo" class="select2 form-select">
                            <option value="1" {{ old('genero') == 1 ? 'selected' : '' }}>Pertenece a grupo</option>
                            <option value="2" {{ old('genero') == 2 ? 'selected' : '' }}>No pertenece</option>
                            <option value="3" {{ old('genero') == 3 ? 'selected' : '' }}>Ambos</option>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="actividadGrupo" class="form-label">Definir actividad en grupo</label>
                        <select id="actividadGrupo" name="actividadGrupo" class="select2 form-select">
                            <option value="1" {{ old('genero') == 1 ? 'selected' : '' }}>Activos</option>
                            <option value="2" {{ old('genero') == 2 ? 'selected' : '' }}>Inactivos</option>
                            <option value="3" {{ old('genero') == 3 ? 'selected' : '' }}>Ambos</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="rangosEdad" class="form-label">Definir rangos de edad</label>
                        <select id="rangosEdad" name="rangosEdad[]" class="select2 form-select" multiple>
                            @foreach ($rangosEdad as $rango)
                            <option value="{{ $rango->id }}" {{ in_array($rango->id, old('rangosEdad', [])) ? 'selected' : '' }}>
                                {{ $rango->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="pasosCrecimientoRequisito" class="form-label">Definir procesos requisito</label>
                        <select id="pasosCrecimientoRequisito" name="pasosCrecimientoRequisito[]" class="select2 form-select" multiple>
                            @foreach ($pasosCrecimiento as $paso)
                            <option value="{{ $paso->id }}" {{ in_array($paso->id_paso, old('pasosCrecimientoCulminar', [])) ? 'selected' : '' }}>
                                {{ $paso->nombre }}
                            </option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-12 mb-3">
                        <label for="tipoUsuarios" class="form-label">Definir tipo usuario</label>
                        <select id="tipoUsuarios" name="tipoUsuarios[]" class="select2 form-select" multiple>
                            @foreach ($tipoUsuarios as $tipo)
                            <option value="{{ $tipo->id }}" {{ in_array($tipo->id, old('tipoUsuarios', [])) ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="pasosCrecimientoCulminar" class="form-label">Definir procesos a culminar</label>
                        <select id="pasosCrecimientoCulminar" name="pasosCrecimientoCulminar[]" class="select2 form-select" multiple>
                            @foreach ($pasosCrecimiento as $paso)
                            <option value="{{ $paso->id_paso }}" {{ in_array($paso->id_paso, old('pasosCrecimientoCulminar', [])) ? 'selected' : '' }}>
                                {{ $paso->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="estadosCiviles" class="form-label">Definir estados civiles</label>
                        <select id="estadosCiviles" name="estadosCiviles[]" class="select2 form-select" multiple>
                            @foreach ($estadosCiviles as $estado)
                            <option value="{{ $estado->id }}" {{ in_array($estado->id, old('estadosCiviles', [])) ? 'selected' : '' }}>
                                {{ $estado->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="tipoServicios" class="form-label">Definir tipos servicios</label>
                        <select id="tipoServicios" name="tipoServicios[]" class="select2 form-select" multiple>
                            @foreach ($tipoServicios as $tipoSer)
                            <option value="{{ $tipoSer->id }}" {{ in_array($tipoSer->id, old('tipoServicios', [])) ? 'selected' : '' }}>
                                {{ $tipoSer->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>



                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información Actividad</h5>
                </div>
                <div class="row card-body">

                    <div class="col-12 mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <div id="descripcion" class="mt-5">
                        </div>
                        <input id="contenidoDescripcion" name="contenidoDescripcion" class='d-none'>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="instrucciones" class="form-label">Instrucciones finales</label>
                        <div id="instruccionesFinales" class="mt-5">
                        </div>
                        <input id="contenidoFinal" name="contenidoFinal" class='d-none'>
                    </div>



                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Configuración de fechas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fecha-picke" for="eventStartDate">Fecha visualizacion</label>
                        <input id="fecha_visualizacion" value="{{ old('fecha_visualizacion') }}" placeholder="YYYY-MM-DD" name="fecha_visualizacion" class="fecha form-control fecha-picker" type="text" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label fecha-picke" for="eventStartDate">Fecha cierre inscripciones</label>
                        <input id="fecha_cierre" value="{{ old('fecha_cierre') }}" placeholder="YYYY-MM-DD" name="fecha_cierre" class="fecha form-control fecha-picker" type="text" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label fecha-picke" for="eventStartDate">Fecha inicio</label>
                        <input id="fecha_inicio" value="{{ old('fecha_inicio', $actividad->fecha_inicio) }}" placeholder="YYYY-MM-DD" name="fecha_inicio" class="fecha form-control fecha-picker" type="text" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label fecha-picke" for="eventStartDate">Fecha finalización</label>
                        <input id="fecha_fin" value="{{ old('fecha_fin', $actividad->fecha_finalizacion) }}" placeholder="YYYY-MM-DD" name="fecha_fin" class="fecha form-control fecha-picker" type="text" />
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-tile mb-0">Configuraciones SAP</h5>
                </div>

                <div class="row card-body">
                    <div class="col-12 mb-3">
                        <label class="form-label" for="eventTitle">Cuenta SAP</label>
                        <input type="text" value="{{ old('cuentaSAP') }}" class="form-control" id="cuentaSAP" name="cuentaSAP" placeholder="cuenta SAP" />
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label" for="eventTitle">Proyecto SAP</label>
                        <input type="text" value="{{ old('proyectoSAP') }}" class="form-control" id="proyectoSAP" name="proyectoSAP" placeholder="proyecto SAP" />
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label" for="eventTitle">Centro costo de SAP</label>
                        <input type="text" value="{{ old('centro_costoSAP') }}" class="form-control" id="centro_costoSAP" name="centro_costoSAP" placeholder="Centro SAP" />
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label" for="eventTitle">Label destinatario</label>
                        <input type="text" value="{{ old('centro_costoSAP') }}" class="form-control" id="centro_costoSAP" name="centro_costoSAP" placeholder="Ejemplo: selecciona la sede" />
                    </div>
                    <div class="col-12 mb-3">
                        <label for="destinatarios" class="form-label">Destinatarios </label>
                        <select id="destinatarios" name="destinatarios[]" class="select2 form-select" multiple>
                            @foreach ($destinatarios as $destinatario)
                            <option value="{{ $destinatario->id }}" {{ in_array($destinatario->id, old('destinatarios', [])) ? 'selected' : '' }}>
                                {{ $destinatario->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-tile mb-0">Evaluar actividad</h5>
                </div>
                <div class="row card-body">
                    <div class="col-12 mb-3">
                        <label class="form-label" for="evaluacionGeneral">Evaluación general de la actividad:</label>
                        <textarea value="{{ old('evaluacionGeneral') }}" class="form-control" id="evaluacionGeneral" name="evaluacionGeneral" placeholder="cuenta SAP"> </textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label" for="evaluacionFinanciera">Evaluación financiera de la
                            actividad:</label>
                        <textarea value="{{ old('evaluacionFinanciera') }}" class="form-control" id="evaluacionFinanciera" name="evaluacionFinanciera" placeholder="cuenta SAP"> </textarea>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <center>
                        <button type="button" class="btn btn-danger me-1 btnCerrar">Cancelar actividad</button>
                    </center>

                </div>
            </div>


        </div>
        <!-- botonera -->
        <div class="d-flex mb-1 mt-5">
            <div class="me-auto">
                <button type="submit" class="btn btn-primary me-1 btnGuardar">Guardar</button>
                <button type="reset" class="btn btn-label-secondary">Cancelar</button>
            </div>
            <div class="p-2 bd-highlight">
                <p class="text-muted">Campos obligatorios</p>
            </div>
        </div>
        <!-- /botonera -->
    </div>

</form>

@endsection
