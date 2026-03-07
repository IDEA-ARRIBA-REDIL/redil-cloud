@php
$configData = Helper::appClasses();

use Carbon\Carbon;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Perfil del grupo')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js'
])
@endsection

@section('page-script')

<script type="module">
  $(function() {
    //esta bandera impide que entre en un bucle cuando se ejecuta la funcion cb(start, end)
    let band=0;
    moment.locale('es');

    function cb(start, end) {

      $('#filtroFechaIni').val(start.format('YYYY-MM-DD'));
      $('#filtroFechaFin').val(end.format('YYYY-MM-DD'));

      $('#filtroFechas span').html(start.format('YYYY-MM-DD') + ' hasta ' + end.format('YYYY-MM-DD'));

      if(band==1)
      $("#filtro").submit();
      band=1;
    }

    //comprobamos si existe la fecha incio y fecha fin y creamos las fechas con el formato aceptado
    @if(isset($filtroFechaIni))
      var fecha_ini = moment('{{$filtroFechaIni}}');
      fecha_ini.format("YYYY-MM-DD");
    @endif

    @if(isset($filtroFechaFin))
      var fecha_fin = moment('{{$filtroFechaFin}}');
      fecha_fin.format("YYYY-MM-DD");
    @endif

    @if(isset($filtroFechaIni) && isset($filtroFechaFin))
      cb(fecha_ini, fecha_fin);
    @else
      cb(moment().startOf('month'), moment().endOf('month'));
    @endif

    $('#filtroFechas').daterangepicker({
        ranges: {
          'Mes actual': [moment().startOf('month'), moment().endOf('month')],
          'Últimos 30 días': [ moment().subtract(30, 'days') , moment()],
          'Últimos 90 días': [ moment().subtract(90, 'days') , moment()],
          'Últimos 180 días': [ moment().subtract(120, 'days') , moment()],
        },
        "locale": {
          "format": "YYYY-MM-DD",
          "separator": " hasta ",
          "applyLabel": "Aplicar",
          "cancelLabel": "Cancelar",
          "fromLabel": "Desde",
          "toLabel": "Hasta",
          "customRangeLabel": "Otro rango",
          "monthNames": JSON.parse(<?php print json_encode(json_encode($meses)); ?>),
          "firstDay": 1
        },
        @if(isset($filtroFechaIni))
        "startDate": fecha_ini,
        @endif
        @if(isset($filtroFechaIni))
        "endDate": fecha_fin,
        @endif
        showDropdowns: true
      }, cb);
  });
</script>


