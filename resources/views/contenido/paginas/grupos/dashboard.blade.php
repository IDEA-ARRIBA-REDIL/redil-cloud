@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard grupo')

@section('page-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
<style>
  .select2-container--default .select2-selection--single,
  .select2-container--default .select2-selection--multiple {
    border: none !important;
    background-color: transparent !important;
  }
  
  /* Custom Accordion Icon */
  .accordion-button::after {
    display: none !important;
  }
  .accordion-button .accordion-icon {
    transition: transform 0.3s ease;
  }
  .accordion-button:not(.collapsed) .accordion-icon {
    transform: rotate(180deg);
  }
</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js'
])
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
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
      firstDayOfWeek: 1,
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
              instance.setDate([start, end], true); // true = trigger change (optional, but good for UI update)
          }
      }
    });

    // Inicializar Select2
    $('.select2').select2({
      width: '100%',
      //dropdownParent: $('#offcanvasFiltros'), // Ya no es necesario
      allowClear: true,
      language: {
        noResults: function() {
          return "No se encontraron resultados";
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
        fp.setDate([inicio, fin]); // true handles triggering hooks if needed, or update input
      }
    };
    
    // --- Lógica Dependiente Bloques -> Sedes (Single Selection) ---
    $(document).ready(function() {
        const $selectBloques = $('#filtro_bloques');
        const $selectSedes = $('#filtro_sedes');
        
        // Clonar opciones originales de Sedes para restaurar al filtrar
        let $opcionesOriginales = $selectSedes.find('option').clone();

        $selectBloques.on('change', function() {
            const bloqueId = $(this).val();

            // Limpiar select de sedes
            $selectSedes.empty();

            if (bloqueId) {
                $selectSedes.prop('disabled', false);
                $selectSedes.append('<option value="">Todas las sedes</option>');
                
                // Filtrar y agregar
                $opcionesOriginales.each(function() {
                    // data 'bloque-id' puede venir como número o string
                    const dataBloque = $(this).data('bloque-id');
                    if (dataBloque == bloqueId) {
                        $selectSedes.append($(this).clone());
                    }
                });
            } else {
                $selectSedes.prop('disabled', true);
                $selectSedes.append('<option value="">Todas las sedes</option>');
            }
            
            // Forzar actualización visual Select2
            $selectSedes.trigger('change.select2'); 
        });

        // Estado inicial al cargar la página
        if (!$selectBloques.val()) {
             $selectSedes.prop('disabled', true);
        } else {
             $selectSedes.prop('disabled', false);
             // Trigger change para asegurar que sedes filtradas se muestren correctamente
             // si es que ya había algo seleccionado (aunque backend filtre, visualmente select2 necesita options)
             
             // Si el back ya filtró visualmente las opciones (no lo hace, lo hace el JS), necesitamos filtrar.
             $selectBloques.trigger('change');
             
             // Si el back seleccionó una sede, restaurarla
             const sedePreseleccionada = "{{ isset($sedesSeleccionadas) && count($sedesSeleccionadas) == 1 ? $sedesSeleccionadas[0] : '' }}";
             if(sedePreseleccionada && sedePreseleccionada !== '') {
                 $selectSedes.val(sedePreseleccionada);
                 $selectSedes.trigger('change.select2');
             }
        }
    });

    // Función global para seleccionar/deseleccionar todo
    window.toggleSelectAll = function(idSelect, seleccionar) {
        const $select = $('#' + idSelect);
        if (seleccionar) {
             // Seleccionar todas las opciones (solo las que existen en el DOM, o sea las visibles/filtradas)
             // Para Sedes, ya hemos eliminado del DOM las que no corresponden, así que esto es seguro.
             const allValues = $select.find('option').map(function() {
                return $(this).val();
            }).get();
            $select.val(allValues);
        } else {
            $select.val(null);
        }
        $select.trigger('change');
    }
    
    // Gráfico de Tipos de Grupo
    const tiposGrupoEl = document.querySelector('#tiposGrupoChart');
    if (tiposGrupoEl) {
      const dataGrafica = @json($datosGraficaTipos);
      const seriesData = dataGrafica.map(item => item.value);
      const labelsData = dataGrafica.map(item => item.label);

      const chartConfig = {
        chart: {
          height: 380,
          type: 'donut',
          toolbar: { show: false }
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
          show: false,
          position: 'bottom',
          fontFamily: 'Poppins'
        },
        plotOptions: {
            pie: {
                donut: {
                    labels: {
                        show: true,
                         total: {
                            show: true,
                            label: 'Total',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        }
      };

      const chart = new ApexCharts(tiposGrupoEl, chartConfig);
      chart.render();
    }
    
    // Validación del formulairo de filtros
    $('#formFiltros').on('submit', function(e) {
        const rango = $('#rango_fechas').val();
        const tipos = $('#filtro_tipo_grupo').val();
        const bloques = $('#filtro_bloques').val();
        const sedes = $('#filtro_sedes').val();
        
        // Validar que no estén vacíos. 
        // Nota: Filtros de tipo select multiple devuelven array vacio [] o null si no hay seleccion,
        // pero select2/bootstrap-select suelen manejarlo. Val() devuelve [] si vacio en multiple.
        
        let errores = [];
        
        if (!rango || rango.trim() === '') {
            errores.push("El rango de fechas es obligatorio.");
        }
        
        if (!tipos || tipos.length === 0) {
            // errores.push("Debe seleccionar al menos un tipo de grupo."); // Quitamos validación estricta si queremos permitir vacio como 'todos' o defecto
        }
        
        // Agrupaciones puede ser vacio (todas)
        // Sedes puede ser vacio (todas)
        
        if (errores.length > 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Faltan filtros',
                html: errores.join('<br>'),
                icon: 'warning',
                confirmButtonText: 'Entendido',
                customClass: {
                  confirmButton: 'btn btn-primary'
                }
            });
        }
    });

  });
</script>
@endsection

@section('content')
  <h4 class="mb-1 fw-semibold text-primary">Dashboard grupos</h4>

  <!-- Tabs de Navegación -->
  @php
    $activeTab = request('tab', 'grupos');
  @endphp

  <div class="card mb-10 p-1 border-1">
    <ul class="nav nav-pills justify-content-start flex-column flex-md-row gap-2" role="tablist">
      <li class="nav-item flex-fill">
        <button type="button" class="nav-link p-3 waves-effect waves-light {{ $activeTab == 'grupos' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tab-grupos" aria-controls="navs-tab-grupos" aria-selected="{{ $activeTab == 'grupos' ? 'true' : 'false' }}">
          Grupos
        </button>
      </li>
      <li class="nav-item flex-fill">
        <button type="button" class="nav-link p-3 waves-effect waves-light {{ $activeTab == 'asistencias' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tab-asistencias" aria-controls="navs-tab-asistencias" aria-selected="{{ $activeTab == 'asistencias' ? 'true' : 'false' }}">
          Asistencias
        </button>
      </li>
    </ul>
  </div>


<!-- Barra de Filtros -->
<form id="formFiltros" action="{{ route('grupos.dashboard') }}" method="GET">
  <div class="row bg-white rounded-3 p-0 m-0 mb-4 shadow-sm border border-gray">
    <div class="row col-12 col-md-11 p-0 m-0">
      
      <!-- Rango Predefinido -->
      <div class="col-12 col-md-2 border-end border-gray p-0 d-flex">

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
      
      <!-- Tipos de grupo -->
      <div class="col-12 col-md-3 border-end border-gray p-0 d-flex align-items-center">
         <select class="selectpicker form-select  border-none w-100" id="filtro_tipo_grupo" name="filtro_tipo_grupo[]" multiple data-actions-box="true" data-select-all-text="Todos" data-deselect-all-text="Borrar" data-placeholder="Tipo de grupo" data-style="btn-default border-0">
              @foreach($tiposGrupo as $tipo)
                <option value="{{ $tipo->id }}" {{ in_array($tipo->id, $tiposSeleccionados) ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
              @endforeach
          </select>
      </div>

      <!-- Agrupaciones -->
      <div class="col-12 col-md-2 border-end border-gray p-0 d-flex align-items-center">
         <select class="select2 form-select  border-0 " id="filtro_bloques" name="filtro_bloques" data-placeholder="Agrupaciones">
                 <option value="">Todas las agrupaciones</option>
                @foreach($bloques as $bloque)
                  <option value="{{ $bloque->id }}" {{ (isset($bloquesSeleccionados) && count($bloquesSeleccionados) == 1 && $bloquesSeleccionados[0] == $bloque->id) ? 'selected' : '' }}>{{ $bloque->nombre }}</option>
                @endforeach
            </select>
      </div>


      <!-- Sede -->
      <div class="col-12 col-md-2 border-end border-gray p-0 d-flex align-items-center">
        <select class="select2 form-select border-0 m-0" id="filtro_sedes" name="filtro_sedes" data-placeholder="Sede" {{ !(isset($bloquesSeleccionados) && count($bloquesSeleccionados) == 1) ? 'disabled' : '' }}>
            <option value="">Todas las sedes</option>
            @foreach($bloques as $bloque)
                @foreach($bloque->sedes as $sede)
                    <option value="{{ $sede->id }}" data-bloque-id="{{ $bloque->id }}" {{ (isset($sedesSeleccionadas) && count($sedesSeleccionadas) == 1 && $sedesSeleccionadas[0] == $sede->id) ? 'selected' : '' }}>{{ $sede->nombre }} {{ $sede->id}}</option>
                @endforeach
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


<!-- Contenido de las Tabs -->
<div class="tab-content p-0 bg-transparent shadow-none border-0">
  
  <div class="card mb-4 border rounded-3 border-info"> 
    <div class="card-body p-4">
      <div class="row">
        <div class="col-12 col-md-10 d-flex align-items-center">
          <p class="text-black mb-0"><b>Comparador de grupos:</b> Consulta las estadísticas de los distintos grupos y comparalas en tiempo real.</p>
        </div>
        <div class="col-12 col-md-2 text-center">
          <a  href="{{ route('grupos.comparativo') }}" class="btn btn-primary rounded-pill">
            <i class="ti ti-arrows-diff me-1"></i> Comparativo
          </a>
        </div>
      </div>
    </div>
  </div>
 

  <!-- TAB GRUPOS -->
  <div class="tab-pane fade {{ $activeTab == 'grupos' ? 'show active' : '' }}" id="navs-tab-grupos" role="tabpanel">

  
    <div class="d-flex justify-content-end">   
      <a href="{{ route('consolidacion.bloques') }}" class="btn btn-outline-primary rounded-pill btn-sm">
        <i class="ti ti-settings me-1"></i> Bloques de sedes 
      </a>
    </div>

    <div class="row g-4 justify-content-center py-5">


      <div class="col-12 col-md-5">
        <div class="row equal-height-row g-2">

          <div class="col col-12 equal-height-col">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between">
                <div>
                  <h4 class="card-title text-uppercase mb-0 fw-light">Total grupos</h4>
                  <a href="{{ route('grupos.detalle-kpi', array_merge(request()->query(), ['kpi' => 'total'])) }}" target="_blank" class="text-decoration-none">
                    <h2 class="card-title text-uppercase mb-0 fw-semibold text-black">{{ $totalGrupos }}</h2>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div class="col col-12 equal-height-col">
            <a href="{{ route('grupos.detalle-kpi', array_merge(request()->query(), ['kpi' => 'nuevos'])) }}" target="_blank" class="text-decoration-none">
              <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                  <div>
                    <h6 class="card-title text-uppercase mb-0 fw-light">Nuevos</h6>
                    <h3 class="card-title text-uppercase mb-0 fw-semibold text-black">{{ $gruposNuevos }}</h3>
                  </div>
                  <div class="p-2 d-flex align-items-center justify-content-center">
                    <i class="ti ti-chevron-right text-black ti-lg"></i>
                  </div>
                </div>
              </div>
            </a>
          </div>
          
          <div class="col col-12 equal-height-col">
            <a href="{{ route('grupos.detalle-kpi', array_merge(request()->query(), ['kpi' => 'bajas'])) }}" target="_blank" class="text-decoration-none">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between">
                <div>
                  <h6 class="card-title text-uppercase mb-0 fw-light">Bajas</h6>
                  <h3 class="card-title text-uppercase mb-0 fw-semibold text-black">{{ $gruposBaja }}</h3>
                </div>
                <div class="p-2 d-flex align-items-center justify-content-center">
                  <i class="ti ti-chevron-right text-black ti-lg"></i>
                </div>
              </div>
            </div>
            </a>
          </div>

          <div class="col col-12 equal-height-col">
            <a href="{{ route('grupos.detalle-kpi', array_merge(request()->query(), ['kpi' => 'inactivos'])) }}" target="_blank" class="text-decoration-none">
              <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                  <div>
                    <h6 class="card-title text-uppercase mb-0 fw-light">Inactivos</h6>
                    <h3 class="card-title text-uppercase mb-0 fw-semibold text-black">{{ $gruposInactivos }}</h3>
                  </div>
                  <div class="p-2 d-flex align-items-center justify-content-center">
                    <i class="ti ti-chevron-right text-black ti-lg"></i>
                  </div>
                </div>
              </div>
            </a>
          </div>

        </div>
      </div>

      <div class="col-12 col-md-7">
        <!-- Gráfica de Tipos de Grupo -->
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h6 class="card-title mb-0 fw-bold">Tipos de grupo</h6>
              <small class="text-black">
                Distribución por tipo
              </small>
            </div>
          </div>
          <div class="card-body">                  
            <div id="tiposGrupoChart"></div>
          </div>
        </div>
      </div>

      <div class="col-12">

        <hr class="my-5">

        <!-- Detalle por Bloques -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-muted fw-light mb-0">Total sedes: <span class="text-black fw-semibold">{{ count($sedesSeleccionadas) }}</span></h5>
        </div>

        <div class="accordion" id="accordionBloques">
          @foreach($bloques as $bloque)
              @if(in_array($bloque->id, $bloquesSeleccionados))
              <div class="accordion-item mb-3 border-0 shadow-none" style="background-color: transparent !important;">
                  @php
                      $sedesFiltradas = $bloque->sedes->whereIn('id', $sedesSeleccionadas);
                      $totalGruposBloque = $sedesFiltradas->sum('grupos_activos_count');
                  @endphp
                  <h6 class="accordion-header border-bottom" id="headingBloque{{ $bloque->id }}" >
                      <button class="accordion-button collapsed d-flex justify-content-between align-items-center" style="background-color: transparent !important;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBloque{{ $bloque->id }}" aria-expanded="false" aria-controls="collapseBloque{{ $bloque->id }}">
                          <div class="d-flex flex-column text-start">
                              <span class="fs-5 fw-semibold text-uppercase text-black">{{ $bloque->nombre }}</span>
                              <small class="text-black fw-light text-black">Total grupos: {{ $totalGruposBloque }}</small>
                          </div>
                          <i class="ti ti-chevron-down fs-4 text-black accordion-icon"></i>
                      </button>
                  </h6>
                  <div id="collapseBloque{{ $bloque->id }}" class="accordion-collapse collapse" aria-labelledby="headingBloque{{ $bloque->id }}" data-bs-parent="#accordionBloques">
                      <div class="accordion-body">

                          @if($sedesFiltradas->count() > 0)
                              <!-- Lista de Sedes Apiladas -->
                              <div class="mt-3 ps-2 pe-2">
                                  @foreach($sedesFiltradas as $sede)
                                      <div class="mb-4 card pb-3 border m-2 rounded-3">
                                          <div class="d-flex w-100 justify-content-between align-items-center mb-2 p-3 py-4" style="background-color:#F9F9F9!important">
                                              <span class="fw-semibold fs-6 text-black">{{ $sede->nombre }}</span>
                                              <span class="fw-semibold fs-6 text-black">{{ $sede->grupos_activos_count }} Grupos</span>
                                          </div>
                                          <div>
                                              <div id="chartSede{{ $sede->id }}"></div>
                                                  <script>
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        const rangos{{ $sede->id }} = @json(array_column($sede->datos_grafica ?? [], 'label_rango'));

                                                        const options{{ $sede->id }} = {
                                                            series: [{
                                                                name: 'Realizados',
                                                                data: @json(array_column($sede->datos_grafica ?? [], 'realizados'))
                                                            }, {
                                                                name: 'No realizados',
                                                                data: @json(array_column($sede->datos_grafica ?? [], 'no_realizados'))
                                                            }, {
                                                                name: 'No reportados',
                                                                data: @json(array_column($sede->datos_grafica ?? [], 'no_reportados'))
                                                            }],
                                                            chart: {
                                                                type: 'bar',
                                                                height: 250,
                                                                stacked: true,
                                                                toolbar: { show: false }
                                                            },
                                                            plotOptions: {
                                                                bar: {
                                                                    horizontal: false,
                                                                    columnWidth: '50%',
                                                                    borderRadius: 4
                                                                },
                                                            },
                                                            dataLabels: { enabled: false },
                                                            stroke: { show: true, width: 2, colors: ['transparent'] },
                                                            xaxis: {
                                                                categories: @json(array_column($sede->datos_grafica ?? [], 'semana')),
                                                                labels: { style: { colors: '#6e6b7b', fontSize: '10px' } },
                                                                tooltip: {
                                                                    enabled: false // Desactivamos el tooltip default del eje X para manejarlo global
                                                                }
                                                            },
                                                            colors: ['#28c76f', '#ff9f43', '#ea5455'],
                                                            fill: { opacity: 1 },
                                                            legend: { position: 'bottom' },
                                                            tooltip: {
                                                                y: {
                                                                    formatter: function (val, { series, seriesIndex, dataPointIndex, w }) {
                                                                        let total = 0;
                                                                        w.globals.series.forEach(serie => {
                                                                            total += serie[dataPointIndex];
                                                                        });
                                                                        let percentage = total > 0 ? ((val / total) * 100).toFixed(0) : 0;
                                                                        return val + " grupos (" + percentage + "%)";
                                                                    }
                                                                },
                                                                x: {
                                                                    formatter: function(val, { dataPointIndex, w }) {
                                                                        return rangos{{ $sede->id }}[dataPointIndex];
                                                                    }
                                                                }
                                                            }
                                                        };

                                                        const chart{{ $sede->id }} = new ApexCharts(document.querySelector("#chartSede{{ $sede->id }}"), options{{ $sede->id }});
                                                        chart{{ $sede->id }}.render();
                                                    });
                                                  </script>
                                          </div>
                                      </div>
                                  @endforeach
                              </div>
                          @else
                              <p class="text-muted mb-0">No hay sedes seleccionadas para este bloque.</p>
                          @endif
                      </div>
                  </div>
              </div>
              @endif
          @endforeach
        </div>

      </div>

    </div>
  </div>

  <!-- TAB ASISTENCIAS -->
  <div class="tab-pane fade {{ $activeTab == 'asistencias' ? 'show active' : '' }}" id="navs-tab-asistencias" role="tabpanel">
      
    <div class="d-flex justify-content-end">
      <a href="{{ route('bloques-clasificacion') }}" class="btn btn-outline-primary rounded-pill btn-sm me-2">
        <i class="ti ti-settings me-1"></i> Bloques de clasificación
      </a>    
      
      <a href="{{ route('consolidacion.bloques') }}" class="btn btn-outline-primary rounded-pill btn-sm">
        <i class="ti ti-settings me-1"></i> Bloques de sedes 
      </a>
    </div>
  
    <div class="row g-4 py-5">
      @if(isset($bloquesEstadisticas) && count($bloquesEstadisticas) > 0)
          @foreach($bloquesEstadisticas as $bloque)
          <div class="col col-12 col-md-4 equal-height-col">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between">
                <div>
                  <h6 class="card-title text-uppercase mb-0 fw-light">{{ $bloque->nombre }}</h6>
                  <h3 class="card-title  mb-0 fw-semibold">{{ $bloque->valor }} {{ $bloque->tipo_calculo == 'promedio' ? '%' : '' }}</h3>
                </div>
              </div>
            </div>
          </div>
          @endforeach
      @else
          <div class="col-12 text-center">
             <div class="text-muted">
                 <i class="ti ti-info-circle fs-1 mb-3"></i>
                 <h5>No hay bloques de clasificación configurados.</h5>
             </div>
          </div>
      @endif
    </div>

    <hr class="my-5">

    <!-- Detalle por Bloques (Asistencias) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-muted fw-light mb-0">Total sedes: <span class="text-black fw-semibold">{{ count($sedesSeleccionadas) }}</span></h5>
    </div>

    <div class="accordion" id="accordionBloquesAsistencia">
      @foreach($bloques as $bloque)
          @if(in_array($bloque->id, $bloquesSeleccionados))
          <div class="accordion-item mb-3 border-0 shadow-none" style="background-color: transparent !important;">
              <h6 class="accordion-header border-bottom" id="headingBloqueAsis{{ $bloque->id }}" >
                  <button class="accordion-button collapsed d-flex justify-content-between align-items-center" style="background-color: transparent !important;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBloqueAsis{{ $bloque->id }}" aria-expanded="false" aria-controls="collapseBloqueAsis{{ $bloque->id }}">
                      <div class="d-flex flex-column text-start">
                          <span class="fs-5 fw-semibold text-uppercase text-black">{{ $bloque->nombre }}</span>
                      </div>
                      <i class="ti ti-chevron-down fs-4 text-black accordion-icon"></i>
                  </button>
              </h6>
              <div id="collapseBloqueAsis{{ $bloque->id }}" class="accordion-collapse collapse" aria-labelledby="headingBloqueAsis{{ $bloque->id }}" data-bs-parent="#accordionBloquesAsistencia">
                  <div class="accordion-body">

                      @php
                          $sedesFiltradas = $bloque->sedes->whereIn('id', $sedesSeleccionadas);
                      @endphp

                      @if($sedesFiltradas->count() > 0)
                          <!-- Lista de Sedes -->
                          <div class="mt-3 ps-2 pe-2">
                              @foreach($sedesFiltradas as $sede)
                                  <div class="mb-4 card pb-3 border m-2 rounded-3">
                                      <div class="d-flex w-100 justify-content-between align-items-center mb-2 p-3 py-4" style="background-color:#F9F9F9!important">
                                          <span class="fw-semibold fs-6 text-black">{{ $sede->nombre }}</span>
                                      </div>
                                      <div class="p-3">
                                          <div class="row g-3">
                                              @if(isset($sede->estadisticas_asistencia))
                                                  @foreach($sede->estadisticas_asistencia as $stat)
                                                      <div class="col-12 col-md-4 col-lg-3">
                                                          <div class="card bg-white h-100 ">
                                                              <div class="card-body p-3 ">
                                                                  <h6 class="text-black fw-normal text-uppercase mb-1">{{ $stat->nombre }}</h6>
                                                                  <h5 class="text-black mb-0 fw-semibold">{{ $stat->valor }} @if($stat->tipo_calculo == 'promedio') % @endif</h5>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  @endforeach
                                              @else
                                                  <div class="col-12">
                                                      <p class="text-muted">Sin datos de asistencia calculados.</p>
                                                  </div>
                                              @endif
                                          </div>
                                      </div>
                                  </div>
                              @endforeach
                          </div>
                      @else
                          <p class="text-muted mb-0">No hay sedes seleccionadas para este bloque.</p>
                      @endif
                  </div>
              </div>
          </div>
          @endif
      @endforeach
    </div>
  </div>

</div>
@endsection
