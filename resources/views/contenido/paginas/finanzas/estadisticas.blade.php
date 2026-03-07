@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Estadísticas Financieras') {{-- Título más específico --}}

@section('page-style')
{{-- Asegúrate que chartjs.js se carga ANTES de tu script de inicialización --}}
{{-- NOTA: resources/assets/js/charts-chartjs.js podría contener inicializaciones genéricas.
     Si tienes problemas, verifica que no interfiera con tu inicialización específica abajo. --}}
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/chartjs/chartjs.js', {{-- JS Principal de Chart.js --}}
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
// 'resources/assets/js/charts-chartjs.js' // Considera quitarlo si causa conflictos
])
@endsection

@section('page-script')
{{-- Script para Flatpickr y Select2 (como lo tenías) --}}
<script type="module">
  $(document).ready(function() {
    $(".fecha-picker").flatpickr({
      dateFormat: "Y-m-d"
    });

    // Inicialización de Select2 (si lo necesitas en esta página)
    $('.select2').select2({
      width: '100px', // Quizás '100%' sea mejor?
      allowClear: true,
      placeholder: 'Ninguno'
    });
  });
</script>

{{-- Script para inicializar los gráficos con los datos del controlador --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const activeTabKeyGlobal = 'activeEstadisticasTabGlobal';
    const allChartDataByCurrency = @json($datosGraficosPorMoneda ?? []); // Añadido ?? [] por si no hay datos

    // --- Lógica para mantener la pestaña activa ---
    const tabButtons = document.querySelectorAll('.nav-pills .nav-link[data-bs-toggle="tab"]');
    const storedTabId = sessionStorage.getItem(activeTabKeyGlobal);

    tabButtons.forEach(tabButton => {
      tabButton.addEventListener('shown.bs.tab', function(event) {
        const currentTabId = event.target.getAttribute('data-bs-target') || event.target.getAttribute('href');
        if (currentTabId) {
          sessionStorage.setItem(activeTabKeyGlobal, currentTabId);
        }
      });

      if (storedTabId && (tabButton.getAttribute('data-bs-target') === storedTabId || tabButton.getAttribute('href') === storedTabId)) {
        const tabInstance = new bootstrap.Tab(tabButton);
        try {
          tabInstance.show();
        } catch (e) {
          console.warn("Error mostrando tab guardado:", e, tabButton);
        }
      }
    });

    // --- Iterar y crear gráficos para cada moneda ---
    for (const currencyCode in allChartDataByCurrency) {
      if (allChartDataByCurrency.hasOwnProperty(currencyCode)) {
        const currencyData = allChartDataByCurrency[currencyCode];

        const nombreMonedaParaDisplay = currencyData.infoMoneda && currencyData.infoMoneda.nombre ? currencyData.infoMoneda.nombre : currencyCode;
        // Asegúrate que tu modelo Moneda tiene 'codigo' (ej. 'COP', 'USD')
        const codigoMonedaReal = currencyData.infoMoneda && currencyData.infoMoneda.codigo ? currencyData.infoMoneda.codigo : 'COP'; // Default

        // --- Gráfico General para esta moneda ---
        const generalCtx = document.getElementById(`general-${currencyCode}`);
        if (generalCtx && currencyData.general && currencyData.general.labels) {
          new Chart(generalCtx.getContext('2d'), {
            type: 'bar',
            data: {
              labels: currencyData.general.labels,
              datasets: [{
                label: `Resumen ${nombreMonedaParaDisplay}`,
                data: currencyData.general.data,
                backgroundColor: currencyData.general.colors,
                borderColor: currencyData.general.colors.map(color => typeof color === 'string' ? color.replace(')', ', 0.8)').replace('rgb', 'rgba') : 'rgba(0,0,0,0.1)'),
                borderWidth: 1
              }]
            },
            options: { // ***** OPCIONES PARA GRÁFICO GENERAL *****
              responsive: true,
              maintainAspectRatio: false,
              indexAxis: 'y',
              scales: {
                x: {
                  beginAtZero: true
                }
              },
              plugins: {
                legend: {
                  display: true
                },
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      let label = context.dataset.label || '';
                      if (label) {
                        label += ': ';
                      }
                      const value = context.parsed.x; // indexAxis: 'y'
                      if (value !== null) {
                        label += new Intl.NumberFormat('es-CO', {
                          style: 'currency',
                          currency: codigoMonedaReal
                        }).format(value);
                      }
                      return label;
                    }
                  }
                },
                datalabels: { // <--- CONFIGURACIÓN DE DATALABELS
                  display: true,
                  anchor: 'end',
                  align: 'start',
                  formatter: (value, context) => {
                    const label = context.chart.data.labels[context.dataIndex];
                    const formattedValue = new Intl.NumberFormat('es-CO', {
                      style: 'currency',
                      currency: codigoMonedaReal,
                      /* ... */
                    }).format(value);
                    return `<span class="math-inline">\{label\}\\n</span>{formattedValue}`;
                  },
                  color: '#333',
                  font: {
                    weight: 'bold'
                  }
                }
              }
            } // ***** FIN OPCIONES GRÁFICO GENERAL *****
          });
        } else {
          console.error(`Canvas "general-${currencyCode}" o sus datos no encontrados.`);
        }

        // --- Gráfico Ingresos para esta moneda ---
        const ingresosCtx = document.getElementById(`ingresos-${currencyCode}`);
        if (ingresosCtx && currencyData.ingresos && currencyData.ingresos.labels) {
          new Chart(ingresosCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
              labels: currencyData.ingresos.labels,
              datasets: [{
                label: `Ingresos ${nombreMonedaParaDisplay}`,
                data: currencyData.ingresos.data,
                backgroundColor: currencyData.ingresos.colors,
                borderWidth: 0,
                hoverOffset: 4
              }]
            },
            options: { // ***** OPCIONES PARA GRÁFICO INGRESOS *****
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: true
                }, // Ocultar si datalabels son suficientes
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      let label = context.label || '';
                      if (label) {
                        label += ': ';
                      }
                      const value = context.parsed;
                      if (value !== null) {
                        label += new Intl.NumberFormat('es-CO', {
                          style: 'currency',
                          currency: codigoMonedaReal
                        }).format(value);
                      }
                      return label;
                    }
                  }
                },
                datalabels: { // <--- CONFIGURACIÓN DE DATALABELS
                  display: true,
                  formatter: (value, context) => {
                    const label = context.chart.data.labels[context.dataIndex];
                    const formattedValue = new Intl.NumberFormat('es-CO', {
                      style: 'currency',
                      currency: codigoMonedaReal,
                      minimumFractionDigits: 0,
                      maximumFractionDigits: 0
                    }).format(value);
                    return `${label}\n${formattedValue}`; // Nombre de categoría + Valor
                  },
                  color: '#222', // Color del texto
                  backgroundColor: 'rgba(255, 255, 255, 0.75)',
                  borderColor: 'rgba(150, 150, 150, 0.75)',
                  borderWidth: 1,
                  borderRadius: 4,
                  padding: {
                    top: 4,
                    bottom: 3,
                    left: 6,
                    right: 6
                  },
                  textAlign: 'center',
                  font: {
                    weight: '600',
                    size: 11
                  },
                  // Posicionamiento para líneas conectoras ("flechas")
                  anchor: 'end',
                  align: 'end', // Intenta con 'start', 'center', 'end' para ver cuál te gusta más
                  offset: 10, // Distancia para la línea conectora
                  clamp: true // Evita que las etiquetas se salgan del gráfico
                }
              }
            } // ***** FIN OPCIONES GRÁFICO INGRESOS *****
          });
        } else {
          console.error(`Canvas "ingresos-${currencyCode}" o sus datos no encontrados.`);
        }

        // --- Gráfico Egresos para esta moneda ---
        const egresosCtx = document.getElementById(`egresos-${currencyCode}`);
        if (egresosCtx && currencyData.egresos && currencyData.egresos.labels) {
          new Chart(egresosCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
              labels: currencyData.egresos.labels,
              datasets: [{
                label: `Egresos ${nombreMonedaParaDisplay}`,
                data: currencyData.egresos.data,
                backgroundColor: currencyData.egresos.colors,
                borderWidth: 0,
                hoverOffset: 4
              }]
            },
            options: { // ***** OPCIONES PARA GRÁFICO EGRESOS *****
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: true
                }, // Ocultar si datalabels son suficientes
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      let label = context.label || '';
                      if (label) {
                        label += ': ';
                      }
                      const value = context.parsed;
                      if (value !== null) {
                        label += new Intl.NumberFormat('es-CO', {
                          style: 'currency',
                          currency: codigoMonedaReal
                        }).format(value);
                      }
                      return label;
                    }
                  }
                },
                datalabels: { // <--- CONFIGURACIÓN DE DATALABELS (igual que ingresos)
                  display: true,
                  formatter: (value, context) => {
                    const label = context.chart.data.labels[context.dataIndex];
                    const formattedValue = new Intl.NumberFormat('es-CO', {
                      style: 'currency',
                      currency: codigoMonedaReal,
                      minimumFractionDigits: 0,
                      maximumFractionDigits: 0
                    }).format(value);
                    return `${label}\n${formattedValue}`;
                  },
                  color: '#222',
                  backgroundColor: 'rgba(255, 255, 255, 0.75)',
                  borderColor: 'rgba(150, 150, 150, 0.75)',
                  borderWidth: 1,
                  borderRadius: 4,
                  padding: {
                    top: 4,
                    bottom: 3,
                    left: 6,
                    right: 6
                  },
                  textAlign: 'center',
                  font: {
                    weight: '600',
                    size: 11
                  },
                  anchor: 'end',
                  align: 'end',
                  offset: 10,
                  clamp: true
                }
              }
            } // ***** FIN OPCIONES GRÁFICO EGRESOS *****
          });
        } else {
          console.error(`Canvas "egresos-${currencyCode}" o sus datos no encontrados.`);
        }
      }
    }
  });
