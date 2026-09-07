@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia Infantil — Lista del Turno')

@section('page-style')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    ])
@endsection

@section('page-script')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    // Confirmación SweetAlert2 para eliminar registro
    function confirmarEliminarRegistro(formId, nombre) {
        Swal.fire({
            title: '¿Eliminar el registro de <b>' + nombre + '</b>?',
            html: 'Esta acción no es reversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    document.addEventListener('alpine:init', () => {

        // Escucha el evento del componente Livewire de reportes y auto-filtra
        window.addEventListener('reporteIglesiaInfantilSeleccionado', (event) => {
            const reporteId = event.detail[0]?.reporteId ?? event.detail.reporteId ?? '';
            const campo = document.getElementById('filtro_reporte_id');
            if (campo && reporteId) {
                campo.value = reporteId;
                document.getElementById('formFiltrar').submit();
            }
        });

        // Data para paneles y modales de retiro
        Alpine.data('retiroPanelForm', () => ({
            adultoId: '',
            adultoNombre: '',
            qrCodigo: '',

            init() {
                window.addEventListener('usuario-seleccionado', (event) => {
                    const id = event.detail?.id ?? '';
                    const nombre = event.detail?.nombre_completo ?? event.detail?.nombre ?? '';
                    if (id) {
                        this.adultoId = id;
                        this.adultoNombre = nombre;
                    } else {
                        this.adultoId = '';
                        this.adultoNombre = '';
                    }
                });
            },
        }));
    });

    // QR Scanner para retiro rápido
    document.addEventListener('livewire:initialized', () => {
        if (typeof Html5Qrcode !== 'undefined') {
            Html5Qrcode.getCameras().then(devices => {
                const sel = document.getElementById('qrCameraSelect');
                if (!sel) return;
                if (devices && devices.length) {
                    sel.innerHTML = '<option value="" disabled>Selecciona una cámara</option>';
                    devices.forEach(d => {
                        sel.innerHTML += `<option value="${d.id}">${d.label}</option>`;
                    });
                    sel.disabled = false;
                }
            }).catch(() => {
                const sel = document.getElementById('qrCameraSelect');
                if (sel) sel.innerHTML = '<option value="">No se encontraron cámaras</option>';
            });
        }
    });

    let qrRetiroScanner = null;
    const qrRetiroUrl = '{{ route('iglesiaInfantil.checkin.retiroQr') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function abrirScannerRetiro() {
        const sel = document.getElementById('qrCameraSelect');
        if (!sel || !sel.value) {
            Swal.fire('Atención', 'Por favor selecciona una cámara primero.', 'warning');
            return;
        }

        const modalEl = document.getElementById('scannerRetiroModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        modalEl.addEventListener('shown.bs.modal', () => {
            if (qrRetiroScanner && qrRetiroScanner.isScanning) return;
            qrRetiroScanner = new Html5Qrcode('qrReaderRetiro');
            qrRetiroScanner.start(
                sel.value,
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    qrRetiroScanner.stop().then(() => {
                        qrRetiroScanner = null;
                        document.getElementById('qrReaderRetiro').innerHTML = '';
                        modal.hide();

                        // Llamada al endpoint JSON — procesa el retiro automáticamente
                        fetch(qrRetiroUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ codigo_retiro: decodedText.toUpperCase() }),
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.ok) {
                                Swal.fire({
                                    title: '¡Entregado!',
                                    html: '<b>' + (data.menor ?? 'Menor') + '</b> fue entregado correctamente.',
                                    icon: 'success',
                                    timer: 3000,
                                    showConfirmButton: false,
                                }).then(() => window.location.reload());
                            } else {
                                Swal.fire('Sin resultados', data.mensaje, 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'));

                    }).catch(err => console.error('Error al detener scanner', err));
                },
                () => {}
            ).catch(() => Swal.fire('Error', 'No se pudo iniciar el scanner.', 'error'));
        }, { once: true });

        modalEl.addEventListener('hidden.bs.modal', () => {
            if (qrRetiroScanner && qrRetiroScanner.isScanning) {
                qrRetiroScanner.stop().catch(() => {});
                qrRetiroScanner = null;
                document.getElementById('qrReaderRetiro').innerHTML = '';
            }
        }, { once: true });
    }