<script type="module">
  let cardColor, headingColor, labelColor, borderColor, legendColor;

  if (isDarkStyle) {
    cardColor = config.colors_dark.cardColor;
    headingColor = config.colors_dark.headingColor;
    labelColor = config.colors_dark.textMuted;
    legendColor = config.colors_dark.bodyColor;
    borderColor = config.colors_dark.borderColor;
  } else {
    cardColor = config.colors.cardColor;
    headingColor = config.colors.headingColor;
    labelColor = config.colors.textMuted;
    legendColor = config.colors.bodyColor;
    borderColor = config.colors.borderColor;
  }

  const chartColors = {
    column: {
      series1: '#826af9',
      series2: '#d2b0ff',
      bg: '#f8d3f9'
    },
    donut: {
      series1: '#fee802',
      series2: '#3fd0bd',
      series3: '#826bf8',
      series4: '#2b9bf4',
      series5: '#f56954',
      series6: '#d2b0ff',
      series7: '#00a65a"',
      series9: '#f56900',
      series10: '#d2b050',

    },
    area: {
      series1: '#29dac7',
      series2: '#60f2ca',
      series3: '#a5f8cd'
    },
    sexo: {
      series1: '#2b9bf4',
      series2: '#826bf8'
    }
  };

  // grafico ultimos reportes
  const graficoUltimosReportes = document.querySelector('#graficoUltimosReportes'),
    dataGraficoAsistenciaReportes = JSON.parse(<?php print json_encode(json_encode($dataGraficoAsistenciaReportes)); ?>),
    graficoUltimosReportesConfig = {
      chart: {
        height: 300,
        type: 'area',
        parentHeightOffset: 0,
        toolbar: {
          show: true,
          offsetX: -20,
          offsetY: 0,
          tools: {
            download: true,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: false,
            reset: true | '<img src="/static/icons/reset.png" width="20">',
            customIcons: []
          },
          export: {
            svg: {
              filename: 'Gráfico_reportes_{{$grupo->nombre}}',
            },
            csv: {
              filename: 'Gráfico_reportes_{{$grupo->nombre}}',
            },
            png: {
              filename: 'Gráfico_reportes_{{$grupo->nombre}}',
            }
          },
        },
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false,
        curve: 'straight'
      },
      legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'start',
        labels: {
          colors: legendColor,
          useSeriesColors: false
        }
      },
      grid: {
        borderColor: borderColor,
        xaxis: {
          lines: {
            show: true
          }
        }
      },
      colors: [chartColors.area.series1],
      series: [{
        name: 'Asistencias',
        data: dataGraficoAsistenciaReportes
      }, ],
      xaxis: {
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px'
          }
        }
      },
      yaxis: {
        min: 0,
        labels: {
          formatter: function(val) {
            return val.toFixed(0)
          },
          style: {
            colors: labelColor,
            fontSize: '13px'
          }
        }
      },
      fill: {
        opacity: 1,
        type: 'solid'
      },
      tooltip: {
        shared: false
      }
    };
  if (typeof graficoUltimosReportes !== undefined && graficoUltimosReportes !== null) {
    let areaChartUltimosReportes = new ApexCharts(graficoUltimosReportes, graficoUltimosReportesConfig);
    areaChartUltimosReportes.render();
  }
  // grafico ultimos reportes

  // grafico promedios de asistencia
  const graficoPromedioAsistenciaMensual = document.querySelector('#graficoPromedioAsistenciaMensual'),
    dataPromedioAsistenciaMensual = JSON.parse(<?php print json_encode(json_encode($dataUltimosMeses)); ?>),
    seriePromedioAsistenciaMensual = JSON.parse(<?php print json_encode(json_encode($serieUltimosMeses)); ?>),
    graficoPromedioAsistenciaMensualConfig = {
      series: [{
          name: 'Promedio asistencias',
          data: dataPromedioAsistenciaMensual
        }
      ],

      annotations: {
        yaxis: [{
            y: '{{$grupo->asistentes()->count()}}',
            borderColor: '#00E396',
            label: {
              borderColor: '#00E396',
              style: {
                color: '#fff',
                background: '#00E396',
              },
              text: 'Total integrantes: {{$grupo->asistentes()->count()}}',
            }
          }]
      },
      colors: [chartColors.donut.series4],
      chart: {
        type: 'bar',
        height: 300,
        toolbar: {
          show: true,
          offsetX: -20,
          offsetY: 0,
          tools: {
            download: true,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: false,
            reset: true | '<img src="/static/icons/reset.png" width="20">',
            customIcons: []
          },
          export: {
            svg: {
              filename: 'Gráfico_asistencias_{{$grupo->nombre}}',
            },
            csv: {
              filename: 'Gráfico_asistencias_{{$grupo->nombre}}',
            },
            png: {
              filename: 'Gráfico_asistencias_{{$grupo->nombre}}',
            }
          },
        },
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '55%',
          endingShape: 'rounded'
        },
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      xaxis: {
        categories: seriePromedioAsistenciaMensual,
      },
      fill: {
        opacity: 1
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return "" + val + " "
          }
        }
      },
    };
  if (typeof graficoPromedioAsistenciaMensual !== undefined && graficoPromedioAsistenciaMensual !== null) {
    let areaChartPromedioAsistenciaMensual = new ApexCharts(graficoPromedioAsistenciaMensual, graficoPromedioAsistenciaMensualConfig);
    areaChartPromedioAsistenciaMensual.render();
  }
  // grafico promedios de asistencia

  // Grafico por edades
  const rangoEdadesGrafico = document.querySelector('#rangoEdades'),
  seriesRangoEdades = JSON.parse(<?php print json_encode(json_encode($seriesRangoEdades)); ?>),
  labelsRangoEdades = JSON.parse(<?php print json_encode(json_encode($labelsRangoEdades)); ?>),
    rangoEdadesConfig = {
      chart: {
        height: 390,
        type: 'donut',
        toolbar: {
          show: true,
          offsetX: -20,
          offsetY: 0,
          tools: {
            download: true,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: false,
            reset: true | '<img src="/static/icons/reset.png" width="20">',
            customIcons: []
          },
          export: {
            svg: {
              filename: 'Gráfico_edades_{{$grupo->nombre}}',
            },
            csv: {
              filename: 'Gráfico_edades_{{$grupo->nombre}}',
            },
            png: {
              filename: 'Gráfico_edades_{{$grupo->nombre}}',
            }
          },
        },
      },
      labels: labelsRangoEdades,
      series: seriesRangoEdades,
      colors: [
        chartColors.donut.series1,
        chartColors.donut.series2,
        chartColors.donut.series3,
        chartColors.donut.series4,
        chartColors.donut.series5,
        chartColors.donut.series6,
        chartColors.donut.series7,
        chartColors.donut.series8,
        chartColors.donut.series9,
        chartColors.donut.series10,
      ],
      stroke: {
        show: false,
        curve: 'straight'
      },
      dataLabels: {
        enabled: true,
        formatter: function (val, opt) {
          return parseInt(val, 10) + '%';
        }
      },
      legend: {
        show: true,
        position: 'bottom',
        markers: { offsetX: -3 },
        itemMargin: {
          vertical: 3,
          horizontal: 10
        },
        labels: {
          colors: legendColor,
          useSeriesColors: false
        }
      },
      plotOptions: {
        pie: {
          donut: {
            labels: {
              show: true,
              name: {
                fontSize: '2rem',
                fontFamily: 'Public Sans'
              },
              value: {
                fontSize: '1.2rem',
                color: legendColor,
                fontFamily: 'Public Sans',
                formatter: function (val) {
                  return parseInt(val, 10) + '%';
                }
              }
            }
          }
        }
      },
      responsive: [
        {
          breakpoint: 992,
          options: {
            chart: {
              height: 380
            },
            legend: {
              position: 'bottom',
              labels: {
                colors: legendColor,
                useSeriesColors: false
              }
            }
          }
        },
        {
          breakpoint: 576,
          options: {
            chart: {
              height: 320
            },
            plotOptions: {
              pie: {
                donut: {
                  labels: {
                    show: true,
                    name: {
                      fontSize: '1.5rem'
                    },
                    value: {
                      fontSize: '1rem'
                    },
                    total: {
                      fontSize: '1.5rem'
                    }
                  }
                }
              }
            },
            legend: {
              position: 'bottom',
              labels: {
                colors: legendColor,
                useSeriesColors: false
              }
            }
          }
        },
        {
          breakpoint: 420,
          options: {
            chart: {
              height: 280
            },
            legend: {
              show: false
            }
          }
        },
        {
          breakpoint: 360,
          options: {
            chart: {
              height: 250
            },
            legend: {
              show: false
            }
          }
        }
      ]
    };
  if (typeof rangoEdadesGrafico !== undefined && rangoEdadesGrafico !== null) {
    const rangoEdades = new ApexCharts(rangoEdadesGrafico, rangoEdadesConfig);
    rangoEdades.render();
  }
  // Grafico por edades

  // Grafico por sexos
  const tiposSexosGrafico = document.querySelector('#tiposDeSexos'),
  seriesTiposSexos = JSON.parse(<?php print json_encode(json_encode($seriesTiposSexos)); ?>),
  labelsTiposSexos = JSON.parse(<?php print json_encode(json_encode($labelsTiposSexos)); ?>),
    tiposSexosConfig = {
      chart: {
        height: 390,
        type: 'donut',
        toolbar: {
          show: true,
          offsetX: -20,
          offsetY: 0,
          tools: {
            download: true,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: false,
            reset: true | '<img src="/static/icons/reset.png" width="20">',
            customIcons: []
          },
          export: {
            svg: {
              filename: 'Gráfico_sexo_{{$grupo->nombre}}',
            },
            csv: {
              filename: 'Gráfico_sexo_{{$grupo->nombre}}',
            },
            png: {
              filename: 'Gráfico_sexo_{{$grupo->nombre}}',
            }
          },
        },
      },
      labels: labelsTiposSexos,
      series: seriesTiposSexos,
      colors: [
        chartColors.sexo.series1,
        chartColors.sexo.series2,
      ],
      stroke: {
        show: false,
        curve: 'straight'
      },
      dataLabels: {
        enabled: true,
        formatter: function (val, opt) {
          return parseInt(val, 10) + '%';
        }
      },
      legend: {
        show: true,
        position: 'bottom',
        markers: { offsetX: -3 },
        itemMargin: {
          vertical: 3,
          horizontal: 10
        },
        labels: {
          colors: legendColor,
          useSeriesColors: false
        }
      },
      plotOptions: {
        pie: {
          donut: {
            labels: {
              show: true,
              name: {
                fontSize: '1.5rem',
                fontFamily: 'Public Sans'
              },
              value: {
                fontSize: '1.2rem',
                color: legendColor,
                fontFamily: 'Public Sans',
                formatter: function (val) {
                  return parseInt(val, 10) + '%';
                }
              }
            }
          }
        }
      },
      responsive: [
        {
          breakpoint: 992,
          options: {
            chart: {
              height: 380
            },
            legend: {
              position: 'bottom',
              labels: {
                colors: legendColor,
                useSeriesColors: false
              }
            }
          }
        },
        {
          breakpoint: 576,
          options: {
            chart: {
              height: 320
            },
            plotOptions: {
              pie: {
                donut: {
                  labels: {
                    show: true,
                    name: {
                      fontSize: '1.5rem'
                    },
                    value: {
                      fontSize: '1rem'
                    },
                    total: {
                      fontSize: '1.5rem'
                    }
                  }
                }
              }
            },
            legend: {
              position: 'bottom',
              labels: {
                colors: legendColor,
                useSeriesColors: false
              }
            }
          }
        },
        {
          breakpoint: 420,
          options: {
            chart: {
              height: 280
            },
            legend: {
              show: false
            }
          }
        },
        {
          breakpoint: 360,
          options: {
            chart: {
              height: 250
            },
            legend: {
              show: false
            }
          }
        }
      ]
    };
  if (typeof tiposSexosGrafico !== undefined && tiposSexosGrafico !== null) {
    const tiposSexos = new ApexCharts(tiposSexosGrafico, tiposSexosConfig);
    tiposSexos.render();
  }
  // Grafico por sexos
