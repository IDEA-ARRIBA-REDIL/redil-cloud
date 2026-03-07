@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard consolidación')

@section('page-style')
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

      const fp = document.querySelector("#rango_fechas")._flatpickr;
      if (fp) {
        fp.setDate([inicio, fin]);
      }
    };

    // Gráfico de Vinculación
    const chartEl = document.querySelector('#vinculacionChart');
    if (chartEl) {
      const seriesData = @json($vinculacionesCosecha->pluck('usuarios_count'));
      const labelsData = @json($vinculacionesCosecha->pluck('nombre'));

      const chartConfig = {
        chart: {
          height: 400,
          type: 'donut',
          toolbar: { show: true }
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
                    return parseInt(val) + ' Personas';
                  }
                },
                total: {
                  show: true,
                  fontSize: '1.5rem',
                  label: 'Total',
                  formatter: function (w) {
                    return '{{ $totalCosecha }}';
                  }
                }
              }
            }
          }
        }
      };

      const chart = new ApexCharts(chartEl, chartConfig);
      chart.render();
    }

    // Inicializar gráficos de los bloques
    document.querySelectorAll('.chart-bloque').forEach(function(el) {
        const seriesData = JSON.parse(el.getAttribute('data-series'));
        const labelsData = JSON.parse(el.getAttribute('data-labels'));
        const totalCosecha = el.getAttribute('data-total');

        const blockChartConfig = {
            chart: {
                height: 300,
                type: 'donut',
                toolbar: { show: false }
            },
            labels: labelsData,
            series: seriesData,
            colors: ['#7367f0', '#28c76f', '#ea5455', '#ff9f43', '#00cfe8', '#a8aaae'],
            stroke: { show: false },
            dataLabels: { enabled: false }, // Simplificado para las cards pequeñas
            legend: { show: false }, // Ocultamos leyenda para ahorrar espacio, ya hay lista al lado
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: { fontSize: '1rem', fontFamily: 'Public Sans' },
                            value: { fontSize: '1rem', fontFamily: 'Public Sans', formatter: val => parseInt(val) },
                            total: {
                                show: true,
                                fontSize: '1rem',
                                label: 'Total',
                                formatter: () => totalCosecha
                            }
                        }
                    }
                }
            }
        };
        new ApexCharts(el, blockChartConfig).render();
    });

    // Gráfico de Distribución (Sector vs Templo)
    const matriculasTipoEl = document.querySelector('#matriculasTipo');
    if (matriculasTipoEl) {
        const matriculasTipoConfig = {
            chart: {
                height: 150,
                type: 'bar',
                toolbar: { show: false }
            },
            plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '80%',
                distributed: true,
                borderRadius: 4
            }
            },
            grid: {
                show: false,
                padding: { top: 0, bottom: 0, left: 0 }
            },
            colors: ['#7367f0', '#ff9f43'],
            dataLabels: {
                enabled: true,
                formatter: val => Math.floor(val)
            },  
            series: [{
                name: 'Matrículas',
                data: [{{ $matriculasSector }}, {{ $matriculasTemplo }}]
            }],
            xaxis: {
                categories: ['Sector', 'Templo'],
                axisBorder: { show: false },
                axisTicks: { show: false },
                tickAmount: {{ min(max($matriculasSector, $matriculasTemplo, 1), 5) }},
                labels: {
                    formatter: val => Math.floor(val)
                }
            },
            yaxis: {
                labels: {
                    minWidth: 70,
                    style: {
                        colors: '#000000ff',
                        fontSize: '11px',
                        fontWeight: 400,
                        fontFamily: 'Poppins'
                    }
                }
            },
            legend: { show: false },
            tooltip: { enabled: true }
        };
        new ApexCharts(matriculasTipoEl, matriculasTipoConfig).render();
    }

    // Configuración base para gráficos de edad
    const ageChartBaseConfig = {
        chart: {
            height: 150,
            type: 'bar',
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '80%',
                distributed: true,
                borderRadius: 4
            }
        },
        grid: {
            show: false,
            padding: { top: 0, bottom: 0, left: 0 }
        },
        colors: ['#7367f0', '#ff9f43'],
        dataLabels: {
            enabled: true,
            formatter: val => Math.floor(val)
        },
        xaxis: {
            categories: ['Adultos', 'Warriors'],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                formatter: val => Math.floor(val)
            }
        },
        yaxis: {
            labels: {
                minWidth: 70,
                style: {
                    colors: '#000000ff',
                    fontSize: '11px',
                    fontFamily: 'Poppins'
                }
            }
        },
        legend: { show: false },
        tooltip: { enabled: true }
    };

    // Gráfico Sector Edad
    const sectorEdadEl = document.querySelector('#sectorEdadChart');
    if (sectorEdadEl) {
        const sectorEdadConfig = JSON.parse(JSON.stringify(ageChartBaseConfig));
        sectorEdadConfig.series = [{
            name: 'Matrículas',
            data: [{{ $sectorAdultos }}, {{ $sectorMenores }}]
        }];
        sectorEdadConfig.xaxis.tickAmount = {{ min(max($sectorAdultos, $sectorMenores, 1), 5) }};
        new ApexCharts(sectorEdadEl, sectorEdadConfig).render();
    }

    // Gráfico Templo Edad
    const temploEdadEl = document.querySelector('#temploEdadChart');
    if (temploEdadEl) {
        const temploEdadConfig = JSON.parse(JSON.stringify(ageChartBaseConfig));
        temploEdadConfig.series = [{
            name: 'Matrículas',
            data: [{{ $temploAdultos }}, {{ $temploMenores }}]
        }];
        temploEdadConfig.xaxis.tickAmount = {{ min(max($temploAdultos, $temploMenores, 1), 5) }};
        new ApexCharts(temploEdadEl, temploEdadConfig).render();
    }

    // Gráfico Donut Unión Libre vs Aptos
    const unionLibreEl = document.querySelector('#unionLibreChart');
    if (unionLibreEl) {
        const unionLibreConfig = {
            chart: {
                height: 220, // Altura ajustada
                type: 'donut',
                toolbar: { show: false }
            },
            labels: ['Aptos', 'Unión Libre'],
            series: [{{ $matriculasAptos }}, {{ $matriculasUnionLibre }}],
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
                                fontSize: '1rem',
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
        new ApexCharts(unionLibreEl, unionLibreConfig).render();
    }

    // Gráfico Donut Deserciones vs Efectivos
    const desercionesEl = document.querySelector('#desercionesChart');
    if (desercionesEl) {
        const desercionesConfig = {
            chart: {
                height: 220, // Altura ajustada
                type: 'donut',
                toolbar: { show: false }
            },
            labels: ['Efectivos', 'Deserciones'],
            series: [{{ $matriculasEfectivos }}, {{ $matriculasDeserciones }}],
            colors: ['#7367f0', '#ff9f43'],
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
                                fontSize: '1rem',
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
        new ApexCharts(desercionesEl, desercionesConfig).render();
    }
  });
