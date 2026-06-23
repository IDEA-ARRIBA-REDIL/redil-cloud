@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard de Peticiones')

@section('page-style')
@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js'
])
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    // Definición manual del español para evitar problemas de carga de archivos
    flatpickr.l10ns.es = {
      weekdays: {
        shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
      },
      months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      },
      ordinal: () => {
        return 'º';
      },
      firstDayOfWeek: 1, // Lunes inicia la semana
      rangeSeparator: ' a ',
      time_24hr: true,
    };

    // Funciones auxiliares para semana (Lunes-Domingo)
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

    // Función global para el filtro rápido de fechas
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

      // Ajustar a semanas completas
      inicio = getMonday(inicio);
      fin = getSunday(fin);

      const fpInput = document.querySelector("#rango_fechas")._flatpickr;
      if (fpInput) {
        fpInput.setDate([inicio, fin], true);
      }
    };

    // Gráfico Donut Registrados vs Externos
    const tipoUsuarioEl = document.querySelector('#tipoUsuarioChart');
    if (tipoUsuarioEl) {
      const tipoUsuarioConfig = {
        chart: {
          height: 220,
          type: 'donut',
          toolbar: { show: false }
        },
        labels: ['Registrados', 'Externos'],
        series: [{{ $registrados }}, {{ $externos }}],
        colors: ['#28c76f', '#ea5455'],
        dataLabels: {
          enabled: true,
          formatter: function (val, opts) {
            return opts.w.config.series[opts.seriesIndex]
          }
        },
        plotOptions: {
          pie: {
            donut: {
              size: '70%',
              labels: {
                show: true,
                name: {
                  fontSize: '1rem',
                  fontFamily: 'Poppins'
                },
                value: {
                  fontSize: '1.2rem',
                  fontFamily: 'Poppins',
                  formatter: function (val) {
                    return parseInt(val);
                  }
                },
                total: {
                  show: true,
                  fontSize: '0.8rem',
                  label: 'Total',
                  formatter: function (w) {
                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                  }
                }
              }
            }
          }
        },
        legend: {
          show: true,
          position: 'bottom',
          markers: { offsetX: -3 },
          itemMargin: { vertical: 3, horizontal: 10 }
        },
        tooltip: { enabled: true }
      };
      new ApexCharts(tipoUsuarioEl, tipoUsuarioConfig).render();
    }

    // Gráfico de Líneas Histórico de Peticiones
    const historicoEl = document.querySelector('#historicoPeticionesChart');
    if (historicoEl) {
      const lineData = @json($datosGraficaLineas);
      const seriesData = lineData.map(item => item.y);
      const labelsData = lineData.map(item => item.x);

      const historicoConfig = {
        series: [{
          name: "Peticiones",
          data: seriesData
        }],
        chart: {
          height: 220,
          type: 'line',
          zoom: { enabled: false },
          toolbar: { show: false }
        },
        dataLabels: { enabled: true },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#7367f0'],
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'],
            opacity: 0.5
          },
        },
        xaxis: {
          categories: labelsData,
          labels: {
            style: {
              fontSize: '10px',
              fontFamily: 'Poppins'
            }
          }
        },
        yaxis: {
          labels: {
            formatter: function (val) {
              return Math.floor(val);
            },
            style: {
              fontFamily: 'Poppins'
            }
          }
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + " peticiones"
            }
          }
        }
      };
      new ApexCharts(historicoEl, historicoConfig).render();
    }

    // Gráfico de Países
    const paisesEl = document.querySelector('#paisesChart');
    if (paisesEl) {
      const seriesData = @json($peticionesPorPais->pluck('total'));
      const labelsData = @json($peticionesPorPais->pluck('nombre'));
      const total = {{ $totalPeticiones }};

      const paisesConfig = {
        chart: {
          height: 380,
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
          formatter: function (val) {
            return parseInt(val) + '%';
          }
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
                name: {
                  fontSize: '1.5rem',
                  fontFamily: 'Poppins'
                },
                value: {
                  fontSize: '1.2rem',
                  fontFamily: 'Poppins',
                  formatter: function (val) {
                    return parseInt(val) + ' Peticiones';
                  }
                },
                total: {
                  show: true,
                  fontSize: '1.5rem',
                  label: 'Total',
                  formatter: function (w) {
                    return total;
                  }
                }
              }
            }
          }
        }
      };

      new ApexCharts(paisesEl, paisesConfig).render();
    }

    // Gráfico de Tipos de Petición
    const tiposEl = document.querySelector('#tiposChart');
    if (tiposEl) {
      const seriesData = @json($peticionesPorTipo->pluck('total'));
      const labelsData = @json($peticionesPorTipo->pluck('nombre'));
      const total = {{ $totalPeticiones }};

      const tiposConfig = {
        chart: {
          height: 380,
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
          formatter: function (val) {
            return parseInt(val) + '%';
          }
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
                name: {
                  fontSize: '1.5rem',
                  fontFamily: 'Poppins'
                },
                value: {
                  fontSize: '1.2rem',
                  fontFamily: 'Poppins',
                  formatter: function (val) {
                    return parseInt(val) + ' Peticiones';
                  }
                },
                total: {
                  show: true,
                  fontSize: '1.5rem',
                  label: 'Total',
                  formatter: function (w) {
                    return total;
                  }
                }
              }
            }
          }
        }
      };

      new ApexCharts(tiposEl, tiposConfig).render();
    }
  });