</script>

<script>
  function darBajaAlta(grupoId, tipo)
  {
    Livewire.dispatch('abrirModalBajaAlta', { grupoId: grupoId, tipo: tipo });
  }

  function eliminacion(grupoId)
  {
    Livewire.dispatch('confirmarEliminacion', { grupoId: grupoId });
  }
</script>

<script>
  $(document).ready(function() {
    $('.selectTiposDeGrupo').select2({
      placeholder: "Selecciona los tipos de grupo",
      allowClear: true
    });
  });
</script>

<script>
  $('#conCobertura').change(function() {
    $('#filtroPorTipoDeGrupo').prop('disabled', true);
    $('.selectTiposDeGrupo').val(null).trigger('change');

    if ($(this).val() == 1)
    {
      $('#filtroPorTipoDeGrupo').prop('disabled', false);
    }
  });
</script>

<script type="text/javascript">
  $('#formulario').submit(function(){
    $('.btnOk').attr('disabled','disabled');

    Swal.fire({
      title: "Espera un momento",
      text: "Ya estamos guardando...",
      icon: "info",
      showCancelButton: false,
      showConfirmButton: false,
      showDenyButton: false
    });
  });
</script>
@endsection

@section('content')

  @include('layouts.status-msn')

  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-6">
        <div class="user-profile-header-banner">
          <img src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/'.$grupo->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png')}}" alt="Banner image" class="rounded-top">
        </div>
        <div class="user-profile-header d-flex flex-column flex-md-row text-sm-start text-center mb-5">
          <div class="flex-shrink-0 mt-n2 mx-0 mx-auto">
            <div class="card rounded-pill icon-card text-center mb-0 mx-3 p-2" style="background-color: {{ $grupo->tipoGrupo->color }}">
              <div class="card-body text-white"> <i class="ti ti-users-group ti-xl"></i>
              </div>
            </div>
          </div>
          <div class="flex-grow-1 mt-3 mt-md-5">
            <div class="d-flex align-items-md-end align-items-md-start align-items-center justify-content-md-between justify-content-start mx-2 flex-md-row flex-column gap-4">
              <div class="user-profile-info">
                <h4 class="mb-0 mt-md-4 fw-bold">{{ $grupo->nombre }}</h4>
                <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-md-start justify-content-center gap-4 my-0">
                  <li class="list-inline-item d-flex gap-2 align-items-center">
                    <span class="fw-medium"> {{ $grupo->tipoGrupo->nombre }}</span>
                  </li>
                </ul>
              </div>
              <div class="d-flex mb-4">
                <div class="p-2 flex-grow-1 bd-highlight">
                </div>
                <div class="flex-shrink-1 ">
                  <div class="dropdown d-flex border rounded py-2 px-4 ">
                    <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false">Opciones <i class="ti ti-dots-vertical text-muted"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      @if($grupo->dado_baja == 0)
                        @if($rolActivo->hasPermissionTo('grupos.opcion_modificar_grupo'))
                          <li><a class="dropdown-item" href="{{ route('grupo.modificar', $grupo)}}">Modificar</a></li>
                        @endif

                        @if($rolActivo->hasPermissionTo('grupos.opcion_excluir_grupo'))
                          <form id="excluirGrupo" method="POST" action="{{ route('grupo.excluir', ['grupo' => $grupo]) }}">
                            @csrf
                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('excluirGrupo').submit();" >Excluir grupo</a></li>
                          </form>
                        @endif

                        @if($rolActivo->hasPermissionTo('grupos.opcion_dar_de_baja_alta_grupo'))
                          <li><a class="dropdown-item" href="javascript:void(0);" onclick="darBajaAlta('{{$grupo->id}}', 'baja')">Dar de baja</a></li>
                        @endif

                        @if($rolActivo->hasPermissionTo('grupos.opcion_eliminar_grupo'))
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="eliminacion('{{$grupo->id}}')">Eliminar</a></li>
                        @endif
                      @else
                        @if($rolActivo->hasPermissionTo('grupos.opcion_dar_de_baja_alta_grupo'))
                          <li><a class="dropdown-item" href="javascript:void(0);" onclick="darBajaAlta('{{$grupo->id}}', 'alta')">Dar de alta</a></li>
                        @endif
                      @endif
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Header -->

  @livewire('Grupos.modal-baja-alta-grupo')

  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12 ">
      <div class="nav-align-top">
        <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0 justify-content-center gap-2">
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasGrupo', $grupo) }}" class="tapControl nav-link waves-effect waves-light active"><i class='ti-xs ti ti-chart-bar me-1'></i> Estadísticas del grupo </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasCobertura', $grupo) }}" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-chart-dots-2 me-1'></i> Estadísticas cobertura </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil', $grupo) }}" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-info-square-rounded me-1'></i> Información básica</a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.integrantes', $grupo) }}" class="tapControl nav-link waves-effect waves-light"><i class='ti-xs ti ti-users me-1'></i> Integrantes </a></li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->

  <form id="formulario" class="forms-sample" method="GET" action="{{ route('grupo.perfil.estadisticasGrupo', $grupo) }}">
    <div id="div-filtros" class="row">
      <!-- Información principal -->
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-body">
            <div class="row mb-4">

              <!-- Cobertura -->
              <div class="mb-2 col-12 col-md-4 mb-3">
                <label for="conCobertura" class="form-label d-none">¿Con cobertura?</label>
                <select id="conCobertura" name="conCobertura" class="form-select">
                  <option value="0" {{ $filtroCobertura==0 ? 'selected' : '' }}>Estadísticas del grupo</option>
                  <option value="1" {{ $filtroCobertura==1 ? 'selected' : '' }}>Estadísticas de la cobertura</option>
                </select>
              </div>

              <!-- Por tipo de grupo -->
              <div id="divTiposDeGrupo" class="col-12 col-md-8 mb-3">
                <label for="filtroPorTipoDeGrupo" class="form-label d-none">Fitrar por tipo de grupo </label>
                <select id="filtroPorTipoDeGrupo" name="filtroPorTipoDeGrupo[]" {{ $filtroCobertura == 0 ? 'disabled' : '' }}  class="selectTiposDeGrupo form-select" multiple>
                  @foreach($tiposDeGrupo as $tipoGrupo)
                  <option value="{{ $tipoGrupo->id }}" {{ in_array($tipoGrupo->id, $filtroTipoGrupos) ? "selected" : "" }}>{{ $tipoGrupo->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <!-- Por tipo de grupo -->


              <!-- Por rango de fechas  -->
              <div class="col-12 col-md-4 mb-3">
                <label for="rangoDeFechas" class="form-label d-none">Rango de fechas</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                  <input type="text" id="filtroFechaIni" name="filtroFechaIni" value="{{ $filtroFechaIni }}" class="form-control d-none" placeholder="">
                  <input type="text" id="filtroFechaFin" name="filtroFechaFin" value="{{ $filtroFechaFin }}" class="form-control d-none" placeholder="">
                  <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
                </div>
              </div>

              <!-- Botón -->
              <div class="col-12 col-md-1 text-center">
                <button type="submit" class="btn rounded-pill btn-primary waves-effect waves-light btnOk">Ok</button>
              </div>

            </div>
          </div>
        </div>
      </div>
      <!-- Información principal /-->
    </div>
  </form>

  <div id="div-principal" class="row">

    <div class="col-12 col-md-4">

      <!-- Clasficaciones -->
      <div class="card mb-4">
        <div class="card-header align-items-center">
          <h6 class="card-title text-uppercase mb-0 fw-bold">Clasificaciones</h6>
          <small class="text-muted">
            Promedios entre <b>{{$filtroFechaIni}}</b> hasta <b>{{$filtroFechaFin}}</b>
          </small>
        </div>
        <div class="card-body pb-3">
          <div class="row mb-4 g-3">
            @if($clasificaciones->count()>0)
              @foreach ($clasificaciones as $clasificacion)
              <div class="col-12 col-sm-12">
                <div class="d-flex">
                  <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-category ti-28px"></i></span>
                  </div>
                  <div>
                    <small>{{ $clasificacion->nombre }}</small>
                    <h6 class="mb-0 text-nowrap">{{ $clasificacion->promedio }}</h6>
                  </div>
                </div>
              </div>
              @endforeach
            @else
              <div class="py-4 border rounded mt-2">
                <center>
                  <i class="ti ti-category ti-xl pb-1"></i>
                  <h6 class="text-center">
                    @if ($filtroCobertura==0)
                      El tipo de grupo <b>{{$grupo->tipoGrupo->nombre}}</b> no posee clasificaciones.</h6>
                    @else
                    Los tipos de grupos seleccionados no poseen clasificaciones.</h6>
                    @endif
                </center>
              </div>
            @endif
          </div>
        </div>
      </div>
      <!-- Clasificaciones /-->


      @if($filtroCobertura==0)
      <!-- taps de personas -->
      <div class="card mb-3">
        <div class="card-body p-0 mt-5">
          <div class="nav-align-top">
            <ul class="nav nav-tabs nav-fill rounded-0 timeline-indicator-advanced" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tipo-default" aria-controls="navs-tipo-default" aria-selected="true"><i class="ti {{ $tipoUsuarioDefault->icono }} ti-lg"></i></button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-personas-inactivas" aria-controls="navs-personas-inactivas" aria-selected="false"><i class="ti ti-user-exclamation ti-lg"></i></button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-matriculas-activas" aria-controls="navs-matriculas-activas" aria-selected="false"><i class="ti ti-school ti-lg"></i></button>
              </li>
            </ul>
            <div class="tab-content border-0  mx-1">
              <div class="tab-pane fade show active" id="navs-tipo-default" role="tabpanel">
                  @if($personasTipoDefault->count() > 0)
                    <small>Listado de <b>{{ $tipoUsuarioDefault->nombre_plural }}</b>:</small>
                    <ul class="list-unstyled mb-0 mt-2">
                      @foreach ($personasTipoDefault as $persona)
                      <li class="mb-1 mt-1 p-2 border rounded">
                        <div class="d-flex align-items-start">
                          <div class="d-flex align-items-start">
                            <div class="avatar me-2">
                              <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto }}" alt="Avatar" class="rounded-circle" />
                            </div>
                            <div class="me-2 ms-1">
                              <h6 class="mb-0">{{ $persona->nombre(3) }}</h6>
                              <small class="text-muted">Creado {!! $persona->diasCreacion !!} </small>
                            </div>
                          </div>

                          <div class="ms-auto my-auto pt-1">
                            @can('verPerfilUsuarioPolitica', [$persona, 'principal'])
                            <a href="{{ route('usuario.perfil', $persona) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                              <i class="ti ti-user-check me-2 ti-sm"></i></a>
                            @endcan
                          </div>
                        </div>
                      </li>
                      @endforeach
                    </ul>
                  @else
                  <div class="py-4 border rounded mt-2 px-2">
                    <center>
                      <i class="ti ti-{{ $tipoUsuarioDefault->icono}} ti-xl pb-1"></i>
                      <h6 class="text-center">
                        ¡Ups! ... no hay personas <b>{{ $tipoUsuarioDefault->nombre_plural}} </b> por aquí.</h6>
                    </center>
                  </div>
                  @endif
              </div>

              <div class="tab-pane fade" id="navs-personas-inactivas" role="tabpanel">
                @if($personasInactivas->count() > 0)
                  <small >Personas inactivas con más de <b>{{ $configuracion->tiempo_para_definir_inactivo_grupo }}</b> días:</small>
                  <ul class="list-unstyled mb-0 mt-2">
                    @foreach ($personasInactivas as $persona)
                    <li class="mb-1 mt-1 p-2  border rounded">
                      <div class="d-flex align-items-start">
                        <div class="d-flex align-items-start">
                          <div class="avatar me-2">
                            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto }}" alt="Avatar" class="rounded-circle" />
                          </div>
                          <div class="me-2 ms-1">
                            <h6 class="mb-0">{{ $persona->nombre(3) }}</h6>
                            <small class="text-muted">Inactivo {!!$persona->inactividad !!}</small>
                          </div>
                        </div>

                        <div class="ms-auto my-auto pt-1">
                          @can('verPerfilUsuarioPolitica', [$persona, 'principal'])
                          <a href="{{ route('usuario.perfil', $persona) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                            <i class="ti ti-user-check me-2 ti-sm"></i>
                          </a>
                          @endcan
                        </div>
                      </div>
                    </li>
                    @endforeach
                  </ul>
                @else
                <div class="py-4 border rounded mt-2 px-2">
                  <center>
                    <i class="ti ti-user-exclamation ti-xl pb-1"></i>
                    <h6 class="text-center">
                      ¡Ups! ... no hay personas inactivas por aquí.</h6>
                  </center>
                </div>
                @endif
              </div>

              <div class="tab-pane fade" id="navs-matriculas-activas" role="tabpanel">
                <div class="py-4 border rounded mt-2 px-2">
                  <center>
                    <i class="ti ti-school ti-xl pb-1 "></i>
                    <h6 class="text-center">
                      ¡Ups! ... no hay personas activas en escuelas en este momento.</h6>
                  </center>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ taps de personas  -->
      @elseif($filtroCobertura==1)
        <!-- taps de grupos -->
        <div class="card mb-3">
        <div class="card-body p-0 mt-5">
          <div class="nav-align-top">
            <ul class="nav nav-tabs nav-fill rounded-0 timeline-indicator-advanced" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tipos-grupo" aria-controls="navs-tipos-grupo" aria-selected="true"><i class="ti ti-atom-2 ti-lg"></i></button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-grupos-inactivos" aria-controls="navs-grupos-inactivos" aria-selected="false"><i class="ti ti-exclamation-circle ti-lg"></i></button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-matriculas-por-materia" aria-controls="navs-matriculas-por-materia" aria-selected="false"><i class="ti ti-school ti-lg"></i></button>
              </li>
            </ul>
            <div class="tab-content border-0  mx-1">
              <div class="tab-pane fade show active" id="navs-tipos-grupo" role="tabpanel">
                @if($indicadoresPortipoGrupo->count() > 0)
                  <small class="mb-3">Cantidad de grupos por tipo:</small>
                  <ul class="list-unstyled mb-0 mt-2">
                    @foreach($indicadoresPortipoGrupo as $indicador)
                    <li class="mb-1 mt-1 p-2 border rounded">
                      <form id="formIndicador{{$indicador->id}}" class="forms-sample" method="GET" action="{{ route('grupo.lista', $indicador->url) }}">
                        <input name="filtroGrupo" value="{{$grupo->id}}" class="d-none">
                        <div class="d-flex align-items-start">
                          <div class="d-flex align-items-start">
                            <div class="avatar flex-shrink-0 me-3">
                              <span class="avatar-initial rounded {{$indicador->color}}"><i class="ti ti-{{$indicador->icono}} ti-28px"></i></span>
                            </div>
                            <div class="me-2 ms-1">
                              <h6 class="mb-0">{{ $indicador->nombre }}</h6>
                              <small class="text-muted"> {{ $indicador->cantidad}}</small>
                            </div>
                          </div>

                          <div class="ms-auto my-auto ">
                            <a href="#" onclick="event.preventDefault(); document.getElementById('formIndicador{{$indicador->id}}').submit();" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver listado" data-bs-original-title="Ver listado">
                              <i class="ti ti-chevron-right me-2 ti-sm"></i>
                            </a>
                          </div>
                        </div>
                      </form>
                    </li>
                    @endforeach
                  </ul>
                @else
                  <div class="py-4 border rounded mt-2 px-2">
                    <center>
                      <i class="ti ti-atom-2 ti-xl pb-1"></i>
                      <h6 class="text-center">
                        ¡Ups! ... no hay grupos bajo tu cobertura.</h6>
                    </center>
                  </div>
                @endif
              </div>

              <div class="tab-pane fade" id="navs-grupos-inactivos" role="tabpanel">
                @if($gruposInactivos->count() > 0)
                  <small >Grupos inactivos:</small>
                  <ul class="list-unstyled mb-0 mt-2">
                    @foreach ($gruposInactivos as $grupoInactivo)
                    <li class="mb-1 mt-1 p-2  border rounded">
                      <div class="d-flex align-items-start">
                        <div class="d-flex align-items-start">
                          <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded {{$grupoInactivo->color}}"><i class="ti ti-{{$grupoInactivo->icono}} ti-28px"></i></span>
                          </div>
                          <div class="me-2 ms-1">
                            <h6 class="mb-0">{{ $grupoInactivo->nombre }}</h6>
                            <small class="text-muted">Inactivo {!!$grupoInactivo->inactividad !!}</small>
                          </div>
                        </div>

                        <div class="ms-auto my-auto pt-1">
                          @if($rolActivo->hasPermissionTo('grupos.opcion_ver_perfil_grupo'))
                          <a href="{{ route('grupo.perfil', $grupoInactivo) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                            <i class="ti ti-chevron-right me-2 ti-sm"></i></a>
                          </a>
                          @endif
                        </div>
                      </div>
                    </li>
                    @endforeach
                  </ul>
                @else
                <div class="py-4 border rounded mt-2 px-2">
                  <center>
                    <i class="ti ti-exclamation-circle ti-xl pb-1"></i>
                    <h6 class="text-center">
                      ¡Ups! ... no hay grupos inactivos por aquí.</h6>
                  </center>
                </div>
                @endif
              </div>

              <div class="tab-pane fade" id="navs-matriculas-por-materia" role="tabpanel">
                <div class="py-4 border rounded mt-2 px-2">
                  <center>
                    <i class="ti ti-school ti-xl pb-1"></i>
                    <h6 class="text-center">
                      ¡Ups! ... no hay personas activas en escuelas en este momento.</h6>
                  </center>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ taps de grupos  -->
      @endif

    </div>

    <div class="col-12 col-md-8">







