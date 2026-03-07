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
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
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
'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',

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
      let urlDestino = "{{ route('grupo.perfil.estadisticasCobertura', ['grupo' => 'ID_REEMPLAZABLE', 'encargado' => $encargado->id ?? null]) }}";
      urlDestino = urlDestino.replace('ID_REEMPLAZABLE', nuevoGrupoId);
      window.location.href = urlDestino;
    });
  });
</script>
@endif

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
  function GraficasDinamicasTab(grafica)
  {
    const {
        datos: datosArray,
        categorias: categoriasArray
    } = grafica;
    const highlightData = 5;

    let seriesConfig;
    let colorsConfig;
    let plotOptionsConfig;
    let legendConfig;
    let total = 0;

    // 1. DETECTAR EL TIPO DE GRÁFICO
    // Si el primer elemento del array de datos es un objeto, es multi-serie.
    const esMultiSerie = Array.isArray(datosArray) && datosArray.length > 0 && typeof datosArray[0] === 'object';

    // 2. CALCULAR EL TOTAL SEGÚN EL TIPO DE GRÁFICO
    if (esMultiSerie) {
        // Sumar los valores de todas las series
        datosArray.forEach(serie => {
            serie.data.forEach(valor => {
                total += valor;
            });
        });
    } else {
        // Sumar los valores de la única serie
        datosArray.forEach(valor => {
            total += valor;
        });
    }

    const showEjeY = total > 0;

    // 3. CONFIGURAR LAS OPCIONES SEGÚN EL TIPO
    if (esMultiSerie) {
        // --- Opciones para MÚLTIPLES SERIES ---
        seriesConfig = datosArray; // Los datos ya tienen el formato correcto
        colorsConfig = [chartColors.column.series1, chartColors.column.series2]; // Colores por serie
        legendConfig = { show: true, position: 'top', horizontalAlign: 'left' }; // Mostrar leyenda
        plotOptionsConfig = {
            bar: {
                columnWidth: '45%', // Un poco más ancho para agrupar barras
                startingShape: 'rounded',
                borderRadius: 6,
                // 'distributed' debe ser falso para agrupar las barras
                distributed: false,
            }
        };

    } else {
        // --- Opciones para UNA SOLA SERIE (tu lógica original) ---
        seriesConfig = [{ data: datosArray }];
        legendConfig = { show: false };
        plotOptionsConfig = {
            bar: {
                columnWidth: '32%',
                startingShape: 'rounded',
                borderRadius: 6,
                // 'distributed' debe ser verdadero para colorear cada barra individualmente
                distributed: true,
                dataLabels: {
                    position: 'top'
                }
            }
        };

        // Construir el array de colores para cada barra individual
        const basicColor = config.colors.primary;
        const highlightColor = config.colors.primary; // O el color que prefieras
        const colorArr = [];
        for (let i = 0; i < datosArray.length; i++) {
            if (i === highlightData) {
                colorArr.push(highlightColor);
            } else {
                colorArr.push(basicColor);
            }
        }
        colorsConfig = colorArr;
    }

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
      plotOptions: plotOptionsConfig,
      grid: {
        show: false,
        padding: {
          top: 0,
          bottom: 0,
          left: -10,
          right: -10
        }
      },
      colors: colorsConfig,
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
      series: seriesConfig, // Series dinámicas
      legend: legendConfig,
      tooltip: {
        enabled: true
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

  function crearGraficoEstadoReportes(elemento, datosGrafico) {
    const {
        datos,
        categorias,
        valorObjetivo
    } = datosGrafico;

    // 1. Calcular la altura máxima necesaria para el eje Y
    let alturaMaxima = valorObjetivo || 0;
    const numeroDeCategorias = categorias.length;

    for (let i = 0; i < numeroDeCategorias; i++) {
        let sumaBarra = 0;
        datos.forEach(serie => {
            if (serie.data[i]) {
                sumaBarra += serie.data[i];
            }
        });
        if (sumaBarra > alturaMaxima) {
            alturaMaxima = sumaBarra;
        }
    }

    // Añadimos un 20% de espacio extra en la parte superior del gráfico
    const limiteEjeY = alturaMaxima > 0 ? Math.ceil(alturaMaxima * 1.2) : 5;

    // 2. Definir las opciones del gráfico de forma limpia y específica
    const opciones = {
        series: datos,
        chart: {
            type: 'bar',
            stacked: true,
            height: 250, // Le damos un poco más de altura
            parentHeightOffset: 0,
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
            //borderRadius: 6,
            //distributed: true,
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

        colors: ['#29dac7', '#d2b050', '#f56954'], // Finalizado, No reportado, No realizado

        dataLabels: {
          enabled: true,
          formatter: function (val) {
            return val;
          },
          offsetY: 0,
          style: {
            fontSize: '15px',
            colors: [headingColor],
            fontWeight: '500',
            fontFamily: 'Public Sans'
          }
        },
        /*annotations: {
            yaxis: [{
                y: valorObjetivo,
                borderColor: '#2e2e2eff', // Un color oscuro para máximo contraste
                borderWidth: 2,
                strokeDashArray: 2,
                label: {
                    borderColor: '#2e2e2eff',
                    offsetX: -15,
                    style: {
                        color: '#fff',
                        background: '#2e2e2eff',
                        fontSize: '12px',
                        fontWeight: 'bold',
                    },
                    text: 'Nro grupos: ' + valorObjetivo
                }
            }]
        },*/
        xaxis: {
          categories: categorias,
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
            min: 0,
            max: limiteEjeY, // Usamos el límite calculado
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
          }
        },
        legend: {
        show: true,
        position: 'top',
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

    };

    // 3. Renderizar el gráfico
    const grafico = new ApexCharts(elemento, opciones);
    grafico.render();
  }

  var graficasTab = @json($graficasTab);

  $.each(graficasTab, function(key, grafica) {
     const graficaDinamicaEl = document.querySelector('#' + grafica.id);
     if (graficaDinamicaEl) {
        // --- LÓGICA MODIFICADA ---
        if (grafica.id === 'graficaEstadoReportes') {
            // Si es el gráfico problemático, usamos la nueva función aislada
            crearGraficoEstadoReportes(graficaDinamicaEl, grafica);
        } else {
            // Para todos los demás gráficos, usamos la función original
            const graficaDinamicaConfig = GraficasDinamicasTab(grafica);
            const graficaDinamica = new ApexCharts(graficaDinamicaEl, graficaDinamicaConfig);
            graficaDinamica.render();
        }
        // --- FIN DE LA LÓGICA MODIFICADA ---
    }
  });



  // Graficas dinamicas tab
  function GraficasDinamicasPasosTab(datosArray, highlightData, categoriasArray)
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

  var graficasPasosTab = @json($graficaPasosTab);

  $.each(graficasPasosTab, function(key, grafica) {
    const graficaDinamicaEl = document.querySelector('#'+grafica.id),
      graficaDinamicaConfig = GraficasDinamicasPasosTab(
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


  // Grafico por tipo de grupos
  const tiposGrupoGrafico = document.querySelector('#tiposDeGrupos'),
   seriesTiposGrupos = JSON.parse(<?php print json_encode(json_encode($seriesTiposGrupos)); ?>),
   labelsTiposGrupos = JSON.parse(<?php print json_encode(json_encode($labelsTiposGrupos)); ?>),
   colorsTiposGrupos = JSON.parse(<?php print json_encode(json_encode($colorsTiposGrupos)); ?>),

    tiposGruposConfig = {
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
              filename: 'Gráfico_tipo_grupo_{{$grupo->nombre}}',
            },
            csv: {
              filename: 'Gráfico_tipo_grupo_{{$grupo->nombre}}',
            },
            png: {
              filename: 'Gráfico_tipo_grupo_{{$grupo->nombre}}',
            }
          },
        },
      },
      labels: labelsTiposGrupos,
      series: seriesTiposGrupos,
      colors: colorsTiposGrupos,
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
                fontSize: '0.8rem',
                fontFamily: 'Public Sans'
              },
              value: {
                fontSize: '1.2rem',
                color: legendColor,
                fontFamily: 'Public Sans',
                formatter: function (val) {
                  return parseInt(val, 10) + ' Grupo(s)';
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
  if (typeof tiposGrupoGrafico !== undefined && tiposGrupoGrafico !== null) {
    const tiposGrupos = new ApexCharts(tiposGrupoGrafico, tiposGruposConfig);
    tiposGrupos.render();
  }
  // Grafico por tipo de grupos
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
    $('#divRango').addClass('col-md-12');

    $('#semanaIni').removeAttr('required');
    $('#semanaFin').removeAttr('required');

    $('#mesIni').removeAttr('required');
    $('#mesFin').removeAttr('required');


    if ($(this).val() == 'otroSemanas')
    {
      $('#divSemanaIni').removeClass('d-none');
      $('#divSemanaFin').removeClass('d-none');
      $('#divRango').removeClass('col-md-12');
      $('#divRango').addClass('col-md-6');

      $('#semanaIni').attr('required', 'required');
      $('#semanaFin').attr('required', 'required');
    }else if ($(this).val() == 'otroMeses'){
      $('#divMesIni').removeClass('d-none');
      $('#divMesFin').removeClass('d-none');
      $('#divRango').removeClass('col-md-12');
      $('#divRango').addClass('col-md-6');

      $('#mesIni').attr('required', 'required');
      $('#mesFin').attr('required', 'required');
    }
  });
</script>

<script type="module">
  const verticalExample = document.getElementById('navs-tipo-default');
  new PerfectScrollbar(verticalExample, {
    wheelPropagation: false
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

      $('#selectorGraficoPasosTab').change(function(){
        var selectedTab = $(this).val();

        $('#tab-dinamico-pasos .tab-pane').removeClass('active');
        $('#navs-'+selectedTab+'-id').addClass('active');

      });
    });

  });
