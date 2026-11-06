@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();

    $esAprobada = (bool) ($ofrenda->estadoPago?->estado_final_inscripcion ?? false);
    $esPendiente = (bool) ($ofrenda->estadoPago?->estado_pendiente ?? false);

    $nombreDonante = $ofrenda->nombre_donante ?: ($ofrenda->usuario ? $ofrenda->usuario->nombre(3) : 'Donante');
    $identificacionDonante = $ofrenda->identificacion_donante ?: ($ofrenda->usuario?->identificacion ?? 'N/A');
    $emailDonante = $ofrenda->email_donante ?: ($ofrenda->usuario?->email ?? '');
    $simboloMoneda = $ofrenda->moneda?->simbolo ?? '$';
    $codigoMoneda = $ofrenda->moneda?->nombre_corto ?? 'COP';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Estado de tu Donación')

@section('content')
<div class="min-vh-100 bg-body d-flex flex-column">
    {{-- Encabezado superior --}}
    <nav class="navbar navbar-expand-lg bg-white border-bottom py-3 shadow-xs">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                @include('_partials.logo_iglesia', ['logo_negro' => true, 'width' => '150px'])
            </a>
            <a href="{{ route('ofrendas.donaciones.formulario') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="ti ti-heart-handshake me-1"></i> Volver a donaciones
            </a>
        </div>
    </nav>

    <div class="container py-5 my-auto">
        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-7">
                <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                    <div class="card-body p-4 p-md-5 text-center">
                        @if($esAprobada)
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded-circle bg-label-success">
                                    <i class="ti ti-circle-check ti-xl"></i>
                                </span>
                            </div>
                            <span class="badge bg-label-success rounded-pill px-3 py-1 mb-2">Donación Confirmada</span>
                            <h3 class="fw-bold text-black mb-2">¡Muchas gracias por tu generosidad, {{ $nombreDonante }}!</h3>
                            <div class="text-muted mb-4">
                                @if(!empty($configuracion?->mensaje_donacion_ofrenda))
                                    {!! $configuracion->mensaje_donacion_ofrenda !!}
                                @else
                                    <p class="mb-0">Tu ofrenda ha sido recibida y confirmada exitosamente. Damos gracias a Dios por tu vida y por sembrar en nuestra iglesia.</p>
                                @endif
                            </div>

                            @if($emailDonante)
                                <div class="alert alert-success d-inline-flex align-items-center gap-2 py-2 px-3 small mb-4">
                                    <i class="ti ti-mail-check ti-sm"></i>
                                    <span>Hemos enviado el comprobante de agradecimiento a <strong>{{ $emailDonante }}</strong></span>
                                </div>
                            @endif
                        @elseif($esPendiente)
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded-circle bg-label-warning">
                                    <i class="ti ti-clock-hour-4 ti-xl"></i>
                                </span>
                            </div>
                            <span class="badge bg-label-warning rounded-pill px-3 py-1 mb-2">
                                {{ $ofrenda->estadoPago?->nombre ?? 'Transacción Pendiente' }}
                            </span>
                            <h3 class="fw-bold text-black mb-2">Tu donación está en proceso de validación</h3>
                            <p class="text-muted mb-4">
                                La entidad financiera está procesando tu transacción. Tan pronto sea confirmada por el banco, actualizaremos el estado automáticamente y te enviaremos el correo de confirmación.
                            </p>
                        @else
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded-circle bg-label-danger">
                                    <i class="ti ti-circle-x ti-xl"></i>
                                </span>
                            </div>
                            <span class="badge bg-label-danger rounded-pill px-3 py-1 mb-2">
                                {{ $ofrenda->estadoPago?->nombre ?? 'Transacción No Completada' }}
                            </span>
                            <h3 class="fw-bold text-black mb-2">No se pudo completar la donación</h3>
                            <p class="text-muted mb-4">
                                La transacción fue rechazada o cancelada en la pasarela de pagos. Si lo deseas, puedes intentar nuevamente.
                            </p>
                        @endif

                        {{-- Resumen de la transacción de Ofrenda --}}
                        <div class="card bg-lighter border-0 text-start p-4 mb-4">
                            <h6 class="fw-bold text-uppercase text-muted small mb-3">Resumen de la Donación</h6>

                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Referencia interna:</span>
                                <span class="fw-bold text-black">O-{{ $ofrenda->id }}</span>
                            </div>

                            @if($ofrenda->referencia_donacion)
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Referencia pasarela / CUS:</span>
                                    <span class="fw-semibold text-black">{{ $ofrenda->referencia_donacion }}</span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Fecha:</span>
                                <span class="text-black">{{ $ofrenda->fecha }}</span>
                            </div>

                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Donante:</span>
                                <span class="text-black">{{ $nombreDonante }} ({{ $identificacionDonante }})</span>
                            </div>

                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Concepto / Tipo de Ofrenda:</span>
                                <span class="fw-semibold text-black">{{ $ofrenda->tipoOfrenda?->nombre ?? 'Ofrenda' }}</span>
                            </div>

                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Destinado a:</span>
                                <span class="text-black">{{ $ofrenda->destinatarioRelacion?->nombre ?? 'General' }}</span>
                            </div>

                            <div class="d-flex justify-content-between pt-3 mt-1">
                                <span class="fw-bold text-black fs-6">Valor Total:</span>
                                <span class="fw-bold text-primary fs-5">
                                    {{ $simboloMoneda }} {{ number_format((float) $ofrenda->valor, 0, ',', '.') }} {{ $codigoMoneda }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            @if($esPendiente)
                                <a href="{{ route('ofrendas.donacion.finalizada', ['ofrenda' => $ofrenda->id]) }}" class="btn btn-warning rounded-pill px-4">
                                    <i class="ti ti-refresh me-1"></i> Verificar estado nuevamente
                                </a>
                            @endif

                            <a href="{{ route('ofrendas.donaciones.formulario') }}" class="btn btn-primary rounded-pill px-4">
                                <i class="ti ti-heart-plus me-1"></i> Realizar otra donación
                            </a>

                            <a href="{{ url('/') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                Finalizar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
