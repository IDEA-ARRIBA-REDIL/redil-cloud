@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Reportes de grupo')

<!-- Page -->
@section('page-style')
@vite([

'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection


@section('page-script')
<script type="module">

  const swiperContainer = document.querySelector('#swiper-with-pagination-cards');
  const swiper = new Swiper(swiperContainer, {
    slidesPerView: "auto",
    spaceBetween: 30,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });


  $(function() {
    //esta bandera impide que entre en un bucle cuando se ejecuta la funcion cb(start, end)
    let band=0;
    moment.locale('es');

    function cb(start, end) {

      $('#filtroFechaIni').val(start.format('YYYY-MM-DD'));
      $('#filtroFechaFin').val(end.format('YYYY-MM-DD'));

      $('#filtroFechas span').html(start.format('YYYY-MM-DD') + ' hasta ' + end.format('YYYY-MM-DD'));
      if(band==1)
      $("#formBuscar").submit();
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
            'Hoy': [moment(), moment()],
            'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
            'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
            'Mes actual': [moment().startOf('month'), moment().endOf('month')],
            'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Año actual': [moment().startOf('year'), moment().endOf('year')],
            'Año anterior': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
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


<script>
    const buscarInput = document.getElementById('buscar');
    const btnBorrarBusquedaPorPalabra = document.getElementById('borrarBusquedaPorPalabra');
    const formularioBuscar = document.getElementById('formBuscar');
    let timeoutId;
    const delay = 1000; // Tiempo en milisegundos después de dejar de escribir para enviar el formulario

    buscarInput.addEventListener('input', function() {
        clearTimeout(timeoutId); // Limpiar cualquier timeout anterior

        if (this.value.length >= 3) {
          timeoutId = setTimeout(() => {
              formularioBuscar.submit();
          }, delay);
        }else if(this.value.length == 0)
        {
          formularioBuscar.submit();
        }
    });

    btnBorrarBusquedaPorPalabra.addEventListener('click', function() {
      buscarInput.value = "";
      formularioBuscar.submit();
    });
</script>

<script>
  document.querySelectorAll('.remove-tag').forEach(button => {
    button.addEventListener('click', function() {

      const field = this.dataset.field;
      const fieldAux = this.dataset.field2;
      const value = this.dataset.value;
      const form = document.getElementById('busquedaAvanzada');
      const input = form.querySelector('[id="' + field + '"]');

      if (input && $(input).hasClass('select2BusquedaAvanzada')) {
        // Si es un Select2, usa el método 'val' de Select2 para eliminar la opción
        let currentValues = $(input).val();
        if (Array.isArray(currentValues)) {
            // Si es un select múltiple
            const newValue = currentValues.filter(v => v != value);
            $(input).val(newValue).trigger('change');
        } else {
            // Si es un select simple
            $(input).val(null).trigger('change');
        }
      } else if (input && input.tagName === 'SELECT' && input.multiple) {
        // Si es un select múltiple nativo (poco probable con Select2, pero por si acaso)
        let currentValues = Array.from(input.selectedOptions).map(option => option.value);
        const newValue = currentValues.filter(v => v != value);
        for (let i = 0; i < input.options.length; i++) {
            input.options[i].selected = newValue.includes(input.options[i].value);
        }
        $(input).trigger('change'); // Dispara el evento change para otras posibles escuchas*/
      } else if (input && input.tagName === 'SELECT') {
        // Si es un select simple nativo
        input.value = '';
      } else if (input) {
        // Si es un input normal
        input.value = '';
        if(fieldAux)
        {
          const inputAux = form.querySelector('[id="' + fieldAux + '"]');
          inputAux.value = '';
        }
      }

      form.submit();
    });
  });
</script>

<script>
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron =/[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }
</script>

<script>

  function abrirSupervisarReporte(reporteId, tipoGestion)
  {
    Livewire.dispatch('abrirSupervisarReporte', { reporteId: reporteId, tipoGestion: tipoGestion });
  }

  function eliminacionForzada(reporteId)
  {
    Swal.fire({
        title: '¿Deseas eliminar el reporte N° '+reporteId+'?',
        text: "Esta acción no es reversible.",
        icon: 'warning',
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'No',
        customClass: {
          confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
          cancelButton: 'btn btn-label-secondary waves-effect waves-light'
        },
        buttonsStyling: false
      }).then((result) => {
      if (result.isConfirmed) {

        $('#eliminarReporte').attr('action',"/reporte-grupo/"+reporteId+"/eliminar");
        $('#eliminarReporte').submit();
      }
    })
  }
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      // Seleccionamos todos los elementos que se pueden colapsar
      const collapseElements = document.querySelectorAll('.card-body.collapse');

      collapseElements.forEach(function (collapseEl) {
          // Escuchamos el evento que Bootstrap dispara ANTES de empezar a MOSTRAR el contenido
          collapseEl.addEventListener('show.bs.collapse', function () {
              // Buscamos el botón que controla este div en específico
              const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
              if (triggerButton) {
                  const icon = triggerButton.querySelector('span.ti');
                  // Cambiamos el ícono a 'menos'
                  icon.classList.remove('ti-plus');
                  icon.classList.add('ti-minus');
              }
          });

          // Escuchamos el evento que Bootstrap dispara ANTES de empezar a OCULTAR el contenido
          collapseEl.addEventListener('hide.bs.collapse', function () {
              const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
              if (triggerButton) {
                  const icon = triggerButton.querySelector('span.ti');
                  // Cambiamos el ícono a 'más'
                  icon.classList.remove('ti-minus');
                  icon.classList.add('ti-plus');
              }
          });
      });
  });
</script>
@endsection

@section('content')
<h4 class=" mb-1 fw-semibold text-primary">Reportes de grupo</h4>


@include('layouts.status-msn')

<div class="row pt-5">
  <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg" id="swiper-with-pagination-cards">
    <div class="swiper-wrapper">
        <!-- Cards with few info -->
        @foreach( $indicadoresGenerales->chunk(4) as $chunk )
        <div class="swiper-slide">
          <div class="row equal-height-row  g-2">
            @foreach($chunk as $indicador )
            <div class="col equal-height-col col-lg-3 col-12">
              <a href="{{ route('reporteGrupo.lista', $indicador->url) }}">
                <div class="h-100 card border rounded-3 shadow-sm">
                  <div class="card-body d-flex flex-row p-3">

                    <div class="card-icon me-1 ">
                    <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/'. $indicador->imagen) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/'. $indicador->imagen) }}" alt="icono" class="me-2" width="50">
                    </div>

                    <div class="card-title mb-0 lh-sm">
                      <p class="text-black mb-0" style="font-size: .8125rem">{{ $indicador->nombre }}</p>
                      <h5 class="mb-0 me-2">{{ $indicador->cantidad }}</h5>
                    </div>

                  </div>
                </div>
              </a>
            </div>
            @endforeach
          </div>
        </div>
        @endforeach
        <!--/ Cards with few info -->
    </div>
    <div class="d-flex mt-10">
        <div class="swiper-pagination"></div>
    </div>
  </div>
