@php
$configData = Helper::appClasses();

use Carbon\Carbon;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Perfil del grupo')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
])
@endsection

@section('page-script')

@if($grupos && $grupos->count() > 0)
<script>
  // Nos aseguramos de que el DOM esté completamente cargado antes de ejecutar el script
  document.addEventListener('DOMContentLoaded', function () {
    // 1. Obtenemos el elemento <select> por su ID
    const grupoSelect = document.getElementById('grupoSeleccionado');

    // 2. Añadimos un "oyente" para el evento 'change'
    grupoSelect.addEventListener('change', function () {
      const nuevoGrupoId = this.value;
      let urlDestino = "{{ route('grupo.perfil.estadisticasGrupo', ['grupo' => 'ID_REEMPLAZABLE', 'encargado' => $encargado->id ?? null]) }}";
      urlDestino = urlDestino.replace('ID_REEMPLAZABLE', nuevoGrupoId);
      window.location.href = urlDestino;
    });
  });
</script>
@endif

<!-- foto portada -->
<script type="module">

  $(function () {
    'use strict';

    var croppingImagePortada = document.querySelector('#croppingImagePortada'),
      cropBtnPortada = document.querySelector('#cropSubmitPortada'),
      upload = document.querySelector('#cropperImageUploadPortada'),
      inputResultadoPortada = document.querySelector('#imagen-recortada-portada'),
      formularioPortada  =document.querySelector('#formularioPortada'),
      cropper = '';

    setTimeout(() => {
      cropper = new Cropper( croppingImagePortada, {
        zoomable: false,
        aspectRatio: 1693 / 376,
        cropBoxResizable: true
      });
    }, 1000);

    // on change show image with crop options
    upload.addEventListener('change', function (e) {
      if (e.target.files.length) {
        console.log(e.target.files[0]);
        var fileType = e.target.files[0].type;
        if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png') {
          cropper.destroy();
          // start file reader
          const reader = new FileReader();
          reader.onload = function (e) {
            if (e.target.result) {
              croppingImagePortada.src = e.target.result;
              cropper = new Cropper(croppingImagePortada, {
                zoomable: false,
                aspectRatio: 1693 / 376,
                cropBoxResizable: true
              });
            }
          };
          reader.readAsDataURL(e.target.files[0]);
        } else {
          alert('Selected file type is not supported. Please try again');
        }
      }
    });

    // crop on click
    cropBtnPortada.addEventListener('click', function (e) {
      e.preventDefault();

      // get result to data uri
      let imgSrc = cropper
        .getCroppedCanvas({
          height: 376,
          width: 1693 // input value
        })
        .toDataURL();

      inputResultadoPortada.value = imgSrc;
      cropBtnPortada.disabled = true;
      formularioPortada.submit();
    });
  });

</script>
<!-- foto portada -->

