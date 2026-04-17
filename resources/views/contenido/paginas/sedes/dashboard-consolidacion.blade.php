@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Consolidación: ' . $sede->nombre)

@section('page-style')
<style>
  .hover-link:hover h5 { text-decoration: underline !important; }
  .hover-link { transition: all 0.2s ease; }
  .hover-link:hover { transform: translateY(-2px); }
</style>
@vite([
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js'
])
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('.selectpicker').selectpicker();

    flatpickr.l10ns.es = {
      weekdays: {
        shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
      },
      months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      },
      ordinal: () => 'º',
      firstDayOfWeek: 1,
      rangeSeparator: ' a ',
      time_24hr: true,
    };

    function getMonday(d) {
      d = new Date(d);
      var day = d.getDay(),
          diff = d.getDate() - day + (day == 0 ? -6 : 1); 
      return new Date(d.setDate(diff));
    }

    function getSunday(d) {
        d = new Date(d);
        var day = d.getDay(),
            diff = d.getDate() + (day == 0 ? 0 : 7 - day); 
        return new Date(d.setDate(diff));
    }

    const fp = flatpickr(".flatpickr-range", {
      mode: "range",
      dateFormat: "Y-m-d",
      locale: "es",
      weekNumbers: true,
      onChange: function(selectedDates, dateStr, instance) {
         document.getElementById('filtro_rapido').value = "";
      },
      onClose: function(selectedDates, dateStr, instance) {
          if (selectedDates.length > 0) {
              const start = getMonday(selectedDates[0]);
              const end = getSunday(selectedDates[selectedDates.length - 1]);
              instance.setDate([start, end], true);
          }
      }
    });

    window.seleccionarRango = function(tipo) {
      if (!tipo) return;
      let inicio, fin;
      const hoy = new Date();
      if (tipo === 'este_mes') {
        inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
      } else if (tipo === 'mes_pasado') {
        inicio = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        fin = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
      } else if (tipo === 'este_ano') {
        inicio = new Date(hoy.getFullYear(), 0, 1);
        fin = new Date(hoy.getFullYear(), 11, 31);
      } else if (tipo === 'trimestre_actual') {
        const mesActual = hoy.getMonth();
        const inicioMesTrimestre = Math.floor(mesActual / 3) * 3;
        inicio = new Date(hoy.getFullYear(), inicioMesTrimestre, 1);
        fin = new Date(hoy.getFullYear(), inicioMesTrimestre + 3, 0);
      }
      inicio = getMonday(inicio);
      fin = getSunday(fin);
      const fpInstance = document.querySelector("#rango_fechas")._flatpickr;
      if (fpInstance) {
        fpInstance.setDate([inicio, fin]);
      }
    };

    // Gráfico de Vinculación
    const vincEl = document.querySelector('#vinculacionChart');
    if (vincEl) {
      const seriesData = JSON.parse(vincEl.getAttribute('data-series') || '[]');
      const labelsData = JSON.parse(vincEl.getAttribute('data-labels') || '[]');
      const totalCosecha = vincEl.getAttribute('data-total') || '0';

      const vincConfig = {
        chart: {
          height: 400,
          type: 'donut',
          toolbar: { show: true },
          fontFamily: 'Poppins'
        },
        labels: labelsData,
        series: seriesData,
        colors: ['#7367f0', '#28c76f', '#ea5455', '#ff9f43', '#00cfe8', '#a8aaae'],
        stroke: { show: false },
        dataLabels: {
          enabled: true,
          formatter: val => parseInt(val) + '%'
        },
        legend: {
          show: true,
          position: 'bottom',
          fontFamily: 'Poppins'
        },
        plotOptions: {
          pie: {
            donut: {
              labels: {
                show: true,
                name: { fontSize: '1.5rem', fontFamily: 'Poppins' },
                value: {
                  fontSize: '1.2rem',
                  fontFamily: 'Poppins',
                  formatter: val => parseInt(val) + ' Personas'
                },
                total: {
                  show: true,
                  fontSize: '1.5rem',
                  label: 'Total',
                  formatter: w => totalCosecha
                }
              }
            }
          }
        }
      };
      new ApexCharts(vincEl, vincConfig).render();
    }

    // Gráfico de Distribución (Sector vs Templo)
    const matriculasTipoEl = document.querySelector('#matriculasTipo');
    if (matriculasTipoEl) {
        new ApexCharts(matriculasTipoEl, {
            chart: { height: 150, type: 'bar', toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, barHeight: '80%', distributed: true, borderRadius: 4 } },
            grid: { show: false, padding: { top: 0, bottom: 0, left: 0 } },
            colors: ['#7367f0', '#ff9f43'],
            dataLabels: { enabled: true, formatter: val => Math.floor(val) },
            series: [{ name: 'Matrículas', data: [{{ $matriculasSector }}, {{ $matriculasTemplo }}] }],
            xaxis: {
                categories: ['Sector', 'Templo'],
                axisBorder: { show: false },
                axisTicks: { show: false },
                tickAmount: {{ min(max($matriculasSector, $matriculasTemplo, 1), 5) }},
                labels: { formatter: val => Math.floor(val) }
            },
            yaxis: { labels: { minWidth: 70, style: { colors: '#000000ff', fontSize: '11px', fontWeight: 400, fontFamily: 'Poppins' } } },
            legend: { show: false },
            tooltip: { enabled: true }
        }).render();
    }

    const ageChartBaseConfig = {
        chart: { height: 150, type: 'bar', toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, barHeight: '80%', distributed: true, borderRadius: 4 } },
        grid: { show: false, padding: { top: 0, bottom: 0, left: 0 } },
        colors: ['#7367f0', '#ff9f43'],
        dataLabels: { enabled: true, formatter: val => Math.floor(val) },
        xaxis: { categories: ['Adultos', 'Warriors'], axisBorder: { show: false }, axisTicks: { show: false }, labels: { formatter: val => Math.floor(val) } },
        yaxis: { labels: { minWidth: 70, style: { colors: '#000000ff', fontSize: '11px', fontFamily: 'Poppins' } } },
        legend: { show: false },
        tooltip: { enabled: true }
    };

    const sectorEdadEl = document.querySelector('#sectorEdadChart');
    if (sectorEdadEl) {
        const sectorEdadConfig = JSON.parse(JSON.stringify(ageChartBaseConfig));
        sectorEdadConfig.series = [{ name: 'Matrículas', data: [{{ $sectorAdultos }}, {{ $sectorMenores }}] }];
        sectorEdadConfig.xaxis.tickAmount = {{ min(max($sectorAdultos, $sectorMenores, 1), 5) }};
        new ApexCharts(sectorEdadEl, sectorEdadConfig).render();
    }

    const temploEdadEl = document.querySelector('#temploEdadChart');
    if (temploEdadEl) {
        const temploEdadConfig = JSON.parse(JSON.stringify(ageChartBaseConfig));
        temploEdadConfig.series = [{ name: 'Matrículas', data: [{{ $temploAdultos }}, {{ $temploMenores }}] }];
        temploEdadConfig.xaxis.tickAmount = {{ min(max($temploAdultos, $temploMenores, 1), 5) }};
        new ApexCharts(temploEdadEl, temploEdadConfig).render();
    }

    const commonDonutOptions = {
        chart: { height: 220, type: 'donut', toolbar: { show: false } },
        dataLabels: { enabled: true, formatter: (val, opts) => opts.w.config.series[opts.seriesIndex] },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: { fontSize: '1rem', fontFamily: 'Poppins' },
                        value: { fontSize: '1rem', fontFamily: 'Poppins', formatter: val => parseInt(val) },
                        total: { show: true, fontSize: '0.8rem', label: 'Total', formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) }
                    }
                }
            }
        },
        legend: { show: true, position: 'bottom', markers: { offsetX: -3 }, itemMargin: { vertical: 3, horizontal: 10 } },
        tooltip: { enabled: true }
    };

    const unionLibreEl = document.querySelector('#unionLibreChart');
    if (unionLibreEl) {
        new ApexCharts(unionLibreEl, {
            ...commonDonutOptions,
            labels: ['Aptos', 'Unión Libre'],
            series: [{{ $matriculasAptos }}, {{ $matriculasUnionLibre }}],
            colors: ['#28c76f', '#ea5455']
        }).render();
    }

    const desercionesEl = document.querySelector('#desercionesChart');
    if (desercionesEl) {
        new ApexCharts(desercionesEl, {
            ...commonDonutOptions,
            labels: ['Efectivos', 'Deserciones'],
            series: [{{ $matriculasEfectivos }}, {{ $matriculasDeserciones }}],
            colors: ['#7367f0', '#ff9f43']
        }).render();
    }

    const bautismosTrasladosEl = document.querySelector('#bautismosTrasladosChart');
    if (bautismosTrasladosEl) {
        new ApexCharts(bautismosTrasladosEl, {
            ...commonDonutOptions,
            labels: ['Bautismos', 'Traslados'],
            series: [{{ $miembrosBautismos }}, {{ $miembrosTraslados }}],
            colors: ['#00cfe8', '#ea5455']
        }).render();
    }

    const trasladosEdadesEl = document.querySelector('#trasladosEdadesChart');
    if (trasladosEdadesEl) {
        new ApexCharts(trasladosEdadesEl, {
            ...commonDonutOptions,
            labels: ['Adultos', 'Warriors'],
            series: [{{ $trasladosAdultos }}, {{ $trasladosMenores }}],
            colors: ['#28c76f', '#00cfe8']
        }).render();
    }

    const bautismosEdadesEl = document.querySelector('#bautismosEdadesChart');
    if (bautismosEdadesEl) {
        new ApexCharts(bautismosEdadesEl, {
            ...commonDonutOptions,
            labels: ['Adultos', 'Warriors'],
            series: [{{ $bautismosAdultos }}, {{ $bautismosMenores }}],
            colors: ['#28c76f', '#00cfe8']
        }).render();
    }

    window.alertaExportandoExcel = function() {
        Swal.fire({
            title: 'Preparando Excel...',
            html: 'Por favor, espera un momento.<br><br><b>La descarga iniciará automáticamente.</b>',
            icon: 'info',
            timer: 4500,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    };
  });

  document.addEventListener('DOMContentLoaded', function() {
      // Cosecha Semanal
      const dataCosecha = @json($datosGraficaSemanal ?? []);
      if(dataCosecha.length > 0) {
          new ApexCharts(document.querySelector("#cosechaSemanalChart"), {
              series: [{ name: "Cosecha", data: dataCosecha.map(item => item.y) }, { name: "Deserciones", data: dataCosecha.map(item => item.y_desercion) }],
              chart: { height: 350, type: 'line', toolbar: { show: false } },
              dataLabels: { enabled: true },
              stroke: { curve: 'smooth', width: 3 },
              colors: ['#7367f0', '#ea5455'],
              xaxis: { categories: dataCosecha.map(item => item.x), labels: { style: { fontSize: '10px', fontFamily: 'Poppins' } } },
              yaxis: { labels: { formatter: val => Math.floor(val), style: { fontFamily: 'Poppins' } } },
              tooltip: { y: { formatter: val => val + " personas" } }
          }).render();
      }

      // Matrículas Semanal
      const dataMatriculas = @json($datosMatriculasSemanal ?? []);
      if(dataMatriculas.length > 0) {
          new ApexCharts(document.querySelector("#matriculasSemanalChart"), {
              series: [{ name: "Matrículas", data: dataMatriculas.map(item => item.y) }],
              chart: { height: 350, type: 'line', toolbar: { show: false } },
              dataLabels: { enabled: true },
              stroke: { curve: 'smooth', width: 3 },
              colors: ['#28c76f'],
              xaxis: { categories: dataMatriculas.map(item => item.x), labels: { style: { fontSize: '10px', fontFamily: 'Poppins' } } },
              yaxis: { labels: { formatter: val => Math.floor(val), style: { fontFamily: 'Poppins' } } }
          }).render();
      }

      // Vinculación Semanal
      const dataVinc = @json($datosVinculacionSemanal ?? ['labels' => [], 'series' => []]);
      if(dataVinc.labels.length > 0) {
          new ApexCharts(document.querySelector("#vinculacionSemanalChart"), {
              series: dataVinc.series,
              chart: { height: 380, type: 'bar', stacked: true, toolbar: { show: false } },
              colors: ['#7367f0', '#28c76f', '#ea5455', '#ff9f43', '#00cfe8', '#82868b'],
              plotOptions: { bar: { columnWidth: '55%' } },
              xaxis: { categories: dataVinc.labels, labels: { style: { fontSize: '10px', fontFamily: 'Poppins' } } },
              yaxis: { labels: { formatter: val => Math.floor(val), style: { fontFamily: 'Poppins' } } },
              legend: { position: 'top', horizontalAlign: 'left', fontFamily: 'Poppins' }
          }).render();
      }
  });
