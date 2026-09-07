@php
$configData = Helper::appClasses();
$kpis = $datos['kpis'] ?? [];
$tablaReuniones = $datos['tabla_reuniones'] ?? [];
$bloquesSede = $datos['bloques_sede'] ?? [];
$demografia = $datos['demografia'] ?? [];
$alertas = $datos['alertas'] ?? [];
$tendencia = $datos['tendencia'] ?? [];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard de Reuniones')

@section('page-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
<style>
  .kpi-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
  }
</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js'
])
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Flatpickr en español
    flatpickr.l10ns.es = {
      weekdays: {
        shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
      },
      months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      }
    };

    $('.flatpickr-date').flatpickr({
      locale: 'es',
      dateFormat: 'Y-m-d'
    });

    $('.select2').select2({
      placeholder: 'Seleccionar...',
      allowClear: true,
      width: '100%'
    });

    // Gráfico de Composición por Tipo de Usuario
    const demografiaTipo = @json($demografia['por_tipo_usuario'] ?? []);
    if (demografiaTipo.length > 0) {
      const optionsTipo = {
        chart: { type: 'donut', height: 280 },
        labels: demografiaTipo.map(d => d.etiqueta),
        series: demografiaTipo.map(d => d.total),
        colors: ['#7367F0', '#28C76F', '#EA5455', '#FF9F43', '#00CFDD', '#1E1E1E'],
        legend: { position: 'bottom' },
        dataLabels: {
          enabled: true,
          formatter: function(val) { return Math.round(val) + '%'; }
        }
      };
      new ApexCharts(document.querySelector("#chartTipoUsuario"), optionsTipo).render();
    }

    // Gráfico de Composición por Género
    const demografiaGenero = @json($demografia['por_genero'] ?? []);
    if (demografiaGenero.length > 0) {
      const optionsGenero = {
        chart: { type: 'pie', height: 280 },
        labels: demografiaGenero.map(d => d.etiqueta),
        series: demografiaGenero.map(d => d.total),
        colors: ['#00CFDD', '#EA5455', '#A8AAAE'],
        legend: { position: 'bottom' },
        dataLabels: {
          enabled: true,
          formatter: function(val) { return Math.round(val) + '%'; }
        }
      };
      new ApexCharts(document.querySelector("#chartGenero"), optionsGenero).render();
    }

    // Gráfico de Tendencia Temporal
    const tendenciaData = @json($tendencia ?? []);
    if (tendenciaData.length > 0) {
      const optionsTendencia = {
        chart: { type: 'area', height: 300, toolbar: { show: false } },
        series: [
          { name: 'Asistencias Miembros', data: tendenciaData.map(t => t.asistencias) },
          { name: 'Invitados', data: tendenciaData.map(t => t.invitados) }
        ],
        xaxis: {
          categories: tendenciaData.map(t => t.fecha + ' (' + t.reunion + ')')
        },
        colors: ['#7367F0', '#FF9F43'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        tooltip: { shared: true, intersect: false }
      };
      new ApexCharts(document.querySelector("#chartTendencia"), optionsTendencia).render();
    }
  });
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header con título y exportación -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold py-1 mb-1 text-primary">
        <span class="text-muted fw-light">Reuniones /</span> Dashboard Estadístico
      </h4>
      <p class="text-muted mb-0">Consolidación analítica de asistencias, cobertura y métricas de reuniones.</p>
    </div>
    <div class="d-flex gap-2 mt-2 mt-sm-0">
      <a href="{{ route('reporteReunion.lista') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Lista de reportes
      </a>
      @if($rolActivo->hasPermissionTo('reporte_reuniones.exportar_dashboard_estadistico'))
      <a href="{{ route('reporteReunion.dashboard.exportar', request()->query()) }}" class="btn btn-success">
        <i class="ti ti-file-spreadsheet me-1"></i> Exportar a Excel
      </a>
      @endif
    </div>
  </div>

  <!-- Alertas de calidad de datos -->
  @if(!empty($alertas))
    @foreach($alertas as $alerta)
      <div class="alert alert-{{ $alerta['tipo'] }} alert-dismissible d-flex align-items-center mb-3" role="alert">
        <i class="ti ti-alert-triangle fs-4 me-2"></i>
        <div>{{ $alerta['mensaje'] }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endforeach
  @endif

  <!-- Filtros -->
  <div class="card mb-4 border shadow-sm">
    <div class="card-header border-bottom py-3">
      <h5 class="card-title mb-0 d-flex align-items-center">
        <i class="ti ti-filter me-2 text-primary"></i> Filtros de búsqueda
      </h5>
    </div>
    <div class="card-body pt-4">
      <form method="GET" action="{{ route('reporteReunion.dashboard') }}" id="formFiltrosDashboard">
        <div class="row g-3">
          <div class="col-12 col-md-3">
            <label class="form-label fw-semibold" for="fecha_inicio">Fecha Inicial</label>
            <input type="text" name="fecha_inicio" id="fecha_inicio" class="form-control flatpickr-date" value="{{ $filtros['fecha_inicio'] }}" placeholder="YYYY-MM-DD">
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label fw-semibold" for="fecha_fin">Fecha Final</label>
            <input type="text" name="fecha_fin" id="fecha_fin" class="form-control flatpickr-date" value="{{ $filtros['fecha_fin'] }}" placeholder="YYYY-MM-DD">
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label fw-semibold" for="sedes_id">Sedes Organizadoras</label>
            <select name="sedes_id[]" id="sedes_id" class="form-select select2" multiple>
              @foreach($sedesDisponibles as $s)
                <option value="{{ $s->id }}" @selected(in_array($s->id, (array)$filtros['sedes_id']))>{{ $s->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label fw-semibold" for="tipos_reunion_id">Tipos de Reunión</label>
            <select name="tipos_reunion_id[]" id="tipos_reunion_id" class="form-select select2" multiple>
              @foreach($tiposDisponibles as $t)
                <option value="{{ $t->id }}" @selected(in_array($t->id, (array)$filtros['tipos_reunion_id']))>{{ $t->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold" for="reuniones_id">Reunión Específica</label>
            <select name="reuniones_id[]" id="reuniones_id" class="form-select select2" multiple>
              @foreach($reunionesDisponibles as $r)
                <option value="{{ $r->id }}" @selected(in_array($r->id, (array)$filtros['reuniones_id']))>{{ $r->nombre }} ({{ $r->sede?->nombre }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold" for="estado">Estado del Reporte</label>
            <select name="estado" id="estado" class="form-select">
              <option value="finalizado" @selected($filtros['estado'] === 'finalizado')>Finalizados (Oficial)</option>
              <option value="en_curso" @selected($filtros['estado'] === 'en_curso')>En curso</option>
              <option value="programado" @selected($filtros['estado'] === 'programado')>Programados</option>
              <option value="cancelado" @selected($filtros['estado'] === 'cancelado')>Cancelados</option>
              <option value="todos" @selected($filtros['estado'] === 'todos')>Todos los estados</option>
            </select>
          </div>
          <div class="col-12 col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
              <i class="ti ti-search me-1"></i> Aplicar Filtros
            </button>
            <a href="{{ route('reporteReunion.dashboard') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
              <i class="ti ti-refresh"></i>
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Tarjetas KPIs Principales -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-2">
      <div class="card kpi-card shadow-sm border h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-primary rounded p-1">
              <i class="ti ti-calendar-event text-primary"></i>
            </div>
            <span class="text-muted fw-semibold small">Reportes</span>
          </div>
          <h4 class="card-title mb-0 fw-bold">{{ number_format($kpis['total_reportes'] ?? 0) }}</h4>
          <small class="text-muted">En el período</small>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-2">
      <div class="card kpi-card shadow-sm border h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-success rounded p-1">
              <i class="ti ti-users text-success"></i>
            </div>
            <span class="text-muted fw-semibold small">Asistencias</span>
          </div>
          <h4 class="card-title mb-0 fw-bold">{{ number_format($kpis['asistencias_acumuladas'] ?? 0) }}</h4>
          <small class="text-success fw-semibold">{{ number_format($kpis['asistencias_miembros'] ?? 0) }} miembros</small>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-2">
      <div class="card kpi-card shadow-sm border h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-info rounded p-1">
              <i class="ti ti-user-check text-info"></i>
            </div>
            <span class="text-muted fw-semibold small">Personas Únicas</span>
          </div>
          <h4 class="card-title mb-0 fw-bold">{{ number_format($kpis['personas_unicas'] ?? 0) }}</h4>
          <small class="text-muted">Miembros distintos</small>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-2">
      <div class="card kpi-card shadow-sm border h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-warning rounded p-1">
              <i class="ti ti-chart-bar text-warning"></i>
            </div>
            <span class="text-muted fw-semibold small">Promedio</span>
          </div>
          <h4 class="card-title mb-0 fw-bold">{{ $kpis['promedio_asistencias'] ?? 0 }}</h4>
          <small class="text-muted">Por reporte</small>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-2">
      <div class="card kpi-card shadow-sm border h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-danger rounded p-1">
              <i class="ti ti-target text-danger"></i>
            </div>
            <span class="text-muted fw-semibold small">Cobertura</span>
          </div>
          <h4 class="card-title mb-0 fw-bold">{{ $kpis['cobertura_congregacion'] ?? 0 }}%</h4>
          <small class="text-muted">De población elegible</small>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-2">
      <div class="card kpi-card shadow-sm border h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-secondary rounded p-1">
              <i class="ti ti-ticket text-secondary"></i>
            </div>
            <span class="text-muted fw-semibold small">Invitados</span>
          </div>
          <h4 class="card-title mb-0 fw-bold">{{ number_format($kpis['invitados_totales'] ?? 0) }}</h4>
          <small class="text-muted">Confirmados</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Tendencia Temporal -->
  <div class="card mb-4 border shadow-sm">
    <div class="card-header border-bottom py-3">
      <h5 class="card-title mb-0">
        <i class="ti ti-chart-line me-2 text-primary"></i> Tendencia de Asistencia en el Tiempo
      </h5>
    </div>
    <div class="card-body pt-3">
      @if(count($tendencia) > 0)
        <div id="chartTendencia"></div>
      @else
        <div class="text-center py-4 text-muted">
          <i class="ti ti-chart-line fs-1 mb-2 d-block"></i>
          No hay suficientes registros para graficar la tendencia temporal en el período seleccionado.
        </div>
      @endif
    </div>
  </div>

  <!-- Tabla Comparativa por Reunión -->
  <div class="card mb-4 border shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">
        <i class="ti ti-layout-grid me-2 text-primary"></i> Comparativo por Reunión
      </h5>
      <span class="badge bg-label-primary">{{ count($tablaReuniones) }} reuniones</span>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Reunión</th>
            <th>Tipo</th>
            <th>Sede Organizadora</th>
            <th class="text-center">Reportes</th>
            <th class="text-center">Asistencias Totales</th>
            <th class="text-center">Promedio / Reporte</th>
            <th class="text-center">Personas Únicas</th>
            <th class="text-center">Cobertura</th>
            <th class="text-center">Invitados</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tablaReuniones as $reunion)
            <tr>
              <td class="fw-semibold text-primary">{{ $reunion['reunion_nombre'] }}</td>
              <td><span class="badge bg-label-info">{{ $reunion['tipo_servicio'] }}</span></td>
              <td>{{ $reunion['sede_nombre'] }}</td>
              <td class="text-center">{{ $reunion['reportes_count'] }}</td>
              <td class="text-center fw-bold">{{ number_format($reunion['asistencias_totales']) }}</td>
              <td class="text-center">{{ $reunion['promedio_reporte'] }}</td>
              <td class="text-center">{{ number_format($reunion['personas_unicas']) }}</td>
              <td class="text-center">
                <span class="badge bg-label-{{ $reunion['cobertura'] >= 70 ? 'success' : ($reunion['cobertura'] >= 40 ? 'warning' : 'danger') }}">
                  {{ $reunion['cobertura'] }}%
                </span>
              </td>
              <td class="text-center">{{ number_format($reunion['invitados']) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">
                <i class="ti ti-calendar-off fs-2 d-block mb-1"></i>
                No se encontraron reuniones que cumplan los filtros seleccionados.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bloques por Sede Organizadora (Acordeón) -->
  <div class="card mb-4 border shadow-sm">
    <div class="card-header border-bottom py-3">
      <h5 class="card-title mb-0">
        <i class="ti ti-building me-2 text-primary"></i> Detalle por Sede Organizadora
      </h5>
    </div>
    <div class="card-body pt-3">
      <div class="accordion" id="accordionSedes">
        @forelse($bloquesSede as $index => $bloque)
          <div class="accordion-item border mb-2 rounded overflow-hidden">
            <h2 class="accordion-header" id="headingSede{{ $bloque['sede_id'] }}">
              <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }} bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSede{{ $bloque['sede_id'] }}">
                <span class="fw-bold text-dark me-3"><i class="ti ti-map-pin me-1 text-primary"></i> {{ $bloque['sede_nombre'] }}</span>
                <span class="badge bg-primary me-2">{{ $bloque['kpis']['total_reportes'] }} reportes</span>
                <span class="badge bg-success me-2">{{ number_format($bloque['kpis']['asistencias_acumuladas']) }} asistencias</span>
                <span class="badge bg-info">Prom: {{ $bloque['kpis']['promedio_asistencias'] }}</span>
              </button>
            </h2>
            <div id="collapseSede{{ $bloque['sede_id'] }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#accordionSedes">
              <div class="accordion-body p-3">
                <div class="table-responsive">
                  <table class="table table-sm table-bordered table-striped mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Reunión</th>
                        <th>Tipo</th>
                        <th class="text-center">Reportes</th>
                        <th class="text-center">Asistencias</th>
                        <th class="text-center">Promedio</th>
                        <th class="text-center">Únicas</th>
                        <th class="text-center">Cobertura</th>
                        <th class="text-center">Invitados</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($bloque['reuniones'] as $rSede)
                        <tr>
                          <td class="fw-semibold">{{ $rSede['reunion_nombre'] }}</td>
                          <td><span class="badge bg-label-info">{{ $rSede['tipo_servicio'] }}</span></td>
                          <td class="text-center">{{ $rSede['reportes_count'] }}</td>
                          <td class="text-center fw-bold">{{ number_format($rSede['asistencias_totales']) }}</td>
                          <td class="text-center">{{ $rSede['promedio_reporte'] }}</td>
                          <td class="text-center">{{ number_format($rSede['personas_unicas']) }}</td>
                          <td class="text-center">{{ $rSede['cobertura'] }}%</td>
                          <td class="text-center">{{ number_format($rSede['invitados']) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center py-4 text-muted">
            No hay sedes con reportes en este período.
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Composición Demográfica -->
  <div class="row g-4 mb-4">
    <div class="col-12 col-md-6">
      <div class="card border shadow-sm h-100">
        <div class="card-header border-bottom py-3">
          <h5 class="card-title mb-0">
            <i class="ti ti-id-badge-2 me-2 text-primary"></i> Asistencia por Tipo de Usuario
          </h5>
        </div>
        <div class="card-body pt-3">
          @if(count($demografia['por_tipo_usuario'] ?? []) > 0)
            <div id="chartTipoUsuario"></div>
          @else
            <div class="text-center py-4 text-muted">Sin datos demográficos en el período.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6">
      <div class="card border shadow-sm h-100">
        <div class="card-header border-bottom py-3">
          <h5 class="card-title mb-0">
            <i class="ti ti-gender-intergender me-2 text-primary"></i> Asistencia por Género
          </h5>
        </div>
        <div class="card-body pt-3">
          @if(count($demografia['por_genero'] ?? []) > 0)
            <div id="chartGenero"></div>
          @else
            <div class="text-center py-4 text-muted">Sin datos de género en el período.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
