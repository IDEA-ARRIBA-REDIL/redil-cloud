@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
])
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@section('page-script')
<script type="module">
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron = /[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }

  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d"
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

    // Lógica de Cropper para el Logo Blanco
    var croppingLogo = document.querySelector('#croppingLogo'),
        cropLogoBtn = document.querySelector('.cropLogo'),
        croppedLogoImg = document.querySelector('#preview-logo'),
        uploadLogo = document.querySelector('#cropperLogoUpload'),
        inputLogoResultado = document.querySelector('#logo-recortado'),
        cropperLogo = '';

    setTimeout(() => {
        if (croppingLogo) {
            cropperLogo = new Cropper(croppingLogo, {
                zoomable: true,
                aspectRatio: 300 / 150,
                cropBoxResizable: true,
                viewMode: 1
            });
        }
    }, 1000);

    if (uploadLogo) {
        uploadLogo.addEventListener('change', function(e) {
            if (e.target.files.length) {
                var fileType = e.target.files[0].type;
                if (fileType.includes('image/')) {
                    if (cropperLogo && typeof cropperLogo.destroy === 'function') {
                        cropperLogo.destroy();
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (e.target.result) {
                            croppingLogo.src = e.target.result;
                            cropperLogo = new Cropper(croppingLogo, {
                                zoomable: true,
                                aspectRatio: 300 / 150,
                                cropBoxResizable: true,
                                viewMode: 1
                            });
                        }
                    };
                    reader.readAsDataURL(e.target.files[0]);
                } else {
                    alert('El tipo de archivo seleccionado no es compatible.');
                }
            }
        });
    }

    if (cropLogoBtn) {
        cropLogoBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (cropperLogo) {
                let imgSrc = cropperLogo.getCroppedCanvas({
                    width: 300,
                    height: 150
                }).toDataURL('image/png');
                croppedLogoImg.src = imgSrc;
                inputLogoResultado.value = imgSrc;
            }
        });
    }

    // Lógica de Cropper para el Logo Negro
    var croppingLogoNegro = document.querySelector('#croppingLogoNegro'),
        cropLogoNegroBtn = document.querySelector('.cropLogoNegro'),
        croppedLogoNegroImg = document.querySelector('#preview-logo-negro'),
        uploadLogoNegro = document.querySelector('#cropperLogoNegroUpload'),
        inputLogoNegroResultado = document.querySelector('#logo-negro-recortado'),
        cropperLogoNegro = '';

    setTimeout(() => {
        if (croppingLogoNegro) {
            cropperLogoNegro = new Cropper(croppingLogoNegro, {
                zoomable: true,
                aspectRatio: 300 / 150,
                cropBoxResizable: true,
                viewMode: 1
            });
        }
    }, 1000);

    if (uploadLogoNegro) {
        uploadLogoNegro.addEventListener('change', function(e) {
            if (e.target.files.length) {
                var fileType = e.target.files[0].type;
                if (fileType.includes('image/')) {
                    if (cropperLogoNegro && typeof cropperLogoNegro.destroy === 'function') {
                        cropperLogoNegro.destroy();
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (e.target.result) {
                            croppingLogoNegro.src = e.target.result;
                            cropperLogoNegro = new Cropper(croppingLogoNegro, {
                                zoomable: true,
                                aspectRatio: 300 / 150,
                                cropBoxResizable: true,
                                viewMode: 1
                            });
                        }
                    };
                    reader.readAsDataURL(e.target.files[0]);
                } else {
                    alert('El tipo de archivo seleccionado no es compatible.');
                }
            }
        });
    }

    if (cropLogoNegroBtn) {
        cropLogoNegroBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (cropperLogoNegro) {
                let imgSrc = cropperLogoNegro.getCroppedCanvas({
                    width: 300,
                    height: 150
                }).toDataURL('image/png');
                croppedLogoNegroImg.src = imgSrc;
                inputLogoNegroResultado.value = imgSrc;
            }
        });
    }

    const formConfig = document.getElementById('formulario');
    if (formConfig) {
        formConfig.addEventListener('submit', function() {
            const btnGuardar = document.querySelector('.btnGuardar');
            if (btnGuardar) {
                btnGuardar.setAttribute('disabled', 'disabled');
            }

            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espera mientras se actualiza la información de la iglesia.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    }
  });
</script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Gestiona tu iglesia</h4>
<p class="mb-4 text-black">Aquí podrás configurar y actualizar la información de tu congregación.</p>

@include('layouts.status-msn')