<script type="module">

  let cardColor, labelColor, headingColor, shadeColor, legendColor, borderColor, barBgColor;
  if (isDarkStyle) {
    cardColor = config.colors_dark.cardColor;
    labelColor = config.colors_dark.textMuted;
    legendColor = config.colors_dark.bodyColor;
    borderColor = config.colors_dark.borderColor;
    headingColor = config.colors_dark.headingColor;
    barBgColor = '#3d4157';
    shadeColor = 'dark';
  } else {
    cardColor = config.colors.cardColor;
    labelColor = config.colors.textMuted;
    legendColor = config.colors.bodyColor;
    borderColor = config.colors.borderColor;
    headingColor = config.colors.headingColor;
    barBgColor = '#efeef0';
    shadeColor = '';
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


  // Graficas dinamicas tab
  function GraficasDinamicasTab(datosArray, highlightData, categoriasArray)
  {
    const basicColor = config.colors.primary,
    highlightColor = config.colors.primary;
    var colorArr = [];
    var total = 0;
    var showEjeY = false;

    for (let i = 0; i < datosArray.length; i++) {
      if (i === highlightData) {
        colorArr.push(highlightColor);
      } else {
        colorArr.push(basicColor);
      }
    }

    $.each(datosArray, function(index, value){
      total += value;
    });

    if(total > 0)
    showEjeY = true;

    const graficaBarChartOpt = {
      chart: {
        height: 231,
        parentHeightOffset: 0,
        type: 'bar',
        toolbar: {
          show: true,
          offsetX: 1,
          offsetY: -20,
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
        }
      },
      plotOptions: {
        bar: {
          columnWidth: '32%',
          startingShape: 'rounded',
          borderRadius: 6,
          distributed: true,
          dataLabels: {
            position: 'top'
          }
        }
      },
      grid: {
        show: false,
        padding: {
          top: 0,
          bottom: 0,
          left: -10,
          right: -10
        }
      },
      colors: colorArr,
      dataLabels: {
        enabled: true,
        formatter: function (val) {
          return val;
        },
        offsetY: -30,
        style: {
          fontSize: '15px',
          colors: [headingColor],
          fontWeight: '500',
          fontFamily: 'Public Sans'
        }
      },
      series: [
        {
          data: datosArray
        }
      ],
      legend: {
        show: false
      },
      tooltip: {
        enabled: false
      },
      xaxis: {
        categories: categoriasArray,
        axisBorder: {
          show: true,
          color: borderColor
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px',
            fontFamily: 'Public Sans'
          }
        }
      },
      yaxis: {
        show: showEjeY,
        labels: {
          offsetX: -15,
          formatter: function (val) {
            return val;
          },
          style: {
            fontSize: '13px',
            colors: labelColor,
            fontFamily: 'Public Sans'
          },
          min: 0,
          max: 60000,
          tickAmount: 6
        }
      },
      responsive: [
        {
          breakpoint: 1441,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '41%'
              }
            }
          }
        },
        {
          breakpoint: 590,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '61%',
                borderRadius: 5
              }
            },
            yaxis: {
              labels: {
                show: false
              }
            },
            grid: {
              padding: {
                right: 0,
                left: -20
              }
            },
            dataLabels: {
              style: {
                fontSize: '12px',
                fontWeight: '400'
              }
            }
          }
        }
      ]
    };
    return graficaBarChartOpt;
  }

  var graficasTab = @json($graficasTab);

  $.each(graficasTab, function(key, grafica) {
    const graficaDinamicaEl = document.querySelector('#'+grafica.id),
      graficaDinamicaConfig = GraficasDinamicasTab(
        grafica.datos,
        5,
        grafica.categorias,
      );
    if (typeof graficaDinamicaEl !== undefined && graficaDinamicaEl !== null) {
      const graficaDinamica = new ApexCharts(graficaDinamicaEl, graficaDinamicaConfig);
      graficaDinamica.render();
    }
  });



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
          return parseInt(val, 10) + '';
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
                  return parseInt(val, 10) + ' Persona(s)';
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
                  return parseInt(val, 10) + ' Persona(s)';
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

