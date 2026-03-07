@extends('layouts/layoutMaster')

@section('title', 'Solicitudes de anulación')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/js/app.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])

    <script>
        $(document).ready(function() {
            $('.fecha-picker').flatpickr({
                mode: "range",
                dateFormat: "Y-m-d",
                disableMobile: true,
                   allowClear: true
            });
              $('.select2').select2({

                   dateFormat: "Y-m-d",
                disableMobile: true,
                    allowClear: true
                });
        });


    </script>

@endsection

@section('content')
    <h4 class="text-primary fw-bold py-3 mb-4">
        Solicitudes de anulación
    </h4>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filtros de búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('taquilla.listarSolicitudesAnulacion') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Caja</label>
                        <select name="caja_id" class="form-select select2">
                            <option value="">Todas</option>
                            @foreach ($cajas as $caja)
                                <option value="{{ $caja->id }}" {{ request('caja_id') == $caja->id ? 'selected' : '' }}>
                                    {{ $caja->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Punto de pago</label>
                        <select name="punto_pago_id" class="form-select select2">
                            <option value="">Todos</option>
                            @foreach ($puntosPago as $punto)
                                <option value="{{ $punto->id }}"
                                    {{ request('punto_pago_id') == $punto->id ? 'selected' : '' }}>
                                    {{ $punto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha</label>
                        <input type="text" name="fecha" class="form-control  fecha-picker" placeholder="Seleccionar rango"
                            value="{{ request('fecha') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="busqueda" class="form-control" placeholder="Nombre o Cédula"
                            value="{{ request('busqueda') }}">
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-outline-secondary rounded-pill"><i
                                class="ti ti-trash me-1"></i>Limpiar</button>
                        <a href="{{ route('taquilla.listarSolicitudesAnulacion') }}"
                            class="btn btn-primary rounded-pill me-2"><i class="ti ti-search me-1"></i>Buscar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row equal-height-row g-4">
        @forelse ($solicitudes as $solicitud)
            <div class="col equal-height-col col-lg-4 col-md-6 col-sm-12">
                <div class="card h-100 border rounded p-0">
                    <div class="card-header">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="d-flex flex-column">
                                <h5 class="mb-0 fw-semibold text-black lh-sm"
                                    title="{{ $solicitud->nombre_completo_comprador }}">
                                    {{ $solicitud->nombre_completo_comprador }}
                                </h5>
                                <small class="text-dark mt-1">Recibo #{{ $solicitud->id }}</small>
                            </div>
                            <span class="badge bg-warning text-white">Pendiente anulación</span>
                        </div>

                        <div class="d-flex flex-row mt-3">
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-id me-2"></i>Identificación:</small>
                                <small class="fw-semibold text-black">{{ $solicitud->identificacion_comprador }}</small>
                            </div>
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-calendar-event me-2"></i>Fecha compra:</small>
                                <small class="fw-semibold text-black">
                                    {{ $solicitud->created_at->format('d/m/Y h:i A') }}
                                </small>
                            </div>
                        </div>

                        <div class="d-flex flex-row mt-3">
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-box me-2"></i>Taquilla:</small>
                                <small class="fw-semibold text-black">
                                    {{ $solicitud->pagos->first()->caja->nombre ?? 'N/A' }}
                                </small>
                            </div>
                            <div class="d-flex flex-column col-12 col-md-6">
                                <small class="text-dark"><i class="ti ti-user me-2"></i>Asesor:</small>
                                <small class="fw-semibold text-black">
                                    {{ $solicitud->pagos->first()->caja->usuario->name ?? 'N/A' }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row justify-content-between mb-2">
                            <div class="d-flex flex-row mt-3">
                                <div class="d-flex flex-column col-12 col-md-6">
                                    <small class="text-dark"><i class="ti ti-activity me-2"></i>Actividad:</small>
                                    <small class="fw-semibold text-black"
                                        title="{{ $solicitud->actividad->nombre ?? 'N/A' }}">
                                        {{ $solicitud->actividad->nombre ?? 'Actividad no encontrada' }}
                                    </small>
                                </div>
                                <div class="d-flex flex-column col-12 col-md-6">
                                    <small class="text-dark"><i class="ti ti-currency-dollar me-2"></i>Valor:</small>
                                    <small class="fw-semibold text-black">
                                        ${{ number_format($solicitud->valor, 0, ',', '.') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-danger mt-3 mb-0" role="alert">
                            <h6 class="alert-heading mb-1"><i class="ti ti-alert-circle me-2"></i>Motivo de anulación:</h6>
                            @php
                                $parts = explode('| MOTIVO_ANULACION: ', $solicitud->listado_carrito);
                                $motivo = count($parts) > 1 ? end($parts) : 'Sin motivo especificado';
                            @endphp
                            <p class="mb-0 small">{{ $motivo }}</p>

                        </div>
                    </div>

                    <div class="card-footer pt-5 border-top d-flex justify-content-between gap-2">
                        {{-- Botón Rechazar --}}
                        <form action="{{ route('taquilla.rechazarAnulacion', $solicitud->id) }}" method="POST"
                            class="w-50 form-rechazar">
                            @csrf
                            <button type="button" class="btn btn-outline-secondary rounded-pill w-100 btn-rechazar">
                                <i class="ti ti-x me-1"></i> Rechazar
                            </button>
                        </form>

                        {{-- Botón Autorizar --}}
                        <form action="{{ route('taquilla.autorizarAnulacion', $solicitud->id) }}" method="POST"
                            class="w-50 form-autorizar">
                            @csrf
                            <button type="button" class="btn btn-primary rounded-pill w-100 btn-autorizar">
                                <i class="ti ti-check me-1"></i> Autorizar
                                <input type="text" name="motivo_anulacion" class="d-none" value="{{ $motivo }}">
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center p-5">
                        <div class="alert alert-success text-center" role="alert">
                            <i class="ti ti-check-circle me-2"></i>
                            No hay solicitudes de anulación pendientes.
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="row mt-4">
        <div class="col-12">
            {{ $solicitudes->links() }}
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // Confirmación para Rechazar
            $('.btn-rechazar').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: '¿Rechazar anulación?',
                    text: "La compra volverá a estado 'Pagado' y se eliminará la solicitud.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, rechazar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill me-3',
                        cancelButton: 'btn btn-outline-secondary rounded-pill'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Confirmación para Autorizar
            $('.btn-autorizar').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: '¿Autorizar anulación?',
                    text: "Se eliminarán permanentemente los registros y se devolverán los cupos.",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, autorizar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill me-3',
                        cancelButton: 'btn btn-outline-secondary rounded-pill'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
