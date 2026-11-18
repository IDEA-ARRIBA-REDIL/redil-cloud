@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Solicitud enviada')

@section('content')
<div class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="container my-5" style="padding-bottom: 100px;">
            <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2">
                <div class="mx-auto my-auto text-center">
                    <img src="{{ Storage::url('generales/img/otros/tiempo_con_Dios_respuesta.png') }}"
                        class="img-fluid w-25 p-0" alt="Solicitud enviada">
                    <h2 class="text-black fw-bold mb-0 lh-sm mt-3">¡Solicitud enviada!</h2>
                    <p class="text-black mt-1 mb-10">
                        ¡Muy bien! Tu solicitud para pertenecer al grupo ha sido registrada con éxito. Pronto se pondrán en contacto contigo.
                    </p>
                </div>

                @include('layouts.status-msn')

                {{-- CARD DE INFORMACIÓN DE LA SOLICITUD --}}
                <div class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
                    <div class="card-header pb-1">
                        <h6 class="card-title mb-0 fw-semibold">Información de tu solicitud</h6>
                    </div>
                    <div class="card-body">
                        <h4 class="fw-bold text-black">
                            {{ $grupo ? $grupo->nombre : ($grupoEjemplo->nombre ?? 'Grupo seleccionado') }}
                        </h4>
                        <hr>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <small>Tipo de grupo</small>
                                <p class="fw-semibold text-black mb-0">
                                    {{ $grupo && $grupo->tipoGrupo ? $grupo->tipoGrupo->nombre : ($grupoEjemplo->tipo ?? 'No especificado') }}
                                </p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small>Sede</small>
                                <p class="fw-semibold text-black mb-0">
                                    {{ $grupo && $grupo->sede ? $grupo->sede->nombre : ($grupoEjemplo->sede ?? 'No especificada') }}
                                </p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small>Día y hora de reunión</small>
                                <p class="fw-semibold text-black mb-0">
                                    @if ($grupo)
                                        {{ $grupo->dia ? Helper::obtenerDiaDeLaSemana($grupo->dia, 'corto') : 'Día no indicado' }}{{ $grupo->hora ? ', ' . \Carbon\Carbon::parse($grupo->hora)->format('g:i a') : '' }}
                                    @else
                                        {{ $grupoEjemplo->diaHora ?? 'Por confirmar' }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small>Dirección</small>
                                <p class="fw-semibold text-black mb-0">
                                    {{ $grupo && $grupo->direccion ? $grupo->direccion : ($grupoEjemplo->direccion ?? 'Dirección no registrada') }}
                                </p>
                            </div>

                            <hr>
                            <div class="col-12 mb-0">
                                <small>Mensaje adicional</small>
                                <p class="fw-semibold text-black mb-0">
                                    {{ $mensajeAdicional ?: 'Sin mensaje adicional' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mx-auto my-auto text-center">
                    <div class="d-grid gap-2 d-sm-flex justify-content-center pt-3">
                        <a href="{{ route('dashboard') }}" type="button" class="btn btn-primary rounded-pill px-10 py-3">
                            <span class="align-middle me-sm-1 me-0">Salir</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
