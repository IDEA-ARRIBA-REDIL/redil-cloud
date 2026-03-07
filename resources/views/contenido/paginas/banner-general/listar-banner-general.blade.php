@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Banner General')

@section('page-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
])
<style>
    .img-container {
        min-height: 300px;
        max-height: 80vh;
        background-color: #f7f7f7;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }
    .img-container img {
        display: block;
        max-width: 100%;
    }
    .daterangepicker {
        z-index: 1600 !important;
    }
</style>
@endsection

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@vite([
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('page-script')
<script>
    window.crearBanner = function() {
        document.getElementById('modalTitle').innerText = 'Nuevo banner';
        let form = document.getElementById('formBanner');
        form.action = "{{ route('banner-general.crearBanner') }}";
        form.reset();

        document.getElementById('banner_id').value = "";
        document.getElementById('link').value = "";
        document.getElementById('imagen_recortada').value = "";
        document.getElementById('imagen').value = "";

        document.getElementById('div_imagen_actual').classList.add('d-none');
        document.getElementById('preview_imagen_db').src = "";

        if (typeof $ !== 'undefined') {
            $('#fecha_visualizacion').val('');
            let drp = $('#fecha_visualizacion').data('daterangepicker');
            if(drp) { drp.setStartDate(moment()); drp.setEndDate(moment()); }
        } else {
            document.getElementById('fecha_visualizacion').value = '';
        }

        let methodInput = document.getElementById('methodPut');
        if(methodInput) methodInput.remove();

        // Limpiar errores visuales previos
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');

        var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBanner'));
        myModal.show();
    }

    window.editarBanner = function(id, nombre, fecha, visible, imagenUrl, link) {
        document.getElementById('modalTitle').innerText = 'Editar Banner';
        let form = document.getElementById('formBanner');
        form.action = "/banner-general/actualizar/" + id;

        let methodInput = document.getElementById('methodPut');
        if(!methodInput){
            methodInput = document.createElement('input');
            methodInput.setAttribute('type', 'hidden');
            methodInput.setAttribute('name', '_method');
            methodInput.setAttribute('value', 'PUT');
            methodInput.setAttribute('id', 'methodPut');
            form.appendChild(methodInput);
        }

        document.getElementById('banner_id').value = id;
        document.getElementById('nombre').value = nombre;
        document.getElementById('link').value = link;
        document.getElementById('visible').value = visible;

        document.getElementById('imagen').value = "";
        document.getElementById('imagen_recortada').value = "";

        if(imagenUrl && imagenUrl.length > 5 && !imagenUrl.includes('null')) {
            document.getElementById('preview_imagen_db').src = imagenUrl;
            document.getElementById('div_imagen_actual').classList.remove('d-none');
        } else {
            document.getElementById('div_imagen_actual').classList.add('d-none');
        }

        if (typeof $ !== 'undefined' && fecha && fecha.includes(' a ')) {
            $('#fecha_visualizacion').val(fecha);
            let picker = $('#fecha_visualizacion').data('daterangepicker');
            if (picker) {
                let partes = fecha.split(' a ');
                let inicio = moment(partes[0], 'YYYY-MM-DD');
                let fin = moment(partes[1], 'YYYY-MM-DD');
                if(inicio.isValid() && fin.isValid()){
                    picker.setStartDate(inicio);
                    picker.setEndDate(fin);
                    picker.calculateChosenLabel();
                }
            }
        } else {
             $('#fecha_visualizacion').val('');
        }

        // Limpiar errores visuales
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');

        var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBanner'));
        myModal.show();
    }

    window.confirmarEliminacion = function(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esto",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-primary me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // --- LOGICA DE REAPERTURA DE MODAL SI HAY ERRORES ---
        @if($errors->any())
            var bannerId = "{{ old('banner_id') }}";
            if (bannerId) {
                // Modo Edición: Restaurar estado de edición
                document.getElementById('modalTitle').innerText = 'Editar Banner';
                let form = document.getElementById('formBanner');
                form.action = "/banner-general/actualizar/" + bannerId;

                let methodInput = document.getElementById('methodPut');
                if(!methodInput){
                    methodInput = document.createElement('input');
                    methodInput.setAttribute('type', 'hidden');
                    methodInput.setAttribute('name', '_method');
                    methodInput.setAttribute('value', 'PUT');
                    methodInput.setAttribute('id', 'methodPut');
                    form.appendChild(methodInput);
                }
            } else {
                // Modo Creación: Restaurar estado de creación
                document.getElementById('modalTitle').innerText = 'Nuevo banner';
                let form = document.getElementById('formBanner');
                form.action = "{{ route('banner-general.crearBanner') }}";
            }
            var myModal = new bootstrap.Modal(document.getElementById('modalBanner'));
            myModal.show();
        @endif

        // --- Configuración del DateRangePicker ---
        try {
            const fechaInput = $('#fecha_visualizacion');
            if (fechaInput.length) {
                fechaInput.daterangepicker({
                    autoUpdateInput: false,
                    dropdownParent: $('#modalBanner'),
                    ranges: {
                        'Hoy': [moment(), moment()],
                        'Mañana': [moment().add(1, 'days'), moment().add(1, 'days')],
                        'Próximos 7 Días': [moment(), moment().add(6, 'days')],
                        'Próximos 30 Días': [moment(), moment().add(29, 'days')],
                        'Este Mes': [moment().startOf('month'), moment().endOf('month')],
                        'Próximo Mes': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')]
                    },
                    locale: {
                        format: 'YYYY-MM-DD',
                        separator: ' a ',
                        applyLabel: 'Aplicar',
                        cancelLabel: 'Borrar',
                        fromLabel: 'Desde',
                        toLabel: 'Hasta',
                        customRangeLabel: 'Personalizado',
                        weekLabel: 'S',
                        daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                        firstDay: 1
                    },
                    opens: 'center'
                });

                fechaInput.on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' a ' + picker.endDate.format('YYYY-MM-DD'));
                });

                fechaInput.on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                });
            }
        } catch (error) {
            console.error('Error inicializando DateRangePicker:', error);
        }

        // --- Lógica del Buscador ---
        const buscarInput = document.getElementById('buscar');
        const formularioBuscar = document.getElementById('formBuscar');
        let timeoutId;

        if(buscarInput){
            buscarInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                if (this.value.length >= 3 || this.value.length == 0) {
                    timeoutId = setTimeout(() => {
                        formularioBuscar.submit();
                    }, 1000);
                }
            });
        }

        // --- Lógica del Cropper ---
        var croppingImage = document.getElementById('croppingImage');
        var cropBtn = document.querySelector('.crop-btn');
        var upload = document.getElementById('imagen');
        var modalRecorteEl = document.getElementById('modalRecorte');
        var inputResultado = document.getElementById('imagen_recortada');
        var cropper = null;

        upload.addEventListener('change', function(e) {
            if (e.target.files.length) {
                var file = e.target.files[0];
                var fileType = file.type;

                if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        croppingImage.src = e.target.result;
                        if(cropper) {
                            cropper.destroy();
                            cropper = null;
                        }
                        var modalRecorte = bootstrap.Modal.getOrCreateInstance(modalRecorteEl);
                        modalRecorte.show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    Swal.fire('Error', 'Formato de archivo no soportado', 'error');
                }
            }
        });

        modalRecorteEl.addEventListener('shown.bs.modal', function () {
            cropper = new Cropper(croppingImage, {
                zoomable: false,
                viewMode: 1,
                aspectRatio: 1200 / 800,
                autoCropArea: 1,
                responsive: true,
                restore: false,
                checkCrossOrigin: false,
            });
        });

        modalRecorteEl.addEventListener('hidden.bs.modal', function () {
            if(cropper){
                cropper.destroy();
                cropper = null;
            }
            if(inputResultado.value === ""){
                upload.value = "";
            }
        });

        cropBtn.addEventListener('click', function() {
            if(!cropper) return;
            var canvas = cropper.getCroppedCanvas({
                width: 1200,
                height: 800,
                fillColor: '#fff',
            });
            var imgSrc = canvas.toDataURL('image/jpeg');
            inputResultado.value = imgSrc;

            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Imagen recortada',
                showConfirmButton: false,
                timer: 1500
            });

            var modalRecorte = bootstrap.Modal.getInstance(modalRecorteEl);
            modalRecorte.hide();
        });

    });
