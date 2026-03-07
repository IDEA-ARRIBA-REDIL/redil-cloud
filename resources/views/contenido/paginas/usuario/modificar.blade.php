@php
$configData = Helper::appClasses();
@endphp

@extends($layout)

@section('title', 'Actualizar usuario')

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

@section('vendor-style')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection


@section('page-script')
@vite(['resources/assets/js/form-basic-inputs.js'])

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
        aspectRatio: 1,
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
                aspectRatio: 1,
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
          width: 300 // input value
        })
        .toDataURL();
      croppedImg.src = imgSrc;
      inputResultado.value = imgSrc;
      //dwn.setAttribute('href', imgSrc);
      //dwn.download = 'imagename.png';
    });
  });
</script>

<script type="module">
  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d"
  });

  $('.select2').each(function() {
    var placeholder = $(this).data('placeholder');
    $(this).select2({
      placeholder: placeholder,
      allowClear: true
    });
  });
</script>

<script type="module">
  window.addEventListener('msn', event => {
    Swal.fire({
      title: event.detail.msnTitulo,
      html: event.detail.msnTexto,
      icon: event.detail.msnIcono,
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
  });
</script>

<script type="module">
  window.addEventListener('bloquedoBtnGuardar', event => {
    $(".btnGuardar").attr('disabled', 'disabled');
  });

  window.addEventListener('desbloquedoBtnGuardar', event => {
    $(".btnGuardar").removeAttr('disabled');
  });

  window.addEventListener('abrirModalCambioDeFormulario', event => {
    $('#' + event.detail.nombreModal).modal('show');
    $('#modalMsnCambioDeFormulario').html(event.detail.html);
  });
</script>

<script type="module">
  $('.selectorGenero').on('change', function(event) {
    if ($("#imagen-recortada").val() == "") {
      @if($usuario->foto == 'default-m.png' || $usuario->foto == 'default-f.png')
      if ($(this).val() == 1) {
        $("#preview-foto").attr("src",
          "{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/default-f.png') : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/default-f.png' }}"
        );
      } else {
        $("#preview-foto").attr("src",
          "{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/default-m.png') : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/default-m.png' }}"
        );
      }
      @endif
    }
  });
</script>

<script type="module">
  $('#tienesUnaPeticion').change(function() {

    if (this.checked) {
      $("#divSelectTipoPeticion").removeClass("d-none");
      $("#divDescripcionPeticion").removeClass("d-none");
      $('#descripcion_peticion').prop("required", true);
      $('#tipo_peticion').prop("required", true);
    } else {
      $("#divSelectTipoPeticion").addClass("d-none");
      $("#divDescripcionPeticion").addClass("d-none");

      $("#descripcion_peticion").val("");
      $('#descripcion_peticion').removeAttr("required");

      $("#tipo_peticion").val("");
      $('#tipo_peticion').removeAttr("required");
    }
  });
</script>

<script type="module">
  $('#preguntaVivesEn').change(function() {

    if (this.checked) {
      Livewire.dispatch('mostrarBuscadorUbicacion', {
        cambiarPor: true
      });
    } else {
      Livewire.dispatch('mostrarBuscadorUbicacion', {
        cambiarPor: false
      });
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

  $('#identificacion').keyup(function() {
    clearTimeout($.data(this, 'timer'));
    if ($("#identificacion").val() != '') {
      @if($configuracion->correo_por_defecto == true && $formulario->visible_email == true)
      if ($("#email").val() == '') {
        $("#email").val($("#identificacion").val() + "@cambiaestecorreo.com");
      } else if ($("#email").val().indexOf('cambiaestecorreo.com') != -1) {
        $("#email").val($("#identificacion").val() + "@cambiaestecorreo.com");
      }
      @endif
    }
  });

  $(".btn-remplazar-archivo").click(function() {
    var archivoR = $(this).data('input');
    $("#mensaje_remplazar_" + archivoR).addClass('d-none');
    $("#div_input_" + archivoR).removeClass('d-none');
  });
</script>

<script>
  $(".botonSubirArchivo").click(function() {
    var input = $(this).data('input');
    $('#' + input).click();
  });

  $('.inputFile').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    var input = $(this).data('input');
    $('#nombre_' + input).val(fileName);
  });
</script>

<script type="module">
  $('.btnGuardar').click(function() {
    // Obtener los datos del formulario del paso actual
    var datosFormulario = $('#formulario').find('input, select, textarea').serializeArray();

    // Convertir los datos del formulario a un objeto
    var data = {};
    $.each(datosFormulario, function() {
      let nameInput = this.name.replace(/\[\]/g, "");
      data[nameInput] = this.value;
    });

    // Llamar al método validar del componente Livewire
    Livewire.dispatch('validar', {
      tipoValidacion: 'formulario',
      seccionId: null,
      dataSeccion: data
    });
  });

  Livewire.on('validacionFormulario', (e) => {
    // Limpiar errores anteriores

    $('.text-danger').remove();
    if (e.resultado) {
      // Si la validacion esta todo ok
      if ($('#formulario')[0].checkValidity()) {
        $('#formulario').submit();
      } else {
        Swal.fire({
          title: '¡Ya falta poco!',
          text: 'Solo te falta aceptar los términos y condiciones.',
          icon: 'info',
          showCancelButton: false,
          focusConfirm: false,
          confirmButtonText: 'Entiendo',
          customClass: {
            confirmButton: 'btn btn-primary rounded-pill me-3 waves-effect waves-light'
          },
        });
      }
    } else {
      // La validación falló, mostrar los errores al usuario

      // Mostrar los errores debajo de cada campo
      $.each(e.errores, function(campo, mensajes) {
        var input = $("body input[name=" + campo + "]");
        var divError = $(
          '<div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i> ' +
          mensajes + '</div>');
        $('#error' + campo).html(divError);
      });
    }

  });

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

@endsection

@section('content')



<h4 class="mb-1 fw-semibold text-primary">{{ $formulario->titulo }}</h4>
<p class="mb-4 text-black">{{ $formulario->descripcion }}</p>

@include('layouts.status-msn')

<!-- Navbar pills -->
@if ($formulario->es_formulario_exterior == false)
<div class="row">
  <div class="col-md-12">
    <div class="card mb-10 p-1 border-1">
      <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">
        @can('modificarUsuarioPolitica', [App\Models\User::class, $formulario])
        <li class="nav-item flex-fill">
          <a id="tap-principal" href="{{ route('usuario.modificar', [$formulario, $usuario]) }}" class="nav-link p-3 waves-effect waves-light active" data-tap="principal">
            <i class='ti-xs ti ti-user-check me-2'></i> Datos principales
          </a>
        </li>
        @endcan

        @can('informacionCongregacionalPolitica', $usuario)
        <li class="nav-item flex-fill">
          <a id="tap-info-congregacional" href="{{ route('usuario.informacionCongregacional', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="info-congregacional">
            <i class='ti-xs ti ti-building-church me-2'></i> Información congregacional
          </a>
        </li>
        @endif

        @can('geoasignacionUsuarioPolitica', $usuario)
        <li class="nav-item flex-fill">
          <a id="tap-geoasignacion" href="{{ route('usuario.geoAsignacion', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="geoasignacion">
            <i class='ti-xs ti ti-map-pin-2 me-2'></i>Geo asignación
          </a>
        </li>
        @endif

        @can('relacionesFamiliaresUsuarioPolitica', $usuario)
        <li class="nav-item flex-fill">
          <a id="tap-familia" href="{{ route('usuario.relacionesFamiliares', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="familia">
            <i class='ti-xs ti ti-home-heart me-2'></i>Relaciones familiares
          </a>
        </li>
        @endif
      </ul>
    </div>
  </div>
</div>
@endif
<!--/ Navbar pills -->

@livewire('Usuarios.Formularios.validar-formulario', ['formulario' => $formulario, 'usuario' => $usuario])

<form id="formulario" role="form" class="forms-sample" method="POST"
  action="{{ route($formulario->tipo->action, ['formulario' => $formulario, 'usuario' => $usuario]) }}"
  enctype="multipart/form-data">
  @csrf
  @method('PATCH')

  @foreach ($formulario->secciones as $seccion)
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header text-black fw-semibold">
        @if ($seccion->logo)
        <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/secciones-formulario/' . $seccion->logo) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $seccion->logo }}?v={{ time() }}"
          alt="react-logo" class="me-2" width="30">
        @endif
        {{ $seccion->titulo }}
      </h5>

      <div class="card-body">
        <div class="row">
          @foreach ($seccion->campos()->orderBy('campo_seccion_formulario_usuario.orden', 'asc')->orderBy('nombre', 'asc')->get() as $campo)
          @if ($campo->es_campo_extra)
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="{{ $campo->name_id }}">
              {{ $campo->nombre }}
            </label>

            <!-- campo tipo 1 -->
            @if ($campo->tipo_de_campo == 1)
            <input id="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}"
              value="{{ old($campo->name_id, $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first() ? $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()->pivot->valor : '') }}"
              class="form-control">
            @endif
            <!-- /campo tipo 1 -->

            <!-- campo tipo 2 -->
            @if ($campo->tipo_de_campo == 2)
            <textarea id="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}"
              class="form-control">{{ old($campo->name_id, $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first() ? $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()->pivot->valor : '') }}</textarea>
            @endif
            <!-- /campo tipo 2 -->

            <!-- campo tipo 3 -->
            @if ($campo->tipo_de_campo == 3)
            <select id="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}"
              class="select2 form-control" data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach (json_decode($campo->opciones_select) as $opcion)
              <option value="{{ $opcion->value }}"
                {{ old($campo->name_id, $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first() ? $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()->pivot->valor : '') == $opcion->value ? 'selected' : '' }}>
                {{ ucwords($opcion->nombre) }}
              </option>
              @endforeach
            </select>
            @endif
            <!-- /campo tipo 3 -->

            <!-- campo tipo 4 -->
            @if ($campo->tipo_de_campo == 4)
            <select id="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}[]" multiple class="select2 form-control"
              data-allow-clear="true">
              @foreach (json_decode($campo->opciones_select) as $opcion)
              <option value="{{ $opcion->value }}"
                {{ in_array(
                                                            $opcion->value,
                                                            old(
                                                                $campo->name_id,
                                                                $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()
                                                                    ? json_decode(
                                                                        $usuario->camposFormularioUsuario()->where('campos_formulario_usuario.id', $campo->id)->first()->pivot->valor,
                                                                    )
                                                                    : [],
                                                            ),
                                                        )
                                                            ? 'selected'
                                                            : '' }}>
                {{ ucwords($opcion->nombre) }}
              </option>
              @endforeach
            </select>
            @endif
            <!-- /campo tipo 4 -->

            <div id="error{{ $campo->name_id }}"></div>

          </div>
          @else
          <!-- foto -->
          @if ($campo->nombre_bd == 'foto')
          <div class="col-12 mb-3">
            <div class="avatar avatar-xxl">
              <img id="preview-foto"
                src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario->foto }}"
                alt="{{ $usuario->foto }}"
                class="cropped-img  avatar-initial rounded-circle border border-5 border-white bg-info">
              <button
                class="btn btn-sm rounded-pill btn-icon btn-primary waves-effect waves-light position-absolute bottom-0 end-0 mb-2 mr-2"
                data-bs-toggle="modal" data-bs-target="#modalFoto"><i
                  class="ti ti-camera"></i></button>
            </div>
            <input class="form-control d-none" type="text"
              value="{{ old('foto') }}" id="imagen-recortada"
              name="{{ $campo->name_id }}">
          </div>
          @endif
          <!-- foto -->

          <!-- fecha nacimiento -->
          @if ($campo->nombre_bd == 'fecha_nacimiento')
          @livewire('Usuarios.formularios.fecha-nacimiento', [
          'fechaDefault' => $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento : $fechaDefault,
          'usuario' => $usuario,
          'class' => $campo->pivot->class,
          'label' => $campo->nombre,
          'nameId' => $campo->name_id,
          'formulario' => $formulario,
          ])
          @endif
          <!-- fecha nacimiento -->

          <!--  Tipo de id  -->
          @if ($campo->nombre_bd == 'tipo_identificacion_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="tipo_identificacion_id">
              {{ $campo->nombre }}
            </label>
            <select id="tipo_identificacion_id" name="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="" selected>Ninguno</option>
              @foreach ($tiposIdentificaciones as $tipoIdentificacion)
              <option value="{{ $tipoIdentificacion->id }}"
                {{ old($campo->name_id, $usuario->tipo_identificacion_id) == $tipoIdentificacion->id ? 'selected' : '' }}>
                {{ $tipoIdentificacion->nombre }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!--  Tipo de id  -->

          <!-- identificacion -->
          @if ($campo->nombre_bd == 'identificacion')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="identificacion">
              {{ $campo->nombre }}
            </label>
            <input id="identificacion" name="{{ $campo->name_id }}"
              placeholder="{{ $campo->placeholder }}"
              value="{{ old($campo->name_id, $usuario->identificacion) }}"
              onkeyup="javascript:this.value=this.value.replace('.', '').replace(' ', '')"
              type="text" class="form-control" autocomplete="off" />
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /identificacion -->

          <!-- /Email -->
          @if ($campo->nombre_bd == 'email')
          <div class="mb-3 form-group {{ $campo->pivot->class }}">
            <label class="form-label" for="email">
              {{ $campo->nombre }}
            </label>
            <div class="input-group input-group-merge">
              <input type="email" id="email" name="{{ $campo->name_id }}"
                placeholder="{{ $campo->placeholder }}"
                value="{{ old($campo->name_id, $usuario->email) }}"
                onkeyup="javascript:this.value=this.value.toLowerCase()"
                class="form-control" />
            </div>
            <div id="error{{ $campo->name_id }}"></div>

          </div>
          @endif
          <!-- /Email -->

          <!-- Primer Nombre -->
          @if ($campo->nombre_bd == 'primer_nombre')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="primer_nombre">
              {{ $campo->nombre }}
            </label>
            <input id="primer_nombre" name="{{ $campo->name_id }}"
              placeholder="{{ $campo->placeholder }}"
              value="{{ old($campo->name_id, $usuario->primer_nombre) }}"
              type="text" class="form-control" />
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Primer Nombre  -->

          <!-- Segundo Nombre  -->
          @if ($campo->nombre_bd == 'segundo_nombre')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="segundo_nombre">
              {{ $campo->nombre }}
            </label>
            <input id="segundo_nombre" name="{{ $campo->name_id }}"
              placeholder="{{ $campo->placeholder }}"
              value="{{ old($campo->name_id, $usuario->segundo_nombre) }}"
              type="text" class="form-control" />
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Segundo Nombre -->

          <!-- Primer apellido -->
          @if ($campo->nombre_bd == 'primer_apellido')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="primer_apellido">
              {{ $campo->nombre }}
            </label>
            <input id="primer_apellido" name="{{ $campo->name_id }}"
              placeholder="{{ $campo->placeholder }}"
              value="{{ old($campo->name_id, $usuario->primer_apellido) }}"
              type="text" class="form-control" />
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Primer apellido  -->

          <!-- Segundo apellido  -->
          @if ($campo->nombre_bd == 'segundo_apellido')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="segundo_apellido">
              {{ $campo->nombre }}
            </label>
            <input id="segundo_apellido" name="{{ $campo->name_id }}"
              placeholder="{{ $campo->placeholder }}"
              value="{{ old($campo->name_id, $usuario->segundo_apellido) }}"
              type="text" class="form-control" />
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Segundo apellido -->

          <!-- Genero sexual -->
          @if ($campo->nombre_bd == 'genero')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="genero">
              {{ $campo->nombre }}
            </label>
            <select id="genero" name="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}"
              class="grupoSelect select2 selectorGenero form-select"
              data-allow-clear="true">
              <option id="genero-m" value="0"
                {{ old($campo->name_id, $usuario->genero) == 0 ? 'selected' : '' }}>
                Masculino</option>
              <option id="genero-f" value="1"
                {{ old($campo->name_id, $usuario->genero) == 1 ? 'selected' : '' }}>
                Femenino</option>
              <option value=""
                {{ old($campo->name_id, $usuario->genero) === '' ? 'selected' : '' }}>
                Ninguno</option>
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Genero sexual -->

          <!-- Estado Civil -->
          @if ($campo->nombre_bd == 'estado_civil_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="estado_civil_id">
              {{ $campo->nombre }}
            </label>
            <select id="estado_civil_id" name="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="" selected>Ninguno</option>
              @foreach ($tiposDeEstadosCiviles as $tiposDeEstadoCivil)
              <option value="{{ $tiposDeEstadoCivil->id }}"
                {{ old($campo->name_id, $usuario->estado_civil_id) == $tiposDeEstadoCivil->id ? 'selected' : '' }}>
                {{ $tiposDeEstadoCivil->nombre }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Estado Civil -->

          <!-- Nacionalidad -->
          @if ($campo->nombre_bd == 'pais_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="pais_id">
              {{ $campo->nombre }}
            </label>
            <select id="pais_id" name="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($paises as $pais)
              <option value="{{ $pais->id }}"
                {{ old($campo->name_id, $usuario->pais_id) == $pais->id ? 'selected' : '' }}>
                {{ ucwords($pais->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Nacionalidad -->

          <!-- Telefono fijo -->
          @if ($campo->nombre_bd == 'telefono_fijo')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="telefono_fijo">
              {{ $campo->nombre }}
            </label>
            <div class="input-group input-group-merge">
              <input id="telefono_fijo" name="{{ $campo->name_id }}"
                placeholder="{{ $campo->placeholder }}"
                value="{{ old($campo->name_id, $usuario->telefono_fijo) }}"
                type="text" class="form-control" spellcheck="false"
                data-ms-editor="true">
              <span id="basic-icon-default-phone2" class="input-group-text"><i
                  class="ti ti-phone"></i></span>
            </div>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Telefono fijo -->

          <!-- Telefono Movil #1 -->
          @if ($campo->nombre_bd == 'telefono_movil')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="telefono_movil">
              {{ $campo->nombre }}
            </label>
            <div class="input-group input-group-merge">
              <input id="telefono_movil" name="{{ $campo->name_id }}"
                placeholder="{{ $campo->placeholder }}"
                value="{{ old($campo->name_id, $usuario->telefono_movil) }}"
                type="text" class="form-control" spellcheck="false"
                data-ms-editor="true">
              <span id="basic-icon-default-phone2" class="input-group-text"><i
                  class="ti ti-device-mobile"></i></span>
            </div>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Telefono Movil #1 -->

          <!-- Telefono  otro Telefono -->
          @if ($campo->nombre_bd == 'telefono_otro')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="telefono_otro">
              {{ $campo->nombre }}
            </label>
            <div class="input-group input-group-merge">
              <input id="telefono_otro" name="{{ $campo->name_id }}"
                placeholder="{{ $campo->placeholder }}"
                value="{{ old($campo->name_id, $usuario->telefono_otro) }}"
                type="text" class="form-control" spellcheck="false"
                data-ms-editor="true">
              <span id="basic-icon-default-phone2" class="input-group-text"><i
                  class="ti ti-phone"></i></span>
            </div>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Telefono otro Telefono -->

          <!-- vivienda_en_calidad_de -->
          @if ($campo->nombre_bd == 'tipo_vivienda_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="vivienda_en_calidad_de">
              {{ $campo->nombre }}
            </label>
            <select id="vivienda_en_calidad_de" name="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($tiposDeVivienda as $tipoDeVivienda)
              <option value="{{ $tipoDeVivienda->id }}"
                {{ old($campo->name_id, $usuario->tipo_vivienda_id) == $tipoDeVivienda->id ? 'selected' : '' }}>
                {{ ucwords($tipoDeVivienda->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /vivienda_en_calidad_de -->

          <!-- Direccion -->
          @if ($campo->nombre_bd == 'direccion')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="direccion">
              {{ $campo->nombre }}
            </label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="ti ti-map"></i></span>
              <input id="direccion" name="{{ $campo->name_id }}"
                placeholder="{{ $campo->placeholder }}"
                value="{{ old($campo->name_id, $usuario->direccion) }}"
                type="text" class="form-control" spellcheck="false"
                data-ms-editor="true">
            </div>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Direccion -->

          <!-- pregunta_vives_en -->
          @if ($campo->nombre_bd == 'pregunta_vives_en')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <div class=" small fw-medium mb-1">{{ $campo->nombre }}</div>
            <label class="switch switch-lg">
              <input id="preguntaVivesEn" name="{{ $campo->name_id }}"
                placeholder="{{ $campo->placeholder }}" type="checkbox"
                @checked(old($campo->name_id, $usuario->localidad_id ? true : false)) class="switch-input preguntaVivesEn" />
              <span class="switch-toggle-slider">
                <span class="switch-on">SI</span>
                <span class="switch-off">NO</span>
              </span>
              <span class="switch-label"></span>
            </label>
          </div>
          @endif
          <!-- / pregunta_vives_en -->

          <!-- / ubicacion-->
          @if ($tieneCampoPreguntaViveEn)
          @if ($campo->nombre_bd == 'ubicacion')
          @livewire('Generales.barrio-localidad-buscador', [
          'class' => $campo->pivot->class,
          'label' => $campo->nombre,
          'nameId' => $campo->name_id,
          'conPreguntaAdiccional' => 'si',
          'mostrar' => false,
          'placeholder' => $campo->placeholder,
          'usuario' => $usuario,
          ])
          @endif
          @else
          @if ($campo->nombre_bd == 'ubicacion')
          @livewire('Generales.barrio-localidad-buscador', [
          'class' => $campo->pivot->class,
          'label' => $campo->nombre,
          'nameId' => $campo->name_id,
          'conPreguntaAdiccional' => 'no',
          'mostrar' => true,
          'placeholder' => $campo->placeholder,
          'usuario' => $usuario,
          ])
          @endif
          @endif
          <!-- / ubicacion-->

          <!-- Nivel academico -->
          @if ($campo->nombre_bd == 'nivel_academico_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="nivel_academico">
              {{ $campo->nombre }}
            </label>
            <select id="nivel_academico" data-placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($nivelesAcademicos as $nivelAcademico)
              <option value="{{ $nivelAcademico->id }}"
                {{ old($campo->name_id, $usuario->nivel_academico_id) == $nivelAcademico->id ? 'selected' : '' }}>
                {{ ucwords($nivelAcademico->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Nivel academico -->

          <!-- Estado Nivel Academico -->
          @if ($campo->nombre_bd == 'estado_nivel_academico_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="estado_nivel_academico">
              {{ $campo->nombre }}
            </label>
            <select id="estado_nivel_academico"
              data-placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($estadosNivelesAcademicos as $estadoNivelAcademico)
              <option value="{{ $estadoNivelAcademico->id }}"
                {{ old($campo->name_id, $usuario->estado_nivel_academico_id) == $estadoNivelAcademico->id ? 'selected' : '' }}>
                {{ ucwords($estadoNivelAcademico->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Estado Nivel Academico -->

          <!-- Profesión -->
          @if ($campo->nombre_bd == 'profesion_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="profesion">
              {{ $campo->nombre }}
            </label>
            <select id="profesion" data-placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($profesiones as $profesion)
              <option value="{{ $profesion->id }}"
                {{ old($campo->name_id, $usuario->profesion_id) == $profesion->id ? 'selected' : '' }}>
                {{ ucwords($profesion->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Profesión -->

          <!-- Ocupación -->
          @if ($campo->nombre_bd == 'ocupacion_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="ocupacion">
              {{ $campo->nombre }}
            </label>
            <select id="ocupacion" data-placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($ocupaciones as $ocupacion)
              <option value="{{ $ocupacion->id }}"
                {{ old($campo->name_id, $usuario->ocupacion_id) == $ocupacion->id ? 'selected' : '' }}>
                {{ ucwords($ocupacion->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Ocupación -->

          <!-- Sector económico -->
          @if ($campo->nombre_bd == 'sector_economico_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="sector_economico">
              {{ $campo->nombre }}
            </label>
            <select id="sector_economico" data-placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($sectoresEconomicos as $sectorEconomico)
              <option value="{{ $sectorEconomico->id }}"
                {{ old($campo->name_id, $usuario->sector_economico_id) == $sectorEconomico->id ? 'selected' : '' }}>
                {{ ucwords($sectorEconomico->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Sector económico -->

          <!-- Tipo de sangre -->
          @if ($campo->nombre_bd == 'tipo_sangre_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="tipo_sangre">
              {{ $campo->nombre }}
            </label>
            <select id="tipo_sangre" name="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($tiposDeSangres as $tipoSangre)
              <option value="{{ $tipoSangre->id }}"
                {{ old($campo->name_id, $usuario->tipo_sangre_id) == $tipoSangre->id ? 'selected' : '' }}>
                {{ ucwords($tipoSangre->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Tipo de sangre -->

          <!-- Indicaciones medicas -->
          @if ($campo->nombre_bd == 'indicaciones_medicas')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="indicaciones_medicas">
              {{ $campo->nombre }}
            </label>
            <textarea onkeypress="return sinComillas(event)" id="indicaciones_medicas" name="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}" placeholder="{{ $campo->placeholder }}" class="form-control"
              rows="2" maxlength="500" spellcheck="false" data-ms-editor="true">{{ old($campo->name_id, $usuario->indicaciones_medicas) }}</textarea>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Indicaciones medicas -->

          <!-- Tienes una petición -->
          @if ($campo->nombre_bd == 'tienes_una_peticion')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <div class=" small fw-medium mb-1">{{ $campo->nombre }}</div>
            <label class="switch switch-lg">
              <input id="tienesUnaPeticion" name="{{ $campo->name_id }}"
                placeholder="{{ $campo->placeholder }}" type="checkbox"
                @checked(old($campo->name_id)) class="switch-input tienesUnaPeticion" />
              <span class="switch-toggle-slider">
                <span class="switch-on">SI</span>
                <span class="switch-off">NO</span>
              </span>
              <span class="switch-label"></span>
            </label>
          </div>
          @endif
          <!-- / Tienes una petición -->

          <!-- Tipo de Petición -->
          @if ($campo->nombre_bd == 'tipo_peticion_id')
          <div id="divSelectTipoPeticion"
            class="mb-2 {{ old('tienesUnaPeticion') ? '' : 'd-none' }} {{ $campo->pivot->class }}">
            <label class="form-label" for="tipo_peticion">
              {{ $campo->nombre }}
            </label>
            <select id="tipo_peticion" name="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($tipoPeticiones as $tipoPeticion)
              <option value="{{ $tipoPeticion->id }}"
                {{ old($campo->name_id) == $tipoPeticion->id ? 'selected' : '' }}>
                {{ ucwords($tipoPeticion->nombre) }}
              </option>
              @endforeach
            </select>

            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Tipo de Petición -->

          <!-- Descripción de la petición -->
          @if ($campo->nombre_bd == 'descripcion_peticion')
          <div id="divDescripcionPeticion"
            class="mb-2 {{ old('tienesUnaPeticion') ? '' : 'd-none' }} {{ $campo->pivot->class }}">
            <label class="form-label" for="descripcion_peticion">
              {{ $campo->nombre }}
            </label>
            <textarea onkeypress="return sinComillas(event)" id="descripcion_peticion" placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="form-control" rows="2" maxlength="500" spellcheck="false"
              data-ms-editor="true">{{ old($campo->name_id) }}</textarea>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Descripción de la petición -->

          <!-- Sede -->
          @if ($campo->nombre_bd == 'sede_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="sede">
              {{ $campo->nombre }}
            </label>
            <select id="sede" data-placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($sedes as $sede)
              <option value="{{ $sede->id }}"
                {{ old($campo->name_id, $usuario->sede_id) == $sede->id ? 'selected' : '' }}>
                {{ ucwords($sede->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Sede -->

          <!-- Tipo de vinculación-->
          @if ($campo->nombre_bd == 'tipo_vinculacion_id')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="tipo_vinculacion">
              {{ $campo->nombre }}
            </label>
            <select id="tipo_vinculacion" name="{{ $campo->name_id }}"
              data-placeholder="{{ $campo->placeholder }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="">Ninguno</option>
              @foreach ($tiposDeVinculacion as $tipoDeVinculacion)
              <option value="{{ $tipoDeVinculacion->id }}"
                {{ old($campo->name_id, $usuario->tipo_vinculacion_id) == $tipoDeVinculacion->id ? 'selected' : '' }}>
                {{ ucwords($tipoDeVinculacion->nombre) }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Tipo de vinculación -->

          <!-- información opcional -->
          @if ($campo->nombre_bd == 'informacion_opcional')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="informacion_opcional">
              {{ $campo->nombre }}
            </label>
            <textarea onkeypress="return sinComillas(event)" id="informacion_opcional" placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="form-control" rows="5" maxlength="10000" placeholder="">{{ old($campo->name_id, $usuario->informacion_opcional) }}</textarea>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- información opcional-->

          <!-- campo extra reservado -->
          @if ($campo->nombre_bd == 'campo_reservado')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label class="form-label" for="campo_reservado">
              {{ $campo->nombre }}
            </label>
            <textarea onkeypress="return sinComillas(event)" id="campo_reservado" placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="form-control" rows="5" maxlength="50000" placeholder="">{{ old($campo->name_id, $usuario->campo_reservado) }}</textarea>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- campo extra reservado-->

          <!-- archivo_a -->
          @if ($campo->nombre_bd == 'archivo_a')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label id="label_archivo_a" class="form-label" for="archivo_a">
              {{ $campo->nombre }}
              @if ($campo->tiene_descargable)
              (<a href="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/archivos/descargable_archivo_a.pdf') : $configuracion->ruta_almacenamiento . '/archivos/descargable_archivo_a.pdf' }} "
                target="_blank">Descargar formato</a>)
              @endif
            </label>

            @if ($usuario->archivo_a != '')
            <div id="mensaje_remplazar_archivo_a" class="d-grid mx-auto">
              <button type="button" data-input="archivo_a"
                class="btn-remplazar-archivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1"
                style="font-size: 13px">
                <span class="ti-xs ti ti-file-upload me-2"></span>Remplazar
              </button>
              <span class="ti-12px mt-2"><i class="ti ti-info-circle text-info"></i>
                Ya adjuntaste un archivo, ¿deseo reemplazarlo?</span>
            </div>
            @endif
            <div id="div_input_archivo_a"
              class="row g-0 {{ $usuario->archivo_a != '' ? 'd-none' : '' }}">
              <div class="d-grid col-6 mx-auto">
                <button type="button" data-input="archivo_a"
                  class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1"
                  style="font-size: 13px">
                  <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                </button>
              </div>
              <div class="col-6 d-flex">
                <input type="text" id="nombre_archivo_a" class="form-control"
                  placeholder="{{ $campo->placeholder }}" readonly>
              </div>
            </div>
            <input type="file" id="archivo_a" name="{{ $campo->name_id }}"
              data-input="archivo_a" class="form-control inputFile d-none"
              accept=".gif, .jpg, .png, .jpeg, .pdf">
            @if ($errors->has('archivo_a'))
            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>
              {{ $errors->first('archivo_a') }}
            </div>
            @endif
          </div>
          @endif
          <!--/ archivo_a -->

          <!-- archivo_b -->
          @if ($campo->nombre_bd == 'archivo_b')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label id="label_archivo_b" class="form-label" for="archivo_b">
              {{ $campo->nombre }}
              @if ($campo->tiene_descargable)
              (<a href="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/archivos/descargable_archivo_b.pdf') : $configuracion->ruta_almacenamiento . '/archivos/descargable_archivo_b.pdf' }} "
                target="_blank">Descargar formato</a>)
              @endif
            </label>

            @if ($usuario->archivo_b != '')
            <div id="mensaje_remplazar_archivo_b" class="d-grid mx-auto">
              <button type="button" data-input="archivo_b"
                class="btn-remplazar-archivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1"
                style="font-size: 13px">
                <span class="ti-xs ti ti-file-upload me-2"></span>Remplazar
              </button>
              <span class="ti-12px mt-2"><i class="ti ti-info-circle text-info"></i>
                Ya adjuntaste un archivo, ¿deseo reemplazarlo?</span>
            </div>
            @endif
            <div id="div_input_archivo_b"
              class="row g-0 {{ $usuario->archivo_b != '' ? 'd-none' : '' }}">
              <div class="d-grid col-6 mx-auto">
                <button type="button" data-input="archivo_b"
                  class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1"
                  style="font-size: 13px">
                  <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                </button>
              </div>
              <div class="col-6 d-flex">
                <input type="text" id="nombre_archivo_b" class="form-control"
                  placeholder="{{ $campo->placeholder }}" readonly>
              </div>
            </div>
            <input type="file" id="archivo_b" name="{{ $campo->name_id }}"
              data-input="archivo_b" class="form-control inputFile d-none"
              accept=".gif, .jpg, .png, .jpeg, .pdf">
            @if ($errors->has('archivo_b'))
            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>
              {{ $errors->first('archivo_b') }}
            </div>
            @endif
          </div>
          @endif
          <!--/ archivo_b -->

          <!-- archivo_c -->
          @if ($campo->nombre_bd == 'archivo_c')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label id="label_archivo_c" class="form-label" for="archivo_c">
              {{ $campo->nombre }}
              @if ($campo->tiene_descargable)
              (<a href="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/archivos/descargable_archivo_c.pdf') : $configuracion->ruta_almacenamiento . '/archivos/descargable_archivo_c.pdf' }} "
                target="_blank">Descargar formato</a>)
              @endif
            </label>

            @if ($usuario->archivo_c != '')
            <div id="mensaje_remplazar_archivo_c" class="d-grid mx-auto">
              <button type="button" data-input="archivo_c"
                class="btn-remplazar-archivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1"
                style="font-size: 13px">
                <span class="ti-xs ti ti-file-upload me-2"></span>Remplazar
              </button>
              <span class="ti-12px mt-2"><i class="ti ti-info-circle text-info"></i>
                Ya adjuntaste un archivo, ¿deseo reemplazarlo?</span>
            </div>
            @endif
            <div id="div_input_archivo_c"
              class="row g-0 {{ $usuario->archivo_c != '' ? 'd-none' : '' }}">
              <div class="d-grid col-6 mx-auto">
                <button type="button" data-input="archivo_c"
                  class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1"
                  style="font-size: 13px">
                  <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                </button>
              </div>
              <div class="col-6 d-flex">
                <input type="text" id="nombre_archivo_c" class="form-control"
                  placeholder="{{ $campo->placeholder }}" readonly>
              </div>
            </div>
            <input type="file" id="archivo_c" name="{{ $campo->name_id }}"
              data-input="archivo_c" class="form-control inputFile d-none"
              accept=".gif, .jpg, .png, .jpeg, .pdf">
            @if ($errors->has('archivo_c'))
            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>
              {{ $errors->first('archivo_c') }}
            </div>
            @endif
          </div>
          @endif
          <!--/ archivo_c -->

          <!-- archivo_d -->
          @if ($campo->nombre_bd == 'archivo_d')
          <div class="mb-3 {{ $campo->pivot->class }}">
            <label id="label_archivo_d" class="form-label" for="archivo_d">
              {{ $campo->nombre }}
              @if ($campo->tiene_descargable)
              (<a href="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/archivos/descargable_archivo_d.pdf') : $configuracion->ruta_almacenamiento . '/archivos/descargable_archivo_d.pdf' }} "
                target="_blank">Descargar formato</a>)
              @endif
            </label>

            @if ($usuario->archivo_d != '')
            <div id="mensaje_remplazar_archivo_d" class="d-grid mx-auto">
              <button type="button" data-input="archivo_d"
                class="btn-remplazar-archivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1"
                style="font-size: 13px">
                <span class="ti-xs ti ti-file-upload me-2"></span>Remplazar
              </button>
              <span class="ti-12px mt-2"><i class="ti ti-info-circle text-info"></i>
                Ya adjuntaste un archivo, ¿deseo reemplazarlo?</span>
            </div>
            @endif
            <div id="div_input_archivo_d"
              class="row g-0 {{ $usuario->archivo_d != '' ? 'd-none' : '' }}">
              <div class="d-grid col-6 mx-auto">
                <button type="button" data-input="archivo_d"
                  class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1"
                  style="font-size: 13px">
                  <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                </button>
              </div>
              <div class="col-6 d-flex">
                <input type="text" id="nombre_archivo_d" class="form-control"
                  placeholder="{{ $campo->placeholder }}" readonly>
              </div>
            </div>
            <input type="file" id="archivo_d" name="{{ $campo->name_id }}"
              data-input="archivo_d" class="form-control inputFile d-none"
              accept=".gif, .jpg, .png, .jpeg, .pdf">
            @if ($errors->has('archivo_d'))
            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>
              {{ $errors->first('archivo_d') }}
            </div>
            @endif
          </div>
          @endif
          <!--/ archivo_d -->

          <!--  Tipo de id acudiente -->
          @if ($campo->nombre_bd == 'tipo_identificacion_acudiente_id')
          <div class="mb-2 {{ $campo->pivot->class }}">
            <label class="form-label" for="tipo_identificacion_acudiente_id">
              {{ $campo->nombre }}
            </label>
            <select id="tipo_identificacion_acudiente_id"
              data-placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}" class="select2 form-select"
              data-allow-clear="true">
              <option value="" selected>Ninguno</option>
              @foreach ($tiposIdentificaciones as $tipoIdentificacion)
              <option value="{{ $tipoIdentificacion->id }}"
                {{ old($campo->name_id, $usuario->tipo_identificacion_acudiente_id) == $tipoIdentificacion->id ? 'selected' : '' }}>
                {{ $tipoIdentificacion->nombre }}
              </option>
              @endforeach
            </select>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!--  Tipo de id acudiente -->

          <!-- identificacion acudiente -->
          @if ($campo->nombre_bd == 'identificacion_acudiente')
          <div class="mb-2 {{ $campo->pivot->class }}">
            <label class="form-label" for="identificacion_acudiente">
              {{ $campo->nombre }}
            </label>
            <input id="identificacion_acudiente" placeholder="{{ $campo->placeholder }}"
              name="{{ $campo->name_id }}"
              value="{{ old($campo->name_id, $usuario->identificacion_acudiente) }}"
              type="text" class="form-control" />
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /identificacion acudiente -->

          <!-- Nombre acudiente -->
          @if ($campo->nombre_bd == 'nombre_acudiente')
          <div class="mb-2 {{ $campo->pivot->class }}">
            <label class="form-label" for="nombre_acudiente">
              {{ $campo->nombre }}
            </label>
            <input id="nombre_acudiente" name="{{ $campo->name_id }}"
              placeholder="{{ $campo->placeholder }}"
              value="{{ old($campo->name_id, $usuario->nombre_acudiente) }}"
              type="text" class="form-control" />
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Nombre acudiente  -->

          <!-- Telefono acudiente -->
          @if ($campo->nombre_bd == 'telefono_acudiente')
          <div class="mb-2 {{ $campo->pivot->class }}">
            <label class="form-label" for="telefono_acudiente">
              {{ $campo->nombre }}
            </label>
            <div class="input-group input-group-merge">
              <span id="basic-icon-default-phone2" class="input-group-text"><i
                  class="ti ti-phone"></i></span>
              <input id="telefono_acudiente" placeholder="{{ $campo->placeholder }}"
                name="{{ $campo->name_id }}"
                value="{{ old($campo->name_id, $usuario->telefono_acudiente) }}"
                type="text" class="form-control" spellcheck="false"
                data-ms-editor="true">
            </div>
            <div id="error{{ $campo->name_id }}"></div>
          </div>
          @endif
          <!-- /Telefono acudiente -->
          @endif
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endforeach

  <!-- Terminos y condiciones-->
  @if ($formulario->visible_terminos_condiciones)
  <div class="col-12">
    <div class="form-check mt-3">
      <input class="form-check-input" type="checkbox" name="habeas" @checked(old('habeas')) required
        id="habeas">
      <label class="form-check-label" for="habeas">
        <b>Términos y condiciones</b>
      </label>
      <br>
      {{ $formulario->mensaje_terminos_condiciones_resumen }}
      @if ($formulario->mensaje_terminos_condiciones_detallado != '')
      <a href="javascript:;" target="_blank" data-bs-toggle="modal"
        data-bs-target="#modalTerminosCondiciones">(Ver más) </a>
      @endif
      @if ($formulario->url_terminos_condiciones != '')
      <a href="{{ $formulario->url_terminos_condiciones }}" target="_blank">Más información...</a>
      @endif

      @if ($formulario->mensaje_terminos_condiciones_detallado != '')
      <!-- modal terminos y condiciones -->
      <div class="modal fade" id="modalTerminosCondiciones" data-bs-backdrop="static" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-dialog-scrollable" role="document">
          <div class="modal-content">
            <div class="modal-body p-1">
              <button type="button" class="btn-close" data-bs-dismiss="modal"
                aria-label="Close"></button>
              <div class="mb-6">
                <h4 class="mb-2 text-center fw-semibold">
                  {{ $formulario->label_terminos_condiciones }}
                </h4>
                <p></p>
              </div>
              @if (auth()->user())
              <p>Yo <b>{{ auth()->user()->nombre(3) }}</b>.</p>
              @endif
              <p>{!! $formulario->mensaje_terminos_condiciones_detallado !!}</p>
            </div>
            <div class="modal-footer p-0">
              <button type="button" class="btn btn-label-secondary rounded-pill"
                data-bs-dismiss="modal" aria-label="Close">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
      <!--/ modal terminos y condiciones -->
      @endif

    </div>
  </div>
  @endif
  <!--/ Terminos y condiciones-->

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="button" class="btn btnGuardar btn-primary rounded-pill px-12 py-2">
        <span class="align-middle me-sm-1 me-0 ">Guardar</span>
      </button>
    </div>
  </div>
  <!-- /botonera -->
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
              <label class="mb-2"><span class="fw-bold">Paso #1</span> Selecciona la foto</label><br>
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
          <button type="submit" class="btn btn-primary crop me-sm-3 me-1"
            data-bs-dismiss="modal">Guardar</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
            aria-label="Close">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ modal foto -->

<!-- modal cambio de formulario -->
<div class="modal fade" id="modalCambioDeFormulario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="mb-6">
          <h4 class="mb-2 text-center">Cambio de fecha</h4>
          <p>El rango permitido de edades para este formulario es de <b>{{ $formulario->edad_minima }}</b> a
            <b>{{ $formulario->edad_maxima }}</b> años, estás intentando cambiar la fecha fuera del rango.
            Por favor, usa uno de los siguientes formularios.
          </p>
        </div>
        <div class="row" id="modalMsnCambioDeFormulario">

        </div>
      </div>
    </div>
  </div>
</div>
<!-- modal cambio de formulario -->

@endsection
