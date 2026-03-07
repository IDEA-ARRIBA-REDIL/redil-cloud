@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Peticiones')

<!-- Page -->
@section('page-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
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

<script type="module">

  $(document).ready(function() {
    $('.select2').select2({
        placeholder: 'Filtrar por tipo de petición',
      }
    );
  });


  $(document).ready(function() {
    $('.select2').select2({
      dropdownParent: $('#modalBusquedaAvanzada')
    });
  });


  $(document).ready(function() {
    $('.select2GeneradorExcel').select2({
      dropdownParent: $('#modalGeneradorExcel')
    });
  });

  // Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
  $('#modalGeneradorExcel').on('scroll', function(event) {
    $(this).find(".select2GeneradorExcel").each(function() {
      $(this).select2({
        dropdownParent: $(this).parent()
      });
    });
  });

  $(".clearAllItems").click(function() {
    let value = $(this).data('select');
    $('#' + value).val(null).trigger('change');
  });

  $(".selectAllItems").click(function() {
    let value = $(this).data('select');
    $("#" + value + " > option").prop("selected", true);
    $("#" + value).trigger("change");
  });
</script>

<script type="text/javascript">
  function modalRespuesta(peticionId, personaId)
  {
    Livewire.dispatch('modalRespuesta', { peticionId: peticionId, personaId: personaId });
  }

  function modalSeguimiento(peticionId, personaId)
  {
    Livewire.dispatch('modalSeguimiento', { peticionId: peticionId, personaId: personaId });
  }
</script>

<script type="text/javascript">
  function confirmarEliminacionMasiva(cantidad, tipo)
  {
    if(cantidad>0)
    {
      let titulo = '¿Estás seguro que deseas elimina esta <b>'+cantidad+'</b> petición?';
      if(cantidad > 1)
      titulo = '¿Estás seguro que deseas eliminar las <b>'+cantidad+'</b> peticiones?';

      Swal.fire({
        title: titulo,
        html: 'Esta acción no es reversible.',
        icon: 'warning',
        showCancelButton: false,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          $('#eliminacionMasiva').attr('action',"/peticion/"+tipo+"/eliminaciones");
          $('#eliminacionMasiva').submit();
        }
      });
    }else{
      Swal.fire({
        title: 'No hay peticiones por eliminar',
        html: 'Intenta nuevamente.',
        icon: 'info',
        showCloseButton: false,
        showCancelButton: false,
      });
    }

  }

  function confirmarEliminacion($peticionId)
  {
    Swal.fire({
      title: '¿Estás seguro que deseas elimina esta petición?',
      html: 'Esta acción no es reversible.',
      icon: 'warning',
      showCancelButton: false,
      confirmButtonText: 'Si, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#eliminacion').attr('action',"/peticion/"+$peticionId+"/eliminacion");
        $('#eliminacion').submit();
      }
    })
  }
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
@endsection


@section('content')

<h4 class=" mb-1 fw-semibold text-primary">Gestionar peticiones</h4>

