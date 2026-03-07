@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Formulario de Actividad')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/rateyo/rateyo.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/rateyo/rateyo.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/js/app.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-style')

<style>
    body {
        background: #FFF !important;
        overflow-x: hidden;
    }

    .bs-stepper-icon {
        width: 60px;
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .step.active .bs-stepper-icon {
        box-shadow: 0 4px 6px rgba(59, 113, 254, 0.2);
    }

    .step.active .bs-stepper-icon svg {
        fill: white;
        width: 60%;
        height: 60%;
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }

    @media (max-width: 768px) {
        .bs-stepper-icon {
            width: 50px;
            height: 50px;
        }

        .line {
            display: none !important;
        }

        .bs-stepper-label {
            font-size: 0.8rem;
        }
    }

</style>
@endsection

@section('page-script')
<script>
    // Script para inicializar Flatpickr y Select2
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#formularioActividad')
        });
        $(".fecha-picker").flatpickr({
            dateFormat: "Y-m-d"
            , disableMobile: true
        });
    });

</script>

{{-- Modal para el Cropper de Imágenes (sin cambios) --}}
<script>
    $('#formularioActividad').submit(function() {



        Swal.fire({
            title: "Espera un momento"
            , text: "Ya estamos guardando..."
            , icon: "info"
            , showCancelButton: false
            , showConfirmButton: false
            , showDenyButton: false
        });


    });

</script>

@endsection


@section('content')
<nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
    <div class="col-3 text-start">
        {{-- El botón volver ahora es manejado por el div fijo en la parte inferior --}}
    </div>
    <div class="col-6 pl-5 text-center">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Formulario de Inscripción</h5>
    </div>
    <div class="col-3 text-end">
        <a href="{{ route('dashboard') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
            <span class="d-none d-md-block fw-normal">Salir</span>
            <span class="ti-xs ti ti-x ms-1"></span>
        </a>
    </div>
</nav>