</script>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1 fw-semibold text-primary">Dashboard de Peticiones</h4>
    <p class="mb-0 text-black">Métricas, distribución geográfica y tipologías de las peticiones recibidas.</p>
  </div>
</div>

<!-- Barra de Filtros -->
<form id="formFiltros" action="{{ route('peticion.dashboard') }}" method="GET">
  <div class="row bg-white rounded-3 p-0 m-0 mb-4 shadow-sm border border-gray">
    <div class="row col-12 col-md-11 p-0 m-0">
      
      <!-- Rango Predefinido -->
      <div class="col-12 col-md-4 border-end border-gray p-0 d-flex">
          <div class="input-group input-group-merge ">
            <span class="input-group-text bg-transparent border-none"><i class="ti ti-calendar text-black"></i></span>
            <select class="form-select text-black border-none" id="filtro_rapido" onchange="seleccionarRango(this.value)" >
                <option value="">Rango de fecha</option>
                <option value="este_mes">Este mes</option>
                <option value="mes_pasado">Mes pasado</option>
                <option value="este_ano">Este año</option>
                <option value="trimestre_actual">Trimestre actual</option>
            </select>
          </div>
      </div>

      <!-- Flatpickr Range -->
      <div class="col-12 col-md-8 p-0 d-flex">
          <input type="text" class="form-control border-none flatpickr-range text-center w-100" id="rango_fechas" name="rango_fechas" value="{{ $rangoFechas }}" placeholder="DD/MM/AAAA - DD/MM/AAAA">
      </div>
            
    </div>
    
    <!-- Botón Filtrar -->
    <div class="col-12 col-md-1 p-0">
      <button type="submit" class="btn btn-xl btn-primary w-100 rounded-0 rounded-end h-100 px-auto fs-6">Filtrar</button>
    </div>
  </div>
</form>


<!-- Primera Sección: KPIs -->
<div class="row g-4 mb-4 mt-10">

  <!-- Total de peticiones -->
  <div class="col-12 col-sm-4 col-md col-lg ">  
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h5 class="card-title text-uppercase mb-0 fw-semibold">
            <a href="{{ route('peticion.dashboard.detalle-kpi', ['kpi' => 'total', 'rango_fechas' => $rangoFechas]) }}">{{ $totalPeticiones }}</a>
          </h5>
          <small class="text-black">
            Total peticiones
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Peticiones Pendientes -->
  <div class="col-12 col-sm-4 col-md col-lg ">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h5 class="card-title text-uppercase mb-0 fw-semibold">
            <a href="{{ route('peticion.dashboard.detalle-kpi', ['kpi' => 'pendientes', 'rango_fechas' => $rangoFechas]) }}">{{ $pendientes }}</a>
          </h5>
          <small class="text-black">
            Pendientes
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Peticiones En Proceso -->
  <div class="col-12 col-sm-4 col-md col-lg ">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h5 class="card-title text-uppercase mb-0 fw-semibold">
            <a href="{{ route('peticion.dashboard.detalle-kpi', ['kpi' => 'en_proceso', 'rango_fechas' => $rangoFechas]) }}">{{ $enProceso }}</a>
          </h5>
          <small class="text-black">
            En proceso
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Peticiones Cerradas -->
  <div class="col-12 col-sm-4 col-md col-lg ">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h5 class="card-title text-uppercase mb-0 fw-semibold">
            <a href="{{ route('peticion.dashboard.detalle-kpi', ['kpi' => 'cerradas', 'rango_fechas' => $rangoFechas]) }}">{{ $cerradas }}</a>
          </h5>
          <small class="text-black">
            Cerradas
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Peticiones Sin Asignar -->
  <div class="col-12 col-sm-4 col-md col-lg ">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h5 class="card-title text-uppercase mb-0 fw-semibold">
            <a href="{{ route('peticion.dashboard.detalle-kpi', ['kpi' => 'sin_asignar', 'rango_fechas' => $rangoFechas]) }}">{{ $sinAsignar }}</a>
          </h5>
          <small class="text-black">
            Sin asignar
          </small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Gráficos de Primera Sección: Proporción y Evolución Temporal -->
