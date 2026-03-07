@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
  @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')
    <script type="module">
        document.addEventListener("DOMContentLoaded", function() {
            const toggleReserva = document.getElementById("toggleReserva");
            const reservaConfig = document.getElementById("reservaConfig");
            const toggleInvitados = document.getElementById("toggleInvitados");
            const cantidadInvitados = document.getElementById("cantidad_invitados");

            function actualizarEstadoReserva() {
                if (toggleReserva.checked) {
                    reservaConfig.classList.remove("d-none"); // Muestra los campos si está activado
                } else {
                    reservaConfig.classList.add("d-none"); // Oculta los campos si está desactivado
                }
            }

            function actualizarEstadoInvitados() {
                // Si la reserva está habilitada y los invitados también, habilita el campo de cantidad
                if (toggleReserva.checked && toggleInvitados.checked) {
                    cantidadInvitados.disabled = false;
                } else {
                    cantidadInvitados.disabled = true;
                    cantidadInvitados.value = ''; // Limpia el campo si se desactiva
                }
            }

            if (toggleReserva && reservaConfig && toggleInvitados && cantidadInvitados) {
                // Manejar la visibilidad de la configuración de reserva
                toggleReserva.addEventListener("change", function() {
                    reservaConfig.classList.toggle("d-none", !this.checked);
                    actualizarEstadoInvitados();
                    actualizarEstadoReserva();
                });

                // Manejar la habilitación del input de invitados
                toggleInvitados.addEventListener("change", actualizarEstadoInvitados);

                // Asegurar que el estado inicial sea el correcto
                actualizarEstadoInvitados();
                actualizarEstadoReserva();
            }
        });

        $(".hora-picker").flatpickr({
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        });

        $(document).ready(function() {
            $('.select2').select2({
                width: '100px',
                allowClear: true,
                placeholder: 'Ninguno'
            });
        });

        $('#selectSedes').select2();

        $(".clearAllItems").click(function() {
            var selectId = $(this).data('select');
            $('#' + selectId).val(null).trigger('change');
        });

        $(".selectAllItems").click(function() {
            var selectId = $(this).data('select');
            $('#' + selectId).select2('destroy').find('option').prop('selected', 'selected').end().select2();
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
@endsection

@section('content')

    @include('layouts.status-msn')

    <form id="formulario" role="form" class="forms-sample" method="POST"  action="{{ route('reuniones.actualizar', $reunion) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <!-- PORTADA -->
        <div class="col-md-12">
          <div class="card mb-4 rounded rounded-3">
            <img id="preview-foto" class="cropped-img card-img-top mb-2" src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/reuniones/'.$reunion->portada) }}" alt="Portada {{$reunion->nombre}}">
            <button type="button" style="background-color: rgba(255, 255, 255, 0.5);" class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2" data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;" class="ti ti-camera"></i></button>
            <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada" name="foto">

            <div class="row p-4 m-0 d-flex card-body">
              <h5 class="mb-1 fw-semibold text-black">Editar reunión</h5>
              <p class="mb-4 text-black">Aquí podras editar la reunión, por favor llena los campos que son requeridos.</p>
            </div>
          </div>
        </div>
        <!-- PORTADA -->

        <!-- Información principal -->
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header text-black fw-semibold">
                    <img src="{{ Storage::url('generales/img/reuniones/icono_seccion_informacion_principal.png') }}"
                        alt="icono" class="me-2" width="30">
                    Información principal
                </h5>
                <div class="card-body">
                    <div class="row">
                        <!-- nombre -->
                        <div class="col-lg-8 col-md-8 col-sm-12 mb-3">
                            <label for="defaultFormControlInput" class="form-label">Nombre</label>
                            <input name="nombre" type="text" value="{{ old('nombre', $reunion->nombre) }}"
                                class="form-control" placeholder="Ejemplo: Reunión familiar"
                                aria-describedby="defaultFormControlHelp" />
                            @if ($errors->has('nombre'))
                                <div class="text-danger form-label">{{ $errors->first('nombre') }}</div>
                            @endif
                        </div>
                        <!-- /nombre -->

                        <!-- sede -->
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <label for="sede" class="form-label">¿En que sede se va ha realizar?</label>
                            <select id="sede" name="sede" class="select2 form-select form-select-lg"
                                data-allow-clear="true">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s->id }}"
                                        {{ old('sede', $reunion->sede_id ?? '') == $s->id ? 'selected' : '' }}>
                                        {{ $s->nombre }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('sede'))
                                <div class="text-danger form-label">{{ $errors->first('sede') }}</div>
                            @endif
                        </div>
                        <!-- /sede -->


                        <!-- día -->
                          <!--
                        <div class="col-lg-3 col-md-3 col-sm-6 col-sm-12 mb-3">
                            <label for="dia" class="form-label">Día</label>
                            <select id="dia" name="día" class="select2 form-select" data-allow-clear="true">
                              <option value="">Sin definir</option>
                              @foreach (Helper::diasDeLaSemana() as $dia)
                              <option value="{{$dia->id}}" {{ old('día', $reunion->dia )==$dia->id ? 'selected' : '' }}>{{$dia->nombre}}</option>
                              @endforeach
                            </select>
                            @if ($errors->has('día'))
                                <div class="text-danger form-label">{{ $errors->first('día') }}</div>
                            @endif
                        </div>
                        /día -->
                        <!-- /día -->

                        <!-- Hora -->
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <label for="hora" class="form-label">Hora</label>
                            <input class="form-control" name="hora" type="time" id="hora" value="{{ old('hora', $reunion->hora) }}" />
                            @if ($errors->has('hora'))
                              <div class="text-danger form-label">{{ $errors->first('hora') }}</div>
                            @endif
                        </div>
                        <!-- /Hora -->

                        <!-- Dias de plazo -->
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3 ">
                            <label for="diasDePlazo" class="form-label">Días de plazo para reportar</label>
                            <input class="form-control" name="díasDePlazo" type="number" value="{{ old('díasDePlazo', $reunion->dias_plazo_reporte) }}" id="diasDePlazo" />
                            @if ($errors->has('díasDePlazo'))
                              <div class="text-danger form-label">{{ $errors->first('díasDePlazo') }}</div>
                            @endif
                        </div>
                        <!-- /Dias de plazo -->

                        <!-- HoraMaxima -->
                        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                            <label for="horaMaxima" class="form-label">Hora máxima para reportar </label>
                            <input class="form-control" name="horaMáxima" type="time" id="horaMaxima" value="{{ old('horaMáxima', $reunion->hora_maxima_reportar_asistencia) }}" />
                        </div>
                        <!-- /HoraMaxima -->

                        <!-- /descripción -->
                        <div class="col-12 mb-3">
                            <label for="descripcion" class="form-label my-3">Descripción</label>
                            <textarea rows="3" class="form-control w-100" id="descripcion" name="descripción">{{ old('descripción', $reunion->descripcion ?? '') }}</textarea>
                        </div>
                        <!-- /descripción -->

                        <!-- /selectSedes -->
                        <div class="col-12 mb-3">
                            <label for="selectSedes" class="form-label my-3">¿Qué sedes pueden asistir? <a
                                    href="javascript:;" data-select="selectSedes" class="ms-1 selectAllItems"><span
                                        class="fw-medium">Seleccionar todas</span></a> | <a href="javascript:;"
                                    data-select="selectSedes" class="clearAllItems"><span class="fw-medium">Quitar
                                        todas</span></a></label>
                            <div class="select2-primary">
                                <select id="selectSedes" name="sedesAsistentes[]" class="select2 form-select" multiple>
                                    @foreach ($sedes as $s)
                                        <option value="{{ $s->id }}"
                                            {{ in_array($s->id, old('sedesAsistentes', $sedesReunion)) ? 'selected' : '' }}>
                                            {{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- selectSedes -->

                        <!-- /selectSexos -->
                        <div class="col-12 mb-3">
                            <label for="selectSexos" class="form-label my-3">¿Personas de qué sexo pueden asistir? <a
                                    href="javascript:;" data-select="selectSexos" class="ms-1 selectAllItems"><span
                                        class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;"
                                    data-select="selectSexos" class="clearAllItems"><span class="fw-medium">Quitar
                                        todos</span></a></label>
                            <div class="select2-primary">
                                <select id="selectSexos" name="sexos[]" class="select2 form-select" multiple>
                                    <option value="0"
                                        {{ in_array(0, old('sexos', $sexosReunion ?? [])) ? 'selected' : '' }}>Masculino
                                    </option>
                                    <option value="1"
                                        {{ in_array(1, old('sexos', $sexosReunion ?? [])) ? 'selected' : '' }}>Femenino
                                    </option>
                                </select>
                            </div>
                        </div>
                        <!-- /selectSexos -->

                        <!-- /selectRangoEdad -->
                        <div class="col-12 mb-3">
                            <label for="selectRangoEdad" class="form-label my-3">¿Personas de qué rango de edades pueden asistir?
                              <a href="javascript:;" data-select="selectRangoEdad" class="ms-1 selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> |
                              <a href="javascript:;" data-select="selectRangoEdad" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>
                            </label>
                            <div class="select2-primary">
                                <select id="selectRangoEdad" name="rangosEdad[]" class="select2 form-select" multiple>
                                    @foreach ($tiposEdades as $tipoEdad)
                                        <option value="{{ $tipoEdad->id }}"
                                            {{ in_array($tipoEdad->id, old('rangosEdad', $tiposEdadesReunion)) ? 'selected' : '' }}>
                                            {{ $tipoEdad->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- /selectRangoEdad -->

                        <!-- /selectTipoUsuarios -->
                        <div class="col-12 mb-3">
                              <label for="selectTipoUsuarios" class="form-label my-3">¿Qué tipos de usuarios pueden asistir?
                                <a href="javascript:;" data-select="selectTipoUsuarios" class="ms-1 selectAllItems"><span class="fw-medium">Seleccionar todas</span></a> |
                                <a href="javascript:;" data-select="selectTipoUsuarios" class="clearAllItems"><span class="fw-medium">Quitar todas</span></a>
                              </label>
                            <div class="select2-primary">
                                <select id="selectTipoUsuarios" name="tipoUsuarios[]" class="select2 form-select"
                                    multiple>
                                    @foreach ($tipoUsuarios as $tu)
                                        <option value="{{ $tu->id }}"
                                            {{ in_array($tu->id, old('tipoUsuarios', $tipoUsuariosReunion)) ? 'selected' : '' }}>
                                            {{ $tu->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- /selectTipoUsuarios -->

                        <!-- /selectOfrendas -->
                        <div class="col-12 mb-3">
                            <label for="selectOfrendas" class="form-label my-3">¿Qué tipos de ofrendas se recolectarán?
                              <a href="javascript:;" data-select="selectOfrendas" class="ms-1 selectAllItems"><span class="fw-medium">Seleccionar todas</span></a> |
                              <a href="javascript:;" data-select="selectOfrendas" class="clearAllItems"><span class="fw-medium">Quitar todas</span></a>
                            </label>
                            <div class="select2-primary">
                                <select id="selectOfrendas" name="ofrendas[]" class="select2 form-select" multiple>
                                    @foreach ($ofrendas as $ofrenda)
                                        <option value="{{ $ofrenda->id }}"
                                            {{ in_array($ofrenda->id, old('ofrendas', $ofrendaReunion)) ? 'selected' : '' }}>
                                            {{ $ofrenda->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- /selectOfrendas -->



                        <!-- /selectClasificacionAsistentes -->
                        <div class="col-12 mb-3">
                            <label for="selectClasificacionAsistentes" class="form-label my-3">Define las clasificaciones
                                <a href="javascript:;" data-select="selectClasificacionAsistentes"
                                    class="ms-1 selectAllItems"><span class="fw-medium">Seleccionar todas</span></a> | <a
                                    href="javascript:;" data-select="selectClasificacionAsistentes"
                                    class="clearAllItems"><span class="fw-medium">Quitar todas</span></a></label>
                            <div class="select2-primary">
                                <select id="selectClasificacionAsistentes" name="clasificacionAsistentes[]"
                                    class="select2 form-select" multiple>
                                    @foreach ($clasificacionesAsistentes as $ca)
                                        <option value="{{ $ca->id }}"
                                            {{ in_array($ca->id, old('clasificacionAsistentes', $clasificacionAsistentesReunion)) ? 'selected' : '' }}>
                                            {{ $ca->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- /selectClasificacionAsistentes -->
                    </div>
                </div>
            </div>
        </div>
        <!-- Información principal  -->

        <!-- Configuración de reservas -->
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header text-black fw-semibold">
                    <img src="{{ Storage::url('generales/img/reuniones/icono_seccion_configuracion_reservas.png') }}"
                        alt="icono" class="me-2" width="30">
                    Configuración de reservas
                </h5>
                <div class="card-body">
                    <div class="row">
                        <!-- Fila con los elementos en una sola línea -->
                        <div class="row align-items-center">
                            <!-- ¿Habilitar reserva? -->
                            <div class="col-md-12 mb-3">
                                <div class="small fw-medium mb-3">¿Habilitar reserva?</div>
                                <label class="switch switch-lg">
                                    <input type="checkbox" @checked(old('habilitarReserva', $reunion->habilitar_reserva)) class="switch-input" id="toggleReserva" name="habilitarReserva" />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on">Si</span>
                                        <span class="switch-off">No</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Contenedor de los campos dependientes -->
                        <div id="reservaConfig" class="{{ old('habilitarReserva') ? '' : 'd-none' }}">

                            <div class="row align-items-center mt-4">
                                <!-- Días de plazo para reservar -->
                                <div class="col-12 col-md-4 mb-3">
                                    <label for="dias_plazo" class="form-label">Días de plazo para reservar {{ $reunion->dias_plazo_reserva }}</label>
                                    <input type="number" class="form-control" id="dias_plazo" name="díasPlazoReserva"  value="{{ old('díasPlazoReserva', $reunion->dias_plazo_reserva) }}" placeholder="" />
                                    @if ($errors->has('díasPlazoReserva'))
                                        <div class="text-danger form-label">{{ $errors->first('díasPlazoReserva') }}</div>
                                    @endif
                                </div>

                                <!-- Aforo -->
                                <div class="col-12 col-md-4 mb-3">
                                    <label for="aforo" class="form-label">Aforo</label>
                                    <input type="number" class="form-control" id="aforo" name="aforo"
                                        value="{{ old('aforo', $reunion->aforo) }}" placeholder="" />
                                    @if ($errors->has('aforo'))
                                        <div class="text-danger form-label">{{ $errors->first('aforo') }}</div>
                                    @endif
                                </div>

                                <!-- ¿Solo los que reservaron pueden asistir? -->
                                <div class="col-12 col-md-4 mb-3">
                                    <div class="small fw-medium mb-3">¿Solo los que reservaron pueden asistir?</div>
                                    <label class="switch switch-lg">
                                        <input type="checkbox" class="switch-input" name="soloReservaronAsistir"
                                            @checked(old('soloReservaronAsistir', $reunion->solo_reservados_pueden_asistir)) />
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">Si</span>
                                            <span class="switch-off">No</span>
                                        </span>
                                    </label>
                                </div>
                                <!-- /¿Solo los que reservaron pueden asistir? -->
                            </div>

                            <div class="row align-items-center my-4">
                                <!-- ¿Habilitar reserva a invitados? -->
                                <div class="col-12 col-md-4 mb-3">
                                    <div class="small fw-medium mb-4">¿Habilitar reserva a invitados?</div>
                                    <label class="switch switch-lg">
                                        <input type="checkbox" class="switch-input" id="toggleInvitados"
                                            name="habilitarReservaInvitados" @checked(old('habilitarReservaInvitados', $reunion->habilitar_reserva_invitados)) />
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">Si</span>
                                            <span class="switch-off">No</span>
                                        </span>
                                    </label>
                                </div>
                                <!-- /¿Habilitar reserva a invitados? -->

                                <!-- Cantidad máxima de invitados -->
                                <div class="col-12 col-md-4 mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="cantidad_invitados" class="form-label mb-0">Cantidad máxima de
                                            invitados</label>
                                    </div>
                                    <input type="number"
                                        value="{{ old('cantidadMáximaDeInvitados', $reunion->cantidad_maxima_reserva_invitados) }}"
                                        class="form-control" id="cantidad_invitados" name="cantidadMáximaDeInvitados"
                                        placeholder="" value="{{ old('cantidadMáximaDeInvitados') }}" />
                                    @if ($errors->has('cantidadMáximaDeInvitados'))
                                        <div class="text-danger form-label">
                                            {{ $errors->first('cantidadMáximaDeInvitados') }}</div>
                                    @endif
                                </div>
                                <!-- Cantidad máxima de invitados -->

                                <!-- ¿Habilitar reserva a familiares? -->
                                <div class="col-12 col-md-4 mb-3">
                                    <div class="small fw-medium mb-4">¿Habilitar reserva a familiares?</div>
                                    <label class="switch switch-lg">
                                        <input type="checkbox" class="switch-input" id="toggleReservaFamiliares"
                                            name="habilitarReservaFamiliares" @checked(old('habilitarReservaFamiliares', $reunion->habilitar_reserva_familiares)) />
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">Si</span>
                                            <span class="switch-off">No</span>
                                        </span>
                                    </label>
                                </div>
                                <!-- /¿Habilitar reserva a invitados? -->

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Configuración de reservas  -->

        <!-- Iglesia infantil -->
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header text-black fw-semibold">
                    <img src="{{ Storage::url('generales/img/reuniones/icono_seccion_iglesia_infantil.png') }}"
                        alt="icono" class="me-2" width="30">
                    Iglesia infantil
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="small fw-medium mb-4">¿Habilitar el preregistro a la iglesia infantil?</div>
                            <label class="switch switch-lg">
                                <input type="checkbox" class="switch-input" id="toggleInvitados"
                                    name="habilitarPreregistroInfantil" @checked(old('habilitarPreregistroInfantil', $reunion->habilitar_preregistro_iglesia_infantil)) />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">Si</span>
                                    <span class="switch-off">No</span>
                                </span>
                                <span class="switch-label"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Iglesia infantil  -->

        <!-- botonera -->
        <div class="d-flex mb-1 mt-5">
            <div class="me-auto">
                <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2">Guardar</button>
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
                <img src="{{ Storage::url('generales/img/otros/placeholder.jpg') }}" class="w-100" id="croppingImage" alt="cropper">
              </center>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer text-center">
        <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary rounded-pill crop me-sm-3 me-1" data-bs-dismiss="modal">Guardar</button>
          <button type="reset" class="btn rounded-pill btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ modal foto -->
@endsection
