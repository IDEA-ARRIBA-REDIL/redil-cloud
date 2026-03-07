<div class="row g-2 mb-4">
    <!-- Total Grupos -->
    <div class="col-6 col-md-3 equal-height-col">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"> 
            <div>
                <h6 class="card-title text-uppercase mb-0 fw-light">Total</h6>
                <h3 class="card-title text-uppercase mb-0 fw-semibold text-black">{{ $stats['totalGrupos'] }}</h3>
            </div>
            </div>
        </div>
    </div>
    <!-- Nuevos -->
    <div class="col-6 col-md-3 equal-height-col">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"> 
            <div>
                <h6 class="card-title text-uppercase mb-0 fw-light ">Nuevos</h6>
                <h3 class="card-title text-uppercase mb-0 fw-semibold text-success">{{ $stats['gruposNuevos'] }}</h3>
            </div>
            </div>
        </div>
    </div>
    <!-- Bajas -->
    <div class="col-6 col-md-3 equal-height-col">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"> 
            <div>
                <h6 class="card-title text-uppercase mb-0 fw-light">Bajas</h6>
                <h3 class="card-title text-uppercase mb-0 fw-semibold text-danger">{{ $stats['gruposBaja'] }}</h3>
            </div>
            </div>
        </div>
    </div>
    <!-- Inactivos -->
    <div class="col-6 col-md-3 equal-height-col">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"> 
            <div>
                <h6 class="card-title text-uppercase mb-0 fw-light">Inactivos</h6>
                <h3 class="card-title text-uppercase mb-0 fw-semibold text-warning">{{ $stats['gruposInactivos'] }}</h3>
            </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Generales (Asistencias) -->
<h6 class="text-black fw-semibold text-uppercase small mb-3">Datos de asistencia</h6>
<div class="row g-2 mb-4">
    @foreach($stats['bloquesEstadisticas'] as $bloqueEst)
    <div class="col-6 col-md-4">
        <div class="card h-100 bg-opacity-10 border-0">
            <div class="card-body p-2 text-center">
                <div class="small text-dark mb-1">{{ $bloqueEst->nombre }}</div>
                <div class="h5 mb-0">{{ $bloqueEst->valor }}{{ $bloqueEst->etiqueta_tipo == 'promedio' ? '%' : '' }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Desglose por Agrupacion -->
<hr class="my-10"> 
<div class="accordion" id="accordion{{ $suffix }}">
    @foreach($stats['bloques'] as $bloque)
        @if(count($bloque->sedes) > 0)
        <div class="accordion-item mb-3 border-0 shadow-none" style="background-color: transparent !important;">   
    

            <h6 class="accordion-header border-bottom" id="heading{{ $suffix }}{{ $bloque->id }}" >
                <button class="accordion-button collapsed d-flex justify-content-between align-items-center" style="background-color: transparent !important;" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $suffix }}{{ $bloque->id }}" aria-expanded="false" aria-controls="collapse{{ $suffix }}{{ $bloque->id }}">
                    <div class="d-flex flex-column text-start">
                        <span class="fs-5 fw-semibold text-uppercase text-black">{{ $bloque->nombre }}</span>
                        <small class="text-black fw-light text-black">Total grupos: {{ $bloque->sedes->sum('grupos_activos_count') }}</small>
                    </div>
                    <i class="ti ti-chevron-down fs-4 text-black accordion-icon"></i>
                </button>
            </h6>
            
            <div id="collapse{{ $suffix }}{{ $bloque->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $suffix }}{{ $bloque->id }}" data-bs-parent="#accordion{{ $suffix }}">
                <div class="accordion-body">
                    @foreach($bloque->sedes as $sede)
                        @if($sede->grupos_activos_count > 0 || count($sede->datos_grafica) > 0)
                        <div class="mb-4 card pb-3 border m-2 rounded-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-2 p-3 py-4" style="background-color:#F9F9F9!important">
                                <span class="fw-semibold fs-6 text-black">{{ $sede->nombre }}</span>
                            </div>
                            <div class="p-3">

                                  

                                <!-- 1. KPIs por Sede (Cards) -->
                                <div class="row g-2 mb-3">
                                    <!-- Total -->
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <div class="card bg-white h-100 ">
                                            <div class="card-body p-3 ">
                                                <h6 class="text-black fw-normal text-uppercase mb-1">Total</h6>
                                                <h5 class="text-black mb-0 fw-semibold">{{ $sede->kpi_total ?? 0 }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Nuevos -->
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <div class="card bg-white h-100 ">
                                            <div class="card-body p-3 ">
                                                <h6 class="text-black fw-normal text-uppercase mb-1">Nuevos</h6>
                                                <h5 class="text-black mb-0 fw-semibold">{{ $sede->kpi_nuevos ?? 0 }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Bajas -->
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <div class="card bg-white h-100 ">
                                            <div class="card-body p-3 ">
                                                <h6 class="text-black fw-normal text-uppercase mb-1">Bajas</h6>
                                                <h5 class="text-black mb-0 fw-semibold">{{ $sede->kpi_bajas ?? 0 }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Inactivos -->
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <div class="card bg-white h-100 ">
                                            <div class="card-body p-3 ">
                                                <h6 class="text-black fw-normal text-uppercase mb-1">Inactivos</h6>
                                                <h5 class="text-black mb-0 fw-semibold">{{ $sede->kpi_inactivos ?? 0 }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                
                                </div>

                                <!-- 2. Estadísticas de Asistencia (Tabla) -->
                                @if(isset($sede->estadisticas_asistencia) && count($sede->estadisticas_asistencia) > 0)
                                <h6 class="text-black fw-semibold text-uppercase  mb-2">Datos de asistencia</h6>
                                <div class="table-responsive border rounded mb-3">
                                    <table class="table table-sm table-striped mb-0" style="font-size: 0.8rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Indicador</th>
                                                <th class="text-center"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sede->estadisticas_asistencia as $statSede)
                                            <tr>
                                                <td class="text-black">{{ $statSede->nombre }}</td>
                                                <td class="text-center fw-bold text-black">{{ $statSede->valor }}{{ $statSede->tipo_calculo == 'promedio' ? '%' : '' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                          <div class="text-muted small fst-italic py-2 border-bottom">{{ $sede->nombre }} - Sin actividad</div>
                        @endif
                    @endforeach
                </div>
            </div>


        </div>
        @endif
    @endforeach
</div>
