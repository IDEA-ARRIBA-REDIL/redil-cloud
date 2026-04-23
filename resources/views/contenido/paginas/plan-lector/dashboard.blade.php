@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard plan lector')

@section('page-style')
@vite([
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
<style>
  .card-top-list {
    transition: transform 0.2s;
  }
  .card-top-list:hover {
    transform: translateY(-5px);
  }
  .table-premium thead th {
    background-color: #f8f9fa;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
    font-weight: 700;
  }
  .progress-premium {
    height: 8px;
    border-radius: 10px;
    background-color: #eee;
  }

</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js'
])
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('.selectpicker').selectpicker();

    // Configuración de Flatpickr en español
    flatpickr.l10ns.es = {
      weekdays: {
        shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
      },
      months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      },
      rangeSeparator: ' a ',
    };

    flatpickr(".flatpickr-range", {
      mode: "range",
      dateFormat: "Y-m-d",
      locale: "es",
      onChange: function(selectedDates, dateStr, instance) {
         document.getElementById('filtro_rapido').value = "";
      }
    });

    // Función para el filtro rápido
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
      } else if (tipo === 'esta_semana') {
        const diaSemana = hoy.getDay();
        const diff = hoy.getDate() - diaSemana + (diaSemana === 0 ? -6 : 1); // Lunes
        inicio = new Date(hoy.setDate(diff));
        fin = new Date(inicio);
        fin.setDate(inicio.getDate() + 6);
      } else if (tipo === 'semana_pasada') {
        const diaSemana = hoy.getDay();
        const diff = hoy.getDate() - diaSemana + (diaSemana === 0 ? -6 : 1) - 7; // Lunes pasado
        inicio = new Date(hoy.setDate(diff));
        fin = new Date(inicio);
        fin.setDate(inicio.getDate() + 6);
      }

      const fp = document.querySelector("#rango_fechas")._flatpickr;
      if (fp) {
        fp.setDate([inicio, fin], true);
      }
    };

    // --- GRÁFICOS DIVIDIDOS ---

    const commonOptions = {
        chart: {
          height: 300,
          type: 'area',
          toolbar: { show: false },
          fontFamily: 'Public Sans'
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: {
          categories: @json($labelsGrafica),
          labels: { 
            rotate: -45,
            rotateAlways: true,
            style: { fontSize: '10px' } 
          }
        },
        fill: {
          type: 'gradient',
          gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1, stops: [0, 90, 100] }
        },
        tooltip: { x: { format: 'dd/MM/yy' } }
    };

    // 1. Gráfico de Inscripciones
    const inscripcionesEl = document.querySelector('#inscripcionesChart');
    if (inscripcionesEl) {
      const config = {...commonOptions, 
        colors: ['#7367f0'],
        series: [{ name: 'Inscripciones', data: @json($dataSerieInscripciones) }]
      };
      new ApexCharts(inscripcionesEl, config).render();
    }

    // 2. Gráfico de Lecturas
    const lecturasEl = document.querySelector('#lecturasChart');
    if (lecturasEl) {
      const config = {...commonOptions, 
        colors: ['#28c76f'],
        series: [{ name: 'Lecturas', data: @json($dataSerieLecturas) }]
      };
      new ApexCharts(lecturasEl, config).render();
    }
  });
</script>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-1 fw-bold text-primary">Dashboard plan lector</h4>
</div>

<!-- Filtros -->
<form action="{{ route('planes-lectores.dashboard') }}" method="GET">
  <div class="row bg-white rounded-3 p-0 m-0 mb-4 shadow-sm border border-gray">
    <div class="row col-12 col-md-11 p-0 m-0">
      
      <!-- Rango Predefinido -->
      <div class="col-12 col-md-3 border-end border-gray p-0 d-flex">
        <div class="input-group input-group-merge">
          <span class="input-group-text bg-transparent border-none"><i class="ti ti-calendar text-black"></i></span>
          <select class="form-select text-black border-none" id="filtro_rapido" onchange="seleccionarRango(this.value)">
            <option value="">Opciones rápidas</option>
            <option value="este_mes">Este mes</option>
            <option value="mes_pasado">Mes pasado</option>
            <option value="este_ano">Este año</option>
            <option value="esta_semana">Esta semana</option>
            <option value="semana_pasada">Semana pasada</option>
          </select>
        </div>
      </div>

      <!-- Rango Personalizado -->
      <div class="col-12 col-md-4 border-end border-gray p-0 d-flex">
        <input type="text" class="form-control border-none flatpickr-range text-center" id="rango_fechas" name="rango_fechas" value="{{ $rangoFechas }}" placeholder="Seleccionar rango de fechas">
      </div>

      <!-- Sede -->
      <div class="col-12 col-md-5 border-end border-gray p-0 d-flex align-items-center">
        <select name="sede_id" class="selectpicker form-select border-none w-100" data-style="btn-default border-0" data-live-search="true" title="Filtrar por Sede (Opcional)">
          <option value="">Todas las Sedes</option>
          @foreach($sedes as $sede)
            <option value="{{ $sede->id }}" {{ $sedeId == $sede->id ? 'selected' : '' }}>{{ $sede->nombre }}</option>
          @endforeach
        </select>
      </div>
    </div>
    
    <!-- Botón Filtrar -->
    <div class="col-12 col-md-1 p-0">
      <button type="submit" class="btn btn-primary w-100 rounded-0 rounded-end h-100 fs-6">Filtrar</button>
    </div>

  </div>
