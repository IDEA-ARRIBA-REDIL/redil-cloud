@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Seleccionar Caja')

@section('content')
    <h4 class="mb-4 fw-semibold text-primary">Mis cajas asignadas</h4>
    <p>Selecciona la caja desde la cual vas a operar.</p>

    <div class="row g-4 mt-1 equal-height-row">

        @forelse($cajasAsignadas as $caja)
            <div class="col equal-height-col col-12 col-md-6 col-lg-4">
                <div class="card rounded-3 shadow h-100">

                    {{-- 1. Encabezado (Nombre y Estado) --}}
                    <div class="card-header border-bottom d-flex px-4 pt-3 pb-1" style="background-color:#F9F9F9!important">
                        <div class="flex-fill row">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-semibold ms-1 text-black m-0">
                                    {{ $caja->nombre }}
                                </h5>
                                {{-- Usamos el estilo que definiste --}}
                                <span
                                    class="{{ $caja->estado ? 'badge bg-label-primary' : 'badge bg-label-secondary' }} rounded-pill">
                                    {{ $caja->estado ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Cuerpo (Detalle Primario: Punto de Pago) --}}
                    <div class="card-body">
                        <div class="row mt-2">
                            <div class="col-12 d-flex flex-column">
                                <small class="text-black">Punto de pago:</small>
                                <small class="fw-semibold text-black ">{{ $caja->puntoDePago->nombre }}</small>
                            </div>
                        </div>

                        {{-- 3. Cuerpo Colapsable (Detalles Secundarios: Sede y Horarios) --}}
                        <div class="collapse" id="cardBodyCaja{{ $caja->id }}">
                            <div class="col-12">
                                <hr class="my-3 border-1">
                            </div>

                            <div class="col-12 d-flex flex-column mt-1">
                                <small class="text-black">Sede:</small>
                                <small class="fw-semibold text-black ">{{ $caja->puntoDePago->sede->nombre }}</small>
                            </div>

                            {{-- Mostramos los nuevos campos de configuración --}}
                            <div class="col-12 d-flex flex-column mt-2">
                                <small class="text-black">Horario de operación:</small>
                                <small class="fw-semibold text-black ">
                                    {{ $caja->hora_apertura ? \Carbon\Carbon::parse($caja->hora_apertura)->format('g:i A') : 'N/A' }}
                                    -
                                    {{ $caja->hora_cierre ? \Carbon\Carbon::parse($caja->hora_cierre)->format('g:i A') : 'N/A' }}
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Footer con Botón de Acceso y Colapsar --}}
                    <div class="card-footer border-top p-1 d-flex justify-content-between align-items-center">

                        {{-- Botón para Colapsar --}}
                        <button type="button"
                            class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2 ms-2"
                            data-bs-toggle="collapse" data-bs-target="#cardBodyCaja{{ $caja->id }}">
                            <span class="ti ti-plus"></span>
                        </button>

                        {{-- ¡NUEVO BOTÓN DE ACCESO! --}}
                        {{-- Este botón redirige a la ruta 'taquilla.gestionar' con el ID de la caja --}}
                        <div class="d-flex gap-2">
                            <a href="{{ route('taquilla.historialTransacciones', $caja) }}"
                                class="btn btn-outline-secondary rounded-pill my-2" title="Ver Historial">
                                <i class="ti ti-history"></i> Historial
                            </a>
                            <a href="{{ route('taquilla.operar', $caja) }}" class="btn btn-primary rounded-pill my-2">
                                <i class="ti ti-login me-1"></i>
                                Acceder
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center" role="alert">
                    <i class="ti ti-alert-triangle me-2"></i>
                    No tienes ninguna caja activa asignada a tu usuario. Contacta a un administrador.
                </div>
            </div>
        @endforelse
    </div>
@endsection