</div>

<hr>

<form id="formBuscar" class="forms-sample" method="GET" action="{{ route('reporteGrupo.lista', $tipo) }}">
  <div class="row mt-5">
    <div class="col-12 col-md-6 mb-3">
      <div class="input-group input-group-merge bg-white">
        <input id="buscar" name="buscar" type="text" value="{{ $buscar }}" onkeypress="return sinComillas(event)" class="form-control" placeholder="Busqueda..." aria-describedby="btnBusqueda">
        @if($buscar)
        <span id="borrarBusquedaPorPalabra" class="input-group-text"><i class="ti ti-x"></i></span>
        @else
        <span class="input-group-text"><i class="ti ti-search"></i></span>
        @endif
      </div>
    </div>

    <!-- Por rango de fechas  -->
    <div class="col-12 col-md-6 mb-3">
      <div class="input-group input-group-merge">
        <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
      </div>
      <input type="text" id="filtroFechaIni" name="filtroFechaIni" value="{{ $filtroFechaIni }}" class="form-control d-none" placeholder="">
      <input type="text" id="filtroFechaFin" name="filtroFechaFin" value="{{ $filtroFechaFin }}" class="form-control d-none" placeholder="">
    </div>
  </div>

  <div class="filter-tags py-3">
    <span class="text-black me-5">{{  $reportes->total().' Reportes ('.$textoInformativo.')' }}</span>
    @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
      @foreach($tagsBusqueda as $tag)
        <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
          <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
        </button>
      @endforeach
      @if($bandera == 1)
        <a type="button" href="{{ route('reporteGrupo.lista', $tipo) }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
          <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
        </a>
      @endif
    @endif
  </div>
