@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
@vite([
'resources/js/app.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection
@section('page-script')
<script type="module">
  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d"
  });

  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });
  });
</script>

<script>
  const aforo = document.getElementById("aforo");
  const diasPlazo = document.getElementById("dias_plazo");
  const cantidadInvitados = document.getElementById("cantidad_invitados");
  const toggleReserva = document.getElementById("toggleReserva");
  const toggleInvitados = document.getElementById("toggleInvitados");
  const reservaConfig = document.getElementById("reservaConfig");
  const habilitarPreregistroIglesia = document.getElementById("habilitar_preregistro_infantil");

  function actualizarEstadoInvitados() {
    // Si la reserva está habilitada y los invitados también, habilita el campo de cantidad
    if (toggleReserva.checked && toggleInvitados.checked) {
      cantidadInvitados.disabled = false;
    } else {
      cantidadInvitados.disabled = true;
      cantidadInvitados.value = ''; // Limpia el campo si se desactiva
    }
  }

  function toggleRequiredFields() {
    const habilitarReserva = toggleReserva.checked;
    reservaConfig.classList.toggle("d-none", !habilitarReserva);
  }

  function toggleRequiredInvitados() {
    const habilitarInvitados = toggleInvitados.checked;

  }

  toggleReserva.addEventListener("change", toggleRequiredFields);
  toggleInvitados.addEventListener("change", toggleRequiredInvitados);

  if (toggleReserva && reservaConfig && toggleInvitados && cantidadInvitados) {
    // Manejar la visibilidad de la configuración de reserva
    toggleReserva.addEventListener("change", function() {
      reservaConfig.classList.toggle("d-none", !this.checked);
      actualizarEstadoInvitados();
    });

    // Manejar la habilitación del input de invitados
    toggleInvitados.addEventListener("change", actualizarEstadoInvitados);

    // Asegurar que el estado inicial sea el correcto
    actualizarEstadoInvitados();
    toggleRequiredInvitados();
    toggleRequiredFields();
  }

