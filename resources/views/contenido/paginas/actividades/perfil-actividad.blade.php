@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Actividades')

<!-- Page -->
@section('page-style')


@section('vendor-style')
<style>
    .color-picker-container {
        width: 100px;
        /* Ajusta este valor al tamaño que necesites */
    }

    body {
        background: #fff !important;
        overflow-x: hidden;
    }

    .pickr .pcr-button {
        height: 38px !important;
        width: 40px !important;
        border: solid 1px #3e3e3e;
    }

    @media(max-width:750px){
        .banner-img{
                   background-position: center !important;
        background-size: contain !important;
        max-height: 300 !important;
        min-height: 180px;
        background-repeat: no-repeat;
        }

        .text-info{    color: black !important;
                                            font-size: 12px;
                                            padding: 5px !important;
                                            border: solid 2px #95CDDF;
                                            border-radius: 14px;
                                            text-align:justify;
                                        }
    }

    @media(min-width:850px){
        .banner-img{
            background-position: center !important;
            background-size: contain !important;
               min-height: 600px;
               background-repeat: no-repeat;
        }

         .text-info{    color: black !important;
                                            font-size: 15px;
                                            padding: 24px !important;
                                            border: solid 2px #95CDDF;
                                            border-radius: 14px;text-align:justify;
                                        }
    }

</style>


@vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])

@endsection


@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])

@endsection

@section('page-script')
@endsection
@auth
@include('layouts/sections/navbar/navbar')
@else
@include('layouts/sections/navbar/navbar-front')
@endif

@section('content')
@include('layouts.status-msn')

<div @auth style="margin-top:100px" @else style="margin-top:100px" @endif class="row p-0 mb-3">
    <div class="col-12">
        @if (isset($actividad->banner->id))
        <div style="background-image: url('{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $actividad->banner->nombre) : $configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $actividad->banner->nombre }}') !important" class="text-center banner-img">
        </div>
        @else
        <div class="bg-label-primary rounded text-center">
            <img class="img-fluid " src="{{ asset('assets/img/illustrations/girl-with-laptop.png') }}" alt="Card girl image" width="140" />
        </div>
        @endif


    </div>