<div class="row g-4 mb-4">

  <!-- Proporción Registrados vs Externos -->
  <div class="col equal-height-col col-12 col-xl-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h6 class="card-title mb-0 fw-bold">Peticiones registrados vs peticiones externas</h6>
          <small class="text-black">
            Peticiones de usuarios registrados vs externos/invitados
          </small>
        </div>
      </div>
      <div class="card-body">
        <div id="tipoUsuarioChart" style="min-height: 250px;"></div>
      </div>
    </div>
  </div>

  <!-- Histórico de Peticiones -->
  <div class="col equal-height-col col-12 col-xl-8 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h6 class="card-title mb-0 fw-bold">Peticiones en el tiempo</h6>
          <small class="text-black">
            Evolución temporal de la cantidad de peticiones ({{ $diasDiferencia <= 30 ? 'Diaria' : 'Mensual' }})
          </small>
        </div>
      </div>
      <div class="card-body">
        <div id="historicoPeticionesChart" style="min-height: 250px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Segunda Sección: Países -->
<div class="row g-4 mb-4">
  <!-- Gráfico de distribución geográfica -->

  <div class="col equal-height-col col-xl-8 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h6 class="card-title mb-0 fw-bold">Distribución por países</h6>
          <small class="text-black">
            Proporción de peticiones enviadas desde cada país
          </small>
        </div>
      </div>
      <div class="card-body">
        <div id="paisesChart" style="min-height: 250px;"></div>
      </div>
    </div>
  </div>

  <!-- Listado de Países -->  
  <div class="col equal-height-col col-12 col-xl-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h6 class="card-title mb-0 fw-bold">Peticiones por países</h6>
          <small class="text-black">
            Desglose cuantitativo por ubicación geográfica
          </small>
        </div>
      </div>
      <div class="card-body py-2" style="max-height: 400px; overflow-y: auto;">
        <ul class="list-group list-group-flush">
          @forelse ($peticionesPorPais as $paisInfo)
            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 cursor-pointer">
              <a href="{{ route('peticion.dashboard.detalle-kpi', ['pais_id' => $paisInfo['id'], 'rango_fechas' => $rangoFechas]) }}" class="d-flex justify-content-between align-items-center w-100 text-decoration-none">
                <div class="d-flex align-items-center">
                  @if ($paisInfo['codigo_alpha'])
                    <i class="fis fi fi-{{ $paisInfo['codigo_alpha'] }} rounded-circle fs-3 me-2 border shadow-xs"></i>
                  @else
                    <i class="ti ti-world rounded-circle fs-3 me-2 text-secondary bg-light p-1"></i>
                  @endif
                  <span class="text-black fw-medium">{{ $paisInfo['nombre'] }}</span>
                </div>
                <small class="fw-semibold ">
                  {{ $paisInfo['total'] }}
                </small>
              </a>
            </li>
          @empty
            <li class="list-group-item text-center text-muted px-0 py-3">No se encontraron peticiones en este rango.</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Tercera Sección: Tipos de Petición -->
<div class="row g-4">
  <!-- Gráfico de distribución por tipo -->
  <div class="col equal-height-col col-12 col-xl-8 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h6 class="card-title mb-0 fw-bold">Distribución por tipo de petición</h6>
          <small class="text-black">
            Proporción de peticiones según su temática o categoría
          </small>
        </div>
      </div>
      <div class="card-body">
        <div id="tiposChart" style="min-height: 380px;"></div>
      </div>
    </div>
  </div>

  <!-- Listado de Tipos -->
  <div class="col equal-height-col col-12 col-xl-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h6 class="card-title mb-0 fw-bold">Peticiones por tipo</h6>
          <small class="text-black">
            Desglose cuantitativo por tipo de petición
          </small>
        </div>
      </div>

       <div class="card-body">
            
              <div class="row">
                @foreach ($peticionesPorTipo as $tipoInfo)
                <div class=" col-12 d-flex flex-column">
                  <small class="text-black">{{ $tipoInfo['nombre'] }} </small>
                  <small class="fw-semibold text-black ">
                    <a href="{{ route('peticion.dashboard.detalle-kpi', ['tipo_peticion_id' => $tipoInfo['id'], 'rango_fechas' => $rangoFechas]) }}">{{ $tipoInfo['total'] }}</a>
                  </small>
                  <hr class="my-3 border-2">
                </div>
                @endforeach
              </div>

          </div>




    </div>
  </div>
</div>
@endsection
