@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Formularios')

<!-- Page -->
@section('vendor-style')

@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/libs/quill/typography.scss',
  'resources/assets/vendor/libs/quill/editor.scss'
])

@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
    'resources/assets/vendor/libs/quill/quill.js'
  ])
@endsection


@section('page-script')

<script type="module">
  function actualizarVisibilidadTerminos() {
    var mostrarTerminos = $('#habilitarTerminosCondiciones').is(':checked');
    if (mostrarTerminos) {
      $('#divLabelTerminosCondiciones').removeClass('d-none');
      $('#divTerminosYCondiciones').removeClass('d-none');
      $('#divModalidadTerminos').removeClass('d-none');

      var modalidad = $('input[name="modalidadTerminos"]:checked').val() || 'url';
      if (modalidad === 'url') {
        $('#divUrlTerminosCondiciones').removeClass('d-none');
        $('#divTerminosYCondicionesDatallado').addClass('d-none');
      } else {
        $('#divUrlTerminosCondiciones').addClass('d-none');
        $('#divTerminosYCondicionesDatallado').removeClass('d-none');
      }
    } else {
      $('#divLabelTerminosCondiciones').addClass('d-none');
      $('#divTerminosYCondiciones').addClass('d-none');
      $('#divModalidadTerminos').addClass('d-none');
      $('#divUrlTerminosCondiciones').addClass('d-none');
      $('#divTerminosYCondicionesDatallado').addClass('d-none');
    }
  }

  $('#habilitarTerminosCondiciones').change(actualizarVisibilidadTerminos);
  $('input[name="modalidadTerminos"]').change(actualizarVisibilidadTerminos);

  $('#validarEdad').change(function() {

    if (this.checked) {
      $("#divEdadMinima").removeClass("d-none");
      $("#divEdadMaxima").removeClass("d-none");
      $("#divMensaje").removeClass("d-none");
    } else {
      $("#divEdadMinima").addClass("d-none");
      $("#divEdadMaxima").addClass("d-none");
      $("#divMensaje").addClass("d-none");

      $("#edadMinima").val("");
      $("#edadMaxima").val("");
      $("#mensaje").val("");
    }
  });

  $('#validarEdadOperador').change(function() {
    if (this.checked) {
      $("#divEdadMinimaOperador").removeClass("d-none");
      $("#divEdadMaximaOperador").removeClass("d-none");
    } else {
      $("#divEdadMinimaOperador").addClass("d-none");
      $("#divEdadMaximaOperador").addClass("d-none");

      $("#edadMinimaOperador").val("");
      $("#edadMaximaOperador").val("");
    }
  });
</script>

<script type="module">
    function renderIcons(option) {
      if (!option.id) {
        return option.text;
      }
      var $icon = "<i class='" + $(option.element).data('icon') + " me-2'></i>" + option.text;

      return $icon;
    }
    $('.select2Iconos').wrap('<div class="position-relative"></div>').select2({
      dropdownParent: $('.select2Iconos').parent(),
      templateResult: renderIcons,
      templateSelection: renderIcons,
      escapeMarkup: function (es) {
        return es;
      }
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

<script type="module">
   var placeholder = $("#tipoDeFormulario").data('placeholder');
  $("#tipoDeFormulario").select2({
      placeholder: 'Selecciona el tipo de formulario',
      allowClear: true
  });

  $("#tipoUsuarioPorDefecto").select2({
      placeholder: 'Selecciona el tipo usuario por defecto',
      allowClear: true
  });

  $("#sedeDefault").select2({
      placeholder: 'Selecciona la sede por defecto',
      allowClear: true
  });

  $('#tipoDeFormulario').on('change', function() {
    var selectedOption = $(this).find(':selected');
    var esFormularioExterior = selectedOption.data('es-formulario-exterior');
    var esFormularioNuevo = selectedOption.data('es-formulario-nuevo');

    if (esFormularioExterior) {
      $('#divConfiguracionUsoFormulario').addClass('d-none');
      $('#roles').val(null).trigger('change');
      $('#roles').prop('disabled', true);
      $('#validarEdadOperador').prop('checked', false).trigger('change');
      $('#edadMinimaOperador').val('');
      $('#edadMaximaOperador').val('');
    } else {
      $('#divConfiguracionUsoFormulario').removeClass('d-none');
      $('#roles').prop('disabled', false);
    }

    if (esFormularioNuevo) {
       // Habilitar la selección de tipoUsuarioPorDefecto
      $('#tipoUsuarioPorDefecto').prop('disabled', false);
    } else {
      // Deshabilitar y limpiar la selección de tipoUsuarioPorDefecto
      $('#tipoUsuarioPorDefecto').val(null).trigger('change');
      $('#tipoUsuarioPorDefecto').prop('disabled', true);
    }
  });
</script>

<script type="module">

  /* editor Respuesta */
  var editorTerminosCondicionesDetallado = new Quill('#editorTerminosCondicionesDetallado', {
  bounds: '#editorTerminosCondicionesDetallado',
  placeholder: ' Escribe la descripción detallado de los terminos y condiciones',
  modules: {
    toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'align': [] }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'font': [] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['clean']
      ]
    },
    theme: 'snow'
  });

  editorTerminosCondicionesDetallado.root.innerHTML = @json($formulario->mensaje_terminos_condiciones_detallado);

  editorTerminosCondicionesDetallado.on('text-change', (delta, oldDelta, source) => {
    $('#terminosYCondicionesDetallado').html(editorTerminosCondicionesDetallado.root.innerHTML);
  });
  /* fin editor respuesta */
