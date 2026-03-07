@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Rueda de la vida')

@section('vendor-style')


<style>
  /*esto es para eliminar las flechas de los inputs type=number */
  input[type=number]::-webkit-inner-spin-button,
  input[type=number]::-webkit-outer-spin-button
  {
    -webkit-appearance: none;
    margin: 0;
  }
  input[type=number] { -moz-appearance:textfield; }
 /*esto es para eliminar las flechas de los inputs type=number */

  .apexcharts-legend-text
  {
      position: relative;
      font-size: 14px;
      font-family: 'Poppins' !important;
  }
  body {
    overflow-x: hidden;
  }

  .promedio-seccion
  {
      transition: all 0.3s ease;
      font-size: 1.1rem;
      font-weight: 600;
  }

  .containerLabelPromedios{
    padding: 19px;
    box-shadow: 0px 3px 7px #d4d4d4;
    border-radius: 9px;
    min-width: 350px;
    margin-bottom: 7px;
  }

  .bg-label-danger
  {
    background-color: #fff2f0;
    color: #ff4d4f !important;
    border: 1px solid #ffccc7;
  }

  .bg-label-success
  {
    background-color: #f6ffed;
    color: #52c41a !important;
    border: 1px solid #b7eb8f;
  }

  .input-number
  {
   
    align-items: center;
    padding: 5px;
    border-radius: 5px;
  }

 
  .input-number input
  {
    width: 35px;
    text-align: center;
    border: solid 1px #d8d8d8;
    border-radius: 9px;
    padding: 5px;
  }

    .input-number .promedioGeneral
  {
    width: 50px;
    text-align: center;
    border: none !important;
    background: #f8f7fa;
    padding: 5px;
  }

 
  .minus
  {
      border: solid 2px #1977E5 !important;
      border-radius: 20px;
      padding: 2px !impotant;
      width: 31px;
      height: 30px;
      margin-right: 6px;
      color: #1977E5;
  }

  .plus
  {
      border: solid 2px #1977E5 !important;
      border-radius: 20px;
      padding: 2px !impotant;
      width: 31px;
      height: 30px;
      margin-left: 6px;
      color: #1977E5;
  }

  @media(max-width:480px){
    .minus
  {
      border: solid 2px #1977E5 !important;
      border-radius: 20px;
      padding: 2px !important;
      width: 41px !important;
      height: 40px !important;
      margin-right: 6px;
      color: #1977E5;
  }

  .plus
  {
      border: solid 2px #1977E5 !important;
      border-radius: 20px;
      padding: 2px !impotant;
      width: 41px !important;
      height: 40px !important ;
      margin-left: 6px;
      color: #1977E5;
  }


  }

</style>

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
])
@endsection


