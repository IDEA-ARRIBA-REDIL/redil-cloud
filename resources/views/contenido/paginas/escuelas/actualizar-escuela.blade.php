@section('isEscuelasModule', true)

@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Actualizar Escuela')

{{-- HEAD - Estilos y Scripts de Vendor (Sin cambios) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
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
@endsection

@section('content')

    @include('layouts.status-msn') {{-- Muestra mensajes flash temporales --}}

    <form id="formActualizarEscuela" method="POST" action="{{ route('escuelas.update', $escuela->id) }}">
        <div class="row">

            @csrf

            {{-- Portada --}}
            <div class="col-md-12">
                <div class="card mb-4 rounded rounded-3">
                    <img id="preview-foto" class="cropped-img card-img-top mb-2"
                        src="{{ $escuela->portada && $escuela->portada !== 'default.png' ? Storage::url($configuracion->ruta_almacenamiento . '/img/escuelas/' . $escuela->portada) : asset('assets/img/pages/profile-banner.png') }}"
                        alt="Portada {{ $escuela->nombre }}">
                    <button type="button" style="background-color: rgba(255, 255, 255, 0.5);"
                        class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2"
                        data-bs-toggle="modal" data-bs-target="#modalFoto">Cambiar portada <i style="padding-left: 5px;"
                            class="ti ti-camera"></i></button>
                    <input class="form-control d-none" type="text" value="{{ old('foto') }}" id="imagen-recortada"
                        name="foto">

                    <div class="row p-4 m-0 d-flex card-body">
                        <h5 class="mb-1 fw-semibold text-black">Actualizar escuela: {{ $escuela->nombre }}</h5>
                        <p class="mb-4 text-black">Aquí podrás actualizar la información de tu escuela.</p>
                    </div>
                </div>
            </div>

            {{-- Datos Generales Escuela --}}
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-1 fw-semibold text-black">Datos generales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- ... campos nombre, tipo, descripción ... --}}
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                    id="nombre" name="nombre" value="{{ old('nombre', $escuela->nombre) }}" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo_matricula" class="form-label">Tipo matrícula</label>
                                <select class="form-select @error('tipo_matricula') is-invalid @enderror"
                                    id="tipo_matricula" name="tipo_matricula" required>
                                    <option value="materias_independientes"
                                        {{ old('tipo_matricula', $escuela->tipo_matricula) == 'materias_independientes' ? 'selected' : '' }}>
                                        Materias independientes</option>
                                    <option value="niveles_agrupados"
                                        {{ old('tipo_matricula', $escuela->tipo_matricula) == 'niveles_agrupados' ? 'selected' : '' }}>
                                        Niveles agrupados</option>
                                </select>
                                @error('tipo_matricula')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="habilitada_consilidacion" name="habilitada_consilidacion" value="1" {{ old('habilitada_consilidacion', $escuela->habilitada_consilidacion) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-black" for="habilitada_consilidacion">Habilitar Consolidación</label>
                                </div>
                                <small class="form-text text-muted">Indica si esta escuela tendrá habilitada la opción de consolidación para informes.</small>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                                    rows="3">{{ old('descripcion', $escuela->descripcion) }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        <div class="col-12 mt-4">
                            <a href="{{ route('escuelas.gestionarEscuelas') }}"
                                class="btn rounded-pill btn-outline-secondary">Volver</a>
                            <button type="submit" class="btn rounded-pill btn-primary me-2">Guardar cambios</button>

                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- SECCIÓN PARA GESTIONAR CORTES DE LA ESCUELA --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-1 fw-semibold text-black">Cortes definidos para la escuela</h5>
                        {{-- Botón Añadir Corte (opcional) --}}
                    </div>
                    <div class="card-body">

                        {{-- ** INICIO: Alerta Persistente de Porcentaje ** --}}
                        @if (isset($sumaPorcentajesActual) && $sumaPorcentajesActual != 100)
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <span class="alert-icon text-warning me-2">
                                    <i class="ti ti-alert-triangle ti-xs"></i>
                                </span>
                                <div>
                                    <strong>¡Atención!</strong> La suma actual de los porcentajes de los cortes es
                                    <strong>{{ $sumaPorcentajesActual }}%</strong>.
                                    @if ($sumaPorcentajesActual < 100)
                                        Falta un <strong>{{ 100 - $sumaPorcentajesActual }}%</strong> para alcanzar el
                                        100%.
                                    @else
                                        {{-- $sumaPorcentajesActual > 100 --}}
                                        Supera el 100% por un <strong>{{ $sumaPorcentajesActual - 100 }}%</strong>.
                                    @endif
                                    Por favor, ajusta los porcentajes para que sumen exactamente 100%.
                                </div>
                            </div>
                        @endif
                        {{-- ** FIN: Alerta Persistente de Porcentaje ** --}}


                        <div class="row">
                            @forelse ($escuela->cortesEscuela->sortBy('orden') as $corte)
                                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4">
                                    <div class="card rounded position-relative h-100 shadow">
                                        {{-- Menú Opciones --}}
                                        <div class="position-absolute top-0 end-0 mt-5 me-2 z-1">
                                            {{-- ... dropdown ... --}}
                                            <div class="dropdown zindex-2"> {{-- Eliminado zindex, border, p-1, ajustado botón --}}
                                                <button  style="border-radius: 20px;" class="btn p-1 border" type="button" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <button class="dropdown-item btn-editar-corte" type="button"
                                                            data-bs-toggle="offcanvas"
                                                            data-bs-target="#offcanvasEditarCorte"
                                                            data-corte-id="{{ $corte->id }}"
                                                            data-corte-nombre="{{ $corte->nombre }}"
                                                            data-corte-orden="{{ $corte->orden }}"
                                                            data-corte-porcentaje="{{ $corte->porcentaje }}"
                                                            data-action-url="{{ route('cortes_escuela.update', $corte->id) }}">
                                                            Editar
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item btn-eliminar-corte" type="button"
                                                            data-corte-id="{{ $corte->id }}"
                                                            data-corte-nombre="{{ $corte->nombre }}"
                                                            data-action-url="{{ route('cortes_escuela.destroy', ['corte' => $corte->id]) }}">
                                                            Eliminar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        {{-- Contenido Tarjeta --}}
                                        <div class="card-body text-center d-flex flex-column justify-content-center">

                                            <h5 class="card-title text-start fw-semibold">{{ $corte->nombre }}</h5>
                                            <div class="d-flex flex-row justify-content-between mb-2">
                                                <div class="d-flex flex-row">
                                                    <i class="ti ti-sort-ascending-small-big"></i>
                                                    <div class="d-flex flex-column text-star">
                                                        <small class="text-black ms-1"> Orden: </small>
                                                        <small class="fw-semibold ms-1 text-black ">
                                                            {{ $corte->orden }}</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-row justify-content-between mb-2">
                                                    <div class="d-flex flex-row">
                                                        <i class="ti ti-circle-dashed-percentage"></i>
                                                        <div class="d-flex flex-column text-star">
                                                            <small class="text-black ms-1"> Porcentaje: </small>
                                                            <small class="fw-semibold ms-1 text-black ">
                                                                {{ $corte->porcentaje ?? 'N/A' }}%</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-secondary text-center" role="alert">
                                        {{-- Cambiado a alert-secondary --}}
                                        <i class="ti ti-info-circle me-2"></i> No hay cortes definidos para
                                        esta
                                        escuela
                                        todavía.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </form>

    {{-- Modal Foto (Sin cambios) --}}
    <div class="modal fade modal-img" id="modalFoto" tabindex="-1" aria-hidden="true">
        {{-- ... contenido del modal ... --}}
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content p-3 p-md-5">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-camera ti-lg me-1"></i> Cambiar Portada</h3>
                        <p class="text-muted">Selecciona y recorta la nueva imagen de portada.</p>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Paso 1: Selecciona la foto</label>
                            <input class="form-control" type="file" id="cropperImageUpload"
                                accept="image/png, image/jpeg, image/gif">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Paso 2: Recorta la foto (Ratio 1693:376)</label>
                            <div style="max-height: 400px; overflow: hidden;">
                                <img src="{{ asset('assets/img/pages/profile-banner.png') }}" class="w-100"
                                    id="croppingImage" alt="Recortador de imagen">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer pt-4">
                    <button type="button" class="btn rounded-pill btn-label-secondary" data-bs-dismiss="modal"
                        aria-label="Close">Cancelar</button>
                    <button type="button" class="btn rounded-pill btn-primary crop" data-bs-dismiss="modal">Guardar
                        Portada</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Offcanvas Editar Corte (Sin cambios en HTML) --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditarCorte"
        aria-labelledby="offcanvasEditarCorteLabel">
        {{-- ... contenido del offcanvas ... --}}
        <div class="offcanvas-header">
            <h4 id="titel" class="offcanvas-title fw-bold text-primary">Editar corte</h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0">
            <form id="formEditarCorte" method="POST" action=""> {{-- Action se setea con JS --}}
                @csrf
                @method('PUT')
                <input type="hidden" name="corte_id" id="editCorteId">
                <div class="mb-3">
                    <label for="editCorteNombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="editCorteNombre" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label for="editCorteOrden" class="form-label">Orden</label>
                    <input type="number" class="form-control" id="editCorteOrden" name="orden" min="1"
                        required>
                </div>
                <div class="mb-3">
                    <label for="editCortePorcentaje" class="form-label">Porcentaje (%)</label>
                    <input type="number" class="form-control" id="editCortePorcentaje" name="porcentaje"
                        min="0" max="100" required>
                    <small class="form-text text-muted">La suma total debe ser 100.</small>
                </div>

        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <div class="mt-4 d-flex justify-content-start">
                <button type="submit" class="btn btn-primary rounded-pill me-sm-3 me-1">Guardar cambios</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill"
                    data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </div>
        </form>
    </div>
    </div>

    {{-- Formulario Eliminar Corte (Sin cambios) --}}
    <form id="formEliminarCorte" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- ** INICIO: @push('scripts') ACTUALIZADO ** --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // --- Lógica para Editar Corte (Offcanvas) ---
                const offcanvasEditarCorte = document.getElementById('offcanvasEditarCorte');
                const formEditarCorte = document.getElementById('formEditarCorte'); // Mover fuera para mejor acceso

                // Verificar si los elementos principales existen
                if (offcanvasEditarCorte && formEditarCorte) {
                    const inputId = document.getElementById('editCorteId');
                    const inputNombre = document.getElementById('editCorteNombre');
                    const inputOrden = document.getElementById('editCorteOrden');
                    const inputPorcentaje = document.getElementById('editCortePorcentaje');

                    // Asegurarse que los inputs existen antes de usarlos
                    if (!inputId || !inputNombre || !inputOrden || !inputPorcentaje) {
                        console.error(
                            "Error: No se encontraron todos los inputs dentro del formulario #formEditarCorte.");
                        return; // Detener si faltan inputs
                    }

                    // Escuchar clics en TODOS los botones de editar
                    document.querySelectorAll('.btn-editar-corte').forEach(button => {
                        button.addEventListener('click', function() {
                            // Obtener datos del botón clickeado
                            const corteId = this.dataset.corteId;
                            const corteNombre = this.dataset.corteNombre;
                            const corteOrden = this.dataset.corteOrden;
                            const cortePorcentaje = this.dataset.cortePorcentaje;
                            const actionUrl = this.dataset.actionUrl;

                            // --- Debugging Logs ---
                            console.log("Botón Editar clickeado para Corte ID:", corteId);
                            console.log("Action URL obtenida:", actionUrl);
                            console.log("Formulario encontrado:", formEditarCorte);
                            // --- End Debugging Logs ---

                            // Validar que la URL de acción existe
                            if (actionUrl) {
                                // Establecer el action del formulario
                                formEditarCorte.action = actionUrl;
                                console.log("Action del formulario establecido en:", formEditarCorte
                                    .action);

                                // Poblar los campos del formulario
                                inputId.value = corteId;
                                inputNombre.value = corteNombre;
                                inputOrden.value = corteOrden;
                                inputPorcentaje.value = cortePorcentaje;

                            } else {
                                console.error(
                                    "Error: El atributo 'data-action-url' está vacío o no definido en el botón."
                                );
                                // Opcional: Mostrar un error al usuario
                                Swal.fire('Error',
                                    'No se pudo determinar la URL para guardar los cambios.',
                                    'error');
                            }
                        });
                    });

                    // Limpiar formulario al cerrar el offcanvas
                    offcanvasEditarCorte.addEventListener('hidden.bs.offcanvas', function() {
                        console.log("Offcanvas cerrado, reseteando formulario.");
                        formEditarCorte.reset(); // Limpia todos los campos
                        formEditarCorte.action = ''; // Limpia la URL de acción
                        // No es necesario limpiar inputId explícitamente si se usa reset()
                    });

                } else {
                    // Log si no se encuentra el offcanvas o el formulario al cargar la página
                    if (!offcanvasEditarCorte) console.error(
                        "Error: No se encontró el elemento #offcanvasEditarCorte.");
                    if (!formEditarCorte) console.error("Error: No se encontró el elemento #formEditarCorte.");
                }


                // --- Lógica para Eliminar Corte (SweetAlert - Sin cambios) ---
                const formEliminarCorte = document.getElementById('formEliminarCorte');
                if (formEliminarCorte) { // Verificar que el form de eliminar existe
                    document.querySelectorAll('.btn-eliminar-corte').forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            const corteId = this.dataset.corteId;
                            const corteNombre = this.dataset.corteNombre;
                            const actionUrl = this.dataset.actionUrl;

                            // Debugging Log
                            console.log("Botón Eliminar clickeado para corte ID:", corteId, "URL:",
                                actionUrl);

                            if (!actionUrl) {
                                console.error(
                                    "Error: El atributo 'data-action-url' para eliminar está vacío o no definido."
                                );
                                Swal.fire('Error',
                                    'No se pudo determinar la URL para eliminar el corte.', 'error');
                                return;
                            }

                            Swal.fire({
                                title: '¿Eliminar corte?',
                                html: `Estás seguro de que deseas eliminar el corte: <strong>${corteNombre}</strong>?<br><small class="text-danger">Esta acción no se puede deshacer y podría afectar periodos existentes.</small>`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, eliminar',
                                cancelButtonText: 'Cancelar',
                                customClass: {
                                    confirmButton: 'btn btn-danger me-2',
                                    cancelButton: 'btn btn-label-secondary'
                                },
                                buttonsStyling: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    console.log("Confirmado eliminar. Estableciendo action:",
                                        actionUrl);
                                    formEliminarCorte.action = actionUrl;
                                    formEliminarCorte.submit();
                                }
                            });
                        });
                    });
                } else {
                    console.error("Error: No se encontró el formulario #formEliminarCorte.");
                }

            });
        </script>
    @endpush
    {{-- ** FIN: @push('scripts') ACTUALIZADO ** --}}

@endsection
