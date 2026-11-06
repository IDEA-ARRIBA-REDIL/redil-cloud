<div>
    {{-- Formulario de Filtros --}}
    <form id="reporte-form" action="{{ route('reporteEscuela.exportarResumen') }}" method="POST">
        @csrf
        <div class="row g-3">
            {{-- Fila 1: Periodo y Semana --}}
            <div class="col-12 col-md-6">
                <label for="periodo" class="form-label fw-semibold">1. Selecciona un periodo </label>
                <select id="periodo" class="form-select" wire:model.live="periodoSeleccionado" name="periodoSeleccionado" required>
                    <option value="">Seleccionar periodo...</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6">
                <label for="semana" class="form-label fw-semibold">2. Selecciona una semana </label>
                <select id="semana" class="form-select" wire:model.live="semanaSeleccionada" name="semanaSeleccionada" required @if(empty($semanas)) disabled @endif>
                     <option value="">Seleccionar semana...</option>
                     @foreach($semanas as $semana)
                        <option value="{{ $semana['valor'] }}">{{ $semana['texto'] }}</option>
                     @endforeach
                </select>
            </div>

            {{-- Fila 2: Sedes y Materia --}}
            <div class="col-12 col-md-12" wire:ignore>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="select-sedes" class="form-label fw-semibold mb-0">3. Selecciona sedes </label>
                    <small>
                        <a href="javascript:;" id="select-all-sedes" class="text-primary fw-medium">Todas</a> |
                        <a href="javascript:;" id="deselect-all-sedes" class="text-muted fw-medium">Ninguna</a>
                    </small>
                </div>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <select id="select-sedes" class="form-select" name="sedesSeleccionadas[]" multiple required></select>
                    </div>
                </div>
            </div>

            {{-- Fila 3: Materias (MÚLTIPLE) --}}
            <div class="col-12 col-md-12" wire:ignore>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="select-materias" class="form-label fw-semibold mb-0">4. Selecciona materias </label>
                    <small>
                        <a href="javascript:;" id="select-all-materias" class="text-primary fw-medium">Todas</a> |
                        <a href="javascript:;" id="deselect-all-materias" class="text-muted fw-medium">Ninguna</a>
                    </small>
                </div>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <select id="select-materias" class="form-select" name="materiasSeleccionadas[]" multiple required></select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fila de Botones de Acción (Consultar + Exportar Excel) --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mt-4 pt-2 border-top">
            {{-- Botón Opción A: Consultar y graficar dentro de la vista --}}
            <button type="button" wire:click="consultarReporte" class="btn btn-primary rounded-pill mb-2" @disabled(! $periodoSeleccionado || empty($sedesSeleccionadas) || empty($materiasSeleccionadas) || ! $semanaSeleccionada)>
                <span wire:loading.remove wire:target="consultarReporte">
                    <i class="ti ti-chart-pie me-1"></i> Consultar y graficar
                </span>
                <span wire:loading wire:target="consultarReporte">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Calculando asistencia...
                </span>
            </button>

            {{-- Botones de Exportar a Excel (se conservan) --}}
            <button type="submit" class="btn btn-outline-success rounded-pill action-btn mb-2" data-action="{{ route('reporteEscuela.exportarResumen') }}" @disabled(! $periodoSeleccionado || empty($sedesSeleccionadas) || empty($materiasSeleccionadas) || ! $semanaSeleccionada) title="Descargar archivo Excel con el resumen consolidado por sede">
                <i class="ti ti-file-spreadsheet me-1"></i> Exportar resumen por sede
            </button>

            <button type="submit" class="btn btn-outline-secondary rounded-pill action-btn mb-2" data-action="{{ route('reporteEscuela.exportarReporte') }}" @disabled(! $periodoSeleccionado || empty($sedesSeleccionadas) || empty($materiasSeleccionadas) || ! $semanaSeleccionada) title="Descargar archivo Excel detallado">
                <i class="ti ti-file-spreadsheet me-1"></i> Exportar informe detallado
            </button>
        </div>
    </form>

    {{-- Indicador de carga durante el cálculo --}}
    <div wire:loading wire:target="consultarReporte" class="w-100 py-4 text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Calculando métricas de asistencia...</span>
        </div>
        <p class="text-muted small mt-2 mb-0">Consultando reportes de asistencia en sedes y horarios seleccionados...</p>
    </div>

    {{-- Sección de Resultados y Gráficos --}}
    @if ($reporteConsultado)
        <div class="mt-4 pt-3 border-top" wire:loading.remove wire:target="consultarReporte">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="ti ti-chart-bar text-primary me-1"></i> Resultados de Asistencia
                    </h5>
                    <span class="text-muted small">Semana evaluada: <strong>{{ $infoSemanaTexto }}</strong></span>
                </div>
                <span class="badge bg-label-success rounded-pill px-3 py-1 fs-6">
                    <i class="ti ti-percentage me-1"></i>{{ $granTotalReporte['porcentajeAsistencia'] }}% de asistencia general
                </span>
            </div>

            {{-- 6 Tarjetas Métricas de Resumen --}}
            <div class="row g-3 mb-4">
                {{-- 1. Total Matrículas --}}
                <div class="col-6 col-sm-4 col-xl-2">
                    <div class="card h-100 border shadow-none rounded-3 bg-white text-center p-2">
                        <small class="text-muted d-block mb-1">
                            <i class="ti ti-users text-primary me-1"></i> Matrículas
                        </small>
                        <h4 class="mb-0 fw-bold text-primary">{{ number_format($granTotalReporte['totalMatriculas'], 0, ',', '.') }}</h4>
                    </div>
                </div>

                {{-- 2. Asistió --}}
                <div class="col-6 col-sm-4 col-xl-2">
                    <div class="card h-100 border shadow-none rounded-3 bg-white text-center p-2">
                        <small class="text-muted d-block mb-1">
                            <i class="ti ti-circle-check text-success me-1"></i> Asistió
                        </small>
                        <h4 class="mb-0 fw-bold text-success">{{ number_format($granTotalReporte['asistio'], 0, ',', '.') }}</h4>
                    </div>
                </div>

                {{-- 3. No Asistió --}}
                <div class="col-6 col-sm-4 col-xl-2">
                    <div class="card h-100 border shadow-none rounded-3 bg-white text-center p-2">
                        <small class="text-muted d-block mb-1">
                            <i class="ti ti-circle-x text-danger me-1"></i> No asistió
                        </small>
                        <h4 class="mb-0 fw-bold text-danger">{{ number_format($granTotalReporte['ausente'], 0, ',', '.') }}</h4>
                    </div>
                </div>

                {{-- 4. Sin Reporte --}}
                <div class="col-6 col-sm-4 col-xl-2">
                    <div class="card h-100 border shadow-none rounded-3 bg-white text-center p-2">
                        <small class="text-muted d-block mb-1">
                            <i class="ti ti-clock-pause text-warning me-1"></i> Sin reporte
                        </small>
                        <h4 class="mb-0 fw-bold text-warning">{{ number_format($granTotalReporte['no_registrado'], 0, ',', '.') }}</h4>
                    </div>
                </div>

                {{-- 5. Deserciones --}}
                <div class="col-6 col-sm-4 col-xl-2">
                    <div class="card h-100 border shadow-none rounded-3 bg-white text-center p-2">
                        <small class="text-muted d-block mb-1">
                            <i class="ti ti-user-x text-secondary me-1"></i> Deserciones
                        </small>
                        <h4 class="mb-0 fw-bold text-secondary">{{ number_format($granTotalReporte['desercion'], 0, ',', '.') }}</h4>
                    </div>
                </div>

                {{-- 6. % Asistencia --}}
                <div class="col-6 col-sm-4 col-xl-2">
                    <div class="card h-100 border shadow-none rounded-3 bg-white text-center p-2">
                        <small class="text-muted d-block mb-1">
                            <i class="ti ti-activity text-info me-1"></i> Cumplimiento
                        </small>
                        <h4 class="mb-0 fw-bold text-info">{{ $granTotalReporte['porcentajeAsistencia'] }}%</h4>
                    </div>
                </div>
            </div>

            {{-- Fila de Gráficos ApexCharts --}}
            <div class="row g-3 mb-4">
                {{-- Gráfico 1: Dona de Estado General --}}
                <div class="col-12 col-lg-4">
                    <div class="card h-100 border shadow-none rounded-3 bg-white">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1 text-dark">Distribución general de asistencia</h6>
                            <p class="text-muted small mb-2">Comportamiento global de las matrículas en la semana.</p>
                            <div id="grafico-asistencia-general"
                                 wire:ignore
                                 data-series="{{ json_encode($graficoGeneral['series'] ?? []) }}"
                                 data-labels="{{ json_encode($graficoGeneral['labels'] ?? []) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gráfico 2: Asistencia por Sede --}}
                <div class="col-12 col-lg-4">
                    <div class="card h-100 border shadow-none rounded-3 bg-white">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1 text-dark">Asistencia por sede</h6>
                            <p class="text-muted small mb-2">Comparativa de asistencias vs inasistencias por sede.</p>
                            <div id="grafico-asistencia-sedes"
                                 wire:ignore
                                 data-categories="{{ json_encode($graficoSedes['categories'] ?? []) }}"
                                 data-asistio="{{ json_encode($graficoSedes['asistio'] ?? []) }}"
                                 data-ausente="{{ json_encode($graficoSedes['ausente'] ?? []) }}"
                                 data-noreporte="{{ json_encode($graficoSedes['no_registrado'] ?? []) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gráfico 3: Cumplimiento por Materia --}}
                <div class="col-12 col-lg-4">
                    <div class="card h-100 border shadow-none rounded-3 bg-white">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1 text-dark">Porcentaje de asistencia por materia</h6>
                            <p class="text-muted small mb-2">Tasa de cumplimiento de asistencia por cada materia.</p>
                            <div id="grafico-asistencia-materias"
                                 wire:ignore
                                 data-categories="{{ json_encode($graficoMaterias['categories'] ?? []) }}"
                                 data-porcentajes="{{ json_encode($graficoMaterias['porcentajes'] ?? []) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla / Desglose por Sede y Materia --}}
            <div class="card border shadow-none rounded-3 bg-white mb-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2 text-dark">Desglose detallado por sede y materia</h6>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sede</th>
                                    <th>Materia</th>
                                    <th class="text-center">Matriculados</th>
                                    <th class="text-center">Asistió</th>
                                    <th class="text-center">No asistió</th>
                                    <th class="text-center">Sin reporte</th>
                                    <th class="text-center">Deserción</th>
                                    <th class="text-center">% Asistencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($datosReporte as $sedeRow)
                                    @foreach ($sedeRow['materias'] as $mIdx => $matRow)
                                        <tr>
                                            @if ($mIdx === 0)
                                                <td rowspan="{{ count($sedeRow['materias']) }}" class="fw-bold text-dark align-top border-end">
                                                    <i class="ti ti-building-community text-primary me-1"></i>{{ $sedeRow['nombre'] }}
                                                    <div class="text-muted small fw-normal mt-1">
                                                        Total sede: {{ $sedeRow['totales']['asistio'] }} presentes ({{ $sedeRow['porcentajeAsistencia'] ?? 0 }}%)
                                                    </div>
                                                </td>
                                            @endif
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $matRow['nombre'] }}</div>
                                            </td>
                                            <td class="text-center">{{ $matRow['legalizados'] }}</td>
                                            <td class="text-center text-success fw-bold">{{ $matRow['contadores']['asistio'] }}</td>
                                            <td class="text-center text-danger">{{ $matRow['contadores']['ausente'] }}</td>
                                            <td class="text-center text-warning">{{ $matRow['contadores']['no_registrado'] }}</td>
                                            <td class="text-center text-secondary">{{ $matRow['contadores']['desercion'] }}</td>
                                            <td class="text-center">
                                                @if ($matRow['porcentajeAsistencia'] !== null)
                                                    <span class="badge rounded-pill bg-label-{{ $matRow['porcentajeAsistencia'] >= 80 ? 'success' : ($matRow['porcentajeAsistencia'] >= 60 ? 'warning' : 'danger') }}">
                                                        {{ $matRow['porcentajeAsistencia'] }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted small">Sin reporte</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-3 text-muted">No se encontraron datos para los filtros seleccionados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Script de interactividad Select2 y ApexCharts --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            // --- SELECT2 SEDES ---
            const selectSedes = $('#select-sedes');
            const btnSelectAllSedes = $('#select-all-sedes');
            const btnDeselectAllSedes = $('#deselect-all-sedes');
            let availableSedeIds = [];

            selectSedes.select2({ placeholder: 'Selecciona una o varias sedes', allowClear: true, width: '100%' });
            selectSedes.on('change', function () { @this.set('sedesSeleccionadas', $(this).val()); });
            Livewire.on('actualizar-sedes-select2', ({ sedes }) => {
                selectSedes.empty();
                availableSedeIds = sedes.map(sede => sede.id);
                const hasSedes = availableSedeIds.length > 0;
                btnSelectAllSedes.prop('disabled', !hasSedes);
                btnDeselectAllSedes.prop('disabled', !hasSedes);
                sedes.forEach(function(sede) { selectSedes.append(new Option(sede.nombre, sede.id)); });
                selectSedes.trigger('change');
            });
            btnSelectAllSedes.on('click', function() { selectSedes.val(availableSedeIds).trigger('change'); });
            btnDeselectAllSedes.on('click', function() { selectSedes.val(null).trigger('change'); });
            btnSelectAllSedes.prop('disabled', true);
            btnDeselectAllSedes.prop('disabled', true);

            // --- SELECT2 MATERIAS ---
            const selectMaterias = $('#select-materias');
            const btnSelectAllMaterias = $('#select-all-materias');
            const btnDeselectAllMaterias = $('#deselect-all-materias');
            let availableMateriaIds = [];

            selectMaterias.select2({ placeholder: 'Selecciona una o varias materias', allowClear: true, width: '100%' });
            selectMaterias.on('change', function () { @this.set('materiasSeleccionadas', $(this).val()); });
            Livewire.on('actualizar-materias-select2', ({ materias }) => {
                selectMaterias.empty();
                availableMateriaIds = materias.map(materia => materia.id);
                const hasMaterias = availableMateriaIds.length > 0;
                btnSelectAllMaterias.prop('disabled', !hasMaterias);
                btnDeselectAllMaterias.prop('disabled', !hasMaterias);
                materias.forEach(function(materia) { selectMaterias.append(new Option(materia.nombre, materia.id)); });
                selectMaterias.trigger('change');
            });
            btnSelectAllMaterias.on('click', function() { selectMaterias.val(availableMateriaIds).trigger('change'); });
            btnDeselectAllMaterias.on('click', function() { selectMaterias.val(null).trigger('change'); });
            btnSelectAllMaterias.prop('disabled', true);
            btnDeselectAllMaterias.prop('disabled', true);

            // --- BOTONES DE ACCIÓN PARA EXPORTACIÓN EXCEL ---
            const form = document.getElementById('reporte-form');
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    form.setAttribute('action', this.dataset.action);
                });
            });

            // --- GESTIÓN DE GRÁFICOS APEXCHARTS ---
            window.graficosAsistenciaReporte = window.graficosAsistenciaReporte || {};

            function destruirGraficos() {
                Object.keys(window.graficosAsistenciaReporte).forEach(k => {
                    if (window.graficosAsistenciaReporte[k]) {
                        try { window.graficosAsistenciaReporte[k].destroy(); } catch (e) {}
                        delete window.graficosAsistenciaReporte[k];
                    }
                });
            }

            function inicializarGraficosAsistencia(datos) {
                if (typeof ApexCharts === 'undefined') return;
                destruirGraficos();

                // 1. Gráfico General (Dona)
                const cGen = document.getElementById('grafico-asistencia-general');
                if (cGen && datos.general && datos.general.series && datos.general.series.some(s => s > 0)) {
                    window.graficosAsistenciaReporte.general = new ApexCharts(cGen, {
                        chart: { type: 'donut', height: 260, toolbar: { show: false } },
                        series: datos.general.series,
                        labels: datos.general.labels,
                        colors: datos.general.colors || ['#28c76f', '#ea5455', '#ff9f43', '#7367f0'],
                        legend: { position: 'bottom', fontSize: '11px' },
                        plotOptions: { pie: { donut: { size: '60%' } } },
                        dataLabels: { enabled: true }
                    });
                    window.graficosAsistenciaReporte.general.render();
                }

                // 2. Gráfico por Sede (Barras apiladas)
                const cSed = document.getElementById('grafico-asistencia-sedes');
                if (cSed && datos.sedes && datos.sedes.categories && datos.sedes.categories.length > 0) {
                    window.graficosAsistenciaReporte.sedes = new ApexCharts(cSed, {
                        chart: { type: 'bar', height: 260, stacked: true, toolbar: { show: false } },
                        plotOptions: { bar: { horizontal: false, columnWidth: '45%', borderRadius: 3 } },
                        series: [
                            { name: 'Asistió', data: datos.sedes.asistio },
                            { name: 'No asistió', data: datos.sedes.ausente },
                            { name: 'Sin reporte', data: datos.sedes.no_registrado },
                            { name: 'Deserción', data: datos.sedes.desercion }
                        ],
                        xaxis: { categories: datos.sedes.categories },
                        colors: ['#28c76f', '#ea5455', '#ff9f43', '#7367f0'],
                        legend: { position: 'bottom', fontSize: '11px' },
                        dataLabels: { enabled: false }
                    });
                    window.graficosAsistenciaReporte.sedes.render();
                }

                // 3. Gráfico por Materia (Barras horizontales de % Asistencia)
                const cMat = document.getElementById('grafico-asistencia-materias');
                if (cMat && datos.materias && datos.materias.categories && datos.materias.categories.length > 0) {
                    window.graficosAsistenciaReporte.materias = new ApexCharts(cMat, {
                        chart: { type: 'bar', height: 260, toolbar: { show: false } },
                        plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
                        series: [{ name: '% Asistencia', data: datos.materias.porcentajes }],
                        xaxis: {
                            categories: datos.materias.categories,
                            max: 100,
                            labels: { formatter: (v) => v + '%' }
                        },
                        colors: ['#00cfe8'],
                        dataLabels: {
                            enabled: true,
                            formatter: (v) => v + '%'
                        },
                        legend: { show: false }
                    });
                    window.graficosAsistenciaReporte.materias.render();
                }
            }

            Livewire.on('reporteAsistenciaGenerado', (payload) => {
                const data = Array.isArray(payload) ? (payload[0] || {}) : payload;
                setTimeout(() => {
                    inicializarGraficosAsistencia(data);
                }, 100);
            });
        });
    </script>
    @endpush
</div>