@section('page-script')
@vite([
'resources/assets/js/form-basic-inputs.js',

])

  <script type="module">
    $('#formulario').submit(function() {
      e.preventDefault();
      $('.btnGuardar').attr('disabled', 'disabled');

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

  <script>
    // Aquí se ejecutan las acciones para los botones de mas y menos
      const inputNumbers = document.querySelectorAll('.input-number');

      inputNumbers.forEach(inputNumber => {
          const minusButton = inputNumber.querySelector('.minus');
          const plusButton = inputNumber.querySelector('.plus');
          const inputField = inputNumber.querySelector('input');

          minusButton.addEventListener('click', () => {
              // Aquí se usa inputField, que está dentro del scope de cada iteración
              inputField.value = parseInt(inputField.value) - 1;
          });

          plusButton.addEventListener('click', () => {
              // Aquí también se usa inputField del scope de la iteración
              inputField.value = parseInt(inputField.value) + 1;
          });
      });
  </script>

  <script>
    $(document).ready(function ()
    {
      let actualStep = 1;
      let maximoStep = @json($cantidadTotalSecciones);
      $(".next-step").click(function ()
      {
        $("#step-" + actualStep).addClass('d-none');
        actualStep++;

        //paso 1 si es necesario
        $(".prev-step").removeClass('d-none');
        /// resto de los pasos
        $("#step-" + actualStep).removeClass('d-none');
      });

      $(".prev-step").click(function ()
      {
        if (actualStep > 1) {
          $("#step-" + actualStep).addClass('d-none');
          actualStep--;

          if(actualStep == 1) {
            $(".prev-step").addClass('d-none');
          }
          $("#step-" + actualStep).removeClass('d-none');
        }
      });

    });
  </script>

  <script type="module">// Array para almacenar los gráficos
  // Array para almacenar los gráficos
  let charts =[];

// Array para almacenar los nombres de los campos de cada sección
let nombresCampos = {};

  // Declarar la variable options fuera del bucle
  let options;
  let chart ;
  let json_string;

// Función para inicializar los listeners de los botones
function initializeButtonListeners() {
    // Obtener todos los botones "más" y "menos"
    const botones = document.querySelectorAll('.input-number button');

    // Recorrer cada botón
    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            // Obtener el input asociado al botón (ahora dentro de input-number)
            const inputContainer = boton.closest('.input-number');
            const input = inputContainer.querySelector('input');
            const seccionId = input.dataset.seccion;
            const promedioMinimo = input.dataset.promedio;

            // Obtener la sección padre (ajustado para la nueva estructura)
            const seccion = inputContainer.closest('.row').closest('.col-lg-5');

            // Obtener el nuevo valor del input
            let nuevoValor = parseInt(input.value);

            // Ajustar el valor según el botón presionado
            if (boton.classList.contains('minus')) {
                nuevoValor = Math.max(1, nuevoValor - 1);
            } else {
                nuevoValor = Math.min(input.max, nuevoValor );
            }

            // Actualizar el valor del input
            input.value = nuevoValor;

            // Obtener todos los inputs de la sección (ajustado para la nueva estructura)
            const inputsSeccion = seccion.querySelectorAll('.input-number input[type="number"]');

            // Calcular suma y promedio
            let suma = 0;
            inputsSeccion.forEach(input => {
                suma += parseInt(input.value);
            });

            // Calcular promedio
            const promedio = inputsSeccion.length > 0 ? suma / inputsSeccion.length : 0;

            // Actualizar los valores del promedio
            $(`#promedioSeccion-${seccionId}`).val(promedio.toFixed(1));
            $(`#promedioSeccionGeneral-${seccionId}`).val(promedio.toFixed(1));

            // Actualizar clases CSS según el promedio
            if (promedioMinimo >= promedio) {
                $('#valorPromedioVisible-' + seccionId).addClass('text-danger').removeClass('text-success');
                $(`#promedioSeccionGeneral-${seccionId}`).addClass('text-danger').removeClass('text-success');
            } else {
                $('#valorPromedioVisible-' + seccionId).addClass('text-success').removeClass('text-danger');
                $(`#promedioSeccionGeneral-${seccionId}`).addClass('text-success').removeClass('text-danger');
            }

            $('#valorPromedioVisible-' + seccionId).html(promedio.toFixed(1));

            // Actualizar el gráfico
            const chart = charts.find(chart => chart.el.id === `polarChart-${seccionId}`);
            if (chart) {
                // Crear una copia de la serie actual
                let series = [...chart.w.config.series];

                // Encontrar el índice del valor a actualizar (ajustado para la nueva estructura)
                const allInputRows = Array.from(seccion.querySelectorAll('.row'));
                const currentRow = inputContainer.closest('.row');
                const indice = allInputRows.indexOf(currentRow);

                // Actualizar el valor en la serie
                series[indice] = nuevoValor;

                // Actualizar el gráfico
                chart.updateOptions({
                    series: series
                });
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Configuración e inicialización de los gráficos
    @foreach ($seccionesContadorPromedios as $seccion)
        // Obtener los inputs de la sección
        const inputNumbersSeccion{{ $seccion->id }} = document.querySelectorAll('[data-seccion="{{ $seccion->id }}"]');

        // Array para valores de inputs y colores
        @php
            $valoresInputs = array();
            $colores = array();
            foreach($seccion->campos as $campo) {
                array_push($valoresInputs, 0);
                array_push($colores, $campo->color);
            }
        @endphp

        // Nombres de campos de la sección
        nombresCampos['seccion{{ $seccion->id }}'] = [];
        @foreach ($seccion->campos as $campo)
            nombresCampos['seccion{{ $seccion->id }}'].push('{{ $campo->nombre }}');
        @endforeach

        // Opciones del gráfico
         options = {
            series: {{json_encode($valoresInputs)}},
            labels: nombresCampos['seccion{{ $seccion->id }}'],
            chart: {
                type: 'polarArea',
            },
            yaxis: {
                min: 0,
                max: {{$seccion->max}},
                tickAmount: {{$seccion->max}},
            },
            plotOptions: {
                polarArea: {
                    rings: {
                        strokeWidth: 2
                    },
                    spokes: {
                        strokeWidth: 2
                    }
                }
            },
            fill: {
                opacity: 0.8,
                colors: @json($colores)
            },
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 780,
                options: {
                    chart: {
                        width: 320,
                        height: 500
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        // Crear y renderizar el gráfico
        chart = new ApexCharts(document.querySelector("#polarChart-{{ $seccion->id }}"), options);
        chart.render();
        charts.push(chart);
    @endforeach

    // Inicializar los listeners de los botones
    initializeButtonListeners();
});
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function()
    {
        // Capturar todos los inputs y labels
        const inputs = document.querySelectorAll('.promedioGeneral');
        const data = [];
        const labels = [];

        // Recorrer los inputs para extraer valores y etiquetas
        inputs.forEach(input =>
        {
            // Obtener valor numérico
            const value = parseFloat(input.value) || 0;
            data.push(value);

            // Obtener el label (asumiendo que está en el span adyacente)
            const label = input.nextElementSibling.textContent.trim();
            labels.push(label);
        });

        // Configuración del gráfico
        const options =
        {
            chart: {
                type: 'polarArea',
                height: 350,
                toolbar: { show: false }
            },
            series: [{
                name: 'Promedio General',
                data: data
            }],
            labels: labels,
            yaxis: { show: false },
            markers: {
                size: 4,
                colors: ['#fff'],
                strokeColors: '#6777ef',
                strokeWidth: 2,
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val.toFixed(1);
                    }
                }
            }
        };

        // Renderizar el gráfico
        const chart = new ApexCharts(document.querySelector('#polarPromedioGeneral'), options);
        chart.render();
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function()
    {
        // Variables globales
        let chart = null;
        let labels = [];
        const chartContainer = document.querySelector('#polarPromedioGeneral');

        // Función para inicializar o actualizar el gráfico
          function actualizarGrafico() {
          const inputs = document.querySelectorAll('.promedioGeneral');
          const data = [];
          let sumaTotal = 0; // Variable para acumular la suma

          inputs.forEach(input => {
              const value = Math.max(parseFloat(input.value) || 1, 1);
              data.push(value);
              sumaTotal += value; // Sumamos cada valor

              if(labels.length < inputs.length) {
                  labels.push(input.nextElementSibling.textContent.trim());
              }
          });

          // Calcular promedio
          const promedio = sumaTotal / data.length;
          const promedioRedondeado = promedio.toFixed(1); // Redondear a 1 decimal

          // Actualizar el elemento en el DOM
          document.getElementById('valorPromedioGeneralVisible').textContent = promedioRedondeado;
           $('#valorPromedioGeneralOculto').val(promedioRedondeado);
          if(promedioRedondeado >=  {{$configuracionRv->promedio_general}})
          {
             
                  $('#valorPromedioGeneralVisible').addClass('text-success').removeClass('text-danger');
               
          }else{
                  $('#valorPromedioGeneralVisible').addClass('text-danger').removeClass('text-success');
          }
          

            // Configuración del gráfico
            const options = {
                chart: {
                    type: 'polarArea', // Cambia a 'radar' si prefieres gráfico radar


                },
                legend: {
                    position: 'bottom' // Coloca la leyenda en la parte inferior
                },
                series: data,
                labels: labels,
                plotOptions: {
                    polarArea: {
                        rings: {
                            strokeWidth: 2

                        },
                        spokes: {
                            strokeWidth: 2
                        }
                    }
                },
                yaxis: { show: false },
                markers: {
                    size: 4,
                    colors: ['#fff'],
                    strokeColors: '#6777ef',
                    strokeWidth: 5,
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val.toFixed(1);
                        }
                    }
                },
                responsive: [{
                breakpoint: 780,
                options: {
                  chart: {
                    width: 320,
                    height: 500
                  },
                  legend: {
                    position: 'bottom'
                  }
                }
                }]
            };

            // Crear o actualizar gráfico
            if(chart) {
                chart.updateOptions(options);
            } else {
                chart = new ApexCharts(chartContainer, options);
                chart.render();
            }
        }

        // Inicializar gráfico al cargar
        actualizarGrafico();

        // Eventos para los botones "Continuar"
        document.querySelectorAll('.next-step').forEach(button => {
            button.addEventListener('click', function() {
                // Forzar actualización
                actualizarGrafico();

                // Opcional: Obtener ID de la sección
                const seccionId = this.dataset.seccion;
                console.log('Actualizando gráfico para sección:', seccionId);
            });
        });
    });
  </script>


@endsection

@section('content')

<div class="col-12 min-vh-100">
  <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
    <div class="col-3 text-start">
      <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
        <span class="ti-xs ti ti-arrow-left me-2"></span>
        <span class="d-none d-md-block fw-normal">Volver</span>
      </button>
    </div>
    <div class="col-6 pl-5 text-center">
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">{{$configuracionRv->nombre_general}}</h5>
    </div>
    <div class="col-3 text-end">
      <a href="{{ route('dashboard')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
        <span class="d-none d-md-block fw-normal">Salir</span>
        <span class="ti-xs ti ti-x mx-2"></span>
      </a>
    </div>
  </nav>

  <div class="pt-5 px-7 px-sm-0" style="padding-bottom: 100px;">
    <div class="col-12 col-sm-8 offset-sm-2 col-lg-8  offset-lg-2">
      <form id="formulario" role="form" class="forms-sample" method="POST" action="/rueda-vida/crear" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        @php
          $contador=1;
        @endphp
        @foreach ($secciones as $seccion)
           <!-- Secciones -->
           <div class="step row {{$contador == 1 ? '' : 'd-none'}}" id="step-{{$contador}}" >
              <div class="p-2 col-12">
                <div class="d-flex align-items-start p-2 mt-1">
                  <div class="badge rounded rounded-circle bg-label-primary p-3 me-1 rounded">
                    <i class="{{ $seccion->icono }} ti-md"></i>
                  </div>
                  <div class="my-auto ms-1 ">
                    <small class="text-muted">Paso {{$contador}} de {{ $cantidadTotalSecciones }} </small>
                     @if($seccion->tipoSeccion->nombre == 'encuesta')
                     <h6 class="mb-0">{{ $seccion->titulo_steper }}</h6>
                    @else
                      <h6 class="mb-0">{{ $seccion->nombre_seccion }}</h6>
                      @endif
                  </div>
                </div>
                <div class="progress mx-2">
                  <div id="progress-bar" class="progress-bar" role="progressbar" style="width: {{($contador / $cantidadTotalSecciones) * 100}}%;" aria-valuenow="{{($contador / 2) * 100}}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
            @if($seccion->tipoSeccion->nombre == 'contador')
              <div class="row mt-10 m-0 p-0">
                    <h4 class="fw-semibold">{{$seccion->nombre_seccion}}</h4>
                    <p>{{$seccion->subtitulo_seccion}}</p>
                     <!-- bloque izquierda -->
                    <div class="col-lg-7 col-md-8 col-sm-12 mb-5">
                        <!-- Radar Chart -->
                            <div class="">
                              <div class=" d-flex justify-content-between align-items-center">
                              </div>
                                <div class="">
                                    <div id="polarChart-{{ $seccion->id }}"></div>
                                </div>
                            </div>
                        <!-- /Radar Chart -->
                    </div>
                     <!-- bloque derecha -->
                    <div class="col-lg-5 col-md-4 col-sm-12">
                        @foreach ($seccion->campos as $campo)
                       <div class="row py-2">
                          <div class="input-number d-sm-flex col-lg-4 p-0 col-md-8 col-5">
                              <button type="button" class="minus rounded-pill"><span>-</span></button>
                              <input data-promedio="{{$seccion->promedio_minimo}}" min="0" max="{{$seccion->max}}" id="campo-{{$campo->id}}-seccion-{{$seccion->id}}" name="campo-{{$campo->id}}-seccion-{{$seccion->id}}" type="number" value="0" step="1" data-seccion="{{ $seccion->id }}">
                              <button type="button" class="plus"><span>+</span></button>
                          </div>
                          <div class="input-name my-auto col-lg-8 col-md-4 col-7">
                              @if($campo->abierto == false)
                              <span class="label text-mobile fw-normal">{{$campo->nombre}}</span>
                              @else
                              <input style=" width: 150px;
                                      border: none;
                                      border-bottom: solid 1px #d8d8d8;
                                      border-radius: 0px;"
                              class="abierto" id="campo-{{$campo->id}}-seccion-{{$seccion->id}}" name="campo-abierto-{{$campo->id}}-seccion{{$seccion->id}}"
                              type="text" placeholder="Ingresa tu habito">
                              @endif
                          </div>
                       </div>
                        @endforeach
                          <button style="height:60px;border: dotted 2px #421EB7;" type="button" class="btn mt-7 btn-label-gray waves-effect">
                           <h5 style="color:#000" class="mb-0  p-2"> Promedio:</h5> <span id='valorPromedioVisible-{{$seccion->id}}' class="text-danger" > 0.0 </span>
                          </button>
                          <input class="d-none"  value="1" data-nombreSeccion="{{$seccion->nombre_seccion}}"
                                data-seccion="{{$seccion->id}}" id="promedioSeccion-{{$seccion->id}}">
                     </div>
              </div>
            @endif

            @if($seccion->tipoSeccion->nombre == 'promedios')
              <div class="row mt-10 m-0 p-0">
                    <h4 class="fw-semibold">{{$seccion->nombre_seccion}}</h4>
                    <p>{{$seccion->subtitulo_seccion}}</p>

                    <div class="col-lg-7 col-md-6 col-sm-12">
                        <!-- Radar Chart -->
                            <div class="">
                            <div class=" d-flex justify-content-between align-items-center">
                            </div>
                            <div class="">
                                <div id="polarPromedioGeneral"></div>
                            </div>
                            </div>
                        <!-- /Radar Chart -->
                    </div>

                    <div class="col-lg-5 col-md-6 col-sm-12">
                        @foreach ($seccionesContadorPromedios as $seccion)
                        <div class="input-number">
                          <div class="containerLabelPromedios">
                           <span id="labelPromedioSeccion-{{$seccion->id}}" class="fw-semibold ">  {{$configuracionRv->label_promedio_general}} : </span>
                           <input id="promedioSeccionGeneral-{{$seccion->id}}"  class="promedioGeneral text-danger" value="0.0" disabled min="1" max="{{$seccion->max}}" name="promedioSeccionGeneral-{{$seccion->id}}" type="number" data-seccion="{{ $seccion->id }}">
                            <span class="label ">{{$seccion->nombre_seccion}}</span>
                          </div>
                        </div>
                        @endforeach
                        <button style="height:60px;border: dotted 2px #421EB7;" type="button" class="btn mt-7 btn-label-gray waves-effect">
                          <h5 style="color:#000" class="mb-0  p-2"> Promedio:</h5> <span id='valorPromedioGeneralVisible' class="text-danger" > 0.0 </span>
                          <input class="d-none" name="valorPromedioGeneralOculto" id="valorPromedioGeneralOculto">
                         </button>

                    </div>
              </div>

            @endif

            @if($seccion->tipoSeccion->nombre == 'encuesta')
              <div class="row mt-10 m-0 p-0">
                    <h4 class="fw-semibold">{{$seccion->nombre_seccion}}</h4>
                    <p>{{$seccion->subtitulo_seccion}}</p>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @foreach($seccion->metas as $meta)
                        <div class="form-group mb-4">
                          <h4> {{$meta->nombre}}</h4>
                          <input @if($meta->requerida == true) required @else  @endif name="inputMeta-{{$meta->id}}" id="inputMeta-{{$meta->id}}"  class="form-control" placeholder="Ingresa tu {{$meta->nombre}}">
                        </div>
                        <div class="row">
                          <h4> {{$configuracionRv->nombre_habitos}}</h4>
                          @foreach($meta->habitos as $habito)
                          <div class="col-lg-6 col-md-6 col-sm-12 mb-4 ">
                            <input @if($habito->requerida == true) required @else  @endif name="inputHabitoMeta-{{$habito->id}}" id="inputHabitoMeta-{{$habito->id}}"   class="form-control" placeholder="Ingresa tu {{$habito->nombre}}">
                          </div>
                          @endforeach
                        </div>
                        <hr>
                        @endforeach
                    </div>
              </div>
            @endif

            <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
                <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex  {{ $contador == 1 ? 'justify-content-sm-end' : 'justify-content-sm-between' }} ">
                  <button type="button" class="btn btn-label-secondary  rounded-pill btn-outline-secondary px-7 py-2 prev-step d-none" >
                    <span class="align-middle">Volver</span>
                  </button>
                  <button type="{{$contador == $cantidadTotalSecciones ? 'submit': 'button' }}" class="btn  {{$contador == $cantidadTotalSecciones ? 'btnGuardar': '' }} btn-primary rounded-pill  {{$contador != $cantidadTotalSecciones ? 'next-step': '' }} px-7 py-2" data-seccion="{{$seccion->id}}">
                    <span class="align-middle me-sm-1 me-0 ">{{$contador == $cantidadTotalSecciones ? 'Guardar': 'Continuar' }}</span>
                  </button>
                </div>
              </div>
            </div>
          <!-- /Secciones -->
           @php
             $contador++;
           @endphp
        @endforeach
      </form>
    </div>
  </div>
</div>


@endsection
