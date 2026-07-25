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
               min-height: 500px;
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

<div class="row p-0 mb-3">
    <div class="col-12">
        <div style="background-image: url('{{ $actividad->portada_url }}') !important" class="text-center banner-img"></div>
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
                                                        <span class="btn bg-success text-white">Disponible </span>
                                                    @else
                                                        <span class="btn bg-danger text-white">No disponible </span>
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
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="fw-bold mb-0 text-primary">
                                                <i class="ti ti-receipt-2 me-1"></i> Resumen de tu Compra
                                            </h5>
                                            <span class="badge bg-label-primary px-3 py-2 fs-6">
                                                ID Compra: #{{ $compraExistente->id }}
                                            </span>
                                        </div>

                                        <!-- KPI Cards de la Compra -->
                                        <div class="row g-3 mb-4">
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="card border ">
                                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-label-primary rounded p-2">
                                                            <i class="ti ti-calendar fs-3"></i>
                                                        </div>
                                                        <div>
                                                            <small class="text-black d-block">Fecha de Compra</small>
                                                            <h6 class="fw-bold mb-0">{{ Carbon\Carbon::parse($compraExistente->fecha)->format('d/m/Y') }}</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="card border ">
                                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-label-success rounded p-2">
                                                            <i class="ti ti-currency-dollar fs-3"></i>
                                                        </div>
                                                        <div>
                                                            <small class="text-black d-block">Valor Total</small>
                                                            <h6 class="fw-bold mb-0 text-success">$ {{ number_format($compraExistente->valor, 2, ',', '.') }} {{ $compraExistente->moneda->nombre_corto ?? 'COP' }}</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="card border ">
                                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-md bg-label-info rounded p-2">
                                                            <i class="ti ti-credit-card fs-3"></i>
                                                        </div>
                                                        <div>
                                                            <small class="text-black d-block">Total de Pagos</small>
                                                            <h6 class="fw-bold mb-0">{{ $compraExistente->pagos->count() }} transaccion(es)</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Detalles Específicos (Matrícula o Inscripción) --}}
                                        @if ($actividad->tipo->tipo_escuelas && $matriculaExistente)
                                        <div class="card border shadow-sm mb-4">
                                            <div class="card-header bg-label-primary py-3 d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0 text-white">
                                                    <i class="ti ti-school me-2"></i> Información de Matrícula
                                                </h6>
                                                @php
                                                    $badgeColor = match($matriculaExistente->estado_pago_matricula) {
                                                        'pagada' => 'bg-success',
                                                        'pendiente' => 'bg-warning',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeColor }} text-uppercase text-white">
                                                    {{ $matriculaExistente->estado_pago_matricula }}
                                                </span>
                                            </div>
                                            <div class="card-body pt-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Materia</small>
                                                        <span class="fw-semibold text-dark fs-6">{{ $matriculaExistente->horarioMateriaPeriodo->materiaPeriodo->materia->nombre ?? 'N/D' }}</span>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Sede de Clase</small>
                                                        <span class="fw-semibold text-dark fs-6">{{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->aula->sede->nombre ?? 'N/D' }}</span>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Aula</small>
                                                        <span class="fw-semibold text-dark fs-6">{{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->aula->nombre ?? 'N/D' }}</span>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Horario</small>
                                                        <span class="fw-semibold text-dark fs-6">
                                                            {{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->dia_semana }} de
                                                            {{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->hora_inicio_formato }} a
                                                            {{ $matriculaExistente->horarioMateriaPeriodo->horarioBase->hora_fin_formato }}
                                                        </span>
                                                    </div>
                                                    @if($matriculaExistente->materialSede)
                                                    <div class="col-12 mt-2 pt-2 border-top">
                                                        <small class="text-muted d-block"><i class="ti ti-map-pin me-1"></i> Sede Entrega de Material</small>
                                                        <span class="fw-bold text-primary fs-6">{{ $matriculaExistente->materialSede->nombre }}</span>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @elseif($inscripcionesExistentes->isNotEmpty())
                                        <div class="card border shadow-sm mb-4">
                                            <div class="card-header bg-label-info py-3">
                                                <h6 class="fw-bold mb-0 text-info">
                                                    <i class="ti ti-user-check me-2"></i> Estado de Inscripción
                                                </h6>
                                            </div>
                                            <div class="card-body pt-3">
                                                @foreach ($inscripcionesExistentes as $inscripcion)
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-semibold">Estado actual:</span>
                                                    @if($inscripcion->estado == 1)
                                                        <span class="badge bg-info">Iniciada</span>
                                                    @elseif($inscripcion->estado == 2)
                                                        <span class="badge bg-warning">Pendiente</span>
                                                    @else
                                                        <span class="badge bg-success">Finalizada</span>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif

                                        {{-- Historial de Pagos --}}
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0 text-dark">
                                                    <i class="ti ti-history me-2"></i> Historial de Transacciones y Comprobantes
                                                </h6>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table align-middle table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Pago</th>
                                                            <th>Fecha</th>
                                                            <th>Valor</th>
                                                            <th>Estado</th>
                                                            <th class="text-end">Comprobante</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($compraExistente->pagos as $pagoItem)
                                                        <tr>
                                                            <td>
                                                                <span class="fw-bold text-dark">#{{ $pagoItem->id }}</span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">{{ Carbon\Carbon::parse($pagoItem->fecha)->format('d/m/Y h:i A') }}</small>
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold text-dark">$ {{ number_format($pagoItem->valor, 2, ',', '.') }} {{ $pagoItem->moneda->nombre_corto ?? '' }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge" style="background-color: {{ $pagoItem->estadoPago->color ?? '#6c757d' }}; color: white !important;">
                                                                    {{ $pagoItem->estadoPago->nombre ?? 'Desconocido' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end">
                                                                <a href="{{ route('carrito.descargarComprobante', $pagoItem->id) }}" target="_blank" class="btn btn-sm btn-label-danger d-inline-flex align-items-center gap-1 shadow-sm px-3 rounded-pill" title="Descargar Recibo PDF en una sola página">
                                                                    <i class="ti ti-file-type-pdf fs-5"></i>
                                                                    <span>Descargar Recibo PDF</span>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center py-4 text-muted">
                                                                <i class="ti ti-info-circle me-1"></i> No se han registrado pagos para esta compra.
                                                            </td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
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
                    @if ($pagoConfirmado)
                        <div class="alert alert-success text-center">
                            <h6 class="alert-heading mb-1"><i class="ti ti-check"></i> ¡Ya estás matriculado!</h6>
                            <p class="mb-0 small">Puedes ver los detalles de tu matrícula en la pestaña "Mis compras".</p>
                        </div>
                    @elseif ($pagoPendiente)
                        <div class="alert alert-warning text-center">
                            <h6 class="alert-heading mb-1"><i class="ti ti-clock"></i> Pago en proceso de verificación</h6>
                            <p class="mb-2 small">
                                Tienes un pago en proceso de verificación por la pasarela. Debes esperar a que se complete el proceso (suele tomar entre 7 y 30 minutos).
                            </p>
                            @if ($compraExistente)
                                <a href="{{ route('carrito.checkout', ['compra' => $compraExistente, 'actividad' => $actividad]) }}" class="btn btn-warning btn-sm text-dark w-100 mt-1">
                                    <i class="ti ti-refresh me-1"></i> Verificar Estado de Mi Pago
                                </a>
                            @endif
                        </div>
                    @else
                        @if ($pagoAnuladoOFallido)
                            <div class="alert alert-warning p-2 mb-3 small text-start">
                                <i class="ti ti-alert-triangle me-1"></i> Tu intento de pago anterior no fue efectivo (Estado: {{ $estadoPagoObj->nombre ?? 'Anulado/Cancelado' }}). Puedes intentar matricularte nuevamente.
                            </div>
                        @endif

                        @if (isset($hayDisponibles) && !$hayDisponibles)
                            <div class="alert alert-danger">
                                <h6>No cumples con los requisitos</h6>
                                <p class="mb-0 small">No cumples con los requisitos para ninguna de las categorías disponibles.</p>
                            </div>
                        @else
                            <a class='btn btn-primary w-100' href="{{ route('carrito.escuelasCarrito', ['actividad' => $actividad, 'primeraVez' => true, 'compra' => 0]) }}">Gestionar matrícula</a>
                        @endif
                    @endif

                {{-- REGLA 3.1: Validación para Actividades de ABONOS --}}
                @elseif ($actividad->tipo->permite_abonos)
                    @if ($pagoPendiente)
                        <div class="alert alert-warning text-center">
                            <h6 class="alert-heading mb-1"><i class="ti ti-clock"></i> Abono en proceso de verificación</h6>
                            <p class="mb-2 small">Tienes un pago de abono pendiente por verificar por la pasarela.</p>
                            @if ($compraExistente)
                                <a href="{{ route('carrito.checkout', ['compra' => $compraExistente, 'actividad' => $actividad]) }}" class="btn btn-warning btn-sm text-dark w-100 mt-1">
                                    <i class="ti ti-refresh me-1"></i> Verificar Estado de Mi Abono
                                </a>
                            @endif
                        </div>
                    @else
                        <a class='btn btn-primary w-100' href="{{ route('carrito.iniciarProcesoAbono', ['actividad' => $actividad]) }}">Gestionar abono</a>
                    @endif

                {{-- REGLA 4: Validación para Actividades GENERALES (no escuelas, no abonos) --}}
                @else
                    @if ($esActividadDePago)
                        {{-- 4a: La actividad tiene un costo --}}
                        @if ($pagoConfirmado)
                            <div class="alert alert-success text-center">
                                <h6 class="alert-heading mb-1"><i class="ti ti-check"></i> ¡Compra realizada!</h6>
                                <p class="mb-0 small">Puedes ver los detalles en la pestaña "Mis compras".</p>
                            </div>
                        @elseif ($pagoPendiente)
                            <div class="alert alert-warning text-center">
                                <h6 class="alert-heading mb-1"><i class="ti ti-clock"></i> Pago en proceso de verificación</h6>
                                <p class="mb-2 small">Tienes un pago en proceso de verificación. Por favor espera unos minutos.</p>
                                @if ($compraExistente)
                                    <a href="{{ route('carrito.checkout', ['compra' => $compraExistente, 'actividad' => $actividad]) }}" class="btn btn-warning btn-sm text-dark w-100 mt-1">
                                        <i class="ti ti-refresh me-1"></i> Verificar Estado de Mi Pago
                                    </a>
                                @endif
                            </div>
                        @else
                            @if ($pagoAnuladoOFallido)
                                <div class="alert alert-warning p-2 mb-3 small text-start">
                                    <i class="ti ti-alert-triangle me-1"></i> Tu intento de pago anterior no fue efectivo (Estado: {{ $estadoPagoObj->nombre ?? 'Anulado' }}). Puedes intentar comprar nuevamente.
                                </div>
                            @endif

                            @if (isset($hayDisponibles) && !$hayDisponibles)
                                <div class="alert alert-danger mb-0">
                                    <h6 class="alert-heading mb-1"><i class="ti ti-ban"></i> Requisitos no cumplidos</h6>
                                    <p class="mb-1 small">No puedes comprar por los siguientes motivos:</p>
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
                                <p class="mb-1 small">No puedes inscribirte por los siguientes motivos:</p>
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