</script>

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
        aspectRatio: 1693 / 376,
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
    cropBtn.addEventListener('click', function (e) {
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
  $('#formulario').submit(function(){
    $('.btnGuardar').attr('disabled','disabled');

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

<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('reporteReunion.crear', $reunion) }}"  enctype="multipart/form-data">
  @csrf

    <!-- PORTADA -->
    <div class="col-md-12">
      <div class="card mb-4 rounded rounded-3">
        <img id="preview-foto" class="cropped-img card-img-top mb-2" src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/reuniones/'.$reunion->portada) }}" alt="Portada">
        <button type="button" style="background-color: rgba(255, 255, 255, 0.5);" class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2" data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;" class="ti ti-camera"></i></button>
        <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada" name="foto">

        <div class="row p-4 m-0 d-flex card-body">
          <h5 class="mb-1 fw-semibold text-black">Nuevo reporte de reunión</h5>
          <p class="mb-4 text-black">Crea un nuevo reporte para <b> {{$reunion->nombre}} </b> </p>
        </div>
      </div>
    </div>
    <!-- PORTADA -->

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
          Información principal
        </h5>
        <div class="card-body">
          <div class="row">

            <!-- fecha -->
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label" for="fecha">
                Fecha
              </label>
              <input id="fecha" value="{{ old('fecha') }}" placeholder="YYYY-MM-DD" name="fecha" class="fecha form-control fecha-picker" type="text" />
              @if($errors->has('fecha')) <div class="text-danger form-label">{{ $errors->first('fecha') }}</div> @endif
            </div>
            <!-- fecha -->

            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="html5-time-input" class="form-label">Conteo preliminar</label>
              <input class="form-control" name="conteoPreliminar" value="{{old('conteoPreliminar')}}" type="number" value="" id="html5-time-input" />
            </div>

            <!-- Hora -->
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="hora" class="form-label">Hora</label>
              <input class="form-control" name="hora" type="time" value="{{ old('hora',$reunion->hora) }}" id="hora" />
              @if ($errors->has('hora'))
                <div class="text-danger form-label">{{ $errors->first('hora') }}</div>
              @endif
            </div>
            <!-- /Hora -->

            <div class="col-md-12 col-sm-12 col-xs-12 mb-6">
              <label for="html5-time-input" class="form-label">Observaciones</label>
              <textarea id="" class="form-control" name="observaciones" type="text" value="">{{old('observaciones')}}</textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
          Configuración de reservas
        </h5>
        <div class="card-body">
          <div class="row align-items-center">
            <!-- ¿Habilitar reserva? -->
            <div class="col-md-12">
              <div class="small mb-3">¿Habilitar reserva?</div>
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
          <div id="reservaConfig" class="class=" {{ old('habilitarReserva') ? '' : 'd-none' }}">
            <div class="row align-items-center mt-4">
              <!-- Días de plazo para reservar -->
              <div class="col-12 col-md-4">
                <label for="dias_plazo" class="form-label">Días de plazo para reservar</label>
                <input type="number" class="form-control" id="dias_plazo" name="díasPlazoReserva" placeholder="" value="{{old('díasPlazoReserva', $reunion->dias_plazo_reserva)}}" />
                @if($errors->has('díasPlazoReserva'))
                <div class="text-danger">{{ $errors->first('díasPlazoReserva') }}</div>
                @endif
              </div>

              <!-- Aforo -->
              <div class="col-12 col-md-4">
                <label for="aforo" class="form-label">Aforo</label>
                <input type="number" class="form-control" id="aforo" name="aforo" placeholder="" value="{{old('aforo', $reunion->aforo)}}" />
                @if($errors->has('aforo'))
                <div class="text-danger">{{ $errors->first('aforo') }}</div>
                @endif
              </div>

              <!-- ¿Solo los que reservaron pueden asistir? -->
              <div class="col-12 col-md-4">
                <div class="small fw-medium mb-3">¿Solo los que reservaron pueden asistir?</div>
                <label class="switch switch-lg">
                  <input type="checkbox" @checked(old('soloReservaronAsistir', $reunion->solo_reservados_pueden_asistir)) id="solo_reserva_puede_asistir" class="switch-input" name="soloReservaronAsistir" />
                  <span class="switch-toggle-slider">
                    <span class="switch-on">Si</span>
                    <span class="switch-off">No</span>
                  </span>
                </label>
              </div>
            </div>

            <div class="row align-items-center my-4">
              <div class="col-12 col-md-4">
                <div class="small fw-medium mb-4">¿Habilitar reserva a invitados?</div>
                <label class="switch switch-lg">
                  <input type="checkbox" class="switch-input" id="toggleInvitados" name="habilitarReservaInvitados"
                    @checked(old('habilitarReservaInvitados', $reunion->habilitar_reserva_invitados)) />
                  <span class="switch-toggle-slider">
                    <span class="switch-on">Si</span>
                    <span class="switch-off">No</span>
                  </span>
                </label>
              </div>

              <!-- Cantidad máxima de invitados -->
              <div class="col-md-4 col-12">
                <div class="d-flex align-items-center mb-2">
                  <label for="cantidad_invitados" class="form-label mb-0">Cantidad máxima de invitados</label>
                </div>
                <input type="number" class="form-control" id="cantidad_invitados" value="{{old('cantidadInvitados', $reunion->cantidad_maxima_reserva_invitados)}}" name="cantidadInvitados" placeholder="" />
                @if($errors->has('cantidadInvitados'))
                <div class="text-danger">{{ $errors->first('cantidadInvitados') }}</div>
                @endif
              </div>

              <div class="col-12 col-md-4">
                <div class="small fw-medium mb-4">¿Habilitar reserva a familiares?</div>
                <label class="switch switch-lg">
                  <input type="checkbox" class="switch-input" id="toggleReservaFamiliares" name="habilitarReservaFamiliares"
                    @checked(old('habilitarReservaFamiliares', $reunion->habilitar_reserva_familiares)) />
                  <span class="switch-toggle-slider">
                    <span class="switch-on">Si</span>
                    <span class="switch-off">No</span>
                  </span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
          Iglesia infantil
        </h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-12">
            <div class="small mb-4">¿Habilitar el preregistro a la iglesia infantil?</div>
              <label class="switch switch-lg">
                <input type="checkbox" class="switch-input" id="habilitar_preregistro_infantil" name="habilitarPreregistroInfantil"
                  @checked(old('habilitarPreregistroInfantil', $reunion->habilitar_preregistro_iglesia_infantil)) />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
          Iglesia virtual
        </h5>
        <div class="card-body">

          <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12 mb-6">
              <label for="html5-time-input" class="form-label">Url (Ejemplo: https://redil.co)</label>
              <input class="form-control" name="url" type="text" value="{{old('url')}}" id="html5-time-input" />
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 mb-6">
              <label for="html5-time-input" class="form-label">IFRAME (Unicamente para transmisiones en vivo de youtube o
                facebook)</label>
              <input class="form-control" name="iframe" type="text" value="{{old('iframe')}}" id="html5-time-input" />
            </div>
          </div>

        </div>
      </div>
    </div>

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
            <button type="submit" class="btn rounded-pill  btn-primary crop me-sm-3 me-1" data-bs-dismiss="modal">Guardar</button>
            <button type="reset" class="btn rounded-pill  btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ modal foto -->

@endsection