</script>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1 fw-semibold text-primary">Consolidación: {{ $sede->nombre }}</h4>
   
  </div>
  <div class="d-flex">
    <button type="submit" form="formFiltros" formaction="{{ route('sede.dashboardConsolidacion.exportar', $sede) }}" class="btn btn-primary rounded-pill me-2 shadow-sm" onclick="window.alertaExportandoExcel()">
      <i class="ti ti-file-spreadsheet me-1"></i> Exportar
    </button>
  </div>
</div>

<!-- Barra de Filtros -->
<form id="formFiltros" action="{{ route('sede.dashboardConsolidacion', $sede) }}" method="GET">
  <div class="row bg-white rounded-3 p-0 m-0 mb-4 shadow-sm border border-gray">
    <div class="row col-12 col-md-11 p-0 m-0">
      <div class="col-12 col-md-4 border-end border-gray p-0 d-flex">
          <div class="input-group input-group-merge">
            <span class="input-group-text bg-transparent border-none"><i class="ti ti-calendar text-black"></i></span>
            <select class="form-select text-black border-none" id="filtro_rapido" onchange="seleccionarRango(this.value)">
                <option value="">Rango de fecha</option>
                <option value="este_mes">Este mes</option>
                <option value="mes_pasado">Mes pasado</option>
                <option value="este_ano">Este año</option>
                <option value="trimestre_actual">Trimestre actual</option>
            </select>
          </div>
      </div>
      <div class="col-12 col-md-8 p-0 d-flex">
          <input type="text" class="form-control border-none flatpickr-range text-center" id="rango_fechas" name="rango_fechas" value="{{ $rangoFechas }}" placeholder="DD/MM/AAAA - DD/MM/AAAA">
      </div>
    </div>
    <div class="col-12 col-md-1 p-0">
      <button type="submit" class="btn btn-primary w-100 rounded-0 rounded-end h-100 fs-6">Filtrar</button>
    </div>
  </div>
