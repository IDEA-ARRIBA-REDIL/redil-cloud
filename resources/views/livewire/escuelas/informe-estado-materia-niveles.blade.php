<div class="row g-4">

    {{-- ========================================================================= --}}
    {{-- 1. BARRA SUPERIOR DE FILTROS Y CONTROLES                                  --}}
    {{-- ========================================================================= --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                {{-- Fila 1: Parámetros Clave (Escuela, Materia/Nivel y Rango de Fechas) --}}
                <div class="row g-3 align-items-end">

                    {{-- Selector de Escuela --}}
                    <div class="col-12 col-md-4 col-lg-4">
                        <label class="form-label text-black fw-semibold small mb-1">
                            <i class="ti ti-building-skyscraper me-1 text-primary"></i> Escuela:
                        </label>
                        <select wire:model.live="escuelaSeleccionadaId" class="form-select form-select-sm">
                            <option value="">-- Selecciona una escuela --</option>
                            @foreach ($escuelas as $esc)
                                <option value="{{ $esc->id }}">{{ $esc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Materia o Nivel --}}
                    <div class="col-12 col-md-4 col-lg-4">
                        <label class="form-label text-black fw-semibold small mb-1">
                            <i class="ti ti-book me-1 text-primary"></i> {{ $modo === 'materias' ? 'Materia:' : 'Nivel / Grado:' }}
                        </label>
                        <select wire:model="itemSeleccionadoId" class="form-select form-select-sm" {{ empty($escuelaSeleccionadaId) ? 'disabled' : '' }}>
                            <option value="">-- Selecciona {{ $modo === 'materias' ? 'una materia' : 'un nivel' }} --</option>
                            @foreach ($itemsDisponibles as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->nombre }}
                                    @if ($modo === 'materias' && !empty($item->creditos))
                                        ({{ $item->creditos }} cr)
                                    @endif
                                    @if ($item->caracter_obligatorio)
                                        *
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Rango de Fechas --}}
                    <div class="col-12 col-md-4 col-lg-4">
                        <label class="form-label text-black fw-semibold small mb-1">
                            <i class="ti ti-calendar me-1 text-primary"></i> Rango de fechas:
                        </label>
                        <div class="input-group input-group-sm" wire:ignore>
                            <span class="input-group-text bg-light border-end-0">
                                <i class="ti ti-calendar text-black"></i>
                            </span>
                            <input type="text" id="rango_fechas_informe" class="form-control form-control-sm flatpickr-range-informe text-center border-start-0" placeholder="aaaa-mm-dd a aaaa-mm-dd" value="{{ $rangoFechas }}" autocomplete="off" />
                            @if (!empty($rangoFechas))
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarRangoFechas()">
                                    <i class="ti ti-x"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Fila 2: Criterios Específicos y Botón Filtrar --}}
                <div class="row g-3 mt-1 align-items-end">

                    {{-- Filtro de Estado Académico --}}
                    <div class="col-12 col-md-3 col-lg-3">
                        <label class="form-label text-black fw-semibold small mb-1">
                            <i class="ti ti-chart-pie me-1 text-primary"></i> Estado:
                        </label>
                        <select wire:model="filtroEstadoAcademico" class="form-select form-select-sm">
                            <option value="todos">Todos los estados</option>
                            <option value="aprobados">Solo aprobados</option>
                            <option value="en_curso">Solo en proceso</option>
                            <option value="reprobados">Solo reprobados</option>
                        </select>
                    </div>

                    {{-- Filtro de Tipo de Registro --}}
                    <div class="col-12 col-md-3 col-lg-3">
                        <label class="form-label text-black fw-semibold small mb-1">
                            <i class="ti ti-certificate me-1 text-primary"></i> Registro:
                        </label>
                        <select wire:model="filtroTipoRegistro" class="form-select form-select-sm">
                            <option value="todos">Todos los tipos</option>
                            <option value="regular">Cursado</option>
                            <option value="homologacion">Homologado</option>
                        </select>
                    </div>

                    {{-- Filtro de Estado del Usuario --}}
                    <div class="col-12 col-md-3 col-lg-3">
                        <label class="form-label text-black fw-semibold small mb-1">
                            <i class="ti ti-user-check me-1 text-primary"></i> Estudiantes:
                        </label>
                        <select wire:model="filtroEstadoUsuario" class="form-select form-select-sm">
                            <option value="activos">Solo activos</option>
                            <option value="todos">Todos</option>
                            <option value="dados_de_baja">Solo dados de baja</option>
                        </select>
                    </div>

                    {{-- Botón de Filtrado / Consulta --}}
                    <div class="col-12 col-md-3 col-lg-3 text-md-end">
                        <button wire:click="consultar" type="button" class="btn btn-sm btn-primary rounded-pill w-100" wire:loading.attr="disabled" {{ (empty($escuelaSeleccionadaId) || empty($itemSeleccionadoId) || empty($rangoFechas)) ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="consultar">
                                <i class="ti ti-filter me-1"></i> Filtrar
                            </span>
                            <span wire:loading wire:target="consultar">
                                <i class="ti ti-loader rotate me-1"></i> Consultando...
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. TARJETAS KPI (RESUMEN ACADÉMICO DEL CURSO/MATERIA)                     --}}
    {{-- ========================================================================= --}}
    @if ($consultado && !empty($escuelaId) && !empty($itemId))
        <div class="col-12">
            <div class="row g-2">
                {{-- Total Registrados --}}
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md bg-success rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-users fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Total registrados</p>
                                <h5 class="mb-0 fw-bold text-black">{{ $metricas['total'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aprobados --}}
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md bg-success rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-circle-check fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Aprobados</p>
                                <h5 class="mb-0 fw-bold text-black">
                                    {{ $metricas['aprobados'] }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- En Proceso --}}
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md bg-warning rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-clock fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">En proceso</p>
                                <h5 class="mb-0 fw-bold text-black">
                                    {{ $metricas['en_curso'] }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reprobados --}}
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md bg-danger rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-circle-x fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Reprobados</p>
                                <h5 class="mb-0 fw-bold text-black">
                                    {{ $metricas['reprobados'] }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Promedio Calificación --}}
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md bg-info rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background-color: #00cfe8;">
                                    <i class="ti ti-award fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Promedio General</p>
                                <h5 class="mb-0 fw-bold text-black">{{ $metricas['promedio_general'] > 0 ? number_format($metricas['promedio_general'], 1) : '-' }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 3. BUSCADOR RÁPIDO (ENTRE KPIS Y TABLA)                                   --}}
        {{-- ========================================================================= --}}

    @endif

     @if ($consultado && !empty($escuelaId) && !empty($itemId) && $estudiantesPaginados->isNotEmpty())
     <div class="d-flex align-items-center justify-content-end gap-2">
        <button wire:click="exportarExcel" type="button" class="btn btn-sm btn-outline-success rounded-pill" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="exportarExcel">
                <i class="ti ti-file-spreadsheet me-1"></i> Exportar a Excel
            </span>
            <span wire:loading wire:target="exportarExcel">
                <i class="ti ti-loader rotate me-1"></i> Generando...
            </span>
        </button>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 4. TABLA DE RESULTADOS                                                    --}}
    {{-- ========================================================================= --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="ti ti-table me-1"></i> {{ $itemActivo ? $itemActivo->nombre : 'Informe Estado Materia / Niveles' }}
                        @if ($escuelaActual)
                            <span class="text-black fw-normal fs-6">({{ $escuelaActual->nombre }})</span>
                        @endif
                    </h5>
                    @if ($consultado && $itemActivo)
                        <small class="text-black">
                            {{ $modo === 'materias' ? 'Materia' : 'Nivel' }} {{ $itemActivo->caracter_obligatorio ? 'obligatoria' : 'opcional' }}
                            @if ($modo === 'materias' && !empty($itemActivo->creditos))
                                | {{ $itemActivo->creditos }} Créditos
                            @endif
                            | Mostrando <b>{{ $estudiantesPaginados->total() }}</b> estudiantes
                        </small>
                    @endif
                </div>

                {{-- Acciones Derecha: Botón Exportar + Selector de cantidad por página --}}
                <div class="d-flex align-items-center gap-2">

                    <div class="d-flex align-items-center gap-1">
                        <label class="small text-black mb-0">Mostrar:</label>
                        <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 75px;">
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            @if ($consultado && !empty($escuelaId))
                <div class="row m-3 align-items-center">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="ti ti-search text-muted"></i>
                            </span>
                            <input type="text" wire:model.live.debounce.350ms="buscar" class="form-control form-control-sm border-start-0" placeholder="Buscar por nombre, apellido o cédula..." />
                            @if (!empty($buscar))
                                <button wire:click="$set('buscar', '')" class="btn btn-sm " type="button">
                                    <i class="ti ti-x"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-8 text-md-end">

                    </div>
                </div>
            @endif


            @if (! $consultado || empty($escuelaId) || empty($itemId))
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl bg-label-primary mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-school fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-black mb-1">Selecciona una escuela y materia / nivel</h5>
                    <p class="text-black mb-0">Elige la escuela, la materia o nivel a evaluar y haz clic en <strong>Filtrar</strong> para generar el informe.</p>
                </div>
            @elseif ($estudiantesPaginados->isEmpty())
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl bg-label-info mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-user-x fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-black mb-1">No se encontraron estudiantes</h5>
                    <p class="text-black mb-0">Ningún estudiante coincide con los filtros y rango de fechas seleccionados.</p>
                </div>
            @else
                <div class="table-responsive" style="max-height: 560px; overflow-y: auto;">
                    <table class="table table-hover table-bordered align-middle mb-0" style="font-size: 0.8125rem;">
                        <thead class="table-light sticky-top shadow-sm" style="z-index: 10;">
                            <tr>
                                <th class="text-center bg-light" style="width: 50px;">No.</th>
                                <th class="bg-light" style="min-width: 110px;">Identificación</th>
                                <th class="bg-light" style="min-width: 220px;">Estudiante</th>
                                <th class="bg-light" style="min-width: 180px;">Email</th>
                                <th class="text-center bg-light" style="min-width: 110px;">Estado</th>
                                <th class="text-center bg-light" style="min-width: 110px;">Tipo</th>
                                <th class="text-center" style="min-width: 85px; background-color: #cff4fc; color: #055160;">Calificación</th>
                                @if ($modo === 'materias')
                                    <th class="text-center" style="min-width: 85px; background-color: #e2d9f3; color: #512da8;">Créditos</th>
                                @endif
                                @if ($modo === 'materias' && !empty($itemActivo?->habilitar_asistencias))
                                    <th class="text-center bg-light" style="min-width: 90px;">Asistencias</th>
                                @endif
                                <th class="text-center bg-light" style="min-width: 120px;">Fecha Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiantesPaginados as $index => $est)
                                <tr>
                                    {{-- No. Correlativo --}}
                                    <td class="text-center fw-bold text-black">
                                        {{ ($estudiantesPaginados->currentPage() - 1) * $estudiantesPaginados->perPage() + $index + 1 }}
                                    </td>

                                    {{-- Identificación --}}
                                    <td class="fw-semibold text-black">
                                        {{ $est['identificacion'] ?: 'No identificado' }}
                                    </td>

                                    {{-- Estudiante --}}
                                    <td>
                                        <div class="text-black small">{{ $est['nombres'] }}</div>
                                        <div class="text-black small d-flex align-items-center gap-1">
                                            <span>{{ $est['apellidos'] }}</span>
                                            @if (!empty($est['dado_de_baja']))
                                                <span class="badge bg-label-danger rounded-pill" style="font-size: 0.6rem;" title="Usuario dado de baja">Dado de baja</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Correo Electrónico --}}
                                    <td>
                                        <span class="text-black small">{{ $est['email'] ?: '-' }}</span>
                                    </td>

                                    {{-- Estado --}}
                                    <td class="text-center">
                                        @if ($est['estado'] === 1)
                                            <span class="badge text-white bg-success rounded-pill fw-bold py-1 px-2" style="font-size: 0.75rem;">
                                                <i class="ti ti-check me-1"></i> Aprobado
                                            </span>
                                        @elseif ($est['estado'] === 2)
                                            <span class="badge text-white bg-warning rounded-pill py-1 px-2" style="font-size: 0.72rem;">
                                                <i class="ti ti-clock me-1"></i> En proceso
                                            </span>
                                        @elseif ($est['estado'] === 0)
                                            <span class="badge text-white bg-danger rounded-pill py-1 px-2" style="font-size: 0.72rem;">
                                                <i class="ti ti-x me-1"></i> Reprobado
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary rounded-pill py-1 px-2" style="font-size: 0.72rem;">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Tipo de Registro (Cursado vs Homologado) --}}
                                    <td class="text-center">
                                        @if ($est['es_homologacion'])
                                            <span class="badge bg-label-info rounded-pill py-1 px-2" style="font-size: 0.72rem;">
                                                <i class="ti ti-certificate me-1"></i> Homologado
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary rounded-pill py-1 px-2" style="font-size: 0.72rem;">
                                                Cursado
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Calificación / Nota Final --}}
                                    <td class="text-center fw-bold bg-light">
                                        {{ $est['nota'] !== null ? $est['nota'] : '-' }}
                                    </td>

                                    {{-- Créditos (Solo si es materias) --}}
                                    @if ($modo === 'materias')
                                        <td class="text-center fw-bold bg-light" style="color: #512da8;">
                                            {{ $est['estado'] === 1 ? ($est['creditos'] ?? 0) : 0 }}
                                        </td>
                                    @endif

                                    {{-- Asistencias (Solo si aplica) --}}
                                    @if ($modo === 'materias' && !empty($itemActivo?->habilitar_asistencias))
                                        <td class="text-center text-black">
                                            {{ $est['asistencias'] !== null ? $est['asistencias'] : '-' }}
                                        </td>
                                    @endif

                                    {{-- Fecha de Registro / Aprobación --}}
                                    <td class="text-center text-black small">
                                        {{ $est['fecha_registro'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginador al pie --}}
                <div class="card-footer bg-light border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-black">
                        Mostrando {{ $estudiantesPaginados->firstItem() ?? 0 }} a {{ $estudiantesPaginados->lastItem() ?? 0 }} de {{ $estudiantesPaginados->total() }} estudiantes
                    </small>
                    <div>
                        {{ $estudiantesPaginados->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        inicializarFlatpickrInforme();
    });

    document.addEventListener('livewire:navigated', function () {
        inicializarFlatpickrInforme();
    });

    function inicializarFlatpickrInforme() {
        const input = document.getElementById('rango_fechas_informe');
        if (!input) return;

        if (input._flatpickr) {
            input._flatpickr.destroy();
        }

        flatpickr(input, {
            mode: "range",
            dateFormat: "Y-m-d",
            rangeSeparator: " a ",
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                },
            },
            onChange: function(selectedDates, dateStr, instance) {
                @this.set('rangoFechas', dateStr);
            }
        });
    }

    window.limpiarRangoFechas = function() {
        const input = document.getElementById('rango_fechas_informe');
        if (input && input._flatpickr) {
            input._flatpickr.clear();
        }
        @this.set('rangoFechas', '');
    };
</script>
@endpush