</script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">
    Lista del turno
</h4>
<p class="mb-4 text-black">Menores registrados en la iglesia infantil durante el servicio.</p>

@include('layouts.status-msn')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <a href="{{ route('iglesiaInfantil.checkin') }}" class="btn btn-primary waves-effect waves-light {{ !$reporteReunionId ? 'disabled' : '' }}">
        <i class="ti ti-plus me-1"></i>Nuevo check-in
    </a>
    @if ($reporteReunionId)
        <a href="{{ route('iglesiaInfantil.exportar', ['reporte_reunion_id' => $reporteReunionId]) }}"
            class="btn btn-success waves-effect waves-light">
            <i class="ti ti-file-spreadsheet me-1"></i>Descargar reporte Excel
        </a>
    @endif
</div>

{{-- Contadores rápidos y Escáner QR --}}
@if ($reporteReunionId)
    <div class="row g-3 mb-5 align-items-stretch">
        {{-- Panel de Escaneo QR (Integrado) --}}
        <div class="col-lg-7">
            <div class="card h-100 border-primary border-top border-3 shadow-sm">
                <div class="card-body py-3 d-flex flex-column justify-content-center">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h5 class="mb-1 text-primary fw-bold"><i class="ti ti-qrcode me-2"></i>Salida con QR</h5>
                            <p class="text-black small mb-2 mb-md-0">Apunta al QR del ticket para entrega inmediata.</p>
                        </div>
                        <div class="col-md-7 border-start-md ps-md-4">
                            <div class="d-flex gap-2 flex-wrap">
                                <div class="flex-fill">
                                    <select id="qrCameraSelect" class="form-select form-select-sm" disabled>
                                        <option value="">Cargando c&aacute;maras...</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm waves-effect waves-light"
                                    onclick="abrirScannerRetiro()">
                                    <i class="ti ti-scan me-1"></i>Escanear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contadores Rápidos --}}
        <div class="col-lg-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="card h-100 text-center py-2 border-bottom border-primary border-2 shadow-sm bg-white">
                        <div class="card-body p-2 d-flex flex-column justify-content-center">
                            <h3 class="mb-0 fw-bold text-black">{{ $registros->total() }}</h3>
                            <small class="text-black fw-semibold text-uppercase" style="font-size: 0.65rem;">Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card h-100 text-center py-2 border-bottom border-warning border-2 shadow-sm bg-white">
                        <div class="card-body p-2 d-flex flex-column justify-content-center">
                            <h3 class="mb-0 fw-bold text-black">{{ $registros->getCollection()->where('estado', 'en_custodia')->count() }}</h3>
                            <small class="text-black fw-semibold text-uppercase" style="font-size: 0.65rem;">Custodia</small>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card h-100 text-center py-2 border-bottom border-success border-2 shadow-sm bg-white">
                        <div class="card-body p-2 d-flex flex-column justify-content-center">
                            <h3 class="mb-0 fw-bold text-black">{{ $registros->getCollection()->where('estado', 'entregado')->count() }}</h3>
                            <small class="text-black fw-semibold text-uppercase" style="font-size: 0.65rem;">Entregados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row g-4">

    {{-- ================================================================ --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                {{-- Filtros --}}
                <form method="GET" action="{{ route('iglesiaInfantil.listaTurno') }}"
                    id="formFiltrar">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6">
                            <input type="hidden" name="reporte_reunion_id" id="filtro_reporte_id"
                                value="{{ $reporteReunionId }}">
                            <label class="form-label mb-1 small text-black fw-bold">Reporte de reuni&oacute;n</label>
                            @livewire('IglesiaInfantil.reportes-para-checkin', ['reporteReunionId' => $reporteReunionId])
                        </div>
                        <div class="col-12 col-sm-8 col-md-4">
                            <label class="form-label mb-1 small text-black fw-bold">Buscar por nombre o c&oacute;digo</label>
                            <input type="text" name="buscar" class="form-control"
                                placeholder="Escribe aqu&iacute;..." value="{{ $buscar }}">
                        </div>
                        <div class="col-12 col-sm-4 col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary waves-effect waves-light flex-fill">
                                <i class="ti ti-search me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('iglesiaInfantil.listaTurno') }}" class="btn btn-outline-secondary waves-effect" title="Limpiar filtros">
                                <i class="ti ti-x"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">

                {{-- Grid de cards --}}
                @forelse ($registros as $registro)
                    @if ($loop->first)<div class="row g-3">@endif

                    <div class="col-sm-6 col-lg-4">
                        <div class="card h-100 border shadow-none"
                            style="border-left: 4px solid {{ $registro->estaEnCustodia() ? '#ffc107' : '#28a745' }} !important;">
                            <div class="card-body p-3">

                                {{-- Encabezado: nombre + badge estado --}}
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="fw-bold  text-black mb-0 lh-sm">{{ $registro->menor?->nombre(3) }}</p>
                                        <small class="text-black">Servidor: {{ $registro->servidorIngreso?->nombre(2) }}</small>
                                    </div>
                                    <span class="badge bg-label-{{ $registro->estaEnCustodia() ? 'warning' : 'success' }} ms-2 flex-shrink-0">
                                        {{ $registro->estaEnCustodia() ? 'En custodia' : 'Entregado' }}
                                    </span>
                                </div>

                                <hr class="my-2">

                                {{-- Datos --}}
                                <div class="small">
                                    <div class="mb-1">
                                        <i class="ti ti-user me-1 text-black"></i>
                                        <span class="text-black">Adulto:</span>
                                        {{ $registro->adultoIngreso?->nombre(3) }}
                                    </div>
                                    <div class="mb-1">
                                        <i class="ti ti-door me-1 text-black"></i>
                                        <span class="text-black">Salon:</span>
                                        <span class="fw-bold text-black me-1">{{ $registro->salon?->nombre }}</span><br>
                                        <i class="ti ti-map-pin me-1 text-black"></i> <span class="text-black">Estaci&oacute;n:</span>
                                        <span class="fw-bold text-black">{{ $registro->estacion?->nombre }}</span>
                                    </div>
                                    <div class="mb-1">
                                        <i class="ti ti-clock me-1 text-black"></i>
                                        <span class="text-black">Entrada:</span>
                                        <span class="fw-bold text-black">{{ $registro->hora_entrada }}</span>
                                        @if($registro->hora_entrega)
                                            <span class="text-black">Salida:</span>
                                            <span class="fw-bold text-black">{{ $registro->hora_entrega }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <i class="ti ti-qrcode me-1 text-black"></i>
                                        <code>{{ $registro->codigo_retiro }}</code>
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div class="mt-3 d-flex justify-content-end align-items-center gap-2">
                                    {{-- Botón rápido para abrir modal de los 4 tamaños --}}
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary waves-effect"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalImprimirTicket{{ $registro->id }}"
                                        title="Imprimir ticket en diferentes tamaños">
                                        <i class="ti ti-printer me-1"></i>Imprimir ticket
                                    </button>

                                    <div class="dropdown">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary dropdown-toggle waves-effect"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button type="button" class="dropdown-item py-2 fw-semibold text-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalImprimirTicket{{ $registro->id }}">
                                                    <i class="ti ti-printer me-2"></i>Elegir tamaño de ticket...
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <h6 class="dropdown-header text-muted fw-bold py-1" style="font-size: 0.75rem;">
                                                    Impresión rápida (2 hojas):
                                                </h6>
                                            </li>
                                            <li>
                                                <a href="{{ route('iglesiaInfantil.registro.ticket', ['registro' => $registro, 'formato' => 'actual']) }}"
                                                    target="_blank" class="dropdown-item py-1">
                                                    <i class="ti ti-receipt me-2 text-muted"></i>1. Estándar 58 mm
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('iglesiaInfantil.registro.ticket', ['registro' => $registro, 'formato' => 'termica_80mm']) }}"
                                                    target="_blank" class="dropdown-item py-1">
                                                    <i class="ti ti-printer me-2 text-muted"></i>2. Térmica 80 mm (POS)
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('iglesiaInfantil.registro.ticket', ['registro' => $registro, 'formato' => 'dymo_450']) }}"
                                                    target="_blank" class="dropdown-item py-1">
                                                    <i class="ti ti-tag me-2 text-muted"></i>3. Dymo 450 (Manilla)
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('iglesiaInfantil.registro.ticket', ['registro' => $registro, 'formato' => 'largo_20x9']) }}"
                                                    target="_blank" class="dropdown-item py-1">
                                                    <i class="ti ti-file-text me-2 text-muted"></i>4. Largo 20 × 9 cm
                                                </a>
                                            </li>
                                            @if ($registro->estaEnCustodia())
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalCambiarSalon{{ $registro->id }}">
                                                        <i class="ti ti-edit me-2"></i>Cambiar sal&oacute;n
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalRetiro{{ $registro->id }}">
                                                        <i class="ti ti-logout me-2"></i>Procesar retiro
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form id="formEliminar{{ $registro->id }}" method="POST"
                                                        action="{{ route('iglesiaInfantil.registro.eliminar', $registro) }}"
                                                        class="d-inline w-100">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="dropdown-item text-danger"
                                                            onclick="confirmarEliminarRegistro('formEliminar{{ $registro->id }}', '{{ $registro->menor?->nombre(2) }}')">
                                                            <i class="ti ti-trash me-2"></i>Eliminar
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    @if ($loop->last)</div>@endif

                    {{-- Modales (fuera del grid pero dentro del forelse) --}}

                    {{-- Modal para seleccionar el modelo de ticket --}}
                    <div class="modal fade" id="modalImprimirTicket{{ $registro->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header pb-2">
                                    <h5 class="modal-title text-primary fw-bold">
                                        <i class="ti ti-printer me-2"></i>Imprimir Ticket — {{ $registro->menor?->nombre(3) }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-2">
                                    <div class="alert alert-info py-2 mb-3 small d-flex align-items-center">
                                        <i class="ti ti-info-circle fs-5 me-2 flex-shrink-0"></i>
                                        <div>
                                            Elige el modelo adecuado para la impresora conectada. Cada modelo imprimirá <strong>2 hojas consecutivas</strong> (1 Gafete del Menor + 1 Comprobante de Retiro del Adulto).
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        {{-- 1. Estándar 58 mm --}}
                                        <div class="col-sm-6">
                                            <div class="card h-100 border shadow-none p-3 bg-light">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="badge bg-primary p-2 rounded me-2">
                                                        <i class="ti ti-receipt fs-4 text-white"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-black">1. Estándar 58 mm</h6>
                                                        <small class="text-muted">Mini-POS o recibo térmico</small>
                                                    </div>
                                                </div>
                                                <p class="small text-black mb-3">
                                                    Formato continuo de 58 mm para mini-impresoras portátiles o de recibos con línea punteada de corte.
                                                </p>
                                                <a href="{{ route('iglesiaInfantil.registro.ticket', ['registro' => $registro, 'formato' => 'actual']) }}"
                                                    target="_blank" class="btn btn-sm btn-primary w-100 waves-effect waves-light mt-auto">
                                                    <i class="ti ti-printer me-1"></i>Imprimir en 58 mm
                                                </a>
                                            </div>
                                        </div>

                                        {{-- 2. Térmica 80 mm POS --}}
                                        <div class="col-sm-6">
                                            <div class="card h-100 border shadow-none p-3 bg-light">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="badge bg-info p-2 rounded me-2">
                                                        <i class="ti ti-printer fs-4 text-white"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-black">2. Térmica 80 mm (POS)</h6>
                                                        <small class="text-muted">Epson, Star, Bixolon, Xprinter</small>
                                                    </div>
                                                </div>
                                                <p class="small text-black mb-3">
                                                    Ancho estándar POS de 80 mm con doble talón (salón + retiro) y código QR ampliado para escáner rápido.
                                                </p>
                                                <a href="{{ route('iglesiaInfantil.registro.ticket', ['registro' => $registro, 'formato' => 'termica_80mm']) }}"
                                                    target="_blank" class="btn btn-sm btn-info w-100 waves-effect waves-light mt-auto">
                                                    <i class="ti ti-printer me-1"></i>Imprimir en 80 mm POS
                                                </a>
                                            </div>
                                        </div>

                                        {{-- 3. Dymo LabelWriter 450 --}}
                                        <div class="col-sm-6">
                                            <div class="card h-100 border shadow-none p-3 bg-light">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="badge bg-warning p-2 rounded me-2">
                                                        <i class="ti ti-tag fs-4 text-dark"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-black">3. Dymo 450 (Manilla)</h6>
                                                        <small class="text-muted">Etiquetas / Manilla (102×59mm)</small>
                                                    </div>
                                                </div>
                                                <p class="small text-black mb-3">
                                                    Nombre del menor en tipografía gigante para visibilidad en ropa y segunda etiqueta de retiro para el adulto.
                                                </p>
                                                <a href="{{ route('iglesiaInfantil.registro.ticket', ['registro' => $registro, 'formato' => 'dymo_450']) }}"
                                                    target="_blank" class="btn btn-sm btn-warning w-100 waves-effect waves-light mt-auto text-dark">
                                                    <i class="ti ti-printer me-1"></i>Imprimir en Dymo 450
                                                </a>
                                            </div>
                                        </div>

                                        {{-- 4. Largo 20 cm × 9 cm --}}
                                        <div class="col-sm-6">
                                            <div class="card h-100 border shadow-none p-3 bg-light">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="badge bg-success p-2 rounded me-2">
                                                        <i class="ti ti-file-text fs-4 text-white"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-black">4. Largo 20 × 9 cm</h6>
                                                        <small class="text-muted">Ficha oficial y talón de seguridad</small>
                                                    </div>
                                                </div>
                                                <p class="small text-black mb-3">
                                                    Formato vertical estilizado (90×200 mm) con ficha técnica de custodia y pase oficial de entrega para padres.
                                                </p>
                                                <a href="{{ route('iglesiaInfantil.registro.ticket', ['registro' => $registro, 'formato' => 'largo_20x9']) }}"
                                                    target="_blank" class="btn btn-sm btn-success w-100 waves-effect waves-light mt-auto">
                                                    <i class="ti ti-printer me-1"></i>Imprimir en 20 × 9 cm
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($registro->estaEnCustodia())
                        {{-- Modal cambiar sal&oacute;n/estaci&oacute;n --}}
                        <div class="modal fade" id="modalCambiarSalon{{ $registro->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Cambiar sal&oacute;n/estaci&oacute;n</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST"
                                        action="{{ route('iglesiaInfantil.registro.actualizarSalonEstacion', $registro) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-body">
                                            <p class="text-black small mb-3">
                                                <i class="ti ti-user me-1"></i>Menor: <strong>{{ $registro->menor?->nombre(3) }}</strong>
                                            </p>
                                            <div class="mb-3">
                                                <label class="form-label">Sal&oacute;n <span class="text-danger">*</span></label>
                                                <select name="salon_infantil_id" class="form-select" required>
                                                    @foreach (\App\Models\SalonInfantil::with('estaciones')->activos()->get() as $salon)
                                                        <option value="{{ $salon->id }}"
                                                            {{ $registro->salon_infantil_id == $salon->id ? 'selected' : '' }}>
                                                            {{ $salon->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Estaci&oacute;n <span class="text-danger">*</span></label>
                                                <select name="estacion_salon_infantil_id" class="form-select" required>
                                                    @foreach (\App\Models\EstacionSalonInfantil::orderBy('nombre')->get() as $est)
                                                        <option value="{{ $est->id }}"
                                                            {{ $registro->estacion_salon_infantil_id == $est->id ? 'selected' : '' }}>
                                                            {{ $est->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">Guardar cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Modal retiro --}}
                        <div class="modal fade" id="modalRetiro{{ $registro->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="ti ti-logout me-2 text-success"></i>Procesar retiro
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('iglesiaInfantil.checkin.retiro') }}"
                                        x-data="retiroPanelForm()">
                                        @csrf
                                        <input type="hidden" name="codigo_retiro" value="{{ $registro->codigo_retiro }}">
                                        <input type="hidden" name="adulto_retiro_user_id" :value="adultoId">
                                        <div class="modal-body">
                                            <div class="alert alert-info py-2 mb-3">
                                                <strong>Menor:</strong> {{ $registro->menor?->nombre(3) }}<br>
                                                <strong>Adulto que registr&oacute;:</strong> {{ $registro->adultoIngreso?->nombre(3) }}<br>
                                                <strong>C&oacute;digo:</strong> <code>{{ $registro->codigo_retiro }}</code>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Adulto que retira <span class="text-danger">*</span></label>
                                                @livewire('Usuarios.usuarios-para-busqueda', [
                                                    'queUsuariosCargar' => 'todos',
                                                    'tipoBuscador' => 'unico',
                                                    'soloVerificados' => false,
                                                    'conDadosDeBaja' => 'no',
                                                    'placeholder' => 'Buscar adulto responsable...',
                                                    'label' => ''
                                                ], 'retiro-adulto-{{ $registro->id }}')
                                                <div x-show="adultoNombre" class="alert alert-success py-2 mt-2">
                                                    <i class="ti ti-user-check me-1"></i>
                                                    Seleccionado: <strong x-text="adultoNombre"></strong>
                                                </div>
                                                <small class="text-black">Solo el adulto que hizo el registro puede retirar al menor.</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success waves-effect waves-light" :disabled="!adultoId">
                                                <i class="ti ti-check me-1"></i>Confirmar entrega
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                @empty
                    @if (!$reporteReunionId)
                        <div class="text-center py-5 text-black bg-light rounded-3 border border-dashed">
                            <i class="ti ti-calendar-event d-block mb-3" style="font-size:3.5rem; opacity: 0.5;"></i>
                            <h5 class="text-black mb-1">Sin reporte seleccionado</h5>
                            <p class="mb-0">Por favor selecciona un reporte de reunion para visualizar el listado del turno.</p>
                        </div>
                    @else
                        <div class="text-center py-5 text-black bg-light rounded-3 border border-dashed">
                            <i class="ti ti-baby-carriage d-block mb-3" style="font-size:3.5rem; opacity: 0.5;"></i>
                            <h5 class="text-black mb-1">Sin niños registrados</h5>
                            <p class="mb-0">Aun no hay menores asociados a este reporte de reunion.</p>
                        </div>
                    @endif
                @endforelse

                {{-- Paginaci&oacute;n --}}
                @if ($registros->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $registros->links() }}
                    </div>
                @endif

            </div>
        </div>

{{-- Modal del scanner QR --}}
<div class="modal fade" id="scannerRetiroModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-scan me-2"></i>Escanear QR de retiro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qrReaderRetiro" style="width:100%;" class="mx-auto"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

@endsection
