@php
    $general = $dashboard['general'];
    $abierto = $periodo->estado;
    $etiquetaAprobados = $abierto ? 'Cumplirían hoy' : 'Aprobados';
    $etiquetaRiesgo = $abierto ? 'En riesgo hoy' : 'Reprobados';
@endphp
<div class="dashboard-periodo">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge bg-label-{{ $abierto ? 'success' : 'secondary' }} rounded-pill">{{ $abierto ? 'Periodo abierto' : 'Periodo cerrado / inactivo' }}</span>
                <span class="text-muted small">Actualizado: {{ $dashboard['actualizado']->format('d/m/Y H:i') }}</span>
            </div>
            <h4 class="fw-semibold text-primary mb-1">Dashboard · {{ $periodo->nombre }}</h4>
            <p class="mb-1">{{ $periodo->escuela?->nombre ?? 'Escuela no disponible' }}</p>
            <p class="text-muted small mb-0">
                {{ $periodo->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha inicial' }} — {{ $periodo->fecha_fin?->format('d/m/Y') ?? 'Sin fecha final' }}
                · Entrega de notas: {{ $periodo->fecha_maxima_entrega_notas?->format('d/m/Y') ?? 'Sin fecha' }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('periodo.dashboard', $periodo) }}" class="btn btn-outline-primary rounded-pill"><i class="ti ti-refresh me-1"></i> Actualizar</a>
            <a href="{{ route('periodo.gestionar') }}" class="btn btn-outline-secondary rounded-pill"><i class="ti ti-arrow-left me-1"></i> Periodos</a>
        </div>
    </div>

    <div class="alert alert-{{ $abierto ? 'info' : 'secondary' }} mb-4">
        @if ($abierto)
            <strong>Proyección con lo registrado hasta ahora.</strong> Las notas pendientes aportan cero a la nota acumulada.
            Estar en riesgo significa que hoy no cumple algún criterio; el resultado puede mejorar antes del cierre.
            Las materias ya finalizadas usan su resultado guardado.
        @else
            <strong>Resultados de cierre.</strong> Se muestran los resultados guardados, sin recalcularlos.
            Las materias sin resultado definitivo figuran como «Sin evaluar».
        @endif
    </div>

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
                <div class="card h-100"><div class="card-body d-flex gap-3 align-items-start">
                    <span class="avatar rounded bg-label-{{ $color }} flex-shrink-0"><i class="ti {{ $icono }}"></i></span>
                    <div><p class="mb-1 text-muted">{{ $titulo }}</p><h3 class="mb-1">{{ number_format($valor, 0, ',', '.') }}</h3><small class="text-muted">{{ $ayuda }}</small></div>
                </div></div>
            </div>
        @endforeach
    </div>

    @if ($general['estudiantes'] === 0)
        <div class="alert alert-info">Este periodo todavía no tiene matrículas académicamente vigentes. Las materias configuradas se muestran abajo.</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-5">
            <div class="card h-100"><div class="card-body">
                <h5 class="mb-1">Distribución por género</h5>
                <p class="text-muted small mb-4">Sobre {{ $general['estudiantes'] }} estudiantes únicos.</p>
                @foreach ($general['generos'] as $genero => $cantidad)
                    @php($porcentaje = $general['estudiantes'] > 0 ? round($cantidad * 100 / $general['estudiantes'], 1) : 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1"><span>{{ $genero }}</span><strong>{{ $cantidad }} <small class="text-muted">({{ $porcentaje }}%)</small></strong></div>
                        <div class="progress" style="height: 8px" role="progressbar" aria-label="{{ $genero }}" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-{{ $loop->index === 0 ? 'primary' : ($loop->index === 1 ? 'info' : 'secondary') }}" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div></div>
        </div>
        <div class="col-12 col-lg-7">
            <div class="card h-100"><div class="card-body">
                <h5 class="mb-3">Pulso académico</h5>
                <div class="row g-3">
                    <div class="col-6"><small class="text-muted">Materias / horarios</small><h4 class="mb-0">{{ $dashboard['materias']->count() }} / {{ $dashboard['horarios'] }}</h4></div>
                    <div class="col-6"><small class="text-muted">{{ $abierto ? 'Cumplimiento actual' : 'Aprobación' }}</small><h4 class="mb-0">{{ $general['porcentajeAprobacion'] === null ? 'Sin datos' : $general['porcentajeAprobacion'].'%' }}</h4></div>
                    <div class="col-6"><small class="text-muted">Asistencia registrada</small><h4 class="mb-0">{{ $general['asistencia'] === null ? 'Sin datos' : $general['asistencia'].'%' }}</h4></div>
                    <div class="col-6"><small class="text-muted">Evaluaciones calificadas</small><h4 class="mb-0">{{ $general['avanceCalificacion'] === null ? 'No aplica' : $general['avanceCalificacion'].'%' }}</h4><small class="text-muted">{{ $general['calificadas'] }} de {{ $general['evaluaciones'] }} alumno–evaluaciones</small></div>
                </div>
                <p class="text-muted small mt-3 mb-0">La asistencia es presentes / registros de asistencia tomados, solo en materias que la controlan. No incluye clases sin reporte.</p>
            </div></div>
        </div>
    </div>

    <div class="card mb-4"><div class="card-body">
        <h5 class="mb-1">Detalle por nivel</h5>
        <p class="text-muted small">Personas únicas dentro de cada nivel. Un alumno puede aparecer en varios niveles; sus cantidades no se suman al total general.</p>
        <div class="table-responsive border rounded dashed-border">
            <table class="table table-hover mb-0">
                <thead><tr><th scope="col">Nivel</th><th scope="col">Estudiantes</th><th scope="col">Femenino</th><th scope="col">Masculino</th><th scope="col">Sin género</th><th scope="col">{{ $etiquetaAprobados }}</th><th scope="col">{{ $etiquetaRiesgo }}</th><th scope="col">Sin evaluar</th><th scope="col">Asistencia</th></tr></thead>
                <tbody>
                    @forelse ($dashboard['niveles'] as $nivel)
                        @php($resumen = $nivel['resumen'])
                        <tr><th scope="row">{{ $nivel['nombre'] }}</th><td>{{ $resumen['estudiantes'] }}</td><td>{{ $resumen['generos']['Femenino'] }}</td><td>{{ $resumen['generos']['Masculino'] }}</td><td>{{ $resumen['generos']['Sin registrar'] }}</td><td class="text-success">{{ $resumen['aprobados'] }}</td><td class="text-danger">{{ $resumen['riesgo'] }}</td><td>{{ $resumen['pendientes'] }}</td><td>{{ $resumen['asistencia'] === null ? 'Sin datos' : $resumen['asistencia'].'%' }}</td></tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No hay niveles ni materias configurados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-muted small mt-3 mb-0">Cumplir todas las materias inscritas no acredita haber terminado el plan completo de un nivel.</p>
    </div></div>

    <h5 class="mb-1">Detalle por materia</h5>
    <p class="text-muted small">Abre cada materia para consultar sus gráficos, criterios y acciones.</p>
    <div class="accordion mb-4" id="acordeon-materias-periodo">
        @forelse ($dashboard['materias'] as $materia)
            @include('contenido.paginas.escuelas.periodos.materia-dashboard-periodo', ['expandida' => $loop->first])
        @empty
            <div class="alert alert-info">Este periodo no tiene materias configuradas.</div>
        @endforelse
    </div>

    <div class="card"><div class="card-body">
        <h5 class="mb-3">Puntos de atención</h5>
        <div class="row g-3">
            @foreach ([
                'NOTA_INSUFICIENTE' => 'Nota insuficiente',
                'ASISTENCIA_INSUFICIENTE' => 'Asistencia insuficiente',
                'MATRICULA_BLOQUEADA' => 'Bloqueo de matrícula',
                'CRITERIOS_INCOMPLETOS' => 'Criterios incompletos',
                'SIN_RESULTADO_FINAL' => 'Sin resultado de cierre',
                'MATRICULA_INCONSISTENTE' => 'Materia ausente o varios horarios simultáneos',
            ] as $motivo => $etiqueta)
                <div class="col-12 col-md-6 col-xl-4"><div class="border rounded p-3 h-100"><strong class="me-2">{{ $general['motivos'][$motivo] ?? 0 }}</strong>{{ $etiqueta }}</div></div>
            @endforeach
        </div>
        <p class="text-muted small mt-3 mb-1">Se cuentan casos alumno–materia; un caso puede tener varios motivos. Los criterios de nota requieren una nota mínima y ponderaciones que sumen 100%.</p>
        <p class="text-muted small mb-0">Se excluyeron {{ $dashboard['excluidas'] }} matrículas eliminadas, anuladas, rechazadas o sin estado de pago. Las pendientes de pago vigentes sí se incluyen, como en el cierre académico. La consulta no modifica calificaciones ni finaliza el periodo.</p>
    </div></div>
</div>