</script>
@endsection

@section('content')
<h4 class="mb-1 fw-semibold text-primary">Banner general</h4>
<p class="text-black">Gestiona las imágenes visibles en la aplicación.</p>

<form id="formBuscar" class="forms-sample" method="GET" action="{{ route('banner-general.listarBanners') }}">
    <div class="row mt-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="input-group input-group-merge bg-white">
                <input id="buscar" name="buscar" type="text" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar por nombre..." aria-describedby="btnBusqueda">
                <span class="input-group-text"><i class="ti ti-search"></i></span>
            </div>
        </div>
        <div class="col-12 col-md-6 d-flex justify-content-end mt-3 mt-md-0">
            <button type="button" class="btn btn-primary waves-effect waves-light rounded-pill" onclick="crearBanner()">
                <i class="ti ti-plus me-1"></i> Nuevo banner
            </button>
        </div>
    </div>
</form>

<div class="row equal-height-row g-4 mt-10">
    @if($banners->count() > 0)
        @foreach($banners as $banner)
        <div class="col-12 col-sm-6 col-lg-4 col-xl-4 equal-height-col">
            <div class="card h-100 border rounded shadow-sm">
                <div class="position-relative">
                    <img class="card-img-top object-fit-cover" style="height: 180px; width: 100%;"
                         src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/banners/' . $banner->imagen) }}"
                         alt="{{ $banner->nombre }}" />

                    <span class="badge position-absolute top-0 end-0 m-2 {{ $banner->visible ? 'bg-label-success' : 'bg-label-secondary' }}">
                        {{ $banner->visible ? 'Visible' : 'Oculto' }}
                    </span>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0 text-truncate" title="{{ $banner->nombre }}">
                            {{ $banner->nombre ?? 'Sin nombre' }}
                        </h5>
                        <div class="dropdown zindex-2">
                            <button type="button" class="btn dropdown-toggle hide-arrow btn-sm p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical fs-4"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                     <a class="dropdown-item" href="javascript:void(0);"
                                        onclick="editarBanner(
                                            '{{$banner->id}}',
                                            '{{$banner->nombre}}',
                                            '{{ ($banner->fecha_inicio && $banner->fecha_fin) ? $banner->fecha_inicio->format('Y-m-d') . ' a ' . $banner->fecha_fin->format('Y-m-d') : '' }}',
                                            '{{$banner->visible}}',
                                            '{{ Storage::url($configuracion->ruta_almacenamiento . '/img/banners/' . $banner->imagen) }}'
                                        )">
                                        <i class="ti ti-pencil me-1"></i> Editar
                                    </a>
                                </li>
                                <li>
                                     <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmarEliminacion('{{$banner->id}}')">
                                        <i class="ti ti-trash me-1"></i> Eliminar
                                    </a>
                                    <form id="delete-form-{{ $banner->id }}" action="{{ route('banner-general.eliminarBanner', $banner->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center text-black">
                          <i class="ti ti-calendar me-2"></i>
                          <small>
                              {{ $banner->fecha_inicio ? $banner->fecha_inicio->format('Y-m-d') : 'No indicado' }}
                              al
                              {{ $banner->fecha_fin ? $banner->fecha_fin->format('Y-m-d') : 'No indicado' }}
                          </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="col-12 mt-5 text-center">
            <i class="ti ti-photo-off ti-xl text-muted mb-2"></i>
            <p class="text-muted">No se encontraron banners.</p>
        </div>
    @endif
