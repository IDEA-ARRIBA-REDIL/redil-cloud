@php
    $resumen = $materia['resumen'];
    $procesando = $materia['estadoCierre'] === 'procesando';
    $tituloAprobados = $materia['cerrada'] ? 'Aprobados' : 'Cumplirían hoy';
    $tituloRiesgo = $materia['cerrada'] ? 'Reprobados' : 'En riesgo hoy';
@endphp
<div class="accordion-item card mb-3">
    <h3 class="accordion-header" id="titulo-materia-{{ $materia['id'] }}">
        <button class="accordion-button {{ $expandida ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#detalle-materia-{{ $materia['id'] }}" aria-expanded="{{ $expandida ? 'true' : 'false' }}" aria-controls="detalle-materia-{{ $materia['id'] }}">
            <span class="d-flex flex-wrap align-items-center gap-2 pe-3">
                <strong>{{ $materia['nombre'] }}</strong>
                <span class="text-muted small">{{ $materia['nivel'] }} · {{ $resumen['estudiantes'] }} estudiantes · {{ $materia['horarios'] }} horarios</span>
                <span class="badge rounded-pill bg-label-{{ $procesando ? 'warning' : ($materia['finalizada'] ? 'success' : 'info') }}">{{ $procesando ? 'Cierre en proceso' : ($materia['finalizada'] ? 'Materia cerrada' : 'Materia abierta') }}</span>
            </span>
        </button>
    </h3>
    <div id="detalle-materia-{{ $materia['id'] }}" class="accordion-collapse collapse {{ $expandida ? 'show' : '' }}" aria-labelledby="titulo-materia-{{ $materia['id'] }}">
        <div class="accordion-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <p class="text-muted small mb-0">
                    Nota mínima: <strong>{{ $materia['evaluaNotas'] ? ($materia['notaMinima'] ?? 'Sin configurar') : 'No aplica' }}</strong>
                    · Asistencias mínimas: <strong>{{ $materia['asistenciasMinimas'] ?? 'No aplica / sin configurar' }}</strong>
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @if ($abierto && ! $procesando)
                        <form method="POST" action="{{ route($materia['finalizada'] ? 'periodo.dashboard.reabrir-materia' : 'periodo.dashboard.cerrar-materia', [$periodo, $materia['id']]) }}" class="accion-cierre-materia" data-reabrir="{{ $materia['finalizada'] ? '1' : '0' }}" data-materia="{{ $materia['nombre'] }}">
                            @csrf
                            <button type="submit" class="btn btn-{{ $materia['finalizada'] ? 'outline-warning' : 'primary' }} rounded-pill">
                                <i class="ti ti-{{ $materia['finalizada'] ? 'lock-open' : 'checks' }} me-1"></i>{{ $materia['finalizada'] ? 'Reabrir materia' : ($materia['estadoCierre'] === 'error' ? 'Reintentar cierre' : 'Cerrar y calcular aprobaciones') }}
                            </button>
                        </form>
                    @endif
                    @if (! $abierto && $materia['finalizada'] && ! $procesando)
                        <button type="button" class="btn btn-outline-success rounded-pill" data-bs-toggle="modal" data-bs-target="#informe-materia-{{ $materia['id'] }}" @disabled(empty($materia['sedes']))><i class="ti ti-file-spreadsheet me-1"></i> Descargar Excel por sede</button>
                    @endif
                </div>
            </div>
            @if ($procesando)
                <div class="alert alert-info">Se está calculando el cierre en segundo plano. Usa «Actualizar» para comprobar si terminó.</div>
            @elseif ($materia['estadoCierre'] === 'error' && ! $materia['finalizada'])
                <div class="alert alert-danger">No se pudo completar el cierre. Revisa los criterios y las matrículas de esta materia antes de reintentarlo.</div>
            @endif
            @if (! $abierto)
                <p class="text-muted small">Para cerrar o reabrir materias, primero debes reabrir el periodo. El Excel estará disponible cuando esta materia tenga su cierre completo.</p>
            @else
                <p class="text-muted small">La descarga de Excel se habilitará cuando el periodo esté cerrado.</p>
            @endif
            <div class="row g-3 mb-4">
                @foreach ([[$tituloAprobados, $resumen['aprobados'], 'success'], [$tituloRiesgo, $resumen['riesgo'], 'danger'], ['Sin evaluar', $resumen['pendientes'], 'warning'], ['Nota promedio', $resumen['notaPromedio'] === null ? 'No disponible' : number_format($resumen['notaPromedio'], 2, ',', '.'), 'primary']] as [$titulo, $valor, $color])
                    <div class="col-6 col-lg-3"><div class="border rounded p-3 h-100"><small class="text-muted d-block">{{ $titulo }}</small><strong class="fs-4 text-{{ $color }}">{{ $valor }}</strong></div></div>
                @endforeach
            </div>
            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <h6>Resultados académicos</h6>
                    <div id="grafico-resultados-{{ $materia['id'] }}" role="img" aria-label="{{ $tituloAprobados }}: {{ $resumen['aprobados'] }}. {{ $tituloRiesgo }}: {{ $resumen['riesgo'] }}. Sin evaluar: {{ $resumen['pendientes'] }}."></div>
                    @if ($resumen['estudiantes'] === 0)<p class="text-muted">Sin estudiantes matriculados.</p>@endif
                </div>
                <div class="col-12 col-lg-4">
                    <h6>Distribución por género</h6>
                    <div id="grafico-generos-{{ $materia['id'] }}" role="img" aria-label="Femenino: {{ $resumen['generos']['Femenino'] }}. Masculino: {{ $resumen['generos']['Masculino'] }}. Sin registrar: {{ $resumen['generos']['Sin registrar'] }}."></div>
                    <p class="text-muted small">Femenino: {{ $resumen['generos']['Femenino'] }} · Masculino: {{ $resumen['generos']['Masculino'] }} · Sin registrar: {{ $resumen['generos']['Sin registrar'] }}</p>
                </div>
                <div class="col-12 col-lg-4">
                    <h6>Asistencias e inasistencias registradas</h6>
                    <div id="grafico-asistencias-{{ $materia['id'] }}" role="img" aria-label="Asistencias: {{ $resumen['presentes'] }}. Inasistencias: {{ $resumen['ausentes'] }}."></div>
                    <p class="mb-1"><strong>{{ $resumen['asistencia'] === null ? 'Sin datos' : $resumen['asistencia'].'% de asistencia' }}</strong></p>
                    <p class="text-muted small">{{ $resumen['promedioAsistencias'] === null ? 'No hay registros de asistencia.' : $resumen['promedioAsistencias'].' asistencias por alumno en promedio.' }} Solo cuenta registros tomados.</p>
                </div>
            </div>
            <div class="border-top pt-3 mt-3">
                <div class="d-flex justify-content-between gap-2"><span>Evaluaciones calificadas</span><strong>{{ $resumen['calificadas'] }} / {{ $resumen['evaluaciones'] }} ({{ $resumen['avanceCalificacion'] ?? 0 }}%)</strong></div>
                <div class="progress mt-2" style="height: 8px" role="progressbar" aria-label="Evaluaciones calificadas" aria-valuenow="{{ $resumen['avanceCalificacion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar bg-primary" style="width: {{ $resumen['avanceCalificacion'] ?? 0 }}%"></div></div>
            </div>
        </div>
    </div>
