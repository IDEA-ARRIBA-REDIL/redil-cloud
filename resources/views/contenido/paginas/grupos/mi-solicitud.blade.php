@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Mi solicitud en curso')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnCancelar = document.getElementById('btnCancelarSolicitud');
            if (btnCancelar) {
                btnCancelar.addEventListener('click', function() {
                    Swal.fire({
                        title: '¿Cancelar tu solicitud?',
                        html: 'Si cancelas esta solicitud en curso, podrás elegir y postularte a otro grupo cuando lo desees.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, cancelar solicitud',
                        cancelButtonText: 'No, mantenerla',
                        customClass: {
                            confirmButton: 'btn btn-danger me-2 waves-effect waves-light rounded-pill',
                            cancelButton: 'btn btn-outline-secondary waves-effect rounded-pill'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Solicitud cancelada',
                                text: 'Tu solicitud fue retirada con éxito (simulación del prototipo).',
                                icon: 'success',
                                confirmButtonText: 'Ir al inicio',
                                customClass: {
                                    confirmButton: 'btn btn-primary rounded-pill'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                window.location.href = "{{ route('dashboard') }}";
                            });
                        }
                    });
                });
            }
        });
    </script>
@endsection

@section('content')
<div class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="container my-5" style="padding-bottom: 100px;">
            <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2">

                <div class="mx-auto my-auto text-center mb-6">
                    <h4 class="text-black fw-bold mb-1 lh-sm">Ya tienes una solicitud en curso</h2>
                    <p class="text-black mt-2 mb-0">
                        Actualmente estás postulado(a) a un grupo. Mientras el encargado revisa tu solicitud, no es posible enviar otra nueva.
                    </p>
                </div>

                @include('layouts.status-msn')

                {{-- CARD DE INFORMACIÓN DE LA SOLICITUD ACTUAL --}}
                <div class="card mb-5 shadow-sm overflow-hidden" style="background-color: #f8f7fa">
                    <img src="{{ $solicitudActual->portada }}" alt="Portada {{ $solicitudActual->grupoNombre }}"
                        class="card-img-top object-fit-cover" style="height: 150px;">

                    <div class="card-header pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="card-title mb-0 fw-semibold text-black">Información de tu solicitud actual</h6>
                            <small class="text-black">Enviada el {{ $solicitudActual->fechaSolicitud }}</small>
                        </div>
                        <span class="badge rounded-pill bg-label-warning px-3 py-2 fw-semibold">
                            <i class="ti ti-clock ti-xs me-1"></i>{{ $solicitudActual->estado }}
                        </span>
                    </div>

                    <div class="card-body pt-3">
                        <h4 class="fw-bold text-black mb-1">{{ $solicitudActual->grupoNombre }}</h4>
                        <span class="badge bg-label-primary rounded-pill">{{ $solicitudActual->grupoTipo }}</span>
                        <hr>

                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <small class="text-black d-block">Sede</small>
                                <p class="fw-semibold text-black mb-0">
                                    <i class="ti ti-building-church me-1 text-primary"></i>{{ $solicitudActual->sedeNombre }}
                                </p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small class="text-black d-block">Día y hora de reunión</small>
                                <p class="fw-semibold text-black mb-0">
                                    <i class="ti ti-brand-days-counter me-1 text-primary"></i>{{ $solicitudActual->diaHora }}
                                </p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small class="text-black d-block">Dirección</small>
                                <p class="fw-semibold text-black mb-0">
                                    <i class="ti ti-map-pin me-1 text-primary"></i>{{ $solicitudActual->direccion }}
                                </p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small class="text-black d-block">Encargados</small>
                                <p class="fw-semibold text-black mb-0">
                                    <i class="ti ti-users me-1 text-primary"></i>{{ implode(', ', $solicitudActual->encargados) }}
                                </p>
                            </div>

                            <hr>
                            <div class="col-12 mb-0">
                                <small class="text-black d-block mb-1">Tu mensaje adicional</small>
                                <p class="fw-semibold text-black mb-0">
                                    {{ $solicitudActual->mensajeAdicional ?: 'Sin mensaje adicional' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mx-auto my-auto text-center">
                    <div class="d-grid gap-3 d-sm-flex justify-content-center pt-2">
                        <button type="button" id="btnCancelarSolicitud"
                            class="btn btn-outline-danger rounded-pill px-7 py-3">
                            <i class="ti ti-x me-1"></i>
                            <span>Cancelar solicitud</span>
                        </button>
                        <a href="{{ route('dashboard') }}" type="button"
                            class="btn btn-primary rounded-pill px-10 py-3">
                            <span class="align-middle me-sm-1 me-0">Volver al inicio</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