</div>
<div style="margin:2% 5%" class="row  g-6">

    @if (!empty($mensajesError))
    <div class="card-footer">
        <div class="alert alert-danger alert-dismissible border border-danger fade show" role="alert">
            {!! $mensajesError !!}
        </div>

    </div>
    @endif
    <!-- bloque de la izquierda -->
    <div class="col-lg-9 col-md-7 col-sm-12">
        <button onclick="history.back()" class="btn">
            <h5> <i class="ti ti-arrow-left"></i> Volver </h5>
        </button>
        <div class="card shadow">
            <div class="card-header">
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-6 gap-2">
                    <div class="me-1">
                        <h4 class="mb-0 fw-semibold text-primary">{{ $actividad->nombre }}</h4>
                        <p>{{ $actividad->descripcion_corta }}</p>
                    </div>
                </div>
                <div class="card academy-content">
                    <div class="p-2">
                        <div class="cursor-pointer">
                        @if (isset($video->id))
                         <iframe width="100%" height="415" src="https://www.youtube.com/embed/{{ $video->url }}"
                         title="YouTube video player" frameborder="0"
                         allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                         referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
                         @endif
            </iframe>
                        </div>
                    </div>
                    <div class="card-body px-0 py-4 ">
                        <div class="col-12">
                            <div class="nav-align-top nav-tabs-shadow mb-6">
                                <ul class="nav nav-tabs nav-fill" role="tablist">
                                    <li class="nav-item">
                                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-descripcion" aria-controls="navs-descripcion" aria-selected="true"><span class="d-none d-sm-block"> <i class="ti ti-sunglasses"></i>
                                                Descripción</span><i class="ti ti-home ti-sm d-sm-none"></i></button>
                                    </li>
                                    @if($actividad->instrucciones_finales)
                                    <li class="nav-item">
                                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-instrucciones-finales" aria-controls="navs-instrucciones-finales" aria-selected="false"><span class="d-none d-sm-block"> <i class="ti ti-vocabulary"></i>Instrucciones finales</span><i class="ti ti-user ti-sm d-sm-none"></i></button>
                                    </li>
                                    @endif
                                    <li class="nav-item">
                                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-messages" aria-controls="navs-justified-messages" aria-selected="false"><span class="d-none d-sm-block"> <i class="ti ti-businessplan"></i>
                                                {{ $actividad->tipo->es_gratuita ? 'Requisitos' : 'Precios y Requisitos' }}</span><i class="ti ti-message-dots ti-sm d-sm-none"></i></button>
                                    </li>
                                    @if ($compraExistente)
                                    <li class="nav-item">
                                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-mi-compra" aria-controls="navs-mi-compra" aria-selected="false">
                                            <span class="d-none d-sm-block">
                                                <i class="ti ti-receipt-2"></i> Mis compras
                                            </span>
                                            <i class="ti ti-receipt-2 ti-sm d-sm-none"></i>
                                        </button>
                                    </li>
                                    @endif
                                </ul>
                                <div class="tab-content px-0 py-3">
                                    <div class="tab-pane fade show active" id="navs-descripcion" role="tabpanel">
                                       <span style="text-align:justify">  {!! $actividad->descripcion !!} </span>
                                    </div>
                                    @if($actividad->instrucciones_finales)
                                    <div class="tab-pane fade" id="navs-instrucciones-finales" role="tabpanel">
                                        {!! $actividad->instrucciones_finales !!}
                                    </div>
                                    @endif
                                    <div class="tab-pane fade" id="navs-justified-messages" role="tabpanel">
                                        <div class="row">
                                            @php
                                                $itemsAMostrar = $categoriasEstado->isNotEmpty() ? $categoriasEstado : $actividadEstados;
                                            @endphp

                                            @if ($itemsAMostrar->isNotEmpty())
                                            @foreach ($itemsAMostrar as $item)
                                            <div class="col-lg-6">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <b>{{ isset($item->categoria) ? $item->categoria->nombre : 'Requisitos Generales' }}</b>
                                                    @if($item->estado == 'DISPONIBLE')
                                                        <span class="badge bg-success">Disponible</span>
                                                    @else
                                                        <span class="badge bg-danger">No disponible</span>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    @if($item->estado != 'DISPONIBLE')
                                                        <div class="alert alert-warning p-2 mb-2">
                                                            <small>
                                                                <ul class="mb-0 ps-3">
                                                                    @foreach($item->motivos as $motivo)
                                                                        <li>{{ $motivo }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </small>
                                                        </div>
                                                    @endif

                                                    @if(isset($item->categoria))
                                                        @foreach ($item->categoria->monedas as $moneda)
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="badge rounded bg-label-primary me-2 p-2"><i class="ti ti-currency-dollar "></i></div>
                                                            <div class="card-info">
                                                                @php
                                                                $nombreMoneda = $moneda->nombre_corto;
                                                                $currency = Number::currency($moneda->pivot->valor, in: $nombreMoneda, locale: 'co');
                                                                @endphp
                                                                <small>Valor</small>
                                                                <h5 class="mb-0">{{ $moneda->nombre_corto == 'USD' ? 'USD ' : '$ ' }}{{ $currency }} </h5>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                            @elseif (isset($categoriasActividad))
                                            @foreach ($categoriasActividad as $categoria)
                                            <div class="col-lg-6">
                                                <div class="card-header">
                                                    <b>{{ $categoria->nombre }}</b>
                                                </div>
                                                <div class="card-body">
                                                    @foreach ($categoria->monedas as $moneda)
                                                    <div class="d-flex align-items-center">

                                                        <div class="badge rounded bg-label-primary me-2 p-2"><i class="ti ti-currency-dollar "></i>
                                                        </div>
                                                        <div class="card-info">

                                                            @php
                                                            // Aseguramos que nombre_corto tenga un valor por defecto si es null
                                                            $nombreMoneda = $moneda->nombre_corto;
                                                            // Puedes cambiar 'COP' por la moneda por defecto que prefieras
                                                            $currency = Number::currency(
                                                            $moneda->pivot->valor,
                                                            in: $nombreMoneda,
                                                            locale: 'co',
                                                            );
                                                            @endphp
                                                            <small>Valor</small>
                                                            @if ($moneda->nombre_corto == 'USD')
                                                            <h5 class="mb-0">USD {{ $currency }} </h5>
                                                            @else
                                                            <h5 class="mb-0">$ {{ $currency }} </h5>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    @if ($compraExistente)
                                    <div class="tab-pane fade" id="navs-mi-compra" role="tabpanel">
                                        <h5 class="fw-bold">Resumen de tu Compra</h5>
                                        <hr>

                                        {{-- Detalles Generales de la Compra --}}
                                        <p class="text-black fw-semibold">ID de compra: #{{ $compraExistente->id }}</p>
                                        <p class="text-black fw-semibold">Fecha: {{ Carbon\Carbon::parse($compraExistente->fecha)->format('d/m/Y') }}</p>
                                        <p class="text-black fw-semibold">Valor total: {{ number_format($compraExistente->valor, 2) }} {{ $compraExistente->moneda->simbolo ?? '' }}</p>

                                        {{-- Detalles de los Pagos --}}
                                        <h6 class="fw-semibold mt-4">Historial de pagos</h6>
                                        @forelse ($compraExistente->pagos as $pago)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-black fw-semibold">Pago #{{ $pago->id }} - {{ Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge" style="background-color: {{ $pago->estadoPago->color ?? '#6c757d' }}; color: white !important;">
                                                    {{ $pago->estadoPago->nombre ?? 'Desconocido' }}
                                                </span>
                                                <a href="{{ route('carrito.descargarComprobante', $pago->id) }}" class="btn btn-sm btn-outline-danger p-1" title="Descargar Comprobante PDF">
                                                    <i class="ti ti-file-type-pdf"></i>
                                                </a>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted">No se han registrado pagos para esta compra.</p>
                                        @endforelse
                                        <hr>

                                        {{-- Detalles Específicos (Matrícula o Inscripción) --}}
                                        @if ($actividad->tipo->tipo_escuelas && $matriculaExistente)
                                        <h6 class="fw-semibold mt-4">Detalles de la matrícula</h6>
                                        <p class="text-black fw-semibold">Materia: {{ $matriculaExistente->horarioMateriaPeriodo->materiaPeriodo->materia->nombre }}</p>
                                        <p class="text-black fw-semibold">Estado matrícula: <span class="fw-bold">{{ ucfirst($matriculaExistente->estado_pago_matricula) }}</span></p>
                                        <p class="text-black fw-semibold">Sede: {{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->aula->sede->nombre }}</p>
                                        <p class="text-black fw-semibold">Aula: {{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->aula->nombre }}</p>
                                        <p class="text-black fw-semibold">Horario:
                                            {{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->dia_semana }} de
                                            {{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->hora_inicio_formato }} a
                                            {{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->hora_fin_formato }}
                                        </p>
                                        @elseif($inscripcionesExistentes->isNotEmpty())
                                        <h6 class="fw-semibold mt-4">Detalles de la inscripción</h6>
                                        @foreach ($inscripcionesExistentes as $inscripcion)

                                        <p class="text-black fw-semibold">Estado:
                                            @if($inscripcion->estado == 1)
                                            Iniciada
                                            @elseif($inscripcion->estado == 2)
                                            Pendiente
                                            @else
                                            Finalizada
                                            @endif
                                        </p>
                                        @endforeach
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($actividad->mensaje_informativo)
                    <div class="card-footer px-0 py-2">
                        <p class="mb-7 text-info">
                            <i  class="ti ti-info-circle"></i> {{ $actividad->mensaje_informativo }}
                        <p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- bloque de la derecha -->
    <div style="margin-top:84px;margin-bottom: 50px;" class="col-lg-3 col-md-5 col-sm-12">
        <div style="position: sticky; top: 100px;" class="card shadow">
            <div class="card-header pt-3 pb-0">
             @if ($actividad->tipo->tipo_escuelas)
                <h5>Iniciar matrícula</h5>
            @elseif($actividad->tipo->permite_abonos)
                 <h5>Iniciar abono</h5>
            @elseif($actividad->tipo->unica_inscripcion && $actividad->tipo->es_gratuita  )
                <h5>Iniciar inscripción</h5>
            @else
                <h5>Iniciar compra</h5>
            @endif
            </div>

            <div class="card-body">

                {{-- REGLA 1: Validación de Fechas (Aplica a todo) --}}
                @if (Carbon\Carbon::now()->between($actividad->fecha_visualizacion, $actividad->fecha_cierre))

                {{-- REGLA 2: Validación de Inicio de Sesión --}}
                @if ($actividad->tipo->requiere_inicio_sesion && Auth::guest())
                <div class="alert alert-info">
                    <p class="mb-0">Debes iniciar sesión para poder inscribirte en esta actividad.</p>
                </div>
                <a href="{{ route('login') }}" class="btn btn-primary w-100">Iniciar sesión</a>

                {{-- Si la regla 2 se cumple (o no aplica), continuamos --}}
                @else

                {{-- REGLA 3: Validación para Actividades de ESCUELAS --}}
                @if ($actividad->tipo->tipo_escuelas)
                @if ($compraExistente)
                <div class="alert alert-success text-center">
                    <h6 class="alert-heading mb-1"><i class="ti ti-check"></i> ¡Ya estás matriculado!</h6>
                    <p class="mb-0 small">Puedes ver los detalles de tu matrícula en la pestaña "Mi compra".</p>
                </div>
                @elseif (isset($hayDisponibles) && !$hayDisponibles)
                <div class="alert alert-danger">
                    <h6>No cumples con los requisitos</h6>
                    <p class="mb-0 small">No cumples con los requisitos para ninguna de las categorías disponibles.</p>
                </div>
                @else
                <a class='btn btn-primary w-100' href="{{ route('carrito.escuelasCarrito', ['actividad' => $actividad, 'primeraVez' => true, 'compra' => 0]) }}">Gestionar matrícula</a>
                @endif

                {{-- REGLA 3.1: Validación para Actividades de ABONOS --}}
                @elseif ($actividad->tipo->permite_abonos)
                <a class='btn btn-primary w-100' href="{{ route('carrito.iniciarProcesoAbono', ['actividad' => $actividad]) }}">Gestionar abono</a>

                {{-- REGLA 4: Validación para Actividades GENERALES (no escuelas, no abonos) --}}
                @else
                @if ($esActividadDePago)
                {{-- 4a: La actividad tiene un costo --}}
                @if ($compraExistente)
                <div class="alert alert-success text-center">
                    <h6 class="alert-heading mb-1"><i class="ti ti-check"></i> ¡Compra realizada!</h6>
                    <p class="mb-0 small">Puedes ver los detalles en la pestaña "Mi compra".</p>
                </div>
                @elseif (isset($hayDisponibles) && !$hayDisponibles)
                <div class="alert alert-danger mb-0">
                    <h6 class="alert-heading mb-1"><i class="ti ti-ban"></i> Requisitos no cumplidos</h6>
                    <p class="mb-1 small">No puedes comprar por los siguientes motivos 1:</p>
                    <ul class="mb-0 ps-3 small text-start">
                        @php
                            $motivosVistos = [];
                            $itemsErrores = $categoriasEstado->isNotEmpty() ? $categoriasEstado : $actividadEstados;
                        @endphp
                        @foreach($itemsErrores as $item)
                            @foreach($item->motivos as $motivo)
                                @if(!in_array($motivo, $motivosVistos))
                                    <li>{{ $motivo }}</li>
                                    @php $motivosVistos[] = $motivo; @endphp
                                @endif
                            @endforeach
                        @endforeach
                    </ul>
                </div>
                @else
                <a class='btn btn-primary w-100' href="{{ route('carrito.carrito', ['actividad' => $actividad]) }}">Comprar</a>
                @endif
                @else
                {{-- 4b: La actividad es gratuita --}}
                @if ($inscripcionesExistentes->isNotEmpty())
                <div class="alert alert-success text-center">
                    <h6 class="alert-heading mb-1"><i class="ti ti-check"></i> ¡Ya estás inscrito!</h6>
                    <p class="mb-0 small">Gracias por registrarte.</p>
                </div>
                @elseif (isset($hayDisponibles) && !$hayDisponibles)
                <div class="alert alert-danger mb-0">
                    <h6 class="alert-heading mb-1"><i class="ti ti-ban"></i> Requisitos no cumplidos</h6>
                    <p class="mb-1 small">No puedes inscribirte por los siguientes motivos 2:</p>
                    <ul class="mb-0 ps-3 small text-start">
                        @php
                            $motivosVistos = [];
                            $itemsErrores = $categoriasEstado->isNotEmpty() ? $categoriasEstado : $actividadEstados;
                        @endphp
                        @foreach($itemsErrores as $item)
                            @foreach($item->motivos as $motivo)
                                @if(!in_array($motivo, $motivosVistos))
                                    <li>{{ $motivo }}</li>
                                    @php $motivosVistos[] = $motivo; @endphp
                                @endif
                            @endforeach
                        @endforeach
                    </ul>
                </div>
                @else
                <a class='btn btn-primary w-100' href="{{ route('carrito.carrito', ['actividad' => $actividad]) }}">Inscribirme</a>
                @endif
                @endif
                @endif

                @endif

                @else
                {{-- Si la REGLA 1 falla --}}
                <div class="alert alert-warning">
                    <h6 class="alert-heading mb-1">Inscripciones cerradas</h6>
                    <p class="mb-0 small">La fecha para registrarse en esta actividad ha finalizado.</p>
                </div>
                @endif

            </div>
        </div>
    </div>

</div>


@endsection