<form id="formulario" role="form" class="
    forms-sample" method="POST" action="{{ route('iglesia.update', $iglesia) }}" enctype="multipart/form-data">
  @csrf
  @method('PATCH')

  <div class="card p-4 w-100"> 
    <div class="card-header px-0 py-1">
      <h5 class="fw-semibold" >Información básica</h5>
    </div>
    <div class="row">
      <div class="col-4 mb-3">
        <label class="form-label">Nombre</label>
        <input required type="text" value="{{ $iglesia->nombre }}" id="" name="nombre" class="form-control">
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Fecha de creación de la iglesia</label>
        <input type="text" value="{{ $iglesia->fecha_apertura }}" name="fechaApertura" placeholder="YYYY-MM-DD"
          class="fecha form-control fecha-picker">
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Fecha de suscripción de la iglesia</label>
        <input type="text" value="{{ $iglesia->fecha_suscripcion }}" name="fechaSuscripcion" placeholder="YYYY-MM-DD"
          class="fecha form-control fecha-picker">
      </div>

      <!-- Segunda fila -->
      <div class="col-4 mb-3">
        <label class="form-label">Cantidad estimada de membresía</label>
        <input type="number" value="{{ $iglesia->membresia_estimada }}" name="cantidadMembresia" class="form-control">
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Teléfono fijo</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
          <input type="text" value="{{ $iglesia->telefono1 }}" name="telefonoFijo" class="form-control">
        </div>
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Otro teléfono</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="ti ti-phone"></i></span>
          <input type="text" value="{{ $iglesia->telefono2 }}" name="otroTelefono" class="form-control">
        </div>
      </div>

      <!-- Fila de Logos -->
      <div class="col-md-6 col-12 mb-3 mt-3">
        <label class="form-label">Logo de la Iglesia (300 x 150 px)</label>
        <div class="d-flex align-items-center gap-3">
          <div class="position-relative d-inline-block">
            <img id="preview-logo"
                 src="{{ $iglesia->logo ? tenant_asset('img/iglesia/'.$iglesia->logo) : asset('assets/img/illustrations/page-pricing-enterprise.png') }}"
                 alt="Logo actual"
                 style="max-width: 150px; max-height: 75px; object-fit: contain; background: #2d2d2d; padding: 4px; border-radius: 4px;" class="rounded border shadow-sm">
            <button type="button"
                    class="btn btn-sm btn-icon btn-primary rounded-circle position-absolute bottom-0 end-0 mb-n1 me-n1 shadow"
                    data-bs-toggle="modal" data-bs-target="#modalLogo">
              <i class="ti ti-camera"></i>
            </button>
          </div>
          @if($iglesia->logo)
            <span class="badge bg-label-secondary">{{ basename($iglesia->logo) }}</span>
          @endif
        </div>
        <input type="hidden" id="logo-recortado" name="logo_base64">
      </div>

      <div class="col-md-6 col-12 mb-3 mt-3">
        <label class="form-label">Logo Negro (Fondo Claro) (300 x 150 px)</label>
        <div class="d-flex align-items-center gap-3">
          <div class="position-relative d-inline-block">
            <img id="preview-logo-negro"
                 src="{{ $iglesia->logo_negro ? tenant_asset('img/iglesia/'.$iglesia->logo_negro) : asset('assets/img/illustrations/page-pricing-enterprise.png') }}"
                 alt="Logo negro actual"
                 style="max-width: 150px; max-height: 75px; object-fit: contain; background: #f5f5f5; padding: 4px; border-radius: 4px;" class="rounded border shadow-sm">
            <button type="button"
                    class="btn btn-sm btn-icon btn-primary rounded-circle position-absolute bottom-0 end-0 mb-n1 me-n1 shadow"
                    data-bs-toggle="modal" data-bs-target="#modalLogoNegro">
              <i class="ti ti-camera"></i>
            </button>
          </div>
          @if($iglesia->logo_negro)
            <span class="badge bg-label-secondary">{{ basename($iglesia->logo_negro) }}</span>
          @endif
        </div>
        <input type="hidden" id="logo-negro-recortado" name="logo_negro_base64">
      </div>
    </div>
  </div>


  <!-- Segunda Card -->
  <div class="card p-4 w-100 mt-4">
    <div class="card-header px-0 py-1">
      <h5 class="fw-semibold">Ubicación</h5>
    </div>
    <div class="row">
      <!-- Primera fila -->
      <div class="col-4 mb-3">
        <label class="form-label">Continente</label>
        <select id="continente" name="continente" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($continentes as $continente)
          <option @if ($continente->id == $iglesia->continente_id) selected @endif value="{{$continente->id}}">
            {{$continente->nombre}}
          </option>
          @endforeach
        </select>
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">País</label>
        <select id="pais" name="pais" class="grupoSelect select2 selectorGenero form-select" data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($paises as $pais)
          <option @if ($pais->id == $iglesia->pais_id) selected @endif
            value={{ $pais->id }}>{{ $pais->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-4 mb-3">
        <label class="form-label">Región</label>
        <select id="region" name="region" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($regiones as $region)
          <option @if ($region->id == $iglesia->region_id) selected @endif
            value={{ $region->id }}>{{ $region->nombre }}</option>
          @endforeach
        </select>
      </div>

      <!-- Segunda fila -->
      <div class="col-4 mb-3">
        <label class="form-label">Departamento</label>
        <select id="departamento" name="departamento" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($departamentos as $departamento)
          <option @if ($departamento->id == $iglesia->departamento_id) selected @endif value={{ $departamento->id }}>{{ $departamento->nombre }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-4 mb-3">
        <label class="form-label">Ciudad</label>
        <select id="ciudad" name="ciudad" class="grupoSelect select2 selectorGenero form-select"
          data-allow-clear="true">
          <option value="0">Ninguno</option>
          @foreach ($ciudades as $ciudad)
          <option @if ($ciudad->id == $iglesia->municipio_id) selected @endif value={{ $ciudad->id }}>{{ $ciudad->nombre }}</option>
          @endforeach
        </select>
      </div>

    </div>
    <div class="mb-2 col-12 col-md-6">
      <label class="form-label">Dirección</label>
      <div class="input-group input-group-merge">
        <span class="input-group-text"><i class="ti ti-map"></i></span>
        <input onkeypress="return sinComillas(event)" id="direccion" name="direccion" value="{{ $iglesia->direccion }}"
          type="text" class="form-control" spellcheck="false" data-ms-editor="true"
          placeholder="Digita la dirección, la ciudad y el país, donde vives.">
      </div>
    </div>
  </div>

  <!-- Tercera Card (Redes sociales) -->
  <div class="card p-4 w-100 mt-4">
    <div class="card-header px-0 py-1">
      <h5 class="fw-semibold">Redes sociales</h5>
    </div>
    <div class="row">
      <div class="col-md-3 col-sm-6 col-12 mb-3">
        <label class="form-label">Instagram</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-brand-instagram"></i></span>
          <input type="url" value="{{ $iglesia->instagram }}" name="instagram" class="form-control" placeholder="https://instagram.com/miiglesia">
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12 mb-3">
        <label class="form-label">Facebook</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-brand-facebook"></i></span>
          <input type="url" value="{{ $iglesia->facebook }}" name="facebook" class="form-control" placeholder="https://facebook.com/miiglesia">
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12 mb-3">
        <label class="form-label">YouTube</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-brand-youtube"></i></span>
          <input type="url" value="{{ $iglesia->youtube }}" name="youtube" class="form-control" placeholder="https://youtube.com/miiglesia">
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12 mb-3">
        <label class="form-label">TikTok</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-brand-tiktok"></i></span>
          <input type="url" value="{{ $iglesia->tiktok }}" name="tiktok" class="form-control" placeholder="https://tiktok.com/@miiglesia">
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btn-primary rounded-pill me-1 btnGuardar">Guardar</button>
    </div>
  </div>

</form>

<!-- Modal Logo -->
<div class="modal fade modal-img" id="modalLogo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-simple">
        <div class="modal-content">
            <div class="modal-body p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4 p-4">
                    <h3 class="mb-2"><i class="ti ti-camera ti-lg"></i> Subir logo</h3>
                    <p class="text-black">Selecciona y recorta el logo para la iglesia (300x150 px)</p>
                </div>

                <div class="row px-4">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Paso #1 Selecciona el logo</label>
                            <input class="form-control" type="file" id="cropperLogoUpload" accept="image/*">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Paso #2 Recorta el logo</label>
                            <center style="background: #2d2d2d; padding: 10px; border-radius: 4px;">
                                <img src="{{ Storage::disk('global_media')->url('placeholder.jpg') }}" class="w-100"
                                    id="croppingLogo" alt="cropper" style="max-height: 300px; object-fit: contain;">
                            </center>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <div class="col-12 text-center">
                    <button type="button" class="btn btn-outline-secondary px-5 rounded-pill" data-bs-dismiss="modal"
                        aria-label="Close">Cerrar</button>
                    <button type="button" class="btn btn-primary rounded-pill cropLogo me-sm-3 me-1 px-5"
                        data-bs-dismiss="modal">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Logo Negro -->
<div class="modal fade modal-img" id="modalLogoNegro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-simple">
        <div class="modal-content">
            <div class="modal-body p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4 p-4">
                    <h3 class="mb-2"><i class="ti ti-camera ti-lg"></i> Subir logo negro</h3>
                    <p class="text-black">Selecciona y recorta el logo negro para la iglesia (300x150 px)</p>
                </div>

                <div class="row px-4">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Paso #1 Selecciona el logo</label>
                            <input class="form-control" type="file" id="cropperLogoNegroUpload" accept="image/*">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Paso #2 Recorta el logo</label>
                            <center style="background: #f5f5f5; padding: 10px; border-radius: 4px;">
                                <img src="{{ Storage::disk('global_media')->url('placeholder.jpg') }}" class="w-100"
                                    id="croppingLogoNegro" alt="cropper" style="max-height: 300px; object-fit: contain;">
                            </center>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <div class="col-12 text-center">
                    <button type="button" class="btn btn-outline-secondary px-5 rounded-pill" data-bs-dismiss="modal"
                        aria-label="Close">Cerrar</button>
                    <button type="button" class="btn btn-primary rounded-pill cropLogoNegro me-sm-3 me-1 px-5"
                        data-bs-dismiss="modal">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
@endsection