</script>

<script>
  $(document).ready(function() {
    $('.selectTiposDeGrupo').select2({
      placeholder: "Selecciona los tipos de grupo",
      allowClear: true
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
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasGrupo', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="nav-link waves-effect waves-light"><i class='ti-xs ti ti-chart-bar me-1'></i> Estadísticas del grupo </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.estadisticasCobertura', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="nav-link waves-effect waves-light active"><i class='ti-xs ti ti-chart-dots-2 me-1'></i> Estadísticas cobertura </a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="nav-link waves-effect waves-light"><i class='ti-xs ti ti-info-square-rounded me-1'></i> Información básica</a></li>
          <li class="nav-item"><a href="{{ route('grupo.perfil.integrantes', [ 'grupo' => $grupo, 'encargado' => $encargado ]) }}" class="nav-link waves-effect waves-light"><i class='ti-xs ti ti-users me-1'></i> Integrantes </a></li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->

  <form id="formulario" class="forms-sample" method="GET" action="{{ route('grupo.perfil.estadisticasCobertura', $grupo) }}">
    <div id="div-filtros" class="row">
      <!-- Información principal -->

      <div class="col-12">
        <div class="card mb-4">
          <div class="card-body">
            <div class="row mb-4">
              <!-- Rango -->
              <div id="divRango" class="mb-2 col-12 {{ !$request || $request->rango=='otroSemanas' || $request->rango=='otroMeses' ? 'col-md-6' : 'col-md-12' }} mb-3">
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

              <!-- Por tipo de grupo -->
              <div id="divTiposDeGrupo" class="col-12 col-md-10 mb-3">
                <label for="filtroPorTipoDeGrupo" class="form-label">Selecciona los tipos de grupo </label>
                <select id="filtroPorTipoDeGrupo" name="filtroPorTipoDeGrupo[]" class="selectTiposDeGrupo form-select" multiple>
                  @foreach($tiposDeGrupo as $tipoGrupo)
                  <option value="{{ $tipoGrupo->id }}" {{ in_array($tipoGrupo->id, $filtroTipoGrupos ) ? "selected" : "" }}>{{ $tipoGrupo->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <!-- Por tipo de grupo -->

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
      <div class="card mb-4">
        <div class="card-header align-items-center">
          <h6 class="card-title text-uppercase mb-0 fw-bold">Clasificaciones</h6>
          <small class="text-black">
            Promedios de clasificaciones entre <br> <b>{{$fechaInicio}}</b> hasta <b>{{$fechaFin}}</b> y los tipos de grupos seleccionados
          </small>
        </div>
        <div class="card-body pb-3">
          <div class="row mb-4 g-3">
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
        </div>
      </div>
      <!-- Clasificaciones /-->

      <!-- taps de grupos -->
      <div class="card mb-3">
        <div class="card-body p-0 mt-5">
          <div class="nav-align-top">
            <ul class="nav nav-tabs nav-fill rounded-0 timeline-indicator-advanced" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tipos-grupo" aria-controls="navs-tipos-grupo" aria-selected="true"><i class="ti ti-atom-2 ti-lg me-2"></i> Grupos por tipo </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-grupos-inactivos" aria-controls="navs-grupos-inactivos" aria-selected="false"><i class="ti ti-exclamation-circle ti-lg me-2"></i> Grupos inactivos </button>
              </li>
            </ul>
            <div class="tab-content border-0  mx-1 p-2">
              <div class="tab-pane fade show active" id="navs-tipos-grupo" role="tabpanel">
                @if($indicadoresPortipoGrupo->count() > 0)
                  <small class="text-black">Cantidad de grupos por tipo:</small>
                  <ul class="list-unstyled mb-0 mt-2 overflow-auto" style="height: 300px;">
                    @foreach($indicadoresPortipoGrupo as $indicador)
                    <li class="mb-1 mt-1 p-2 border-bottom pb-3 border-2">
                      <form id="formIndicador{{$indicador->id}}" class="forms-sample" method="GET" action="{{ route('grupo.lista', $indicador->url) }}">
                        <input name="filtroGrupo" value="{{$grupo->id}}" class="d-none">
                          <div class="d-flex align-items-start">
                            <div class="d-flex align-items-start">
                              <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded" style="background-color: {{$indicador->color}}"><i class="ti ti-{{$indicador->icono}} ti-28px"></i></span>
                              </div>
                              <div class="me-2 ms-1">
                                <h6 class="mb-0">{{ $indicador->nombre }}</h6>
                                <small class="text-black">{{ $indicador->cantidad}}</small>
                              </div>
                            </div>

                            <div class="ms-auto my-auto pt-1">
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
                  <small class="text-black">Grupos inactivos:</small>
                  <ul class="list-unstyled mb-0 mt-2 overflow-auto" style="height: 300px;">
                    @foreach ($gruposInactivos as $grupoInactivo)
                    <li class="mb-1 mt-1 p-2 border-bottom pb-3 border-2">
                      <div class="d-flex align-items-start">
                        <div class="d-flex align-items-start">
                          <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded {{$grupoInactivo->color}}"><i class="ti ti-{{$grupoInactivo->icono}} ti-28px"></i></span>
                          </div>
                          <div class="me-2 ms-1">
                            <h6 class="mb-0">{{ $grupoInactivo->nombre }}</h6>
                            <small class="text-black">Inactivo hace {!!$grupoInactivo->inactividad !!}</small>
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
            </div>
          </div>
        </div>
      </div>
      <!--/ taps de grupos  -->

      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title m-0">
            <h6 class="card-title text-uppercase mb-0 fw-bold">Estadísticas por pasos de crecimiento</h6>
            <small class="text-black">
              Esta gráfica muestra la cantidad de personas de la cobertura clasificándola según el paso de crecimiento.
            </small>
          </div>
        </div>
        <div class="card-body">

          <div class="col-md-12 mb-6">
            <label for="selectorGraficoPasosTab" class="form-label">¿Qué gráfico deseas ver?</label>
            <select id="selectorGraficoPasosTab" class="selectpicker w-100 show-tick" data-icon-base="ti" data-tick-icon="ti-check" data-style="btn-default">
              @foreach ($graficaPasosTab as $grafica)
                <option value="{{$grafica['id']}}" data-icon="ti ti-versions" {{ $grafica['tabActiva'] ? 'selected' : ''}}>
                  {{ $grafica['tabNombre'] }}
                </option>
              @endforeach
            </select>
          </div>

          <div id="tab-dinamico-pasos" class="tab-content p-0 mt-10 ms-0 ms-sm-2">

            @foreach ($graficaPasosTab as $grafica)
            <div class="tab-pane fade show {{ $grafica['tabActiva'] ? 'active' : ''}}" id="navs-{{$grafica['id']}}-id" role="tabpanel">
              <div id="{{$grafica['id']}}"></div>
              <h6 class="mb-0">{{ $grafica['titulo'] ? $grafica['titulo'] : '' }}</h6>
              <small class="text-black">{{ $grafica['descripcion'] ? $grafica['descripcion'] : '' }} </small>
            </div>
            @endforeach
          </div>
        </div>
      </div>

    </div>

    <div class="col-12 col-md-7">

      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title m-0">
            <h6 class="card-title text-uppercase mb-0 fw-bold">Estadísticas de crecimiento</h6>
            <small class="text-black">
            Las siguientes estadísticas corresponden a la data entre <b>{{$fechaInicio}}</b> hasta <b>{{$fechaFin}}</b> y los tipos de grupos seleccionados
            </small>
          </div>
        </div>
        <div class="card-body">

          <div class="col-md-12 mb-6">
            <label for="selectorGraficoTab" class="form-label">¿Qué gráfico deseas ver?</label>
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


      <!-- taps de grupos -->
      <div class="card mb-3">
        <div class="card-body p-0 mt-5">
          <div class="nav-align-top">
            <ul class="nav nav-tabs nav-fill rounded-0 timeline-indicator-advanced" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-aaa" aria-controls="navs-aaa" aria-selected="true"> Personas por edades </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-bbb" aria-controls="navs-bbb" aria-selected="false"> Personas por sexo</button>
              </li>

              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-ccc" aria-controls="navs-ccc" aria-selected="false"> Grupos por tipo</button>
              </li>
            </ul>
            <div class="tab-content border-0  mx-1 p-2">
              <div class="tab-pane fade show active" id="navs-aaa" role="tabpanel">
                 <!-- Grafico por edades -->
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
                        <div class=" col-12  d-flex flex-column">
                          <small class="text-black">{{ $rangoEdad->nombre }} ({{ $rangoEdad->edad_minima }} a {{ $rangoEdad->edad_maxima }}) </small>
                          <small class="fw-semibold text-black ">{{ $rangoEdad->cantidad }}</small>
                          <hr class="my-3 border-2">
                        </div>
                        @endforeach
                      </div>

                    </div>
                  </div>
                <!-- /Grafico por edades -->
              </div>

              <div class="tab-pane fade" id="navs-bbb" role="tabpanel">
                <!-- Grafico por sexo -->
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
                        <div class=" col-12 d-flex flex-column">
                          <small class="text-black">{{ $tipoSexo->nombre }} </small>
                          <small class="fw-semibold text-black ">{{ $tipoSexo->cantidad }}</small>
                          <hr class="my-3 border-2">
                        </div>
                        @endforeach
                      </div>

                    </div>
                  </div>
                <!-- /Grafico por sexo -->
              </div>

              <div class="tab-pane fade" id="navs-ccc" role="tabpanel">
                <!-- Grafico por tipo de grupo-->
                  <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                      <div>
                        <h6 class="card-title text-uppercase mb-0 fw-bold">Grupos por tipo</h6>
                        <small class="text-black">
                          Esta gráfica muestra la cantidad de grupos según su tipo.
                        </small>
                      </div>
                    </div>
                    <div class="card-body">
                      <div id="tiposDeGrupos"></div>

                      <div class="row mt-10">
                        @foreach ($indicadoresPortipoGrupo as $indicador)
                        <div class=" col-12 d-flex flex-column">
                          <small class="text-black">{{ $indicador->nombre }} </small>
                          <small class="fw-semibold text-black ">{{ $indicador->cantidad }}</small>
                          <hr class="my-3 border-2">
                        </div>
                        @endforeach
                      </div>

                    </div>
                  </div>
                <!-- /Grafico por tipo de grupo -->
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ taps de grupos  -->

      <div class="row">





      </div>

    </div>
  </div>


  @livewire('Grupos.modal-baja-alta-grupo')
  @livewire('ReporteGrupos.modal-nuevo-reporte')
@endsection
