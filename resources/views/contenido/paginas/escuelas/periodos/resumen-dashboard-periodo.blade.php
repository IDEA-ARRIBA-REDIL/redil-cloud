@php
    $general = $dashboard['general'];
    $abierto = $periodo->estado;
    $etiquetaAprobados = $abierto ? 'Cumplirían hoy' : 'Aprobados';
    $etiquetaRiesgo = $abierto ? 'En riesgo hoy' : 'Reprobados';
@endphp
<div class="dashboard-periodo">
    {{-- Encabezado del Dashboard --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge bg-label-{{ $abierto ? 'success' : 'secondary' }} rounded-pill px-3 py-1 fw-semibold">
                    <i class="ti ti-{{ $abierto ? 'circle-check' : 'lock' }} me-1"></i>
                    {{ $abierto ? 'Periodo abierto' : 'Periodo cerrado / inactivo' }}
                </span>
                <span class="text-muted small">Actualizado: {{ $dashboard['actualizado']->format('d/m/Y H:i') }}</span>
            </div>
            <h4 class="fw-semibold text-primary mb-1">Dashboard · {{ $periodo->nombre }}</h4>
            <p class="mb-1 text-dark fw-medium">{{ $periodo->escuela?->nombre ?? 'Escuela no disponible' }}</p>
            <p class="text-muted small mb-0">
                <i class="ti ti-calendar me-1"></i>
                {{ $periodo->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha inicial' }} — {{ $periodo->fecha_fin?->format('d/m/Y') ?? 'Sin fecha final' }}
                · Entrega de notas: {{ $periodo->fecha_maxima_entrega_notas?->format('d/m/Y') ?? 'Sin fecha' }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('periodo.dashboard', $periodo) }}" class="btn btn-outline-primary rounded-pill shadow-none">
                <i class="ti ti-refresh me-1"></i> Actualizar
            </a>
            <a href="{{ route('periodo.gestionar') }}" class="btn btn-outline-secondary rounded-pill shadow-none">
                <i class="ti ti-arrow-left me-1"></i> Volver a Periodos
            </a>
        </div>
    </div>

    {{-- Banner explicativo del estado del periodo --}}
    <div class="alert alert-{{ $abierto ? 'info' : 'secondary' }} rounded-3 mb-4 border-0 shadow-none d-flex align-items-start">
        <i class="ti ti-info-circle me-2 fs-5 flex-shrink-0 mt-1"></i>
        <div>
            @if ($abierto)
                <strong>Proyección con lo registrado hasta ahora.</strong> Las notas pendientes aportan cero a la nota acumulada.
                Estar en riesgo significa que hoy no cumple algún criterio; el resultado puede mejorar antes del cierre.
                Las materias ya finalizadas usan su resultado guardado.
            @else
                <strong>Resultados de cierre.</strong> Se muestran los resultados guardados, sin recalcularlos.
                Las materias sin resultado definitivo figuran como «Sin evaluar».
            @endif
        </div>
    </div>

    {{-- 6 Tarjetas Métricas Principales --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['Estudiantes únicos', $general['estudiantes'], 'primary', 'ti-users', 'Cada persona cuenta una sola vez'],
            ['Matrículas vigentes', $dashboard['matriculas'], 'info', 'ti-id', 'Inscripciones a materias del periodo'],
            [$etiquetaAprobados, $general['aprobados'], 'success', 'ti-circle-check', 'Cumplen todas sus materias inscritas'],
            [$etiquetaRiesgo, $general['riesgo'], 'danger', 'ti-alert-triangle', 'No cumplen al menos una materia'],
            ['Sin evaluar', $general['pendientes'], 'warning', 'ti-clock', 'Sin riesgo conocido y con datos pendientes'],
            ['Matrículas bloqueadas', $dashboard['bloqueadas'], 'secondary', 'ti-lock', 'El bloqueo impide aprobar al cierre'],
        ] as [$titulo, $valor, $color, $icono, $ayuda])
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card h-100 border shadow-none rounded-3">
                    <div class="card-body d-flex gap-3 align-items-start p-3">
                        <span class="avatar avatar-md rounded-3 bg-label-{{ $color }} flex-shrink-0 d-flex align-items-center justify-content-center">
                            <i class="ti {{ $icono }} fs-4"></i>
                        </span>
                        <div>
                            <p class="mb-0 text-muted small">{{ $titulo }}</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($valor, 0, ',', '.') }}</h3>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $ayuda }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($general['estudiantes'] === 0)
        <div class="alert alert-info rounded-3 mb-4">
            <i class="ti ti-info-circle me-1"></i> Este periodo todavía no tiene matrículas académicamente vigentes. Las materias configuradas se muestran abajo.
        </div>
    @endif

    {{-- Distribución por género y Pulso académico --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-5">
            <div class="card h-100 border shadow-none rounded-3">
                <div class="card-body p-3">
                    <h5 class="mb-1 text-dark fw-bold">Distribución por género</h5>
                    <p class="text-muted small mb-3">Sobre {{ $general['estudiantes'] }} estudiantes únicos.</p>
                    @foreach ($general['generos'] as $genero => $cantidad)
                        @php
                            $porcentaje = $general['estudiantes'] > 0 ? round($cantidad * 100 / $general['estudiantes'], 1) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span>{{ $genero }}</span>
                                <strong>{{ $cantidad }} <span class="text-muted">({{ $porcentaje }}%)</span></strong>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px" role="progressbar" aria-label="{{ $genero }}" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar rounded-pill bg-{{ $loop->index === 0 ? 'primary' : ($loop->index === 1 ? 'info' : 'secondary') }}" style="width: {{ $porcentaje }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-7">
            <div class="card h-100 border shadow-none rounded-3">
                <div class="card-body p-3">
                    <h5 class="mb-3 text-dark fw-bold">Pulso académico</h5>
                    <div class="row g-3">
                        <div class="col-6 col-sm-3">
                            <div class="border rounded-2 p-2 bg-white h-100">
                                <small class="text-muted d-block mb-1">Materias / horarios</small>
                                <h4 class="mb-0 fw-bold text-dark">{{ $dashboard['materias']->count() }} / {{ $dashboard['horarios'] }}</h4>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="border rounded-2 p-2 bg-white h-100">
                                <small class="text-muted d-block mb-1">{{ $abierto ? 'Cumplimiento actual' : 'Aprobación' }}</small>
                                <h4 class="mb-0 fw-bold text-success">{{ $general['porcentajeAprobacion'] === null ? 'Sin datos' : $general['porcentajeAprobacion'].'%' }}</h4>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="border rounded-2 p-2 bg-white h-100">
                                <small class="text-muted d-block mb-1">Asistencia</small>
                                <h4 class="mb-0 fw-bold text-info">{{ $general['asistencia'] === null ? 'Sin datos' : $general['asistencia'].'%' }}</h4>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="border rounded-2 p-2 bg-white h-100">
                                <small class="text-muted d-block mb-1">Evaluaciones</small>
                                <h4 class="mb-0 fw-bold text-primary">{{ $general['avanceCalificacion'] === null ? 'No aplica' : $general['avanceCalificacion'].'%' }}</h4>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $general['calificadas'] }}/{{ $general['evaluaciones'] }}</small>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mt-3 mb-0" style="font-size: 0.75rem;">
                        La asistencia es presentes / registros de asistencia tomados, solo en materias que la controlan. No incluye clases sin reporte. Promedio: {{ $general['notaPromedio'] !== null ? number_format($general['notaPromedio'], 2, ',', '.') : 'Sin datos' }}.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Detalle por nivel --}}
    <div class="card mb-4 border shadow-none rounded-3">
        <div class="card-body p-3">
            <h5 class="mb-1 text-dark fw-bold">Detalle por nivel</h5>
            <p class="text-muted small mb-3">Personas únicas dentro de cada nivel. Un alumno puede aparecer en varios niveles; sus cantidades no se suman al total general.</p>
            <div class="table-responsive border rounded-3 dashed-border">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nivel</th>
                            <th scope="col">Estudiantes</th>
                            <th scope="col">Femenino</th>
                            <th scope="col">Masculino</th>
                            <th scope="col">Sin género</th>
                            <th scope="col">{{ $etiquetaAprobados }}</th>
                            <th scope="col">{{ $etiquetaRiesgo }}</th>
                            <th scope="col">Sin evaluar</th>
                            <th scope="col">Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dashboard['niveles'] as $nivel)
                            @php
                                $resumen = $nivel['resumen'];
                            @endphp
                            <tr>
                                <th scope="row" class="fw-semibold">{{ $nivel['nombre'] }}</th>
                                <td><span class="badge bg-label-primary rounded-pill">{{ $resumen['estudiantes'] }}</span></td>
                                <td>{{ $resumen['generos']['Femenino'] }}</td>
                                <td>{{ $resumen['generos']['Masculino'] }}</td>
                                <td>{{ $resumen['generos']['Sin registrar'] }}</td>
                                <td class="text-success fw-bold">{{ $resumen['aprobados'] }}</td>
                                <td class="text-danger fw-bold">{{ $resumen['riesgo'] }}</td>
                                <td>{{ $resumen['pendientes'] }}</td>
                                <td>{{ $resumen['asistencia'] === null ? 'Sin datos' : $resumen['asistencia'].'%' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No hay niveles ni materias configurados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-2 mb-0" style="font-size: 0.75rem;">Cumplir todas las materias inscritas no acredita haber terminado el plan completo de un nivel.</p>
        </div>
    </div>

    {{-- SECCIÓN CENTRAL: ACORDEÓN POR CADA SEDE HABILITADA CON DETALLE POR MATERIA A TODO EL ANCHO --}}
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div>
            <h5 class="mb-0 text-primary fw-bold">
                <i class="ti ti-building me-1"></i> Detalle por sede y materia
            </h5>
            <p class="text-muted small mb-0">
                Diferenciación por cada sede habilitada. Abre cada sede para consultar el detalle por materia ocupando todo el ancho.
            </p>
        </div>
        <div class="d-flex align-items-center gap-1">
            <span class="badge bg-label-primary rounded-pill px-3 py-1">
                <i class="ti ti-building me-1"></i> {{ count($dashboard['sedes']) }} {{ count($dashboard['sedes']) === 1 ? 'sede habilitada' : 'sedes habilitadas' }}
            </span>
        </div>
    </div>

    {{-- Acordeón por Sede --}}
    <div class="accordion mb-4" id="acordeon-sedes-periodo">
        @forelse ($dashboard['sedes'] as $sede)
            @php
                $expandidaSede = $loop->first;
                $sedeId = $sede['id'] ?? 'general';
            @endphp
            <div class="accordion-item card mb-3 border shadow-none rounded-3 overflow-hidden">
                <h3 class="accordion-header" id="titulo-sede-{{ $sedeId }}">
                    <button class="accordion-button {{ $expandidaSede ? '' : 'collapsed' }} py-3" type="button" data-bs-toggle="collapse" data-bs-target="#detalle-sede-{{ $sedeId }}" aria-expanded="{{ $expandidaSede ? 'true' : 'false' }}" aria-controls="detalle-sede-{{ $sedeId }}">
                        <div class="d-flex flex-wrap align-items-center justify-content-between w-100 pe-3 gap-2">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-sm rounded-circle bg-label-primary me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-building"></i>
                                </span>
                                <div>
                                    <strong class="fs-6 text-dark me-2">{{ $sede['nombre'] }}</strong>
                                    <span class="badge bg-label-secondary rounded-pill small">{{ count($sede['materias']) }} {{ count($sede['materias']) === 1 ? 'materia' : 'materias' }}</span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-1 ms-auto">
                                <span class="badge bg-label-primary rounded-pill px-2 py-1" title="Estudiantes matriculados en esta sede">
                                    <i class="ti ti-users me-1"></i><strong>{{ $sede['estudiantes'] }}</strong> Matriculados
                                </span>
                                <span class="badge bg-label-success rounded-pill px-2 py-1" title="Aprobados o cumplirían hoy">
                                    <i class="ti ti-circle-check me-1"></i><strong>{{ $sede['aprobados'] }}</strong> {{ $abierto ? 'Cumplirían' : 'Aprobados' }}
                                </span>
                                <span class="badge bg-label-danger rounded-pill px-2 py-1" title="Reprobados o van a perder">
                                    <i class="ti ti-alert-triangle me-1"></i><strong>{{ $sede['van_a_perder'] }}</strong> {{ $abierto ? 'Van a perder' : 'Reprobados' }}
                                </span>
                                <span class="badge bg-label-secondary rounded-pill px-2 py-1" title="Matrículas bloqueadas en esta sede">
                                    <i class="ti ti-lock me-1"></i><strong>{{ $sede['bloqueados'] }}</strong> Bloqueados
                                </span>
                                @if($sede['sin_evaluar'] > 0)
                                    <span class="badge bg-label-warning rounded-pill px-2 py-1" title="Matrículas sin evaluar en esta sede">
                                        <i class="ti ti-clock me-1"></i><strong>{{ $sede['sin_evaluar'] }}</strong> Sin evaluar
                                    </span>
                                @endif
                            </div>
                        </div>
                    </button>
                </h3>
                <div id="detalle-sede-{{ $sedeId }}" class="accordion-collapse collapse {{ $expandidaSede ? 'show' : '' }}" aria-labelledby="titulo-sede-{{ $sedeId }}">
                    <div class="accordion-body p-3 bg-white">
                        @if ($sede['estudiantes'] === 0 && count($sede['materias']) === 0)
                            <div class="alert alert-light border rounded-3 text-center py-3 my-2 text-muted">
                                <i class="ti ti-info-circle me-1"></i> Esta sede no tiene horarios ni matrículas activas en este periodo.
                            </div>
                        @else
                            {{-- Detalle por materia a todo el ancho dentro de la sede --}}
                            @forelse ($sede['materias'] as $materia)
                                @php
                                    $procesando = $materia['estadoCierre'] === 'procesando';
                                    $tituloAprobados = $materia['cerrada'] ? 'Aprobados' : 'Cumplirían hoy';
                                    $tituloRiesgo = $materia['cerrada'] ? 'Reprobados' : 'Van a perder';
                                @endphp
                                <div class="card border rounded-3 mb-3 bg-white shadow-none w-100">
                                    <div class="card-body p-3">
                                        {{-- Encabezado de la Materia --}}
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                <span class="avatar avatar-sm rounded-circle bg-label-warning flex-shrink-0 d-flex align-items-center justify-content-center">
                                                    <i class="ti ti-book"></i>
                                                </span>
                                                <div>
                                                    <h5 class="mb-0 fw-bold text-dark">{{ $materia['nombre'] }}</h5>
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span class="badge bg-label-secondary rounded-pill small">{{ $materia['nivel'] }}</span>
                                                        <span class="text-muted small">
                                                            <i class="ti ti-clock me-1"></i>{{ $materia['horariosCount'] }} {{ $materia['horariosCount'] === 1 ? 'horario' : 'horarios' }} en esta sede
                                                        </span>
                                                        <span class="badge rounded-pill bg-label-{{ $procesando ? 'warning' : ($materia['finalizada'] ? 'success' : 'info') }}">
                                                            {{ $procesando ? 'Cierre en proceso' : ($materia['finalizada'] ? 'Materia cerrada' : 'Materia abierta') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Acciones de la Materia --}}
                                            <div class="d-flex flex-wrap gap-2 ms-auto">
                                                @if ($abierto && ! $procesando)
                                                    <form method="POST" action="{{ route($materia['finalizada'] ? 'periodo.dashboard.reabrir-materia' : 'periodo.dashboard.cerrar-materia', [$periodo, $materia['id']]) }}" class="accion-cierre-materia" data-reabrir="{{ $materia['finalizada'] ? '1' : '0' }}" data-materia="{{ $materia['nombre'] }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-{{ $materia['finalizada'] ? 'outline-warning' : 'primary' }} rounded-pill shadow-none">
                                                            <i class="ti ti-{{ $materia['finalizada'] ? 'lock-open' : 'checks' }} me-1"></i>{{ $materia['finalizada'] ? 'Reabrir materia' : ($materia['estadoCierre'] === 'error' ? 'Reintentar cierre' : 'Cerrar y calcular aprobaciones') }}
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (! $abierto && $materia['finalizada'] && ! $procesando)
                                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill shadow-none" data-bs-toggle="modal" data-bs-target="#informe-materia-{{ $materia['id'] }}" @disabled(empty($materia['sedes']))>
                                                        <i class="ti ti-file-spreadsheet me-1"></i> Descargar Excel
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- 5 Indicadores Clave Solicitados por el Cliente (Ocupando todo el ancho) --}}
                                        <div class="row g-2 mb-3">
                                            {{-- 1. Número de Matriculados --}}
                                            <div class="col-6 col-sm-4 col-md">
                                                <div class="border rounded-2 p-2 text-center bg-white h-100">
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="ti ti-users text-primary me-1"></i> Matriculados
                                                    </small>
                                                    <h3 class="mb-0 fw-bold text-primary">{{ $materia['matriculados'] }}</h3>
                                                </div>
                                            </div>
                                            {{-- 2. Cuántos Aprobados --}}
                                            <div class="col-6 col-sm-4 col-md">
                                                <div class="border rounded-2 p-2 text-center bg-white h-100">
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="ti ti-circle-check text-success me-1"></i> {{ $tituloAprobados }}
                                                    </small>
                                                    <h3 class="mb-0 fw-bold text-success">{{ $materia['aprobados'] }}</h3>
                                                </div>
                                            </div>
                                            {{-- 3. Cuántos Bloqueados --}}
                                            <div class="col-6 col-sm-4 col-md">
                                                <div class="border rounded-2 p-2 text-center bg-white h-100">
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="ti ti-lock text-secondary me-1"></i> Bloqueados
                                                    </small>
                                                    <h3 class="mb-0 fw-bold text-secondary">{{ $materia['bloqueados'] }}</h3>
                                                </div>
                                            </div>
                                            {{-- 4. Cuántos Van a Perder --}}
                                            <div class="col-6 col-sm-4 col-md">
                                                <div class="border rounded-2 p-2 text-center bg-white h-100">
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="ti ti-alert-triangle text-danger me-1"></i> {{ $tituloRiesgo }}
                                                    </small>
                                                    <h3 class="mb-0 fw-bold text-danger">{{ $materia['van_a_perder'] }}</h3>
                                                </div>
                                            </div>
                                            {{-- 5. Sin Evaluar --}}
                                            <div class="col-6 col-sm-4 col-md">
                                                <div class="border rounded-2 p-2 text-center bg-white h-100">
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="ti ti-clock text-warning me-1"></i> Sin evaluar
                                                    </small>
                                                    <h3 class="mb-0 fw-bold text-warning">{{ $materia['sin_evaluar'] }}</h3>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Barra Visual de Resultados y Criterios Académicos --}}
                                        @php
                                            $totalMat = $materia['matriculados'];
                                            $pctAprobados = $totalMat > 0 ? round(($materia['aprobados'] * 100) / $totalMat, 1) : 0;
                                            $pctPerder = $totalMat > 0 ? round(($materia['van_a_perder'] * 100) / $totalMat, 1) : 0;
                                            $pctSinEvaluar = $totalMat > 0 ? round(($materia['sin_evaluar'] * 100) / $totalMat, 1) : 0;
                                        @endphp
                                        <div class="p-3 mb-3 rounded-3 bg-white border">
                                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                                                <div class="small">
                                                    <span class="text-muted">Nota mínima:</span> <strong>{{ $materia['evaluaNotas'] ? ($materia['notaMinima'] ?? 'Sin configurar') : 'No aplica' }}</strong>
                                                    <span class="text-muted ms-2">· Asistencias mínimas:</span> <strong>{{ $materia['asistenciasMinimas'] ?? 'No aplica' }}</strong>
                                                    <span class="text-muted ms-2">· Nota promedio:</span> <strong class="text-primary">{{ $materia['notaPromedio'] === null ? 'No disponible' : number_format($materia['notaPromedio'], 2, ',', '.') }}</strong>
                                                    <span class="text-muted ms-2">· Asistencia:</span> <strong>{{ $materia['asistencia'] === null ? 'Sin datos' : $materia['asistencia'].'%' }}</strong>
                                                </div>
                                                <div class="small">
                                                    <span class="text-muted">Evaluaciones:</span> <strong>{{ $materia['calificadas'] }} / {{ $materia['evaluaciones'] }} ({{ $materia['avanceCalificacion'] ?? 0 }}%)</strong>
                                                </div>
                                            </div>
                                            @if($totalMat > 0)
                                                <div class="progress rounded-pill" style="height: 10px;" role="progressbar" title="Verde: Aprobados ({{ $pctAprobados }}%) | Rojo: Van a perder ({{ $pctPerder }}%) | Amarillo: Sin evaluar ({{ $pctSinEvaluar }}%)">
                                                    <div class="progress-bar bg-success" style="width: {{ $pctAprobados }}%" title="{{ $pctAprobados }}% Aprobados"></div>
                                                    <div class="progress-bar bg-danger" style="width: {{ $pctPerder }}%" title="{{ $pctPerder }}% Van a perder"></div>
                                                    <div class="progress-bar bg-warning" style="width: {{ $pctSinEvaluar }}%" title="{{ $pctSinEvaluar }}% Sin evaluar"></div>
                                                </div>
                                                <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 0.72rem;">
                                                    <span class="text-success fw-semibold"><i class="ti ti-circle-filled text-success" style="font-size: 0.5rem;"></i> {{ $pctAprobados }}% {{ $tituloAprobados }}</span>
                                                    <span class="text-danger fw-semibold"><i class="ti ti-circle-filled text-danger" style="font-size: 0.5rem;"></i> {{ $pctPerder }}% {{ $tituloRiesgo }}</span>
                                                    <span class="text-warning fw-semibold"><i class="ti ti-circle-filled text-warning" style="font-size: 0.5rem;"></i> {{ $pctSinEvaluar }}% Sin evaluar</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Sección expandible de Gráficos Apex y Horarios --}}
                                        @if($materia['matriculados'] > 0 || count($materia['horarios']) > 0)
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <button class="btn btn-xs btn-outline-secondary rounded-pill shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#desglose-materia-{{ $sedeId }}-{{ $materia['id'] }}" aria-expanded="false">
                                                    <i class="ti ti-chart-bar me-1"></i> Ver gráficos y horarios de esta sede <i class="ti ti-chevron-down ms-1"></i>
                                                </button>
                                                @if($procesando)
                                                    <span class="text-muted small"><i class="ti ti-loader me-1"></i> Calculando cierre...</span>
                                                @endif
                                            </div>

                                            <div class="collapse mt-3" id="desglose-materia-{{ $sedeId }}-{{ $materia['id'] }}">
                                                <div class="border rounded-3 p-3 bg-white">
                                                    @if(count($materia['horarios']) > 0)
                                                        <h6 class="fw-bold mb-2 text-dark"><i class="ti ti-clock me-1 text-primary"></i> Horarios en {{ $sede['nombre'] }}:</h6>
                                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                                            @foreach($materia['horarios'] as $h)
                                                                <span class="badge bg-white border text-dark p-2 rounded-2">
                                                                    <strong>{{ $h['aula'] }}</strong> · {{ $h['dia'] }} ({{ $h['horario'] }})
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    @if($materia['matriculados'] > 0)
                                                        @php
                                                            $noBloqueadas = max(0, $materia['matriculados'] - $materia['bloqueados']);
                                                        @endphp
                                                        <div class="row g-3">
                                                            <div class="col-12 col-sm-6 col-xl-3">
                                                                <h6 class="small fw-bold mb-1">Resultados académicos</h6>
                                                                <div id="grafico-resultados-{{ $sedeId }}-{{ $materia['id'] }}" class="apex-chart-container" data-aprobados="{{ $materia['aprobados'] }}" data-riesgo="{{ $materia['van_a_perder'] }}" data-pendientes="{{ $materia['sin_evaluar'] }}" data-cerrada="{{ $materia['cerrada'] ? '1' : '0' }}"></div>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-xl-3">
                                                                <h6 class="small fw-bold mb-1">Estado de bloqueo</h6>
                                                                <div id="grafico-bloqueo-{{ $sedeId }}-{{ $materia['id'] }}" class="apex-chart-container" data-nobloqueadas="{{ $noBloqueadas }}" data-bloqueadas="{{ $materia['bloqueados'] }}"></div>
                                                                <p class="text-muted small mt-1" style="font-size: 0.75rem;">No bloqueadas: {{ $noBloqueadas }} · Bloqueadas: {{ $materia['bloqueados'] }}</p>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-xl-3">
                                                                <h6 class="small fw-bold mb-1">Distribución por género</h6>
                                                                <div id="grafico-generos-{{ $sedeId }}-{{ $materia['id'] }}" class="apex-chart-container" data-femenino="{{ $materia['generos']['Femenino'] ?? 0 }}" data-masculino="{{ $materia['generos']['Masculino'] ?? 0 }}" data-sin="{{ $materia['generos']['Sin registrar'] ?? 0 }}"></div>
                                                                <p class="text-muted small mt-1" style="font-size: 0.75rem;">Femenino: {{ $materia['generos']['Femenino'] ?? 0 }} · Masculino: {{ $materia['generos']['Masculino'] ?? 0 }}</p>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-xl-3">
                                                                <h6 class="small fw-bold mb-1">Asistencias registradas</h6>
                                                                <div id="grafico-asistencias-{{ $sedeId }}-{{ $materia['id'] }}" class="apex-chart-container" data-presentes="{{ $materia['presentes'] }}" data-ausentes="{{ $materia['ausentes'] }}"></div>
                                                                <p class="text-muted small mt-1" style="font-size: 0.75rem;">{{ $materia['promedioAsistencias'] === null ? 'No hay registros de asistencia.' : $materia['promedioAsistencias'].' asistencias por alumno en promedio.' }}</p>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <p class="text-muted small mb-0">Sin estudiantes matriculados en esta sede para graficar.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-light border rounded-3 text-center py-3 my-2 text-muted">
                                    <i class="ti ti-info-circle me-1"></i> No hay materias configuradas para esta sede.
                                </div>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">No se encontraron sedes habilitadas en este periodo.</div>
        @endforelse
    </div>

    {{-- Puntos de atención --}}
    <div class="card border shadow-none rounded-3 mb-4">
        <div class="card-body p-3">
            <h5 class="mb-3 text-dark fw-bold">Puntos de atención</h5>
            <div class="row g-3">
                @foreach ([
                    'NOTA_INSUFICIENTE' => 'Nota insuficiente',
                    'ASISTENCIA_INSUFICIENTE' => 'Asistencia insuficiente',
                    'MATRICULA_BLOQUEADA' => 'Bloqueo de matrícula',
                    'CRITERIOS_INCOMPLETOS' => 'Criterios incompletos',
                    'SIN_RESULTADO_FINAL' => 'Sin resultado de cierre',
                    'MATRICULA_INCONSISTENTE' => 'Materia ausente o varios horarios simultáneos',
                ] as $motivo => $etiqueta)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="border rounded-2 p-3 h-100 bg-white d-flex align-items-center">
                            <span class="badge bg-{{ ($general['motivos'][$motivo] ?? 0) > 0 ? 'danger' : 'secondary' }} rounded-circle p-2 me-3 fs-6">
                                {{ $general['motivos'][$motivo] ?? 0 }}
                            </span>
                            <span class="fw-medium text-dark">{{ $etiqueta }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="text-muted small mt-3 mb-1" style="font-size: 0.75rem;">Se cuentan casos alumno–materia; un caso puede tener varios motivos. Los criterios de nota requieren una nota mínima y ponderaciones que sumen 100%.</p>
            <p class="text-muted small mb-0" style="font-size: 0.75rem;">Se excluyeron {{ $dashboard['excluidas'] }} matrículas eliminadas, anuladas, rechazadas o sin estado de pago. Las pendientes de pago vigentes sí se incluyen, como en el cierre académico. La consulta no modifica calificaciones ni finaliza el periodo.</p>
        </div>
    </div>

    {{-- Modales de Descarga de Excel por Sede (cuando periodo y materia estén cerrados) --}}
    @if (! $abierto)
        @foreach ($dashboard['materias'] as $matGlobal)
            @if ($matGlobal['finalizada'])
                <div class="modal fade" id="informe-materia-{{ $matGlobal['id'] }}" tabindex="-1" aria-labelledby="titulo-informe-{{ $matGlobal['id'] }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="GET" action="{{ route('periodo.dashboard.informe-materia', [$periodo, $matGlobal['id']]) }}">
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title" id="titulo-informe-{{ $matGlobal['id'] }}">
                                        <i class="ti ti-file-spreadsheet text-success me-1"></i> Informe · {{ $matGlobal['nombre'] }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label fw-semibold" for="sede-informe-{{ $matGlobal['id'] }}">Sede de los horarios</label>
                                    <select class="form-select" id="sede-informe-{{ $matGlobal['id'] }}" name="sede_id" required>
                                        <option value="">Selecciona una sede</option>
                                        @foreach ($matGlobal['sedes'] as $sedeOpt)
                                            <option value="{{ $sedeOpt['id'] }}">{{ $sedeOpt['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-muted small mt-3 mb-0">El archivo tendrá una hoja por horario de esta sede, con la nota acumulada final, las asistencias finales y el resultado de cada estudiante. La sede corresponde al aula del horario, no a la sede personal del alumno.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success rounded-pill">
                                        <i class="ti ti-download me-1"></i> Descargar Excel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
