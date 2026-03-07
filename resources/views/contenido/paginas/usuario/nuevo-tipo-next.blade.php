@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends($layout)

@section('title', 'Nuevo usuario')

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

@section('vendor-style')


<style>
  body {
    overflow-x: hidden;
  }
</style>

@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
])
@endsection


@section('page-script')
@vite([
'resources/assets/js/form-basic-inputs.js',
'resources/assets/js/form-input-group.js'
])

<script type="module">

  $(function () {
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
      cropper = new Cropper( croppingImage, {
        zoomable: false,
        aspectRatio: 1,
        cropBoxResizable: true
      });
    }, 1000);

    // on change show image with crop options
    upload.addEventListener('change', function (e) {
      if (e.target.files.length) {
        console.log(e.target.files[0]);
        var fileType = e.target.files[0].type;
        if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
          cropper.destroy();
          // start file reader
          const reader = new FileReader();
          reader.onload = function (e) {
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
    cropBtn.addEventListener('click', function (e) {
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
    $('#'+event.detail.nombreModal).modal('show');
    $('#modalMsnCambioDeFormulario').html(event.detail.html);
  });

</script>

<script type="module">
  $('.selectorGenero').on('change', function(event) {
    if ($("#imagen-recortada").val() == "") {
      if ($(this).val() == 1) {
        $("#preview-foto").attr("src", "{{$configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/default-f.png') : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/default-f.png' }}");
      } else {
        $("#preview-foto").attr("src", "{{$configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/default-m.png') : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/default-m.png' }}");
      }
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
      Livewire.dispatch('mostrarBuscadorUbicacion', { cambiarPor: true });
    } else {
      Livewire.dispatch('mostrarBuscadorUbicacion', { cambiarPor: false });
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
      @if($configuracion->correo_por_defecto == TRUE && $formulario->visible_email == TRUE)
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
    $('#'+input).click();
  });

  $('.inputFile').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    var input = $(this).data('input');
    $('#nombre_'+input).val(fileName);
  });
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

<script >
  $(document).ready(function () {
    let actualStep = 1;
    let maximoStep = @json($cantidadTotalSecciones);

    $(".next-step").click(function () {
      let seccionId = $(this).data('seccion');

      // Obtener los datos del formulario del paso actual
      var datosFormulario = $('#step-' + actualStep).find('input, select, textarea').serializeArray();

      // Convertir los datos del formulario a un objeto
      var data = {};
      $.each(datosFormulario, function() {
        let nameInput = this.name.replace(/\[\]/g, "");
        data[nameInput] = this.value;
      });

      // Llamar al método validar del componente Livewire
      Livewire.dispatch('validar', { tipoValidacion: 'seccion', seccionId: seccionId, dataSeccion: data });

    });
    // Escuchar el evento de validación

    Livewire.on('validacionFormulario', (e) => {
       // Limpiar errores anteriores

      $('.text-danger').remove();
      if (e.resultado) {
        // La validación fue exitosa, pasar al siguiente paso

        $("#step-" + actualStep).addClass('d-none');
        actualStep++;

        $(".prev-step").removeClass('d-none');
        $("#step-" + actualStep).removeClass('d-none');

        if(actualStep > maximoStep)
        {
          // Obtener los datos del formulario del paso actual
          var datosFormulario = $('#formulario').find('input, select, textarea').serializeArray();

          // Convertir los datos del formulario a un objeto
          var data = {};
          $.each(datosFormulario, function() {
              // si es un select2 obtenemos el text y no value
              if($('#formulario select[name="' + this.name + '"]').length){
                data[this.name] = $('select[name="' + this.name + '"]').find('option:selected').text();
              }else{
                data[this.name] = this.value;
              }

          });
          Livewire.dispatch('crearResumen', { dataSeccion: data });
        }
      } else {
        // La validación falló, mostrar los errores al usuario

        // Mostrar los errores debajo de cada campo
        $.each(e.errores, function(campo, mensajes) {
          var input = $("body input[name="+campo+"]");
          var divError = $('<div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i> ' + mensajes + '</div>');
          $('#error'+campo).html(divError);
        });
      }
    });

    Livewire.on('imprimirResumen', (e) => {
      $('#htmlRespuesta').html(e.html);
    });

    $(".prev-step").click(function () {
      if (actualStep > 1) {
        $("#step-" + actualStep).addClass('d-none');
        actualStep--;

        if(actualStep == 1) {
          $(".prev-step").addClass('d-none');
        }
        $("#step-" + actualStep).removeClass('d-none');
      }
    });

    $(document).on('click', '.step-especifico', function () {
      if (actualStep > 1) {
        $("#step-" + actualStep).addClass('d-none');
        actualStep= $(this).data('seccion');

        if(actualStep == 1) {
          $(".prev-step").addClass('d-none');
        }
        $("#step-" + actualStep).removeClass('d-none');
      }
    });

  });
</script>

  @if($formulario->visible_terminos_condiciones)
  <script>
    const modal = document.getElementById('modalTerminosCondiciones');
    const modalBody = modal.querySelector('.modal-body');
    const btnAceptar = modal.querySelector('#btnAceptoTerminos');

    function verificarScroll() {
      if (modalBody.scrollHeight > modalBody.clientHeight) {
        // Hay scroll, así que el botón debe estar deshabilitado al inicio
        btnAceptar.disabled = true;

        // Listener para el scroll
        modalBody.addEventListener('scroll', () => {
          if (modalBody.scrollTop + modalBody.clientHeight >= modalBody.scrollHeight) {
            btnAceptar.disabled = false;
          }
        });
      } else {
        // No hay scroll, el botón debe estar habilitado
        btnAceptar.disabled = false;
      }
    }

    // Verificar el scroll al abrir el modal
    modal.addEventListener('shown.bs.modal', () => {
      modalBody.scrollTop = 0; // Reinicia el scroll
      verificarScroll(); // Verifica si hay scroll y ajusta el botón
    });

    // Listener para el clic en "Aceptar" (igual que antes)
    btnAceptar.addEventListener('click', () => {
      if (btnAceptar.disabled) {
        alert("Debes llegar al final de los términos y condiciones para continuar.");
        return;
      }
      $('#formulario').submit();
      // Aquí puedes agregar la lógica para enviar el formulario o realizar otras acciones
    });

  </script>
  @endif
@endsection

@section('content')
@include('layouts.status-msn')
<div class="col-12">
@livewire('Usuarios.Formularios.validar-formulario', ['formulario' => $formulario])
  <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
    <div class="col-3 text-start">
      <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
        <span class="ti-xs ti ti-arrow-left me-2"></span>
        <span class="d-none d-md-block fw-normal">Volver</span>
      </button>
    </div>
    <div class="col-6 pl-5 text-center">
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">{{ $formulario->titulo}}</h5>
    </div>
    <div class="col-3 text-end">
      <a href="{{ $formulario->tipo->es_externo ? url()->previous() : route('login') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
        <span class="d-none d-md-block fw-normal">Salir</span>
        <span class="ti-xs ti ti-x mx-2"></span>
      </a>
    </div>
  </nav>

  <div class="pt-5 px-7 px-sm-0" style="padding-bottom: 100px;">
    <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2">
      <form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route($formulario->tipo->action, [ 'formulario' => $formulario]) }}" enctype="multipart/form-data">
        @csrf

        @if($grupoId)
          <input id="grupoId" name="grupoId" value="{{ $grupoId }}" class="form-control d-none">
        @endif

        @foreach ($secciones as $seccion)
        <!-- Secciones -->
        <div class="step row {{$seccion->orden == 1 ? '' : 'd-none'}}" id="step-{{$seccion->orden}}" >
          <div class="p-2 col-12">
            <div class="d-flex align-items-start p-2 mt-1">
              <div class="badge rounded rounded-circle bg-label-primary p-3 me-1 rounded">
                <i class="{{ $seccion->icono }} ti-md"></i>
              </div>
              <div class="my-auto ms-1 ">
                <small class="text-muted">Paso {{$seccion->orden}} de {{ $cantidadTotalSecciones }} </small>
                <h6 class="mb-0">{{ $seccion->titulo }}</h6>
              </div>
            </div>
            <div class="progress mx-2">
              <div id="progress-bar" class="progress-bar" role="progressbar" style="width: {{($seccion->orden / $cantidadTotalSecciones) * 100}}%;" aria-valuenow="{{($seccion->orden / 2) * 100}}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>

          <div class="row mt-10 m-0 p-0">
            @foreach ($seccion->campos()->orderBy('campo_seccion_formulario_usuario.orden', 'asc')->orderBy('nombre', 'asc')->get() as $campo)

              @if($campo->es_campo_extra)
              <div class="mb-3 {{ $campo->pivot->class }}">
                <label class="form-label" for="{{$campo->name_id}}">
                  {{ $campo->nombre }}
                </label>

                <!-- campo tipo 1 -->
                @if($campo->tipo_de_campo == 1)
                <input id="{{$campo->name_id}}" placeholder="{{ $campo->placeholder }}" name="{{$campo->name_id}}" value="{{ old($campo->name_id) }}" class="form-control">
                @endif
                <!-- /campo tipo 1 -->

                <!-- campo tipo 2 -->
                @if($campo->tipo_de_campo == 2)
                <textarea id="{{$campo->name_id}}" placeholder="{{ $campo->placeholder }}" name="{{$campo->name_id}}" class="form-control">{{ old($campo->name_id) }}</textarea>
                @endif
                <!-- /campo tipo 2 -->

                <!-- campo tipo 3 -->
                @if($campo->tipo_de_campo == 3)
                <select id="{{$campo->name_id}}" data-placeholder="{{ $campo->placeholder }}" name="{{$campo->name_id}}" class="select2 form-control" data-allow-clear="true">
                  <option value="">Ninguno</option>
                  @foreach (json_decode($campo->opciones_select) as $opcion)
                  <option value="{{$opcion->value}}" {{ old($campo->name_id) == $opcion->value ? 'selected' : '' }}> {{ ucwords($opcion->nombre) }} </option>
                  @endforeach
                </select>
                @endif
                <!-- /campo tipo 3 -->

                <!-- campo tipo 4 -->
                @if($campo->tipo_de_campo == 4)
                <select id="{{$campo->name_id}}" data-placeholder="{{ $campo->placeholder }}" name="{{$campo->name_id}}[]" multiple class="select2 form-control" data-allow-clear="true">
                  @foreach (json_decode($campo->opciones_select) as $opcion)
                  <option value="{{$opcion->value}}" {{ in_array($opcion->value, old( $campo->name_id , [] )) ? "selected" : "" }}> {{ ucwords($opcion->nombre) }} </option>
                  @endforeach
                </select>
                @endif
                <!-- /campo tipo 4 -->
                @if($campo->pivot->informacion_de_apoyo)
                <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                @endif
                <div id="error{{$campo->name_id}}"></div>

              </div>
              @else
                <!-- foto -->
                @if($campo->nombre_bd == "foto")
                <div class="col-12 mb-3">
                    <div class="avatar avatar-xxl">
                      <img id="preview-foto"  src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/default-m.png') : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/default-m.png') }}" alt="Foto de perfil" class="cropped-img  avatar-initial rounded-circle border border-5 border-white bg-info">
                      <button type="button" class="btn btn-sm rounded-pill btn-icon btn-primary waves-effect waves-light position-absolute bottom-0 end-0 mb-2 mr-2" data-bs-toggle="modal" data-bs-target="#modalFoto"><i class="ti ti-camera"></i></button>
                    </div>
                    <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada" name="{{ $campo->name_id }}">
                </div>
                @endif
                <!-- foto -->

                <!-- fecha nacimiento -->
                @if($campo->nombre_bd == 'fecha_nacimiento')
                  @livewire('Usuarios.formularios.fecha-nacimiento', [
                    'fechaDefault' => $fechaDefault,
                    'class' => $campo->pivot->class,
                    'label' => $campo->nombre,
                    'nameId' => $campo->name_id,
                    'formulario' => $formulario
                  ])
                @endif
                <!-- fecha nacimiento -->

                <!--  Tipo de id  -->
                @if($campo->nombre_bd == 'tipo_identificacion_id')
                <div class="mb-3 {{ $campo->pivot->class}}">
                  <label class="form-label" for="tipo_identificacion_id">
                    {{ $campo->nombre }}
                  </label>
                  <select id="tipo_identificacion_id" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="select2 form-select" data-allow-clear="true">
                    <option value="" selected>Ninguno</option>
                    @foreach ($tiposIdentificaciones as $tipoIdentificacion)
                    <option value="{{$tipoIdentificacion->id}}" {{ old($campo->name_id) ? 'selected' : '' }}>{{$tipoIdentificacion->nombre}}</option>
                    @endforeach
                  </select>
                  <div id="error{{ $campo->name_id }}"></div>
                </div>
                @endif
                <!--  Tipo de id  -->

                <!-- identificacion -->
                @if($campo->nombre_bd == 'identificacion')
                <div class="mb-3 {{ $campo->pivot->class}}">
                  <label class="form-label" for="identificacion">
                    {{ $campo->nombre }}
                  </label>
                  <input id="identificacion" name="{{ $campo->name_id }}"  placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id) }}" onkeyup="javascript:this.value=this.value.replace('.', '').replace(' ', '')" type="text" class="form-control" autocomplete="off" />
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /identificacion -->

                <!-- /Email -->
                @if($campo->nombre_bd == 'email')
                <div class="mb-3 form-group {{$campo->pivot->class}}">
                  <label class="form-label" for="email">
                    {{ $campo->nombre }}
                  </label>
                  <div class="input-group input-group-merge">
                    <input type="email" id="email" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id) }}" onkeyup="javascript:this.value=this.value.toLowerCase()" class="form-control" />
                  </div>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>

                </div>
                @endif
                <!-- /Email -->

                <!-- Primer Nombre -->
                @if($campo->nombre_bd == 'primer_nombre')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="primer_nombre">
                    {{ $campo->nombre }}
                  </label>
                  <input id="primer_nombre" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id) }}" type="text" class="form-control" />
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Primer Nombre  -->

                <!-- Segundo Nombre  -->
                @if($campo->nombre_bd == 'segundo_nombre')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="segundo_nombre">
                    {{ $campo->nombre }}
                  </label>
                  <input id="segundo_nombre" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id) }}" type="text" class="form-control" />
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Segundo Nombre -->

                <!-- Primer apellido -->
                @if($campo->nombre_bd == 'primer_apellido')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="primer_apellido">
                    {{ $campo->nombre }}
                  </label>
                  <input id="primer_apellido" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id) }}" type="text" class="form-control" />
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Primer apellido  -->

                <!-- Segundo apellido  -->
                @if($campo->nombre_bd == 'segundo_apellido')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="segundo_apellido">
                    {{ $campo->nombre }}
                  </label>
                  <input id="segundo_apellido" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id) }}" type="text" class="form-control" />
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Segundo apellido -->

                <!-- Genero sexual -->
                @if($campo->nombre_bd == 'genero')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="genero">
                    {{ $campo->nombre }}
                  </label>
                  <select id="genero" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="grupoSelect select2 selectorGenero form-select" data-allow-clear="true">
                    <option id="genero-m" value="0" {{ old($campo->name_id)==0 ? 'selected' : '' }}>Masculino</option>
                    <option id="genero-f" value="1" {{ old($campo->name_id)==1 ? 'selected' : '' }}>Femenino</option>
                    <option value="" {{ old($campo->name_id) === '' ? 'selected' : '' }}>Ninguno</option>
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Genero sexual -->

                <!-- Estado Civil -->
                @if($campo->nombre_bd == 'estado_civil_id')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="estado_civil_id">
                    {{ $campo->nombre }}
                  </label>
                  <select id="estado_civil_id" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="select2 form-select" data-allow-clear="true">
                    <option value="" selected>Ninguno</option>
                    @foreach ($tiposDeEstadosCiviles as $tiposDeEstadoCivil)
                    <option value="{{$tiposDeEstadoCivil->id}}" {{ old($campo->name_id)==$tiposDeEstadoCivil->id ? 'selected' : '' }}>{{$tiposDeEstadoCivil->nombre}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Estado Civil -->

                <!-- Nacionalidad -->
                @if($campo->nombre_bd == 'pais_id')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="pais_id">
                  {{ $campo->nombre }}
                  </label>
                  <select id="pais_id" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($paises as $pais)
                    <option value="{{$pais->id}}" {{ old($campo->name_id)==$pais->id ? 'selected' : '' }}>{{ucwords ($pais->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Nacionalidad -->

                <!-- Telefono fijo -->
                @if($campo->nombre_bd == 'telefono_fijo')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="telefono_fijo">
                    {{$campo->nombre}}
                  </label>
                  <div class="input-group input-group-merge">
                    <input id="telefono_fijo" name="{{ $campo->name_id }}" placeholder="{{$campo->placeholder}}" value="{{ old($campo->name_id) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                    <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
                  </div>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Telefono fijo -->

                <!-- Telefono Movil #1 -->
                @if($campo->nombre_bd == 'telefono_movil')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="telefono_movil">
                    {{ $campo->nombre }}
                  </label>
                  <div class="input-group input-group-merge">
                    <input id="telefono_movil" name="{{ $campo->name_id }}" placeholder="{{$campo->placeholder}}" value="{{ old($campo->name_id) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                    <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-device-mobile"></i></span>
                  </div>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Telefono Movil #1 -->

                <!-- Telefono  otro Telefono -->
                @if($campo->nombre_bd == 'telefono_otro')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="telefono_otro">
                    {{ $campo->nombre }}
                  </label>
                  <div class="input-group input-group-merge">
                    <input id="telefono_otro" name="{{ $campo->name_id }}" placeholder="{{$campo->placeholder}}" value="{{ old($campo->name_id) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                    <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
                  </div>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Telefono otro Telefono -->

                <!-- vivienda_en_calidad_de -->
                @if($campo->nombre_bd == 'tipo_vivienda_id')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="vivienda_en_calidad_de">
                    {{ $campo->nombre }}
                  </label>
                  <select id="vivienda_en_calidad_de" name="{{ $campo->name_id }}" data-placeholder="{{$campo->placeholder}}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($tiposDeVivienda as $tipoDeVivienda)
                    <option value="{{$tipoDeVivienda->id}}" {{ old($campo->name_id)==$tipoDeVivienda->id ? 'selected' : '' }}>{{ucwords ($tipoDeVivienda->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /vivienda_en_calidad_de -->

                <!-- Direccion -->
                @if($campo->nombre_bd == 'direccion')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="direccion">
                    {{ $campo->nombre }}
                  </label>
                  <div class="input-group input-group-merge">
                    <input id="direccion" name="{{ $campo->name_id }}" placeholder="{{$campo->placeholder}}" value="{{ old($campo->name_id) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                  </div>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Direccion -->

                <!-- pregunta_vives_en -->
                @if($campo->nombre_bd == 'pregunta_vives_en')
                  <div class="mb-3 {{ $campo->pivot->class }}">
                    <div class="form-label small fw-medium mb-1">{{ $campo->nombre }}</div>
                    <label class="switch switch-lg">
                      <input id="preguntaVivesEn" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" type="checkbox" @checked(old($campo->name_id)) class="switch-input preguntaVivesEn" />
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
                @if($tieneCampoPreguntaViveEn)
                  @if($campo->nombre_bd == 'ubicacion')
                    @livewire('Generales.barrio-localidad-buscador', [
                      'class' => $campo->pivot->class,
                      'label' => $campo->nombre,
                      'nameId' => $campo->name_id,
                      'conPreguntaAdiccional' => 'si',
                      'mostrar' => false,
                      'placeholder' => $campo->placeholder
                    ])
                  @endif
                @else
                  @if($campo->nombre_bd == 'ubicacion')
                    @livewire('Generales.barrio-localidad-buscador', [
                      'class' => $campo->pivot->class,
                      'label' => $campo->nombre,
                      'nameId' => $campo->name_id,
                      'conPreguntaAdiccional' => 'no',
                      'mostrar' => true,
                      'placeholder' => $campo->placeholder
                    ])
                  @endif
                @endif
                <!-- / ubicacion-->


                <!-- Nivel academico -->
                @if($campo->nombre_bd == 'nivel_academico_id')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="nivel_academico">
                    {{ $campo->nombre }}
                  </label>
                  <select id="nivel_academico" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($nivelesAcademicos as $nivelAcademico)
                    <option value="{{$nivelAcademico->id}}" {{ old($campo->name_id)==$nivelAcademico->id ? 'selected' : '' }}>{{ucwords ($nivelAcademico->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Nivel academico -->

                <!-- Estado Nivel Academico -->
                @if($campo->nombre_bd == 'estado_nivel_academico_id')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="estado_nivel_academico">
                    {{ $campo->nombre }}
                  </label>
                  <select id="estado_nivel_academico" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($estadosNivelesAcademicos as $estadoNivelAcademico)
                    <option value="{{$estadoNivelAcademico->id}}" {{ old($campo->name_id)==$estadoNivelAcademico->id ? 'selected' : '' }}>{{ucwords ($estadoNivelAcademico->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Estado Nivel Academico -->

                <!-- Profesión -->
                @if($campo->nombre_bd == 'profesion_id')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="profesion">
                    {{ $campo->nombre }}
                  </label>
                  <select id="profesion" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($profesiones as $profesion)
                    <option value="{{$profesion->id}}" {{ old($campo->name_id)==$profesion->id ? 'selected' : '' }}>{{ucwords ($profesion->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Profesión -->

                <!-- Ocupación -->
                @if($campo->nombre_bd == 'ocupacion_id')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="ocupacion">
                  {{ $campo->nombre }}
                  </label>
                  <select id="ocupacion" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($ocupaciones as $ocupacion)
                    <option value="{{$ocupacion->id}}" {{ old($campo->name_id)==$ocupacion->id ? 'selected' : '' }}>{{ucwords ($ocupacion->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Ocupación -->

                <!-- Sector económico -->
                @if($campo->nombre_bd == 'sector_economico_id')
                <div class="mb-3 {{$campo->pivot->class}}">
                  <label class="form-label" for="sector_economico">
                    {{ $campo->nombre }}
                  </label>
                  <select id="sector_economico" data-placeholder="{{$campo->placeholder}}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($sectoresEconomicos as $sectorEconomico)
                    <option value="{{$sectorEconomico->id}}" {{ old($campo->name_id)==$sectorEconomico->id ? 'selected' : '' }}>{{ucwords ($sectorEconomico->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Sector económico -->

                <!-- Tipo de sangre -->
                @if($campo->nombre_bd == 'tipo_sangre_id')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="tipo_sangre">
                    {{ $campo->nombre }}
                  </label>
                  <select id="tipo_sangre" name="{{ $campo->name_id }}" data-placeholder="{{ $campo->placeholder }}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($tiposDeSangres as $tipoSangre)
                    <option value="{{$tipoSangre->id}}" {{ old($campo->name_id)==$tipoSangre->id ? 'selected' : '' }}>{{ucwords ($tipoSangre->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Tipo de sangre -->

                <!-- Indicaciones medicas -->
                @if($campo->nombre_bd == 'indicaciones_medicas')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="indicaciones_medicas">
                    {{ $campo->nombre }}
                  </label>
                  <textarea onkeypress="return sinComillas(event)" id="indicaciones_medicas" name="{{ $campo->name_id }}" data-placeholder="{{ $campo->placeholder }}" placeholder="{{ $campo->placeholder }}" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true" >{{ old($campo->name_id) }}</textarea>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Indicaciones medicas -->

                <!-- Tienes una petición -->
                @if($campo->nombre_bd == 'tienes_una_peticion')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <div class=" small fw-medium mb-1">{{ $campo->nombre }}</div>
                  <label class="switch switch-lg">
                    <input id="tienesUnaPeticion" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" type="checkbox" @checked(old($campo->name_id)) class="switch-input tienesUnaPeticion" />
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
                @if($campo->nombre_bd == 'tipo_peticion_id')
                <div id="divSelectTipoPeticion" class="mb-3 {{ old('tienes_una_peticion') ? '' : 'd-none' }} {{ $campo->pivot->class }}">
                  <label class="form-label" for="tipo_peticion">
                    {{ $campo->nombre }}
                  </label>
                  <select id="tipo_peticion" name="{{ $campo->name_id }}" data-placeholder="{{ $campo->placeholder }}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($tipoPeticiones as $tipoPeticion)
                    <option value="{{$tipoPeticion->id}}" {{ old($campo->name_id)==$tipoPeticion->id ? 'selected' : '' }}>{{ucwords ($tipoPeticion->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Tipo de Petición -->

                <!-- Descripción de la petición -->
                @if($campo->nombre_bd == 'descripcion_peticion')
                <div id="divDescripcionPeticion" class="mb-3 {{ old('tienes_una_peticion') ? '' : 'd-none' }} {{ $campo->pivot->class }}">
                  <label class="form-label" for="descripcion_peticion">
                    {{ $campo->nombre }}
                  </label>
                  <textarea onkeypress="return sinComillas(event)" id="descripcion_peticion" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true">{{ old($campo->name_id) }}</textarea>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Descripción de la petición -->

                <!-- Sede -->
                @if($campo->nombre_bd == 'sede_id')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="sede">
                    {{ $campo->nombre }}
                  </label>
                  <select id="sede" data-placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($sedes as $sede)
                    <option value="{{$sede->id}}" {{ old($campo->name_id)==$sede->id ? 'selected' : '' }}>{{ucwords ($sede->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Sede -->

                <!-- Tipo de vinculación-->
                @if($campo->nombre_bd == 'tipo_vinculacion_id')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="tipo_vinculacion">
                    {{ $campo->nombre }}
                  </label>
                  <select id="tipo_vinculacion" name="{{ $campo->name_id }}" data-placeholder="{{ $campo->placeholder }}" class="select2 form-select" data-allow-clear="true">
                    <option value="">Ninguno</option>
                    @foreach ($tiposDeVinculacion as $tipoDeVinculacion)
                    <option value="{{$tipoDeVinculacion->id}}" {{ old($campo->name_id)==$tipoDeVinculacion->id ? 'selected' : '' }}>{{ucwords ($tipoDeVinculacion->nombre)}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Tipo de vinculación -->

                <!-- información opcional -->
                @if($campo->nombre_bd == 'informacion_opcional')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="informacion_opcional">
                    {{ $campo->nombre }}
                  </label>
                  <textarea onkeypress="return sinComillas(event)" id="informacion_opcional" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" class="form-control" rows="5" maxlength="10000" placeholder="">{{ old($campo->name_id) }}</textarea>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- información opcional-->

                <!-- campo extra reservado -->
                @if($campo->nombre_bd == 'campo_reservado')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="campo_reservado">
                    {{ $campo->nombre }}
                  </label>
                  <textarea onkeypress="return sinComillas(event)" id="campo_reservado" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" class="form-control" rows="5" maxlength="50000" placeholder="">{{ old($campo->name_id) }}</textarea>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- campo extra reservado-->

                <!-- archivo_a -->
                @if($campo->nombre_bd == 'archivo_a')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label id="label_archivo_a" class="form-label" for="archivo_a">
                    {{ $campo->nombre }}
                    @if($campo->tiene_descargable)
                    (<a href="{{$configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/archivos/descargable_archivo_a.pdf') : $configuracion->ruta_almacenamiento.'/archivos/descargable_archivo_a.pdf' }} " target="_blank">Descargar formato</a>)
                    @endif
                  </label>

                  <div id="div_input_archivo_a" class="row g-0">
                    <div class="d-grid col-6 mx-auto">
                      <button type="button" data-input="archivo_a" class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                      <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                      </button>
                    </div>
                    <div class="col-6 d-flex">
                        <input type="text" id="nombre_archivo_a" class="form-control" placeholder="{{ $campo->placeholder }}" readonly>
                    </div>
                  </div>
                  <input type="file" id="archivo_a" name="{{ $campo->name_id }}" data-input="archivo_a" class="form-control inputFile d-none" accept=".gif, .jpg, .png, .jpeg, .pdf">
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!--/ archivo_a -->

                <!-- archivo_b -->
                @if($campo->nombre_bd == 'archivo_b')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label id="label_archivo_b" class="form-label" for="archivo_b">
                    {{ $campo->nombre }}
                    @if($campo->tiene_descargable)
                    (<a href="{{$configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/archivos/descargable_archivo_b.pdf') : $configuracion->ruta_almacenamiento.'/archivos/descargable_archivo_b.pdf' }} " target="_blank">Descargar formato</a>)
                    @endif
                  </label>

                  <div id="div_input_archivo_b" class="row g-0">
                    <div class="d-grid col-6 mx-auto">
                      <button type="button" data-input="archivo_b" class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                      <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                      </button>
                    </div>
                    <div class="col-6 d-flex">
                        <input type="text" id="nombre_archivo_b" class="form-control" placeholder="{{ $campo->placeholder }}" readonly>
                    </div>
                  </div>
                  <input type="file" id="archivo_b" name="{{ $campo->name_id }}" data-input="archivo_b" class="form-control inputFile d-none" accept=".gif, .jpg, .png, .jpeg, .pdf">
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!--/ archivo_b -->

                <!-- archivo_c -->
                @if($campo->nombre_bd == 'archivo_c')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label id="label_archivo_c" class="form-label" for="archivo_c">
                    {{ $campo->nombre }}
                    @if($campo->tiene_descargable)
                    (<a href="{{$configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/archivos/descargable_archivo_c.pdf') : $configuracion->ruta_almacenamiento.'/archivos/descargable_archivo_c.pdf' }} " target="_blank">Descargar formato</a>)
                    @endif
                  </label>

                  <div id="div_input_archivo_c" class="row g-0">
                    <div class="d-grid col-6 mx-auto">
                      <button type="button" data-input="archivo_c" class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                      <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                      </button>
                    </div>
                    <div class="col-6 d-flex">
                        <input type="text" id="nombre_archivo_c" class="form-control" placeholder="{{ $campo->placeholder }}" readonly>
                    </div>
                  </div>
                  <input type="file" id="archivo_c" name="{{ $campo->name_id }}" data-input="archivo_c" class="form-control inputFile d-none" accept=".gif, .jpg, .png, .jpeg, .pdf">
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!--/ archivo_c -->

                <!-- archivo_d -->
                @if($campo->nombre_bd == 'archivo_d')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label id="label_archivo_d" class="form-label" for="archivo_d">
                    {{ $campo->nombre }}
                    @if($campo->tiene_descargable)
                    (<a href="{{$configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/archivos/descargable_archivo_d.pdf') : $configuracion->ruta_almacenamiento.'/archivos/descargable_archivo_d.pdf' }} " target="_blank">Descargar formato</a>)
                    @endif
                  </label>

                  <div id="div_input_archivo_d" class="row g-0">
                    <div class="d-grid col-6 mx-auto">
                      <button type="button" data-input="archivo_d" class="botonSubirArchivo btn btn-secondary waves-effect waves-light me-2 fw-light px-1" style="font-size: 13px">
                      <span class="ti-xs ti ti-file-upload me-2"></span>Adjuntar archivo
                      </button>
                    </div>
                    <div class="col-6 d-flex">
                        <input type="text" id="nombre_archivo_d" class="form-control" placeholder="{{ $campo->placeholder }}" readonly>
                    </div>
                  </div>
                  <input type="file" id="archivo_d" name="{{ $campo->name_id }}" data-input="archivo_d" class="form-control inputFile d-none" accept=".gif, .jpg, .png, .jpeg, .pdf">
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!--/ archivo_d -->

                <!--  Tipo de id acudiente -->
                @if($campo->nombre_bd == 'tipo_identificacion_acudiente_id')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="tipo_identificacion_acudiente_id">
                    {{ $campo->nombre }}
                  </label>
                  <select id="tipo_identificacion_acudiente_id"  data-placeholder="{{ $campo->placeholder }}"  name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                    <option value="" selected>Ninguno</option>
                    @foreach ($tiposIdentificaciones as $tipoIdentificacion)
                    <option value="{{$tipoIdentificacion->id}}" {{ old($campo->name_id)==$tipoIdentificacion->id ? 'selected' : '' }}>{{$tipoIdentificacion->nombre}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!--  Tipo de id acudiente -->

                <!-- identificacion acudiente -->
                @if($campo->nombre_bd == 'identificacion_acudiente')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="identificacion_acudiente">
                    {{ $campo->nombre }}
                  </label>
                  <input id="identificacion_acudiente" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" value="{{ old($campo->name_id) }}" type="text" class="form-control" />
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /identificacion acudiente -->

                <!-- Nombre acudiente -->
                @if($campo->nombre_bd == 'nombre_acudiente')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="nombre_acudiente">
                    {{ $campo->nombre }}
                  </label>
                  <input id="nombre_acudiente" name="{{ $campo->name_id }}" placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id) }}" type="text" class="form-control" />
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Nombre acudiente  -->

                <!-- Telefono acudiente -->
                @if($campo->nombre_bd == 'telefono_acudiente')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="telefono_acudiente">
                    {{ $campo->nombre }}
                  </label>
                  <div class="input-group input-group-merge">
                    <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
                    <input id="telefono_acudiente" placeholder="{{ $campo->placeholder }}" name="{{ $campo->name_id }}" value="{{ old($campo->name_id) }}" type="text" class="form-control" spellcheck="false" data-ms-editor="true">
                  </div>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /Telefono acudiente -->

                <!-- ¿Tienes hijos menores de edad?-->
                @if($campo->nombre_bd == 'tienes_hijos_menores_de_edad')
                <div class="mb-3 {{ $campo->pivot->class }}">

                  <label class="form-label d-block" for="tienes_hijos_menores_de_edad">
                    {{ $campo->nombre }}
                  </label>
                  <div class="form-check form-check-inline mt-2 me-10">
                    <input class="form-check-input" type="radio" name="{{ $campo->name_id }}" id="tienes_hijos_menores_de_edad1" value="Si" @checked(old($campo->name_id) == 'Si' || is_null(old($campo->name_id)))>
                    <label class="form-check-label" for="tienes_hijos_menores_de_edad1">Si</label>
                  </div>
                  <div class="form-check form-check-inline ms-10">
                    <input class="form-check-input" type="radio" name="{{ $campo->name_id }}" id="tienes_hijos_menores_de_edad2" value="No" @checked(old($campo->name_id) == 'No'))>
                    <label class="form-check-label" for="tienes_hijos_menores_de_edad2">No</label>
                  </div>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /¿Tienes hijos menores de edad?-->

                <!-- password -->
                @if($campo->nombre_bd == 'password')
                <div class="mb-3 {{ $campo->pivot->class}} form-password-toggle">
                  <label class="form-label" for="password">
                    {{ $campo->nombre }}
                  </label>
                  <div class="input-group input-group-merge">
                    <input id="password" name="{{ $campo->name_id }}"  placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id) }}" type="password" class="form-control" aria-describedby="basic-default-password" />
                    <span class="input-group-text cursor-pointer" id="basic-default-password"><i class="ti ti-eye-off"></i></span>
                  </div>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>

                </div>
                @endif
                <!-- /password -->

                <!-- confirmarPassword -->
                @if($campo->nombre_bd == 'password_confirmation')
                <div class="mb-3 {{ $campo->pivot->class}} form-password-toggle">
                  <label class="form-label" for="confirmarPassword">
                    {{ $campo->nombre }}
                  </label>
                  <div class="input-group input-group-merge">
                    <input id="confirmarPassword" name="{{ $campo->name_id }}"  placeholder="{{ $campo->placeholder }}" value="{{ old($campo->name_id) }}" type="password" class="form-control" aria-describedby="basic-default-password" />
                    <span class="input-group-text cursor-pointer" id="basic-default-password"><i class="ti ti-eye-off"></i></span>
                  </div>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!-- /confirmarPassword -->

                <!--  Tipo de id acudiente -->
                @if($campo->nombre_bd == 'tipo_pariente_id')
                <div class="mb-3 {{ $campo->pivot->class }}">
                  <label class="form-label" for="tipo_pariente_id">
                    {{ $campo->nombre }}
                  </label>
                  <select id="tipo_pariente_id"  data-placeholder="{{ $campo->placeholder }}"  name="{{ $campo->name_id }}" class="select2 form-select" data-allow-clear="true">
                    <option value="" selected>Ninguno</option>
                    @foreach ($tiposParentescos as $tipoParensco)
                    <option value="{{$tipoParensco->id}}" {{ old($campo->name_id, $tipoParentescoDefault->id )==$tipoParensco->id ? 'selected' : '' }}>{{$tipoParensco->nombre}}</option>
                    @endforeach
                  </select>
                  @if($campo->pivot->informacion_de_apoyo)
                  <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->pivot->informacion_de_apoyo }}</div>
                  @endif
                  <div id="error{{$campo->name_id}}"></div>
                </div>
                @endif
                <!--  Tipo de id acudiente -->

              @endif

            @endforeach

          </div>

          <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
            <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex  {{ $seccion->orden == 1 ? 'justify-content-sm-end' : 'justify-content-sm-between' }} ">
              <button type="button" class="btn btn-label-secondary  rounded-pill btn-outline-secondary px-7 py-2 prev-step d-none" >
                <span class="align-middle">Volver</span>
              </button>
              <button type="button" class="btn btn-primary rounded-pill next-step px-7 py-2" data-seccion="{{$seccion->id}}">
                <span class="align-middle me-sm-1 me-0 ">Continuar</span>
              </button>
            </div>
          </div>
        </div>
        <!-- /Secciones -->
        @endforeach

        <!-- Resumen -->
        <div class="step row {{$cantidadTotalSecciones+1 == 1 ? '' : 'd-none'}}" id="step-{{$cantidadTotalSecciones+1 }}" >

          <div class="row mt-10 m-0 p-0">
            <img src="{{ Storage::url('generales/img/otros/dibujo_formulario_usuario_respuesta.png') }}" class="w-20 p-0">
            <h2 class="fw-semibold p-0 mb-1 text-black">Resumen</h2>
            <p class="p-0 text-black mb-5">Revisa tu información y confirma que todos los datos estén correctos.</p>
            <div class="card shadow shadow-3 rounded-3 p-0" style="background-color: #f8f7fa">
              <div id="htmlRespuesta" class="card-body p-2">
              </div>
            </div>
          </div>

          <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
            <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex  {{ $cantidadTotalSecciones+1 == 1 ? 'justify-content-sm-end' : 'justify-content-sm-between' }} ">
              <button type="button" class="btn btn-label-secondary  rounded-pill btn-outline-secondary px-7 py-2 prev-step d-none" >
                <span class="align-middle">Volver</span>
              </button>

              @if($formulario->visible_terminos_condiciones)
              <button type="button" class="btn btn-primary rounded-pill px-7 py-2" data-bs-toggle="modal" data-bs-target="#modalTerminosCondiciones">
                <span class="align-middle me-sm-1 me-0 ">Continuar</span>
              </button>
              @else
              <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-7 py-2" >
                <span class="align-middle me-sm-1 me-0 ">Continuar</span>
              </button>
              @endif
            </div>
          </div>


          @if($formulario->visible_terminos_condiciones)
            <!-- modal terminos y condiciones -->
            <div class="modal fade" id="modalTerminosCondiciones" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-simple modal-dialog-scrollable" role="document">
                <div class="modal-content">
                  <div class="modal-body p-1">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="mb-6">
                      <h4 class="mb-2 text-center fw-semibold">{{ $formulario->label_terminos_condiciones }}</h4>
                      <p></p>
                    </div>
                    @if(auth()->user())
                    <p>Yo <b>{{auth()->user()->nombre(3)}}</b>.</p>
                    @endif
                    <p >{!! $formulario->mensaje_terminos_condiciones_detallado !!}</p>
                  </div>
                  <div class="">
                    <div class="pt-5 col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex  justify-content-sm-between ">
                      <button id="btnAceptoTerminos" type="button" class="btn btnGuardar btn-primary rounded-pill me-sm-3 me-1" data-bs-dismiss="modal">Aceptar y guardar</button>
                      <button type="button" class="btn btn-label-secondary rounded-pill" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!--/ modal terminos y condiciones -->
          @endif

        </div>
        <!-- /Resumen -->
      </form>
    </div>
  </div>
</div>


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
                <img src="{{ Storage::url('generales/img/otros/placeholder.jpg') }}" class="w-100" id="croppingImage" alt="cropper">
              </center>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer text-center">
        <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary crop me-sm-3 me-1" data-bs-dismiss="modal">Guardar</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
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
          <p>El rango permitido de edades para este formulario es de <b>{{ $formulario->edad_minima }}</b> a <b>{{ $formulario->edad_maxima }}</b> años, estás intentando cambiar la fecha fuera del rango. Por favor, usa uno de los siguientes formularios.</p>
        </div>
        <div class="row" id="modalMsnCambioDeFormulario">

        </div>
      </div>
    </div>
  </div>
</div>
<!-- modal cambio de formulario -->




@endsection