</script>

@endsection

@section('content')

<div class="row mb-4">
  <ul class="nav nav-pills mb-3 d-flex justify-content-end" role="tablist">

    <li class="nav-item me-1">
      <a href="{{ route('formularioUsuario.modificar', $formulario) }}">
        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
          <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">1</span>
          Datos principales
        </button>
      </a>
    </li>

    <li class="nav-item me-1">
      <a href="{{ route('formularioUsuario.seccionesCampos', $formulario) }}">
        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true">
          <span class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">2</span>
          Secciones y campos
        </button>
      </a>
    </li>

  </ul>
</div>


<h4 class="mb-1 fw-semibold text-primary">Editar formulario</h4>
<p class="mb-8">Aquí podrás editar el formulario.</p>


@include('layouts.status-msn')

<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('formularioUsuario.editar', $formulario) }}"  enctype="multipart/form-data" >
  @csrf
  @method('PATCH')

  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header text-black fw-semibold">Información principal</h5>
      <div class="card-body">
        <div class="row">
          <!-- Nombre -->
          <div class="mb-3 col-12 col-md-4">
            <label class="form-label" for="nombre">
              Nombre
            </label>
            <input id="nombre" name="nombre" placeholder="Escribe el nombre" value="{{ old('nombre', $formulario->nombre) }}" type="text" class="form-control" />
            @if($errors->has('nombre'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('nombre') }}
            </div>
            @endif
          </div>
          <!-- /Nombre  -->

          <!-- titulo -->
          <div class="mb-3 col-12 col-md-4">
            <label class="form-label" for="titulo">
              Título
            </label>
            <input id="titulo" name="título" placeholder="Escribe el título" value="{{ old('título', $formulario->titulo) }}" type="text" class="form-control" />
            @if($errors->has('título'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('título') }}
            </div>
            @endif
          </div>
          <!-- /titulo  -->

           <!-- etiqueta -->
           <div class="mb-3 col-12 col-md-4">
            <label class="form-label" for="etiqueta">
              Etiqueta
            </label>
            <input id="etiqueta" name="etiqueta" placeholder="Escribe la etiqueta" value="{{ old('etiqueta', $formulario->label) }}" type="text" class="form-control" />
            @if($errors->has('etiqueta'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('etiqueta') }}
            </div>
            @endif
          </div>
          <!-- /etiqueta  -->


          <!-- Descripcion -->
          <div class="mb-3 col-12">
            <label class="form-label" for="descripcion">
              Escribe una descripción breve
            </label>
            <textarea onkeypress="return sinComillas(event)" id="descripcion" name="descripción" placeholder="Escribe una descripción breve" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true" >{{ old('descripción', $formulario->descripcion) }}</textarea>
            @if($errors->has('descripción'))
              <div class="text-danger ti-12px mt-2">
                <i class="ti ti-circle-x"></i> {{ $errors->first('descripción') }}
              </div>
            @endif
          </div>
          <!-- /Descripcion -->

          <!-- Icono -->
          <div class="mb-3 col-12 col-md-4">
            <label class="form-label" for="icono">
              Ícono representativo
            </label>
            <input id="icono" name="icono" placeholder="Ej: ti ti-building-church" value="{{ old('icono', $formulario->icono) }}" type="text" class="form-control" />
            <div class="form-text">Clase del ícono Tabler (ej: <code>ti ti-building-church</code>, <code>ti ti-world</code>).</div>
            @if($errors->has('icono'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('icono') }}
            </div>
            @endif
          </div>
          <!-- /Icono -->

          <!-- Color -->
          <div class="mb-3 col-12 col-md-4">
            <label class="form-label" for="color">
              Color representativo
            </label>
            <input id="color" name="color" value="{{ old('color', $formulario->color ?? '#138848') }}" type="color" class="form-control form-control-color w-100" />
            <div class="form-text">Color del ícono y fondo en el login.</div>
            @if($errors->has('color'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('color') }}
            </div>
            @endif
          </div>
          <!-- /Color -->


          <!--  Tipo de id  -->
          <div class="mb-3 col-12 col-md-4">
            <label class="form-label" for="">
              Tipo de formulario
            </label>
            <select id="tipoDeFormulario" name="tipoDeFormulario" data-placeholder="Selecciona el tipo de formulario" class="select2 form-select" data-allow-clear="true">
              <option value="" selected>Ninguno</option>
              @foreach ($tipos as $tipo)
              <option value="{{$tipo->id}}" {{ old('tipoDeFormulario', $formulario->tipo_formulario_id) == $tipo->id ? 'selected' : '' }} data-es-formulario-exterior="{{ $tipo->es_formulario_exterior }}" data-es-formulario-nuevo="{{ $tipo->es_formulario_nuevo }}">{{$tipo->nombre}}</option>
              @endforeach
            </select>
            @if($errors->has('tipoDeFormulario'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('tipoDeFormulario') }}
            </div>
            @endif
          </div>
          <!--  Tipo de id  -->


          

          <!--  tipoUsuarioPorDefecto -->
          <div class="mb-3 col-12 col-md-4">
            <label class="form-label" for="">
              ¿Que tipo de usuario se asignará por defecto?
            </label>
            <select id="tipoUsuarioPorDefecto" name="tipoUsuarioPorDefecto" data-placeholder="Selecciona el tipo de formulario" class="select2 form-select" data-allow-clear="true" {{ $formulario->tipo->es_formulario_nuevo ? '' : 'disabled' }}>
              <option value="" selected>Ninguno</option>
              @foreach ($tiposUsuario as $tipo)
              <option value="{{$tipo->id}}" {{ old('tipoUsuarioPorDefecto', $formulario->tipo_usuario_default_id) == $tipo->id ? 'selected' : '' }}>{{$tipo->nombre}}</option>
              @endforeach
            </select>
            @if($errors->has('tipoUsuarioPorDefecto'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('tipoUsuarioPorDefecto') }}
            </div>
            @endif
          </div>
          <!--  tipoUsuarioPorDefecto -->

          <!--  sedeDefault -->
          <div class="mb-3 col-12 col-md-4">
            <label class="form-label" for="sedeDefault">
              ¿Qué sede se asignará por defecto?
            </label>
            <select id="sedeDefault" name="sedeDefault" class="select2 form-select" data-allow-clear="true">
              <option value="" selected>Ninguno</option>
              @foreach ($sedes as $sede)
              <option value="{{$sede->id}}" {{ old('sedeDefault', $formulario->sede_default_id) == $sede->id ? 'selected' : '' }}>{{$sede->nombre}}</option>
              @endforeach
            </select>
            @if($errors->has('sedeDefault'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('sedeDefault') }}
            </div>
            @endif
          </div>
          <!--  sedeDefault -->

        </div>
      </div>
    </div>
  </div>

  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header text-black fw-semibold">Configuración de edad</h5>
      <div class="card-body">
        <div class="row">

          <!-- Validar edad -->
          <div class="mb-3 col-12 col-md-4">
            <div class="small mb-1">¿Validar edad?</div>
            <label class="switch switch-lg">
              <input id="validarEdad" name="validarEdad" placeholder="Validar edad" type="checkbox" @checked(old('validarEdad', $formulario->validar_edad)) class="switch-input switchTerminosCondiciones" />
              <span class="switch-toggle-slider">
                <span class="switch-on">SI</span>
                <span class="switch-off">NO</span>
              </span>
              <span class="switch-label"></span>
            </label>
          </div>
          <!-- / Validar edad -->

          <!-- edadMinima -->
          <div id="divEdadMinima" class="mb-3 col-12 col-md-4 {{ old('validarEdad', $formulario->validar_edad) ? '' : 'd-none' }}">
            <label class="form-label" for="edadMinima">
              Edad mínima
            </label>
            <input id="edadMinima" name="edadMínima" placeholder="Escribe la edad mínima" value="{{ old('edadMínima', $formulario->edad_minima) }}" type="number" class="form-control" />
            @if($errors->has('edadMínima'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('edadMínima') }}
            </div>
            @endif
          </div>
          <!-- /edadMinima  -->

          <!-- edadMaxima -->
          <div id="divEdadMaxima" class="mb-3 col-12 col-md-4 {{ old('validarEdad', $formulario->validar_edad) ? '' : 'd-none' }}">
            <label class="form-label" for="edadMaxima">
              Edad maxima
            </label>
            <input id="edadMaxima" name="edadMaxima" placeholder="Escribe la edad maxima" value="{{ old('edadMaxima', $formulario->edad_maxima) }}" type="number" class="form-control" />
            @if($errors->has('edadMaxima'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('edadMaxima') }}
            </div>
            @endif
          </div>
          <!-- /edadMaxima  -->

          <!-- mensaje -->
          <div id="divMensaje" class="mb-3 col-12 {{ old('validarEdad', $formulario->validar_edad) ? '' : 'd-none' }}">
            <label class="form-label" for="mensaje">
             Describe el mensaje de error para la edad
            </label>
            <textarea onkeypress="return sinComillas(event)" id="mensaje" name="mensaje" placeholder="Escribe el mensaje" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true" >{{ old('mensaje', $formulario->edad_mensaje_error) }}</textarea>
            @if($errors->has('mensaje'))
              <div class="text-danger ti-12px mt-2">
                <i class="ti ti-circle-x"></i> {{ $errors->first('mensaje') }}
              </div>
            @endif
          </div>
          <!-- /mensaje -->

          <!-- Sugerir en recomendaciones -->
          <div class="mb-3 col-12">
            <div class="small mb-1">¿Sugerir este formulario en recomendaciones de cambio de formulario por edad?</div>
            <label class="switch switch-lg">
              <input id="sugerirEnRecomendaciones" name="sugerirEnRecomendaciones" type="checkbox" @checked(old('sugerirEnRecomendaciones', $formulario->sugerir_en_recomendaciones ?? true)) class="switch-input switchTerminosCondiciones" />
              <span class="switch-toggle-slider">
                <span class="switch-on">SI</span>
                <span class="switch-off">NO</span>
              </span>
              <span class="switch-label"></span>
            </label>
            <div class="text-muted small mt-1">Si está activo, este formulario podrá sugerirse a usuarios que ingresen una fecha de nacimiento fuera del rango de otro formulario.</div>
          </div>
          <!-- / Sugerir en recomendaciones -->
        </div>
      </div>
    </div>
  </div>

  @php
    $tipoActualId = old('tipoDeFormulario', $formulario->tipo_formulario_id);
    $tipoActual = $tipos->firstWhere('id', $tipoActualId);
    $esExteriorModificar = $tipoActual && $tipoActual->es_formulario_exterior;
  @endphp
  <div class="col-md-12 {{ $esExteriorModificar ? 'd-none' : '' }}" id="divConfiguracionUsoFormulario">
    <div class="card mb-4">
      <h5 class="card-header text-black fw-semibold">Configuración del uso del formulario</h5>
      <div class="card-body">
        <div class="row">

          <!-- roles -->
          <div class="mb-3 col-12 col-md-12">
            <label for="roles" class="form-label">¿Qué roles van a usar este formulario?</label>
            <select id="roles" name="roles[]" multiple class="select2Iconos form-select" {{ $formulario->tipo->es_formulario_exterior ? 'disabled' : '' }} >
              @foreach ($roles as $rol )
              <option value="{{ $rol->id }}" data-icon="{{ $rol->icono }}" {{ in_array($rol->id, old( 'roles',$rolesSeleccionados )) ? "selected" : "" }}  ">
                {{ $rol->name }} </option>
              @endforeach
            </select>
            @if($errors->has('roles'))
              <div class="text-danger ti-12px mt-2">
                <i class="ti ti-circle-x"></i> {{ $errors->first('roles') }}
              </div>
            @endif
          </div>
          <!-- /roles -->

          <!-- Validar edad operador -->
          <div class="mb-3 col-12 col-md-4">
            <div class="small mb-1">¿Restringir por edad para el uso del formulario?</div>
            <label class="switch switch-lg">
              <input id="validarEdadOperador" name="validarEdadOperador" placeholder="Restringir por edad del operador" type="checkbox" @checked(old('validarEdadOperador', $formulario->validar_edad_operador)) class="switch-input switchTerminosCondiciones" />
              <span class="switch-toggle-slider">
                <span class="switch-on">SI</span>
                <span class="switch-off">NO</span>
              </span>
              <span class="switch-label"></span>
            </label>
          </div>
          <!-- / Validar edad operador -->

          <!-- edadMinimaOperador -->
          <div id="divEdadMinimaOperador" class="mb-3 col-12 col-md-4 {{ old('validarEdadOperador', $formulario->validar_edad_operador) ? '' : 'd-none' }}">
            <label class="form-label" for="edadMinimaOperador">
              Edad mínima del operador
            </label>
            <input id="edadMinimaOperador" name="edadMinimaOperador" placeholder="Ej: 18" value="{{ old('edadMinimaOperador', $formulario->edad_minima_operador) }}" type="number" class="form-control" />
            @if($errors->has('edadMinimaOperador'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('edadMinimaOperador') }}
            </div>
            @endif
          </div>
          <!-- /edadMinimaOperador -->

          <!-- edadMaximaOperador -->
          <div id="divEdadMaximaOperador" class="mb-3 col-12 col-md-4 {{ old('validarEdadOperador', $formulario->validar_edad_operador) ? '' : 'd-none' }}">
            <label class="form-label" for="edadMaximaOperador">
              Edad máxima del operador (opcional)
            </label>
            <input id="edadMaximaOperador" name="edadMaximaOperador" placeholder="Opcional" value="{{ old('edadMaximaOperador', $formulario->edad_maxima_operador) }}" type="number" class="form-control" />
            @if($errors->has('edadMaximaOperador'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('edadMaximaOperador') }}
            </div>
            @endif
          </div>
          <!-- /edadMaximaOperador -->

        </div>
      </div> 
    </div>
  </div>

  @php
    $modalidadTerminos = old('modalidadTerminos', !empty($formulario->url_terminos_condiciones) ? 'url' : 'editor');
    $tieneTerminos = old('términosCondiciones', $formulario->visible_terminos_condiciones);
  @endphp
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header text-black fw-semibold">Configuración de términos y condiciones</h5>
      <div class="card-body">
        <div class="row">
          <!-- Tiene terminos y condiciones -->
          <div class="mb-3 col-12 col-md-4">
            <div class="small mb-1">¿Mostrar términos y condiciones?</div>
            <label class="switch switch-lg">
              <input id="habilitarTerminosCondiciones" name="términosCondiciones" placeholder="Habilitar términos y condiciones" type="checkbox" @checked($tieneTerminos) class="switch-input switchTerminosCondiciones" />
              <span class="switch-toggle-slider">
                <span class="switch-on">SI</span>
                <span class="switch-off">NO</span>
              </span>
              <span class="switch-label"></span>
            </label>
          </div>
          <!-- / Tiene terminos y condiciones -->

          <!-- labelTerminosCondiciones -->
          <div id="divLabelTerminosCondiciones" class="mb-3 col-12 col-md-8 {{ $tieneTerminos ? '' : 'd-none' }}">
            <label class="form-label" for="labelTerminosCondiciones">
              Título o etiqueta de los términos y condiciones
            </label>
            <input id="labelTerminosCondiciones" name="labelTerminosCondiciones" placeholder="Ej: Términos y condiciones para menor de edad" value="{{ old('labelTerminosCondiciones', $formulario->label_terminos_condiciones) }}" type="text" class="form-control" />
            @if($errors->has('labelTerminosCondiciones'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('labelTerminosCondiciones') }}
            </div>
            @endif
          </div>
          <!-- /labelTerminosCondiciones -->

          <!-- terminosCondiciones -->
          <div id="divTerminosYCondiciones" class="mb-3 col-12 {{ $tieneTerminos ? '' : 'd-none' }}">
            <label class="form-label" for="terminosYCondiciones">
              Escribe la descripción breve de los términos y condiciones
            </label>
            <textarea onkeypress="return sinComillas(event)" id="terminosYCondiciones" name="términosYCondiciones" placeholder="Escribe los términos y condiciones" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true" >{{ old('términosYCondiciones', $formulario->mensaje_terminos_condiciones_resumen) }}</textarea>
            @if($errors->has('términosYCondiciones'))
              <div class="text-danger ti-12px mt-2">
                <i class="ti ti-circle-x"></i> {{ $errors->first('términosYCondiciones') }}
              </div>
            @endif
          </div>
          <!-- /terminosCondiciones -->

          <!-- Modalidad de términos detallados -->
          <div id="divModalidadTerminos" class="mb-3 col-12 {{ $tieneTerminos ? '' : 'd-none' }}">
            <label class="form-label d-block fw-semibold mb-2">¿Cómo deseas presentar los términos detallados?</label>
            <div class="form-check form-check-inline me-4">
              <input class="form-check-input modalidad-terminos-radio" type="radio" name="modalidadTerminos" id="modalidadUrl" value="url" {{ $modalidadTerminos == 'url' ? 'checked' : '' }}>
              <label class="form-check-label" for="modalidadUrl">
                <i class="ti ti-link me-1"></i> Documento / Enlace externo (URL de PDF, Drive o web)
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input modalidad-terminos-radio" type="radio" name="modalidadTerminos" id="modalidadEditor" value="editor" {{ $modalidadTerminos == 'editor' ? 'checked' : '' }}>
              <label class="form-check-label" for="modalidadEditor">
                <i class="ti ti-edit me-1"></i> Redactar texto aquí (Editor)
              </label>
            </div>
          </div>
          <!-- /Modalidad de términos detallados -->

          <!-- urlTerminosCondiciones -->
          <div id="divUrlTerminosCondiciones" class="mb-3 col-12 {{ ($tieneTerminos && $modalidadTerminos == 'url') ? '' : 'd-none' }}">
            <label class="form-label" for="urlTerminosCondiciones">
              URL del documento de términos y condiciones
            </label>
            <input id="urlTerminosCondiciones" name="url" placeholder="https://..." value="{{ old('url', $formulario->url_terminos_condiciones) }}" type="text" class="form-control" />
            <div class="text-muted small mt-1">Ingresa el enlace público de Google Drive, PDF o página web con los términos completos.</div>
            @if($errors->has('url'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('url') }}
            </div>
            @endif
          </div>
          <!-- /urlTerminosCondiciones  -->

          <!-- mensaje_terminos_condiciones_detallado -->
          <div id="divTerminosYCondicionesDatallado" class="mb-3 col-12 {{ ($tieneTerminos && $modalidadTerminos == 'editor') ? '' : 'd-none' }}">
            <label class="form-label" for="terminosYCondicionesDetallado">
              Escribe la descripción detallada de los términos y condiciones
            </label>

            <div id="editorTerminosCondicionesDetallado"></div>
            <textarea onkeypress="return sinComillas(event)" id="terminosYCondicionesDetallado" name="términosYCondicionesDetallado" placeholder="Escribe los términos y condiciones" class="form-control d-none" spellcheck="false" data-ms-editor="true" >{{ old('términosCondicionesDetallado', $formulario->mensaje_terminos_condiciones_detallado) }}</textarea>
            @if($errors->has('términosYCondicionesDetallado'))
              <div class="text-danger ti-12px mt-2">
                <i class="ti ti-circle-x"></i> {{ $errors->first('términosYCondicionesDetallado') }}
              </div>
            @endif
          </div>
          <!-- /mensaje_terminos_condiciones_detallado -->

        </div>
      </div>
    </div>
  </div>

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn rounded-pill btn-primary me-1 px-10 btnGuardar">Guardar</button>
    </div>
  </div>
  <!-- /botonera -->

</form>

@endsection