<script type="text/javascript">
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
  $(".btnModalNuevoReporte").click(function() {
    fechaAutomatica = $(this).data('fecha-automatica');
    grupoId = $(this).data('id');
    Livewire.dispatch('abrirModalNuevoReporte', { fechaAutomatica: fechaAutomatica, grupoId: grupoId });
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

<script type="text/javascript">
  $('#rango').change(function() {
    $('#divSemanaIni').addClass('d-none');
    $('#divSemanaFin').addClass('d-none');
    $('#divMesIni').addClass('d-none');
    $('#divMesFin').addClass('d-none');
    $('#divRango').addClass('col-md-10');

    $('#semanaIni').removeAttr('required');
    $('#semanaFin').removeAttr('required');

    $('#mesIni').removeAttr('required');
    $('#mesFin').removeAttr('required');


    if ($(this).val() == 'otroSemanas')
    {
      $('#divSemanaIni').removeClass('d-none');
      $('#divSemanaFin').removeClass('d-none');
      $('#divRango').removeClass('col-md-10');
      $('#divRango').addClass('col-md-4');

      $('#semanaIni').attr('required', 'required');
      $('#semanaFin').attr('required', 'required');
    }else if ($(this).val() == 'otroMeses'){
      $('#divMesIni').removeClass('d-none');
      $('#divMesFin').removeClass('d-none');
      $('#divRango').removeClass('col-md-10');
      $('#divRango').addClass('col-md-4');

      $('#mesIni').attr('required', 'required');
      $('#mesFin').attr('required', 'required');
    }
  });
</script>


<script>
  $(document).ready(function() {
    setTimeout(function(){
      $([document.documentElement, document.body]).animate({scrollTop: $('#divBotonOpciones').offset().top}, 200, "linear")
    },300);

    $(document).ready(function(){
      $('#selectorGraficoTab').change(function(){
        var selectedTab = $(this).val();

        $('#tab-dinamico .tab-pane').removeClass('active');
        $('#navs-'+selectedTab+'-id').addClass('active');

      });
    });

  });
</script>
@endsection

@section('content')

  @include('layouts.status-msn')

  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-5">
        <div class="user-profile-header-banner ">
          <img src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/'.$grupo->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png')}}" alt="Banner image" class="rounded-top">
          @if($rolActivo->hasPermissionTo('grupos.opcion_modificar_grupo'))
          <button type="button" style="background-color: rgba(255, 255, 255, 0.5);" class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-1 end-0 mt-3 mx-6 text-white p-2" data-bs-toggle="modal" data-bs-target="#modalPortada">Cambiar portada <i style="padding-left: 5px;" class="ti ti-camera"></i></button>
          @endif
        </div>
        <div class="user-profile-header d-flex flex-column flex-md-row text-md-start text-center mb-8 mx-5">

          <div class="flex-shrink-0 mt-n2 mx-0 mx-auto">
            <div class="card rounded-pill icon-card text-center mb-0 mx-3 p-2" style="background-color: {{ $grupo->tipoGrupo->color }}">
              <div class="card-body text-white"> <i class="ti ti-users-group ti-xl"></i>
              </div>
            </div>
          </div>

          <div class="flex-grow-1 mt-3 mt-md-5">
            <div class="d-flex align-items-md-end align-items-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
              <div class="user-profile-info">
                @if($grupos && $grupos->count() > 0)
                  <select id="grupoSeleccionado" name="grupoSeleccionado" class="form-select mt-md-7">
                    @foreach ($grupos as $gr)
                    <option value="{{ $gr->id }}" {{ $gr->id == $grupo->id ? 'selected' : ''}} >{{ $gr->nombre }}</option>
                    @endforeach
                  </select>
                @else
                <h4 class="mb-0 mt-md-4 fw-bold">{{ $grupo->nombre }}</h4>
                @endif
                <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-md-start justify-content-center gap-4 my-0 mt-1">
                  <li class="list-inline-item d-flex gap-2 align-items-center">
                    <span class="fw-medium"> {{ $grupo->tipoGrupo->nombre }}</span>
                  </li>
                </ul>
              </div>
              <div id="divBotonOpciones" class="d-flex flex-row ">

                <div class="me-2">
                  @if($grupo->varificarProcesoReporte() == 'botonCrearDeshabilitado' || $grupo->varificarProcesoReporte() == 'botonEditarDeshabilitado')
                    @if($grupo->varificarProcesoReporte() == 'botonCrearDeshabilitado')
                    <button disabled class=" btn btn-sm rounded-3 w-100 btn-primary waves-effect waves-light mx-1 p-2 fw-light" >Crear reporte </button>
                    @elseif ($grupo->varificarProcesoReporte() == 'botonEditarDeshabilitado')
                    <button disabled class=" btn btn-sm rounded-3 w-100 btn-primary waves-effect waves-light mx-1 p-2 fw-light">Editar reporte </button>
                    @endif
                  @else
                    @if($grupo->varificarProcesoReporte() == 'botonCrearReporte')
                    <button data-id="{{ $grupo->id }}" data-fecha-automatica="{{ $grupo->verificaFechaAutomaticaReporte() }}" class="btnModalNuevoReporte btn btn-sm rounded-3 w-100 btn-primary waves-effect waves-light mx-1 p-2 fw-light" >Crear reporte </button>
                    @elseif ($grupo->varificarProcesoReporte() == 'botonEditarReporte')
                    <a href="{{ route('reporteGrupo.asistencia', $grupo->ultimoReporteDelGrupo()->id ) }}" class="btn btn-sm rounded-3 w-100 btn-primary waves-effect waves-light mx-1 p-2 fw-light">Editar reporte </a>
                    @endif
                  @endif
                </div>

                <div class="dropdown">
                  <button type="button" class="btn btn-sm p-2 rounded-3 btn-outline-primary waves-effectdropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="mx-1">Opciones</span>
                    <i class="pl-5 ti ti-edit"></i>
                  </button>
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
                      <hr class="dropdown-divider">
                      @if($rolActivo->hasPermissionTo('grupos.opcion_dar_de_baja_alta_grupo'))
                        <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="darBajaAlta('{{$grupo->id}}', 'baja')">Dar de baja</a></li>
                      @endif

                      @if($rolActivo->hasPermissionTo('grupos.opcion_eliminar_grupo'))
                      <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="eliminacion('{{$grupo->id}}')">Eliminar</a></li>
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
  <!--/ Header -->


  <!-- Navbar pills -->
  <div id="div-pentañas" class="row">
    <div class="col-md-12 ">
      <div class="nav-align-top">
        <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0 justify-content-center gap-2">
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasGrupo', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="nav-link waves-effect waves-light active"><i class='ti-xs ti ti-chart-bar me-1'></i> Estadísticas del grupo </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasCobertura', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="nav-link waves-effect waves-light"><i class='ti-xs ti ti-chart-dots-2 me-1'></i> Estadísticas cobertura </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="nav-link waves-effect waves-light"><i class='ti-xs ti ti-info-square-rounded me-1'></i> Información básica</a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.integrantes', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="nav-link waves-effect waves-light"><i class='ti-xs ti ti-users me-1'></i> Integrantes </a></li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->

  <form id="formulario" class="forms-sample" method="GET" action="{{ route('grupo.perfil.estadisticasGrupo', $grupo) }}">
    <div id="div-filtros" class="row">
      <!-- Información principal -->

      <div class="col-12">
        <div class="card mb-4 shadow">
          <div class="card-body">
            <div class="row mb-4">
              <!-- Rango -->
              <div id="divRango" class="mb-2 col-12 {{ !$request || $request->rango=='otroSemanas' || $request->rango=='otroMeses' ? 'col-md-4' : 'col-md-10' }} mb-3">
                <label for="rango" class="form-label">Selecciona el rango</label>
                <select id="rango" name="rango" class="form-select">
                  <option value="4s" {{ !$request|| $request->rango =='4s' ? 'selected' : '' }} >Últimas 4 semanas</option>
                  <option value="3m" {{ $request->rango =='3m' ? 'selected' : '' }} >Últimos 3 meses</option>
                  <option value="6m" {{ $request->rango =='6m' ? 'selected' : '' }} >Últimos 6 meses</option>
                  <option value="otroSemanas" {{ $request->rango =='otroSemanas' ? 'selected' : '' }}>Otro rango por semanas</option>
                  <option value="otroMeses" {{ $request->rango =='otroMeses' ? 'selected' : '' }}>Otro rango por meses</option>
                </select>
              </div>

              <!-- semana Inicial -->
              <div id="divSemanaIni" class="mb-2 col-12 col-md-3 mb-3 {{ !$request || $request->rango!='otroSemanas' ? 'd-none' : '' }}">
                <label for="semanaIni" class="form-label">Semana inicial</label>
                <input id="semanaIni" {{ $request->rango=='otroSemanas' ? 'required' : '' }} value="{{ $request->semanaIni }}" type="week" name="semanaIni" class="form-control">
              </div>

              <!-- semana Fin -->
              <div id="divSemanaFin" class="mb-2 col-12 col-md-3 mb-3 {{ !$request || $request->rango!='otroSemanas' ? 'd-none' : '' }}">
                <label for="semanaFin" class="form-label">Semana final</label>
                <input id="semanaFin" {{ $request->rango=='otroSemanas' ? 'required' : '' }} value="{{ $request->semanaFin }}" type="week" name="semanaFin"  class="form-control">
              </div>

              <!-- mes Inicial -->
              <div id="divMesIni" class="mb-2 col-12 col-md-3 mb-3 {{ !$request || $request->rango!='otroMeses' ? 'd-none' : '' }}">
                <label for="mesIni" d-none class="form-label">Mes inicial</label>
                <input id="mesIni" {{ $request->rango=='otroMeses' ? 'required' : '' }} value="{{ $request->mesIni }}" type="month" name="mesIni" class="form-control">
              </div>

              <!-- mes Fin -->
              <div id="divMesFin" class="mb-2 col-12 col-md-3 mb-3 {{ !$request || $request->rango!='otroMeses' ? 'd-none' : '' }}">
                <label for="mesFin" class="form-label">Mes final</label>
                <input id="mesFin" {{ $request->rango=='otroMeses' ? 'required' : '' }} value="{{ $request->mesFin }}" type="month" name="mesFin"  class="form-control">
              </div>

              <!-- Botón -->
              <div class="col-12 col-md-2 mb-3 d-flex align-items-end">
                <button type="submit" class="btn rounded-pill btn-primary waves-effect waves-light btnOk">Aceptar</button>
              </div>

            </div>
          </div>
        </div>
      </div>
      <!-- Información principal /-->
    </div>
  </form>

  <div id="div-principal" class="row">

    <div class="col-12 col-md-5">

      <!-- Clasficaciones -->
      <div class="bg-white row text-start rounded p-5 mb-4 mx-0">
        <div class="col-12 mb-5">
          <p class=" fw-semibold text-black mb-1"> Clasificaciones</p>
          <small class="text-black">
            Promedios de clasificaciones entre <br> <b>{{$fechaInicio}}</b> hasta <b>{{$fechaFin}}</b>
          </small>
        </div>
        @if($clasificaciones->count()>0)
          @foreach ($clasificaciones as $clasificacion)
          <div class="col-12 d-flex flex-column">
            <small class="text-black">{{ $clasificacion->nombre }}</small>
            <small class="fw-semibold text-black ">{{ $clasificacion->promedio }}</small>
            <hr class="my-3 border-2">
          </div>
          @endforeach
        @else
          <div class="py-4 border rounded mt-2">
            <center>
              <i class="ti ti-category ti-xl pb-1"></i>
              <h6 class="text-center">El tipo de grupo <b>{{$grupo->tipoGrupo->nombre}}</b> no posee clasificaciones.</h6>
            </center>
          </div>
        @endif
      </div>
      <!-- Clasificaciones /-->

      <!-- tabs de personas -->
      <div class="card mb-3">
        <div class="card-body p-0 mt-5">
          <div class="nav-align-top">
            <ul class="nav nav-tabs nav-fill rounded-0 timeline-indicator-advanced" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tipo-default" aria-controls="navs-tipo-default" aria-selected="true"><i class="ti {{ $tipoUsuarioDefault->icono }} ti-lg me-1"></i>  Nuevos </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-personas-inactivas" aria-controls="navs-personas-inactivas" aria-selected="false"><i class="ti ti-user-exclamation ti-lg me-1"></i> Inactivos</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-matriculas-activas" aria-controls="navs-matriculas-activas" aria-selected="false"><i class="ti ti-school ti-lg me-1"></i> Escuelas</button>
              </li>
            </ul>
            <div class="tab-content border-0  mx-1 p-2">
              <div class="tab-pane fade show active " id="navs-tipo-default" role="tabpanel">
                  @if($personasTipoDefault->count() > 0)
                    <small class="text-black">Listado de <b>{{ $tipoUsuarioDefault->nombre_plural }}</b>:</small>
                    <ul class="list-unstyled mb-0 mt-2 overflow-auto" style="height: 300px;">
                      @foreach ($personasTipoDefault as $persona)
                      <li class="mb-1 mt-1 p-2 border-bottom pb-3 border-2">
                        <div class="d-flex align-items-start">
                          <div class="d-flex align-items-start">
                            <div class="avatar me-2">
                              @if($persona->foto == "default-m.png" || $persona->foto == "default-f.png")
                                <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $persona->inicialesNombre() }} </span>
                              @else
                                  <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) }}" alt="{{ $persona->foto }}" alt="avatar" class="rounded-circle">
                              @endif
                            </div>
                            <div class="me-2 ms-1">
                              <h6 class="mb-0">{{ $persona->nombre(3) }}</h6>
                              <small class="text-black">Creado {!! $persona->diasCreacion !!} </small>
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
                        ¡Ups! ... no hay <b>{{ $tipoUsuarioDefault->nombre_plural}} </b> por aquí.</h6>
                    </center>
                  </div>
                  @endif
              </div>

              <div class="tab-pane fade" id="navs-personas-inactivas" role="tabpanel">
                @if($personasInactivas->count() > 0)
                  <small class="text-black">Personas inactivas con más de <b>{{ $configuracion->tiempo_para_definir_inactivo_grupo }}</b> días:</small>
                  <ul class="list-unstyled mb-0 mt-2 overflow-auto" style="height: 300px;">
                    @foreach ($personasInactivas as $persona)
                    <li class="mb-1 mt-1 p-2 border-bottom pb-3 border-2">
                      <div class="d-flex align-items-start">
                        <div class="d-flex align-items-start">
                          <div class="avatar me-2">
                            @if($persona->foto == "default-m.png" || $persona->foto == "default-f.png")
                              <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $persona->inicialesNombre() }} </span>
                            @else
                                <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) }}" alt="{{ $persona->foto }}" alt="avatar" class="rounded-circle">
                            @endif
                          </div>
                          <div class="me-2 ms-1">
                            <h6 class="mb-0">{{ $persona->nombre(3) }}</h6>
                            <small class="text-black">Hace {!!$persona->inactividad !!}</small>
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
      <!--/ tabs de personas  -->

      <!-- reportes segun periodo seleccionado -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title text-uppercase mb-0 fw-bold">Reportes</h6>
            <small class="text-black">
              Del <b>{{$fechaInicio}}</b> hasta <b>{{$fechaFin}}</b>
            </small>
          </div>

        </div>
        <div class="card-body">
          <div class="row">
            <ul class="list-unstyled ">
              @if($reportes->count() > 0)
                @foreach($reportes as $reporte)
                <li class="mb-1 mt-1 p-2 border rounded">
                  <div class="d-flex align-items-start">
                    <div class="avatar me-2  my-auto">
                      <i class="ti ti-clipboard-data me-2 fs-2"></i>
                    </div>

                    <div class="d-flex align-items-start">
                      <div class="me-2 ms-1">
                        <h6 class="mb-0">{{ $reporte->tema }}</h6>
                        <small class=""><i class="ti ti-calendar text-heading fs-6"></i> {{ $reporte->fecha }}</small><br>
                        <small class=""><i class="ti ti-users text-heading fs-6"></i> Asistencias:  {{ $reporte->cantidad_asistencias }}</small>
                      </div>
                    </div>

                    <div class="ms-auto pt-1 my-auto">
                      @if($rolActivo->hasPermissionTo('reportes_grupos.opcion_ver_perfil_reporte_grupo'))
                      <a href="" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                        <i class="ti ti-user-check me-2 ti-sm"></i></a>
                      </a>
                      @endif
                      <a href="{{ route('reporteGrupo.resumen', $reporte->id) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
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

            @if($reportes->count()>0)
            <div class="mt-0">
                {!! $reportes->appends(request()->input())->links() !!} <p class="m-1"> {{$reportes->lastItem()}} <b>de</b> {{$reportes->total()}} <b> Reportes - Página</b> {{ $reportes->currentPage() }} </p>

            </div>
            @endif
          </div>
        </div>
      </div>
      <!-- reportes según periodo seleccionado -->

    </div>

    <div class="col-12 col-md-7">



      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title m-0">
            <h6 class="card-title text-uppercase mb-0 fw-bold">Estadísticas de crecimiento</h6>
            <small class="text-black">
            Las siguientes estadísticas corresponden a la data entre <b>{{$fechaInicio}}</b> hasta <b>{{$fechaFin}}</b>
            </small>
          </div>
        </div>
        <div class="card-body">
          <ul class="nav nav-tabs widget-nav-tabs pb-2 gap-1 mx-1 d-flex flex-nowrap d-none" role="tablist">
            @foreach ($graficasTab as $grafica)
              <li class="nav-item">
                <a href="javascript:void(0);" class="nav-link btn {{$grafica->tabActiva ? 'active' : ''}} d-flex flex-column align-items-center justify-content-center" role="tab" data-bs-toggle="tab" data-bs-target="#navs-{{$grafica->id}}-id" aria-controls="navs-{{$grafica->id}}-id" aria-selected="true">
                  <div class="badge bg-label-secondary rounded p-2"><i class="{{$grafica->icono}} ti-md"></i></div>
                  <h6 class="tab-widget-title mb-0 mt-2 text-wrap lh-sm">{{$grafica->tabNombre}}</h6>
                </a>
              </li>
            @endforeach
          </ul>

          <div class="col-md-6 mb-6">
            <label for="selectpickerIcons" class="form-label">¿Qué gráfico deseas ver?</label>
            <select id="selectorGraficoTab" class="selectpicker w-100 show-tick" data-icon-base="ti" data-tick-icon="ti-check" data-style="btn-default">
              @foreach ($graficasTab as $grafica)
                <option value="{{$grafica->id}}" data-icon="ti ti-chart-bar" {{$grafica->tabActiva ? 'selected' : ''}}>
                  {{$grafica->tabNombre}}
                </option>
              @endforeach
            </select>
          </div>

          <div id="tab-dinamico" class="tab-content p-0 mt-10 ms-0 ms-sm-2">

            @foreach ($graficasTab as $grafica)
            <div class="tab-pane fade show {{$grafica->tabActiva ? 'active' : ''}}" id="navs-{{$grafica->id}}-id" role="tabpanel">
              <div id="{{$grafica->id}}"></div>
              <h6 class="mb-0">{{ $grafica->titulo ? $grafica->titulo : '' }}</h6>
              <small class="text-black">{{ $grafica->descripcion ? $grafica->descripcion : '' }} </small>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Grafico por edades -->
          <div class="col-12 col-md-12">
           <div class="card mb-4">
             <div class="card-header d-flex justify-content-between">
               <div>
                 <h6 class="card-title text-uppercase mb-0 fw-bold">Personas por rango</h6>
                 <small class="text-black">
                   Esta gráfica muestra la cantidad de personas clasificándola según su edad.
                 </small>
               </div>
             </div>
             <div class="card-body">
               <div id="rangoEdades"></div>

               <div class="row mt-10">
                @foreach ($rangoEdades as $rangoEdad)
                <div class=" col-12 col-md-4 d-flex flex-column">
                  <small class="text-black">{{ $rangoEdad->nombre }} ({{ $rangoEdad->edad_minima }} a {{ $rangoEdad->edad_maxima }}) </small>
                  <small class="fw-semibold text-black ">{{ $rangoEdad->cantidad }}</small>
                  <hr class="my-3 border-2">
                </div>
                @endforeach
               </div>

             </div>
           </div>
          </div>
        <!-- /Grafico por edades -->

        <!-- Grafico por sexo -->
        <div class="col-12 col-md-12">
          <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
              <div>
                <h6 class="card-title text-uppercase mb-0 fw-bold">Personas por sexo</h6>
                <small class="text-black">
                  Esta gráfica muestra la cantidad de personas clasificándola según su sexo.
                </small>
              </div>
            </div>
            <div class="card-body">
              <div id="tiposDeSexos"></div>

              <div class="row mt-10">
                @foreach ($tiposDeSexo as $tipoSexo)
                <div class=" col-12 col-md-6 d-flex flex-column">
                  <small class="text-black">{{ $tipoSexo->nombre }} </small>
                  <small class="fw-semibold text-black ">{{ $tipoSexo->cantidad }}</small>
                  <hr class="my-3 border-2">
                </div>
                @endforeach
              </div>

            </div>
          </div>
        </div>
        <!-- /Grafico por sexo -->

      </div>

    </div>
  </div>

    <!-- modal portada-->
    <form id="formularioPortada"  role="form" class="forms-sample" method="POST" action="{{ route('grupo.cambiarPortada', $grupo) }}"  enctype="multipart/form-data">
      @csrf
      @method('PATCH')
      <div class="modal fade modal-img" id="modalPortada" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
          <div class="modal-content">
            <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-4">
                <h3 class="mb-2"><i class="ti ti-camera  ti-lg"></i> Subir portada</h3>
                <p class="text-black">Selecciona y recorta la portada</p>
              </div>

              <div class="row">
                <div class="col-12">
                  <div class="mb-2">
                    <label class="mb-2"><span class="fw-bold">Paso #1</span> Selecciona la portada</label><br>
                    <input class="form-control" type="file" id="cropperImageUploadPortada">
                  </div>
                  <div class="mb-2">
                    <label class="mb-2"><span class="fw-bold">Paso #2</span> Recorta la portada</label><br>
                    <center>
                      <img src="{{ Storage::url('generales/img/otros/placeholder.jpg') }}" class="w-100" id="croppingImagePortada" alt="cropper">
                    </center>
                    <input class="form-control d-none" type="text" value="" id="imagen-recortada-portada" name="foto">
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer text-center">
              <div class="col-12 text-center">
                <button type="submit" id="cropSubmitPortada" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ modal foto -->
    </form>


  @livewire('Grupos.modal-baja-alta-grupo')
  @livewire('ReporteGrupos.modal-nuevo-reporte')
@endsection
