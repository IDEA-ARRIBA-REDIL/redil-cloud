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
  $('#habilitarTerminosCondiciones').change(function() {

    if (this.checked) {
      $("#divUrlTerminosCondiciones").removeClass("d-none");
      $("#divTerminosYCondiciones").removeClass("d-none");
      $("#divTerminosYCondicionesDatallado").removeClass("d-none");
    } else {
      $("#divUrlTerminosCondiciones").addClass("d-none");
      $("#divTerminosYCondiciones").addClass("d-none");
      $("#divTerminosYCondicionesDatallado").addClass("d-none");

      $("#urlTerminosCondiciones").val("");
      $("#terminosYCondiciones").val("");
    }
  });

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

  $('#tipoDeFormulario').on('change', function() {
    var selectedOption = $(this).find(':selected');
    var esFormularioExterior = selectedOption.data('es-formulario-exterior');
    var esFormularioNuevo = selectedOption.data('es-formulario-nuevo');


    if (esFormularioExterior) {
      // Deshabilitar y limpiar la selección de roles
      $('#roles').val(null).trigger('change');
      $('#roles').prop('disabled', true);
    } else {
      // Habilitar la selección de roles
      $('#roles').prop('disabled', false);
    }

    if (esFormularioNuevo) {
       // Habilitar la selección de tipoUsuarioPorDefecto
      $('#tipoUsuarioPorDefecto').prop('disabled', false);
    }else {
      alert();
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
            @if($errors->has('título'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('título') }}
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


          <!-- roles -->
          <div class="mb-3 col-12 col-md-8">
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
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header text-black fw-semibold">Configuración de términos y condiciones</h5>
      <div class="card-body">
        <div class="row">
          <!-- Tiene terminos y condiciones -->
          <div class="mb-3 col-12 col-md-4">
            <div class=" small  mb-1">¿Mostrar términos y condiciones?</div>
            <label class="switch switch-lg">
              <input id="habilitarTerminosCondiciones" name="términosCondiciones" placeholder="Habilitar términos y condiciones" type="checkbox" @checked(old('términosCondiciones', $formulario->visible_terminos_condiciones)) class="switch-input switchTerminosCondiciones" />
              <span class="switch-toggle-slider">
                <span class="switch-on">SI</span>
                <span class="switch-off">NO</span>
              </span>
              <span class="switch-label"></span>
            </label>
          </div>
          <!-- / Tiene terminos y condiciones -->

          <!-- terminosCondiciones -->
          <div id="divTerminosYCondiciones"  class="mb-3 col-12 {{ old('términosCondiciones', $formulario->visible_terminos_condiciones) ? '' : 'd-none' }}">
            <label class="form-label" for="terminosYCondiciones">
              Escribe la descripción breve de los terminos y condiciones
            </label>
            <textarea onkeypress="return sinComillas(event)" id="terminosYCondiciones" name="términosYCondiciones" placeholder="Escribe los términos y condiciones" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true" >{{ old('términosYCondiciones', $formulario->mensaje_terminos_condiciones_resumen) }}</textarea>
            @if($errors->has('términosYCondiciones'))
              <div class="text-danger ti-12px mt-2">
                <i class="ti ti-circle-x"></i> {{ $errors->first('términosYCondiciones') }}
              </div>
            @endif
          </div>
          <!-- /terminosCondiciones -->

          <!-- urlTerminosCondiciones -->
          <div id="divUrlTerminosCondiciones" class="mb-3 col-12 {{ old('términosCondiciones', $formulario->visible_terminos_condiciones)  ? '' : 'd-none' }}">
            <label class="form-label" for="urlTerminosCondiciones">
              Url de términos y condiciones
            </label>
            <input id="urlTerminosCondiciones" name="url" placeholder="Escribe la URL" value="{{ old('url', $formulario->url_terminos_condiciones) }}" type="text" class="form-control" />
            @if($errors->has('url'))
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $errors->first('url') }}
            </div>
            @endif
          </div>
          <!-- /urlTerminosCondiciones  -->

          <!-- mensaje_terminos_condiciones_detallado -->
          <div id="divTerminosYCondicionesDatallado"  class="mb-3 col-12 {{ old('términosCondiciones', $formulario->visible_terminos_condiciones)  ? '' : 'd-none' }}">
            <label class="form-label" for="terminosYCondicionesDetallado">
              Escribe la descripción detallado de los terminos y condiciones
            </label>

            <div id="editorTerminosCondicionesDetallado"></div>
            <textarea onkeypress="return sinComillas(event)" id="terminosYCondicionesDetallado" name="términosYCondicionesDetallado" placeholder="Escribe los términos y condiciones" class="form-control d-none" spellcheck="false" data-ms-editor="true" >{{ old('términosCondicionesDetallado', $formulario->mensaje_terminos_condiciones_detallado) }}</textarea>
            @if($errors->has('terminosYCondicionesDetallado'))
              <div class="text-danger ti-12px mt-2">
                <i class="ti ti-circle-x"></i> {{ $errors->first('términosYCondiciones') }}
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