</form>




   <!-- lista de reportes -->
  <div class="row equal-height-row g-4 mt-1">

    @if(count($reportes)>0)
      @foreach($reportes as $reporte)
        <div class=" col equal-height-col col-12 ">
            <div class="card rounded-3 shadow">
                <div class="card-header border-bottom d-flex px-4 pt-3 pb-1" style="background-color:#F9F9F9!important">
                  <div class="flex-fill row">

                    <div class="col-2 col-md-3">
                      <div class="d-flex flex-row">
                        <div class="d-flex flex-column">
                          <small class="text-black ms-1">Cod:</small>
                          <small class="fw-semibold ms-1 text-black ">{{ $reporte->id }}</small>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <div class="d-flex flex-row">
                        <div class="d-flex flex-column">
                          <small class="text-black ms-1">Tema:</small>
                          <small class="fw-semibold ms-1 text-black ">{{ $reporte->tema }}</small>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <div class="d-flex flex-row">
                        <i class="ti ti-calendar text-black"></i>
                        <div class="d-flex flex-column">
                          <small class="text-black ms-1">Fecha:</small>
                          <small class="fw-semibold ms-1 text-black ">{{ $reporte->fecha }}</small>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <div class="d-flex flex-row">
                        <i class="ti ti-atom-2 text-black"></i>
                        <div class="d-flex flex-column">
                          <small class="text-black ms-1">Grupo:</small>
                          <small class="fw-semibold ms-1 text-black ">{{ $reporte->grupo->nombre }}</small>
                        </div>
                      </div>
                    </div>

                  </div>

                  <div class="">
                    <div class="ms-auto">

                      @if( $rolActivo->hasPermissionTo('reportes_grupos.ver_opciones_reporte_grupo') )
                      <div class="dropdown zindex-2 p-1 float-end">
                        <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect"  data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
                        <ul class="dropdown-menu dropdown-menu-end">

                          @if( $rolActivo->hasPermissionTo('reportes_grupos.opcion_ver_perfil_reporte_grupo') )
                          <li><a class="dropdown-item" href="{{ route('reporteGrupo.resumen', $reporte->id) }}">Resumen </a></li>
                          @endif

                          @if( $rolActivo->hasPermissionTo('reportes_grupos.opcion_actualizar_reporte_grupo') )
                            @if($reporte->aprobado === null || !$configuracion->tiene_sistema_aprobacion_de_reporte || $rolActivo->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha') )
                              @if( $rolActivo->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha') )
                              <li><a class="dropdown-item" href="{{ route('reporteGrupo.asistencia', $reporte->id) }}">Editar </a></li>
                              @elseif($reporte->grupo->estaDentroDelRango($reporte->fecha, $configuracion))
                              <li><a class="dropdown-item" href="{{ route('reporteGrupo.asistencia', $reporte->id) }}">Editar</a></li>
                              @endif
                            @endif
                          @endif


                          @if($configuracion->tiene_sistema_aprobacion_de_reporte)
                            @if(!$reporte->no_reporte && $reporte->finalizado)
                              @if($reporte->aprobado === null)
                                @if( $rolActivo->hasPermissionTo('reportes_grupos.opcion_aprobar_reporte_grupo') || $rolActivo->hasPermissionTo('reportes_grupos.opcion_desaprobar_reporte_grupo') )
                                  <li><a class="dropdown-item" href="javascript:;" onclick="abrirSupervisarReporte('{{$reporte->id}}', 'revisar')">Revisar reporte</a></li>
                                @endif
                              @else
                                @if( $rolActivo->hasPermissionTo('reportes_grupos.opcion_aprobar_reporte_grupo') && $reporte->aprobado)
                                  <li><a class="dropdown-item" href="javascript:;" onclick="abrirSupervisarReporte('{{$reporte->id}}', 'corregir')">Corregir reporte</a></li>
                                @endif
                                @if( $rolActivo->hasPermissionTo('reportes_grupos.opcion_desaprobar_reporte_grupo') && !$reporte->aprobado)
                                  <li><a class="dropdown-item" href="javascript:;" onclick="abrirSupervisarReporte('{{$reporte->id}}' , 'aprobar')">Aprobar reporte</a></li>
                                @endif
                              @endif
                            @endif
                          @endif

                          <hr class="dropdown-divider">
                          @if($rolActivo->hasPermissionTo('reportes_grupos.opcion_eliminar_reporte_grupo'))
                            @if($reporte->aprobado != true || $rolActivo->hasPermissionTo('reportes_grupos.opcion_aprobar_reporte_grupo'))
                            <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="eliminacionForzada('{{$reporte->id}}')">Eliminación forzada </a></li>
                            @endif
                          @endif

                        </ul>
                      </div>
                      @endif
                    </div>
                  </div>

                </div>

                <div class="card-body p-4 collapse" id="cardBodyReporte{{ $reporte->id }}">
                    <div class="row">

                      <div class="col-12 col-md-3">
                        <div class="d-flex flex-row">
                          <div class="d-flex flex-column">
                            <small class="text-black ms-1">Encargados:</small>
                            @foreach($reporte->grupo->encargadosDirectos() as $encargado)
                            <div class="d-flex flex-row">
                              <div class="d-flex flex-column">
                                <small class="fw-semibold ms-1 text-black">{{ $encargado->nombre }}</small>
                              </div>
                            </div>
                            @endforeach
                          </div>
                        </div>
                      </div>

                      <div class="col-6 col-md-3">
                        <div class="d-flex flex-row">
                          <div class="d-flex flex-column">
                            <small class="text-black ms-1">¿Grupo realizado?</small>
                            <small class="fw-semibold ms-1 text-black ">{{ $reporte->no_reporte ? 'No': 'Si' }}</small>
                          </div>
                        </div>
                      </div>

                      <div class="col-6 col-md-3">
                        <div class="d-flex flex-row">
                          <div class="d-flex flex-column">
                            <small class="text-black ms-1">¿Finalizado?</small>
                            <small class="fw-semibold ms-1 text-black ">{{ $reporte->finalizado ? 'Si': 'No' }}</small>
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-md-3">
                        <div class="d-flex flex-row">
                          <div class="d-flex flex-column">
                            <small class="text-black ms-1">Estado aprobación</small>
                            <small class="fw-semibold ms-1 text-black ">{{ $reporte->aprobado === null ? 'Sin revisar' : ( $reporte->aprobado ? 'Aprobado' : 'Corregido') }}</small>
                          </div>
                        </div>
                      </div>

                    </div>
                </div>

                <div class="card-footer border-top p-1">
                    <div class="d-flex justify-content-center">
                        <button type="button"
                                class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
                                data-bs-toggle="collapse"
                                data-bs-target="#cardBodyReporte{{ $reporte->id }}">
                            <span class="ti ti-plus"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
      @endforeach
    @else
    <div class="mt-5 mb-5 py-5">
      <center>
      <i class="ti ti-user ti-xl"></i>
      <p>La busqueda no arrojo ningun resultado.</p>
      </center>
    </div>
    @endif





  </div>
  <!--/ lista de reportes -->



  <div class="row my-3">
    @if($reportes)
    <p> {{$reportes->lastItem()}} <b>de</b> {{$reportes->total()}} <b>reportes - Página</b> {{ $reportes->currentPage() }} </p>
    {!! $reportes->appends(request()->input())->links() !!}
    @endif
  </div>



  @livewire('ReporteGrupos.gestionar-aprobacion-desaprobacion-de-reportes', [])

  <form id="eliminarReporte" method="POST" action="">
    @csrf
  </form>

@endsection