</div>

<div class="row my-4">
    <div class="col-12">
        @if($banners->hasPages())
            {{ $banners->appends(request()->input())->links() }}
        @endif
    </div>
</div>

<div class="modal fade" id="modalBanner" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modalTitle" class="modal-title fw-bold text-primary">Nuevo banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBanner" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="banner_id" name="banner_id" value="{{ old('banner_id') }}">

                    {{-- 1. NOMBRE --}}
                    <div class="mb-3">
                        <label class="form-label" for="nombre">Descripción</label>
                        {{-- Agregamos value="{{ old() }}" y clase condicional is-invalid --}}
                        <input type="text" id="nombre" name="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre') }}"
                               placeholder="Promoción Agosto" />
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 1.1 LINK --}}
                    <div class="mb-3">
                        <label class="form-label" for="link">Enlace (Link)</label>
                        <input type="text" id="link" name="link"
                               class="form-control @error('link') is-invalid @enderror"
                               value="{{ old('link') }}"
                               placeholder="https://example.com" />
                        @error('link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 2. FECHA --}}
                    <div class="mb-3">
                      <label class="form-label" for="fecha_visualizacion">Rango de visualización </label>
                      <input type="text" id="fecha_visualizacion" name="fecha_visualizacion"
                        class="form-control @error('fecha_visualizacion') is-invalid @enderror"
                        value="{{ old('fecha_visualizacion') }}"
                        placeholder="Seleccione fechas" autocomplete="off" />
                        @error('fecha_visualizacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 3. ESTADO --}}
                    <div class="mb-3">
                      <label class="form-label" for="visible">Estado </label>
                      <select id="visible" name="visible" class="form-select @error('visible') is-invalid @enderror">
                          <option value="1" {{ old('visible') == '1' ? 'selected' : '' }}>Visible</option>
                          <option value="0" {{ old('visible') == '0' ? 'selected' : '' }}>Oculto</option>
                      </select>
                      @error('visible')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>

                    {{-- 4. IMAGEN --}}
                    <div class="mb-4">
                        <label class="form-label" for="imagen">Imagen del banner </label>

                        <div id="div_imagen_actual" class="mb-2 d-none text-center p-2 bg-light rounded">
                            <small class="d-block text-muted mb-1">Imagen actual:</small>
                            <img id="preview_imagen_db" src="" style="max-height: 150px; max-width: 100%; border-radius: 6px;">
                        </div>

                        <input class="form-control @error('imagen') is-invalid @enderror" type="file" id="imagen" name="imagen" accept="image/*">
                        <div class="form-text">La imagen se recortará a 1200x800 px.</div>
                        <input type="hidden" id="imagen_recortada" name="imagen_recortada" value="{{ old('imagen_recortada') }}">

                        @error('imagen')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRecorte" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header pb-4">
        <h5 class="modal-title">Recortar imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
         <div class="img-container">
            <img src="" id="croppingImage" alt="Imagen para recortar">
        </div>
      </div>
      <div class="modal-footer pt-5">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-outline-primary crop-btn rounded-pill">Recortar</button>
      </div>
    </div>
  </div>
</div>

@endsection
