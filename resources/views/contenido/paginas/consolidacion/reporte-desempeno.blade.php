@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Reporte de Desempeño')

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
  <h4 class="mb-1 fw-semibold text-primary">Reporte de Desempeño</h4>
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
<ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="pills-zonas-tab" data-bs-toggle="pill" data-bs-target="#pills-zonas" type="button" role="tab" aria-controls="pills-zonas" aria-selected="true"><i class="ti ti-map-2 me-2"></i>Desempeño por Zonas</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-colaboradores-tab" data-bs-toggle="pill" data-bs-target="#pills-colaboradores" type="button" role="tab" aria-controls="pills-colaboradores" aria-selected="false"><i class="ti ti-users me-2"></i>Ranking Colaboradores</button>
  </li>
</ul>

<div class="tab-content p-0 shadow-none bg-transparent" id="pills-tabContent">
  <!-- Pestaña 1: Zonas -->
  <div class="tab-pane fade show active" id="pills-zonas" role="tabpanel" aria-labelledby="pills-zonas-tab">
    @if(isset($zonasParaReporte) && $zonasParaReporte->count() > 0)
      <div class="row">
        <div class="col-12">
          <div class="accordion" id="accordionDesempenoZonas">
            @foreach($zonasParaReporte as $zona)
              <div class="accordion-item card mb-4 shadow-sm border">
                <h6 class="accordion-header d-flex flex-column justify-content-between align-items-center pe-3" id="heading{{ $zona->id }}">
                  <button type="button" class="accordion-button collapsed flex-grow-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#collapse{{ $zona->id }}" aria-expanded="false" aria-controls="collapse{{ $zona->id }}">
                    <div class="d-flex flex-column text-start">
                      <span class="fs-5 fw-bold text-uppercase text-primary">{{ $zona->nombre }}</span>
                      <small class="text-muted">Indicadores por Sede y desempeño general</small>
                    </div>
                  </button>
                </h6>

                <div id="collapse{{ $zona->id }}" class="accordion-collapse collapse border-top border-2 pt-4" aria-labelledby="heading{{ $zona->id }}">
                  <div class="accordion-body">
                    
                    <!-- Indicadores Rápidos de la Zona -->
                    <div class="row g-3 mb-5">
                        <div class="col-12 col-md-4">
                            <div class="card bg-label-primary shadow-none border-0">
                                <div class="card-body py-3 text-center">
                                    <h5 class="card-title mb-0 fw-bold">{{ $zona->totalCosecha }}</h5>
                                    <small class="text-muted text-uppercase small">Cosecha Total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card bg-label-success shadow-none border-0">
                                <div class="card-body py-3 text-center">
                                    <h5 class="card-title mb-0 fw-bold">{{ $zona->cosechaEfectiva }}</h5>
                                    <small class="text-muted text-uppercase small">Cosecha Efectiva</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card bg-label-warning shadow-none border-0">
                                <div class="card-body py-3 text-center">
                                    <h5 class="card-title mb-0 fw-bold">{{ $zona->sinGestionPeriodo }}</h5>
                                    <small class="text-muted text-uppercase small">Sin Gestión (Periodo)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Desglose por Sedes -->
                    <h6 class="fw-bold text-uppercase mb-3"><i class="ti ti-building me-2"></i>Detalle por Sedes</h6>
                    <div class="table-responsive text-nowrap rounded-3 border mb-5">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Sede</th>
                                    <th class="text-center py-3">Cosecha Total</th>
                                    <th class="text-center py-3">Cosecha Efectiva</th>
                                    <th class="text-center py-3">Sin Gestión (Periodo)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($zona->desgloseSedes as $sede)
                                    <tr>
                                        <td class="fw-semibold">{{ $sede['nombre'] }}</td>
                                        <td class="text-center">{{ $sede['cosecha'] }}</td>
                                        <td class="text-center">{{ $sede['efectiva'] }}</td>
                                        <td class="text-center">
                                            @if($sede['sin_gestion'] > 0)
                                                <span class="badge bg-label-warning rounded-pill">{{ $sede['sin_gestion'] }}</span>
                                            @else
                                                <span class="badge bg-label-success rounded-pill">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabla de Tabulación por Colaborador -->
                    <h6 class="fw-bold text-uppercase mb-3"><i class="ti ti-users me-2"></i>Desempeño de Colaboradores</h6>
                    <div class="table-responsive text-nowrap rounded-3 border">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Colaborador</th>
                                    @foreach($tiposTarea as $tipo)
                                        <th class="text-center py-3">{{ $tipo->nombre }}</th>
                                    @endforeach
                                    <th class="text-center py-3 fw-bold bg-label-secondary">Total</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($zona->tabulacionColaboradores as $colab)
                                    <tr>
                                        <td class="fw-semibold">{{ $colab['nombre'] }}</td>
                                        @foreach($tiposTarea as $tipo)
                                            <td class="text-center">
                                                @if($colab['tareas'][$tipo->id] > 0)
                                                    <span class="badge bg-label-primary rounded-pill">{{ $colab['tareas'][$tipo->id] }}</span>
                                                @else
                                                    <span class="text-muted opacity-25">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center fw-bold bg-label-secondary">{{ $colab['total'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $tiposTarea->count() + 2 }}" class="text-center py-5 text-muted">
                                            No se registraron gestiones.
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

  <!-- Pestaña 2: Por definir -->
  <div class="tab-pane fade" id="pills-colaboradores" role="tabpanel" aria-labelledby="pills-colaboradores-tab">
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-tool fs-1 text-muted opacity-25 mb-3"></i>
            <h5>Proximamente</h5>
            <p class="text-muted">Esta sección será implementada en la siguiente fase.</p>
        </div>
    </div>
  </div>
</div>

@endsection
