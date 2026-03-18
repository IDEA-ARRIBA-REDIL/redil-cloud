@extends('layouts.layoutMaster')
@section('isEscuelasModule', true)

@section('title', 'Mi Historial de Calificaciones')

@section('content')
    <h4 class="mb-1 fw-semibold text-primary">Mi Historial de Calificaciones</h4>
    <p class="text-black">Aquí puedes ver el registro completo de todas las materias que has cursado.</p>

    {{-- Paso 3: Resultados (aparece al encontrar historial) --}}
    <div class="mt-4">
        @if($historialAgrupado && $historialAgrupado->isNotEmpty())
            @foreach($historialAgrupado as $nivelId => $registros)
                @php
                    $nivelInfo = $nivelesAprobados->get($nivelId);
                    $mismoNivel = $registros->first()->materia->nivel;
                @endphp

                <div class="card mb-5 shadow-sm border-primary">
                    <div class="card-header d-flex justify-content-between align-items-center bg-label-primary p-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded bg-primary">
                                    <i class="ti ti-school ti-md"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-primary">
                                    {{ $mismoNivel ? $mismoNivel->nombre : 'Materias sin nivel definido' }}
                                </h4>
                                <small class="text-muted">Resumen de nivel educativo</small>
                            </div>
                        </div>
                        <div>
                            @if($nivelInfo)
                                @if($nivelInfo->aprobado)
                                    <span class="badge bg-success border border-success text-white px-3 py-2 fs-6">
                                        <i class="ti ti-check me-1"></i> NIVEL APROBADO
                                    </span>
                                @else
                                    <span class="badge bg-danger border border-danger text-white px-3 py-2 fs-6">
                                        <i class="ti ti-x me-1"></i> NIVEL NO APROBADO
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-warning border border-warning text-dark px-3 py-2 fs-6">
                                    <i class="ti ti-alert-triangle me-1"></i> NIVEL EN PROCESO
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4 mt-1">
                            @foreach($registros as $registro)
                                <div class="col-12 col-md-6">
                                    <div class="card border border-light h-100 shadow-none hover-shadow transition-all">
                                        <div class="card-header border-bottom d-flex p-3" style="background-color:#fcfcfc">
                                            <div class="flex-fill row">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="fw-bold text-dark m-0 d-flex align-items-center">
                                                        <span class="me-2">{{ $registro->materia->nombre }}</span>
                                                        @if($registro->aprobado)
                                                            <i class="ti ti-circle-check-filled text-success ti-xs"></i>
                                                        @else
                                                            <i class="ti ti-circle-x-filled text-danger ti-xs"></i>
                                                        @endif
                                                    </h5>
                                                    <div class="btn-group">
                                                        <a href="{{ route('escuelas.historial.exportar-boletin', $registro->id) }}" 
                                                            class="btn btn-sm btn-icon btn-outline-secondary rounded-circle" 
                                                            data-bs-toggle="tooltip" 
                                                            title="Descargar Boletín">
                                                            <i class="ti ti-file-type-pdf"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="mt-1">
                                                    @if($registro->aprobado)
                                                        <span class="badge bg-label-success p-1 rounded">Aprobado</span>
                                                    @else
                                                        <span class="badge bg-label-danger p-1 rounded">No aprobado</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row g-2 mt-1">
                                                @if($registro->es_homologacion)
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Periodo</small>
                                                        <span class="fw-medium text-dark small"><i class="ti ti-award me-1"></i>Homologación</span>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <small class="text-muted d-block">Nota Final</small>
                                                        <span class="h5 fw-bold text-primary mb-0">{{ $registro->nota_final ?? 'N/A' }}</span>
                                                    </div>
                                                @else
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Periodo</small>
                                                        <span class="fw-medium text-dark small"><i class="ti ti-calendar me-1"></i>{{ $registro->periodo->nombre }}</span>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <small class="text-muted d-block">Nota Final</small>
                                                        <span class="h5 fw-bold text-primary mb-0">{{ $registro->nota_final ?? 'N/A' }}</span>
                                                    </div>
                                                    <div class="col-12 mt-2">
                                                        <div class="d-flex justify-content-between border-top pt-2">
                                                            <div>
                                                                <small class="text-muted d-block">Aula / Sede</small>
                                                                <span class="small"><i class="ti ti-map-pin me-1"></i>{{ $registro->detalles_matricula->sede ?? 'N/A' }} ({{ $registro->detalles_matricula->aula ?? 'N/A' }})</span>
                                                            </div>
                                                            <div class="text-end">
                                                                <small class="text-muted d-block">Asistencias</small>
                                                                <span class="small badge bg-label-info">{{ $registro->total_asistencias ?? '0' }} clases</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center" role="alert">
                <i class="ti ti-info-circle me-2 ti-md"></i>
                <div>
                    Aún no cuentas con un historial académico registrado.
                </div>
            </div>
        @endif
    </div>
@endsection