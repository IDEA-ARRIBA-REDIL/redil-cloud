@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Reporte de desempeño')

@section('page-style')
@vite([
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
<style>
  .border-none {
    border: none !important;
  }
  .border-gray {
    border-color: #d9dee3 !important;
  }
</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('.selectpicker').selectpicker();

    // Configuración de Flatpickr en Español
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

    const fp = flatpickr(".flatpickr-range", {
      mode: "range",
      dateFormat: "Y-m-d",
      locale: "es",
      onChange: function(selectedDates, dateStr, instance) {
         document.getElementById('filtro_rapido').value = "";
      }
    });

    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Función global para el filtro rápido de fechas
    window.seleccionarRango = function(tipo) {
      if (!tipo) return;
      
      let inicio, fin;
      const hoy = new Date();
      
      if (tipo === 'semana_actual') {
        const diaSemana = hoy.getDay(); // 0 (Dom) a 6 (Sáb)
        const diferenciaLunes = (diaSemana === 0 ? -6 : 1) - diaSemana;
        inicio = new Date(hoy);
        inicio.setDate(hoy.getDate() + diferenciaLunes);
        inicio.setHours(0, 0, 0, 0);
        
        fin = new Date(inicio);
        fin.setDate(inicio.getDate() + 6);
        fin.setHours(23, 59, 59, 999);
      } else if (tipo === 'semana_anterior') {
        const diaSemana = hoy.getDay();
        const diferenciaLunesAnterior = ((diaSemana === 0 ? -6 : 1) - diaSemana) - 7;
        inicio = new Date(hoy);
        inicio.setDate(hoy.getDate() + diferenciaLunesAnterior);
        inicio.setHours(0, 0, 0, 0);
        
        fin = new Date(inicio);
        fin.setDate(inicio.getDate() + 6);
        fin.setHours(23, 59, 59, 999);
      } else if (tipo === 'este_mes') {
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

      const fpInstance = document.querySelector("#rango_fechas")._flatpickr;
      if (fpInstance) {
        fpInstance.setDate([inicio, fin]);
      }
    };
  });
</script>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-1 fw-semibold text-primary">Reporte de desempeño</h4>
</div>

<!-- Barra de Filtros -->
<form id="formFiltros" action="{{ route('consolidacion.reporteDesempeño') }}" method="GET">
  <div class="row bg-white rounded-3 p-0 m-0 mb-4 shadow-sm border border-gray">
    <div class="row col-12 col-md-11 p-0 m-0">
      
      <!-- Rango Predefinido -->
      <div class="col-12 col-md-3 border-end border-gray p-0 d-flex">
        <div class="input-group input-group-merge">
          <span class="input-group-text bg-transparent border-none"><i class="ti ti-calendar text-black"></i></span>
          <select class="form-select text-black border-none" id="filtro_rapido" onchange="seleccionarRango(this.value)">
            <option value="">Rango de fecha</option>
            <option value="semana_actual">Semana actual</option>
            <option value="semana_anterior">Semana anterior</option>
            <option value="este_mes">Este mes</option>
            <option value="mes_pasado">Mes pasado</option>
            <option value="este_ano">Este año</option>
            <option value="trimestre_actual">Trimestre actual</option>
          </select>
        </div>
      </div>

      <!-- Flatpickr Range -->
      <div class="col-12 col-md-3 border-end border-gray p-0 d-flex">
        <input type="text" class="form-control border-none flatpickr-range text-center" id="rango_fechas" name="rango_fechas" value="{{ $rangoFechas }}" placeholder="aaaa-mm-dd a aaaa-mm-dd">
      </div>
            
      <!-- Filtro de Zonas -->
      <div class="col-12 col-md-6 border-end border-gray p-0 d-flex align-items-center">
        <select name="zonas_seleccionadas[]" class="selectpicker form-select border-none w-100" multiple data-actions-box="true" data-select-all-text="Todos" data-deselect-all-text="Borrar" data-style="btn-default border-0" data-live-search="true" title="Seleccione zonas...">
          @foreach($zonasDisponibles as $zona)
            <option value="{{ $zona->id }}" {{ in_array($zona->id, $zonasSeleccionadas) ? 'selected' : '' }}>{{ $zona->nombre }}</option>
          @endforeach
        </select>
      </div>

    </div>
    
    <!-- Botón Filtrar -->
    <div class="col-12 col-md-1 p-0">
      <button type="submit" class="btn btn-xl btn-primary w-100 rounded-0 rounded-end h-100 px-auto fs-6">Filtrar</button>
    </div>
  </div>
</form>

<!-- Pestañas del Reporte -->
<div class="card mb-4 p-1 border">
  <ul class="nav nav-pills justify-content-start flex-column flex-md-row gap-2" role="tablist">
    <li class="nav-item flex-fill">
      <button type="button" class="nav-link p-3 waves-effect waves-light active" id="pills-zonas-tab" data-bs-toggle="pill" data-bs-target="#pills-zonas" role="tab" aria-controls="pills-zonas" aria-selected="true">
        Desempeño por zonas
      </button>
    </li>
    <li class="nav-item flex-fill">
      <button type="button" class="nav-link p-3 waves-effect waves-light" id="pills-colaboradores-tab" data-bs-toggle="pill" data-bs-target="#pills-colaboradores" role="tab" aria-controls="pills-colaboradores" aria-selected="false">
        Ranking de colaboradores
      </button>
    </li>
  </ul>
</div>

<div class="tab-content p-0 shadow-none bg-transparent" id="pills-tabContent">
  <!-- Pestaña 1: Zonas -->
  <div class="tab-pane fade show active" id="pills-zonas" role="tabpanel" aria-labelledby="pills-zonas-tab">
    @if(isset($zonasDesempeno) && $zonasDesempeno->count() > 0)
      <div class="row">
        <div class="col-12">
          <div class="accordion" id="accordionDesempenoZonas">
            @foreach($zonasDesempeno as $zona)
              <div class="accordion-item card mb-3 border">
                <h6 class="accordion-header d-flex flex-column justify-content-between align-items-center pe-3" id="heading{{ $zona->id }}">
                  <button type="button" class="accordion-button collapsed flex-grow-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#collapse{{ $zona->id }}" aria-expanded="false" aria-controls="collapse{{ $zona->id }}">
                    <div class="d-flex flex-column text-start">
                      <span class="fs-5 fw-semibold text-uppercase text-black">{{ $zona->nombre }}</span>
                      <div class="d-flex gap-3">
                        <small class="text-black">Total tareas sin gestión: <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'sin_gestion', 'zona_id' => $zona->id, 'rango_fechas' => $rangoFechas]) }}" class="text-primary fw-bold"><u>{{ $zona->sinGestionPeriodo }}</u></a></small>
                      </div>
                    </div>
                  </button>
                </h6>

                <div id="collapse{{ $zona->id }}" class="accordion-collapse collapse border-top border-2 pt-4" aria-labelledby="heading{{ $zona->id }}">
                  <div class="accordion-body">
                    
                    <!-- Indicadores Rápidos de la Zona -->
                    <div class="row g-3 mb-5">
                        <div class="col-12 col-md-4 col-lg">
                          <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'cosecha_total', 'zona_id' => $zona->id, 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none">
                            <div class="card shadow-none border">
                              <div class="card-body py-3 border-bottom d-block">
                                  <div class="d-flex justify-content-between">
                                    <div>
                                      <h5 class="card-title mb-0 fw-semibold fs-4 text-primary">{{ $zona->totalCosecha }}</h5>
                                      <small class="text-black">Total cosecha</small>
                                    </div>
                                    <div class="p-2 d-flex align-items-center justify-content-center">
                                      <i class="ti ti-chevron-right text-black ti-sm"></i>
                                    </div>
                                  </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <div class="col-12 col-md-4 col-lg">
                          <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'cosecha_efectiva', 'zona_id' => $zona->id, 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none">
                            <div class="card shadow-none border">
                              <div class="card-body py-3 border-bottom d-block">
                                  <div class="d-flex justify-content-between">
                                    <div>
                                      <h5 class="card-title mb-0 fw-semibold fs-4 text-primary">{{ $zona->cosechaEfectiva }}</h5>
                                      <small class="text-black">Cosecha efectiva</small>
                                    </div>
                                    <div class="p-2 d-flex align-items-center justify-content-center">
                                      <i class="ti ti-chevron-right text-black ti-sm"></i>
                                    </div>
                                  </div>
                              </div>
                            </div>
                          </a>
                        </div>
                        <div class="col-12 col-md-4 col-lg">
                            <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'sin_gestion', 'zona_id' => $zona->id, 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none">
                              <div class="card shadow-none border">
                                <div class="card-body py-3 border-bottom d-block">
                                    <div class="d-flex justify-content-between">
                                      <div>
                                        <h5 class="card-title mb-0 fw-semibold fs-4 text-primary">{{ $zona->sinGestionPeriodo }}</h5>
                                        <small class="text-black">Sin gestión de tareas</small>
                                      </div>
                                      <div class="p-2 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-chevron-right text-black ti-sm"></i>
                                      </div>
                                    </div>
                                </div>
                              </div>
                            </a>
                        </div>
                        <div class="col-12 col-md-4 col-lg">
                            <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'matriculas', 'zona_id' => $zona->id, 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none">
                              <div class="card shadow-none border">
                                <div class="card-body py-3 border-bottom d-block">
                                    <div class="d-flex justify-content-between">
                                      <div>
                                        <h5 class="card-title mb-0 fw-semibold fs-4 text-primary">{{ $zona->totalMatriculas }}</h5>
                                        <small class="text-black">Matrículas</small>
                                      </div>
                                      <div class="p-2 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-chevron-right text-black ti-sm"></i>
                                      </div>
                                    </div>
                                </div>
                              </div>
                            </a>
                        </div>
                        @foreach($zona->metricasCrecimiento as $metrica)
                        <div class="col-12 col-md-4 col-lg">
                            <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'paso_'.$metrica['paso_id'], 'zona_id' => $zona->id, 'rango_fechas' => $rangoFechas]) }}" class="text-decoration-none">
                              <div class="card shadow-none border">
                                <div class="card-body py-3 border-bottom d-block">
                                    <div class="d-flex justify-content-between">
                                      <div>
                                        <h5 class="card-title mb-0 fw-semibold fs-4 text-primary">{{ $metrica['total'] }}</h5>
                                        <small class="text-black">{{ $metrica['nombre'] }}</small>
                                      </div>
                                      <div class="p-2 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-chevron-right text-black ti-sm"></i>
                                      </div>
                                    </div>
                                </div>
                              </div>
                            </a>
                        </div>
                        @endforeach
                    </div>

                    <!-- Tabla de Desglose por Sedes -->
                    <h6 class="fw-bold text-uppercase mb-3 mt-5"><i class="ti ti-building me-2"></i>Detalle por sedes</h6>
                    <div class="table-responsive text-nowrap rounded-3 border mb-5">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Sede</th>
                                    <th class="text-center py-3">Cosecha Total</th>
                                    <th class="text-center py-3">Cosecha Efectiva</th>
                                    <th class="text-center py-3">Sin Gestión (Tareas)</th>
                                    <th class="text-center py-3 border-start">Matrículas</th>
                                    @foreach($zona->metricasCrecimiento as $metrica)
                                        <th class="text-center py-3">{{ $metrica['nombre'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($zona->desgloseSedes as $sede)
                                    <tr class="bg-light">
                                        <td class="fw-semibold text-black">{{ $sede['nombre'] }}</td>
                                        <td class="text-center fw-bold text-black">
                                            <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'cosecha_total', 'zona_id' => $zona->id, 'sede_id' => $sede['id'], 'rango_fechas' => $rangoFechas]) }}" class="text-primary"><u>{{ $sede['cosecha'] }}</u></a>
                                        </td>
                                        <td class="text-center fw-bold text-success">
                                            <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'cosecha_efectiva', 'zona_id' => $zona->id, 'sede_id' => $sede['id'], 'rango_fechas' => $rangoFechas]) }}" class="text-success"><u>{{ $sede['efectiva'] }}</u></a>
                                        </td>
                                        <td class="text-center fw-bold text-warning">
                                            <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'sin_gestion', 'zona_id' => $zona->id, 'sede_id' => $sede['id'], 'rango_fechas' => $rangoFechas]) }}" class="text-warning"><u>{{ $sede['sin_gestion'] }}</u></a>
                                        </td>
                                        <td class="text-center fw-bold text-black border-start">
                                            <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'matriculas', 'zona_id' => $zona->id, 'sede_id' => $sede['id'], 'rango_fechas' => $rangoFechas]) }}" class="text-primary"><u>{{ $sede['total_matriculas'] }}</u></a>
                                        </td>
                                        @foreach($zona->metricasCrecimiento as $metrica)
                                            <td class="text-center fw-bold text-primary">
                                                <a href="{{ route('consolidacion.detalle-kpi', ['kpi' => 'paso_'.$metrica['paso_id'], 'zona_id' => $zona->id, 'sede_id' => $sede['id'], 'rango_fechas' => $rangoFechas]) }}" class="text-primary"><u>{{ $sede['crecimiento'][$metrica['paso_id']] ?? 0 }}</u></a>
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td colspan="{{ 5 + count($zona->metricasCrecimiento) }}" class="p-0 border-0">
                                            <div class="px-4 py-3 bg-white">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-borderless bg-white mb-0">
                                                        <thead>
                                                            <tr class="border-bottom">
                                                                <th class="py-2 px-3 fw-semibold small text-black text-uppercase" style="width: 40%;">Tarea</th>
                                                                @foreach($estadosTarea as $est)
                                                                    <th class="text-center py-2 px-3 fw-semibold small text-black text-uppercase">{{ $est->nombre }}</th>
                                                                @endforeach
                                                                <th class="text-center py-2 px-3 fw-semibold small text-black text-uppercase border-start">Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($sede['tabulacion_tareas'] as $idTarea => $dataT)
                                                                <tr class="border-bottom-0">
                                                                    <td class="small px-3 py-2 text-black">{{ $dataT['nombre'] }}</td>
                                                                    @foreach($estadosTarea as $est)
                                                                        <td class="text-center small py-2">
                                                                            @if($dataT['estados'][$est->id] > 0)
                                                                                <span class="badge bg-label-primary rounded-pill fw-bold">{{ $dataT['estados'][$est->id] }}</span>
                                                                            @else
                                                                                <span class="text-muted opacity-50 small">-</span>
                                                                            @endif
                                                                        </td>
                                                                    @endforeach
                                                                    <td class="text-center fw-bold small py-2 border-start text-black">{{ $dataT['total_tarea'] }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                     {{-- Seccion de desempeño de colaboradores ocultada temporalmente --}}

                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @else
      <div class="row">
        <div class="col-12 text-center py-5">
          <div class="card shadow-sm border p-5">
            <i class="ti ti-search fs-1 text-muted opacity-25"></i>
            <h5 class="mt-3">No hay datos para mostrar</h5>
            <p class="text-muted">Por favor selecciona al menos una zona en los filtros superiores para generar el reporte.</p>
          </div>
        </div>
      </div>
    @endif
  </div>

  <!-- Pestaña 2: Ranking de Colaboradores -->
  <div class="tab-pane fade" id="pills-colaboradores" role="tabpanel" aria-labelledby="pills-colaboradores-tab">
    @if(isset($zonasRanking) && $zonasRanking->count() > 0)
      <div class="accordion" id="accordionRanking">
        @foreach($zonasRanking as $zona)
          <div class="accordion-item card mb-3 border">
            <h6 class="accordion-header d-flex flex-column justify-content-between align-items-center pe-3" id="headingRanking{{ $zona->id }}">
              <button type="button" class="accordion-button collapsed flex-grow-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseRanking{{ $zona->id }}" aria-expanded="false" aria-controls="collapseRanking{{ $zona->id }}">
                <div class="d-flex flex-column text-start">
                  <span class="fs-5 fw-semibold text-uppercase text-black">{{ $zona->nombre }}</span>
                  <small class="text-black">Total de gestiones: {{ $zona->totalGestionesRanking ?? 0 }}</small>
                </div>
              </button>
            </h6>

            <div id="collapseRanking{{ $zona->id }}" class="accordion-collapse collapse border-top border-2 pt-4" aria-labelledby="headingRanking{{ $zona->id }}">
              <div class="accordion-body p-0">
                <div class="table-responsive text-nowrap">
                  <table class="table table-sm table-borderless table-hover mb-0">
                    <thead>
                      <tr class="border-bottom">
                        <th class="py-3 px-3 small text-black text-uppercase text-center" style="width: 50px;">Pos.</th>
                        <th class="py-3 small text-black text-uppercase">Colaborador</th>
                        @foreach($tiposTarea as $tipo)
                          <th class="text-center py-3 small text-black text-uppercase">{{ $tipo->nombre }}</th>
                        @endforeach
                        <th class="text-center py-3 small text-black text-uppercase border-start">Total Gestiones</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      @php $pos = 1; @endphp
                      @forelse($zona->rankingColaboradores as $idColab => $colab)
                        <tr class="border-bottom-0">
                          <td class="text-center fw-bold text-black py-2">{{ $pos++ }}</td>
                          <td class="py-2">
                            <div class="d-flex justify-content-start align-items-center user-name">
                              <div class="avatar-wrapper">
                                <div class="avatar avatar-sm me-3 border rounded-circle shadow-xs">
                                  @if($colab['foto'])
                                    <img src="{{ $colab['foto'] }}" alt="Avatar" class="rounded-circle">
                                  @else
                                    <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($colab['nombre'], 0, 2) }}</span>
                                  @endif
                                </div>
                              </div>
                              <div class="d-flex flex-column">
                                <span class="fw-semibold text-black">{{ $colab['nombre'] }}</span>
                              </div>
                            </div>
                          </td>
                          @foreach($tiposTarea as $tipo)
                            <td class="text-center py-2">
                              @if(isset($colab['tareas'][$tipo->id]) && is_array($colab['tareas'][$tipo->id]) && ($colab['tareas'][$tipo->id]['total'] ?? 0) > 0)
                                @php
                                  $tooltipContent = "";
                                  foreach($estadosTarea as $est) {
                                    $cant = $colab['tareas'][$tipo->id]['estados'][$est->id] ?? 0;
                                    if($cant > 0) {
                                      $tooltipContent .= "• {$est->nombre}: <b>{$cant}</b><br>";
                                    }
                                  }
                                @endphp
                                <span class="badge bg-label-primary rounded-pill fw-bold cursor-pointer" 
                                      data-bs-toggle="tooltip" 
                                      data-bs-html="true" 
                                      title="{!! $tooltipContent !!}">
                                  {{ $colab['tareas'][$tipo->id]['total'] }}
                                </span>
                              @else
                                <span class="text-muted opacity-50 small">-</span>
                              @endif
                            </td>
                          @endforeach
                          <td class="text-center fw-bold text-black py-2 border-start">{{ $colab['total'] }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="{{ $tiposTarea->count() + 3 }}" class="text-center py-5">
                            <i class="ti ti-info-circle fs-2 text-muted mb-2"></i>
                            <p class="text-muted mb-0">No se encontraron gestiones registradas por colaboradores en esta zona durante el periodo.</p>
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="ti ti-search fs-1 text-muted opacity-25"></i>
          <h5 class="mt-3">No hay datos para mostrar</h5>
          <p class="text-muted">Selecciona zonas en los filtros para ver el ranking.</p>
        </div>
      </div>
    @endif
  </div>
</div>

@endsection
