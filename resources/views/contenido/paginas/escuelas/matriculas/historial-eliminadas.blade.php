@php
    $configData = Helper::appClasses();
@endphp

@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Historial de Matrículas Eliminadas')

@section('vendor-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary">
                <i class="ti ti-history me-2"></i>Historial de Matrículas Eliminadas / Canceladas
            </h4>
            <p class="mb-0 text-muted">Auditoría contable y registro histórico de matrículas canceladas por la sonda o por administradores.</p>
        </div>
        <a href="{{ route('matriculas.gestionar', $usuarioActivo->id) }}" class="btn btn-outline-primary rounded-pill">
            <i class="ti ti-plus me-1"></i> Nueva Matrícula
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- TARJETA DE FILTROS Y BÚSQUEDA MULTI-CAMPO -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('matriculas.historialEliminadas', $usuarioActivo->id) }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="inputBuscar" class="form-label fw-semibold text-dark">
                        <i class="ti ti-search me-1 text-primary"></i>Buscar Estudiante, ID o Periodo
                    </label>
                    <input type="text" id="inputBuscar" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Escribe nombre, cédula, #ID matrícula o periodo...">
                </div>

                <div class="col-md-4">
                    <label for="selectPeriodo" class="form-label fw-semibold text-dark">
                        <i class="ti ti-calendar me-1 text-primary"></i>Filtrar por Periodo
                    </label>
                    <select id="selectPeriodo" name="periodo_id" class="form-select">
                        <option value="">-- Todos los Periodos --</option>
                        @foreach ($periodos as $per)
                            <option value="{{ $per->id }}" @selected($periodoId == $per->id)>
                                {{ $per->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                        <i class="ti ti-filter me-1"></i> Filtrar
                    </button>
                    @if ($buscar || $periodoId)
                        <a href="{{ route('matriculas.historialEliminadas', $usuarioActivo->id) }}" class="btn btn-label-secondary rounded-pill" title="Limpiar Filtros">
                            <i class="ti ti-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- TARJETA PRINCIPAL DEL HISTORIAL -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-label-secondary border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold text-dark">
                <i class="ti ti-file-x me-2 text-danger"></i>Registros de Cancelación ({{ $matriculasEliminadas->total() }})
            </h5>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Estudiante</th>
                        <th>Escuela / Materia</th>
                        <th>Periodo & Horario</th>
                        <th>Fecha Eliminación</th>
                        <th>Eliminado Por</th>
                        <th>Compra / Pago</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($matriculasEliminadas as $mat)
                        @php
                            $materia = $mat->horarioMateriaPeriodo?->materiaPeriodo?->materia?->nombre ?? 'N/A';
                            $periodo = $mat->periodo?->nombre ?? 'N/A';
                            $aula = $mat->horarioMateriaPeriodo?->horarioBase?->aula?->nombre ?? 'N/A';
                            $deletedUser = $mat->deletedBy?->nombre(3) ?? ($mat->deleted_by ? 'ID #'.$mat->deleted_by : 'Sistema / Sonda');
                            $estudianteNombre = $mat->user?->nombre(3) ?? 'Usuario Eliminado';
                            $estudianteDoc = $mat->user?->identificacion ?? 'S/D';
                            $valorFormat = number_format($mat->valor_pagado ?? $mat->valor_a_pagar ?? 0, 0, ',', '.');
                        @endphp
                        <tr>
                            <td>
                                <span class="badge bg-label-dark font-monospace">#{{ $mat->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                                            {{ strtoupper(substr($estudianteNombre, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark font-weight-bold" style="font-size: 0.9rem;">
                                            {{ $estudianteNombre }}
                                        </h6>
                                        <small class="text-muted"><i class="ti ti-id me-1"></i>{{ $estudianteDoc }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-dark font-size-14 d-block">{{ $materia }}</span>
                                <small class="text-muted"><i class="ti ti-school me-1"></i>{{ $mat->escuela?->nombre ?? 'Escuelas' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-label-info mb-1">{{ $periodo }}</span>
                                <small class="d-block text-muted"><i class="ti ti-building me-1"></i>Aula: {{ $aula }}</small>
                            </td>
                            <td>
                                <span class="fw-bold text-dark d-block">
                                    {{ $mat->deleted_at ? $mat->deleted_at->format('d/m/Y h:i A') : 'N/A' }}
                                </span>
                                <small class="text-muted">
                                    {{ $mat->deleted_at ? $mat->deleted_at->diffForHumans() : '' }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-danger me-1"><i class="ti ti-user-x me-1"></i></span>
                                    <span class="small font-weight-bold text-dark">{{ $deletedUser }}</span>
                                </div>
                            </td>
                            <td>
                                @if ($mat->referencia_pago || $mat->valor_pagado > 0 || $mat->estado_pago_matricula === 'pagada')
                                    <span class="badge bg-label-success mb-1">
                                        <i class="ti ti-receipt-tax me-1"></i>${{ $valorFormat }}
                                    </span>
                                    @if ($mat->referencia_pago)
                                        <small class="d-block text-muted font-monospace">Ref: {{ $mat->referencia_pago }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-label-secondary">
                                        <i class="ti ti-x me-1"></i>Sin Pago (Borrador)
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <img src="{{ asset('assets/img/illustrations/boy-working-light.png') }}" alt="No data" width="130" class="mb-3">
                                <h6 class="text-dark fw-bold mb-1">No hay matrículas eliminadas</h6>
                                <p class="text-muted mb-0 small">El historial se encuentra limpio. Todas las matrículas activas están intactas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($matriculasEliminadas->hasPages())
            <div class="card-footer border-top py-3">
                <div class="d-flex justify-content-end">
                    {{ $matriculasEliminadas->links() }}
                </div>
            </div>
        @endif
    </div>

@endsection