</script>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-1 fw-semibold text-primary">Dashboard consolidación</h4>
</div>

  <div class="d-flex justify-content-end mb-5">
    <a href="{{ route('consolidacion.bloques') }}" class="btn btn-outline-primary">
      <i class="ti ti-settings me-1"></i> Gestionar bloques
    </a>
  </div>


  <!-- Barra de Filtros -->
<form id="formFiltros" action="{{ route('consolidacion.dashboard') }}" method="GET">
  <div class="row bg-white rounded-3 p-0 m-0 mb-4 shadow-sm border border-gray">
    <div class="row col-12 col-md-11 p-0 m-0">
      
      <!-- Rango Predefinido -->
      <div class="col-12 col-md-3 border-end border-gray p-0 d-flex">

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
      <div class="col-12 col-md-3 border-end border-gray p-0 d-flex">
          <input type="text" class="form-control  border-none flatpickr-range text-center" id="rango_fechas" name="rango_fechas" value="{{ $rangoFechas }}" placeholder="DD/MM/AAAA - DD/MM/AAAA">
      </div>
            
      @if($esVistaDetalle)
      <div class="col-12 col-md-6 border-end border-gray p-0 d-flex align-items-center">
        <input type="hidden" name="bloque_detalle_id" value="{{ $bloqueActual->id }}">
          <!-- Fila de Filtro de Sedes (Vista Detalle) -->  
        <select name="sedes_seleccionadas[]" class="selectpicker form-select  border-none w-100" multiple data-actions-box="true" data-select-all-text="Todos" data-deselect-all-text="Borrar" data-placeholder="Tipo de grupo" data-style="btn-default border-0" data-live-search="true" title="Seleccione sedes...">
          @foreach($sedesDisponibles as $sede)
              <option value="{{ $sede->id }}" {{ in_array($sede->id, $sedesSeleccionadas) ? 'selected' : '' }}>{{ $sede->nombre }}</option>
          @endforeach
        </select>
      </div>
      @else
      <div class="col-12 col-md-6 border-end border-gray p-0 d-flex align-items-center">
        <select name="bloques_seleccionados[]" class="selectpicker form-select  border-none w-100" multiple data-actions-box="true" data-select-all-text="Todos" data-deselect-all-text="Borrar" data-placeholder="Tipo de grupo" data-style="btn-default border-0" data-live-search="true" title="Seleccione bloques...">
          @foreach($bloquesDisponibles as $bloque)
              <option value="{{ $bloque->id }}" {{ in_array($bloque->id, $bloquesSeleccionados) ? 'selected' : '' }}>{{ $bloque->nombre }}</option>
          @endforeach
        </select>
      </div>
      @endif

    </div>
    
    <!-- Botón Filtrar -->
    <div class="col-12 col-md-1 p-0">
    <button type="submit" class="btn btn-xl btn-primary w-100 rounded-0 rounded-end h-100 px-auto fs-6">Filtrar</button>
    </div>
  </div>
</form>

<!-- Tabs de Navegación --> 
@php
  $activeTab = request('tab', 'indicador-1');
@endphp