<!-- Earning Reports Tabs-->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title m-0">
          <h5 class="mb-1">Earning Reports</h5>
          <p class="card-subtitle">Yearly Earnings Overview</p>
        </div>
      </div>
      <div class="card-body">
        <ul class="nav nav-tabs widget-nav-tabs pb-8 gap-4 mx-1 d-flex flex-nowrap" role="tablist">
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link btn active d-flex flex-column align-items-center justify-content-center" role="tab" data-bs-toggle="tab" data-bs-target="#navs-orders-id" aria-controls="navs-orders-id" aria-selected="true">
              <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-chart-dots-2 ti-md"></i></div>
              <h6 class="tab-widget-title mb-0 mt-2">Crecimiento</h6>
            </a>
          </li>
          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab" data-bs-toggle="tab" data-bs-target="#navs-sales-id2" aria-controls="navs-sales-id2" aria-selected="false">
              <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-chart-bar ti-md"></i></div>
              <h6 class="tab-widget-title mb-0 mt-2"> Asistencia</h6>
            </a>
          </li>

          <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab" data-bs-toggle="tab" data-bs-target="#navs-sales-id3" aria-controls="navs-sales-id3" aria-selected="false">
              <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-chart-bar ti-md"></i></div>
              <h6 class="tab-widget-title mb-0 mt-2"> Clasificación 1</h6>
            </a>
          </li>
        </ul>
        <div class="tab-content p-0 ms-0 ms-sm-2">
          <div class="tab-pane fade show active" id="navs-orders-id" role="tabpanel">
          <div>
            <h6 class="card-title text-uppercase mb-0 fw-bold">Title</h6>
            <small class="text-muted">
              Subtitle
            </small>
          </div>
            <div id="graficoPromedioAsistenciaMensual"></div>
          </div>
          <div class="tab-pane fade" id="navs-sales-id" role="tabpanel">
            <div id="earningReportsTabsSales"></div>
          </div>

          <div class="tab-pane fade" id="navs-sales-id2" role="tabpanel">
            Muy pronto estadistica...
          </div>

          <div class="tab-pane fade" id="navs-sales-id3" role="tabpanel">
          Muy pronto estadistica...
          </div>
        </div>
      </div>
    </div>

















      <!-- Grafico de asistencia a grupo -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title text-uppercase mb-0 fw-bold">Gráfico asistencias según reportes</h6>
            <small class="text-muted">
              Asistencias de los últimos {{ $ultimosReportes->total() }} reportes
            </small>
          </div>

          <div class="card-title-elements ms-auto">
            <select id="periodoGraficoAsistenciaReportes" name="periodoGraficoAsistenciaReportes" class="form-select form-select-sm w-auto" onchange="event.preventDefault(); document.getElementById('formEstadisticasGrupo').submit();">
              <option value="1" {{ $selectPeriodoGraficoAsistenciaReportes == 1 ? 'selected' : ''}} >1 mes</option>
              <option value="3" {{ $selectPeriodoGraficoAsistenciaReportes == 3 ? 'selected' : ''}} >3 meses</option>
              <option value="6" {{ $selectPeriodoGraficoAsistenciaReportes == 6 ? 'selected' : ''}} >6 meses</option>
              <option value="12" {{ $selectPeriodoGraficoAsistenciaReportes == 12 ? 'selected' : ''}} >12 meses</option>
            </select>
          </div>
        </div>
        <div class="card-body p-0">
          @if($ultimosReportes->count()>0)
          <center>
            <small class="text-muted">
              Promedio de asistencias: <b> {{ round($sumaTotalAsistenciasReportes/$ultimosReportes->total(),2) }}</b>
            </small>
          </center>
          @endif
          <div id="graficoUltimosReportes"></div>
        </div>
        <div class="card-fooder">
          <div class="row p-4">
            <ul class="list-unstyled ">
              @if($ultimosReportes->count() > 0)
                @foreach($ultimosReportes as $reporte)
                <li class="mb-1 mt-1 p-2 border rounded">
                  <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                          <i class="ti ti-clipboard-data me-2 fs-2"></i>
                        </div>
                    <div class="d-flex align-items-start">
                      <div class="me-2 ms-1">
                        <h6 class="mb-0">{{ $reporte->tema }}</h6>
                        <small class=""><i class="ti ti-calendar text-heading fs-6"></i> {{ $reporte->fecha }}</small>
                        <small class=""><i class="ti ti-users text-heading fs-6"></i> Asistencias:  {{ $reporte->cantidad_asistencias }}</small>
                      </div>
                    </div>

                    <div class="ms-auto pt-1 my-auto">
                      @if($rolActivo->hasPermissionTo('reportes_grupos.opcion_ver_perfil_reporte_grupo'))
                      <a href="" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                        <i class="ti ti-user-check me-2 ti-sm"></i></a>
                      </a>
                      @endif
                      <a href="" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                        <i class="ti ti-corner-down-right me-2 ti-sm"></i></a>
                      </a>
                    </div>
                  </div>
                </li>
                @endforeach
              @else
                <div class="py-4 border rounded mt-2">
                  <center>
                    <i class="ti ti-clipboard-data ti-xl pb-1"></i>
                    <h6 class="text-center">¡Ups! este grupo aun no tiene reportes.</h6>
                    @if($rolActivo->hasPermissionTo('grupos.pestana_anadir_lideres_grupo'))
                    <a href="" target="_blank" class="btn btn-primary pendiente" data-bs-toggle="tooltip" aria-label="Crear reporte" data-bs-original-title="Este grupo no tiene encargados, agrégalos aquí">
                      <i class="ti ti-clipboard-data me-2 ti-sm"></i> Crear reporte
                    </a>
                    @endif
                  </center>
                </div>
              @endif
            </ul>

            @if($ultimosReportes->count()>0)
            <div class="mt-0">
                {!! $ultimosReportes->appends(request()->input())->links() !!} <p class="m-1"> {{$ultimosReportes->lastItem()}} <b>de</b> {{$ultimosReportes->total()}} <b> Reportes - Página</b> {{ $ultimosReportes->currentPage() }} </p>

            </div>
            @endif
          </div>
        </div>
      </div>
      <!-- /Grafico de asistencia a grupo -->

      <!-- Grafico de promedios de asistencia  -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title text-uppercase mb-0 fw-bold">Promedios de asistencia mensual</h6>
            <small class="text-muted">
              Promedios de asistencia de los últimos 6 meses
            </small>
          </div>
        </div>
        <div class="card-body">

        </div>
      </div>
      <!-- /Grafico de promedios de asistencia  -->

      <!-- Grafico por edades -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title text-uppercase mb-0 fw-bold">Personas del grupo por rango de edad</h6>
            <small class="text-muted">
              Esta gráfica muestra la cantidad de personas clasificándola según su edad.
            </small>
          </div>
        </div>
        <div class="card-body">
          <div id="rangoEdades"></div>

          <div class="table-responsive text-nowrap mt-3">
            <table class="table">
              <thead>
                <tr>
                  <th class="fw-bold text-center">Nombre</th>
                  <th class="fw-bold text-center">Rango</th>
                  <th class="fw-bold text-center">Cantidad</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                @foreach ($rangoEdades as $rangoEdad)
                <tr>
                  <td class="text-center">{{ $rangoEdad->nombre }}</td>
                  <td class="text-center">{{ $rangoEdad->edad_minima }} a {{ $rangoEdad->edad_maxima }}</td>
                  <td class="text-center">{{ $rangoEdad->cantidad }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

        </div>
      </div>
      <!-- /Grafico por edades -->

      <!-- Grafico por sexo -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title text-uppercase mb-0 fw-bold">Personas del grupo por sexo</h6>
            <small class="text-muted">
              Esta gráfica muestra la cantidad de personas clasificándola según su sexo.
            </small>
          </div>
        </div>
        <div class="card-body">
          <div id="tiposDeSexos"></div>

          <div class="table-responsive text-nowrap mt-3">
            <table class="table">
              <thead>
                <tr>
                <th class="fw-bold text-center">Nombre</th>
                  <th class="fw-bold text-center">Cantidad</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                @foreach ($tiposDeSexo as $tipoSexo)
                <tr>
                  <td class="text-center">{{ $tipoSexo->nombre }}</td>
                  <td class="text-center">{{ $tipoSexo->cantidad }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

        </div>
      </div>
      <!-- /Grafico por sexo -->
    </div>
  </div>
@endsection
