@php
    $general = $dashboard['general'];
    $abierto = $periodo->estado;
    $charts = $dashboard['charts'];
    $sedes = $dashboard['sedes'];
@endphp

<div class="dashboard-horarios">
    {{-- Encabezado del Dashboard de Horarios --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge bg-label-{{ $abierto ? 'success' : 'secondary' }} rounded-pill px-3 py-1 fw-semibold">
                    <i class="ti ti-{{ $abierto ? 'circle-check' : 'lock' }} me-1"></i>
                    {{ $abierto ? 'Periodo abierto' : 'Periodo cerrado / inactivo' }}
                </span>
                <span class="badge bg-label-primary rounded-pill px-3 py-1 fw-semibold">
                    <i class="ti ti-calendar-time me-1"></i> Dashboard de Horarios
                </span>
                <span class="text-muted small">Actualizado: {{ $dashboard['actualizado']->format('d/m/Y H:i') }}</span>
            </div>
            <h4 class="fw-semibold text-primary mb-1">Horarios · {{ $periodo->nombre }}</h4>
            <p class="mb-1 text-dark fw-medium">{{ $periodo->escuela?->nombre ?? 'Escuela no disponible' }}</p>
            <p class="text-muted small mb-0">
                <i class="ti ti-calendar me-1"></i>
                {{ $periodo->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha inicial' }} — {{ $periodo->fecha_fin?->format('d/m/Y') ?? 'Sin fecha final' }}
                · Entrega de notas: {{ $periodo->fecha_maxima_entrega_notas?->format('d/m/Y') ?? 'Sin fecha' }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            {{-- Botón para exportar Excel de Horarios --}}
            <a href="{{ route('periodo.exportarHorarios', $periodo) }}" class="btn btn-outline-success rounded-pill shadow-none" title="Descargar archivo Excel con todos los horarios del periodo">
                <i class="ti ti-file-spreadsheet me-1"></i> Descargar Excel de horarios
            </a>
            <a href="{{ route('periodo.dashboard', $periodo) }}" class="btn btn-outline-primary rounded-pill shadow-none">
                <i class="ti ti-chart-bar me-1"></i> Dashboard académico
            </a>
            <a href="{{ route('periodo.gestionar') }}" class="btn btn-outline-secondary rounded-pill shadow-none">
                <i class="ti ti-arrow-left me-1"></i> Volver a Periodos
            </a>
        </div>
    </div>

    {{-- Banner Informativo --}}
    <div class="alert alert-info rounded-3 mb-4 border-0 shadow-none d-flex align-items-start">
        <i class="ti ti-info-circle me-2 fs-5 flex-shrink-0 mt-1"></i>
        <div>
            <strong>Consolidado general de horarios y aulas.</strong> Aquí puedes auditar la disponibilidad de horarios por materia, la ocupación de cupos y la asignación docente por sede. Usa el acordeón inferior para explorar el detalle por sede y materia.
        </div>
    </div>

    {{-- 6 Tarjetas Métricas Principales --}}
    <div class="row g-3 mb-4">
        {{-- 1. Horarios Totales --}}
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card h-100 border shadow-none rounded-3 bg-white">
                <div class="card-body d-flex gap-3 align-items-start p-3">
                    <span class="avatar avatar-md rounded-3 bg-label-primary flex-shrink-0 d-flex align-items-center justify-content-center">
                        <i class="ti ti-calendar-time fs-4"></i>
                    </span>
                    <div>
                        <p class="mb-0 text-muted small">Horarios totales</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ number_format($general['totalHorarios'], 0, ',', '.') }}</h3>
                        <small class="text-muted" style="font-size: 0.72rem;">Instancias configuradas en el periodo</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Horarios Habilitados vs Inactivos --}}
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card h-100 border shadow-none rounded-3 bg-white">
                <div class="card-body d-flex gap-3 align-items-start p-3">
                    <span class="avatar avatar-md rounded-3 bg-label-success flex-shrink-0 d-flex align-items-center justify-content-center">
                        <i class="ti ti-checks fs-4"></i>
                    </span>
                    <div>
                        <p class="mb-0 text-muted small">Estado de horarios</p>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($general['horariosHabilitados'], 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">activos</span></h3>
                        <small class="text-muted" style="font-size: 0.72rem;">{{ $general['horariosInactivos'] }} inactivos o deshabilitados</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Materias con Horario --}}
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card h-100 border shadow-none rounded-3 bg-white">
                <div class="card-body d-flex gap-3 align-items-start p-3">
                    <span class="avatar avatar-md rounded-3 bg-label-info flex-shrink-0 d-flex align-items-center justify-content-center">
                        <i class="ti ti-book fs-4"></i>
                    </span>
                    <div>
                        <p class="mb-0 text-muted small">Materias con horario</p>
                        <h3 class="mb-0 fw-bold text-info">{{ number_format($general['totalMaterias'], 0, ',', '.') }}</h3>
                        <small class="text-muted" style="font-size: 0.72rem;">Materias con al menos un horario</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Sedes con Oferta --}}
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card h-100 border shadow-none rounded-3 bg-white">
                <div class="card-body d-flex gap-3 align-items-start p-3">
                    <span class="avatar avatar-md rounded-3 bg-label-warning flex-shrink-0 d-flex align-items-center justify-content-center">
                        <i class="ti ti-building-community fs-4"></i>
                    </span>
                    <div>
                        <p class="mb-0 text-muted small">Sedes con horarios</p>
                        <h3 class="mb-0 fw-bold text-warning">{{ number_format($general['totalSedes'], 0, ',', '.') }}</h3>
                        <small class="text-muted" style="font-size: 0.72rem;">Sedes físicas con aulas asignadas</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Estudiantes Matriculados --}}
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card h-100 border shadow-none rounded-3 bg-white">
                <div class="card-body d-flex gap-3 align-items-start p-3">
                    <span class="avatar avatar-md rounded-3 bg-label-danger flex-shrink-0 d-flex align-items-center justify-content-center">
                        <i class="ti ti-users fs-4"></i>
                    </span>
                    <div>
                        <p class="mb-0 text-muted small">Estudiantes matriculados</p>
                        <h3 class="mb-0 fw-bold text-danger">{{ number_format($general['totalInscritos'], 0, ',', '.') }}</h3>
                        <small class="text-muted" style="font-size: 0.72rem;">Inscripciones vigentes en estos horarios</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. Capacidad y Ocupación --}}
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card h-100 border shadow-none rounded-3 bg-white">
                <div class="card-body d-flex gap-3 align-items-start p-3">
                    <span class="avatar avatar-md rounded-3 bg-label-secondary flex-shrink-0 d-flex align-items-center justify-content-center">
                        <i class="ti ti-chart-pie fs-4"></i>
                    </span>
                    <div>
                        <p class="mb-0 text-muted small">Capacidad total</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ number_format($general['capacidadTotal'], 0, ',', '.') }} <span class="fs-6 text-primary fw-normal">({{ $general['porcentajeOcupacion'] }}%)</span></h3>
                        <small class="text-muted" style="font-size: 0.72rem;">Cupos totales definidos en aulas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sección de Gráficos Globales --}}
    @if ($general['totalHorarios'] > 0)
        {{-- Fila 1: Horarios por materia y Distribución por día (50% / 50%) --}}
        <div class="row g-4 mb-4">
            {{-- Gráfico 1: Horarios por Materia --}}
            <div class="col-12 col-lg-6">
                <div class="card h-100 border shadow-none rounded-3 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">Horarios por materia</h5>
                                <p class="text-muted small mb-0">Cantidad de horarios configurados para cada materia.</p>
                            </div>
                            <span class="badge bg-label-primary rounded-pill">{{ count($charts['materias']['labels']) }} materias</span>
                        </div>
                        <div id="grafico-horarios-por-materia"
                             data-labels="{{ json_encode($charts['materias']['labels']) }}"
                             data-series="{{ json_encode($charts['materias']['series']) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráfico 2: Días de la semana --}}
            <div class="col-12 col-lg-6">
                <div class="card h-100 border shadow-none rounded-3 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">Distribución por día de la semana</h5>
                                <p class="text-muted small mb-0">Frecuencia de clases programadas de lunes a domingo.</p>
                            </div>
                            <span class="badge bg-label-info rounded-pill">7 días</span>
                        </div>
                        <div id="grafico-horarios-dias"
                             data-labels="{{ json_encode($charts['dias']['labels']) }}"
                             data-series="{{ json_encode($charts['dias']['series']) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fila 2: Distribución y Proporción por Sede a TODO EL ANCHO (col-12) --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border shadow-none rounded-3 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">Distribución y proporción de horarios por sede</h5>
                                <p class="text-muted small mb-0">Comparativa general de horarios asignados a cada sede y el porcentaje que representa sobre el total del periodo ({{ $general['totalHorarios'] }} horarios en {{ $general['totalSedes'] }} sedes).</p>
                            </div>
                            <span class="badge bg-label-primary rounded-pill">{{ count($charts['sedes']['labels']) }} sedes</span>
                        </div>
                        <div id="grafico-horarios-sedes"
                             data-labels="{{ json_encode($charts['sedes']['labels']) }}"
                             data-series="{{ json_encode($charts['sedes']['series']) }}"
                             data-porcentajes="{{ json_encode($charts['sedes']['porcentajes'] ?? []) }}"
                             data-inscritos="{{ json_encode($charts['sedes']['inscritos'] ?? []) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Sección Principal: Diferenciación de Horarios por Sede --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 mt-4">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Diferenciación de horarios por sede</h4>
            <p class="text-muted small mb-0">Explora la oferta de materias, aulas, docentes asignados y cupos en cada sede habilitada.</p>
        </div>
        <span class="badge bg-label-primary rounded-pill px-3 py-1">
            {{ count($sedes) }} {{ count($sedes) === 1 ? 'sede con horarios' : 'sedes con horarios' }}
        </span>
    </div>

    <div class="accordion mb-4" id="acordeon-sedes-horarios">
        @forelse ($sedes as $index => $sede)
            @php
                $expandidaSede = $index === 0;
                $sedeId = $sede['id'];
            @endphp
            <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-none bg-white">
                <h3 class="accordion-header" id="titulo-sede-horario-{{ $sedeId }}">
                    <button class="accordion-button {{ $expandidaSede ? '' : 'collapsed' }} bg-white py-3 px-3 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#detalle-sede-horario-{{ $sedeId }}" aria-expanded="{{ $expandidaSede ? 'true' : 'false' }}" aria-controls="detalle-sede-horario-{{ $sedeId }}">
                        <div class="d-flex flex-wrap align-items-center justify-content-between w-100 gap-2 me-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-sm rounded-circle bg-label-primary flex-shrink-0 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-building-community"></i>
                                </span>
                                <div>
                                    <span class="fw-bold text-dark fs-6">{{ $sede['nombre'] }}</span>
                                    <span class="badge bg-label-secondary rounded-pill small ms-2">
                                        {{ count($sede['materias']) }} {{ count($sede['materias']) === 1 ? 'materia' : 'materias' }}
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-label-primary rounded-pill px-2 py-1" title="Cantidad de horarios configurados">
                                    <i class="ti ti-calendar-time me-1"></i><strong>{{ $sede['totalHorarios'] }}</strong> Horarios
                                </span>
                                <span class="badge bg-label-info rounded-pill px-2 py-1" title="Estudiantes matriculados en esta sede">
                                    <i class="ti ti-users me-1"></i><strong>{{ $sede['totalInscritos'] }}</strong> Inscritos
                                </span>
                                <span class="badge bg-label-secondary rounded-pill px-2 py-1" title="Capacidad total de cupos en aulas">
                                    <i class="ti ti-chair_director me-1"></i><strong>{{ $sede['capacidadTotal'] }}</strong> Cupos ({{ $sede['porcentajeOcupacion'] }}%)
                                </span>
                            </div>
                        </div>
                    </button>
                </h3>
                <div id="detalle-sede-horario-{{ $sedeId }}" class="accordion-collapse collapse {{ $expandidaSede ? 'show' : '' }}" aria-labelledby="titulo-sede-horario-{{ $sedeId }}">
                    <div class="accordion-body p-3 bg-white">
                        @if ($sede['totalHorarios'] === 0)
                            <div class="alert alert-light border rounded-3 text-center py-3 my-2 text-muted">
                                <i class="ti ti-info-circle me-1"></i> Esta sede no tiene horarios configurados en este periodo.
                            </div>
                        @else
                            {{-- Proporción de Horarios y Ocupación en esta Sede --}}
                            <div class="border rounded-3 p-3 bg-white mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="ti ti-chart-pie text-primary me-1"></i> Proporción de horarios y ocupación en {{ $sede['nombre'] }}
                                    </h6>
                                    <div class="small">
                                        <span class="badge bg-label-primary rounded-pill">{{ $sede['totalHorarios'] }} {{ $sede['totalHorarios'] === 1 ? 'horario' : 'horarios' }}</span>
                                        <span class="badge bg-label-info rounded-pill ms-1">{{ $sede['porcentajeDelTotal'] }}% del total del periodo</span>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    {{-- Gráfico Sede 1: Días con clases en esta sede --}}
                                    <div class="col-12 col-md-4">
                                        <div class="border rounded-2 p-2 bg-white h-100">
                                            <span class="small fw-bold text-dark d-block mb-1">Días con clases</span>
                                            <div id="grafico-sede-dias-{{ $sedeId }}" class="apex-chart-container"
                                                 data-labels="{{ json_encode($sede['chartDias']['labels']) }}"
                                                 data-series="{{ json_encode($sede['chartDias']['series']) }}">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Gráfico Sede 2: Horarios por materia en esta sede --}}
                                    <div class="col-12 col-md-4">
                                        <div class="border rounded-2 p-2 bg-white h-100">
                                            <span class="small fw-bold text-dark d-block mb-1">Horarios por materia</span>
                                            <div id="grafico-sede-materias-{{ $sedeId }}" class="apex-chart-container"
                                                 data-labels="{{ json_encode($sede['chartMaterias']['labels']) }}"
                                                 data-series="{{ json_encode($sede['chartMaterias']['series']) }}">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Gráfico Sede 3: Ocupación de cupos en esta sede --}}
                                    <div class="col-12 col-md-4">
                                        <div class="border rounded-2 p-2 bg-white h-100">
                                            <span class="small fw-bold text-dark d-block mb-1">Ocupación de cupos</span>
                                            @php
                                                $cuposLibres = max(0, $sede['capacidadTotal'] - $sede['totalInscritos']);
                                            @endphp
                                            <div id="grafico-sede-ocupacion-{{ $sedeId }}" class="apex-chart-container"
                                                 data-inscritos="{{ $sede['totalInscritos'] }}"
                                                 data-disponibles="{{ $cuposLibres }}">
                                            </div>
                                            <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 0.73rem;">
                                                <span>Inscritos: <strong>{{ $sede['totalInscritos'] }}</strong></span>
                                                <span>Capacidad: <strong>{{ $sede['capacidadTotal'] }}</strong> ({{ $sede['porcentajeOcupacion'] }}%)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Desglose por materia dentro de la sede a todo el ancho --}}
                            @foreach ($sede['materias'] as $mat)
                                <div class="card border rounded-3 mb-3 bg-white shadow-none w-100">
                                    <div class="card-body p-3">
                                        {{-- Encabezado de la materia dentro de la sede --}}
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar avatar-sm rounded-circle bg-label-warning flex-shrink-0 d-flex align-items-center justify-content-center">
                                                    <i class="ti ti-book"></i>
                                                </span>
                                                <div>
                                                    <h5 class="mb-0 fw-bold text-dark">{{ $mat['nombre'] }}</h5>
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span class="badge bg-label-secondary rounded-pill small">{{ $mat['nivel'] }}</span>
                                                        <span class="text-muted small">
                                                            <i class="ti ti-calendar-time me-1"></i>{{ $mat['totalHorarios'] }} {{ $mat['totalHorarios'] === 1 ? 'horario' : 'horarios' }} en {{ $sede['nombre'] }}
                                                        </span>
                                                        <span class="badge bg-label-info rounded-pill small">
                                                            <i class="ti ti-users me-1"></i>{{ $mat['totalInscritos'] }} inscritos
                                                        </span>
                                                        @if ($mat['capacidadTotal'] > 0)
                                                            <span class="badge bg-label-secondary rounded-pill small">
                                                                Capacidad: {{ $mat['capacidadTotal'] }} cupos
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Tabla de Horarios de la Materia --}}
                                        <div class="table-responsive border rounded-3">
                                            <table class="table table-hover mb-0 align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="min-width: 170px;">Día y Horario</th>
                                                        <th style="min-width: 140px;">Aula / Espacio</th>
                                                        <th style="min-width: 180px;">Inscritos / Capacidad</th>
                                                        <th style="min-width: 220px;">Maestro(s) Asignado(s)</th>
                                                        <th class="text-center" style="min-width: 100px;">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($mat['horarios'] as $h)
                                                        @php
                                                            $pctOcup = $h['capacidad'] > 0 ? min(100, round(($h['inscritos'] * 100) / $h['capacidad'], 1)) : 0;
                                                        @endphp
                                                        <tr>
                                                            {{-- Día y Horario --}}
                                                            <td>
                                                                <span class="badge bg-label-primary rounded-pill px-2 py-1 mb-1 d-inline-block">
                                                                    <i class="ti ti-calendar me-1"></i>{{ $h['dia'] }}
                                                                </span>
                                                                <div class="fw-semibold text-dark small">
                                                                    <i class="ti ti-clock me-1 text-muted"></i>{{ $h['horarioTexto'] }}
                                                                </div>
                                                            </td>

                                                            {{-- Aula --}}
                                                            <td>
                                                                <div class="fw-bold text-dark">{{ $h['aula'] }}</div>
                                                                <span class="badge bg-label-secondary rounded-pill small" style="font-size: 0.72rem;">
                                                                    {{ $h['tipoAula'] }}
                                                                </span>
                                                            </td>

                                                            {{-- Inscritos y Capacidad --}}
                                                            <td>
                                                                <div class="d-flex justify-content-between small mb-1">
                                                                    <strong class="text-primary">{{ $h['inscritos'] }} inscritos</strong>
                                                                    <span class="text-muted">{{ $h['capacidad'] > 0 ? $h['capacidad'] . ' cupos' : 'Sin límite' }}</span>
                                                                </div>
                                                                @if ($h['capacidad'] > 0)
                                                                    <div class="progress rounded-pill" style="height: 6px;" title="{{ $pctOcup }}% ocupación">
                                                                        <div class="progress-bar bg-{{ $pctOcup >= 100 ? 'danger' : ($pctOcup >= 80 ? 'warning' : 'primary') }}" style="width: {{ $pctOcup }}%"></div>
                                                                    </div>
                                                                @endif
                                                            </td>

                                                            {{-- Maestros --}}
                                                            <td>
                                                                @if (count($h['maestros']) > 0)
                                                                    <div class="d-flex flex-column gap-1">
                                                                        @foreach ($h['maestros'] as $doc)
                                                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                                                                <span class="fw-medium text-dark small">
                                                                                    <i class="ti ti-user-check text-success me-1"></i>{{ $doc['nombre'] }}
                                                                                </span>
                                                                                <div class="d-flex align-items-center gap-1">
                                                                                    @if (! empty($doc['telefono']))
                                                                                        @php
                                                                                            $telLimpio = preg_replace('/[^0-9]/', '', $doc['telefono']);
                                                                                        @endphp
                                                                                        <a href="https://wa.me/{{ $telLimpio }}" target="_blank" class="btn btn-xs btn-outline-success p-1 rounded-circle" title="Contactar por WhatsApp: {{ $doc['telefono'] }}">
                                                                                            <i class="ti ti-brand-whatsapp"></i>
                                                                                        </a>
                                                                                    @endif
                                                                                    @if (! empty($doc['email']))
                                                                                        <a href="mailto:{{ $doc['email'] }}" class="btn btn-xs btn-outline-secondary p-1 rounded-circle" title="Enviar correo: {{ $doc['email'] }}">
                                                                                            <i class="ti ti-mail"></i>
                                                                                        </a>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <span class="badge bg-label-warning rounded-pill small">
                                                                        <i class="ti ti-alert-triangle me-1"></i> Sin maestro asignado
                                                                    </span>
                                                                @endif
                                                            </td>

                                                            {{-- Estado --}}
                                                            <td class="text-center">
                                                                <span class="badge rounded-pill bg-label-{{ $h['habilitado'] ? 'success' : 'secondary' }}">
                                                                    <i class="ti ti-{{ $h['habilitado'] ? 'check' : 'x' }} me-1"></i>
                                                                    {{ $h['habilitado'] ? 'Habilitado' : 'Inactivo' }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info rounded-3">
                <i class="ti ti-info-circle me-1"></i> No se encontraron horarios registrados en este periodo.
            </div>
        @endforelse
    </div>
</div>