<div class="card mb-10 p-1 border-1">
  <ul class="nav nav-pills justify-content-start flex-column flex-md-row gap-2" role="tablist">
    <li class="nav-item flex-fill">
      <button type="button" class="nav-link p-3 waves-effect waves-light {{ $activeTab == 'indicador-1' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tab-indicador-1" aria-controls="navs-tab-indicador-1" aria-selected="{{ $activeTab == 'indicador-1' ? 'true' : 'false' }}">
        Cosecha
      </button>
    </li>
    <li class="nav-item flex-fill">
      <button type="button" class="nav-link p-3 waves-effect waves-light {{ $activeTab == 'escuelas' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tab-indicador-2" aria-controls="navs-tab-indicador-2" aria-selected="{{ $activeTab == 'escuelas' ? 'true' : 'false' }}">
        Escuelas
      </button>
    </li>

    <li class="nav-item flex-fill">
      <button type="button" class="nav-link p-3 waves-effect waves-light {{ $activeTab == 'indicador-3' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tab-indicador-3" aria-controls="navs-tab-indicador-3" aria-selected="{{ $activeTab == 'indicador-3' ? 'true' : 'false' }}">
        Membresías
      </button>
    </li>
  </ul>
</div>


<div class="tab-content p-0 bg-transparent shadow-none border-0">
  <!-- Tab 1: Contenido Actual (Cosecha, Efectividad, Bloques) -->
  <div class="tab-pane fade {{ $activeTab == 'indicador-1' ? 'show active' : '' }}" id="navs-tab-indicador-1" role="tabpanel">

    
    <div class="row equal-height-row g-2">  

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-4 text-black fw-semibold text-uppercase">Total cosecha
          @if($esVistaDetalle)
              <span class="text-primary">{{ $bloqueActual->nombre }}</span>
          @else
          <div class="d-flex">
            <span class="text-black small fw-normal">
              <b>Información total de los bloques:</b>
              @foreach($bloquesDisponibles as $bloque)
                  {{ in_array($bloque->id, $bloquesSeleccionados) ? $bloque->nombre : '' }}
              @endforeach
            </span>
          </div>
          @endif
        </h4>

        

        @if($esVistaDetalle)
          <a href="{{ route('consolidacion.dashboard', ['rango_fechas' => $rangoFechas, 'tab' => $activeTab]) }}" class="btn btn-outline-secondary rounded-pill">
              <i class="ti ti-arrow-left me-1"></i> Volver a bloques
          </a>
          @endif

      </div>

      <div class="col col-12 equal-height-col col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title text-uppercase mb-0 fw-semibold">{{ $totalCosecha }}</h5>
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
              <h5 class="card-title text-uppercase mb-0 fw-semibold">{{ $cosechaEfectiva }}</h5>
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


    <div class="row equal-height-row g-2">
      <!-- Gráfico de Vinculación -->
      <div class="col equal-height-col col-12 col-xl-8 mb-4">
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
            <div id="vinculacionChart"></div>
          </div>
        </div>
      </div>

      <!-- Tabla de Vinculación -->
      <div class="col equal-height-col col-12 col-xl-4 mb-4">
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
                @foreach ($vinculacionesCosecha as $vinculacion)
                <div class=" col-12 d-flex flex-column">
                  <small class="text-black">{{ $vinculacion->nombre }} </small>
                  <small class="fw-semibold text-black ">{{ $vinculacion->usuarios_count }}</small>
                  <hr class="my-3 border-2">
                </div>
                @endforeach
              </div>

          </div>
        </div>
      </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between">
                <div>
                  <h6 class="card-title mb-0 fw-bold">Cosecha por semanas</h6>
                  <small class="text-black">
                    Histórico de cosecha por semana
                  </small>
                </div>
              </div>
              <div class="card-body">                  
                <div id="cosechaSemanalChart"></div>
              </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dataSemanal = @json($datosGraficaSemanal ?? []);
            
            if(dataSemanal && dataSemanal.length > 0) {
                 const options = {
                    series: [{
                        name: "Cosecha",
                        data: dataSemanal.map(item => item.y)
                    }, {
                        name: "Deserciones",
                        data: dataSemanal.map(item => item.y_desercion)
                    }],
                    chart: {
                        height: 350,
                        type: 'line',
                        zoom: { enabled: false },
                        toolbar: { show: false }
                    },
                    dataLabels: { enabled: true },
                    stroke: { curve: 'smooth', width: 3 }, 
                    colors: ['#7367f0', '#ea5455'],
                    grid: {
                        row: {
                            colors: ['#f3f3f3', 'transparent'],
                            opacity: 0.5
                        },
                    },
                    xaxis: {
                        categories: dataSemanal.map(item => item.x),
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
                                return val + " personas"
                            }
                        }
                    }
                };
                new ApexCharts(document.querySelector("#cosechaSemanalChart"), options).render();
            }
        });
    </script>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between">
                <div>
                  <h6 class="card-title mb-0 fw-bold">Tipo de vinculación semanal</h6>
                  <small class="text-black">
                    Histórico semanal según tipo de vinculación
                  </small>
                </div>
              </div>
              <div class="card-body">                  
                <div id="vinculacionSemanalChart"></div>
              </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dataVinc = @json($datosVinculacionSemanal ?? ['labels' => [], 'series' => []]);
            
            if(dataVinc.labels && dataVinc.labels.length > 0) {
                 const options = {
                    series: dataVinc.series,
                    chart: {
                        height: 380,
                        type: 'bar', // Changed from area to bar
                        stacked: true,
                        toolbar: { show: false },
                        zoom: { enabled: false }
                    },
                    colors: ['#7367f0', '#28c76f', '#ea5455', '#ff9f43', '#00cfe8', '#82868b'],
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                        },
                    },
                    xaxis: {
                        categories: dataVinc.labels,
                        labels: {
                            style: { fontSize: '10px', fontFamily: 'Poppins' }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) { return Math.floor(val); },
                            style: { fontFamily: 'Poppins' }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left',
                        fontFamily: 'Poppins'
                    },
                    tooltip: {
                         y: {
                            formatter: function (val) { return val + " personas" }
                        }
                    }
                };
                new ApexCharts(document.querySelector("#vinculacionSemanalChart"), options).render();
            }
        });
    </script>

    <hr class="my-5">

    <!-- Sección de Detalles por Bloque/Sede -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="text-black fw-semibold text-uppercase mb-0">
          @if($esVistaDetalle)
              Detalle por Sedes: <span class="text-primary">{{ $bloqueActual->nombre }}</span>
          @else
              Detalle por bloques
          @endif
      </h5>   
    </div>
 
    <div class="accordion" id="accordionDesglose">
        @foreach($datosDesglose as $dato)
            <div class="accordion-item card mb-3 border active">
                <h6 class="accordion-header d-flex flex-column justify-content-between align-items-center pe-3" id="heading{{ $dato->id }}">
                    <button type="button" class="accordion-button collapsed flex-grow-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#collapse{{ $dato->id }}" aria-expanded="false" aria-controls="collapse{{ $dato->id }}">
                        <div class="d-flex flex-column text-start">
                            <span class="fs-5 fw-semibold text-uppercase">{{ $dato->nombre }}</span>
                            <small class="text-black">Total cosecha: {{ $dato->totalCosecha }}</small>
                        </div>
                    </button>
                </h6>  

                <div id="collapse{{ $dato->id }}" class="accordion-collapse collapse border-top border-2 pt-4" aria-labelledby="heading{{ $dato->id }}">
                    <div class="accordion-body">
                        <div class="row g-4">
                            <!-- Cards de Métricas -->
                            <div class="col-12 col-md-4">
                                <div class="card mb-3">
                                    <div class="card-body py-3 border-bottom">
                                        <h5 class="card-title mb-0 fw-semibold">{{ $dato->totalCosecha }}</h5>
                                        <small class="text-black">Total cosecha</small> 
                                    </div>
                                </div>
                                <div class="card mb-3">
                                    <div class="card-body py-3 border-bottom">
                                        <h5 class="card-title mb-0 fw-semibold">{{ $dato->cosechaEfectiva }}</h5>
                                        <small class="text-black">Cosecha efectiva</small>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body py-3  border-bottom">
                                        <h5 class="card-title mb-0 fw-semibold">{{ $dato->porcentajeEfectividad }}%</h5>
                                        <small class="text-black">Efectividad</small>
                                        <div class="progress" style="height: 8px;">
                                          <div class="progress-bar" role="progressbar" style="width: {{ $dato->porcentajeEfectividad }}%" aria-valuenow="{{ $dato->porcentajeEfectividad }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráfico -->
                            <div class="col-12 col-md-4">
                                <h6 class="text-center fw-semibold mb-3">Cosecha por semanas</h6>
                                <div id="chart-desglose-{{ $dato->id }}" class="chart-bloque" 
                                    data-series='@json($dato->vinculacionesCosecha->pluck("usuarios_count"))' 
                                    data-labels='@json($dato->vinculacionesCosecha->pluck("nombre"))'
                                    data-total='{{ $dato->totalCosecha }}'>
                                </div>
                            </div>

                            <!-- Lista -->
                            <div class="col-12 col-md-4">
                                <h6 class="fw-semibold mb-3">Tipo de vinculación semanal</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach($dato->vinculacionesCosecha as $vinc)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            {{ $vinc->nombre }}
                                            <span class="text-black fw-semibold">{{ $vinc->usuarios_count }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Gráficas de Desglose Semanal -->
                        <div class="row mt-4">
                            <!-- Gráfica Semanal (Líneas) -->
                            <div class="col-12 col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-header py-2">
                                        <h6 class="card-title mb-0 fw-bold fs-6">Cosecha por semanas</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div id="cosechaSemanalChart{{ $dato->id }}"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Gráfica Vinculación (Area) -->
                            <div class="col-12 col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-header py-2">
                                        <h6 class="card-title mb-0 fw-bold fs-6">Tipo de vinculación semanal</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div id="vinculacionSemanalChart{{ $dato->id }}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // 1. Gráfica Semanal Item
                                const dataSemanal{{ $dato->id }} = @json($dato->graficaSemanal ?? []);
                                if(dataSemanal{{ $dato->id }} && dataSemanal{{ $dato->id }}.length > 0) {
                                    const optionsSemanal{{ $dato->id }} = {
                                        series: [{
                                            name: "Cosecha",
                                            data: dataSemanal{{ $dato->id }}.map(item => item.y)
                                        }, {
                                            name: "Deserciones",
                                            data: dataSemanal{{ $dato->id }}.map(item => item.y_desercion)
                                        }],
                                        chart: {
                                            height: 250,
                                            type: 'line',
                                            zoom: { enabled: false },
                                            toolbar: { show: false },
                                            fontFamily: 'Poppins'
                                        },
                                        dataLabels: { enabled: false },
                                        stroke: { curve: 'smooth', width: 2 }, 
                                        colors: ['#7367f0', '#ea5455'],
                                        xaxis: {
                                            categories: dataSemanal{{ $dato->id }}.map(item => item.x),
                                            labels: { style: { fontSize: '9px' } }
                                        },
                                        yaxis: { labels: { formatter: val => Math.floor(val) } },
                                        legend: { show: false }
                                    };
                                    new ApexCharts(document.querySelector("#cosechaSemanalChart{{ $dato->id }}"), optionsSemanal{{ $dato->id }}).render();
                                }

                                // 2. Gráfica Vinculación Item
                                const dataVinc{{ $dato->id }} = @json($dato->graficaVinculacion ?? ['labels' => [], 'series' => []]);
                                if(dataVinc{{ $dato->id }}.labels && dataVinc{{ $dato->id }}.labels.length > 0) {
                                     const optionsVinc{{ $dato->id }} = {
                                        series: dataVinc{{ $dato->id }}.series,
                                        chart: {
                                            height: 250,
                                            type: 'bar', // Changed from area to bar
                                            stacked: true,
                                            toolbar: { show: false },
                                            zoom: { enabled: false },
                                            fontFamily: 'Poppins'
                                        },
                                        colors: ['#7367f0', '#28c76f', '#ea5455', '#ff9f43', '#00cfe8', '#82868b'],
                                        dataLabels: { enabled: false },
                                        stroke: { show: true, width: 1, colors: ['transparent'] },
                                        plotOptions: {
                                            bar: {
                                                horizontal: false,
                                                columnWidth: '70%',
                                            },
                                        },
                                        xaxis: {
                                            categories: dataVinc{{ $dato->id }}.labels,
                                            labels: { style: { fontSize: '9px' } }
                                        },
                                        yaxis: {
                                            labels: { formatter: val => Math.floor(val) }
                                        },
                                        legend: {  fontSize: '10px' }
                                    };
                                    new ApexCharts(document.querySelector("#vinculacionSemanalChart{{ $dato->id }}"), optionsVinc{{ $dato->id }}).render();
                                }
                            });
                        </script>

                        @if(!$esVistaDetalle)
                        <div class="d-flex justify-content-end py-2 ">
                              <!-- Botón para ir al Drill-Down del Bloque -->
                              <a href="{{ route('consolidacion.dashboard', array_merge(request()->all(), ['bloque_detalle_id' => $dato->id, 'tab' => 'indicador-1'])) }}" class="btn btn-sm rounded-pill btn-outline-secondary ms-2 z-index-2 position-relative" style="z-index: 5;">
                                  Ver detalle sedes
                              </a>
                        </div>
                        @endif
                    </div>
                </div>

              
            </div>
        @endforeach
    </div>


  </div> <!-- Fin Tab 1 -->

  <!-- Tab 2: Escuelas -->
  <div class="tab-pane fade {{ $activeTab == 'escuelas' ? 'show active' : '' }}" id="navs-tab-indicador-2" role="tabpanel">    
      
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-4 text-black fw-semibold text-uppercase">Estadísticas de Escuelas
          @if($esVistaDetalle)
            <span class="text-primary">{{ $bloqueActual->nombre }}</span>
          @endif
      </h4>

      @if($esVistaDetalle)
          <a href="{{ route('consolidacion.dashboard', ['rango_fechas' => $rangoFechas, 'tab' => $activeTab]) }}" class="btn btn-outline-secondary rounded-pill">
              <i class="ti ti-arrow-left me-1"></i> Volver a bloques
          </a>
      @endif
    </div>

    <div class="row equal-height-row g-2">
        <!-- Gráfico Distribución Sector vs Templo -->
        <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
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

        <!-- Matrículas Sector: Adultos vs Menores -->
        <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
              <div>
                <h6 class="card-title mb-0 fw-bold">Matrículas sector</h6>
                <small class="text-black">
                  Adultos vs Warriors
                </small>
              </div>
            </div>
            <div class="card-body">                  
              <div id="sectorEdadChart"></div>
            </div>
          </div>
        </div>

        <!-- Matrículas Templo: Adultos vs Menores -->
        <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
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

        <!-- Matrículas Unión Libre vs Aptos -->
        <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
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

        <!-- Matrículas Deserciones vs Efectivos -->
        <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
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

        <!-- Porcentaje Efectividad Matrículas -->

         <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
          <div class="card">
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

    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="card h-100">
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dataMatriculas = @json($datosMatriculasSemanal ?? []);
            
            if(dataMatriculas && dataMatriculas.length > 0) {
                 const optionsMatriculas = {
                    series: [{
                        name: "Matrículas",
                        data: dataMatriculas.map(item => item.y)
                    }],
                    chart: {
                        height: 350,
                        type: 'line',
                        zoom: { enabled: false },
                        toolbar: { show: false }
                    },
                    dataLabels: { enabled: true },
                    stroke: { curve: 'smooth', width: 3 }, 
                    colors: ['#28c76f'], // Green for success/enrollment
                    grid: {
                        row: {
                            colors: ['#f3f3f3', 'transparent'],
                            opacity: 0.5
                        },
                    },
                    xaxis: {
                        categories: dataMatriculas.map(item => item.x),
                         labels: {
                            style: { fontSize: '10px', fontFamily: 'Poppins' }
                        }
                    },
                    yaxis: {
                         labels: {
                            formatter: function (val) { return Math.floor(val); },
                             style: { fontFamily: 'Poppins' }
                        }
                    },
                    tooltip: {
                         y: {
                            formatter: function (val) { return val + " matrículas" }
                        }
                    }
                };
                new ApexCharts(document.querySelector("#matriculasSemanalChart"), optionsMatriculas).render();
            }
        });
    </script>
    
    <hr class="my-5">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
      <h5 class="text-black fw-semibold text-uppercase mb-0">
          @if($esVistaDetalle)
              Detalle por Sedes: <span class="text-primary">{{ $bloqueActual->nombre }}</span>
          @else
              Detalle por bloques
          @endif
      </h5>   
    </div>

    <div class="accordion" id="accordionEscuelas">
        @foreach($datosDesglose as $dato)
            <div class="accordion-item card mb-3 border active">
                <h6 class="accordion-header d-flex flex-column justify-content-between align-items-center pe-3" id="headingEscuela{{ $dato->id }}">
                    <button type="button" class="accordion-button collapsed flex-grow-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseEscuela{{ $dato->id }}" aria-expanded="false" aria-controls="collapseEscuela{{ $dato->id }}">
                        <div class="d-flex flex-column text-start">
                            <span class="fs-5 fw-semibold text-uppercase">{{ $dato->nombre }}</span>
                            <small class="text-black">Matrículas Totales: {{ $dato->totalMatriculas }}</small>
                        </div>
                    </button>
                </h6>  

                <div id="collapseEscuela{{ $dato->id }}" class="accordion-collapse collapse border-top border-2 pt-4" aria-labelledby="headingEscuela{{ $dato->id }}">
                    <div class="accordion-body">
                        
                        <div class="row equal-height-row g-2">
                            <!-- Gráfico Distribución Sector vs Templo -->
                            <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
                              <div class="card h-100">
                                <div class="card-header d-flex justify-content-between">
                                  <div>
                                    <h6 class="card-title mb-0 fw-bold">Matrículas</h6>
                                    <small class="text-black">Templo vs sector</small>
                                  </div>
                                </div>
                                <div class="card-body">                  
                                  <div id="matriculasTipo{{ $dato->id }}"></div>
                                </div>
                              </div>
                            </div>

                            <!-- Matrículas Sector: Adultos vs Menores -->
                            <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
                              <div class="card h-100">
                                <div class="card-header d-flex justify-content-between">
                                  <div>
                                    <h6 class="card-title mb-0 fw-bold">Matrículas sector</h6>
                                    <small class="text-black">Adultos vs Warriors</small>
                                  </div>
                                </div>
                                <div class="card-body">                  
                                  <div id="sectorEdadChart{{ $dato->id }}"></div>
                                </div>
                              </div>
                            </div>

                            <!-- Matrículas Templo: Adultos vs Menores -->
                            <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
                              <div class="card h-100">
                                <div class="card-header d-flex justify-content-between">
                                  <div>
                                    <h6 class="card-title mb-0 fw-bold">Matrículas templo</h6>
                                    <small class="text-black">Adultos vs Warriors</small>
                                  </div>
                                </div>
                                <div class="card-body">                  
                                  <div id="temploEdadChart{{ $dato->id }}"></div>
                                </div>
                              </div>
                            </div>

                            <!-- Matrículas Unión Libre vs Aptos -->
                            <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
                              <div class="card h-100">
                                <div class="card-header d-flex justify-content-between">
                                  <div>
                                    <h6 class="card-title mb-0 fw-bold">Aptos</h6>
                                    <small class="text-black">Aptos vs Unión Libre</small>
                                  </div>
                                </div>
                                <div class="card-body">                  
                                  <div id="unionLibreChart{{ $dato->id }}"></div>
                                </div>
                              </div>
                            </div>

                            <!-- Matrículas Deserciones vs Efectivos -->
                            <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
                              <div class="card h-100">
                                <div class="card-header d-flex justify-content-between">
                                  <div>
                                    <h6 class="card-title mb-0 fw-bold">Efectividad Matrículas</h6>
                                    <small class="text-black">Efectivos vs Deserciones</small>
                                  </div>
                                </div>
                                <div class="card-body">                  
                                  <div id="desercionesChart{{ $dato->id }}"></div>
                                </div>
                              </div>
                            </div>

                            <!-- Porcentaje Efectividad Matrículas -->
                            <div class="col equal-height-col col-12 col-lg-4 col-sm-6 mb-4">
                              <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center pb-0">
                                  <small class="text-black">Efectividad de matrículas</small>
                                  <h4 class="text-black fw-semibold mb-0">{{ $dato->porcentajeEfectividadMatriculas }}%</h4>
                                </div>
                                <div class="card-body">              
                                  <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $dato->porcentajeEfectividadMatriculas }}%" aria-valuenow="{{ $dato->porcentajeEfectividadMatriculas }}" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        </div>

                         <!-- Gráfica Semanal Matrículas -->
                        <div class="row mt-4 mb-4">
                            <div class="col-12">
                                <div class="card h-100 border">
                                    <div class="card-header d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title mb-0 fw-bold">Matrículas por semana</h6>
                                            <small class="text-black">Histórico de matrículas por semana</small>
                                        </div>
                                    </div>
                                    <div class="card-body">                  
                                        <div id="matriculasSemanalChart{{ $dato->id }}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // Gráfica Matrículas Semanal Item
                                const dataMatriculas{{ $dato->id }} = @json($dato->graficaMatriculasSemanal ?? []);
                                
                                if(dataMatriculas{{ $dato->id }} && dataMatriculas{{ $dato->id }}.length > 0) {
                                     const optionsMatriculas{{ $dato->id }} = {
                                        series: [{
                                            name: "Matrículas",
                                            data: dataMatriculas{{ $dato->id }}.map(item => item.y)
                                        }],
                                        chart: {
                                            height: 250,
                                            type: 'line',
                                            zoom: { enabled: false },
                                            toolbar: { show: false },
                                            fontFamily: 'Poppins'
                                        },
                                        dataLabels: { enabled: true },
                                        stroke: { curve: 'smooth', width: 3 }, 
                                        colors: ['#28c76f'],
                                        grid: {
                                            row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 },
                                        },
                                        xaxis: {
                                            categories: dataMatriculas{{ $dato->id }}.map(item => item.x),
                                            labels: {
                                                style: { fontSize: '9px', fontFamily: 'Poppins' }
                                            }
                                        },
                                        yaxis: {
                                            labels: { formatter: val => Math.floor(val) }
                                        },
                                        tooltip: {
                                            y: { formatter: val => val + " matrículas" }
                                        }
                                    };
                                    new ApexCharts(document.querySelector("#matriculasSemanalChart{{ $dato->id }}"), optionsMatriculas{{ $dato->id }}).render();
                                }
                            });
                        </script>
                        
                        @if(!$esVistaDetalle)
                        <div class="d-flex justify-content-end py-2 ">
                              <a href="{{ route('consolidacion.dashboard', array_merge(request()->all(), ['bloque_detalle_id' => $dato->id, 'tab' => 'escuelas'])) }}" class="btn btn-sm rounded-pill btn-outline-secondary ms-2 z-index-2 position-relative" style="z-index: 5;">
                                  Ver detalle sedes
                              </a>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                     // Configs Base (reusing headers from global scope if variables accessible, but safer to re-define or clone)
                     const commonOptions = {
                        chart: { height: 220, type: 'donut', toolbar: { show: false } },
                        dataLabels: { enabled: true, formatter: function (val, opts) { return opts.w.config.series[opts.seriesIndex] } },
                        plotOptions: { pie: { donut: { size: '70%', labels: { show: true, name: { fontSize: '1rem', fontFamily: 'Poppins' }, value: { fontSize: '1rem', fontFamily: 'Poppins', formatter: function (val) { return parseInt(val); } }, total: { show: true, fontSize: '0.8rem', label: 'Total', formatter: function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); } } } } } },
                        legend: { show: true, position: 'bottom', markers: { offsetX: -3 }, itemMargin: { vertical: 3, horizontal: 10 } },
                        tooltip: { enabled: true }
                     };
                     
                     // 1. Sector vs Templo (Bar)
                     const matriculasTipoEl{{ $dato->id }} = document.querySelector('#matriculasTipo{{ $dato->id }}');
                     if (matriculasTipoEl{{ $dato->id }}) {
                        const matriculasTipoConfig{{ $dato->id }} = {
                            chart: { height: 150, type: 'bar', toolbar: { show: false } },
                            plotOptions: { bar: { horizontal: true, barHeight: '80%', distributed: true, borderRadius: 4, borderRadiusApplication: 'end' } },
                            grid: { show: false, padding: { top: -20, bottom: -12, left: 10 } },
                            colors: ['#7367f0', '#ff9f43'],
                            dataLabels: { enabled: true, style: { colors: ['#fff'] }, offsetX: 0, formatter: function(val, opt) { return val } },
                            series: [{ data: [{{ $dato->matriculasSector }}, {{ $dato->matriculasTemplo }}] }],
                            legend: { show: false },
                            xaxis: { categories: ['Sector', 'Templo'], tickAmount: {{ min(max($dato->matriculasSector, $dato->matriculasTemplo, 1), 5) }}, axisBorder: { show: false }, axisTicks: { show: false }, labels: { formatter: function(val) { return Math.floor(val) } } },
                            yaxis: { labels: { style: { colors: ['#000'], fontSize: '11px', fontWeight: 400, fontFamily: 'Poppins' } } },
                            tooltip: { theme: 'light', x: { show: false }, y: { title: { formatter: function() { return '' } } } }
                        };
                        new ApexCharts(matriculasTipoEl{{ $dato->id }}, matriculasTipoConfig{{ $dato->id }}).render();
                     }
                     
                     // 2. Edad Sector (Bar)
                     const sectorEdadChartEl{{ $dato->id }} = document.querySelector('#sectorEdadChart{{ $dato->id }}');
                     if (sectorEdadChartEl{{ $dato->id }}) {
                         new ApexCharts(sectorEdadChartEl{{ $dato->id }}, {
                            ...{
                                chart: { height: 150, type: 'bar', toolbar: { show: false } },
                                plotOptions: { bar: { horizontal: true, barHeight: '80%', distributed: true, borderRadius: 4, borderRadiusApplication: 'end' } },
                                grid: { show: false, padding: { top: -20, bottom: -12, left: 10 } },
                                colors: ['#28c76f', '#00cfe8'],
                                dataLabels: { enabled: true, style: { colors: ['#fff'] }, offsetX: 0 },
                                series: [{ data: [{{ $dato->sectorAdultos }}, {{ $dato->sectorMenores }}] }],
                                xaxis: { categories: ['Adultos', 'Warriors'], tickAmount: {{ min(max($dato->sectorAdultos, $dato->sectorMenores, 1), 5) }}, axisBorder: { show: false }, axisTicks: { show: false }, labels: { formatter: function(val) { return Math.floor(val) } } },
                                legend: { show: false },
                                yaxis: { labels: { style: { colors: ['#000'], fontSize: '11px', fontWeight: 400, fontFamily: 'Poppins' } } },
                                tooltip: { theme: 'light', x: { show: false }, y: { title: { formatter: function() { return '' } } } }
                            }
                         }).render();
                     }

                     // 3. Edad Templo (Bar)
                     const temploEdadChartEl{{ $dato->id }} = document.querySelector('#temploEdadChart{{ $dato->id }}');
                     if (temploEdadChartEl{{ $dato->id }}) {
                         new ApexCharts(temploEdadChartEl{{ $dato->id }}, {
                            ...{
                                chart: { height: 150, type: 'bar', toolbar: { show: false } },
                                plotOptions: { bar: { horizontal: true, barHeight: '80%', distributed: true, borderRadius: 4, borderRadiusApplication: 'end' } },
                                grid: { show: false, padding: { top: -20, bottom: -12, left: 10 } },
                                colors: ['#28c76f', '#00cfe8'],
                                dataLabels: { enabled: true, style: { colors: ['#fff'] }, offsetX: 0 },
                                series: [{ data: [{{ $dato->temploAdultos }}, {{ $dato->temploMenores }}] }],
                                legend: { show: false },
                                xaxis: { categories: ['Adultos', 'Warriors'], tickAmount: {{ min(max($dato->temploAdultos, $dato->temploMenores, 1), 5) }}, axisBorder: { show: false }, axisTicks: { show: false }, labels: { formatter: function(val) { return Math.floor(val) } } },
                                yaxis: { labels: { style: { colors: ['#000'], fontSize: '11px', fontWeight: 400, fontFamily: 'Poppins' } } },
                                tooltip: { theme: 'light', x: { show: false }, y: { title: { formatter: function() { return '' } } } }
                            }
                         }).render();
                     }

                     // 4. Union Libre Donut
                     const unionLibreEl{{ $dato->id }} = document.querySelector('#unionLibreChart{{ $dato->id }}');
                     if (unionLibreEl{{ $dato->id }}) {
                        new ApexCharts(unionLibreEl{{ $dato->id }}, {
                            ...commonOptions,
                            labels: ['Aptos', 'Unión Libre'],
                            series: [{{ $dato->matriculasAptos }}, {{ $dato->matriculasUnionLibre }}],
                            colors: ['#28c76f', '#ea5455']
                        }).render();
                     }

                     // 5. Deserciones Donut
                     const desercionesEl{{ $dato->id }} = document.querySelector('#desercionesChart{{ $dato->id }}');
                     if (desercionesEl{{ $dato->id }}) {
                        new ApexCharts(desercionesEl{{ $dato->id }}, {
                            ...commonOptions,
                            labels: ['Efectivos', 'Deserciones'],
                            series: [{{ $dato->matriculasEfectivos }}, {{ $dato->matriculasDeserciones }}],
                            colors: ['#7367f0', '#ff9f43']
                        }).render();
                     }
                });
            </script>
        @endforeach
    </div>
  </div>

  <!-- Tab 3: Futuro Contenido -->
  <div class="tab-pane fade" id="navs-tab-indicador-3" role="tabpanel">
     <div class="alert alert-warning">
        <h6 class="alert-heading fw-bold mb-1">En construcción</h6>
        <span>Próximamente más indicadores aquí.</span>
     </div>
  </div>
</div> <!-- Fin Tab Content -->

@endsection