{{-- Usamos la arquitectura unificada, por lo que el formulario siempre apunta a 'guardarFormulario' --}}
<form id="formularioActividad" role="form" action="{{ route('carrito.guardarFormulario', ['compra' => $compra]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2">

        {{-- Alerta de Solo Lectura --}}
        @if(!$puedeEditar)
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="ti ti-alert-circle ti-md me-2"></i>
            <div>
                <strong>Modo Vista Previa:</strong> Ya has enviado tu información para esta actividad y no se permiten más ediciones.
            </div>
        </div>
        @endif

        {{-- Stepper y Encabezado de la Página --}}
        <div class="step row p-4" id="step-1">
            <div class="col-12">
                <div class="d-flex align-items-start p-2 mt-1">
                    <div class="badge rounded-circle bg-label-primary p-3 me-3 rounded">
                        <i class="ti ti-file-text ti-md"></i>
                    </div>
                    <div class="my-auto">
                        <small class="text-muted">Paso {{ $contador }} de {{ $totalSecciones }}</small>
                        <h6 class="mb-0">Formulario de preguntas</h6>
                    </div>
                </div>
                <div class="progress mx-2">
                    <div class="progress-bar" role="progressbar" style="width: {{ ($contador / $totalSecciones) * 100 }}%;" aria-valuenow="{{ ($contador / $totalSecciones) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
        <h3 class="fw-semibold px-4">Ingresa la Información</h3>

        {{-- Contenedor Principal del Formulario y el Resumen --}}
        <div class="row px-4" style="margin-bottom: 120px;"> {{-- Espacio para el footer fijo --}}
            {{-- Columna Izquierda: Formulario Dinámico --}}
            <div class="col-lg-12 col-md-12 col-sm-12">
          

                @foreach ($actividad->elementos()->orderBy('orden')->get() as $elemento)
                @if ($elemento->visible)
                @php
                $respuestaElemento = $respuestas->firstWhere('elemento_formulario_actividad_id', $elemento->id);
                // Si no se puede editar, deshabilitamos el input
                $disabledAttr = !$puedeEditar ? 'disabled' : '';
                @endphp

                @if ($elemento->tipo_elemento_id == 1)
                {{-- RENDERIZADO PARA ENCABEZADOS DE SECCIÓN --}}
                <div class="col-12 my-4">
                    <h4 class="text-start fw-semibold border-bottom pb-2">{{ $elemento->titulo }}</h4>
                    <p class="text-start text-muted">{{ $elemento->descripcion }}</p>
                </div>
                @else
                {{-- RENDERIZADO PARA PREGUNTAS --}}
                <div class="shadow-sm border p-4 mb-4 rounded-3" @if(!$puedeEditar) style="opacity: 0.8; background-color: #f9f9f9;" @endif>
                    <label for="elemento-{{ $elemento->id }}" class="form-label fw-semibold">{{ $elemento->titulo }} @if($elemento->required)<span class="text-danger">(campo requerido)</span>@endif</label>
                    @if($elemento->descripcion)
                    <p class="small text-muted fst-italic mt-1">{{$elemento->descripcion}}</p>
                    @endif

                    @switch($elemento->tipo_elemento_id)
                    @case(2) {{-- Texto Corto --}}
                    <input type="text" class="form-control @error('elemento-' . $elemento->id) is-invalid @enderror" id="elemento-{{ $elemento->id }}" name="elemento-{{ $elemento->id }}" value="{{ old('elemento-'.$elemento->id, $respuestaElemento?->respuesta_texto_corto) }}" {{ $disabledAttr }}>
                    @break

                    @case(3) {{-- Texto Largo --}}
                    <textarea class="form-control @error('elemento-' . $elemento->id) is-invalid @enderror" id="elemento-{{ $elemento->id }}" name="elemento-{{ $elemento->id }}" rows="3" {{ $disabledAttr }}>{{ old('elemento-'.$elemento->id, $respuestaElemento?->respuesta_texto_largo) }}</textarea>
                    @break

                    @case(4) {{-- Si/No --}}
                    @case(5) {{-- Selección Única --}}
                    <select class="form-select @error('elemento-' . $elemento->id) is-invalid @enderror" id="elemento-{{ $elemento->id }}" name="elemento-{{ $elemento->id }}" {{ $disabledAttr }}>
                        @foreach ($elemento->opciones as $opcion)
                        <option value="{{ $opcion->valor_entero }}" {{ old('elemento-'.$elemento->id, $respuestaElemento?->respuesta_unica) == $opcion->valor_entero ? 'selected' : '' }}>
                            {{ $opcion->valor_texto }}
                        </option>
                        @endforeach
                    </select>
                    @break

                    @case(6) {{-- Selección Múltiple --}}
                    @php
                    $opcionesGuardadas = old('elemento-'.$elemento->id, $respuestaElemento ? explode(',', $respuestaElemento->respuesta_multiple) : []);
                    @endphp
                    <select class="form-select select2 @error('elemento-' . $elemento->id) is-invalid @enderror" id="elemento-{{ $elemento->id }}" name="elemento-{{ $elemento->id }}[]" multiple {{ $disabledAttr }}>
                        @foreach ($elemento->opciones as $opcion)
                        <option value="{{ $opcion->valor_entero }}" {{ in_array($opcion->valor_entero, $opcionesGuardadas) ? 'selected' : '' }}>
                            {{ $opcion->valor_texto }}
                        </option>
                        @endforeach
                    </select>
                    @break

                    @case(7) {{-- Fecha --}}
                    <input type="date" class="form-control fecha-picker @error('elemento-' . $elemento->id) is-invalid @enderror" id="elemento-{{ $elemento->id }}" name="elemento-{{ $elemento->id }}" value="{{ old('elemento-'.$elemento->id, $respuestaElemento?->respuesta_fecha) }}" {{ $disabledAttr }}>
                    @break

                    @case(8) {{-- Número --}}
                    <input type="number" class="form-control @error('elemento-' . $elemento->id) is-invalid @enderror" id="elemento-{{ $elemento->id }}" name="elemento-{{ $elemento->id }}" value="{{ old('elemento-'.$elemento->id, $respuestaElemento?->respuesta_numero) }}" {{ $disabledAttr }}>
                    @break
                    @case(9) {{-- Moneda --}}
                    <input type="number" class="form-control @error('elemento-' . $elemento->id) is-invalid @enderror" id="elemento-{{ $elemento->id }}" name="elemento-{{ $elemento->id }}" value="{{ old('elemento-'.$elemento->id, $respuestaElemento?->respuesta_moneda) }}" {{ $disabledAttr }}>
                    @break

                    @case(10) {{-- Archivo --}}
                    @if ($respuestaElemento && $respuestaElemento->url_archivo)
                    <div class="alert alert-secondary d-flex justify-content-between align-items-center p-2">
                        <span><i class="ti ti-file-check ti-lg me-2"></i><strong>{{ basename($respuestaElemento->url_archivo) }}</strong></span>
                        @if($puedeEditar)
                        <a href="{{ route('carrito.eliminarRespuesta', ['compra' => $compra, 'respuesta' => $respuestaElemento]) }}" class="btn btn-sm btn-danger"><i class="ti ti-trash me-1"></i>Eliminar</a>
                        @endif
                    </div>
                    @else
                    <input type="file" class="form-control @error('elemento-' . $elemento->id) is-invalid @enderror" id="elemento-{{ $elemento->id }}" name="elemento-{{ $elemento->id }}" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" {{ $disabledAttr }}>
                    @endif
                    @break

                    @case(11) {{-- Imagen --}}
                    @if ($respuestaElemento && $respuestaElemento->url_foto)
                    <div class="mb-2">
                        <p class="mb-1">Imagen actual:</p>
                        <img src="{{ Storage::url($respuestaElemento->url_foto) }}" class="img-fluid rounded border" style="max-height: 200px;">
                    </div>
                    @if($puedeEditar)
                    <a href="{{ route('carrito.eliminarRespuesta', ['compra' => $compra, 'respuesta' => $respuestaElemento]) }}" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Eliminar para subir una nueva</a>
                    @endif
                    @else
                    @if($puedeEditar)
                    <button class="btn btn-primary btn-open-modal" type="button" data-bs-toggle="modal" data-bs-target="#modalFoto" data-elemento_imagen="{{ $elemento->id }}" data-max-width="{{ $elemento->ancho ?? 0 }}" data-max-height="{{ $elemento->largo ?? 0 }}">
                        <i class="ti ti-upload me-1"></i> Subir una imagen
                    </button>
                    <input class="form-control cropperImageUpload" type="file" id="cropperImageUpload" accept="image/png, image/jpeg">
                    <div class="mt-3 preview-container_{{ $elemento->id }}" style="display:none;">
                        <p>Vista previa:</p>
                        <img id="preview-img_{{ $elemento->id }}" src="" class="img-fluid rounded border" style="max-height: 200px;">
                    </div>
                    @else
                    <div class="text-muted fst-italic">No se subió ninguna imagen.</div>
                    @endif
                    @endif
                    @break
                    @endswitch
                    @error('elemento-' . $elemento->id)
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                @endif
                @endif
                @endforeach
            </div>

            {{-- Columna Derecha: Resumen de Compra --}}
            @if(isset($compra) && $itemsDelCarrito->isNotEmpty())
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card shadow-sm" style="position: sticky; top: 100px;">
                    <div class="card-header">
                        <h5 class="fw-semibold mb-0">Detalles</h5>
                    </div>
                    <div class="card-body">
                        @if($usuarioCompra)
                        <p class="mb-1"><strong>A nombre de:</strong> {{ $usuarioCompra->nombre(3) }}</p>
                        @endif
                        <hr class="my-2">
                        <dl class="row mb-0">
                            @foreach ($itemsDelCarrito as $item)
                            <dt class="col-8 fw-normal">{{ $item->cantidad }} x {{ $item->categoria->nombre }}</dt>
                            <dd class="col-4 text-end">{{ ($item->precio > 0) ? Number::currency($item->precio * $item->cantidad, $moneda?->nombre_corto) : 'Gratis' }}</dd>
                            @endforeach
                        </dl>
                        <hr class="my-2">
                        <dl class="row mb-0">
                            <dt class="col-8 h6">Total</dt>
                            <dd class="col-4 h6 fw-bold text-end mb-0">{{ ($valorTotal > 0) ? Number::currency($valorTotal, $moneda?->nombre_corto) : 'Gratis' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Barra de Navegación Inferior Fija --}}
    <div class="w-100 fixed-bottom py-3 px-4 border-top bg-white">
        <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2 d-flex justify-content-between">

            {{-- INICIO DE LA CORRECCIÓN --}}
            <a class="btn btn-outline-secondary rounded-pill" href="
                @if ($actividad->tipo->permite_abonos)
                    {{ route('carrito.abonoCarrito', ['compra' => $compra->id, 'actividad' => $actividad->id, 'primeraVez' => 0]) }}
                @else
                    {{ route('carrito.carrito', $actividad) }}
                @endif
               ">
                <i class="ti ti-arrow-left me-1"></i> Anterior
            </a>
            {{-- FIN DE LA CORRECCIÓN --}}

            @if($puedeEditar)
            <button type="submit" class="btn btn-primary rounded-pill btnGuardar">
                Continuar <i class="ti ti-arrow-right ms-1"></i>
            </button>
            @else
            {{-- Botón (Enlace) para Continuar sin Guardar --}}
            @php
                 $rutaSiguiente = ($valorTotal > 0) 
                    ? route('carrito.checkout', ['compra' => $compra, 'actividad' => $actividad]) 
                    : route('carrito.inscripcionFinalizada', ['inscripcion' => $compra->inscripciones->first(), 'actividad' => $actividad]); 
            @endphp
            <a href="{{ $rutaSiguiente }}" class="btn btn-primary rounded-pill">
                Continuar <i class="ti ti-arrow-right ms-1"></i>
            </a>
            @endif
        </div>
    </div>
</form>


@endsection