</script>

{{-- Estilos para el canvas (como lo tenías) --}}
<style>
  .chartjs {
    height: 350px !important;
  }

  .tab-content,
  .tab-pane {
    width: 100%;
  }

  .currency-section {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #eee;
  }
</style>
@endsection

@section('content')

<h4 class="mb-4">Estadísticas Financieras</h4> {{-- Ajustado margen inferior --}}

{{-- Incluir mensajes de estado si los usas --}}
@include('layouts.status-msn')

{{-- FORMULARIO DE FILTRO DE FECHAS --}}
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('finanzas.estadisticas') }}"> {{-- ¡¡USA EL NOMBRE DE TU RUTA AQUÍ!! --}}
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label for="fechaInicio" class="form-label">Fecha Inicio:</label>
          <input type="text" class="form-control fecha-picker" id="fechaInicio" name="fechaInicio" placeholder="YYYY-MM-DD" value="{{ $fechaInicio ?? '' }}">
        </div>
        <div class="col-md-4">
          <label for="fechaFinal" class="form-label">Fecha Final:</label>
          <input type="text" class="form-control fecha-picker" id="fechaFinal" name="fechaFinal" placeholder="YYYY-MM-DD" value="{{ $fechaFinal ?? '' }}">
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-primary me-2">Filtrar</button>
          <a href="{{ route('finanzas.estadisticas') }}" class="btn btn-secondary">Limpiar</a> {{-- ¡¡USA EL NOMBRE DE TU RUTA AQUÍ!! --}}
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Iterar sobre cada moneda y mostrar su conjunto de gráficos y pestañas --}}
@if (!empty($datosGraficosPorMoneda))
@foreach ($datosGraficosPorMoneda as $currencyCode => $currencyChartData)
<div class="currency-section">
  <h5 class="mb-3">Estadísticas para {{ $currencyChartData['infoMoneda']->nombre ?? $currencyCode }}</h5>

  <div class="col-xl-12">
    <div class="nav-align-top mb-4">
      <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item">
          <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
            data-bs-target="#general-tab-{{ $currencyCode }}" aria-controls="general-tab-{{ $currencyCode }}"
            aria-selected="true">General</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
            data-bs-target="#ingresos-tab-{{ $currencyCode }}" aria-controls="ingresos-tab-{{ $currencyCode }}"
            aria-selected="false">Ingresos</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
            data-bs-target="#egresos-tab-{{ $currencyCode }}" aria-controls="egresos-tab-{{ $currencyCode }}"
            aria-selected="false">Egresos</button>
        </li>
      </ul>
      <div class="tab-content">
        {{-- Tab General para esta moneda --}}
        <div class="tab-pane fade show active" id="general-tab-{{ $currencyCode }}" role="tabpanel">
          <div class="card">
            <h6 class="card-header">Resumen General ({{ $currencyChartData['infoMoneda']->nombre ?? $currencyCode }})</h6>
            <div class="card-body">
              <canvas id="general-{{ $currencyCode }}" class="chartjs" data-height="350"></canvas>
            </div>
          </div>
        </div>
        {{-- Tab Ingresos para esta moneda --}}
        <div class="tab-pane fade" id="ingresos-tab-{{ $currencyCode }}" role="tabpanel">
          <div class="card">
            <h6 class="card-header">Ingresos por Caja ({{ $currencyChartData['infoMoneda']->nombre ?? $currencyCode }})</h6>
            <div class="card-body">
              <canvas id="ingresos-{{ $currencyCode }}" class="chartjs" data-height="350"></canvas>
            </div>
          </div>
        </div>
        {{-- Tab Egresos para esta moneda --}}
        <div class="tab-pane fade" id="egresos-tab-{{ $currencyCode }}" role="tabpanel">
          <div class="card">
            <h6 class="card-header">Egresos por Caja ({{ $currencyChartData['infoMoneda']->nombre ?? $currencyCode }})</h6>
            <div class="card-body">
              <canvas id="egresos-{{ $currencyCode }}" class="chartjs" data-height="350"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endforeach
@else
<div class="alert alert-info" role="alert">
  No hay datos disponibles para mostrar estadísticas por moneda.
</div>
@endif

@endsection