</form>

<!-- KPIs -->
<div class="row g-4 mb-4 mt-10">

    <!-- Inscripciones --> 
    <div class="col-sm-6 col-md-4 equal-height-col">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h5 class="card-title text-uppercase mb-0 fw-semibold">
              {{ number_format($totalInscritos) }}
            </h5>
            <small class="text-black">
              Total inscripciones
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Lecturas -->
    <div class="col-sm-6 col-md-4 equal-height-col">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h5 class="card-title text-uppercase mb-0 fw-semibold">
              {{ number_format($totalLecturas) }}
            </h5>
            <small class="text-black">
              Lecturas diarias
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Finalizaciones -->
    <div class="col-sm-6 col-md-4 equal-height-col">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h5 class="card-title text-uppercase mb-0 fw-semibold">
              {{ number_format($totalFinalizados) }}
            </h5>
            <small class="text-black">
              Planes completados
            </small>
          </div>
        </div>
      </div>
    </div>


</div>

<!-- Gráficos de Actividad Divididos -->
<div class="row mb-4">
  <div class="col-12 mb-4">
    <div class="card h-100 ">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-semibold">Tendencia de inscripciones</h5>
        <span class="badge bg-label-primary">Total: {{ number_format($totalInscritos) }}</span>
      </div>
      <div class="card-body">
        <div id="inscripcionesChart"></div>
      </div>
    </div>
  </div>

  <div class="col-12 mb-4">
    <div class="card h-100 ">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-semibold">Tendencia de lecturas</h5>
        <span class="badge bg-label-success">Total: {{ number_format($totalLecturas) }}</span>
      </div>
      <div class="card-body">
        <div id="lecturasChart"></div>
      </div>
    </div>
  </div>
</div>

<!-- Tablas Premium -->
<div class="row">
  <!-- Top 10 Planes -->
  <div class="col-12 col-md-6 mb-4">
    <div class="card card-top-list h-100 ">
      <div id="planesTable" class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-semibold">Top 10 planes más populares</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-premium mb-0">
          <thead>
            <tr>
              <th class="ps-4">Puesto</th>
              <th>Título</th>
              <th class="text-center">Inscritos</th>
              <th>Progreso promedio</th>
            </tr>
          </thead>
          <tbody>
            @forelse($topPlanes as $index => $plan)
            <tr>
              <td class="fw-bold text-center">
                  {{ $index + 1 }}
              </td>
              <td>
                <span class="text-dark fw-normal fs-6">{{ $plan->titulo }}</span>
              </td>
              <td class="fw-semibold text-center text-black">
                {{ number_format($plan->total) }}
              </td>
              <td>
                <div class="d-flex align-items-center mt-1">
                  <div class="progress w-100 progress-premium me-2">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $plan->progreso_promedio }}%" aria-valuenow="{{ $plan->progreso_promedio }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <small class="fw-bold text-black">{{ number_format($plan->progreso_promedio, 0) }}%</small>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center text-black py-4">No hay planes registrados en este rango.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Top 10 Categorías -->
  <div class="col-12 col-md-6 mb-4">
    <div class="card card-top-list h-100 ">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-semibold">Top 10 categorías más populares</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-premium mb-0">
          <thead>
            <tr>
              <th class="ps-4">Puesto</th>
              <th>Categoría</th>
              <th class="text-center">Inscritos</th>
            </tr>
          </thead>
          <tbody>
            @forelse($topCategorias as $index => $cat)
            <tr>
              <td class="fw-bold text-center text-black">
                  {{ $index + 1 }}
              </td>
              <td>
                <span class="fw-normal text-black fs-6">{{ $cat->nombre }}</span>
              </td>
              <td class="fw-semibold text-center text-black">
                {{ number_format($cat->total) }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="text-center text-black py-4">No hay categorías activas. </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Top 10 Autores -->
  <div class="col-12">
    <div class="card card-top-list ">
      <div class="card-header d-flex flex-column align-items-start">
        <h5 class="card-title mb-0 fw-semibold">Top 10 autores más populares</h5>
        <span class="text-muted small">Por número de inscritos en sus planes</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-premium mb-0">
          <thead>
            <tr>
              <td class="fw-bold text-center">
              <th>Autor</th>
              <th class="text-center text-black">Total de inscritos en sus planes</th>
            </tr>
          </thead>
          <tbody>
            @forelse($topAutores as $index => $autor)
            <tr>
              <td class="ps-4 fw-bold text-center text-black">
                  {{ $index + 1 }}
              </td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-2">
                    @if($autor->foto == "default-m.png" || $autor->foto == "default-f.png")
                      <span class="avatar-initial rounded-circle bg-label-{{ ['primary', 'success', 'danger', 'info', 'warning'][rand(0, 4)] }}">
                        {{ strtoupper(substr($autor->primer_nombre, 0, 1) . substr($autor->primer_apellido, 0, 1)) }}
                      </span>
                    @else
                      <img src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$autor->foto) }}" alt="{{ $autor->foto }}" class="rounded-circle">
                    @endif
                  </div>
                  <span class="fw-normal text-dark fs-6">{{ $autor->name }}</span>
                </div>
              </td>
              <td class="fw-semibold text-center text-black">{{ number_format($autor->total) }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="text-center text-black py-4">No se encontraron autores con planes activos en este periodo.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
