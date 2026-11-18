@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Solicitudes de ingreso a grupos')

@section('page-style')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/swiper/swiper.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/swiper/swiper.js',
    ])
@endsection

@section('page-script')
    <script type="module">
        const swiperContainer = document.querySelector('#swiper-with-pagination-cards');
        if (swiperContainer) {
            new Swiper(swiperContainer, {
                slidesPerView: 'auto',
                spaceBetween: 30,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });
        }

        $(document).ready(function() {
            let timeoutBuscar;
            const delayBuscar = 700;

            $('#buscar').on('input', function() {
                clearTimeout(timeoutBuscar);
                const val = $(this).val().trim();
                if (val.length >= 3 || val.length === 0) {
                    timeoutBuscar = setTimeout(function() {
                        $('#filtro').submit();
                    }, delayBuscar);
                }
            });

            $('#buscar').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    clearTimeout(timeoutBuscar);
                    e.preventDefault();
                    $('#filtro').submit();
                }
            });

            $('#btnEjecutarBuscar').on('click', function() {
                clearTimeout(timeoutBuscar);
                $('#filtro').submit();
            });

            $('#btnLimpiarBuscar').on('click', function() {
                clearTimeout(timeoutBuscar);
                $('#buscar').val('');
                $('#filtro').submit();
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const collapseElements = document.querySelectorAll('.collapse');
            collapseElements.forEach(function(collapseEl) {
                collapseEl.addEventListener('show.bs.collapse', function() {
                    const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
                    const icon = triggerButton ? triggerButton.querySelector('span.ti') : null;
                    if (icon) {
                        icon.classList.remove('ti-plus');
                        icon.classList.add('ti-minus');
                    }
                });

                collapseEl.addEventListener('hide.bs.collapse', function() {
                    const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
                    const icon = triggerButton ? triggerButton.querySelector('span.ti') : null;
                    if (icon) {
                        icon.classList.remove('ti-minus');
                        icon.classList.add('ti-plus');
                    }
                });
            });
        });
    </script>

    <script>
        let solicitudRechazarId = null;

        function confirmarAceptarSolicitud(id, nombreSolicitante, grupoNombre) {
            Swal.fire({
                title: '¿Aceptar solicitud?',
                html: `Estás a punto de aceptar a <b>${nombreSolicitante}</b> en el grupo <b>${grupoNombre}</b>.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, aceptar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-success me-2 waves-effect waves-light rounded-pill',
                    cancelButton: 'btn btn-outline-secondary waves-effect rounded-pill'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '¡Solicitud aceptada!',
                        html: `<b>${nombreSolicitante}</b> ha sido aceptado(a) en <b>${grupoNombre}</b> (simulación del prototipo).`,
                        icon: 'success',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-pill'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }

        function abrirModalRechazar(id, nombreSolicitante, grupoNombre) {
            solicitudRechazarId = id;
            document.getElementById('rechazarNombreSolicitante').textContent = nombreSolicitante;
            document.getElementById('rechazarNombreGrupo').textContent = grupoNombre;
            const textarea = document.getElementById('motivoRechazoInput');
            textarea.value = '';
            textarea.classList.remove('is-invalid');

            const modalEl = document.getElementById('modalRechazarSolicitud');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                const btnAceptar = e.target.closest('.btn-aceptar-solicitud');
                if (btnAceptar) {
                    const nombre = btnAceptar.dataset.nombre;
                    const grupo = btnAceptar.dataset.grupo;
                    confirmarAceptarSolicitud(btnAceptar.dataset.id, nombre, grupo);
                    return;
                }

                const btnRechazar = e.target.closest('.btn-rechazar-solicitud');
                if (btnRechazar) {
                    const nombre = btnRechazar.dataset.nombre;
                    const grupo = btnRechazar.dataset.grupo;
                    abrirModalRechazar(btnRechazar.dataset.id, nombre, grupo);
                }
            });

            const btnConfirmarRechazo = document.getElementById('btnConfirmarRechazo');
            if (btnConfirmarRechazo) {
                btnConfirmarRechazo.addEventListener('click', function() {
                    const textarea = document.getElementById('motivoRechazoInput');
                    const motivo = textarea.value.trim();

                    if (motivo.length < 5) {
                        textarea.classList.add('is-invalid');
                        textarea.focus();
                        return;
                    }

                    textarea.classList.remove('is-invalid');
                    const modalEl = document.getElementById('modalRechazarSolicitud');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }

                    const nombre = document.getElementById('rechazarNombreSolicitante').textContent;
                    Swal.fire({
                        title: 'Solicitud rechazada',
                        html: `Se registró el rechazo de <b>${nombre}</b>.<br><small class="text-muted">Motivo: "${motivo}"</small>`,
                        icon: 'info',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-pill'
                        },
                        buttonsStyling: false
                    });
                });
            }
        });
    </script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Gestionar solicitudes de ingreso</h4>
<p class="text-muted mb-0">Revisa, acepta o rechaza las solicitudes de personas que desean pertenecer a un grupo.</p>

@include('layouts.status-msn')

{{-- Indicadores superiores --}}
<div class="row pt-5">
    <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg" id="swiper-with-pagination-cards">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="row equal-height-row g-2">
                    @foreach ($indicadores as $indicador)
                        <div class="col equal-height-col col-lg-4 col-12">
                            <a href="{{ route('grupo.solicitudesIngreso', $indicador->url) }}">
                                <div class="card border rounded-3 shadow-sm {{ $tipo === $indicador->url ? 'border-primary border-2' : '' }}">
                                    <div class="card-body d-flex flex-row p-3">
                                        <div class="card-icon me-1">
                                            <img src="{{ $indicador->imagen }}" alt="icono {{ $indicador->nombre }}" class="me-2" width="50">
                                        </div>
                                        <div class="card-title mb-0">
                                            <p class="text-black mb-0" style="font-size: .8125rem">{{ $indicador->nombre }}</p>
                                            <h5 class="mb-0 me-2">{{ $indicador->cantidad }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="d-flex mt-10">
            <div class="swiper-pagination"></div>
        </div>
    </div>
</div>

<hr>

{{-- Buscador --}}
<form id="filtro" class="forms-sample" method="GET" action="{{ route('grupo.solicitudesIngreso', $tipo) }}">
    <div class="row mt-5 g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group input-group-merge bg-white">
                <span class="input-group-text cursor-pointer" id="btnEjecutarBuscar" title="Buscar">
                    <i class="ti ti-search"></i>
                </span>
                <input id="buscar" name="buscar" type="text" value="{{ $buscar }}" class="form-control"
                    placeholder="Buscar por nombre, correo, grupo o sede..." aria-label="Buscar...">
                @if ($buscar)
                    <span id="btnLimpiarBuscar" class="input-group-text cursor-pointer" title="Limpiar búsqueda">
                        <i class="ti ti-x"></i>
                    </span>
                @endif
            </div>
        </div>

        <div class="col-12 col-md-7 d-flex justify-content-md-end">
            <span class="text-black">
                {{ $solicitudes->count() === 1 ? '1 Solicitud' : $solicitudes->count() . ' Solicitudes' }}
            </span>
        </div>
    </div>
</form>

{{-- Listado de tarjetas --}}
<div class="row equal-height-row g-4 mt-1">
    @if ($solicitudes->count() > 0)
        @foreach ($solicitudes as $solicitud)
            <div class="col equal-height-col col-12 col-xl-4 col-lg-6 col-md-6" id="solicitud-card-{{ $solicitud->id }}">
                <div class="card rounded-3 shadow">

                    <div class="card-header border-bottom px-3 px-sm-4 pt-3 pb-2" style="background-color:#F9F9F9!important">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="d-flex flex-column flex-grow-1" style="min-width: 0;">
                                <h5 class="fw-semibold text-black m-0 text-truncate" title="{{ $solicitud->nombreSolicitante }}">
                                    {{ $solicitud->nombreSolicitante }}
                                </h5>
                                <small class="text-black text-nowrap"><i>Fecha:</i> {{ $solicitud->fechaSolicitud }}</small>
                            </div>

                            @if ($solicitud->estado === 'pendientes')
                                <div class="dropdown zindex-2 flex-shrink-0">
                                    <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item text-success btn-aceptar-solicitud" href="javascript:void(0);"
                                                data-id="{{ $solicitud->id }}"
                                                data-nombre="{{ $solicitud->nombreSolicitante }}"
                                                data-grupo="{{ $solicitud->grupoNombre }}">
                                                <i class="ti ti-check me-1"></i> Aceptar solicitud
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger btn-rechazar-solicitud" href="javascript:void(0);"
                                                data-id="{{ $solicitud->id }}"
                                                data-nombre="{{ $solicitud->nombreSolicitante }}"
                                                data-grupo="{{ $solicitud->grupoNombre }}">
                                                <i class="ti ti-x me-1"></i> Rechazar solicitud
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                            <span class="badge rounded-pill fw-semibold {{ $solicitud->estadoBadge }}">
                                {{ $solicitud->estadoLabel }}
                            </span>
                            <span class="badge rounded-pill fw-light bg-label-primary text-truncate" style="max-width: 100%;"
                                title="{{ $solicitud->grupoNombre }}">
                                <i class="ti ti-users-group me-1"></i>{{ $solicitud->grupoNombre }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-6 d-flex flex-column mb-3" style="min-width: 0;">
                                <small class="text-black">Solicitante</small>
                                <div class="d-flex align-items-center mt-1" style="min-width: 0;">
                                    <div class="avatar avatar-sm me-2 flex-shrink-0">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ $solicitud->iniciales }}</span>
                                    </div>
                                    <small class="fw-semibold text-black text-truncate" title="{{ $solicitud->nombreSolicitante }}">
                                        {{ $solicitud->nombreSolicitante }}
                                    </small>
                                </div>
                            </div>

                            <div class="col-6 d-flex flex-column mb-3" style="min-width: 0;">
                                <small class="text-black">Encargado</small>
                                <div class="d-flex align-items-center mt-1" style="min-width: 0;">
                                    <div class="avatar avatar-sm me-2 flex-shrink-0">
                                        <span class="avatar-initial rounded-circle bg-label-success"><i class="ti ti-user-check"></i></span>
                                    </div>
                                    <small class="fw-semibold text-black text-truncate" title="{{ $solicitud->encargadoNombre }}">
                                        {{ $solicitud->encargadoNombre }}
                                    </small>
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="my-3 border-1">
                            </div>

                            <div class="col-6 d-flex flex-column mt-1">
                                <small class="text-black">Correo</small>
                                <small class="fw-semibold text-black text-truncate" title="{{ $solicitud->email }}">
                                    {{ $solicitud->email }}
                                </small>
                            </div>

                            <div class="col-6 d-flex flex-column mt-1">
                                <small class="text-black">Teléfono</small>
                                <small class="fw-semibold text-black">{{ $solicitud->telefono }}</small>
                            </div>

                            <div class="col-6 d-flex flex-column mt-2">
                                <small class="text-black">Ubicación</small>
                                <small class="fw-semibold text-black text-truncate" title="{{ $solicitud->ubicacion }}">
                                    <i class="ti ti-map-pin me-1 text-muted"></i>{{ $solicitud->ubicacion }}
                                </small>
                            </div>

                            <div class="col-6 d-flex flex-column mt-2">
                                <small class="text-black">Sede</small>
                                <small class="fw-semibold text-black text-truncate" title="{{ $solicitud->sede }}">
                                    <i class="ti ti-building me-1 text-muted"></i>{{ $solicitud->sede }}
                                </small>
                            </div>
                        </div>

                        <div class="collapse" id="cardBodySolicitud{{ $solicitud->id }}">
                            <div class="col-12">
                                <hr class="my-3 border-1">
                            </div>

                            <h6 class="fw-bold text-black px-2 mb-2">Mensaje del solicitante</h6>
                            <div class="px-2 mb-3" style="max-height: 150px; overflow-y: auto;">
                                <p class="m-0 text-sm" style="font-size: 0.875rem;">{{ $solicitud->mensaje }}</p>
                            </div>

                            @if ($solicitud->estado === 'aceptadas')
                                <h6 class="fw-bold text-success px-2 mb-2 border-top pt-3">Detalle de aprobación</h6>
                                <div class="px-2 mb-2">
                                    <p class="text-secondary mt-0 mb-1">
                                        <small><b><i class="ti ti-user-check"></i> Aceptada por:</b> {{ $solicitud->gestionadoPor }}</small>
                                    </p>
                                    <p class="text-secondary mt-0 mb-0">
                                        <small><b><i class="ti ti-calendar-check"></i> Fecha:</b> {{ $solicitud->fechaRespuesta }}</small>
                                    </p>
                                </div>
                            @elseif ($solicitud->estado === 'rechazadas')
                                <h6 class="fw-bold text-danger px-2 mb-2 border-top pt-3">Motivo del rechazo</h6>
                                <div class="px-2 mb-2">
                                    <p class="text-secondary mt-0 mb-1">
                                        <small><b><i class="ti ti-user-x"></i> Rechazada por:</b> {{ $solicitud->gestionadoPor }} ({{ $solicitud->fechaRespuesta }})</small>
                                    </p>
                                    <p class="m-0 text-sm" style="font-size: 0.875rem;">{{ $solicitud->motivoRechazo }}</p>
                                </div>
                            @endif

                            @if ($solicitud->estado === 'pendientes')
                                <div class="d-flex justify-content-center gap-2 mt-4 px-2 mb-2">
                                    <button type="button"
                                        data-id="{{ $solicitud->id }}"
                                        data-nombre="{{ $solicitud->nombreSolicitante }}"
                                        data-grupo="{{ $solicitud->grupoNombre }}"
                                        class="btn btn-sm rounded-pill btn-success waves-effect btn-aceptar-solicitud">
                                        <i class="ti ti-check me-1"></i> Aceptar
                                    </button>
                                    <button type="button"
                                        data-id="{{ $solicitud->id }}"
                                        data-nombre="{{ $solicitud->nombreSolicitante }}"
                                        data-grupo="{{ $solicitud->grupoNombre }}"
                                        class="btn btn-sm rounded-pill btn-outline-danger waves-effect btn-rechazar-solicitud">
                                        <i class="ti ti-x me-1"></i> Rechazar
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer border-top p-1">
                        <div class="d-flex justify-content-center">
                            <button type="button"
                                class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2 align-items-center justify-content-center d-flex btn-collapse-card"
                                data-bs-toggle="collapse"
                                data-bs-target="#cardBodySolicitud{{ $solicitud->id }}"
                                aria-expanded="false"
                                aria-controls="cardBodySolicitud{{ $solicitud->id }}">
                                <span class="ti ti-plus"></span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    @else
        <div class="mt-5 mb-5 py-5 w-100">
            <center>
                <i class="ti ti-inbox ti-xl text-muted"></i>
                <p class="text-muted mt-2">No se encontraron solicitudes en esta categoría.</p>
            </center>
        </div>
    @endif
</div>

{{-- Modal para describir el motivo de rechazo --}}
<div class="modal fade" id="modalRechazarSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-simple">
        <div class="modal-content p-0">
            <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
                <p class="text-black fw-semibold mb-0">Rechazar solicitud de ingreso</p>
                <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x ti-sm"></i>
                </button>
            </div>

            <div class="modal-body px-5 py-4">
                <p class="text-black mb-3">
                    Vas a rechazar la solicitud de <strong id="rechazarNombreSolicitante" class="text-primary"></strong>
                    para el grupo <strong id="rechazarNombreGrupo"></strong>.
                </p>

                <div class="mb-2">
                    <label for="motivoRechazoInput" class="form-label fw-semibold">
                        Motivo del rechazo <span class="text-danger">*</span>
                    </label>
                    <textarea id="motivoRechazoInput" class="form-control" rows="4" required
                        placeholder="Describe el motivo por el cual se rechaza esta solicitud (ej. horario, distancia, cupo, etc.)..."></textarea>
                    <div class="invalid-feedback">
                        Por favor describe brevemente el motivo del rechazo (mínimo 5 caracteres).
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top px-5 py-3">
                <button type="button" data-bs-dismiss="modal"
                    class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarRechazo"
                    class="btn btn-sm py-2 px-4 rounded-pill btn-danger waves-effect waves-light">
                    <i class="ti ti-x me-1"></i>
                    Confirmar rechazo
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