@include('layouts.status-msn')

  <div class="row pt-5">
    <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg" id="swiper-with-pagination-cards">
      <div class="swiper-wrapper">
          <!-- Cards with few info -->
          @foreach( $indicadores->chunk(4) as $chunk )
          <div class="swiper-slide">
            <div class="row equal-height-row g-2">
              @foreach($chunk as $indicador )
              <div class="col equal-height-col col-lg-4 col-12">
                <a href="{{ route('peticion.gestionar', $indicador->url)  }}">
                  <div class="card border rounded-3 shadow-sm">
                    <div class="card-body d-flex flex-row p-3">

                      <div class="card-icon me-1">
                      <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/peticiones/'. $indicador->imagen) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/'. $indicador->imagen) }}" alt="icono" class="me-2" width="50">
                      </div>

                      <div class="card-title mb-0">
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

  <form  id="filtro" class="forms-sample" method="GET" action="{{ route('peticion.gestionar', $tipo) }}">
    <div class="row mt-5">

      <!-- Por rango de fechas  -->
      <div class="col-9 col-md-4">
        <div class="input-group input-group-merge">
          <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
          <span class="input-group-text"><i class="ti ti-calendar"></i></span>
        </div>
        <input type="text" id="filtroFechaIni" name="filtroFechaIni" value="{{ $filtroFechaIni }}" class="form-control d-none" placeholder="">
        <input type="text" id="filtroFechaFin" name="filtroFechaFin" value="{{ $filtroFechaFin }}" class="form-control d-none" placeholder="">
      </div>
      <div class="col-3 col-md-8 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-5  me-1" data-bs-toggle="offcanvas" data-bs-target="#modalBusquedaAvanzada"><span class="d-none d-md-block fw-semibold">Filtros</span><i class="ti ti-filter ms-1"></i> </button>
        @if($rolActivo->hasPermissionTo('peticiones.boton_descargar_excel'))
        <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-3 me-1" data-bs-toggle="offcanvas" data-bs-target="#modalGeneradorExcel"><i class="ti ti-file-download"></i> <span class="d-none d-md-block">Descargar excel</span></button>
        @endif

        @if($rolActivo->hasPermissionTo('peticiones.opcion_eliminacion_masiva'))
        <button type="button" class="btn btn-outline-primary waves-effect px-2 px-md-3" onclick="confirmarEliminacionMasiva('{{ $peticiones->total() }}','{{$tipo}}')"><i class="ti ti-trash"></i> <span class="d-none d-md-block">Eliminación masiva</span></button>
        @endif
      </div>

      <div class="filter-tags py-3">
        <span class="text-black me-5">{{ $peticiones->total() > 1 ? $peticiones->total().' Peticiones' : $peticiones->total().' Petición' }} </span>
        @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
          @foreach($tagsBusqueda as $tag)
            <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
              <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
            </button>
          @endforeach
          @if($bandera == 1)
            <a type="button" href="{{ route('peticion.gestionar', $tipo) }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
              <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
            </a>
          @endif
        @endif
      </div>
    </div>
  </form>

  <!-- lista de peticiones -->
  <div class="row g-4 mt-1">
    @foreach($peticiones as $peticion)
    <div class="col-12 col-xl-4 col-lg-6 col-md-6">
      <div class="card border rounded p-2">

        <div class="card-header">
          <div class="d-flex align-items-start">
            <div class="d-flex align-items-start">
              <div class="px-1">
                <button class="btn rounded-pill btn-icon btn-primary waves-effect waves-light btn-xl"><i class="ti ti-notes ti-xl mx-2"></i></button>
              </div>
              <div class="me-2 ms-1 mt-1">
                <h5 class="mb-0"><a href="javascript:;" class="text-body"><b>Tipo:</b> {{ $peticion->tipoPeticion ? $peticion->tipoPeticion->nombre : 'No definido'}}</a></h5>
                <div class="client-info"><span class="fw-medium"><i class="ti ti-calendar"></i> {{ $peticion->fecha }}</span></div>
              </div>
            </div>
            <div class="ms-auto">
              <div class="dropdown zindex-2 border rounded p-1">
                <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">

                  <hr class="dropdown-divider">
                  @if($rolActivo->hasPermissionTo('peticiones.opcion_eliminar'))
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmarEliminacion('{{$peticion->id}}')" >Eliminar</a></li>
                  @endif




                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">

        <div class="d-flex align-items-center">
          <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2">
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{$peticion->nombreUsuario}}" class="avatar pull-up">
                <img class="rounded-circle" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$peticion->fotoUsuario) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$peticion->fotoUsuario }}" alt="foto {{$peticion->nombreUsuario}}">
              </li>
              <span class="text-muted mx-1">{{ $peticion->nombreUsuario }}</span>
          </ul>
        </div>
        <div class="list-unstyled mb-4 mt-3">
          <ul class="list-unstyled mb-4 mt-3">
            <li class="d-flex align-items-center mb-1"><i class="ti ti-phone-call text-heading"></i> <span class="mx-2">{{ $peticion->telefonosUsuario }}</span> </li>
            <li class="d-flex align-items-center mb-1"><i class="ti ti-mail text-heading"></i> <span class="mx-2">{{ $peticion->emailUsuario }}</span></li>

          </ul>
        </div>

        <div class="accordion mt-3" id="accordionDePeticiones{{$peticion->id}}">

          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingPeticion{{$peticion->id}}">
              <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionPeticion{{$peticion->id}}" aria-expanded="true" aria-controls="accordionPeticion{{$peticion->id}}">
                Petición
              </button>
            </h2>

            <div id="accordionPeticion{{$peticion->id}}" class="accordion-collapse collapse" data-bs-parent="#accordionDePeticiones{{$peticion->id}}">
              <div class="accordion-body" style="height: 100px; overflow-y: scroll;">
                <p class="text-secondary mt-0 mb-2"><b><i class="ti ti-user-circle"></i> Creada por:</b> {{$peticion->usuarioCreacion }}</p>
                <p class="m-0">{!! $peticion->descripcion !!}</p>
              </div>
            </div>
          </div>

          @if($peticion->estado > 1)
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingSeguimiengo{{$peticion->id}}">
              <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionSeguimiengo{{$peticion->id}}" aria-expanded="false" aria-controls="accordionSeguimiengo{{$peticion->id}}">
                Seguimiento
              </button>
            </h2>
            <div id="accordionSeguimiengo{{$peticion->id}}" class="accordion-collapse collapse" aria-labelledby="headingSeguimiengo{{$peticion->id}}" data-bs-parent="#accordionDePeticiones{{$peticion->id}}">
              <div class="accordion-body" style="height: 100px; overflow-y: scroll;">
                @foreach($peticion->seguimientos as $seguimiento)
                <p class="text-secondary mt-0 mb-2"><b><i class="ti ti-user-circle"></i> Creada por:</b> {{$seguimiento->usuarioCreacion ? $seguimiento->usuarioCreacion->nombre(3) : 'No definido' }}</p>
                <p class="m-0">{!! $seguimiento->descripcion !!}</p>
                <hr>
                @endforeach
              </div>
            </div>
          </div>
          @endif

          @if($peticion->estado==2)
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingRespuesta{{$peticion->id}}">
              <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionRespuesta{{$peticion->id}}" aria-expanded="false" aria-controls="accordionRespuesta{{$peticion->id}}">
                Respuesta
              </button>
            </h2>
            <div id="accordionRespuesta{{$peticion->id}}" class="accordion-collapse collapse" aria-labelledby="headingRespuesta{{$peticion->id}}" data-bs-parent="#accordionDePeticiones{{$peticion->id}}">
              <div class="accordion-body">
                {!! $peticion->respuesta !!}
              </div>
            </div>
          </div>
          @endif

          @if($peticion->estado!=2)
          <div class="mt-3">
            <center>
              <button type="button" onclick="modalRespuesta('{{$peticion->id}}', '{{$peticion->user_id}}')" class="btn btn-sm rounded-pill btn-outline-primary waves-effect"> <i class="ti ti-file-check"></i> Respuesta</button>
              <button type="button" onclick="modalSeguimiento('{{$peticion->id}}', '{{$peticion->user_id}}')" class="btn btn-sm rounded-pill btn-outline-warning waves-effect"> <i class="ti ti-file-like"></i> Seguimiento</button>
            </center>
          </div>
          @endif
        </div>

        </div>
      </div>
    </div>
    @endforeach
  </div>
  <!--/ lista de peticiones -->

  <div class="row my-3">
    @if($peticiones)
    {!! $peticiones->appends(request()->input())->links() !!}
    @endif
  </div>

  @livewire('Peticiones.gestionar-peticiones')

  <form id="eliminacionMasiva" method="POST" action="">
    @csrf
    <textarea id="parametros-busqueda" name="parametrosBusqueda" class="d-none">{{json_encode(request()->input())}}</textarea>
  </form>

  <form id="eliminacion" method="POST" action="">
    @csrf
  </form>

   <!-- offcanvas busqueda avanzada -->
  <form id="busquedaAvanzada" class="forms-sample" method="GET" action="{{ route('peticion.gestionar', $tipo) }}">
    <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalBusquedaAvanzada" aria-labelledby="modalBusquedaAvanzadaLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalBusquedaAvanzadaLabel">
              Filtros
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
            <div class="row">


              <!-- Por tipo peticion -->
              <div class="col-12 mb-3">

                <label class="form-label">Por tipo de peticiones</label>
                <select id="filtroTipoPeticiones" name="filtroTipoPeticiones[]" class="select2 form-select" multiple>
                  @foreach($tiposPeticiones as $tipoPeticion)
                  <option value="{{ $tipoPeticion->id }}" {{ $filtroTipoPeticiones && in_array($tipoPeticion->id,$filtroTipoPeticiones) ? 'selected' : '' }}>{{ $tipoPeticion->nombre }}</option>
                  @endforeach
                </select>
              </div>

                <!-- Por persona -->
                @livewire('Usuarios.usuarios-para-busqueda', [
                  'id' => 'persona_id',
                  'class' => 'col-12 col-md-12 mb-3',
                  'label' => 'Filtrar por persona',
                  'estiloSeleccion' => 'pequeno',
                  'placeholder' => 'Filtrar por persona',
                  'tipoBuscador' => 'unico',
                  'queUsuariosCargar' => $queUsuariosCargar,
                  'conDadosDeBaja' => 'no',
                  'modulo' => 'peticiones',
                  'obligatorio' => true,
                  'usuarioSeleccionadoId' => $persona ? $persona->id : ''
                ])
            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Filtrar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

  <!-- offcanvas generador de excel  -->
  <form class="forms-sample" method="POST" action="{{ route('peticion.generarExcel', $tipo) }}">
    @csrf
    <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalGeneradorExcel" aria-labelledby="modalGeneradorExcelLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalGeneradorExcelLabel">
              Exportar a excel
            </h4>

            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
           <div class="mb-4">
              <span class="text-black ti-14px mb-4">Selecciona los campos que deseas exportar en el archivo excel.</span>
            </div>

             <div class="row">
                <!-- Informacion personal -->
                <div class="col-12 mb-3">
                  <label for="informacionPersonal" class="form-label">Información personal <br>
                    (<a href="javascript:;" data-select="informacionPersonal" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionPersonal" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                  </label>
                  <select id="informacionPersonal" name="informacionPersonal[]" class="select2GeneradorExcel form-select" multiple>
                    @foreach($camposInformeExcel->where('selector_id',1) as $campo)
                    <option value="{{ $campo->id }}">{{ $campo->nombre_campo_informe }}</option>
                    @endforeach
                  </select>
                </div>

                <!-- Informacion ministerial -->
                <div class="col-12 mb-3">
                  <label for="informacionMinisterial" class="form-label">Información ministerial <br>
                    (<a href="javascript:;" data-select="informacionMinisterial" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionMinisterial" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                  </label>
                  <select id="informacionMinisterial" name="informacionMinisterial[]" class="select2GeneradorExcel form-select" multiple>
                    @foreach($pasosCrecimiento as $pasoCrecimiento)
                    <option value="{{ $pasoCrecimiento->id }}">{{ $pasoCrecimiento->nombre }}</option>
                    @endforeach
                  </select>
                </div>

                <!-- Informacion congregacional -->
                <div class="col-12 mb-3">
                  <label for="informacionCongregacional" class="form-label">Información congregacional <br>
                    (<a href="javascript:;" data-select="informacionCongregacional" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionCongregacional" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                  </label>
                  <select id="informacionCongregacional" name="informacionCongregacional[]" class="select2GeneradorExcel form-select" multiple>
                    @foreach($camposInformeExcel->where('selector_id',2) as $campo)
                    <option value="{{ $campo->id }}">{{ $campo->nombre_campo_informe }}</option>
                    @endforeach
                  </select>
                </div>

                @if($configuracion->visible_seccion_campos_extra)
                <!-- Informacion congregacional -->
                <div class="col-12 mb-3">
                  <label for="informacionCamposExtras" class="form-label">Información {{$configuracion->label_seccion_campos_extra}} <br>
                    (<a href="javascript:;" data-select="informacionCamposExtras" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionCamposExtras" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                  </label>
                  <select id="informacionCamposExtras" name="informacionCamposExtras[]" class="select2GeneradorExcel form-select" multiple>
                    @foreach($camposExtras as $campo)
                    <option value="{{ $campo->id }}">{{ $campo->nombre }}</option>
                    @endforeach
                  </select>
                </div>
                @endif

                <!-- Información petición-->
                <div class="col-12 mb-3">
                  <label for="informacionCamposPeticiones" class="form-label">Información campos petición <br>
                    (<a href="javascript:;" data-select="informacionCamposPeticiones" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionCamposPeticiones" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                  </label>
                  <select id="informacionCamposPeticiones" name="informacionCamposPeticiones[]" class="select2GeneradorExcel form-select" multiple>
                    @foreach($camposPeticiones as $campoPeticion)
                    <option value="{{ $campoPeticion->id }}">{{ $campoPeticion->nombre }}</option>
                    @endforeach
                  </select>
                </div>


            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Exportar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

@endsection