</form>

<!-- Tabs -->
@php $activeTab = request('tab', 'cosecha'); @endphp
<div class="card mb-4 p-1">
  <ul class="nav nav-pills gap-2" role="tablist">
    <li class="nav-item flex-fill">
      <button class="nav-link p-3 w-100 {{ $activeTab == 'cosecha' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-cosecha">Cosecha</button>
    </li>
    <li class="nav-item flex-fill">
      <button class="nav-link p-3 w-100 {{ $activeTab == 'escuelas' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-escuelas">Escuelas</button>
    </li>
    <li class="nav-item flex-fill">
      <button class="nav-link p-3 w-100 {{ $activeTab == 'membresias' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-membresias">Membresías</button>
    </li>
  </ul>
</div>

<div class="tab-content p-0 bg-transparent shadow-none">
  <!-- Tab Cosecha -->
  <div class="tab-pane fade {{ $activeTab == 'cosecha' ? 'show active' : '' }}" id="tab-cosecha" role="tabpanel">
    <div class="row g-2 mb-4">


      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'cosecha_total', 'rango_fechas' => $rangoFechas]) }}">{{ $totalCosecha }}</a>
              </h5>
              <small class="text-black">
                Total cosecha
              </small>
            </div>
          </div>
        </div>
      </div>

      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'cosecha_efectiva', 'rango_fechas' => $rangoFechas]) }}">{{ $cosechaEfectiva }}</a>
              </h5>
              <small class="text-black">
                Cosecha efectiva
              </small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center pb-0">
            <small class="text-black">Efectividad de la cosecha</small>
            <h4 class="text-black fw-semibold mb-0">
                {{ $porcentajeEfectividad }}%
              </h4>
          </div>
          <div class="card-body">              
            <div class="progress" style="height: 8px;">
              <div class="progress-bar" role="progressbar" style="width: {{ $porcentajeEfectividad }}%" aria-valuenow="{{ $porcentajeEfectividad }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>

    
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h6 class="card-title mb-0 fw-bold">Cosecha por vinculación</h6>
              <small class="text-black">
                Cosecha total
              </small>
            </div>
          </div>
          <div class="card-body">
            <div id="vinculacionChart" data-series='@json($vinculacionesCosecha->pluck("usuarios_count"))' data-labels='@json($vinculacionesCosecha->pluck("nombre"))' data-total='{{ $totalCosecha }}'></div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h6 class="card-title text-uppercase mb-0 fw-bold">Lista de cosecha por vinculación</h6>
              <small class="text-black">
                Detalle de cosecha por vinculación
              </small>
            </div>
          </div>

          <div class="card-body">
            
              <div class="row">
                 @foreach ($vinculacionesCosecha as $v)
                <div class=" col-12 d-flex flex-column">
                  <small class="text-black">{{ $v->nombre }} </small>
                  <small class="fw-semibold text-black ">
                    <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'cosecha_vinculacion_' . $v->id, 'rango_fechas' => $rangoFechas]) }}">{{ $v->usuarios_count }}</a>
                  </small>
                  <hr class="my-3 border-2">
                </div>
                @endforeach
              </div>

          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title mb-0 fw-bold">Cosecha por semanas</h6>
            <small class="text-black">
              Histórico de cosecha por semana
            </small>
          </div>
        </div>
        <div class="card-body"><div id="cosechaSemanalChart"></div></div>
    </div>
    
    <div class="card">
         <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title mb-0 fw-bold">Tipo de vinculación semanal</h6>
            <small class="text-black">
              Histórico semanal según tipo de vinculación
            </small>
          </div>
        </div>
        <div class="card-body"><div id="vinculacionSemanalChart"></div></div>
    </div>
  </div>

  <!-- Tab Escuelas -->
  <div class="tab-pane fade {{ $activeTab == 'escuelas' ? 'show active' : '' }}" id="tab-escuelas" role="tabpanel">
    <div class="row g-2 mb-4">

      <!-- Total Matrículas -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'total_matriculas', 'rango_fechas' => $rangoFechas]) }}">{{ $totalMatriculas }}</a>
              </h5>
              <small class="text-black">Total matrículas</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Matrículas Efectivas -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'matriculas_efectivos', 'rango_fechas' => $rangoFechas]) }}">{{ $matriculasEfectivos }}</a>
              </h5>
              <small class="text-black">Matrículas efectivas</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Efectividad de Matrículas -->
      <div class="col-12 col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center pb-0">
            <small class="text-black">Efectividad de matrículas</small>
            <h4 class="text-black fw-semibold mb-0">
                {{ $porcentajeEfectividadMatriculas }}%
              </h4>
          </div>
          <div class="card-body">              
            <div class="progress" style="height: 8px;">
              <div class="progress-bar" role="progressbar" style="width: {{ $porcentajeEfectividadMatriculas }}%" aria-valuenow="{{ $porcentajeEfectividadMatriculas }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>


        
    </div>
    <div class="row g-2 mb-4">
        <div class="col-md-4">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
              <div>
                <h6 class="card-title mb-0 fw-bold">Matrículas</h6>
                <small class="text-black">
                  Templo vs sector
                </small>
              </div>
            </div>
            <div class="card-body">
              <div id="matriculasTipo"></div>
            </div>
          </div>
        </div>
        <div class="col-md-4"><div class="card h-100">
          <div class="card-header d-flex justify-content-between">
              <div>
                <h6 class="card-title mb-0 fw-bold">Matrículas sector</h6>
                <small class="text-black">
                  Adultos vs Warriors
                </small>
              </div>
            </div><div class="card-body">
              <div id="sectorEdadChart"></div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
              <div>
                <h6 class="card-title mb-0 fw-bold">Matrículas templo</h6>
                <small class="text-black">
                  Adultos vs Warriors
                </small>
              </div>
            </div>
            <div class="card-body">
              <div id="temploEdadChart"></div>
            </div>
          </div>
        </div>
    </div>
    <div class="row g-2 mb-4">
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
              <div>
                <h6 class="card-title mb-0 fw-bold">Aptos</h6>
                <small class="text-black">
                  Aptos vs Unión Libre
                </small>
              </div>
            </div>
            <div class="card-body">
              <div id="unionLibreChart"></div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
              <div>
                <h6 class="card-title mb-0 fw-bold">Efectividad Matrículas</h6>
                <small class="text-black">
              Efectivos vs Deserciones
            </small>
              </div>
            </div>
            <div class="card-body">
              <div id="desercionesChart"></div>
            </div>
          </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title mb-0 fw-bold">Matrículas por semana</h6>
            <small class="text-black">
              Histórico de matrículas por semana
            </small>
          </div>
        </div>
        <div class="card-body">
          <div id="matriculasSemanalChart"></div>
        </div>
    </div> 
  </div>

  <!-- Tab Membresías -->
  <div class="tab-pane fade {{ $activeTab == 'membresias' ? 'show active' : '' }}" id="tab-membresias" role="tabpanel">
    <div class="row equal-height-row g-2">

       <!-- Total miembros -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'total_miembros', 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none hover-link">{{ $totalMiembros }}</a>
              </h5>
              <small class="text-black">
                Total miembros
              </small>
            </div>
          </div>
        </div>
      </div>

      <h6 class="mb-4 text-black">Total matrículas efectivas vs. Total aptos membresías</h6>
      
      <!-- Bautismos vs Traslados -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h6 class="card-title mb-0 fw-bold">Membresías</h6>
              <small class="text-black">Bautismos vs Traslados</small>
            </div>
          </div>
          <div class="card-body">                  
            <div id="bautismosTrasladosChart" style="min-height: 250px;"></div>
          </div>
        </div>
      </div>
      <!-- Traslados: Adultos vs Warriors -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h6 class="card-title mb-0 fw-bold">Traslados</h6>
              <small class="text-black">Adultos vs Warriors</small>
            </div>
          </div>
          <div class="card-body">                  
            <div id="trasladosEdadesChart" style="min-height: 250px;"></div>
          </div>
        </div>
      </div>
      <!-- Bautismos: Adultos vs Warriors -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h6 class="card-title mb-0 fw-bold">Bautismos</h6>
              <small class="text-black">Adultos vs Warriors</small>
            </div>
          </div>
          <div class="card-body">                  
            <div id="bautismosEdadesChart" style="min-height: 250px;"></div>
          </div>
        </div>
      </div>


      <!-- Efectividad matrículas a membresías -->      
      <div class="col-12 col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center pb-0">
            <small class="text-black">Efectividad matrículas a membresías</small>
            <h4 class="text-black fw-semibold mb-0">
              {{ $efectividadMembresiasAptos }}%
            </h4>
          </div>
          <div class="card-body">              
            <div class="progress" style="height: 8px;">
              <div class="progress-bar" role="progressbar" style="width: {{ $efectividadMembresiasAptos }}%" aria-valuenow="{{ $efectividadMembresiasAptos }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>
     
      <hr>
      <h6 class="mb-4 text-black ">Personas en unión libre matriculadas en CHLL vs. Total membresías</h6>

      <!-- Unión libre matriculados -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'union_libre_matriculados', 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none hover-link">{{ $totalUnionLibreMatriculados }}</a>
              </h5>
              <small class="text-black">
                Unión libre matriculados
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- Miembros que estaban en unión libre -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'miembros_formalizados', 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none hover-link">{{ $miembrosFormalizados }}</a>
              </h5>
              <small class="text-black">
                Miembros que estaban en unión libre
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- Pendientes por membresía (Unión libre) -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'pendientes_membresia_union_libre', 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none hover-link">{{ $pendientesMembresiaUnionLibre }}</a>
              </h5>
              <small class="text-black">
                Pendientes por membresía (Unión libre)
              </small>
            </div>
          </div>
        </div>
      </div>

      @php
        $efectividadFormalizacionUnionLibre = $totalUnionLibreMatriculados > 0 ? round(($miembrosFormalizados / $totalUnionLibreMatriculados) * 100, 2) : 0;
      @endphp
       <!-- Efectividad formalización unión libre -->
      <div class="col-12 col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center pb-0">
            <small class="text-black">Efectividad formalización unión libre</small>
            <h4 class="text-black fw-semibold mb-0">
              {{ $efectividadFormalizacionUnionLibre }}%
            </h4>
          </div>
          <div class="card-body">              
            <div class="progress" style="height: 8px;">
              <div class="progress-bar" role="progressbar" style="width: {{ $efectividadFormalizacionUnionLibre }}%" aria-valuenow="{{ $efectividadFormalizacionUnionLibre }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div> 
      </div>

      <hr>

      <h6 class="mb-4 text-black ">Membresías VS. Ubicación en grupos</h6>
    

      <!-- Miembros ubicados en grupo -->
      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi', [$sede, 'kpi' => 'miembros_ubicados', 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none hover-link">{{ $miembrosUbicados }}</a>
              </h5>
              <small class="text-black">
                Miembros ubicados en grupo
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- Efectividad ubicación en grupos-->
      <div class="col-12 col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center pb-0">
            <small class="text-black">Efectividad ubicación en grupos</small>
            <h4 class="text-black fw-semibold mb-0">
              {{ $porcentajeEfectividadMembresia }}%
            </h4>
          </div>
          <div class="card-body">              
            <div class="progress" style="height: 8px;">
              <div class="progress-bar" role="progressbar" style="width: {{ $porcentajeEfectividadMembresia }}%" aria-valuenow="{{ $porcentajeEfectividadMembresia }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