</div>
@if (! $abierto && $materia['finalizada'] && ! $procesando)
    <div class="modal fade" id="informe-materia-{{ $materia['id'] }}" tabindex="-1" aria-labelledby="titulo-informe-{{ $materia['id'] }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <form method="GET" action="{{ route('periodo.dashboard.informe-materia', [$periodo, $materia['id']]) }}">
                <div class="modal-header"><h5 class="modal-title" id="titulo-informe-{{ $materia['id'] }}">Informe · {{ $materia['nombre'] }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                <div class="modal-body">
                    <label class="form-label" for="sede-informe-{{ $materia['id'] }}">Sede de los horarios</label>
                    <select class="form-select" id="sede-informe-{{ $materia['id'] }}" name="sede_id" required>
                        <option value="">Selecciona una sede</option>
                        @foreach ($materia['sedes'] as $sede)<option value="{{ $sede['id'] }}">{{ $sede['nombre'] }}</option>@endforeach
                    </select>
                    <p class="text-muted small mt-3 mb-0">El archivo tendrá una hoja por horario de esta sede, con la nota acumulada final, las asistencias finales y el resultado de cada estudiante. La sede corresponde al aula del horario, no a la sede personal del alumno.</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success rounded-pill">Descargar Excel</button></div>
            </form>
        </div></div>
    </div>
@endif
